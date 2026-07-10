<?php

namespace QuickInstall\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\Project;
use QuickInstall\Sandbox\UiServerService;
use QuickInstall\Tests\Support\TempProjectTrait;

class UiServerServiceTest extends TestCase
{
	use TempProjectTrait;

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
