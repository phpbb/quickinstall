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
	private Output $output;
	private ProcessRunner $processRunner;

	public function __construct(Project $project, ?Output $output = null, ?ProcessRunner $processRunner = null)
	{
		$this->project = $project;
		$this->output = $output ?: new BufferedOutput();
		$this->processRunner = $processRunner ?: new ProcessRunner($this->output);
	}

	public function start(string $name): void
	{
		$board = $this->project->board($name);
		$this->run(['docker', 'compose', '-f', $this->project->composePath($name), 'up', '--build', '-d', '--force-recreate', '--remove-orphans', 'web']);
		$this->output->write("Waiting for phpBB install to finish...\n");
		$this->waitUntilInstalled($name);
		if (!empty($board['debug']))
		{
			$this->output->write("Enabling phpBB debug config...\n");
			$this->enableDebug($name);
		}
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

	protected function seedIfNeeded(string $name, string $preset): void
	{
		if ($preset === 'none' || $preset === '')
		{
			return;
		}

		$marker = $this->seedMarker($name, $preset);
		if (file_exists($marker))
		{
			$this->output->write("Populate preset already applied: $preset\n");
			return;
		}

		$this->runSeeder($name, $preset, 1);
		$this->writeSeedMarker($name, $preset);
	}

	protected function enableDebug(string $name): void
	{
		$board = $this->project->board($name);
		$branch = (string) ($board['phpbb_branch'] ?? '');
		$boardPath = $this->project->boardPath($name);

		if (in_array($branch, ['3.3', '4.0'], true))
		{
			$this->enableYamlDebug($boardPath);
			return;
		}

		if ($branch === '3.2')
		{
			$this->enableConfigPhpDebug($boardPath, true);
		}
	}

	protected function enableConfigPhpDebug(string $boardPath, bool $displayLoadTime): void
	{
		$configPath = $boardPath . '/config.php';
		if (!is_file($configPath))
		{
			return;
		}

		$contents = file_get_contents($configPath);
		if ($contents === false)
		{
			throw new RuntimeException("Unable to read board config: $configPath");
		}

		$contents = $this->setConfigDefine($contents, 'PHPBB_ENVIRONMENT', "@define('PHPBB_ENVIRONMENT', 'production');");
		$contents = $this->setConfigDefine($contents, 'DEBUG_CONTAINER', "// @define('DEBUG_CONTAINER', true);");

		if ($displayLoadTime)
		{
			$contents = $this->setConfigDefine($contents, 'PHPBB_DISPLAY_LOAD_TIME', "@define('PHPBB_DISPLAY_LOAD_TIME', true);");
		}

		file_put_contents($configPath, $contents);
	}

	protected function enableYamlDebug(string $boardPath): void
	{
		$configPath = $boardPath . '/config/production/config.yml';
		if (!is_file($configPath))
		{
			return;
		}

		$contents = file_get_contents($configPath);
		if ($contents === false)
		{
			throw new RuntimeException("Unable to read board config: $configPath");
		}

		if (str_contains($contents, 'debug.load_time:'))
		{
			return;
		}

		$debugConfig = [
			'debug.load_time' => 'true',
			'debug.memory' => 'true',
			'debug.sql_explain' => 'true',
			'debug.show_errors' => 'true',
			'debug.exceptions' => 'true',
			'twig.debug' => 'false',
			'twig.auto_reload' => 'false',
			'twig.enable_debug_extension' => 'false',
		];

		$lines = [];
		foreach ($debugConfig as $key => $value)
		{
			$lines[] = "    $key: $value";
		}

		if (preg_match('/^parameters:\s*$/m', $contents))
		{
			$contents = preg_replace('/^parameters:\s*$/m', "parameters:\n" . implode("\n", $lines), $contents, 1) ?? $contents;
		}
		else
		{
			$contents = rtrim($contents) . "\n\nparameters:\n" . implode("\n", $lines) . "\n";
		}

		file_put_contents($configPath, $contents);
	}

	protected function setConfigDefine(string $contents, string $name, string $line): string
	{
		$pattern = "/^[ \\t]*(?:\\/\\/\\s*)?@define\\('" . preg_quote($name, '/') . "'[^\\n]*;\\s*$/m";
		if (preg_match($pattern, $contents))
		{
			return preg_replace($pattern, $line, $contents, 1) ?? $contents;
		}

		$insert = "\n$line\n";
		if (preg_match('/\?>\s*$/', $contents))
		{
			return preg_replace('/\s*\?>\s*$/', $insert . "?>\n", $contents, 1) ?? $contents;
		}

		return rtrim($contents) . $insert;
	}

	protected function waitUntilInstalled(string $name): void
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

	protected function waitUntilHttpReady(string $name, string $url): void
	{
		if ($url === '')
		{
			return;
		}

		$this->output->write("Waiting for board URL...\n");
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

		$this->output->write("Warning: board container started, but $url was not reachable from the host after 30 seconds.\n");
		$this->output->write("Try opening it again in a few seconds, or run: docker compose -f " . $this->project->composePath($name) . " logs web\n");
	}

	protected function httpStatus(string $url): int
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

	protected function serviceState(string $name, string $service): string
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

	protected function runSeeder(string $name, string $preset, int $seed, string $action = 'seed'): void
	{
		$writer = new SeederWriter($this->project);
		$script = $writer->write($name);

		$this->output->write("Running seed preset: $preset\n");
		$this->run(['docker', 'compose', '-f', $this->project->composePath($name), 'cp', $script, 'web:/tmp/qi_seed.php']);
		$this->run(['docker', 'compose', '-f', $this->project->composePath($name), 'exec', '-T', 'web', 'timeout', '300', 'php', '/tmp/qi_seed.php', $preset, (string) $seed, $action]);
	}

	protected function seedMarker(string $name, string $preset): string
	{
		return $this->project->runtimePath($name) . '/seeded-' . preg_replace('/[^A-Za-z0-9._-]/', '_', $preset);
	}

	protected function deleteSeedMarker(string $name, string $preset): void
	{
		$marker = $this->seedMarker($name, $preset);
		if (file_exists($marker))
		{
			unlink($marker);
		}
	}

	protected function writeSeedMarker(string $name, string $preset): void
	{
		file_put_contents($this->seedMarker($name, $preset), gmdate('c') . "\n");
	}

	protected function run(array $command): void
	{
		$this->processRunner->run($command);
	}

	protected function capture(array $command): array
	{
		return $this->processRunner->capture($command);
	}

	protected function lines(string $output): array
	{
		return array_values(array_filter(array_map('trim', explode("\n", $output)), static function ($line) {
			return $line !== '';
		}));
	}

}
