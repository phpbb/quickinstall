<?php

namespace QuickInstall\Tests\Integration;

use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\Project;
use QuickInstall\Sandbox\Web\Application;
use QuickInstall\Tests\Support\TempProjectTrait;

class WebApplicationTest extends TestCase
{
	use TempProjectTrait {
		tearDown as cleanupTempPaths;
	}

	private array $serverBackup = [];
	private array $postBackup = [];
	private array $getBackup = [];

	protected function setUp(): void
	{
		$this->serverBackup = $_SERVER;
		$this->postBackup = $_POST;
		$this->getBackup = $_GET;
	}

	protected function tearDown(): void
	{
		$_SERVER = $this->serverBackup;
		$_POST = $this->postBackup;
		$_GET = $this->getBackup;
		$this->cleanupTempPaths();
	}

	public function testRenderShowsCoreSandboxWorkflows(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		$this->addDownloadedSource($project, '3.3.14');
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

		$html = $this->runWebApplication($root);

		self::assertStringContainsString('QuickInstall Sandbox', $html);
		self::assertStringContainsString('Create board', $html);
		self::assertStringContainsString('Sources', $html);
		self::assertStringContainsString('Mount extension', $html);
		self::assertStringContainsString('Mount style', $html);
		self::assertStringContainsString('board_start', $html);
		self::assertStringContainsString('board_seed', $html);
		self::assertStringContainsString('source_remove', $html);
		self::assertStringContainsString('data-ajax', $html);
		self::assertStringContainsString('activity-log', $html);
		self::assertStringContainsString('/assets/sandbox-ui.css', $html);
		self::assertStringContainsString('/assets/sandbox-ui.js', $html);
		self::assertStringNotContainsString('<style>', $html);
	}

	public function testInitPostCreatesWorkspace(): void
	{
		$root = $this->createTempProjectRoot();

		$html = $this->runWebApplication($root, ['action' => 'init']);

		self::assertDirectoryExists($root . '/.qi');
		self::assertFileExists($root . '/.qi/boards.json');
		self::assertStringContainsString('Workspace initialized.', $html);
	}

	public function testAjaxPostReturnsDashboardJson(): void
	{
		$root = $this->createTempProjectRoot();

		$json = $this->runWebApplication($root, ['action' => 'init'], true);
		$data = json_decode($json, true);

		self::assertIsArray($data);
		self::assertTrue($data['ok']);
		self::assertSame('Workspace initialized.', $data['notice']);
		self::assertStringContainsString('status-strip', $data['html']);
		self::assertStringContainsString('activity-log', $data['html']);
	}

	public function testAjaxSourceRemoveDeletesSource(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		$this->addDownloadedSource($project, '3.3.14');

		$json = $this->runWebApplication($root, [
			'action' => 'source_remove',
			'source' => '3.3.14',
		], true);
		$data = json_decode($json, true);

		self::assertIsArray($data);
		self::assertTrue($data['ok']);
		self::assertSame('Removed source: 3.3.14', $data['notice']);
		self::assertSame([], $project->readJson('sources.json', []));
		self::assertStringNotContainsString('value="3.3.14"', $data['html']);
	}

	private function runWebApplication(string $root, array $post = [], bool $ajax = false): string
	{
		$_SERVER['REQUEST_METHOD'] = $post ? 'POST' : 'GET';
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		if ($ajax)
		{
			$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		}
		$_POST = $post;
		$_GET = [];

		ob_start();
		(new Application($root))->run();
		return (string) ob_get_clean();
	}

	private function addDownloadedSource(Project $project, string $key): void
	{
		$sourcePath = $project->sourcePath($key);
		mkdir($sourcePath, 0775, true);
		file_put_contents($sourcePath . '/common.php', '<?php');
		$project->writeJson('sources.json', [
			$key => [
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
			],
		]);
	}
}
