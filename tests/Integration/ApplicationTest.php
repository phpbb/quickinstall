<?php

namespace QuickInstall\Tests\Integration;

use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\Project;
use QuickInstall\Tests\Support\ApplicationRunnerTrait;
use QuickInstall\Tests\Support\TempProjectTrait;

class ApplicationTest extends TestCase
{
	use ApplicationRunnerTrait;
	use TempProjectTrait;

	public function testHelpCommandPrintsCommandIndex(): void
	{
		$result = $this->runApplication($this->createTempProjectRoot(), ['qi', 'help']);

		self::assertSame(0, $result['exit_code']);
		self::assertStringContainsString('QuickInstall CLI', $result['output']);
		self::assertStringContainsString('board:create', $result['output']);
		self::assertStringContainsString('source:fetch', $result['output']);
	}

	public function testCommandHelpPrintsSpecificUsage(): void
	{
		$result = $this->runApplication($this->createTempProjectRoot(), ['qi', 'board:create', '--help']);

		self::assertSame(0, $result['exit_code']);
		self::assertStringContainsString('Usage:', $result['output']);
		self::assertStringContainsString('qi board:create <name>', $result['output']);
	}

	public function testInitCreatesWorkspace(): void
	{
		$root = $this->createTempProjectRoot();

		$result = $this->runApplication($root, ['qi', 'init']);

		self::assertSame(0, $result['exit_code']);
		self::assertStringContainsString('Created', $result['output']);
		self::assertDirectoryExists($root . '/.qi');
		self::assertFileExists($root . '/.qi/sources.json');
	}

	public function testSourceListHandlesEmptyWorkspace(): void
	{
		$root = $this->createTempProjectRoot();
		(new Project($root))->init();

		$result = $this->runApplication($root, ['qi', 'source:list']);

		self::assertSame(0, $result['exit_code']);
		self::assertStringContainsString('No sources registered', $result['output']);
	}

	public function testPhpbbListPrintsSupportedSelectors(): void
	{
		$result = $this->runApplication($this->createTempProjectRoot(), ['qi', 'phpbb:list']);

		self::assertSame(0, $result['exit_code']);
		self::assertStringContainsString('Selector', $result['output']);
		self::assertStringContainsString('latest', $result['output']);
		self::assertStringContainsString('3.3.*', $result['output']);
	}

	public function testBoardCreateValidationRunsBeforeExternalFetch(): void
	{
		$result = $this->runApplication($this->createTempProjectRoot(), ['qi', 'board:create', 'demo', '--db', 'oracle']);

		self::assertSame(1, $result['exit_code']);
		self::assertSame('', $result['output']);
		self::assertStringContainsString('--db must be one of: mariadb, mysql, postgres, sqlite.', $result['stderr']);
	}

	public function testBoardSeedRejectsConflictingResetAndReplace(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		$project->appendBoard([
			'name' => 'demo',
			'db' => 'mariadb',
			'url' => 'http://localhost:8081/',
		]);

		$result = $this->runApplication($root, ['qi', 'board:seed', 'demo', '--reset', '--replace']);

		self::assertSame(1, $result['exit_code']);
		self::assertSame('', $result['output']);
		self::assertStringContainsString('Use --reset or --replace, not both.', $result['stderr']);
	}

	public function testUnknownCommandReturnsFailureAndHelp(): void
	{
		$result = $this->runApplication($this->createTempProjectRoot(), ['qi', 'nope']);

		self::assertSame(1, $result['exit_code']);
		self::assertStringContainsString('QuickInstall CLI', $result['output']);
		self::assertStringContainsString('Unknown command: nope', $result['stderr']);
	}
}
