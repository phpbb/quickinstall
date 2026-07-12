<?php
/**
 *
 * QuickInstall sandbox requirement diagnostics
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace QuickInstall\Sandbox;

class DoctorService
{
	private Project $project;
	private ProcessRunner $runner;

	public function __construct(Project $project, ?ProcessRunner $runner = null)
	{
		$this->project = $project;
		$this->runner = $runner ?: new ProcessRunner(new BufferedOutput());
	}

	public function checks(): array
	{
		$checks = [
			$this->check('PHP 8+', PHP_VERSION_ID >= 80000, PHP_VERSION),
			$this->check('JSON extension', extension_loaded('json'), extension_loaded('json') ? 'loaded' : 'missing'),
			$this->check('Process execution', function_exists('proc_open'), function_exists('proc_open') ? 'available' : 'proc_open is disabled'),
		];

		$docker = $this->capture(['docker', 'version', '--format', '{{.Server.Version}}']);
		$checks[] = $this->check('Docker daemon', $docker['exit_code'] === 0, $this->detail($docker, 'not reachable'));

		$compose = $this->capture(['docker', 'compose', 'version', '--short']);
		$checks[] = $this->check('Docker Compose', $compose['exit_code'] === 0, $this->detail($compose, 'not available'));

		if ($docker['exit_code'] === 0)
		{
			$osType = $this->capture(['docker', 'info', '--format', '{{.OSType}}']);
			$isLinux = $osType['exit_code'] === 0 && strtolower(trim($osType['output'])) === 'linux';
			$checks[] = $this->check('Linux containers', $isLinux, $this->detail($osType, 'Docker must use Linux containers'));
		}

		$git = $this->capture(['git', '--version']);
		$checks[] = $this->check('Git', $git['exit_code'] === 0, $this->detail($git, 'not available'));
		$checks[] = $this->check('Composer', $this->composerAvailable(), $this->composerDetail());

		return $checks;
	}

	private function capture(array $command): array
	{
		try
		{
			return $this->runner->capture($command, $this->project->rootPath());
		}
		catch (\Throwable $e)
		{
			return ['exit_code' => 1, 'output' => $e->getMessage()];
		}
	}

	private function composerAvailable(): bool
	{
		if (is_file($this->project->rootPath('composer.phar')))
		{
			return true;
		}
		return $this->capture(['composer', '--version'])['exit_code'] === 0;
	}

	private function composerDetail(): string
	{
		if (is_file($this->project->rootPath('composer.phar')))
		{
			return 'bundled composer.phar';
		}
		$result = $this->capture(['composer', '--version']);
		return $result['exit_code'] === 0 ? $this->detail($result, 'from PATH') : $this->detail($result, 'not available');
	}

	private function check(string $name, bool $ok, string $detail): array
	{
		return ['name' => $name, 'ok' => $ok, 'detail' => trim($detail)];
	}

	private function detail(array $result, string $fallback): string
	{
		$output = trim((string) ($result['output'] ?? ''));
		if ($output === '')
		{
			return $fallback;
		}
		$lines = preg_split('/\R/', $output) ?: [];
		return trim((string) end($lines));
	}
}
