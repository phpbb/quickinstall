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

/** Reports host requirements without changing project or system state. */
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
		$iniPath = php_ini_loaded_file();
		$projectWritable = $this->projectWritable();
		$checks = [
			$this->check('PHP 8+', PHP_VERSION_ID >= 80000, PHP_VERSION),
			$this->check('PHP CLI', in_array(PHP_SAPI, ['cli', 'cli-server'], true), PHP_SAPI),
			$this->check('PHP configuration', true, $iniPath !== false ? $iniPath : 'no php.ini loaded; using built-in defaults'),
			$this->extensionCheck('JSON', 'json'),
			$this->extensionCheck('OpenSSL', 'openssl', 'extension=openssl'),
			$this->extensionCheck('Phar', 'phar'),
			$this->extensionCheck('Filter', 'filter'),
			$this->extensionCheck('Hash', 'hash'),
			$this->extensionCheck('ZIP', 'zip', 'extension=zip'),
			$this->check(
				'URL streams',
				filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN),
				filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN) ? 'allow_url_fopen enabled' : 'enable allow_url_fopen=On in php.ini'
			),
			$this->check('Process execution', is_callable('proc_open'), is_callable('proc_open') ? 'available' : 'proc_open is disabled'),
			$this->check('Network sockets', is_callable('fsockopen'), is_callable('fsockopen') ? 'available' : 'fsockopen is disabled'),
			$this->check('Project writable', $projectWritable, $projectWritable ? $this->project->rootPath() : 'QuickInstall project directory is not writable'),
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
		$composerPhar = $this->project->rootPath('composer.phar');
		if (is_file($composerPhar))
		{
			$composer = $this->capture([PHP_BINARY, $composerPhar, '--version', '--no-ansi']);
			$checks[] = $this->check(
				'Composer',
				$composer['exit_code'] === 0,
				$composer['exit_code'] === 0 ? 'bundled ' . $this->firstDetail($composer, 'composer.phar') : $this->detail($composer, 'bundled composer.phar could not run')
			);
		}
		else
		{
			$composer = $this->capture(['composer', '--version']);
			$checks[] = $this->check('Composer', $composer['exit_code'] === 0, $this->detail($composer, 'not available'));
		}

		return $checks;
	}

	private function extensionCheck(string $label, string $extension, ?string $setting = null): array
	{
		$loaded = extension_loaded($extension);
		$detail = 'loaded';
		if (!$loaded)
		{
			$detail = "missing PHP $extension extension";
			if ($setting !== null)
			{
				$iniPath = php_ini_loaded_file();
				$detail .= '; enable ' . $setting . ' in ' . ($iniPath !== false ? $iniPath : 'php.ini');
			}
		}

		return $this->check("$label extension", $loaded, $detail);
	}

	private function projectWritable(): bool
	{
		$path = is_dir($this->project->workspacePath()) ? $this->project->workspacePath() : $this->project->rootPath();
		return is_dir($path) && is_writable($path);
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

	private function firstDetail(array $result, string $fallback): string
	{
		$output = trim((string) ($result['output'] ?? ''));
		if ($output === '')
		{
			return $fallback;
		}
		$lines = preg_split('/\R/', $output) ?: [];
		return trim((string) reset($lines));
	}
}
