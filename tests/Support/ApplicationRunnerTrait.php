<?php

namespace QuickInstall\Tests\Support;

use QuickInstall\Sandbox\Application;

trait ApplicationRunnerTrait
{
	protected function runApplication(string $root, array $argv): array
	{
		$stderr = fopen('php://temp', 'w+');
		ob_start();
		$exitCode = (new Application($root, $stderr))->run($argv);
		$output = ob_get_clean();
		rewind($stderr);
		$errorOutput = stream_get_contents($stderr);
		fclose($stderr);

		return [
			'exit_code' => $exitCode,
			'output' => $output,
			'stderr' => $errorOutput,
		];
	}
}
