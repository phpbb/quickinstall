<?php

namespace QuickInstall\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\Project;
use QuickInstall\Sandbox\SeederWriter;
use QuickInstall\Tests\Support\TempProjectTrait;

class SeederWriterTest extends TestCase
{
	use TempProjectTrait;

	public function testWritesSeederScriptToRuntimeDirectory(): void
	{
		$project = new Project($this->createTempProjectRoot());
		$project->init();
		mkdir($project->runtimePath('demo'), 0775, true);

		$path = (new SeederWriter($project))->write('demo');

		self::assertSame($project->runtimePath('demo') . '/seed.php', $path);
		self::assertFileExists($path);
		self::assertStringContainsString('$presets = [', file_get_contents($path));
		self::assertStringContainsString('qi_seed_reset', file_get_contents($path));
	}
}
