<?php
/**
 *
 * QuickInstall sandbox process runner
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace QuickInstall\Sandbox;

use RuntimeException;

class ProcessRunner
{
	private Output $output;
	private string $osFamily;

	public function __construct(?Output $output = null, ?string $osFamily = null)
	{
		$this->output = $output ?: new BufferedOutput();
		$this->osFamily = $osFamily ?: PHP_OS_FAMILY;
	}

	public function run(array $command, ?string $cwd = null): void
	{
		$this->output->write('$ ' . implode(' ', array_map('escapeshellarg', $command)) . "\n");
		$streamOutput = $this->output instanceof StreamOutput;
		$result = $streamOutput ? $this->runWithStreamOutput($command, $cwd) : $this->execute($command, true, $cwd);
		if ($result['exit_code'] !== 0)
		{
			throw new RuntimeException("Command failed with exit code {$result['exit_code']}: {$command[0]}" . $this->failureDetails($command, $result, !$streamOutput));
		}
	}

	public function capture(array $command, ?string $cwd = null): array
	{
		return $this->execute($command, false, $cwd);
	}

	private function execute(array $command, bool $stream, ?string $cwd): array
	{
		$command = $this->platformCommand($command);
		if ($this->osFamily === 'Windows')
		{
			return $this->executeWithFiles($command, $stream, $cwd);
		}

		$descriptor = [
			0 => ['pipe', 'r'],
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		];

		$process = @proc_open($command, $descriptor, $pipes, $cwd);
		if (!is_resource($process))
		{
			return ['exit_code' => 1, 'output' => ''];
		}

		fclose($pipes[0]);
		foreach ([1, 2] as $index)
		{
			stream_set_blocking($pipes[$index], false);
		}

		$output = '';
		$open = [1 => true, 2 => true];
		$processExitCode = null;
		while ($open[1] || $open[2])
		{
			$status = proc_get_status($process);
			$processRunning = (bool) ($status['running'] ?? false);
			if (!$processRunning && isset($status['exitcode']) && $status['exitcode'] >= 0)
			{
				$processExitCode = (int) $status['exitcode'];
			}

			foreach ([1, 2] as $index)
			{
				if (!$open[$index])
				{
					continue;
				}

				$chunk = stream_get_contents($pipes[$index]);
				if ($chunk !== false && $chunk !== '')
				{
					$output .= $chunk;
					if ($stream)
					{
						$index === 1 ? $this->output->write($chunk) : $this->output->error($chunk);
					}
				}

				// Windows anonymous pipes do not always report EOF promptly after the
				// child exits. Once proc_get_status confirms exit, the final read above
				// has drained the pipe and it is safe to close it.
				if (feof($pipes[$index]) || !$processRunning)
				{
					fclose($pipes[$index]);
					$open[$index] = false;
				}
			}

			if ($open[1] || $open[2])
			{
				usleep(10000);
			}
		}

		$closeExitCode = proc_close($process);
		return [
			'exit_code' => $processExitCode ?? $closeExitCode,
			'output' => $output,
		];
	}

	private function executeWithFiles(array $command, bool $stream, ?string $cwd): array
	{
		$stdoutPath = tempnam(sys_get_temp_dir(), 'qi-process-out-');
		$stderrPath = tempnam(sys_get_temp_dir(), 'qi-process-err-');
		if ($stdoutPath === false || $stderrPath === false)
		{
			if ($stdoutPath !== false)
			{
				@unlink($stdoutPath);
			}
			if ($stderrPath !== false)
			{
				@unlink($stderrPath);
			}
			return ['exit_code' => 1, 'output' => 'Unable to create temporary process output files.'];
		}

		$descriptor = [
			0 => ['file', $this->nullDevice(), 'r'],
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
		$stdout = (string) @file_get_contents($stdoutPath);
		$stderr = (string) @file_get_contents($stderrPath);
		@unlink($stdoutPath);
		@unlink($stderrPath);
		if ($stream)
		{
			$this->output->write($stdout);
			$this->output->error($stderr);
		}

		return ['exit_code' => $exitCode, 'output' => $stdout . $stderr];
	}

	private function failureDetails(array $command, array $result, bool $includeOutputSummary = true): string
	{
		$details = $includeOutputSummary ? $this->outputSummary((string) ($result['output'] ?? '')) : '';
		$hint = $this->commandHint($command, (int) $result['exit_code'], (string) ($result['output'] ?? ''));

		return $details . $hint;
	}

	private function runWithStreamOutput(array $command, ?string $cwd): array
	{
		$command = $this->platformCommand($command);
		$descriptor = [
			0 => ['file', $this->nullDevice(), 'r'],
			1 => $this->output->stdout(),
			2 => $this->output->stderr(),
		];

		$process = @proc_open($command, $descriptor, $pipes, $cwd);
		if (!is_resource($process))
		{
			return ['exit_code' => 1, 'output' => ''];
		}

		return [
			'exit_code' => proc_close($process),
			'output' => '',
		];
	}

	private function platformCommand(array $command): array
	{
		if ($this->osFamily !== 'Windows' || !$command)
		{
			return $command;
		}

		$executable = strtolower((string) $command[0]);
		if (!str_ends_with($executable, '.bat') && !str_ends_with($executable, '.cmd'))
		{
			return $command;
		}

		$commandLine = implode(' ', array_map([$this, 'escapeWindowsBatchArgument'], $command));
		return [getenv('COMSPEC') ?: 'cmd.exe', '/D', '/S', '/C', '"' . $commandLine . '"'];
	}

	private function nullDevice(): string
	{
		return $this->osFamily === 'Windows' && PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
	}

	private function escapeWindowsBatchArgument(string $argument): string
	{
		// cmd.exe expands percent variables even inside quotes. Doubling percent signs
		// preserves URL escapes and other literal arguments passed to batch wrappers.
		$argument = str_replace('%', '%%', $argument);
		$argument = str_replace('"', '\\"', $argument);
		return '"' . $argument . '"';
	}

	private function outputSummary(string $output): string
	{
		$output = trim($output);
		if ($output === '')
		{
			return '';
		}

		$lines = preg_split('/\R/', $output) ?: [];
		$lines = array_slice(array_values(array_filter($lines, static function ($line) {
			return trim($line) !== '';
		})), -8);

		return "\nCommand output:\n" . implode("\n", $lines);
	}

	private function commandHint(array $command, int $status, string $output): string
	{
		if ($status === 124 && in_array('timeout', $command, true))
		{
			return "\nThe operation timed out. For seeding, try a smaller preset or use mariadb/mysql/postgres instead of sqlite.";
		}

		if (($command[0] ?? '') === 'docker' && ($output === '' || preg_match('/Cannot connect to the Docker daemon|docker daemon|Docker Desktop/i', $output)))
		{
			return "\nCheck that Docker Desktop is running and that the docker command works in this terminal.";
		}

		return '';
	}
}
