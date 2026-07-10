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

	public function __construct(?Output $output = null)
	{
		$this->output = $output ?: new BufferedOutput();
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
		foreach ([1, 2] as $index)
		{
			stream_set_blocking($pipes[$index], false);
		}

		$output = '';
		$open = [1 => true, 2 => true];
		while ($open[1] || $open[2])
		{
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

				if (feof($pipes[$index]))
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

		return [
			'exit_code' => proc_close($process),
			'output' => $output,
		];
	}

	private function failureDetails(array $command, array $result, bool $includeOutputSummary = true): string
	{
		$details = $includeOutputSummary ? $this->outputSummary((string) ($result['output'] ?? '')) : '';
		$hint = $this->commandHint($command, (int) $result['exit_code'], (string) ($result['output'] ?? ''));

		return $details . $hint;
	}

	private function runWithStreamOutput(array $command, ?string $cwd): array
	{
		$descriptor = [
			0 => ['file', '/dev/null', 'r'],
			1 => $this->output->stdout(),
			2 => $this->output->stderr(),
		];

		$process = proc_open($command, $descriptor, $pipes, $cwd);
		if (!is_resource($process))
		{
			return ['exit_code' => 1, 'output' => ''];
		}

		return [
			'exit_code' => proc_close($process),
			'output' => '',
		];
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
