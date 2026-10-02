<?php

namespace QuickInstall\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\DockerComposeWriter;
use QuickInstall\Sandbox\Project;
use QuickInstall\Tests\Support\TempProjectTrait;

class DockerComposeWriterTest extends TestCase
{
	use TempProjectTrait;

	/**
	 * @dataProvider databaseRuntimeProvider
	 */
	public function testWritesDatabaseRuntimeFiles(string $board, string $db, array $expectedContains, array $expectedNotContains = []): void
	{
		[$project, $paths] = $this->writeBoard($board, ['db' => $db]);

		self::assertFileExists($paths['compose']);
		self::assertFileExists($paths['install_config']);
		self::assertFileExists($paths['dockerfile']);
		self::assertFileExists($paths['entrypoint']);
		self::assertFileExists($paths['apache_config']);
		$entrypoint = file_get_contents($paths['entrypoint']);
		self::assertStringContainsString('apache2-foreground', $entrypoint);
		self::assertStringContainsString('new DateTimeZone((string) getenv("QUICKINSTALL_BOARD_TIMEZONE"))', $entrypoint);
		self::assertStringContainsString('config:set board_timezone "$QUICKINSTALL_BOARD_TIMEZONE"', $entrypoint);
		self::assertStringContainsString('config:set board_timezone UTC', $entrypoint);
		self::assertStringContainsString('config:set cookie_path "${QUICKINSTALL_COOKIE_PATH:-/}"', $entrypoint);
		self::assertStringContainsString('RewriteBase ${QUICKINSTALL_REWRITE_BASE}', $entrypoint);
		self::assertStringContainsString("is unsupported by this PHP runtime; using UTC.", $entrypoint);
		self::assertStringContainsString('QUICKINSTALL_BOARD_TIMEZONE: "America/Los_Angeles"', file_get_contents($paths['compose']));
		self::assertStringContainsString('QUICKINSTALL_COOKIE_PATH: "/demo/"', file_get_contents($paths['compose']));
		self::assertStringContainsString('QUICKINSTALL_REWRITE_BASE: "/demo/"', file_get_contents($paths['compose']));
		self::assertStringNotContainsString("\t", file_get_contents($paths['compose']));
		self::assertStringContainsString('a2enmod rewrite', file_get_contents($paths['dockerfile']));
		self::assertStringContainsString('a2enmod rewrite headers', file_get_contents($paths['dockerfile']));

		$output = file_get_contents($paths['compose']) . "\n" . file_get_contents($paths['install_config']) . "\n" . file_get_contents($paths['dockerfile']);
		foreach ($expectedContains as $expected)
		{
			self::assertStringContainsString($expected, $output);
		}
		foreach ($expectedNotContains as $unexpected)
		{
			self::assertStringNotContainsString($unexpected, $output);
		}
	}

	public function testLegacyPhpMysqlRuntimeUsesNativePasswordAuth(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		mkdir($project->boardPath('legacy-mysql'), 0775, true);

		$paths = (new DockerComposeWriter($project))->write('legacy-mysql', $this->config([
			'phpbb' => '3.2.0',
			'phpbb_source' => '3.2.0',
			'php' => '7.1',
			'db' => 'mysql',
		]));

		self::assertStringContainsString('image: mysql:8.0', file_get_contents($paths['compose']));
		self::assertStringContainsString('command: ["--default-authentication-plugin=mysql_native_password"]', file_get_contents($paths['compose']));
		self::assertStringContainsString('dbms: mysqli', file_get_contents($paths['install_config']));
	}

	public function databaseRuntimeProvider(): array
	{
		return [
			'mysql' => [
				'demo',
				'mysql',
				[
					'image: mysql:8.0',
					'server_name: "localhost"',
					'script_path: "/demo"',
					'server_port: 8081',
					'dbms: mysqli',
					'docker-php-ext-install mysqli pdo_mysql',
				],
				['default-authentication-plugin'],
			],
			'mariadb' => [
				'maria',
				'mariadb',
				[
					'image: mariadb:10.11',
					'dbms: mysqli',
					'docker-php-ext-install mysqli pdo_mysql',
				],
				['default-authentication-plugin'],
			],
			'postgres' => [
				'pg',
				'postgres',
				[
					'image: postgres:16',
					'PGDATA: "/var/lib/postgresql/data/pgdata"',
					"psql -U phpbb -d phpbb -tAc 'SELECT 1'",
					'dbms: postgres',
					'docker-php-ext-install pgsql pdo_pgsql',
				],
			],
			'sqlite' => [
				'lite',
				'sqlite',
				[
					'image: busybox',
					'dbms: sqlite3',
					'dbhost: "/var/www/html/store/phpbb.sqlite"',
				],
			],
			'unknown db falls back to mariadb-compatible mysqli' => [
				'fallback',
				'unknown',
				[
					'image: mariadb:10.11',
					'dbms: mysqli',
					'docker-php-ext-install mysqli pdo_mysql',
				],
			],
		];
	}

	public function testPhp71RuntimeDoesNotRequireSodium(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		mkdir($project->boardPath('legacy'), 0775, true);

		$paths = (new DockerComposeWriter($project))->write('legacy', $this->config([
			'phpbb' => '3.2.0',
			'phpbb_source' => '3.2.0',
			'php' => '7.1',
		]));

		$dockerfile = file_get_contents($paths['dockerfile']);

		self::assertStringContainsString('PHP_VERSION: "7.1"', file_get_contents($paths['compose']));
		self::assertStringNotContainsString('docker-php-ext-install sodium', $dockerfile);
		self::assertStringNotContainsString('libsodium-dev', $dockerfile);
		self::assertStringNotContainsString('PDO zip zlib sodium json mbstring', $dockerfile);
		self::assertStringContainsString('PDO zip zlib json mbstring', $dockerfile);
	}

	public function testQuotesYamlSignificantInstallerValues(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		mkdir($project->boardPath('demo'), 0775, true);

		$paths = (new DockerComposeWriter($project))->write('demo', $this->config([
			'admin_name' => '*admin: #1',
			'admin_pass' => 'pa:ss # "quoted"',
			'admin_email' => "admin\t#tag@example.test",
		]));

		$installConfig = file_get_contents($paths['install_config']);

		self::assertStringContainsString('    name: "*admin: #1"', $installConfig);
		self::assertStringContainsString('    password: "pa:ss # \"quoted\""', $installConfig);
		self::assertStringContainsString('    email: "admin\\t#tag@example.test"', $installConfig);
		self::assertStringContainsString('    name: "demo"', $installConfig);
	}

	public function testExistingBoardConfigWithoutServerNameKeepsLocalhost(): void
	{
		$config = $this->config();
		unset($config['server_name']);
		unset($config['script_path'], $config['scoped_path']);

		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		mkdir($project->boardPath('demo'), 0775, true);
		$paths = (new DockerComposeWriter($project))->write('demo', $config);

		self::assertStringContainsString('server_name: "localhost"', file_get_contents($paths['install_config']));
		self::assertStringContainsString('script_path: "/"', file_get_contents($paths['install_config']));
		self::assertStringNotContainsString('Alias ', file_get_contents($paths['apache_config']));
		self::assertStringContainsString('Header always set Cache-Control "no-store, no-cache, must-revalidate, max-age=0"', file_get_contents($paths['apache_config']));
	}

	public function testExistingPostgresConfigKeepsRootDataDirectory(): void
	{
		$config = $this->config(['db' => 'postgres']);
		unset($config['postgres_data_subdir']);

		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		mkdir($project->boardPath('demo'), 0775, true);
		$paths = (new DockerComposeWriter($project))->write('demo', $config);
		$compose = file_get_contents($paths['compose']);

		self::assertStringNotContainsString('PGDATA:', $compose);
		self::assertStringContainsString("psql -U phpbb -d phpbb -tAc 'SELECT 1'", $compose);
	}

	public function testScopedBoardWritesApacheRoute(): void
	{
		[, $paths] = $this->writeBoard('demo');
		$apacheConfig = file_get_contents($paths['apache_config']);

		self::assertStringContainsString('RedirectMatch 302 "^/$" "/demo/"', $apacheConfig);
		self::assertStringContainsString('RedirectMatch 302 "^/demo$" "/demo/"', $apacheConfig);
		self::assertStringContainsString('Alias "/demo/" "/var/www/html/"', $apacheConfig);
		self::assertStringContainsString('LimitRequestFieldSize 65536', $apacheConfig);
		self::assertStringContainsString('Header always set Cache-Control "no-store, no-cache, must-revalidate, max-age=0"', $apacheConfig);
		self::assertStringContainsString('Header always set Expires "0"', $apacheConfig);
		self::assertStringContainsString('target: "/etc/apache2/conf-enabled/quickinstall.conf"', file_get_contents($paths['compose']));
		self::assertStringContainsString('source: "/tmp/de-language"', file_get_contents($paths['compose']));
		self::assertStringContainsString('target: "/var/www/html/language/de"', file_get_contents($paths['compose']));
	}

	public function testEntrypointExcludesBindMountsFromOwnershipChanges(): void
	{
		[, $paths] = $this->writeBoard('demo', [
			'extensions' => [
				'acme/bound' => ['mode' => 'bind', 'source' => '/tmp/acme-bound'],
				'acme/copied' => ['mode' => 'copy', 'source' => '/tmp/acme-copied'],
				'acme/missing' => ['mode' => 'bind', 'source' => ''],
			],
			'styles' => [
				'bound style' => ['mode' => 'bind', 'source' => '/tmp/bound-style'],
				'copied' => ['mode' => 'copy', 'source' => '/tmp/copied-style'],
			],
			'languages' => [
				'de' => ['mode' => 'bind', 'source' => '/tmp/de-language'],
				'fr' => ['mode' => 'copy', 'source' => '/tmp/fr-language'],
			],
		]);

		$entrypoint = file_get_contents($paths['entrypoint']);

		self::assertSame(2, substr_count($entrypoint, 'find /var/www/html'));
		self::assertSame(2, substr_count($entrypoint, "-path '/var/www/html/ext/acme/bound' -prune -o"));
		self::assertSame(2, substr_count($entrypoint, "-path '/var/www/html/styles/bound style' -prune -o"));
		self::assertSame(2, substr_count($entrypoint, "-path '/var/www/html/language/de' -prune -o"));
		self::assertStringNotContainsString('/var/www/html/ext/acme/copied', $entrypoint);
		self::assertStringNotContainsString('/var/www/html/ext/acme/missing', $entrypoint);
		self::assertStringNotContainsString('/var/www/html/styles/copied', $entrypoint);
		self::assertStringNotContainsString('/var/www/html/language/fr', $entrypoint);
		self::assertSame(2, substr_count($entrypoint, '-exec chown www-data:www-data {} +'));
	}

	public function testEntrypointQuotesBindMountTargetsForPosixShell(): void
	{
		[, $paths] = $this->writeBoard('demo', [
			'styles' => [
				"designer's style" => ['mode' => 'bind', 'source' => '/tmp/designer-style'],
			],
		]);

		self::assertStringContainsString(
			"-path '/var/www/html/styles/designer'\"'\"'s style' -prune -o",
			file_get_contents($paths['entrypoint'])
		);
	}

	private function config(array $overrides = []): array
	{
		return $overrides + [
			'phpbb' => '3.3.14',
			'phpbb_source' => '3.3.14',
			'php' => '8.1',
			'db' => 'mariadb',
			'port' => 8081,
			'server_name' => 'localhost',
			'script_path' => '/demo',
			'cookie_path' => '/demo/',
			'scoped_path' => true,
			'postgres_data_subdir' => true,
			'populate' => 'none',
			'admin_name' => 'admin',
			'admin_pass' => 'password',
			'admin_email' => 'admin@example.test',
			'board_email' => 'board@example.test',
			'board_timezone' => 'America/Los_Angeles',
			'extensions' => [
				'acme/demo' => ['mode' => 'bind', 'source' => '/tmp/acme-demo'],
			],
			'styles' => [
				'clean' => ['mode' => 'bind', 'source' => '/tmp/clean-style'],
			],
			'languages' => [
				'de' => ['mode' => 'bind', 'source' => '/tmp/de-language'],
			],
		];
	}

	private function writeBoard(string $name, array $overrides = []): array
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		mkdir($project->boardPath($name), 0775, true);

		return [$project, (new DockerComposeWriter($project))->write($name, $this->config($overrides))];
	}

	public function testGeneratedRuntimeFilesAlwaysUseUnixLineEndings(): void
	{
		$project = new Project($this->createTempProjectRoot());
		$writer = new DockerComposeWriter($project);
		$method = new \ReflectionMethod(DockerComposeWriter::class, 'writeFile');
		$method->setAccessible(true);
		$path = $project->rootPath('entrypoint.sh');

		$method->invoke($writer, $path, "set -eu\r\necho ready\r\n");

		self::assertSame("set -eu\necho ready\n", file_get_contents($path));
	}
}
