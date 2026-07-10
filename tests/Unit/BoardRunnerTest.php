<?php

namespace QuickInstall\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\BoardRunner;
use QuickInstall\Sandbox\Project;
use QuickInstall\Tests\Support\TempProjectTrait;
use RuntimeException;

class BoardRunnerTest extends TestCase
{
	use TempProjectTrait;

	public function testStatusReportsMissingWhenComposeFileDoesNotExist(): void
	{
		[$project] = $this->projectWithBoard();

		self::assertSame('missing', (new TestBoardRunner($project))->status('demo'));
	}

	public function testStatusReportsDockerStates(): void
	{
		[$project] = $this->projectWithBoard();
		$this->writeCompose($project);
		$runner = new TestBoardRunner($project);
		$runner->captures = [
			['exit_code' => 0, 'output' => "web\ndb\n"],
			['exit_code' => 0, 'output' => "web\n"],
		];

		self::assertSame('partial', $runner->status('demo'));

		$runner->captures = [
			['exit_code' => 0, 'output' => "web\ndb\n"],
			['exit_code' => 0, 'output' => "web\ndb\n"],
		];

		self::assertSame('running', $runner->status('demo'));

		$runner->captures = [
			['exit_code' => 0, 'output' => "web\n"],
			['exit_code' => 0, 'output' => ""],
		];

		self::assertSame('stopped', $runner->status('demo'));
	}

	public function testStatusReportsErrorsAndStoppedWhenNoServicesExist(): void
	{
		[$project] = $this->projectWithBoard();
		$this->writeCompose($project);
		$runner = new TestBoardRunner($project);
		$runner->captures = [
			['exit_code' => 1, 'output' => 'docker failed'],
		];

		self::assertSame('error', $runner->status('demo'));

		$runner->captures = [
			['exit_code' => 0, 'output' => "\n"],
		];

		self::assertSame('stopped', $runner->status('demo'));
	}

	public function testStopPurgeCacheAndRecreateWebRunExpectedDockerCommands(): void
	{
		[$project] = $this->projectWithBoard();
		$runner = new TestBoardRunner($project);

		$runner->stop('demo');
		$runner->purgeCache('demo');
		$runner->recreateWeb('demo');

		self::assertSame('stop', end($runner->runs[0]));
		self::assertSame('php bin/phpbbcli.php cache:purge', end($runner->runs[1]));
		self::assertSame(['up', '-d', '--force-recreate', 'web'], array_slice($runner->runs[2], -4));
	}

	public function testDestroyRunsComposeDownAndRemovesBoardData(): void
	{
		[$project] = $this->projectWithBoard();
		$boardPath = $project->boardPath('demo');
		$runtimePath = $project->runtimePath('demo');
		$dbPath = $project->dbPath('demo');
		mkdir($boardPath, 0775, true);
		mkdir($runtimePath, 0775, true);
		mkdir($dbPath, 0775, true);
		$this->writeCompose($project);

		$runner = new TestBoardRunner($project);
		$runner->destroy('demo');

		self::assertSame(['down', '--volumes', '--remove-orphans', '--rmi', 'local'], array_slice($runner->runs[0], -5));
		self::assertDirectoryDoesNotExist($boardPath);
		self::assertDirectoryDoesNotExist($runtimePath);
		self::assertDirectoryDoesNotExist($dbPath);
		self::assertSame([], $project->boards());
	}

	public function testSeedRejectsUnknownAction(): void
	{
		[$project] = $this->projectWithBoard();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Unknown seed action: nope');

		(new TestBoardRunner($project))->seed('demo', 'tiny', 1, 'nope');
	}

	public function testSeedResetDeletesMarkerAndReplaceWritesMarker(): void
	{
		[$project] = $this->projectWithBoard();
		mkdir($project->runtimePath('demo'), 0775, true);
		$marker = $project->runtimePath('demo') . '/seeded-tiny';
		file_put_contents($marker, 'old');
		$runner = new TestBoardRunner($project);

		$runner->seed('demo', 'tiny', 2, 'reset');

		self::assertFileDoesNotExist($marker);
		self::assertSame(['demo', 'tiny', 2, 'reset'], $runner->seedRuns[0]);

		$runner->seed('demo', 'tiny', 3, 'replace');

		self::assertFileExists($marker);
		self::assertSame(['demo', 'tiny', 3, 'replace'], $runner->seedRuns[1]);
	}

	public function testRunSeederRaisesPhpMemoryLimit(): void
	{
		[$project] = $this->projectWithBoard();
		mkdir($project->runtimePath('demo'), 0775, true);
		$runner = new CommandCapturingBoardRunner($project);

		$runner->runSeederForTest('demo', 'load-test', 1, 'replace');

		self::assertSame(['php', '-d', 'memory_limit=512M', '/tmp/qi_seed.php', 'load-test', '1', 'replace'], array_slice($runner->runs[1], -7));
	}

	public function testStartRunsDockerWaitsEnablesDebugSeedsAndChecksHttp(): void
	{
		[$project] = $this->projectWithBoard([
			'debug' => true,
			'populate' => 'tiny',
			'phpbb_branch' => '3.2',
			'url' => 'http://localhost:8080/',
		]);
		$boardPath = $project->boardPath('demo');
		mkdir($boardPath, 0775, true);
		file_put_contents($boardPath . '/config.php', "<?php\n// @define('DEBUG_CONTAINER', true);\n?>\n");
		$runner = new TestBoardRunner($project);

		ob_start();
		$runner->start('demo');
		$output = ob_get_clean();

		self::assertSame('', $output);
		self::assertSame(['up', '--build', '-d', '--force-recreate', '--remove-orphans', 'web'], array_slice($runner->runs[0], -6));
		self::assertSame(['demo'], $runner->installedWaits);
		self::assertSame([['demo', 'tiny']], $runner->seedIfNeededRuns);
		self::assertSame([['demo', 'http://localhost:8080/']], $runner->httpWaits);
		self::assertStringContainsString("@define('PHPBB_ENVIRONMENT', 'production');", file_get_contents($boardPath . '/config.php'));
		self::assertStringContainsString("@define('PHPBB_DISPLAY_LOAD_TIME', true);", file_get_contents($boardPath . '/config.php'));
	}

	public function testStartRejectsSqliteSeededBoards(): void
	{
		[$project] = $this->projectWithBoard([
			'db' => 'sqlite',
			'populate' => 'tiny',
		]);

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('SQLite boards currently support populate:none only');

		(new TestBoardRunner($project))->start('demo');
	}

	public function testStartAddsYamlDebugConfigOnce(): void
	{
		[$project] = $this->projectWithBoard([
			'debug' => true,
			'phpbb_branch' => '3.3',
		]);
		$configPath = $project->boardPath('demo') . '/config/production';
		mkdir($configPath, 0775, true);
		file_put_contents($configPath . '/config.yml', "parameters:\n    existing: true\n");
		$runner = new TestBoardRunner($project);

		$runner->start('demo');
		$runner->start('demo');

		$config = file_get_contents($configPath . '/config.yml');
		self::assertSame(1, substr_count($config, 'debug.load_time: true'));
		self::assertStringContainsString('twig.enable_debug_extension: false', $config);
	}

	private function projectWithBoard(array $overrides = []): array
	{
		$project = new Project($this->createTempProjectRoot());
		$project->init();
		$project->appendBoard($overrides + [
			'name' => 'demo',
			'db' => 'mariadb',
			'debug' => false,
			'populate' => 'none',
			'url' => '',
			'phpbb_branch' => '3.3',
		]);

		return [$project];
	}

	private function writeCompose(Project $project): void
	{
		$runtimePath = $project->runtimePath('demo');
		if (!is_dir($runtimePath))
		{
			mkdir($runtimePath, 0775, true);
		}
		file_put_contents($project->composePath('demo'), "services:\n  web:\n");
	}
}

class CommandCapturingBoardRunner extends BoardRunner
{
	public array $runs = [];

	public function runSeederForTest(string $name, string $preset, int $seed, string $action): void
	{
		$this->runSeeder($name, $preset, $seed, $action);
	}

	protected function run(array $command): void
	{
		$this->runs[] = $command;
	}
}

class TestBoardRunner extends BoardRunner
{
	public array $runs = [];
	public array $captures = [];
	public array $installedWaits = [];
	public array $httpWaits = [];
	public array $seedRuns = [];
	public array $seedIfNeededRuns = [];

	protected function run(array $command): void
	{
		$this->runs[] = $command;
	}

	protected function capture(array $command): array
	{
		return array_shift($this->captures) ?: ['exit_code' => 0, 'output' => ''];
	}

	protected function waitUntilInstalled(string $name): void
	{
		$this->installedWaits[] = $name;
	}

	protected function waitUntilHttpReady(string $name, string $url): void
	{
		$this->httpWaits[] = [$name, $url];
	}

	protected function runSeeder(string $name, string $preset, int $seed, string $action = 'seed'): void
	{
		$this->seedRuns[] = [$name, $preset, $seed, $action];
	}

	protected function seedIfNeeded(string $name, string $preset): void
	{
		$this->seedIfNeededRuns[] = [$name, $preset];
	}
}
