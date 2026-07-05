<?php

namespace QuickInstall\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\Project;
use QuickInstall\Tests\Support\TempProjectTrait;
use RuntimeException;

class ProjectTest extends TestCase
{
	use TempProjectTrait;

	public function testInitCreatesWorkspaceAndDefaults(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);

		$created = $project->init();

		self::assertContains('customisations drop zone', $created);
		self::assertContains('.qi workspace', $created);
		self::assertDirectoryExists($root . '/customisations');
		self::assertDirectoryExists($root . '/.qi/sources');
		self::assertDirectoryExists($root . '/.qi/boards');
		self::assertSame([], $project->readJson('sources.json', ['unexpected']));
		self::assertSame([], $project->init());
	}

	public function testRejectsInvalidBoardNames(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid board');

		(new Project($this->createTempProjectRoot()))->boardPath('../outside');
	}

	public function testDeleteTreeRefusesPathOutsideWorkspace(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		$outside = $root . '/outside.txt';
		file_put_contents($outside, 'keep');

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Refusing to delete path outside QuickInstall workspace');

		$project->deleteTree($outside);
	}

	public function testResolveDropZonePathGuardsExternalPaths(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		$dropZone = $project->customisationsPath();
		$inside = $dropZone . '/thing';
		mkdir($inside);
		$external = $root . '/external';
		mkdir($external);

		self::assertSame(realpath($inside), $project->resolveDropZonePath('thing', $dropZone, false, 'blocked'));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('blocked');
		$project->resolveDropZonePath($external, $dropZone, false, 'blocked');
	}
}
