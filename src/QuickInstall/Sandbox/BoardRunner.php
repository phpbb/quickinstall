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

class BoardRunner
{
	private Project $project;

	public function __construct(Project $project)
	{
		$this->project = $project;
	}

	public function start(string $name): void
	{
		$board = $this->project->board($name);
		$this->run(['docker', 'compose', '-f', $this->project->composePath($name), 'up', '--build', '-d', '--force-recreate', '--remove-orphans', 'web']);
		$this->waitUntilInstalled($name);
		if (($board['db'] ?? '') === 'sqlite' && ($board['populate'] ?? 'none') !== 'none')
		{
			throw new RuntimeException('SQLite boards currently support populate:none only. Use mariadb, mysql, or postgres for seeded boards.');
		}
		$this->seedIfNeeded($name, $board['populate'] ?? 'none');
		$this->waitUntilHttpReady($name, $board['url'] ?? '');
	}

	public function stop(string $name): void
	{
		$this->project->board($name);
		$this->run(['docker', 'compose', '-f', $this->project->composePath($name), 'stop']);
	}

	public function destroy(string $name): void
	{
		$this->project->board($name);
		$compose = $this->project->composePath($name);
		if (file_exists($compose))
		{
			$this->run(['docker', 'compose', '-f', $compose, 'down', '--volumes', '--remove-orphans', '--rmi', 'local']);
		}

		$this->project->deleteTree($this->project->boardPath($name));
		$this->project->deleteTree($this->project->runtimePath($name));
		$this->project->deleteTree($this->project->dbPath($name));
		$this->project->removeBoard($name);
	}

	public function status(string $name): string
	{
		$compose = $this->project->composePath($name);
		if (!file_exists($compose))
		{
			return 'missing';
		}

		$all = $this->capture(['docker', 'compose', '-f', $compose, 'ps', '-a', '--services']);
		if ($all['exit_code'] !== 0)
		{
			return 'error';
		}

		$services = $this->lines($all['output']);
		if (!$services)
		{
			return 'stopped';
		}

		$running = $this->capture(['docker', 'compose', '-f', $compose, 'ps', '--services', '--filter', 'status=running']);
		if ($running['exit_code'] !== 0)
		{
			return 'error';
		}

		$runningServices = $this->lines($running['output']);

		if (count($runningServices) === count($services))
		{
			return 'running';
		}

		return $runningServices ? 'partial' : 'stopped';
	}

	public function seed(string $name, string $preset, int $seed, string $action = 'seed'): void
	{
		$this->project->board($name);
		if (!in_array($action, ['seed', 'reset', 'replace'], true))
		{
			throw new InvalidArgumentException("Unknown seed action: $action");
		}

		if ($action === 'reset' || $action === 'replace')
		{
			$this->deleteSeedMarker($name, $preset);
		}

		$this->runSeeder($name, $preset, $seed, $action);

		if ($action === 'replace')
		{
			$this->writeSeedMarker($name, $preset);
		}
	}

	public function purgeCache(string $name): void
	{
		$this->project->board($name);
		$this->run(['docker', 'compose', '-f', $this->project->composePath($name), 'exec', '-T', 'web', 'sh', '-lc', 'php bin/phpbbcli.php cache:purge']);
	}

	public function recreateWeb(string $name): void
	{
		$this->project->board($name);
		$this->run(['docker', 'compose', '-f', $this->project->composePath($name), 'up', '-d', '--force-recreate', 'web']);
	}

	private function seedIfNeeded(string $name, string $preset): void
	{
		if ($preset === 'none' || $preset === '')
		{
			return;
		}

		$marker = $this->seedMarker($name, $preset);
		if (file_exists($marker))
		{
			echo "Populate preset already applied: $preset\n";
			return;
		}

		$this->runSeeder($name, $preset, 1);
		$this->writeSeedMarker($name, $preset);
	}

	private function waitUntilInstalled(string $name): void
	{
		$boardPath = $this->project->boardPath($name);
		$deadline = time() + 120;

		while (time() <= $deadline)
		{
			$config = $boardPath . '/config.php';
			if (file_exists($boardPath . '/includes/startup.php') && file_exists($config) && filesize($config) > 0 && !is_dir($boardPath . '/install'))
			{
				return;
			}

			$state = $this->serviceState($name, 'web');
			if (in_array($state, ['exited', 'dead'], true))
			{
				throw new RuntimeException("Web container exited before phpBB install completed for board: $name. Run: docker compose -f " . $this->project->composePath($name) . " logs web");
			}

			usleep(500000);
		}

		throw new RuntimeException("Timed out waiting for phpBB install to complete for board: $name");
	}

	private function waitUntilHttpReady(string $name, string $url): void
	{
		if ($url === '')
		{
			return;
		}

		echo "Waiting for board URL: $url\n";
		$deadline = time() + 30;
		while (time() <= $deadline)
		{
			$status = $this->httpStatus($url);
			if ($status >= 200 && $status < 500)
			{
				return;
			}

			usleep(500000);
		}

		echo "Warning: board container started, but $url was not reachable from the host after 30 seconds.\n";
		echo "Try opening it again in a few seconds, or run: docker compose -f " . $this->project->composePath($name) . " logs web\n";
	}

	private function httpStatus(string $url): int
	{
		$context = stream_context_create([
			'http' => [
				'method' => 'GET',
				'timeout' => 2,
				'ignore_errors' => true,
			],
		]);

		$headers = @get_headers($url, false, $context);
		if (!is_array($headers) || !isset($headers[0]))
		{
			return 0;
		}

		return preg_match('/\s(\d{3})\s/', $headers[0], $matches) ? (int) $matches[1] : 0;
	}

	private function serviceState(string $name, string $service): string
	{
		$result = $this->capture(['docker', 'compose', '-f', $this->project->composePath($name), 'ps', $service, '--format', 'json']);
		if ($result['exit_code'] !== 0 || trim($result['output']) === '')
		{
			return '';
		}

		$data = json_decode(trim($result['output']), true);
		if (isset($data[0]) && is_array($data[0]))
		{
			$data = $data[0];
		}

		return strtolower((string) ($data['State'] ?? ''));
	}

	private function runSeeder(string $name, string $preset, int $seed, string $action = 'seed'): void
	{
		$writer = new SeederWriter($this->project);
		$script = $writer->write($name);

		$this->run(['docker', 'compose', '-f', $this->project->composePath($name), 'cp', $script, 'web:/tmp/qi_seed.php']);
		$this->run(['docker', 'compose', '-f', $this->project->composePath($name), 'exec', '-T', 'web', 'timeout', '300', 'php', '/tmp/qi_seed.php', $preset, (string) $seed, $action]);
	}

	private function seedMarker(string $name, string $preset): string
	{
		return $this->project->runtimePath($name) . '/seeded-' . preg_replace('/[^A-Za-z0-9._-]/', '_', $preset);
	}

	private function deleteSeedMarker(string $name, string $preset): void
	{
		$marker = $this->seedMarker($name, $preset);
		if (file_exists($marker))
		{
			unlink($marker);
		}
	}

	private function writeSeedMarker(string $name, string $preset): void
	{
		file_put_contents($this->seedMarker($name, $preset), gmdate('c') . "\n");
	}

	private function run(array $command): void
	{
		echo '$ ' . implode(' ', array_map('escapeshellarg', $command)) . "\n";

		$descriptor = [
			0 => ['file', '/dev/null', 'r'],
			1 => defined('STDOUT') ? constant('STDOUT') : ['file', 'php://output', 'w'],
			2 => defined('STDERR') ? constant('STDERR') : ['file', 'php://stderr', 'w'],
		];

		$process = proc_open($command, $descriptor, $pipes);
		if (!is_resource($process))
		{
			throw new RuntimeException('Unable to start command: ' . $command[0]);
		}

		$status = proc_close($process);
		if ($status !== 0)
		{
			throw new RuntimeException("Command failed with exit code $status: {$command[0]}" . $this->commandHint($command, $status));
		}
	}

	private function capture(array $command): array
	{
		$descriptor = [
			0 => ['file', '/dev/null', 'r'],
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		];

		$process = proc_open($command, $descriptor, $pipes);
		if (!is_resource($process))
		{
			return ['exit_code' => 1, 'output' => ''];
		}

		$output = stream_get_contents($pipes[1]) ?: '';
		$error = stream_get_contents($pipes[2]) ?: '';
		fclose($pipes[1]);
		fclose($pipes[2]);

		return [
			'exit_code' => proc_close($process),
			'output' => $output . $error,
		];
	}

	private function lines(string $output): array
	{
		return array_values(array_filter(array_map('trim', explode("\n", $output)), static function ($line) {
			return $line !== '';
		}));
	}

	private function commandHint(array $command, int $status): string
	{
		if (in_array('timeout', $command, true) && $status === 124)
		{
			return "\nThe operation timed out. For seeding, try a smaller preset or use mariadb/mysql/postgres instead of sqlite.";
		}

		if (($command[0] ?? '') === 'docker')
		{
			return "\nCheck that Docker Desktop is running and that the docker command works in this terminal.";
		}

		return '';
	}
}
