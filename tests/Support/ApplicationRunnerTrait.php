<?php

namespace QuickInstall\Tests\Support;

use QuickInstall\Sandbox\Application;

trait ApplicationRunnerTrait
{
	protected function runApplication(string $root, array $argv, ?string $stdinInput = null): array
	{
		$stderr = fopen('php://temp', 'w+');
		$stdin = null;
		if ($stdinInput !== null)
		{
			$stdin = fopen('php://temp', 'r+');
			fwrite($stdin, $stdinInput);
			rewind($stdin);
		}

		ob_start();
		$exitCode = (new Application($root, $stderr, $stdin))->run($argv);
		$output = ob_get_clean();
		rewind($stderr);
		$errorOutput = stream_get_contents($stderr);
		fclose($stderr);
		if (is_resource($stdin))
		{
			fclose($stdin);
		}

		return [
			'exit_code' => $exitCode,
			'output' => $output,
			'stderr' => $errorOutput,
		];
	}
}
