<?php

namespace QuickInstall\Tests\Unit;

use QuickInstall\Sandbox\BoardRunner;
use QuickInstall\Sandbox\CustomisationUnmountService;
use QuickInstall\Sandbox\ExtensionManager;
use QuickInstall\Sandbox\Project;
use QuickInstall\Sandbox\StyleManager;
use QuickInstall\Tests\Support\TempProjectTrait;
use RuntimeException;

class CustomisationUnmountServiceTest extends \PHPUnit\Framework\TestCase
{
	use TempProjectTrait;

	public function testPurgesExtensionBeforeUnmountingInstalledBoard(): void
	{
		[$project, $root] = $this->projectWithBoard();
		$source = $root . '/customisations/vendor/demo';
		mkdir($source, 0775, true);
		file_put_contents($source . '/composer.json', json_encode(['name' => 'vendor/demo']));
		$manager = new ExtensionManager($project);
		$manager->mount('demo', $source);
		$runner = new UnmountTestBoardRunner($project);

		(new CustomisationUnmountService($project, null, $runner))->extension($manager, 'demo', 'vendor/demo');

		self::assertSame([['extension', 'demo', 'vendor/demo']], $runner->uninstalls);
		self::assertSame([], $manager->list('demo'));
	}

	public function testCleanupFailurePreservesMount(): void
	{
		[$project, $root] = $this->projectWithBoard();
		$source = $root . '/customisations/styles/demo';
		mkdir($source, 0775, true);
		file_put_contents($source . '/style.cfg', "name = demo\n");
		$manager = new StyleManager($project);
		$manager->mount('demo', $source);
		$runner = new UnmountTestBoardRunner($project);
		$runner->fail = true;

		try
		{
			(new CustomisationUnmountService($project, null, $runner))->style($manager, 'demo', 'demo');
			self::fail('Expected cleanup failure.');
		}
		catch (RuntimeException $e)
		{
			self::assertSame('cleanup failed', $e->getMessage());
		}

		self::assertSame('demo', $manager->list('demo')[0]['name']);
	}

	private function projectWithBoard(): array
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		mkdir($project->boardPath('demo'), 0775, true);
		file_put_contents($project->boardPath('demo') . '/config.php', '<?php');
		$project->appendBoard([
			'name' => 'demo',
			'phpbb' => '3.3.17',
			'phpbb_source' => '3.3.17',
			'php' => '8.1',
			'db' => 'mariadb',
			'port' => 8080,
			'url' => 'http://localhost:8080/',
			'extensions' => [],
			'styles' => [],
		]);

		return [$project, $root];
	}
}

class UnmountTestBoardRunner extends BoardRunner
{
	public array $uninstalls = [];
	public bool $fail = false;

	public function status(string $name): string
	{
		return 'running';
	}

	public function uninstallExtension(string $board, string $name): void
	{
		$this->uninstalls[] = ['extension', $board, $name];
	}

	public function uninstallStyle(string $board, string $name): void
	{
		if ($this->fail)
		{
			throw new RuntimeException('cleanup failed');
		}
		$this->uninstalls[] = ['style', $board, $name];
	}

	public function recreateWeb(string $name): void
	{
	}

	public function purgeCache(string $name): void
	{
	}
}
