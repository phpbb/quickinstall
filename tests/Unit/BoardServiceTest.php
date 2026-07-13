<?php

namespace QuickInstall\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\BoardRunner;
use QuickInstall\Sandbox\BoardService;
use QuickInstall\Sandbox\DockerComposeWriter;
use QuickInstall\Sandbox\Project;
use QuickInstall\Tests\Support\TempProjectTrait;

class BoardServiceTest extends TestCase
{
	use TempProjectTrait;

	public function testCreateWritesBoardAndRuntimeConfig(): void
	{
		$project = $this->projectWithSource('3.3.14');
		$runner = new ServiceTestBoardRunner($project);
		$service = new TestBoardService($project, $runner);

		$result = $service->create('demo', '3.3.14', 'postgres', 8090, 'tiny', true);

		self::assertSame('demo', $result['board']['name']);
		self::assertSame('postgres', $result['board']['db']);
		self::assertSame(8090, $result['board']['port']);
		self::assertSame('tiny', $result['board']['populate']);
		self::assertTrue($result['board']['debug']);
		self::assertSame('demo', $project->boards()['demo']['name']);
		self::assertFileExists($project->composePath('demo'));
	}

	public function testCreateRejectsDuplicateWithoutReplace(): void
	{
		$project = $this->projectWithSource('3.3.14');
		$project->appendBoard(['name' => 'demo', 'port' => 8090]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Board already exists');

		(new TestBoardService($project))->create('demo', '3.3.14', 'mariadb', 8090);
	}

	public function testRejectsBoardNamesThatDifferOnlyByCase(): void
	{
		$project = $this->projectWithSource('3.3.14');
		$project->appendBoard(['name' => 'Demo', 'port' => 8090]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('case-insensitive');
		(new TestBoardService($project))->create('demo', '3.3.14', 'mariadb', 8091);
	}

	public function testCreateWithReplacePreservesExistingBoardUntilReplacementIsReady(): void
	{
		$project = $this->projectWithSource('3.3.14');
		$project->appendBoard(['name' => 'demo', 'port' => 8090]);
		$runner = new ServiceTestBoardRunner($project);

		(new TestBoardService($project, $runner))->create('demo', '3.3.14', 'mariadb', 8090, 'none', false, true);

		self::assertSame(['demo'], $runner->preparedForReplacement);
	}

	public function testFailedReplacementRestoresExistingBoardFiles(): void
	{
		$project = $this->projectWithSource('3.3.14');
		$project->appendBoard(['name' => 'demo', 'port' => 8090]);
		mkdir($project->boardPath('demo'), 0775, true);
		file_put_contents($project->boardPath('demo') . '/existing.txt', 'preserved');
		$runner = new ServiceTestBoardRunner($project);
		$writer = new FailingDockerComposeWriter($project);

		try
		{
			(new TestBoardService($project, $runner, false, $writer))->create('demo', '3.3.14', 'mariadb', 8090, 'none', false, true);
			self::fail('Expected replacement failure.');
		}
		catch (\RuntimeException $e)
		{
			self::assertSame('replacement write failed', $e->getMessage());
		}

		self::assertSame('preserved', file_get_contents($project->boardPath('demo') . '/existing.txt'));
		self::assertSame(8090, $project->board('demo')['port']);
	}

	public function testCreateCanUseRegisteredCustomSourceKey(): void
	{
		$project = $this->projectWithSource('topic-123');
		$sources = $project->readJson('sources.json', []);
		$sources['topic-123']['version'] = 'topic/123';
		$sources['topic-123']['branch'] = 'topic/123';
		$sources['topic-123']['phpbb_branch'] = 'custom';
		$sources['topic-123']['status'] = 'experimental';
		$sources['topic-123']['type'] = 'git';
		$sources['topic-123']['url'] = 'https://example.test/phpbb.git';
		unset($sources['topic-123']['php']);
		$project->writeJson('sources.json', $sources);
		mkdir($sources['topic-123']['path'] . '/install', 0775, true);
		file_put_contents($sources['topic-123']['path'] . '/install/phpbbcli.php', "<?php\ndefine('PHPBB_VERSION', '3.3.0');\n");

		$result = (new TestBoardService($project))->create('demo', 'topic-123', 'mariadb', 8090);

		self::assertSame('topic/123', $result['board']['phpbb']);
		self::assertSame('topic-123', $result['board']['phpbb_source']);
		self::assertSame('3.3', $result['board']['phpbb_branch']);
		self::assertSame('7.4', $result['board']['php']);
	}

	public function testCreateRejectsUsedPortAndHostPort(): void
	{
		$project = $this->projectWithSource('3.3.14');
		$project->appendBoard(['name' => 'other', 'port' => 8090]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Port 8090 is already used by board: other');

		(new TestBoardService($project))->create('demo', '3.3.14', 'mariadb', 8090);
	}

	public function testCreateRejectsHostPortInUse(): void
	{
		$project = $this->projectWithSource('3.3.14');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Port 8090 is already in use on this host.');

		(new TestBoardService($project, null, true))->create('demo', '3.3.14', 'mariadb', 8090);
	}

	public function testListAddsRunnerStatusAndDefaults(): void
	{
		$project = $this->projectWithSource('3.3.14');
		$project->appendBoard(['name' => 'demo']);
		$runner = new ServiceTestBoardRunner($project);
		$runner->statuses['demo'] = 'running';

		$list = (new TestBoardService($project, $runner))->list();

		self::assertSame('running', $list[0]['status']);
		self::assertSame('none', $list[0]['populate']);
		self::assertFalse($list[0]['debug']);
	}

	public function testStartStopDestroyAndSeedDelegateToRunner(): void
	{
		$project = $this->projectWithSource('3.3.14');
		$project->appendBoard(['name' => 'demo', 'db' => 'mariadb']);
		$runner = new ServiceTestBoardRunner($project);
		$runner->statuses['demo'] = 'running';
		$service = new TestBoardService($project, $runner);

		self::assertSame('demo', $service->start('demo')['name']);
		$service->stop('demo');
		$service->seed('demo', 'tiny', 4, 'replace');
		$service->destroy('demo');

		self::assertSame(['demo'], $runner->started);
		self::assertSame(['demo'], $runner->stopped);
		self::assertSame([['demo', 'tiny', 4, 'replace']], $runner->seeded);
		self::assertSame(['demo'], $runner->destroyed);
	}

	public function testSeedRequiresRunningBoard(): void
	{
		$project = $this->projectWithSource('3.3.14');
		$project->appendBoard(['name' => 'demo', 'db' => 'mariadb']);
		$runner = new ServiceTestBoardRunner($project);
		$runner->statuses['demo'] = 'stopped';

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Board must be running before seeding. Start it first.');

		(new TestBoardService($project, $runner))->seed('demo', 'tiny', 1, 'seed');
	}

	/**
	 * @dataProvider sqliteSeedActionProvider
	 */
	public function testSqliteSeedActionRules(string $action, bool $allowed): void
	{
		$project = $this->projectWithSource('3.3.14');
		$project->appendBoard(['name' => 'demo', 'db' => 'sqlite']);
		$runner = new ServiceTestBoardRunner($project);
		$runner->statuses['demo'] = 'running';

		if (!$allowed)
		{
			$this->expectException(InvalidArgumentException::class);
			$this->expectExceptionMessage('SQLite boards do not support fixture seeding');
		}

		(new TestBoardService($project, $runner))->seed('demo', 'tiny', 1, $action);

		if ($allowed)
		{
			self::assertSame([['demo', 'tiny', 1, $action]], $runner->seeded);
		}
	}

	public function sqliteSeedActionProvider(): array
	{
		return [
			'seed rejected' => ['seed', false],
			'replace rejected' => ['replace', false],
			'reset allowed' => ['reset', true],
		];
	}

	private function projectWithSource(string $version, ?string $osFamily = null): Project
	{
		$project = new Project($this->createTempProjectRoot(), $osFamily);
		$project->init();
		$sourcePath = $project->sourcePath($version);
		mkdir($sourcePath, 0775, true);
		file_put_contents($sourcePath . '/common.php', '<?php');
		$project->writeJson('sources.json', [
			$version => [
				'version' => $version,
				'source_key' => $version,
				'constraint' => $version,
				'branch' => '3.3',
				'phpbb_branch' => '3.3',
				'php' => '8.1',
				'status' => 'supported',
				'type' => 'composer',
				'package' => 'phpbb/phpbb',
				'url' => null,
				'path' => $sourcePath,
			],
		]);

		return $project;
	}
}

class TestBoardService extends BoardService
{
	private ?ServiceTestBoardRunner $runner;
	private bool $portInUse;
	private ?DockerComposeWriter $writer;

	public function __construct(Project $project, ?ServiceTestBoardRunner $runner = null, bool $portInUse = false, ?DockerComposeWriter $writer = null)
	{
		parent::__construct($project);
		$this->runner = $runner;
		$this->portInUse = $portInUse;
		$this->writer = $writer;
	}

	protected function isPortInUse(int $port): bool
	{
		return $this->portInUse;
	}

	protected function createBoardRunner(): BoardRunner
	{
		return $this->runner ?? parent::createBoardRunner();
	}

	protected function createDockerComposeWriter(): DockerComposeWriter
	{
		return $this->writer ?? parent::createDockerComposeWriter();
	}
}

class FailingDockerComposeWriter extends DockerComposeWriter
{
	public function write(string $name, array $config): array
	{
		throw new \RuntimeException('replacement write failed');
	}
}

class ServiceTestBoardRunner extends BoardRunner
{
	public array $statuses = [];
	public array $started = [];
	public array $stopped = [];
	public array $destroyed = [];
	public array $preparedForReplacement = [];
	public array $seeded = [];

	public function start(string $name): void
	{
		$this->started[] = $name;
	}

	public function stop(string $name): void
	{
		$this->stopped[] = $name;
	}

	public function destroy(string $name): void
	{
		$this->destroyed[] = $name;
	}

	public function prepareReplacement(string $name): void
	{
		$this->preparedForReplacement[] = $name;
	}

	public function status(string $name): string
	{
		return $this->statuses[$name] ?? 'stopped';
	}

	public function seed(string $name, string $preset, int $seed, string $action = 'seed'): void
	{
		$this->seeded[] = [$name, $preset, $seed, $action];
	}
}
