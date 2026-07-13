<?php
/**
 *
 * QuickInstall CLI
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace QuickInstall\Sandbox;

use InvalidArgumentException;
use RuntimeException;
use Throwable;

class BoardService
{
	private Project $project;
	private ?Output $output;

	public function __construct(Project $project, ?Output $output = null)
	{
		$this->project = $project;
		$this->output = $output;
	}

	public function create(string $name, string $version = 'latest', string $db = 'mariadb', int $port = 8080, string $populate = 'none', bool $debug = false, bool $replace = false): array
	{
		$this->project->init();
		$this->project->assertName($name, 'board');
		$selection = $this->resolveSourceSelection($version);
		$boards = $this->project->boards();
		$existingName = null;
		foreach (array_keys($boards) as $registeredName)
		{
			if ($this->project->namesEqual((string) $registeredName, $name))
			{
				$existingName = (string) $registeredName;
				break;
			}
		}
		if ($existingName !== null)
		{
			if ($existingName !== $name)
			{
				throw new InvalidArgumentException("Board already exists: $existingName. Board names are case-insensitive.");
			}
			if (!$replace)
			{
				throw new InvalidArgumentException("Board already exists: $name. Use board:destroy first, or pass --replace to recreate it.");
			}
		}

		foreach ($this->project->boards() as $board)
		{
			if (!$this->project->namesEqual((string) ($board['name'] ?? ''), $name) && (int) ($board['port'] ?? 0) === $port)
			{
				throw new InvalidArgumentException("Port $port is already used by board: {$board['name']}");
			}
		}

		$reusingOwnPort = $existingName !== null && (int) ($boards[$existingName]['port'] ?? 0) === $port;
		if (!$reusingOwnPort && $this->isPortInUse($port))
		{
			throw new InvalidArgumentException("Port $port is already in use on this host.");
		}

		$source = $this->createSourceProvider()->ensure($version);
		$php = $source['php'] ?? $selection['php'] ?? null;
		if ($php === null || $php === '')
		{
			throw new RuntimeException("Unable to determine PHP runtime for source: {$source['source_key']}");
		}

		$config = [
			'phpbb' => $source['version'],
			'phpbb_source' => $source['source_key'],
			'php' => $php,
			'db' => $db,
			'port' => $port,
			'populate' => $populate,
			'debug' => $debug,
			'admin_name' => 'admin',
			'admin_pass' => 'password',
			'admin_email' => 'admin@example.test',
			'board_email' => 'board@example.test',
			'extensions' => [],
			'styles' => [],
		];
		$backups = [];
		if ($existingName !== null)
		{
			$this->createBoardRunner()->prepareReplacement($name);
			$backups = $this->backupBoardState($name);
		}

		$boardDir = $this->project->boardPath($name);
		try
		{
			if (!is_dir($boardDir) && !mkdir($boardDir, 0775, true) && !is_dir($boardDir))
			{
				throw new RuntimeException("Unable to create board directory: $boardDir");
			}

			$paths = $this->createDockerComposeWriter()->write($name, $config);
			$board = [
				'name' => $name,
				'phpbb' => $source['version'],
				'phpbb_source' => $source['source_key'],
				'phpbb_branch' => $source['phpbb_branch'],
				'php' => $php,
				'db' => $db,
				'port' => $port,
				'url' => "http://localhost:$port/",
				'path' => $boardDir,
				'populate' => $populate,
				'debug' => $debug,
				'extensions' => [],
				'styles' => [],
				'created_at' => gmdate('c'),
			];

			$this->project->appendBoard($board);
		}
		catch (Throwable $e)
		{
			$this->restoreBoardState($backups);
			throw $e;
		}

		$this->discardBoardBackups($backups);

		return ['board' => $board, 'paths' => $paths];
	}

	private function backupBoardState(string $name): array
	{
		$token = str_replace('.', '', uniqid('replace-', true));
		$backups = [];
		foreach ([$this->project->boardPath($name), $this->project->runtimePath($name), $this->project->dbPath($name)] as $path)
		{
			$backups[$path] = null;
			if (!file_exists($path) && !is_link($path))
			{
				continue;
			}

			$backup = $path . '.' . $token;
			if (!@rename($path, $backup))
			{
				$this->restoreBoardState(array_filter($backups, 'is_string'));
				throw new RuntimeException("Unable to preserve existing board during replacement: $path");
			}
			$backups[$path] = $backup;
		}

		return $backups;
	}

	private function restoreBoardState(array $backups): void
	{
		foreach ($backups as $path => $backup)
		{
			if (file_exists($path) || is_link($path))
			{
				$this->project->deleteTree($path);
			}
			if (is_string($backup) && (file_exists($backup) || is_link($backup)) && !@rename($backup, $path))
			{
				throw new RuntimeException("Unable to restore board after failed replacement: $path");
			}
		}
	}

	private function discardBoardBackups(array $backups): void
	{
		foreach ($backups as $backup)
		{
			if (!is_string($backup))
			{
				continue;
			}
			try
			{
				$this->project->deleteTree($backup);
			}
			catch (RuntimeException $e)
			{
				if ($this->output !== null)
				{
					$this->output->error("Warning: unable to remove replacement backup $backup: {$e->getMessage()}\n");
				}
			}
		}
	}

	private function resolveSourceSelection(string $version): array
	{
		try
		{
			return (new VersionMatrix())->resolve($version);
		}
		catch (InvalidArgumentException $e)
		{
			$sources = $this->project->readJson('sources.json', []);
			if (!isset($sources[$version]))
			{
				throw $e;
			}

			return $sources[$version];
		}
	}

	protected function isPortInUse(int $port): bool
	{
		$socket = @stream_socket_server("tcp://127.0.0.1:$port", $errno, $errstr);
		if ($socket === false)
		{
			return true;
		}

		fclose($socket);
		return false;
	}

	public function list(): array
	{
		$runner = $this->createBoardRunner();
		$boards = [];
		foreach ($this->project->boards() as $board)
		{
			$board['status'] = $runner->status($board['name']);
			$board['populate'] = $board['populate'] ?? 'none';
			$board['debug'] = $board['debug'] ?? false;
			$boards[] = $board;
		}

		return $boards;
	}

	public function start(string $name): array
	{
		$this->createBoardRunner()->start($name);
		return $this->project->board($name);
	}

	public function stop(string $name): void
	{
		$this->createBoardRunner()->stop($name);
	}

	public function destroy(string $name): void
	{
		$this->createBoardRunner()->destroy($name);
	}

	public function seed(string $name, string $preset, int $seed, string $action): void
	{
		$board = $this->project->board($name);
		if (($board['db'] ?? '') === 'sqlite' && $action !== 'reset')
		{
			throw new InvalidArgumentException('SQLite boards do not support fixture seeding. Use --reset to remove partial seed data, or use mariadb, mysql, or postgres for seeded boards.');
		}

		$runner = $this->createBoardRunner();
		if ($runner->status($name) !== 'running')
		{
			throw new RuntimeException('Board must be running before seeding. Start it first.');
		}

		$runner->seed($name, $preset, $seed, $action);
	}

	protected function createBoardRunner(): BoardRunner
	{
		return new BoardRunner($this->project, $this->output);
	}

	protected function createSourceProvider(): SourceProvider
	{
		return new SourceProvider($this->project, $this->output);
	}

	protected function createDockerComposeWriter(): DockerComposeWriter
	{
		return new DockerComposeWriter($this->project);
	}
}
