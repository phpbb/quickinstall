<?php

namespace QuickInstall\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\Project;
use QuickInstall\Sandbox\UiServerService;
use QuickInstall\Tests\Support\TempProjectTrait;

class UiServerServiceTest extends TestCase
{
	use TempProjectTrait;

	public function testUiServerLifecycle(): void
	{
		$socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
		self::assertIsResource($socket, $error);
		$address = stream_socket_get_name($socket, false);
		fclose($socket);
		$port = (int) substr((string) strrchr((string) $address, ':'), 1);

		$project = new Project($this->createTempProjectRoot());
		$service = new UiServerService($project);
		try
		{
			$started = $service->start('127.0.0.1', $port);
			self::assertSame('started', $started['status']);
			self::assertSame('running', $service->status()['status']);
		}
		finally
		{
			$stopped = $service->stop();
		}

		self::assertSame('stopped', $stopped['status']);
		self::assertSame('not_running', $service->status()['status']);
	}

	public function testWindowsProcessArgumentQuotesPathsWithSpaces(): void
	{
		$service = new UiServerService(new Project($this->createTempProjectRoot()));
		$method = new \ReflectionMethod(UiServerService::class, 'quoteWindowsProcessArgument');
		$method->setAccessible(true);

		self::assertSame('"C:\\Program Files\\QuickInstall\\public\\sandbox-ui.php"', $method->invoke($service, 'C:\\Program Files\\QuickInstall\\public\\sandbox-ui.php'));
	}

	public function testResetLogTruncatesExistingUiLog(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		$logPath = $project->workspacePath('runtime/ui.log');
		file_put_contents($logPath, "old server output\n");

		$service = new UiServerService($project);
		$method = new \ReflectionMethod(UiServerService::class, 'resetLog');
		$method->setAccessible(true);
		$method->invoke($service, $logPath);

		self::assertSame('', file_get_contents($logPath));
	}
}
