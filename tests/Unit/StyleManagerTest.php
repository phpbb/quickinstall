<?php

namespace QuickInstall\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\Project;
use QuickInstall\Sandbox\StyleManager;
use QuickInstall\Tests\Support\TempProjectTrait;

class StyleManagerTest extends TestCase
{
	use TempProjectTrait;

	public function testMountsAndListsBindStyle(): void
	{
		[$project, $root] = $this->projectWithBoard('demo');
		$source = $this->style($root, 'prosilver_child', 'customisations/styles/prosilver_child');

		$mounted = (new StyleManager($project))->mount('demo', $source);

		self::assertSame('prosilver_child', $mounted['name']);
		self::assertSame('bind', $mounted['mode']);
		self::assertSame('/var/www/html/styles/prosilver_child', $mounted['target']);

		$list = (new StyleManager($project))->list('demo');
		self::assertSame('prosilver_child', $list[0]['name']);
		self::assertSame($source, $list[0]['source']);
	}

	public function testCopyMountCopiesFilesIntoBoard(): void
	{
		[$project, $root] = $this->projectWithBoard('demo');
		$source = $this->style($root, 'copied', 'customisations/styles/copied');
		file_put_contents($source . '/template.html', '<html>');

		$mounted = (new StyleManager($project))->mount('demo', $source, true);

		self::assertSame('copy', $mounted['mode']);
		self::assertFileExists($project->boardPath('demo') . '/styles/copied/template.html');
	}

	public function testUnmountRemovesCopiedStyleAndMetadata(): void
	{
		[$project, $root] = $this->projectWithBoard('demo');
		$source = $this->style($root, 'remove', 'customisations/styles/remove');
		file_put_contents($source . '/template.html', '<html>');
		$manager = new StyleManager($project);
		$manager->mount('demo', $source, true);

		$removed = $manager->unmount('demo', 'remove');

		self::assertSame($project->boardPath('demo') . '/styles/remove', $removed);
		self::assertDirectoryDoesNotExist($project->boardPath('demo') . '/styles/remove');
		self::assertSame([], $manager->list('demo'));
	}

	public function testUnmountBindStyleRemovesMetadataOnly(): void
	{
		[$project, $root] = $this->projectWithBoard('demo');
		$source = $this->style($root, 'bound', 'customisations/styles/bound');
		$manager = new StyleManager($project);
		$manager->mount('demo', $source);

		$removed = $manager->unmount('demo', 'bound');

		self::assertSame('/var/www/html/styles/bound', $removed);
		self::assertDirectoryExists($source);
		self::assertSame([], $manager->list('demo'));
	}

	public function testCleanupStaleTargetRemovesStyleDirectoryAndParents(): void
	{
		[$project, $root] = $this->projectWithBoard('demo');
		$source = $this->style($root, 'stale', 'customisations/styles/stale');
		$target = $project->boardPath('demo') . '/styles/stale';
		$project->copyTree($source, $target);

		(new StyleManager($project))->cleanupStaleTarget('demo', 'stale');

		self::assertDirectoryDoesNotExist($target);
	}

	public function testUnmountRejectsUnknownStyle(): void
	{
		[$project] = $this->projectWithBoard('demo');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Style is not mounted: missing');

		(new StyleManager($project))->unmount('demo', 'missing');
	}

	public function testRejectsInvalidStyleName(): void
	{
		[$project, $root] = $this->projectWithBoard('demo');
		$source = $this->style($root, 'bad.name', 'customisations/styles/bad.name');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid style name');

		(new StyleManager($project))->mount('demo', $source);
	}

	public function testRejectsExternalStyleUnlessAllowed(): void
	{
		[$project, $root] = $this->projectWithBoard('demo');
		$source = $this->style($root, 'external', 'external/styles/external');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Style path must be under customisations/');

		(new StyleManager($project))->mount('demo', $source);
	}

	public function testDiscoversNestedStyles(): void
	{
		[$project, $root] = $this->projectWithBoard('demo');
		$one = $this->style($root, 'one', 'customisations/group/one');
		$two = $this->style($root, 'two', 'customisations/group/two');

		self::assertSame([$one, $two], (new StyleManager($project))->discover('group'));
	}

	private function projectWithBoard(string $name): array
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		mkdir($project->boardPath($name), 0775, true);
		$project->appendBoard([
			'name' => $name,
			'phpbb' => '3.3.14',
			'phpbb_source' => '3.3.14',
			'php' => '8.1',
			'db' => 'mariadb',
			'port' => 8081,
			'url' => 'http://localhost:8081/',
			'extensions' => [],
			'styles' => [],
		]);

		return [$project, $root];
	}

	private function style(string $root, string $name, string $relativePath): string
	{
		$path = $root . '/' . $relativePath;
		mkdir($path, 0775, true);
		file_put_contents($path . '/style.cfg', "name = $name\n");

		return realpath($path);
	}
}
