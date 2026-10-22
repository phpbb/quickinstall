<?php

namespace QuickInstall\Tests\Integration;

use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\Project;
use QuickInstall\Sandbox\UpdateService;
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

	public function testRenderShowsProjectState(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		$this->addDownloadedSource($project, '3.3.19');
		$project->appendBoard([
			'name' => 'demo',
			'phpbb' => '3.3.19',
			'phpbb_source' => '3.3.19',
			'phpbb_branch' => '3.3',
			'php' => '8.2',
			'db' => 'mariadb',
			'port' => 8081,
			'url' => 'http://localhost:8081/',
			'path' => $project->boardPath('demo'),
			'populate' => 'none',
			'debug' => false,
			'extensions' => [
				'vendor/one' => ['mode' => 'bind', 'source' => '/tmp/one'],
				'vendor/two' => ['mode' => 'bind', 'source' => '/tmp/two'],
				'vendor/three' => ['mode' => 'bind', 'source' => '/tmp/three'],
				'vendor/four' => ['mode' => 'bind', 'source' => '/tmp/four'],
			],
			'styles' => [],
			'languages' => [
				'de' => ['mode' => 'bind', 'source' => '/tmp/de'],
			],
		]);

		$html = $this->runWebApplication($root);
		$text = $this->visibleText($html);

		self::assertStringContainsString('QuickInstall Dashboard', $text);
		self::assertStringContainsString('demo', $text);
		self::assertStringContainsString('http://localhost:8081/', $text);
		self::assertStringContainsString('3.3.19', $text);
		self::assertStringContainsString('vendor/one', $text);
		self::assertStringContainsString('vendor/four', $text);
	}

	public function testRenderListsEveryBoard(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		for ($index = 1; $index <= 10; $index++)
		{
			$project->appendBoard([
				'name' => 'board-' . $index,
				'phpbb' => '3.3.19',
				'phpbb_source' => '3.3.19',
				'php' => '8.2',
				'db' => 'mariadb',
				'port' => 8100 + $index,
				'url' => 'http://localhost:' . (8100 + $index) . '/',
				'populate' => 'none',
				'extensions' => [],
				'styles' => [],
				'languages' => [],
			]);
		}

		$html = $this->runWebApplication($root);
		$text = $this->visibleText($html);

		for ($index = 1; $index <= 10; $index++)
		{
			self::assertStringContainsString('board-' . $index, $text);
			self::assertStringContainsString('http://localhost:' . (8100 + $index) . '/', $text);
		}
	}

	public function testRunningBoardRendersOneStopAction(): void
	{
		$html = $this->renderDashboard([[
			'name' => 'demo',
			'phpbb' => '3.3.19',
			'php' => '8.2',
			'db' => 'mariadb',
			'url' => 'http://localhost:8080/',
			'populate' => 'none',
			'status' => 'running',
			'mounted_extensions' => [],
			'mounted_styles' => [],
			'mounted_languages' => [],
		]]);

		$text = $this->visibleText($html);
		self::assertStringContainsString('running', $text);
		self::assertStringContainsString('Stop', $text);
		self::assertStringNotContainsString('Start', $text);
	}

	public function testBoardUrlIsLinkedOnlyWhileRunning(): void
	{
		$board = [
			'name' => 'demo',
			'phpbb' => '3.3.19',
			'php' => '8.2',
			'db' => 'mariadb',
			'url' => 'http://localhost:8080/',
			'populate' => 'none',
			'mounted_extensions' => [],
			'mounted_styles' => [],
			'mounted_languages' => [],
		];

		$stoppedHtml = $this->renderDashboard([$board + ['status' => 'stopped']]);
		$runningHtml = $this->renderDashboard([$board + ['status' => 'running']]);

		self::assertStringNotContainsString('href="http://localhost:8080/"', $stoppedHtml);
		self::assertStringContainsString('href="http://localhost:8080/"', $runningHtml);
	}

	public function testBoardCreateRejectsHeavySqliteSeedPreset(): void
	{
		$root = $this->createTempProjectRoot();
		$json = $this->runWebApplicationWithCsrf($root, [
			'action' => 'board_create',
			'name' => 'demo',
			'phpbb' => '3.3.19',
			'db' => 'sqlite',
			'port' => '8080',
			'populate' => 'load-test',
		], true);
		$data = json_decode($json, true);

		self::assertIsArray($data);
		self::assertStringContainsString('SQLite boards support populate none, tiny, or development only.', $data['error']);
	}

	public function testBoardSeedRequiresPreset(): void
	{
		$root = $this->createTempProjectRoot();
		$json = $this->runWebApplicationWithCsrf($root, [
			'action' => 'board_seed',
			'name' => 'demo',
			'seed' => '1',
		], true);
		$data = json_decode($json, true);

		self::assertIsArray($data);
		self::assertStringContainsString('preset is required.', $data['error']);
	}

	public function testDoctorPostShowsResultsAndActivityOutput(): void
	{
		$root = $this->createTempProjectRoot();

		$json = $this->runWebApplicationWithCsrf($root, ['action' => 'doctor'], true);
		$data = json_decode($json, true);

		self::assertIsArray($data);
		self::assertStringContainsString('Doctor found', $data['notice'] ?: $data['error']);
		self::assertStringContainsString('QuickInstall requirements', $data['output']);
		self::assertStringContainsString('[OK] PHP 8+', $data['output']);
	}

	public function testDoctorFailureReturnsErrorAndActivityOutput(): void
	{
		$root = $this->createTempProjectRoot();
		$path = getenv('PATH');
		putenv('PATH=/path-that-does-not-exist');

		try
		{
			$json = $this->runWebApplicationWithCsrf($root, ['action' => 'doctor'], true);
		}
		finally
		{
			$path === false ? putenv('PATH') : putenv("PATH=$path");
		}
		$data = json_decode($json, true);

		self::assertIsArray($data);
		self::assertFalse($data['ok']);
		self::assertSame('', $data['notice']);
		self::assertStringContainsString('Doctor found', $data['error']);
		self::assertStringContainsString('View the Activity Log below for details.', $data['error']);
		self::assertStringContainsString('[FAIL] Git: not available', $data['output']);
	}

	public function testRenderShowsRegisteredSource(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		$sources = $project->readJson('sources.json', []);
		$sources['ticket-1234'] = [
			'version' => 'ticket/1234',
			'source_key' => 'ticket-1234',
			'branch' => 'ticket/1234',
			'phpbb_branch' => '3.3',
			'php' => '7.4',
			'status' => 'experimental',
			'type' => 'git',
			'url' => 'https://example.test/phpbb.git',
			'path' => $project->sourcePath('ticket-1234'),
			'detected_phpbb_version' => '3.3.0',
		];
		$project->writeJson('sources.json', $sources);

		$html = $this->runWebApplication($root);
		$text = $this->visibleText($html);

		self::assertStringContainsString('ticket-1234', $text);
		self::assertStringContainsString('ticket/1234', $text);
	}

	public function testInitPostCreatesWorkspace(): void
	{
		$root = $this->createTempProjectRoot();

		$html = $this->runWebApplicationWithCsrf($root, ['action' => 'init']);

		self::assertDirectoryExists($root . '/.qi');
		self::assertFileExists($root . '/.qi/boards.json');
		self::assertStringContainsString('Workspace initialized.', $this->visibleText($html));
	}

	public function testAjaxPostReturnsDashboardJson(): void
	{
		$root = $this->createTempProjectRoot();

		$json = $this->runWebApplicationWithCsrf($root, ['action' => 'init'], true);
		$data = json_decode($json, true);

		self::assertIsArray($data);
		self::assertTrue($data['ok']);
		self::assertSame('Workspace initialized.', $data['notice']);
		self::assertIsString($data['html']);
		self::assertNotSame('', $data['html']);
	}

	public function testAjaxResponseRemainsJsonWhenDashboardRenderingFails(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		$token = $this->csrfTokenFromRender($root);
		$project->writeJson('boards.json', ['broken' => 'not a board record']);

		$json = $this->runWebApplication($root, ['action' => 'unknown', 'qi_csrf_token' => $token], true);
		$data = json_decode($json, true);

		self::assertIsArray($data);
		self::assertFalse($data['ok']);
		self::assertNull($data['html']);
		self::assertStringNotContainsString('<br', $json);
	}

	public function testJsonResponseSubstitutesInvalidUtf8CommandOutput(): void
	{
		$application = new Application($this->createTempProjectRoot());
		$outputProperty = new \ReflectionProperty(Application::class, 'output');
		$outputProperty->setAccessible(true);
		$outputProperty->getValue($application)->write("invalid-\xB1-output");
		$renderJson = new \ReflectionMethod(Application::class, 'renderJson');
		$renderJson->setAccessible(true);

		ob_start();
		$renderJson->invoke($application);
		$json = (string) ob_get_clean();
		$data = json_decode($json, true);

		self::assertIsArray($data);
		self::assertStringContainsString('invalid-', $data['output']);
		self::assertStringContainsString('-output', $data['output']);
		self::assertIsString($data['html']);
	}

	public function testAjaxSourceRemoveDeletesSource(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		$this->addDownloadedSource($project, '3.3.19');

		$json = $this->runWebApplicationWithCsrf($root, [
			'action' => 'source_remove',
			'source' => '3.3.19',
		], true);
		$data = json_decode($json, true);

		self::assertIsArray($data);
		self::assertTrue($data['ok']);
		self::assertSame('Removed source: 3.3.19', $data['notice']);
		self::assertSame([], $project->readJson('sources.json', []));
	}

	public function testAjaxExtensionMountReturnsJsonError(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		$this->addDownloadedSource($project, '3.3.19');
		$project->appendBoard([
			'name' => 'demo',
			'phpbb' => '3.3.19',
			'phpbb_source' => '3.3.19',
			'phpbb_branch' => '3.3',
			'php' => '8.2',
			'db' => 'mariadb',
			'port' => 8081,
			'url' => 'http://localhost:8081/',
			'path' => $project->boardPath('demo'),
			'populate' => 'none',
			'debug' => false,
			'extensions' => [],
			'styles' => [],
			'languages' => [],
		]);

		$json = $this->runWebApplicationWithCsrf($root, [
			'action' => 'ext_mount',
			'board' => 'demo',
			'source' => 'customisations/missing-extension',
		], true);
		$data = json_decode($json, true);

		self::assertIsArray($data);
		self::assertFalse($data['ok']);
		self::assertStringContainsString('Extension path must be under customisations/', $data['error']);
	}

	public function testAjaxCustomisationMountRejectsUnknownType(): void
	{
		$root = $this->createTempProjectRoot();

		$json = $this->runWebApplicationWithCsrf($root, [
			'action' => 'customisation_mount',
			'type' => 'widget',
		], true);
		$data = json_decode($json, true);

		self::assertIsArray($data);
		self::assertFalse($data['ok']);
		self::assertStringContainsString('type must be one of: extension, style, language.', $data['error']);
	}

	public function testRenderShowsProjectRelativeSourcePaths(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		$this->addDownloadedSource($project, '3.3.19');

		$html = $this->runWebApplication($root);
		$text = $this->visibleText($html);

		self::assertStringContainsString('/' . basename($root) . '/.qi/sources/phpbb-3.3.19', $text);
		self::assertStringNotContainsString($root . '/.qi/sources/phpbb-3.3.19', $text);
	}

	public function testRenderShowsCachedUpdateBanner(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		$currentVersion = (new UpdateService($project))->currentVersion();
		$availableVersion = $this->newerVersionThan($currentVersion);
		$project->writeJson('cache/update-check.json', [
			'checked_at' => time(),
			'current_version' => $currentVersion,
			'update' => ['current' => $availableVersion, 'download' => 'https://example.com/download'],
			'error' => null,
		]);

		$html = $this->runWebApplication($root);
		$text = $this->visibleText($html);

		self::assertStringContainsString("QuickInstall $availableVersion available", $text);
		self::assertStringContainsString('https://example.com/download', $html);
	}

	public function testDockerConnectivityErrorsAreFriendlyForWebUi(): void
	{
		$application = new Application($this->createTempProjectRoot());
		$method = new \ReflectionMethod(Application::class, 'friendlyError');
		$method->setAccessible(true);

		$message = $method->invoke($application, "Command failed with exit code 1: docker\nCommand output:\nunable to get image 'mariadb:10.11': failed to connect to the docker API at unix:///Users/matt/.docker/run/docker.sock; check if the path is correct and if the daemon is running");

		self::assertSame('Check that Docker Desktop is running and that the docker command works in this terminal.', $message);
	}

	public function testPostRejectsMissingCsrfToken(): void
	{
		$root = $this->createTempProjectRoot();
		$script = $this->csrfScript($root, ['action' => 'init'], [], 'secret');

		$result = $this->runPhpScript($script);

		self::assertSame(0, $result['exit_code']);
		self::assertStringContainsString('QuickInstall UI form token is missing or invalid', $result['output']);
		self::assertDirectoryDoesNotExist($root . '/.qi');
	}

	public function testCsrfProtectedPostAcceptsMatchingToken(): void
	{
		$root = $this->createTempProjectRoot();
		$script = $this->csrfScript($root, ['action' => 'init', 'qi_csrf_token' => 'secret'], [], 'secret');

		$result = $this->runPhpScript($script);

		self::assertSame(0, $result['exit_code']);
		self::assertStringContainsString('Workspace initialized.', $result['output']);
		self::assertFileExists($root . '/.qi/boards.json');
	}

	public function testCsrfProtectedPostRejectsNonLocalOrigin(): void
	{
		$root = $this->createTempProjectRoot();
		$script = $this->csrfScript($root, ['action' => 'init', 'qi_csrf_token' => 'secret'], ['HTTP_ORIGIN' => 'https://example.com'], 'secret');

		$result = $this->runPhpScript($script);

		self::assertSame(0, $result['exit_code']);
		self::assertStringContainsString('QuickInstall UI only accepts local form submissions', $result['output']);
		self::assertDirectoryDoesNotExist($root . '/.qi');
	}

	private function runWebApplicationWithCsrf(string $root, array $post = [], bool $ajax = false): string
	{
		$post['qi_csrf_token'] = $this->csrfTokenFromRender($root);
		return $this->runWebApplication($root, $post, $ajax);
	}

	private function csrfTokenFromRender(string $root): string
	{
		$html = $this->runWebApplication($root);
		if (!preg_match('/name="qi_csrf_token" value="([^"]+)"/', $html, $matches))
		{
			throw new \RuntimeException('Unable to find CSRF token in web UI render.');
		}

		return html_entity_decode($matches[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}

	private function visibleText(string $html): string
	{
		$text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
		return trim((string) preg_replace('/\s+/', ' ', $text));
	}

	private function renderDashboard(array $boards): string
	{
		$application = new Application($this->createTempProjectRoot());
		$renderTemplate = new \ReflectionMethod(Application::class, 'renderTemplate');
		$renderTemplate->setAccessible(true);

		return (string) $renderTemplate->invoke($application, 'dashboard.php', [
			'notice' => '',
			'error' => '',
			'output' => '',
			'csrfToken' => 'test-token',
			'update' => null,
			'metrics' => [],
			'boards' => $boards,
			'sources' => [],
			'versionOptions' => [],
			'dbOptions' => ['mariadb', 'mysql', 'postgres', 'sqlite'],
			'populateOptions' => ['none'],
			'presetOptions' => ['tiny'],
			'seedActionOptions' => ['seed', 'replace', 'reset'],
		]);
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

	private function csrfScript(string $root, array $post, array $server, string $token): string
	{
		$script = $this->createTempProjectRoot() . '/web-csrf-test.php';
		$server += [
			'REQUEST_METHOD' => 'POST',
			'REMOTE_ADDR' => '127.0.0.1',
		];
		file_put_contents($script, "<?php\n"
			. "session_save_path(__DIR__);\n"
			. "session_start();\n"
			. '$_SESSION[\'qi_csrf_token\'] = ' . var_export($token, true) . ";\n"
			. '$_SERVER = ' . var_export($server, true) . ";\n"
			. '$_POST = ' . var_export($post, true) . ";\n"
			. '$_GET = [];' . "\n"
			. "require " . var_export(dirname(__DIR__, 2) . '/src/QuickInstall/Sandbox/bootstrap.php', true) . ";\n"
			. "require " . var_export(dirname(__DIR__, 2) . '/src/QuickInstall/Sandbox/Web/Application.php', true) . ";\n"
			. "(new QuickInstall\\Sandbox\\Web\\Application(" . var_export($root, true) . "))->run();\n");

		return $script;
	}

	private function runPhpScript(string $script): array
	{
		$descriptor = [
			0 => ['pipe', 'r'],
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		];
		$process = proc_open([PHP_BINARY, $script], $descriptor, $pipes, dirname(__DIR__, 2));
		if (!is_resource($process))
		{
			throw new \RuntimeException('Unable to start PHP subprocess.');
		}

		fclose($pipes[0]);
		$output = stream_get_contents($pipes[1]) ?: '';
		$error = stream_get_contents($pipes[2]) ?: '';
		fclose($pipes[1]);
		fclose($pipes[2]);

		return [
			'exit_code' => proc_close($process),
			'output' => $output . $error,
		];
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
				'php' => '8.2',
				'status' => 'supported',
				'type' => 'composer',
				'package' => 'phpbb/phpbb',
				'url' => null,
				'path' => $sourcePath,
			],
		]);
	}
}
