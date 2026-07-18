<?php

namespace QuickInstall\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\ExtensionManager;
use QuickInstall\Sandbox\Project;
use QuickInstall\Tests\Support\TempProjectTrait;

class ExtensionManagerTest extends TestCase
{
	use TempProjectTrait;

	public function testMountsAndListsBindExtension(): void
	{
		[$project, $root] = $this->projectWithBoard('demo');
		$source = $this->extension($root, 'vendor/demo', 'customisations/vendor/demo');

		$mounted = (new ExtensionManager($project))->mount('demo', $source);

		self::assertSame('vendor/demo', $mounted['name']);
		self::assertSame('bind', $mounted['mode']);
		self::assertSame('/var/www/html/ext/vendor/demo', $mounted['target']);

		$list = (new ExtensionManager($project))->list('demo');
		self::assertSame('vendor/demo', $list[0]['name']);
		self::assertSame($source, $list[0]['source']);
	}

	public function testListSortsExtensionsByName(): void
	{
		[$project, $root] = $this->projectWithBoard('demo');
		$manager = new ExtensionManager($project);
		$manager->mount('demo', $this->extension($root, 'vendor/zulu', 'customisations/vendor/zulu'));
		$manager->mount('demo', $this->extension($root, 'vendor/alpha', 'customisations/vendor/alpha'));

		self::assertSame(['vendor/alpha', 'vendor/zulu'], array_column($manager->list('demo'), 'name'));
	}

	public function testCopyMountCopiesFilesIntoBoard(): void
	{
		[$project, $root] = $this->projectWithBoard('demo');
		$source = $this->extension($root, 'vendor/copied', 'customisations/vendor/copied');
		file_put_contents($source . '/file.txt', 'copied');

		$mounted = (new ExtensionManager($project))->mount('demo', $source, true);

		self::assertSame('copy', $mounted['mode']);
		self::assertFileExists($project->boardPath('demo') . '/ext/vendor/copied/file.txt');
		self::assertSame('copied', file_get_contents($project->boardPath('demo') . '/ext/vendor/copied/file.txt'));
	}

	public function testUnmountRemovesCopiedExtensionAndMetadata(): void
	{
		[$project, $root] = $this->projectWithBoard('demo');
		$source = $this->extension($root, 'vendor/remove', 'customisations/vendor/remove');
		$manager = new ExtensionManager($project);
		$manager->mount('demo', $source, true);

		$removed = $manager->unmount('demo', 'vendor/remove');

		self::assertSame($project->boardPath('demo') . '/ext/vendor/remove', $removed);
		self::assertDirectoryDoesNotExist($project->boardPath('demo') . '/ext/vendor/remove');
		self::assertSame([], $manager->list('demo'));
	}

	public function testUnmountBindExtensionRemovesMetadataOnly(): void
	{
		[$project, $root] = $this->projectWithBoard('demo');
		$source = $this->extension($root, 'vendor/bound', 'customisations/vendor/bound');
		$manager = new ExtensionManager($project);
		$manager->mount('demo', $source);

		$removed = $manager->unmount('demo', 'vendor/bound');

		self::assertSame('/var/www/html/ext/vendor/bound', $removed);
		self::assertDirectoryExists($source);
		self::assertSame([], $manager->list('demo'));
	}

	public function testRemountPreservesRegisteredBindTarget(): void
	{
		[$project, $root] = $this->projectWithBoard('demo');
		$source = $this->extension($root, 'vendor/bound', 'customisations/vendor/bound');
		$manager = new ExtensionManager($project);
		$manager->mount('demo', $source);
		$target = $project->boardPath('demo') . '/ext/vendor/bound';
		mkdir($target, 0775, true);
		file_put_contents($target . '/mountpoint.txt', 'preserve');

		$mounted = $manager->mount('demo', $source);

		self::assertSame('bind', $mounted['mode']);
		self::assertFileExists($target . '/mountpoint.txt');
	}

	public function testListDiscoversCopiedExtensionWithoutMetadata(): void
	{
		[$project, $root] = $this->projectWithBoard('demo');
		$source = $this->extension($root, 'vendor/copied', 'customisations/vendor/copied');
		$target = $project->boardPath('demo') . '/ext/vendor/copied';
		$project->copyTree($source, $target);

		$list = (new ExtensionManager($project))->list('demo');

		self::assertSame('vendor/copied', $list[0]['name']);
		self::assertSame('copy', $list[0]['mode']);
		self::assertSame($target, $list[0]['source']);
	}

	public function testCleanupStaleTargetRemovesExtensionDirectoryAndParents(): void
	{
		[$project, $root] = $this->projectWithBoard('demo');
		$source = $this->extension($root, 'vendor/stale', 'customisations/vendor/stale');
		$target = $project->boardPath('demo') . '/ext/vendor/stale';
		$project->copyTree($source, $target);

		(new ExtensionManager($project))->cleanupStaleTarget('demo', 'vendor/stale');

		self::assertDirectoryDoesNotExist($target);
		self::assertDirectoryDoesNotExist($project->boardPath('demo') . '/ext/vendor');
	}

	public function testRejectsExtensionWithoutComposerName(): void
	{
		[$project, $root] = $this->projectWithBoard('demo');
		$source = $root . '/customisations/vendor/noname';
		mkdir($source, 0775, true);
		file_put_contents($source . '/composer.json', '{}');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('must contain a name');

		(new ExtensionManager($project))->mount('demo', $source);
	}

	/**
	 * @dataProvider traversalPackageProvider
	 */
	public function testRejectsTraversalComponentsInComposerName(string $package): void
	{
		[$project, $root] = $this->projectWithBoard('demo');
		$source = $this->extension($root, $package, 'customisations/traversal-source');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid extension name');
		(new ExtensionManager($project))->mount('demo', $source, true);
	}

	public function traversalPackageProvider(): array
	{
		return [['../escape'], ['vendor/..'], ['./escape'], ['vendor/.']];
	}

	public function testRejectsExternalSourceUnlessAllowed(): void
	{
		[$project, $root] = $this->projectWithBoard('demo');
		$source = $this->extension($root, 'vendor/external', 'external/vendor/external');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Extension path must be under customisations/');

		(new ExtensionManager($project))->mount('demo', $source);
	}

	public function testDiscoversNestedExtensions(): void
	{
		[$project, $root] = $this->projectWithBoard('demo');
		$one = $this->extension($root, 'vendor/one', 'customisations/group/one');
		$two = $this->extension($root, 'vendor/two', 'customisations/group/two');

		self::assertSame([$one, $two], (new ExtensionManager($project))->discover('group'));
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

	private function extension(string $root, string $package, string $relativePath): string
	{
		$path = $root . '/' . $relativePath;
		mkdir($path, 0775, true);
		file_put_contents($path . '/composer.json', json_encode(['name' => $package], JSON_PRETTY_PRINT));

		return realpath($path);
	}
}
