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

class Application
{
	private Project $project;
	private $stderr;
	private $stdin;

	public function __construct(string $root, $stderr = null, $stdin = null)
	{
		$this->project = new Project($root);
		$this->stderr = $stderr ?: (defined('STDERR') ? STDERR : fopen('php://stderr', 'w'));
		$this->stdin = $stdin ?: (defined('STDIN') ? STDIN : null);
	}

	public function run(array $argv): int
	{
		array_shift($argv);
		$command = array_shift($argv) ?: 'help';
		if (!in_array($command, ['help', '--help', '-h'], true) && (in_array('--help', $argv, true) || in_array('-h', $argv, true)))
		{
			$this->help([$command]);
			return 0;
		}

		try
		{
			switch ($command)
			{
				case 'help':
				case '--help':
				case '-h':
					$this->help($argv);
					return 0;

				case 'init':
					return $this->init();

				case 'source:list':
					return $this->sourceList();

				case 'source:fetch':
					return $this->sourceFetch($argv);

				case 'source:remove':
					return $this->sourceRemove($argv);

				case 'source:prune':
					return $this->sourcePrune();

				case 'phpbb:list':
					return $this->phpbbList();

				case 'board:create':
					return $this->boardCreate($argv);

				case 'board:list':
					return $this->boardList();

				case 'board:start':
					return $this->boardStart($argv);

				case 'board:stop':
					return $this->boardStop($argv);

				case 'board:destroy':
					return $this->boardDestroy($argv);

				case 'board:seed':
					return $this->boardSeed($argv);

				case 'ext:mount':
					return $this->extMount($argv);

				case 'ext:unmount':
					return $this->extUnmount($argv);

				case 'ext:list':
					return $this->extList($argv);

				case 'style:mount':
					return $this->styleMount($argv);

				case 'style:unmount':
					return $this->styleUnmount($argv);

				case 'style:list':
					return $this->styleList($argv);

				case 'ui:start':
					return $this->uiStart($argv);

				case 'ui:stop':
					return $this->uiStop();

				case 'ui:restart':
					return $this->uiRestart($argv);

				case 'ui:status':
					return $this->uiStatus();

				default:
					$this->writeError("Unknown command: $command\n\n");
					$this->help();
					return 1;
			}
		}
		catch (InvalidArgumentException|RuntimeException $e)
		{
			$this->writeError($e->getMessage() . "\n");
			return 1;
		}
	}

	private function init(): int
	{
		$created = $this->project->init();
		if (!$created)
		{
			echo "QuickInstall workspace already initialized\n";
			return 0;
		}

		echo "Created " . implode(' and ', $created) . "\n";
		return 0;
	}

	private function sourceList(): int
	{
		$sources = (new SourceService($this->project, $this->sandboxOutput()))->list();

		if (!$sources)
		{
			echo "No sources registered\n";
			return 0;
		}

		$this->printTable(
			['Source', 'Version', 'Type', 'Status', 'Downloaded', 'Used By', 'Path'],
			array_map(static function ($source) {
				return [
					$source['source_key'] ?? $source['version'],
					$source['version'],
					$source['type'],
					$source['status'] ?? '-',
					!empty($source['downloaded']) ? 'yes' : 'no',
					!empty($source['used_by']) ? implode(', ', $source['used_by']) : '-',
					$source['path'],
				];
			}, $sources)
		);

		return 0;
	}

	private function sourceRemove(array $args): int
	{
		$cli = CommandLine::parse($args);
		$version = $cli->argument(0);
		if ($version === null)
		{
			throw new InvalidArgumentException('Usage: qi source:remove <version|source> [--force]');
		}

		$removed = (new SourceService($this->project, $this->sandboxOutput()))->remove($version, $cli->has('force'));
		echo "Removed source: {$removed['source']['source_key']}\n";
		if (!empty($removed['used_by']))
		{
			echo "Warning: source was referenced by board(s): " . implode(', ', $removed['used_by']) . "\n";
		}

		return 0;
	}

	private function sourcePrune(): int
	{
		$removed = (new SourceService($this->project, $this->sandboxOutput()))->prune();
		if (!$removed)
		{
			echo "No unused sources to prune\n";
			return 0;
		}

		foreach ($removed as $source)
		{
			echo "Removed source: {$source['source_key']}\n";
		}

		return 0;
	}

	private function sourceFetch(array $args): int
	{
		$cli = CommandLine::parse($args);
		$version = $cli->argument(0);
		if ($version === null)
		{
			throw new InvalidArgumentException('Usage: qi source:fetch <version|branch> [--git] [--url URL] [--allow-external]');
		}

		$record = (new SourceService($this->project, $this->sandboxOutput()))->fetch($version, $cli->has('git'), $cli->option('url'), $cli->has('allow-external'));

		echo "Fetched phpBB source: {$record['path']}\n";
		return 0;
	}

	private function phpbbList(): int
	{
		$this->printTable(
			['Selector', 'Status', 'PHP', 'Resolves To', 'Notes'],
			array_map(static function ($row) {
				return [
					$row['selector'],
					$row['status'],
					$row['php'] === '-' ? '-' : 'PHP ' . $row['php'],
					$row['resolves_to'],
					$row['notes'],
				];
			}, (new SourceService($this->project, $this->sandboxOutput()))->supportedVersions())
		);

		return 0;
	}

	private function boardCreate(array $args): int
	{
		$cli = CommandLine::parse($args);
		$name = $cli->argument(0);
		if ($name === null)
		{
			throw new InvalidArgumentException('Usage: qi board:create <name> [--phpbb VERSION] [--db mariadb|mysql|postgres|sqlite] [--port PORT] [--populate PRESET] [--debug] [--replace]');
		}

		$version = $cli->option('phpbb', 'latest');
		$db = $cli->option('db', 'mariadb');
		$db = $db === 'sqlite3' ? 'sqlite' : $db;
		$port = (int) $cli->option('port', '8080');
		$populate = $cli->option('populate', 'none');
		$debug = $cli->has('debug');
		$this->validateBoardCreateOptions($db, $port, $populate);

		$created = (new BoardService($this->project, $this->sandboxOutput()))->create($name, $version, $db, $port, $populate, $debug, $cli->has('replace'));
		$paths = $created['paths'];

		echo "Created board scaffold: $name\n";
		echo "Compose: {$paths['compose']}\n";
		echo "Install config: {$paths['install_config']}\n";
		echo "URL after start: http://localhost:$port/\n";
		if ($populate !== 'none')
		{
			echo "Populate preset: $populate (runs on board:start)\n";
		}
		if ($debug)
		{
			echo "Debug mode: enabled on board:start\n";
		}
		$this->nextStep("php bin/qi board:start $name");
		if ($this->confirm('Run this command now? [Y/n]: ', true))
		{
			return $this->boardStart([$name]);
		}

		return 0;
	}

	private function boardList(): int
	{
		$boards = (new BoardService($this->project, $this->sandboxOutput()))->list();
		if (!$boards)
		{
			echo "No boards created\n";
			return 0;
		}

		$this->printTable(
			['Name', 'Status', 'phpBB', 'PHP', 'DB', 'Populate', 'Debug', 'URL'],
			array_map(static function ($board) {
				return [
					$board['name'],
					$board['status'],
					$board['phpbb'],
					$board['php'],
					$board['db'],
					$board['populate'] ?? 'none',
					!empty($board['debug']) ? 'yes' : 'no',
					$board['url'],
				];
			}, $boards)
		);

		return 0;
	}

	private function printTable(array $headers, array $rows): void
	{
		$widths = array_map('strlen', $headers);
		foreach ($rows as $row)
		{
			foreach ($row as $index => $value)
			{
				$widths[$index] = max($widths[$index], strlen((string) $value));
			}
		}

		$this->printTableRow($headers, $widths);
		$this->printTableRow(array_map(static function ($width) {
			return str_repeat('-', $width);
		}, $widths), $widths);

		foreach ($rows as $row)
		{
			$this->printTableRow($row, $widths);
		}
	}

	private function printTableRow(array $row, array $widths): void
	{
		$cells = [];
		foreach ($row as $index => $value)
		{
			$cells[] = str_pad((string) $value, $widths[$index]);
		}

		echo implode('  ', $cells) . "\n";
	}

	private function nextStep(string $text): void
	{
		echo "\n" . $this->style('NEXT:', '1;33') . " " . $this->style($text, '1') . "\n";
	}

	private function confirm(string $question, bool $default = false): bool
	{
		if (!$this->shouldPrompt())
		{
			return false;
		}

		while (true)
		{
			echo $question;
			$answer = fgets($this->stdin);
			if ($answer === false)
			{
				echo "\n";
				return false;
			}

			$answer = strtolower(trim($answer));
			if ($answer === '')
			{
				return $default;
			}
			if ($answer === 'y' || $answer === 'yes')
			{
				return true;
			}
			if ($answer === 'n' || $answer === 'no')
			{
				return false;
			}

			echo "Please answer y or n.\n";
		}
	}

	private function shouldPrompt(): bool
	{
		if (!is_resource($this->stdin))
		{
			return false;
		}

		if (defined('STDIN') && $this->stdin === STDIN)
		{
			return !function_exists('posix_isatty') || posix_isatty(STDIN);
		}

		return true;
	}

	private function style(string $text, string $code): string
	{
		if (!$this->supportsAnsi())
		{
			return $text;
		}

		return "\033[" . $code . "m" . $text . "\033[0m";
	}

	private function supportsAnsi(): bool
	{
		if (getenv('NO_COLOR') !== false)
		{
			return false;
		}

		if (function_exists('posix_isatty') && defined('STDOUT'))
		{
			return posix_isatty(STDOUT);
		}

		return PHP_SAPI === 'cli';
	}

	private function boardStart(array $args): int
	{
		$name = $this->boardName($args, 'Usage: qi board:start <name>');
		$board = (new BoardService($this->project, $this->sandboxOutput()))->start($name);
		echo "Started board: $name\n";
		echo "URL: {$board['url']}\n";
		return 0;
	}

	private function boardStop(array $args): int
	{
		$name = $this->boardName($args, 'Usage: qi board:stop <name>');
		(new BoardService($this->project, $this->sandboxOutput()))->stop($name);
		echo "Stopped board: $name\n";
		return 0;
	}

	private function boardDestroy(array $args): int
	{
		$name = $this->boardName($args, 'Usage: qi board:destroy <name>');
		(new BoardService($this->project, $this->sandboxOutput()))->destroy($name);
		echo "Destroyed board: $name\n";
		return 0;
	}

	private function boardSeed(array $args): int
	{
		$cli = CommandLine::parse($args);
		$name = $cli->argument(0);
		if ($name === null)
		{
			throw new InvalidArgumentException('Usage: qi board:seed <name> [--preset tiny|extension-dev|load-test|random] [--seed N] [--reset|--replace]');
		}

		$preset = $cli->option('preset', 'extension-dev');
		$seed = (int) $cli->option('seed', '1');
		$this->validatePreset($preset);
		if ($seed < 1)
		{
			throw new InvalidArgumentException('--seed must be a positive integer.');
		}
		if ($cli->has('reset') && $cli->has('replace'))
		{
			throw new InvalidArgumentException('Use --reset or --replace, not both.');
		}
		$action = $cli->has('reset') ? 'reset' : ($cli->has('replace') ? 'replace' : 'seed');

		(new BoardService($this->project, $this->sandboxOutput()))->seed($name, $preset, $seed, $action);
		echo ucfirst($action) . " completed for board: $name\n";
		return 0;
	}

	private function boardName(array $args, string $usage): string
	{
		$cli = CommandLine::parse($args);
		$name = $cli->argument(0);
		if ($name === null)
		{
			throw new InvalidArgumentException($usage);
		}

		return $name;
	}

	private function validateBoardCreateOptions(string $db, int $port, string $populate): void
	{
		if (!in_array($db, ['mariadb', 'mysql', 'postgres', 'sqlite'], true))
		{
			throw new InvalidArgumentException('--db must be one of: mariadb, mysql, postgres, sqlite.');
		}

		if ($port < 1 || $port > 65535)
		{
			throw new InvalidArgumentException('--port must be between 1 and 65535.');
		}

		if ($populate !== 'none')
		{
			$this->validatePreset($populate);
		}

		if ($db === 'sqlite' && $populate !== 'none')
		{
			throw new InvalidArgumentException('SQLite boards currently support --populate none only. Use mariadb, mysql, or postgres for fixture seeding.');
		}
	}

	private function validatePreset(string $preset): void
	{
		if (!in_array($preset, ['tiny', 'extension-dev', 'load-test', 'random'], true))
		{
			throw new InvalidArgumentException('Preset must be one of: tiny, extension-dev, load-test, random.');
		}
	}

	private function extMount(array $args): int
	{
		$cli = CommandLine::parse($args);
		$board = $cli->argument(0);
		$source = $cli->argument(1);
		if ($board === null || $source === null)
		{
			throw new InvalidArgumentException('Usage: qi ext:mount <board> <path> [--copy] [--recursive] [--allow-external]');
		}
		if ($cli->has('recursive') && $cli->has('copy'))
		{
			throw new InvalidArgumentException('--recursive cannot be combined with --copy. Mount recursively with bind mode, or copy individual extensions.');
		}

		return $this->mountResources('extension', new ExtensionManager($this->project), $board, $source, $cli->has('copy'), $cli->has('recursive'), $cli->has('allow-external'));
	}

	private function extUnmount(array $args): int
	{
		$cli = CommandLine::parse($args);
		$board = $cli->argument(0);
		$name = $cli->argument(1);
		if ($board === null || $name === null)
		{
			throw new InvalidArgumentException('Usage: qi ext:unmount <board> <vendor/extension>');
		}

		$extensions = new ExtensionManager($this->project);
		$target = $extensions->unmount($board, $name);
		$this->refreshBoardIfRunning($board);
		$extensions->cleanupStaleTarget($board, $name);
		echo "Unmounted $name from $board\n";
		echo "Removed: $target\n";
		return 0;
	}

	private function refreshBoardIfRunning(string $board): void
	{
		(new BoardRefreshService($this->project))->refreshIfRunning($board);
	}

	private function runtimeConfig(array $board): array
	{
		if (empty($board['port']) && !empty($board['url']))
		{
			$port = parse_url($board['url'], PHP_URL_PORT);
			$board['port'] = $port ?: 80;
		}

		return $board + [
			'admin_name' => 'admin',
			'admin_pass' => 'password',
			'admin_email' => 'admin@example.test',
			'board_email' => 'board@example.test',
			'populate' => 'none',
			'debug' => false,
			'extensions' => [],
			'styles' => [],
		];
	}

	private function uiStart(array $args): int
	{
		$cli = CommandLine::parse($args);
		[$host, $port] = $this->uiOptions($cli);
		$state = $this->readUiState();
		if ($this->isUiStateRunning($state))
		{
			echo "QuickInstall sandbox UI is already running: {$state['url']}\n";
			return 0;
		}
		if ($this->isPortOpen($host, $port))
		{
			throw new RuntimeException("Port $port is already in use on $host. Run qi ui:status for tracked UI details, or choose another port with --port.");
		}

		$this->project->init();
		$router = dirname(__DIR__, 3) . '/public/sandbox-ui.php';
		$token = bin2hex(random_bytes(24));
		$url = "http://{$host}:{$port}/?token={$token}";
		$command = [PHP_BINARY, '-S', $host . ':' . $port, $router];
		$logPath = $this->uiLogPath();
		putenv('QI_SANDBOX_UI_TOKEN=' . $token);
		$pid = $this->startDetachedProcess($command, dirname(__DIR__, 3), $logPath);
		$state = [
			'pid' => $pid,
			'host' => $host,
			'port' => $port,
			'url' => $url,
			'token' => $token,
			'log' => $logPath,
			'started_at' => gmdate('c'),
		];
		try
		{
			$this->waitForUiStart($state);
		}
		catch (RuntimeException $e)
		{
			$this->terminateProcess($pid);
			throw $e;
		}
		$this->writeUiState($state);

		echo "QuickInstall sandbox UI started: $url\n";
		echo "PID: $pid\n";
		echo "Log: $logPath\n";
		echo "Stop it with: php bin/qi ui:stop\n";

		return 0;
	}

	private function startDetachedProcess(array $command, string $cwd, string $logPath): int
	{
		if (PHP_OS_FAMILY === 'Windows')
		{
			return $this->startWindowsDetachedProcess($command, $cwd, $logPath);
		}

		return $this->startUnixDetachedProcess($command, $cwd, $logPath);
	}

	private function startUnixDetachedProcess(array $command, string $cwd, string $logPath): int
	{
		$pidFile = $this->project->workspacePath('runtime/ui.pid');
		$shellCommand = 'cd ' . escapeshellarg($cwd)
			. ' && (QI_SANDBOX_UI_TOKEN=' . escapeshellarg((string) getenv('QI_SANDBOX_UI_TOKEN'))
			. ' nohup ' . implode(' ', array_map('escapeshellarg', $command))
			. ' >> ' . escapeshellarg($logPath) . ' 2>&1 < /dev/null & echo $! > ' . escapeshellarg($pidFile) . ')';
		$result = $this->captureProcess(['/bin/sh', '-c', $shellCommand], $cwd);
		$pid = is_file($pidFile) ? (int) trim((string) file_get_contents($pidFile)) : 0;
		if (is_file($pidFile))
		{
			unlink($pidFile);
		}
		if ($result['exit_code'] !== 0 || $pid <= 0)
		{
			throw new RuntimeException('Unable to start QuickInstall sandbox UI server.');
		}

		return $pid;
	}

	private function startWindowsDetachedProcess(array $command, string $cwd, string $logPath): int
	{
		$arguments = array_slice($command, 1);
		$argumentList = '@(' . implode(',', array_map([$this, 'powerShellString'], $arguments)) . ')';
		$errorLog = $logPath . '.err';
		$script = '$env:QI_SANDBOX_UI_TOKEN = ' . $this->powerShellString((string) getenv('QI_SANDBOX_UI_TOKEN')) . '; '
			. '$p = Start-Process -FilePath ' . $this->powerShellString($command[0])
			. ' -ArgumentList ' . $argumentList
			. ' -WorkingDirectory ' . $this->powerShellString($cwd)
			. ' -RedirectStandardOutput ' . $this->powerShellString($logPath)
			. ' -RedirectStandardError ' . $this->powerShellString($errorLog)
			. ' -WindowStyle Hidden -PassThru; '
			. 'Write-Output $p.Id';
		$result = $this->captureProcess(['powershell', '-NoProfile', '-ExecutionPolicy', 'Bypass', '-Command', $script], $cwd);
		$pid = (int) trim($result['output']);
		if ($result['exit_code'] !== 0 || $pid <= 0)
		{
			throw new RuntimeException('Unable to start QuickInstall sandbox UI server. PowerShell is required on Windows.');
		}

		return $pid;
	}

	private function captureProcess(array $command, string $cwd): array
	{
		$descriptor = [
			0 => ['pipe', 'r'],
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		];
		$process = proc_open($command, $descriptor, $pipes, $cwd);
		if (!is_resource($process))
		{
			return ['exit_code' => 1, 'output' => ''];
		}

		fclose($pipes[0]);
		$output = stream_get_contents($pipes[1]) ?: '';
		$error = stream_get_contents($pipes[2]) ?: '';
		fclose($pipes[1]);
		fclose($pipes[2]);

		return [
			'exit_code' => proc_close($process),
			'output' => $output . $error,
		];
	}

	private function powerShellString(string $value): string
	{
		return "'" . str_replace("'", "''", $value) . "'";
	}

	private function uiStop(): int
	{
		$state = $this->readUiState();
		if (!$state)
		{
			echo "QuickInstall sandbox UI is not tracked as running.\n";
			return 0;
		}

		$pid = (int) ($state['pid'] ?? 0);
		if ($pid > 0 && $this->terminateProcess($pid))
		{
			$this->waitForUiStop($state);
			echo "Stopped QuickInstall sandbox UI";
			if (!empty($state['url']))
			{
				echo ": {$state['url']}";
			}
			echo "\n";
		}
		else
		{
			echo "QuickInstall sandbox UI process was not running";
			if ($pid > 0)
			{
				echo " (PID $pid)";
			}
			echo "\n";
		}

		$this->deleteUiState();
		return 0;
	}

	private function uiRestart(array $args): int
	{
		$this->uiStop();
		return $this->uiStart($args);
	}

	private function uiStatus(): int
	{
		$state = $this->readUiState();
		if ($this->isUiStateRunning($state))
		{
			echo "QuickInstall sandbox UI is running\n";
			echo "URL: {$state['url']}\n";
			echo "PID: {$state['pid']}\n";
			echo "Log: {$state['log']}\n";
			return 0;
		}

		if ($state)
		{
			echo "QuickInstall sandbox UI is not running, but stale state exists.\n";
			echo "Last URL: " . ($state['url'] ?? '(unknown)') . "\n";
			echo "Run: php bin/qi ui:stop\n";
			return 1;
		}

		echo "QuickInstall sandbox UI is not running.\n";
		return 0;
	}

	private function uiOptions(CommandLine $cli): array
	{
		$host = $cli->option('host', '127.0.0.1');
		$port = (int) $cli->option('port', '8079');
		if (!in_array($host, ['127.0.0.1', 'localhost', '::1'], true))
		{
			throw new InvalidArgumentException('ui:start only supports local loopback hosts: 127.0.0.1, localhost, or ::1.');
		}
		if ($port < 1 || $port > 65535)
		{
			throw new InvalidArgumentException('--port must be between 1 and 65535.');
		}

		return [$host, $port];
	}

	private function readUiState(): array
	{
		return $this->project->readJson('runtime/ui.json', []);
	}

	private function writeUiState(array $state): void
	{
		$this->project->writeJson('runtime/ui.json', $state);
	}

	private function deleteUiState(): void
	{
		$path = $this->project->workspacePath('runtime/ui.json');
		if (is_file($path))
		{
			unlink($path);
		}
	}

	private function uiLogPath(): string
	{
		$this->project->init();
		return $this->project->workspacePath('runtime/ui.log');
	}

	private function isUiStateRunning(array $state): bool
	{
		if (!$state)
		{
			return false;
		}

		$pid = (int) ($state['pid'] ?? 0);
		if ($pid <= 0 || !$this->isProcessRunning($pid))
		{
			return false;
		}

		return $this->isPortOpen((string) ($state['host'] ?? '127.0.0.1'), (int) ($state['port'] ?? 0));
	}

	private function isPortOpen(string $host, int $port): bool
	{
		if ($port < 1)
		{
			return false;
		}
		$target = $host === 'localhost' ? '127.0.0.1' : $host;
		$socket = @fsockopen($target, $port, $errno, $errstr, 0.2);
		if (!is_resource($socket))
		{
			return false;
		}

		fclose($socket);
		return true;
	}

	private function terminateProcess(int $pid): bool
	{
		if ($pid <= 0)
		{
			return false;
		}
		if (PHP_OS_FAMILY === 'Windows')
		{
			$result = (new ProcessRunner(new BufferedOutput()))->capture(['taskkill', '/PID', (string) $pid, '/T', '/F']);
			return $result['exit_code'] === 0;
		}
		if (function_exists('posix_kill') && @posix_kill($pid, 0))
		{
			return @posix_kill($pid, 15);
		}

		$result = (new ProcessRunner(new BufferedOutput()))->capture(['kill', (string) $pid]);
		return $result['exit_code'] === 0;
	}

	private function isProcessRunning(int $pid): bool
	{
		if ($pid <= 0)
		{
			return false;
		}
		if (PHP_OS_FAMILY === 'Windows')
		{
			$result = (new ProcessRunner(new BufferedOutput()))->capture(['tasklist', '/FI', 'PID eq ' . $pid, '/NH']);
			return $result['exit_code'] === 0 && strpos($result['output'], (string) $pid) !== false;
		}
		if (function_exists('posix_kill'))
		{
			return @posix_kill($pid, 0);
		}

		$result = (new ProcessRunner(new BufferedOutput()))->capture(['kill', '-0', (string) $pid]);
		return $result['exit_code'] === 0;
	}

	private function waitForUiStart(array $state): void
	{
		$host = (string) ($state['host'] ?? '127.0.0.1');
		$port = (int) ($state['port'] ?? 0);
		$deadline = microtime(true) + 3;
		while (microtime(true) < $deadline)
		{
			if ($this->isPortOpen($host, $port))
			{
				return;
			}
			usleep(100000);
		}

		throw new RuntimeException('QuickInstall sandbox UI server did not become available. Check the UI log for details: ' . ($state['log'] ?? '(unknown)'));
	}

	private function waitForUiStop(array $state): void
	{
		$host = (string) ($state['host'] ?? '127.0.0.1');
		$port = (int) ($state['port'] ?? 0);
		$deadline = microtime(true) + 3;
		while (microtime(true) < $deadline)
		{
			if (!$this->isPortOpen($host, $port))
			{
				return;
			}
			usleep(100000);
		}
	}

	private function extList(array $args): int
	{
		$board = $this->boardName($args, 'Usage: qi ext:list <board>');
		$mounted = (new ExtensionManager($this->project))->list($board);

		if (!$mounted)
		{
			echo "No extensions mounted for board: $board\n";
			return 0;
		}

		$this->printTable(
			['Extension', 'Mode', 'Source'],
			array_map(static function ($extension) {
				return [
					$extension['name'],
					$extension['mode'],
					$extension['source'],
				];
			}, $mounted)
		);

		return 0;
	}

	private function styleMount(array $args): int
	{
		$cli = CommandLine::parse($args);
		$board = $cli->argument(0);
		$source = $cli->argument(1);
		if ($board === null || $source === null)
		{
			throw new InvalidArgumentException('Usage: qi style:mount <board> <path> [--copy] [--recursive] [--allow-external]');
		}
		if ($cli->has('recursive') && $cli->has('copy'))
		{
			throw new InvalidArgumentException('--recursive cannot be combined with --copy. Mount recursively with bind mode, or copy individual styles.');
		}

		return $this->mountResources('style', new StyleManager($this->project), $board, $source, $cli->has('copy'), $cli->has('recursive'), $cli->has('allow-external'));
	}

	private function styleUnmount(array $args): int
	{
		$cli = CommandLine::parse($args);
		$board = $cli->argument(0);
		$name = $cli->argument(1);
		if ($board === null || $name === null)
		{
			throw new InvalidArgumentException('Usage: qi style:unmount <board> <style>');
		}

		$styles = new StyleManager($this->project);
		$target = $styles->unmount($board, $name);
		$this->refreshBoardIfRunning($board);
		$styles->cleanupStaleTarget($board, $name);
		echo "Unmounted $name from $board\n";
		echo "Removed: $target\n";
		return 0;
	}

	private function styleList(array $args): int
	{
		$board = $this->boardName($args, 'Usage: qi style:list <board>');
		$mounted = (new StyleManager($this->project))->list($board);

		if (!$mounted)
		{
			echo "No styles mounted for board: $board\n";
			return 0;
		}

		$this->printTable(
			['Style', 'Mode', 'Source'],
			array_map(static function ($style) {
				return [
					$style['name'],
					$style['mode'],
					$style['source'],
				];
			}, $mounted)
		);

		return 0;
	}

	private function printBulkMountResult(string $type, string $board, array $mounted, array $errors): void
	{
		if (!$mounted && !$errors)
		{
			echo "No {$type}s found for board: $board\n";
			return;
		}

		foreach ($mounted as $item)
		{
			echo "Mounted {$item['name']} on $board ({$item['mode']})\n";
		}

		foreach ($errors as $error)
		{
			$this->writeError("Skipped $type: $error\n");
		}
	}

	private function writeError(string $message): void
	{
		fwrite($this->stderr, $message);
	}

	private function sandboxOutput(): Output
	{
		$stdout = defined('STDOUT') && defined('STDERR') && $this->stderr === STDERR ? STDOUT : fopen('php://output', 'w');
		return new StreamOutput($stdout, $this->stderr);
	}

	private function mountResources(string $type, object $manager, string $board, string $source, bool $copy, bool $recursive, bool $allowExternal): int
	{
		if ($recursive)
		{
			$this->project->board($board);
			$mounted = [];
			$errors = [];
			foreach ($manager->discover($source, $allowExternal) as $path)
			{
				try
				{
					$mounted[] = $manager->mount($board, $path, false, $allowExternal);
				}
				catch (RuntimeException | InvalidArgumentException $e)
				{
					$errors[] = "$path: " . $e->getMessage();
				}
			}

			if ($mounted)
			{
				$this->refreshBoardIfRunning($board);
			}
			$this->printBulkMountResult($type, $board, $mounted, $errors);
			return $errors ? 1 : 0;
		}

		$mounted = $manager->mount($board, $source, $copy, $allowExternal);
		$this->refreshBoardIfRunning($board);
		echo "Mounted {$mounted['name']} on $board ({$mounted['mode']})\n";
		echo "Source: {$mounted['source']}\n";
		echo "Target: {$mounted['target']}\n";
		return 0;
	}

	private function help(array $args = []): void
	{
		$command = $args[0] ?? null;
		$commands = $this->helpCommands();
		if ($command !== null)
		{
			$this->helpCommand($command, $commands);
			return;
		}

		echo "QuickInstall CLI\n";
		echo "Create disposable local phpBB boards with Docker.\n\n";
		echo "Usage:\n";
		echo "  qi <command> [arguments] [options]\n";
		echo "  qi help [command]\n\n";
		echo "Common workflow:\n";
		echo "  qi board:create demo --phpbb 3.3 --db mariadb --port 8081 --populate extension-dev\n";
		echo "  qi board:start demo\n\n";

		foreach ($commands as $group => $items)
		{
			echo "$group:\n";
			$width = max(array_map('strlen', array_keys($items)));
			foreach ($items as $name => $help)
			{
				echo '  ' . str_pad($name, $width) . '  ' . $help['summary'] . "\n";
			}
			echo "\n";
		}

		echo "Run `qi help <command>` for usage and options.\n";
		echo "Full guide: docs/sandbox-cli.md\n";
	}

	private function helpCommand(string $command, array $groups): void
	{
		foreach ($groups as $items)
		{
			if (!isset($items[$command]))
			{
				continue;
			}

			$help = $items[$command];
			echo "{$help['title']}\n\n";
			echo "Usage:\n";
			echo "  qi {$help['usage']}\n\n";
			echo "Description:\n";
			echo "  {$help['description']}\n";
			if (!empty($help['arguments']))
			{
				echo "\nArguments:\n";
				$this->printHelpRows($help['arguments']);
			}
			if (!empty($help['options']))
			{
				echo "\nOptions:\n";
				$this->printHelpRows($help['options']);
			}
			if (!empty($help['examples']))
			{
				echo "\nExamples:\n";
				foreach ($help['examples'] as $example)
				{
					echo "  qi $example\n";
				}
			}
			echo "\n";
			return;
		}

		echo "Unknown command: $command\n\n";
		$this->help();
	}

	private function printHelpRows(array $rows): void
	{
		$width = max(array_map('strlen', array_keys($rows)));
		foreach ($rows as $name => $description)
		{
			echo '  ' . str_pad($name, $width) . '  ' . $description . "\n";
		}
	}

	private function helpCommands(): array
	{
		return [
			'Board commands' => [
				'board:create' => [
					'title' => 'board:create',
					'usage' => 'board:create <name> [--phpbb VERSION] [--db mariadb|mysql|postgres|sqlite] [--port PORT] [--populate PRESET] [--debug] [--replace]',
					'summary' => 'Create a board scaffold and Docker runtime.',
					'description' => 'Creates a local board definition, downloads the requested phpBB source if needed, and prepares Docker files. Run board:start after this.',
					'arguments' => [
						'<name>' => 'Required board name. Use a short local name such as demo or extdev.',
					],
					'options' => [
						'--phpbb VERSION' => 'phpBB selector. Examples: latest, 3.3, 3.3.17, 3.2, master. Default: latest.',
						'--db DB' => 'Database engine. One of: mariadb, mysql, postgres, sqlite. Default: mariadb.',
						'--port PORT' => 'Local browser port. Default: 8080.',
						'--populate PRESET' => 'Seed preset. One of: none, tiny, extension-dev, load-test, random. Default: none.',
						'--debug' => 'Enable phpBB debug settings after install.',
						'--replace' => 'Destroy an existing board with the same name before creating the new one.',
					],
					'examples' => [
						'board:create demo --phpbb 3.3 --db mariadb --port 8081',
						'board:create extdev --phpbb 3.3.17 --populate extension-dev --debug',
					],
				],
				'board:start' => [
					'title' => 'board:start',
					'usage' => 'board:start <name>',
					'summary' => 'Start the board containers and install the board if needed.',
					'description' => 'Starts the board containers with Docker Compose, runs phpBB install on first start, applies the configured populate preset once, and waits for the board URL to respond. Docker must already be running.',
					'arguments' => [
						'<name>' => 'Required board name.',
					],
					'examples' => [
						'board:start demo',
					],
				],
				'board:stop' => [
					'title' => 'board:stop',
					'usage' => 'board:stop <name>',
					'summary' => 'Stop a running board.',
					'description' => 'Stops the board containers without deleting board files or database files.',
					'arguments' => [
						'<name>' => 'Required board name.',
					],
					'examples' => [
						'board:stop demo',
					],
				],
				'board:destroy' => [
					'title' => 'board:destroy',
					'usage' => 'board:destroy <name>',
					'summary' => 'Delete a board and its Docker image.',
					'description' => 'Removes the board files, runtime files, database files, containers, local Docker image, and board registry entry.',
					'arguments' => [
						'<name>' => 'Required board name.',
					],
					'examples' => [
						'board:destroy demo',
					],
				],
				'board:list' => [
					'title' => 'board:list',
					'usage' => 'board:list',
					'summary' => 'Show created boards and running status.',
					'description' => 'Lists known boards with phpBB version, PHP version, database type, populate preset, debug flag, URL, and running status.',
					'examples' => [
						'board:list',
					],
				],
				'board:seed' => [
					'title' => 'board:seed',
					'usage' => 'board:seed <name> [--preset tiny|extension-dev|load-test|random] [--seed N] [--reset|--replace]',
					'summary' => 'Add, replace, or remove fixture content.',
					'description' => 'Seeds categories, forums, users, topics, and replies on an installed board. SQLite boards only support reset.',
					'arguments' => [
						'<name>' => 'Required board name.',
					],
					'options' => [
						'--preset PRESET' => 'Fixture preset. One of: tiny, extension-dev, load-test, random. Default: extension-dev.',
						'--seed N' => 'Positive random seed number for repeatable fixture shape. Default: 1.',
						'--replace' => 'Remove existing QuickInstall seed data, then seed again.',
						'--reset' => 'Remove existing QuickInstall seed data without adding new data.',
					],
					'examples' => [
						'board:seed demo --preset extension-dev --seed 1',
						'board:seed demo --preset extension-dev --replace',
					],
				],
			],
			'Extension commands' => [
				'ext:mount' => [
					'title' => 'ext:mount',
					'usage' => 'ext:mount <board> <path> [--copy] [--recursive] [--allow-external]',
					'summary' => 'Mount one or more extensions into a board.',
					'description' => 'Mounts a phpBB extension from the customisations drop zone. Running boards are refreshed automatically.',
					'arguments' => [
						'<board>' => 'Required board name.',
						'<path>' => 'Extension path, or a directory to scan when --recursive is used.',
					],
					'options' => [
						'--copy' => 'Copy one extension instead of bind-mounting it.',
						'--recursive' => 'Find and bind-mount all extensions below <path>. Cannot be combined with --copy.',
						'--allow-external' => 'Allow trusted paths outside the customisations drop zone.',
					],
					'examples' => [
						'ext:mount demo customisations/vendor/extname',
						'ext:mount demo customisations --recursive',
					],
				],
				'ext:unmount' => [
					'title' => 'ext:unmount',
					'usage' => 'ext:unmount <board> <vendor/extension>',
					'summary' => 'Remove a mounted extension from a board.',
					'description' => 'Removes the extension mount or copied extension files, refreshes the board if running, and clears stale targets.',
					'arguments' => [
						'<board>' => 'Required board name.',
						'<vendor/extension>' => 'Extension name from composer.json, such as phpbb/foo.',
					],
					'examples' => [
						'ext:unmount demo vendor/extname',
					],
				],
				'ext:list' => [
					'title' => 'ext:list',
					'usage' => 'ext:list <board>',
					'summary' => 'Show extensions mounted on a board.',
					'description' => 'Lists mounted extensions, mount mode, and source path for one board.',
					'arguments' => [
						'<board>' => 'Required board name.',
					],
					'examples' => [
						'ext:list demo',
					],
				],
			],
			'Style commands' => [
				'style:mount' => [
					'title' => 'style:mount',
					'usage' => 'style:mount <board> <path> [--copy] [--recursive] [--allow-external]',
					'summary' => 'Mount one or more styles into a board.',
					'description' => 'Mounts a phpBB style from the customisations drop zone. Running boards are refreshed automatically.',
					'arguments' => [
						'<board>' => 'Required board name.',
						'<path>' => 'Style path, or a directory to scan when --recursive is used.',
					],
					'options' => [
						'--copy' => 'Copy one style instead of bind-mounting it.',
						'--recursive' => 'Find and bind-mount all styles below <path>. Cannot be combined with --copy.',
						'--allow-external' => 'Allow trusted paths outside the customisations drop zone.',
					],
					'examples' => [
						'style:mount demo customisations/stylename',
						'style:mount demo customisations --recursive',
					],
				],
				'style:unmount' => [
					'title' => 'style:unmount',
					'usage' => 'style:unmount <board> <style>',
					'summary' => 'Remove a mounted style from a board.',
					'description' => 'Removes the style mount or copied style files, refreshes the board if running, and clears stale targets.',
					'arguments' => [
						'<board>' => 'Required board name.',
						'<style>' => 'Style folder name.',
					],
					'examples' => [
						'style:unmount demo stylename',
					],
				],
				'style:list' => [
					'title' => 'style:list',
					'usage' => 'style:list <board>',
					'summary' => 'Show styles mounted on a board.',
					'description' => 'Lists mounted styles, mount mode, and source path for one board.',
					'arguments' => [
						'<board>' => 'Required board name.',
					],
					'examples' => [
						'style:list demo',
					],
				],
			],
			'UI commands' => [
				'ui:start' => [
					'title' => 'ui:start',
					'usage' => 'ui:start [--host 127.0.0.1] [--port 8079]',
					'summary' => 'Start the local sandbox admin UI.',
					'description' => 'Starts PHP built-in server in the background on a loopback address and serves a minimal admin UI backed by the sandbox services.',
					'options' => [
						'--host HOST' => 'Loopback host. One of: 127.0.0.1, localhost, ::1. Default: 127.0.0.1.',
						'--port PORT' => 'Local UI port. Default: 8079.',
					],
					'examples' => [
						'ui:start',
						'ui:start --port 8088',
					],
				],
				'ui:stop' => [
					'title' => 'ui:stop',
					'usage' => 'ui:stop',
					'summary' => 'Stop the tracked local sandbox admin UI.',
					'description' => 'Stops the background UI server that was started with ui:start and removes its runtime state file.',
					'examples' => [
						'ui:stop',
					],
				],
				'ui:restart' => [
					'title' => 'ui:restart',
					'usage' => 'ui:restart [--host 127.0.0.1] [--port 8079]',
					'summary' => 'Restart the local sandbox admin UI.',
					'description' => 'Stops the tracked UI server if present, then starts a fresh tokenized UI server.',
					'options' => [
						'--host HOST' => 'Loopback host. One of: 127.0.0.1, localhost, ::1. Default: 127.0.0.1.',
						'--port PORT' => 'Local UI port. Default: 8079.',
					],
					'examples' => [
						'ui:restart',
					],
				],
				'ui:status' => [
					'title' => 'ui:status',
					'usage' => 'ui:status',
					'summary' => 'Show whether the local sandbox admin UI is running.',
					'description' => 'Reads the tracked UI runtime state and checks whether the configured local UI port responds.',
					'examples' => [
						'ui:status',
					],
				],
			],
			'Source commands' => [
				'source:fetch' => [
					'title' => 'source:fetch',
					'usage' => 'source:fetch <version|branch> [--git] [--url URL] [--allow-external]',
					'summary' => 'Register and download a phpBB source.',
					'description' => 'Registers and downloads a source under .qi/sources. Normal board:create flows fetch automatically when needed. Prefer exact tags such as 3.3.17; convenience selectors such as 3.3, 3.3.x, latest, and master are stored under the exact phpBB version that was resolved.',
					'arguments' => [
						'<version|branch>' => 'Version selector or source name, such as 3.3.17 or master.',
					],
					'options' => [
						'--git' => 'Fetch a Git source instead of a Composer release source.',
						'--url URL' => 'Git repository URL. Defaults to the official phpBB repository for Git sources.',
						'--allow-external' => 'Allow a trusted non-phpBB Git URL.',
					],
					'examples' => [
						'source:fetch 3.3.17',
						'source:fetch master --git --url https://github.com/phpbb/phpbb.git',
					],
				],
				'source:list' => [
					'title' => 'source:list',
					'usage' => 'source:list',
					'summary' => 'Show registered and downloaded sources.',
					'description' => 'Lists sources, download status, paths, and boards currently using each source.',
					'examples' => [
						'source:list',
					],
				],
				'source:remove' => [
					'title' => 'source:remove',
					'usage' => 'source:remove <version|source> [--force]',
					'summary' => 'Delete one source.',
					'description' => 'Deletes one source from .qi/sources and removes it from the source registry.',
					'arguments' => [
						'<version|source>' => 'Registered source selector.',
					],
					'options' => [
						'--force' => 'Remove even if existing boards reference the source.',
					],
					'examples' => [
						'source:remove 3.3.17',
					],
				],
				'source:prune' => [
					'title' => 'source:prune',
					'usage' => 'source:prune',
					'summary' => 'Delete unused sources.',
					'description' => 'Removes all downloaded sources that are not referenced by existing boards.',
					'examples' => [
						'source:prune',
					],
				],
				'phpbb:list' => [
					'title' => 'phpbb:list',
					'usage' => 'phpbb:list',
					'summary' => 'Show supported phpBB selectors.',
					'description' => 'Lists supported phpBB version selectors, PHP compatibility, and resolution notes.',
					'examples' => [
						'phpbb:list',
					],
				],
			],
			'Workspace commands' => [
				'init' => [
					'title' => 'init',
					'usage' => 'init',
					'summary' => 'Create the .qi workspace folders.',
					'description' => 'Initializes QuickInstall local state folders. Other commands also initialize the workspace when needed.',
					'examples' => [
						'init',
					],
				],
			],
		];
	}
}
