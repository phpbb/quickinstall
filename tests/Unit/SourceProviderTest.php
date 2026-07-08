<?php

namespace QuickInstall\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\Project;
use QuickInstall\Sandbox\SourceProvider;
use QuickInstall\Tests\Support\TempProjectTrait;

class SourceProviderTest extends TestCase
{
	use TempProjectTrait;

	public function testAddComposerExactVersionRegistersSource(): void
	{
		$project = $this->project();

		$source = (new TestSourceProvider($project))->add('3.3.14', 'composer', null);

		self::assertSame('3.3.14', $source['source_key']);
		self::assertSame('phpbb/phpbb', $source['package']);
		self::assertNull($source['url']);
		self::assertSame($source, $project->readJson('sources.json', [])['3.3.14']);
	}

	/**
	 * @dataProvider phpRequirementProvider
	 */
	public function testEnsureDownloadedSourceAddsPhpRequirementMetadata(string $requirement, string $expectedRuntime): void
	{
		$project = $this->project();
		$this->addDownloadedSource($project, '3.3.14', $requirement);

		$source = (new TestSourceProvider($project))->ensure('3.3.14');

		self::assertSame($requirement, $source['php_requirement']);
		self::assertSame($expectedRuntime, $source['php']);
		self::assertSame($expectedRuntime, $project->readJson('sources.json', [])['3.3.14']['php']);
	}

	public function testEnsureRegisteredCustomSourceByKey(): void
	{
		$project = $this->project();
		$this->addDownloadedSource($project, 'fork', '^8.1', [
			'version' => 'vendor/fork',
			'source_key' => 'fork',
			'phpbb_branch' => 'custom',
		]);

		$source = (new TestSourceProvider($project))->ensure('fork');

		self::assertSame('fork', $source['source_key']);
		self::assertSame('^8.1', $source['php_requirement']);
	}

	public function testFetchComposerBuildsCreateProjectCommand(): void
	{
		$project = $this->project();
		$provider = new TestSourceProvider($project);

		$provider->fetch([
			'type' => 'composer',
			'constraint' => '3.3.14',
			'path' => $project->sourcePath('3.3.14'),
		]);

		self::assertSame('composer-bin', $provider->runs[0]['command'][0]);
		self::assertContains('create-project', $provider->runs[0]['command']);
		self::assertContains('phpbb/phpbb', $provider->runs[0]['command']);
		self::assertContains('3.3.14', $provider->runs[0]['command']);
		self::assertSame(dirname($project->sourcePath('3.3.14')), $provider->runs[0]['cwd']);
	}

	public function testFetchGitNormalizesPhpbbSubdirectoryAndRunsComposerInstall(): void
	{
		$project = $this->project();
		$provider = new TestSourceProvider($project);
		$path = $project->sourcePath('custom');

		$provider->fetch([
			'type' => 'git',
			'url' => 'https://github.com/phpbb/phpbb.git',
			'branch' => 'master',
			'version' => 'master',
			'path' => $path,
		]);

		self::assertFileExists($path . '/common.php');
		self::assertFileDoesNotExist($path . '/phpBB/common.php');
		self::assertSame('git', $provider->runs[0]['command'][0]);
		self::assertSame(['composer-bin', 'install', '--no-interaction', '--ignore-platform-reqs'], $provider->runs[1]['command']);
	}

	public function testEnsureFloatingComposerReusesExistingResolvedSource(): void
	{
		$project = $this->project();
		$this->addDownloadedSource($project, '3.3.15');
		$provider = new TestSourceProvider($project);
		$provider->captures = [
			[
				'status' => 0,
				'output' => json_encode(['versions' => ['3.3.14', '3.3.15', 'dev-master']]),
			],
		];

		ob_start();
		$source = $provider->ensure('3.3');
		$output = ob_get_clean();

		self::assertSame('', $output);
		self::assertSame('3.3.15', $source['source_key']);
		self::assertSame('3.3.15', $source['constraint']);
	}

	public function phpRequirementProvider(): array
	{
		return [
			'default runtime satisfies lower minimum' => ['>=7.4', '8.1'],
			'default runtime satisfies caret minimum' => ['^8.1', '8.1'],
			'higher minimum bumps runtime' => ['>=8.2', '8.2'],
			'less-than constraint is ignored as minimum' => ['<8.0 || >=8.1', '8.1'],
			'not-equal constraint is ignored as minimum' => ['!=8.2 >=8.0', '8.1'],
			'malformed requirement keeps default' => ['dev-main', '8.1'],
		];
	}

	private function project(): Project
	{
		$project = new Project($this->createTempProjectRoot());
		$project->init();

		return $project;
	}

	private function addDownloadedSource(Project $project, string $key, string $phpRequirement = '>=7.4', array $overrides = []): void
	{
		$sourcePath = $project->sourcePath($key);
		mkdir($sourcePath, 0775, true);
		file_put_contents($sourcePath . '/common.php', '<?php');
		file_put_contents($sourcePath . '/composer.json', json_encode(['require' => ['php' => $phpRequirement]]));
		$sources = $project->readJson('sources.json', []);
		$sources[$key] = $overrides + [
			'version' => $key,
			'source_key' => $key,
			'constraint' => $key,
			'branch' => '3.3',
			'phpbb_branch' => '3.3',
			'php' => '8.1',
			'status' => 'supported',
			'type' => 'composer',
			'package' => 'phpbb/phpbb',
			'url' => null,
			'path' => $sourcePath,
		];
		$project->writeJson('sources.json', $sources);
	}
}

class TestSourceProvider extends SourceProvider
{
	public array $runs = [];
	public array $captures = [];

	protected function composerCommand(array $arguments): array
	{
		return array_merge(['composer-bin'], $arguments);
	}

	protected function run(array $command, string $cwd): void
	{
		$this->runs[] = ['command' => $command, 'cwd' => $cwd];
		if (($command[0] ?? '') === 'git')
		{
			$target = end($command);
			mkdir($target . '/phpBB', 0775, true);
			file_put_contents($target . '/phpBB/common.php', '<?php');
			file_put_contents($target . '/phpBB/composer.json', '{}');
			file_put_contents($target . '/README.md', 'repository root file');
		}
	}

	protected function capture(array $command, string $cwd): array
	{
		return array_shift($this->captures) ?: ['status' => 1, 'output' => ''];
	}
}
