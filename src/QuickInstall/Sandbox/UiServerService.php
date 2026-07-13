<?php
/**
 *
 * QuickInstall sandbox UI server service
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace QuickInstall\Sandbox;

use RuntimeException;

class UiServerService
{
	private Project $project;
	private ProcessRunner $processRunner;

	public function __construct(Project $project, ?ProcessRunner $processRunner = null)
	{
		$this->project = $project;
		$this->processRunner = $processRunner ?: new ProcessRunner(new BufferedOutput());
	}

	public function start(string $host, int $port): array
	{
		$state = $this->readState();
		if ($this->isStateRunning($state))
		{
			return ['status' => 'already_running', 'state' => $state];
		}
		if ($this->isPortOpen($host, $port))
		{
			throw new RuntimeException("Port $port is already in use on $host. Run qi ui:status for tracked UI details, or choose another port with --port.");
		}

		$this->project->init();
		$router = dirname(__DIR__, 3) . '/public/sandbox-ui.php';
		$url = "http://{$host}:{$port}/";
		$command = [PHP_BINARY, '-S', $host . ':' . $port, $router];
		$logPath = $this->logPath();
		$this->resetLog($logPath);
		$pid = $this->startDetachedProcess($command, dirname(__DIR__, 3), $logPath);
		$state = [
			'pid' => $pid,
			'host' => $host,
			'port' => $port,
			'url' => $url,
			'log' => $logPath,
			'error_log' => PHP_OS_FAMILY === 'Windows' ? $logPath . '.err' : $logPath,
			'started_at' => gmdate('c'),
		];
		try
		{
			$this->waitForStart($state);
		}
		catch (RuntimeException $e)
		{
			$this->terminateProcess($pid);
			throw $e;
		}
		$this->writeState($state);

		return ['status' => 'started', 'state' => $state];
	}

	public function stop(): array
	{
		$state = $this->readState();
		if (!$state)
		{
			return ['status' => 'not_tracked', 'state' => []];
		}

		$pid = (int) ($state['pid'] ?? 0);
		if ($pid > 0 && $this->terminateProcess($pid))
		{
			$this->waitForStop($state);
			$this->deleteState();
			return ['status' => 'stopped', 'state' => $state];
		}

		$this->deleteState();
		return ['status' => 'not_running', 'state' => $state];
	}

	public function restart(string $host, int $port): array
	{
		$stopped = $this->stop();
		$started = $this->start($host, $port);

		return ['stop' => $stopped, 'start' => $started];
	}

	public function status(): array
	{
		$state = $this->readState();
		if ($this->isStateRunning($state))
		{
			return ['status' => 'running', 'state' => $state];
		}
		if ($state)
		{
			return ['status' => 'stale', 'state' => $state];
		}

		return ['status' => 'not_running', 'state' => []];
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
			. ' && (nohup ' . implode(' ', array_map('escapeshellarg', $command))
			. ' >> ' . escapeshellarg($logPath) . ' 2>&1 < /dev/null & echo $! > ' . escapeshellarg($pidFile) . ')';
		$result = $this->captureProcess(['/bin/sh', '-c', $shellCommand], $cwd);
		$pid = is_file($pidFile) ? (int) trim((string) file_get_contents($pidFile)) : 0;
		if (is_file($pidFile))
		{
			unlink($pidFile);
		}
		if ($result['exit_code'] !== 0 || $pid <= 0)
		{
			throw new RuntimeException('Unable to start QuickInstall Dashboard UI server.');
		}

		return $pid;
	}

	private function startWindowsDetachedProcess(array $command, string $cwd, string $logPath): int
	{
		$arguments = array_slice($command, 1);
		$arguments = array_map([$this, 'quoteWindowsProcessArgument'], $arguments);
		$argumentList = '@(' . implode(',', array_map([$this, 'powerShellString'], $arguments)) . ')';
		$errorLog = $logPath . '.err';
		$script = '$p = Start-Process -FilePath ' . $this->powerShellString($command[0])
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
			throw new RuntimeException('Unable to start QuickInstall Dashboard UI server. PowerShell is required on Windows.');
		}

		return $pid;
	}

	private function quoteWindowsProcessArgument(string $value): string
	{
		// Start-Process flattens ArgumentList into one command line. Preserve each
		// argument, including router paths containing spaces, with CRT-style quoting.
		$value = preg_replace('/(\\\\*)"/', '$1$1\\"', $value) ?? $value;
		$value = preg_replace('/(\\\\+)$/', '$1$1', $value) ?? $value;
		return '"' . $value . '"';
	}

	private function captureProcess(array $command, string $cwd): array
	{
		$this->project->init();
		$runtimeDir = $this->project->workspacePath('runtime');
		$stdoutPath = tempnam($runtimeDir, 'process-out-');
		$stderrPath = tempnam($runtimeDir, 'process-err-');
		if ($stdoutPath === false || $stderrPath === false)
		{
			throw new RuntimeException('Unable to create temporary UI process output files.');
		}

		$descriptor = [
			0 => ['file', PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null', 'r'],
			1 => ['file', $stdoutPath, 'w'],
			2 => ['file', $stderrPath, 'w'],
		];
		$process = @proc_open($command, $descriptor, $pipes, $cwd);
		if (!is_resource($process))
		{
			@unlink($stdoutPath);
			@unlink($stderrPath);
			return ['exit_code' => 1, 'output' => ''];
		}

		$exitCode = proc_close($process);
		$output = (string) @file_get_contents($stdoutPath) . (string) @file_get_contents($stderrPath);
		@unlink($stdoutPath);
		@unlink($stderrPath);

		return ['exit_code' => $exitCode, 'output' => $output];
	}

	private function powerShellString(string $value): string
	{
		return "'" . str_replace("'", "''", $value) . "'";
	}

	private function readState(): array
	{
		return $this->project->readJson('runtime/ui.json', []);
	}

	private function writeState(array $state): void
	{
		$this->project->writeJson('runtime/ui.json', $state);
	}

	private function deleteState(): void
	{
		$path = $this->project->workspacePath('runtime/ui.json');
		if (is_file($path))
		{
			unlink($path);
		}
	}

	private function logPath(): string
	{
		$this->project->init();
		return $this->project->workspacePath('runtime/ui.log');
	}

	private function resetLog(string $logPath): void
	{
		// Keep only current UI server session output.
		if (file_put_contents($logPath, '') === false)
		{
			throw new RuntimeException("Unable to reset UI log: $logPath");
		}
		$errorLog = $logPath . '.err';
		if (PHP_OS_FAMILY === 'Windows' && file_put_contents($errorLog, '') === false)
		{
			throw new RuntimeException("Unable to reset UI error log: $errorLog");
		}
	}

	private function isStateRunning(array $state): bool
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
			$result = $this->processRunner->capture(['taskkill', '/PID', (string) $pid, '/T', '/F']);
			return $result['exit_code'] === 0;
		}
		if (function_exists('posix_kill') && @posix_kill($pid, 0))
		{
			return @posix_kill($pid, 15);
		}

		$result = $this->processRunner->capture(['kill', (string) $pid]);
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
			$result = $this->processRunner->capture(['tasklist', '/FI', 'PID eq ' . $pid, '/NH']);
			return $result['exit_code'] === 0 && str_contains($result['output'], (string) $pid);
		}
		if (function_exists('posix_kill'))
		{
			return @posix_kill($pid, 0);
		}

		$result = $this->processRunner->capture(['kill', '-0', (string) $pid]);
		return $result['exit_code'] === 0;
	}

	private function waitForStart(array $state): void
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

		$logs = (string) ($state['log'] ?? '(unknown)');
		if (!empty($state['error_log']) && $state['error_log'] !== $state['log'])
		{
			$logs .= ' and ' . $state['error_log'];
		}
		throw new RuntimeException('QuickInstall Dashboard UI server did not become available. Check the Dashboard UI logs for details: ' . $logs);
	}

	private function waitForStop(array $state): void
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
}
