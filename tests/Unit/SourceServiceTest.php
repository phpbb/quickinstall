<?php

namespace QuickInstall\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\Project;
use QuickInstall\Sandbox\SourceProvider;
use QuickInstall\Sandbox\SourceService;
use QuickInstall\Tests\Support\TempProjectTrait;

class SourceServiceTest extends TestCase
{
	use TempProjectTrait;

	public function testListsSourcesWithDownloadedAndUsageStatus(): void
	{
		[$project, $sourcePath] = $this->projectWithSource('3.3.14');
		$project->appendBoard([
			'name' => 'demo',
			'phpbb_source' => '3.3.14',
		]);

		$list = (new SourceService($project))->list();

		self::assertSame('3.3.14', $list[0]['source_key']);
		self::assertTrue($list[0]['downloaded']);
		self::assertSame(['demo'], $list[0]['used_by']);
		self::assertSame($sourcePath, $list[0]['path']);
	}

	public function testRemoveBlocksUsedSourceWithoutForce(): void
	{
		[$project] = $this->projectWithSource('3.3.14');
		$project->appendBoard([
			'name' => 'demo',
			'phpbb_source' => '3.3.14',
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Source 3.3.14 is used by board(s): demo');

		(new SourceService($project))->remove('3.3.14');
	}

	public function testRemoveCanResolveSourceBySelectorAlias(): void
	{
		$project = new Project($this->createTempProjectRoot());
		$project->init();
		$sourcePath = $this->addSource($project, '3.3');

		$result = (new SourceService($project))->remove('latest');

		self::assertSame('3.3', $result['source']['source_key']);
		self::assertDirectoryDoesNotExist($sourcePath);
	}

	public function testRemoveRejectsUnknownSource(): void
	{
		$project = new Project($this->createTempProjectRoot());
		$project->init();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Unknown source: 3.3.14');

		(new SourceService($project))->remove('3.3.14');
	}

	public function testRemoveWithForceDeletesRecordAndFiles(): void
	{
		[$project, $sourcePath] = $this->projectWithSource('3.3.14');
		$project->appendBoard([
			'name' => 'demo',
			'phpbb_source' => '3.3.14',
		]);

		$result = (new SourceService($project))->remove('3.3.14', true);

		self::assertSame(['demo'], $result['used_by']);
		self::assertDirectoryDoesNotExist($sourcePath);
		self::assertSame([], $project->readJson('sources.json', ['unexpected']));
	}

	public function testPruneRemovesOnlyUnusedSources(): void
	{
		[$project, $unusedPath] = $this->projectWithSource('3.3.14');
		$usedPath = $this->addSource($project, '3.3.15');
		$project->appendBoard([
			'name' => 'demo',
			'phpbb_source' => '3.3.15',
		]);

		$removed = (new SourceService($project))->prune();

		self::assertSame('3.3.14', $removed[0]['source_key']);
		self::assertDirectoryDoesNotExist($unusedPath);
		self::assertDirectoryExists($usedPath);
	}

	public function testCustomGitSourcesRequireExplicitTrust(): void
	{
		$project = new Project($this->createTempProjectRoot());
		$project->init();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Use --allow-external');

		(new SourceProvider($project))->add('master', 'git', 'https://example.test/phpbb.git');
	}

	public function testFetchGitEnsuresRegisteredSourceKey(): void
	{
		$project = new Project($this->createTempProjectRoot());
		$provider = new ServiceTestSourceProvider($project);

		$source = (new TestSourceService($project, $provider))->fetch('topic/123', true, 'https://example.test/phpbb.git', true);

		self::assertSame('topic-123', $source['source_key']);
		self::assertSame('topic-123', $provider->ensured[0]);
	}

	public function testFetchGitRollsBackRegistryWhenEnsureFails(): void
	{
		$project = new Project($this->createTempProjectRoot());
		$provider = new FailingServiceTestSourceProvider($project);

		try
		{
			(new TestSourceService($project, $provider))->fetch('missing_branch', true, 'https://example.test/phpbb.git', true);
			self::fail('Expected fetch failure.');
		}
		catch (\RuntimeException $e)
		{
			self::assertSame('clone failed', $e->getMessage());
		}

		self::assertSame([], $project->readJson('sources.json', ['unexpected']));
		self::assertDirectoryDoesNotExist($project->sourcePath('missing_branch'));
	}

	public function testSupportedVersionsReturnsVersionMatrix(): void
	{
		$versions = (new SourceService(new Project($this->createTempProjectRoot())))->supportedVersions();

		self::assertSame('latest', $versions[0]['selector']);
		self::assertSame('supported', $versions[0]['status']);
	}

	private function projectWithSource(string $version): array
	{
		$project = new Project($this->createTempProjectRoot());
		$project->init();
		$sourcePath = $this->addSource($project, $version);

		return [$project, $sourcePath];
	}

	private function addSource(Project $project, string $version): string
	{
		$sourcePath = $project->sourcePath($version);
		mkdir($sourcePath, 0775, true);
		file_put_contents($sourcePath . '/common.php', '<?php');
		$sources = $project->readJson('sources.json', []);
		$sources[$version] = [
			'version' => $version,
			'source_key' => $version,
			'type' => 'composer',
			'status' => 'supported',
			'path' => $sourcePath,
		];
		$project->writeJson('sources.json', $sources);

		return $sourcePath;
	}
}

class TestSourceService extends SourceService
{
	private SourceProvider $provider;

	public function __construct(Project $project, SourceProvider $provider)
	{
		parent::__construct($project);
		$this->provider = $provider;
	}

	protected function createSourceProvider(): SourceProvider
	{
		return $this->provider;
	}
}

class ServiceTestSourceProvider extends SourceProvider
{
	public array $ensured = [];

	public function ensure(string $version): array
	{
		$this->ensured[] = $version;
		return [
			'version' => 'topic/123',
			'source_key' => $version,
		];
	}
}

class FailingServiceTestSourceProvider extends SourceProvider
{
	private Project $project;

	public function __construct(Project $project)
	{
		parent::__construct($project);
		$this->project = $project;
	}

	public function ensure(string $version): array
	{
		mkdir($this->project->sourcePath($version), 0775, true);
		file_put_contents($this->project->sourcePath($version) . '/partial.txt', 'partial');
		throw new \RuntimeException('clone failed');
	}
}
