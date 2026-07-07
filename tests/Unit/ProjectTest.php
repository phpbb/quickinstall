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

	public function testCopyTreeCopiesNestedFilesAndRemoveEmptyParentsPrunesOnlyToStop(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		$source = $root . '/source';
		$target = $project->workspacePath('boards/demo/ext/vendor/package');
		mkdir($source . '/nested', 0775, true);
		file_put_contents($source . '/nested/file.txt', 'copied');

		$project->copyTree($source, $target);

		self::assertSame('copied', file_get_contents($target . '/nested/file.txt'));

		$project->deleteTree($target);
		$project->removeEmptyParents(dirname($target), $project->workspacePath('boards/demo/ext'));

		self::assertDirectoryDoesNotExist($project->workspacePath('boards/demo/ext/vendor'));
		self::assertDirectoryExists($project->workspacePath('boards/demo/ext'));
	}

	public function testCopyTreeRejectsExistingTarget(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		$source = $root . '/source';
		$target = $project->workspacePath('target');
		mkdir($source);
		mkdir($target);

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Copy target already exists');

		$project->copyTree($source, $target);
	}
}
