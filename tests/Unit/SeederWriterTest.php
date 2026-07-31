<?php

namespace QuickInstall\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\Project;
use QuickInstall\Sandbox\SeederWriter;
use QuickInstall\Tests\Support\TempProjectTrait;

class SeederWriterTest extends TestCase
{
	use TempProjectTrait;

	public function testWritesUnifiedSeederPackageToRuntimeDirectory(): void
	{
		$project = new Project($this->createTempProjectRoot());
		$project->init();
		mkdir($project->runtimePath('demo'), 0775, true);

		$path = (new SeederWriter($project))->write('demo');

		self::assertSame($project->runtimePath('demo') . '/seed-runtime', $path);
		self::assertDirectoryExists($path);
		self::assertFileExists($path . '/run.php');
		self::assertFileExists($path . '/Seeder.php');
		self::assertFileExists($path . '/StandardSeeder.php');
		self::assertFileExists($path . '/DevelopmentSeeder.php');
		self::assertStringContainsString("'tiny' =>", file_get_contents($path . '/StandardSeeder.php'));
		self::assertStringContainsString("'load-test' =>", file_get_contents($path . '/StandardSeeder.php'));
		self::assertStringContainsString('gc_collect_cycles', file_get_contents($path . '/StandardSeeder.php'));
		self::assertStringContainsString('class Seeder', file_get_contents($path . '/Seeder.php'));
	}
}
