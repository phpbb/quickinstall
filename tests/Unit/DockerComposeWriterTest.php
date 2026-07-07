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
		self::assertStringContainsString('apache2-foreground', file_get_contents($paths['entrypoint']));

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

	private function config(array $overrides = []): array
	{
		return $overrides + [
			'phpbb' => '3.3.14',
			'phpbb_source' => '3.3.14',
			'php' => '8.1',
			'db' => 'mariadb',
			'port' => 8081,
			'populate' => 'none',
			'admin_name' => 'admin',
			'admin_pass' => 'password',
			'admin_email' => 'admin@example.test',
			'board_email' => 'board@example.test',
			'extensions' => [
				'acme/demo' => ['mode' => 'bind', 'source' => '/tmp/acme-demo'],
			],
			'styles' => [
				'clean' => ['mode' => 'bind', 'source' => '/tmp/clean-style'],
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
}
