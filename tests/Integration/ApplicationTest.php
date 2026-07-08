<?php

namespace QuickInstall\Tests\Integration;

use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\Application;
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

	public function testBoardCreatePromptsToStartBoardAfterNextStep(): void
	{
		$root = $this->createTempProjectRoot();
		$this->addDownloadedSource($root, '3.3.14');

		$result = $this->runApplication($root, ['qi', 'board:create', 'demo', '--phpbb', '3.3.14', '--port', (string) $this->availablePort()], "n\n");

		self::assertSame(0, $result['exit_code']);
		self::assertStringContainsString('NEXT:', $result['output']);
		self::assertStringContainsString('php bin/qi board:start demo', $result['output']);
		self::assertStringContainsString('Run this command now? [Y/n]: ', $result['output']);
		self::assertStringNotContainsString('Started board: demo', $result['output']);
	}

	public function testBoardCreateDefaultsStartPromptToYes(): void
	{
		self::assertTrue($this->confirmAnswer("\n", true));
	}

	public function testBoardCreateRepeatsStartPromptUntilYesOrNo(): void
	{
		$root = $this->createTempProjectRoot();
		$this->addDownloadedSource($root, '3.3.14');

		$result = $this->runApplication($root, ['qi', 'board:create', 'demo', '--phpbb', '3.3.14', '--port', (string) $this->availablePort()], "maybe\nn\n");

		self::assertSame(0, $result['exit_code']);
		self::assertStringContainsString('Please answer y or n.', $result['output']);
		self::assertSame(2, substr_count($result['output'], 'Run this command now? [Y/n]: '));
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

	public function testUiStartHelpIsExposed(): void
	{
		$result = $this->runApplication($this->createTempProjectRoot(), ['qi', 'ui:start', '--help']);

		self::assertSame(0, $result['exit_code']);
		self::assertStringContainsString('Usage:', $result['output']);
		self::assertStringContainsString('qi ui:start', $result['output']);
		self::assertStringContainsString('built-in server', $result['output']);
	}

	public function testUiStartRejectsNonLocalHostBeforeStartingServer(): void
	{
		$result = $this->runApplication($this->createTempProjectRoot(), ['qi', 'ui:start', '--host', '0.0.0.0']);

		self::assertSame(1, $result['exit_code']);
		self::assertSame('', $result['output']);
		self::assertStringContainsString('local loopback hosts', $result['stderr']);
	}

	public function testUnknownCommandReturnsFailureAndHelp(): void
	{
		$result = $this->runApplication($this->createTempProjectRoot(), ['qi', 'nope']);

		self::assertSame(1, $result['exit_code']);
		self::assertStringContainsString('QuickInstall CLI', $result['output']);
		self::assertStringContainsString('Unknown command: nope', $result['stderr']);
	}

	private function addDownloadedSource(string $root, string $version): void
	{
		$project = new Project($root);
		$project->init();
		$sourcePath = $project->sourcePath($version);
		mkdir($sourcePath, 0775, true);
		file_put_contents($sourcePath . '/common.php', '<?php');
		$project->writeJson('sources.json', [
			$version => [
				'version' => $version,
				'source_key' => $version,
				'constraint' => $version,
				'branch' => $version,
				'phpbb_branch' => '3.3',
				'php' => '8.1',
				'status' => 'supported',
				'type' => 'composer',
				'package' => 'phpbb/phpbb',
				'url' => null,
				'path' => $sourcePath,
			],
		]);
	}

	private function availablePort(): int
	{
		$socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
		if ($socket === false)
		{
			throw new \RuntimeException("Unable to allocate test port: $errstr");
		}

		$name = stream_socket_get_name($socket, false);
		fclose($socket);
		return (int) substr(strrchr($name, ':'), 1);
	}

	private function confirmAnswer(string $input, bool $default): bool
	{
		$stdin = fopen('php://temp', 'r+');
		fwrite($stdin, $input);
		rewind($stdin);
		$application = new Application($this->createTempProjectRoot(), null, $stdin);
		$method = new \ReflectionMethod(Application::class, 'confirm');
		$method->setAccessible(true);

		ob_start();
		$result = $method->invoke($application, 'Run this command now? [Y/n]: ', $default);
		ob_end_clean();
		fclose($stdin);

		return $result;
	}
}
