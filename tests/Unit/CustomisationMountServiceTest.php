<?php

namespace QuickInstall\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\CustomisationMountService;
use QuickInstall\Sandbox\CustomisationManagerInterface;
use QuickInstall\Sandbox\Project;
use QuickInstall\Tests\Support\TempProjectTrait;

class CustomisationMountServiceTest extends TestCase
{
	use TempProjectTrait;

	public function testRecursiveMountCollectsPerItemErrors(): void
	{
		$project = new Project($this->createTempProjectRoot());
		$project->init();
		$project->appendBoard([
			'name' => 'demo',
			'phpbb' => '3.3.14',
			'phpbb_source' => '3.3.14',
			'phpbb_branch' => '3.3',
			'php' => '8.1',
			'db' => 'mariadb',
			'port' => 8081,
			'url' => 'http://localhost:8081/',
			'path' => $project->boardPath('demo'),
			'populate' => 'none',
			'debug' => false,
			'extensions' => [],
			'styles' => [],
		]);
		$manager = new TestCustomisationManager();

		$result = (new CustomisationMountService($project))->mount($manager, 'demo', 'customisations', false, true);

		self::assertTrue($result['recursive']);
		self::assertSame([['name' => 'good', 'mode' => 'bind', 'source' => '/tmp/good', 'target' => '/target/good']], $result['mounted']);
		self::assertSame(['/tmp/bad: broken customisation'], $result['errors']);
		self::assertSame(['/tmp/good', '/tmp/bad'], $manager->mounted);
	}
}

class TestCustomisationManager implements CustomisationManagerInterface
{
	public array $mounted = [];

	public function discover(string $source, bool $allowExternal = false): array
	{
		return ['/tmp/good', '/tmp/bad'];
	}

	public function mount(string $board, string $source, bool $copy = false, bool $allowExternal = false): array
	{
		$this->mounted[] = $source;
		if ($source === '/tmp/bad')
		{
			throw new InvalidArgumentException('broken customisation');
		}

		return ['name' => 'good', 'mode' => 'bind', 'source' => $source, 'target' => '/target/good'];
	}

	public function unmount(string $board, string $name): string
	{
		return '/target/' . $name;
	}

	public function cleanupStaleTarget(string $board, string $name): void
	{
	}
}
