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

		self::assertStringContainsString('QuickInstall Dashboard', $html);
		self::assertStringContainsString('QuickInstall + Docker', $html);
		self::assertStringContainsString('Create board', $html);
		self::assertStringContainsString('Run Doctor', $html);
		self::assertStringContainsString('id="icon-boards"', $html);
		self::assertStringContainsString('href="#icon-activity"', $html);
		self::assertStringContainsString('class="icon" aria-hidden="true"', $html);
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
		self::assertStringContainsString('<option value="3.3.x">', $html);
		self::assertStringContainsString('title="phpBB selector to fetch or reuse.', $html);
		self::assertStringContainsString('title="Allow the path field to point outside the customisations directory.', $html);
		self::assertStringContainsString('Relative to <code>customisations/</code>', $html);
		self::assertStringNotContainsString('<option value="3.0.x">', $html);
		self::assertStringNotContainsString('<style>', $html);
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
		self::assertStringNotContainsString('doctor-results', $data['html']);
	}

	public function testDashboardJavascriptTracksConcurrentActions(): void
	{
		$javascript = file_get_contents(dirname(__DIR__, 2) . '/public/assets/sandbox-ui.js');

		self::assertIsString($javascript);
		self::assertStringContainsString('const pendingActions = new Map();', $javascript);
		self::assertStringContainsString('const active = pendingActions.size > 0;', $javascript);
		self::assertStringContainsString('syncPendingActions();', $javascript);
		self::assertStringContainsString('pendingActions.delete(actionId);', $javascript);
	}

	public function testDoctorFailureUsesErrorToastAndPointsToActivityLog(): void
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
		self::assertStringContainsString('<p class="error">', $data['html']);
	}

	public function testRenderShowsRegisteredSourceOptions(): void
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

		self::assertStringContainsString('<option value="ticket-1234">', $html);
	}

	public function testInitPostCreatesWorkspace(): void
	{
		$root = $this->createTempProjectRoot();

		$html = $this->runWebApplicationWithCsrf($root, ['action' => 'init']);

		self::assertDirectoryExists($root . '/.qi');
		self::assertFileExists($root . '/.qi/boards.json');
		self::assertStringContainsString('Workspace initialized.', $html);
		self::assertStringContainsString('class="toast-stack" role="status" aria-live="polite"', $html);
	}

	public function testAjaxPostReturnsDashboardJson(): void
	{
		$root = $this->createTempProjectRoot();

		$json = $this->runWebApplicationWithCsrf($root, ['action' => 'init'], true);
		$data = json_decode($json, true);

		self::assertIsArray($data);
		self::assertTrue($data['ok']);
		self::assertSame('Workspace initialized.', $data['notice']);
		self::assertStringContainsString('status-strip', $data['html']);
		self::assertStringContainsString('activity-log', $data['html']);
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
		self::assertStringContainsString('status-strip', $data['html']);
	}

	public function testAjaxSourceRemoveDeletesSource(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		$this->addDownloadedSource($project, '3.3.14');

		$json = $this->runWebApplicationWithCsrf($root, [
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

	public function testAjaxExtensionMountReturnsJsonError(): void
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

	public function testRenderShowsProjectRelativeSourcePaths(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		$this->addDownloadedSource($project, '3.3.14');

		$html = $this->runWebApplication($root);

		self::assertStringContainsString('/' . basename($root) . '/.qi/sources/phpbb-3.3.14', $html);
		self::assertStringNotContainsString($root . '/.qi/sources/phpbb-3.3.14', $html);
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

		self::assertStringContainsString('class="update-banner"', $html);
		self::assertStringContainsString("QuickInstall $availableVersion available", $html);
		self::assertStringContainsString('href="https://example.com/download"', $html);
		self::assertStringContainsString('data-dismiss-update', $html);
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
