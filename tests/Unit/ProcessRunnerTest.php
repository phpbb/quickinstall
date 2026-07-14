<?php

namespace QuickInstall\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\BufferedOutput;
use QuickInstall\Sandbox\ProcessRunner;
use QuickInstall\Sandbox\StreamOutput;
use RuntimeException;

class ProcessRunnerTest extends TestCase
{
	public function testProcessTimeoutReturnsStandardTimeoutStatus(): void
	{
		$runner = new ProcessRunner(new BufferedOutput(), PHP_OS_FAMILY, 0.05);

		$result = $runner->capture([PHP_BINARY, '-r', 'usleep(500000);']);

		self::assertSame(124, $result['exit_code']);
		self::assertStringContainsString('Command timed out after', $result['output']);
	}

	public function testDisplayedCommandRedactsUrlCredentials(): void
	{
		$output = new BufferedOutput();
		$runner = new ProcessRunner($output);

		$runner->run([PHP_BINARY, '-r', '', 'https://token:secret@example.test/repo.git']);

		self::assertStringContainsString('https://***@example.test/repo.git', $output->all());
		self::assertStringNotContainsString('token:secret', $output->all());
	}
	public function testWindowsCaptureUsesFilesWithoutPipeDeadlock(): void
	{
		$runner = new ProcessRunner(new BufferedOutput(), 'Windows');
		$script = 'fwrite(STDOUT, str_repeat("o", 200000)); fwrite(STDERR, str_repeat("e", 200000));';

		$result = $runner->capture([PHP_BINARY, '-r', $script]);

		self::assertSame(0, $result['exit_code']);
		self::assertSame(400000, strlen($result['output']));
		self::assertStringStartsWith('oooo', $result['output']);
		self::assertStringEndsWith('eeee', $result['output']);
	}

	public function testMissingCommandFailsWithoutPhpWarning(): void
	{
		$warnings = [];
		set_error_handler(static function (int $severity, string $message) use (&$warnings): bool {
			if (error_reporting() & $severity)
			{
				$warnings[] = $message;
			}
			return true;
		});
		try
		{
			$result = (new ProcessRunner(new BufferedOutput()))->capture(['quickinstall-command-that-does-not-exist']);
		}
		finally
		{
			restore_error_handler();
		}

		self::assertNotSame(0, $result['exit_code']);
		self::assertSame([], $warnings);
	}

	public function testWindowsBatchCommandsUseCmdExe(): void
	{
		$runner = new ProcessRunner(new BufferedOutput(), 'Windows');
		$method = new \ReflectionMethod(ProcessRunner::class, 'platformCommand');
		$method->setAccessible(true);

		$command = $method->invoke($runner, ['C:\\Program Files\\Composer\\composer.bat', 'show', 'vendor/package']);

		self::assertSame(['/D', '/S', '/C'], array_slice($command, 1, 3));
		self::assertStringEndsWith('cmd.exe', strtolower($command[0]));
		self::assertStringContainsString('"C:\\Program Files\\Composer\\composer.bat"', $command[4]);
		self::assertStringContainsString('"vendor/package"', $command[4]);
	}

	public function testWindowsBatchCommandsPreserveLiteralPercentSigns(): void
	{
		$runner = new ProcessRunner(new BufferedOutput(), 'Windows');
		$method = new \ReflectionMethod(ProcessRunner::class, 'platformCommand');
		$method->setAccessible(true);

		$command = $method->invoke($runner, ['composer.cmd', 'show', 'value%20with%20escapes']);

		self::assertStringContainsString('value%%20with%%20escapes', $command[4]);
	}

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
