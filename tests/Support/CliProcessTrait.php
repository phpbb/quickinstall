<?php

namespace QuickInstall\Tests\Support;

trait CliProcessTrait
{
	protected function runCli(array $args): array
	{
		$phpBinary = PHP_BINARY;
		if (PHP_SAPI === 'phpdbg')
		{
			$siblingPhp = dirname(PHP_BINARY) . '/php';
			if (is_file($siblingPhp) && is_executable($siblingPhp))
			{
				$phpBinary = $siblingPhp;
			}
		}

		$command = array_merge([$phpBinary, dirname(__DIR__, 2) . '/bin/qi'], $args);
		$descriptorSpec = [
			0 => ['pipe', 'r'],
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		];

		$process = proc_open($command, $descriptorSpec, $pipes, dirname(__DIR__, 2));
		if (!is_resource($process))
		{
			throw new \RuntimeException('Unable to start CLI process.');
		}

		fclose($pipes[0]);
		$stdout = stream_get_contents($pipes[1]);
		$stderr = stream_get_contents($pipes[2]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		$exitCode = proc_close($process);

		return [
			'exit_code' => $exitCode,
			'stdout' => $stdout,
			'stderr' => $stderr,
		];
	}
}
