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
