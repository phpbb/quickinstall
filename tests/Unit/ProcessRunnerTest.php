<?php

namespace QuickInstall\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\BufferedOutput;
use QuickInstall\Sandbox\ProcessRunner;
use QuickInstall\Sandbox\StreamOutput;
use RuntimeException;

class ProcessRunnerTest extends TestCase
{
	public function testFailureIncludesCommandOutput(): void
	{
		$runner = new ProcessRunner(new BufferedOutput());

		try
		{
			$runner->run([PHP_BINARY, '-r', 'fwrite(STDERR, "Fatal error: Allowed memory size exhausted\n"); exit(255);']);
			self::fail('Expected command failure.');
		}
		catch (RuntimeException $e)
		{
			self::assertStringContainsString('Command failed with exit code 255', $e->getMessage());
			self::assertStringContainsString('Fatal error: Allowed memory size exhausted', $e->getMessage());
		}
	}

	public function testDockerHintIsSkippedWhenCommandOutputExplainsFailure(): void
	{
		$runner = new ProcessRunner(new BufferedOutput());
		$method = new \ReflectionMethod(ProcessRunner::class, 'failureDetails');
		$method->setAccessible(true);

		$message = $method->invoke($runner, ['docker', 'compose', 'exec', 'web', 'php'], [
			'exit_code' => 255,
			'output' => "Fatal error: Allowed memory size exhausted\n",
		]);

		self::assertStringContainsString('Fatal error: Allowed memory size exhausted', $message);
		self::assertStringNotContainsString('Check that Docker Desktop is running', $message);
	}

	public function testStreamOutputDoesNotDuplicateCommandOutputInException(): void
	{
		$stdout = fopen('php://temp', 'w+');
		$stderr = fopen('php://temp', 'w+');
		$runner = new ProcessRunner(new StreamOutput($stdout, $stderr));

		try
		{
			$runner->run([PHP_BINARY, '-r', 'fwrite(STDERR, "Fatal error: bad thing\n"); exit(255);']);
			self::fail('Expected command failure.');
		}
		catch (RuntimeException $e)
		{
			self::assertStringContainsString('Command failed with exit code 255', $e->getMessage());
			self::assertStringNotContainsString('Fatal error: bad thing', $e->getMessage());
		}

		rewind($stderr);
		self::assertStringContainsString('Fatal error: bad thing', stream_get_contents($stderr));
	}

	public function testDockerHintRemainsForDockerConnectivityFailure(): void
	{
		$runner = new ProcessRunner(new BufferedOutput());
		$method = new \ReflectionMethod(ProcessRunner::class, 'failureDetails');
		$method->setAccessible(true);

		$message = $method->invoke($runner, ['docker', 'ps'], [
			'exit_code' => 1,
			'output' => 'Cannot connect to the Docker daemon',
		]);

		self::assertStringContainsString('Cannot connect to the Docker daemon', $message);
		self::assertStringContainsString('Check that Docker Desktop is running', $message);
	}
}
