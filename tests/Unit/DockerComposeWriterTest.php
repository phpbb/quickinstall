<?php

namespace QuickInstall\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\DockerComposeWriter;
use QuickInstall\Sandbox\Project;
use QuickInstall\Tests\Support\TempProjectTrait;

class DockerComposeWriterTest extends TestCase
{
	use TempProjectTrait;

	public function testWritesMysqlRuntimeFiles(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		mkdir($project->boardPath('demo'), 0775, true);

		$paths = (new DockerComposeWriter($project))->write('demo', $this->config(['db' => 'mysql']));

		self::assertFileExists($paths['compose']);
		self::assertFileExists($paths['install_config']);
		self::assertFileExists($paths['dockerfile']);
		self::assertFileExists($paths['entrypoint']);
		self::assertStringContainsString('image: mysql:8.0', file_get_contents($paths['compose']));
		self::assertStringNotContainsString('default-authentication-plugin', file_get_contents($paths['compose']));
		self::assertStringContainsString('server_port: 8081', file_get_contents($paths['install_config']));
		self::assertStringContainsString('docker-php-ext-install mysqli pdo_mysql', file_get_contents($paths['dockerfile']));
		self::assertStringContainsString('apache2-foreground', file_get_contents($paths['entrypoint']));
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

	public function testWritesMariadbRuntimeFiles(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		mkdir($project->boardPath('maria'), 0775, true);

		$paths = (new DockerComposeWriter($project))->write('maria', $this->config(['db' => 'mariadb']));
		$compose = file_get_contents($paths['compose']);

		self::assertStringContainsString('image: mariadb:10.11', $compose);
		self::assertStringNotContainsString('default-authentication-plugin', $compose);
		self::assertStringContainsString('dbms: mysqli', file_get_contents($paths['install_config']));
	}

	public function testWritesPostgresRuntimeFiles(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		mkdir($project->boardPath('pg'), 0775, true);

		$paths = (new DockerComposeWriter($project))->write('pg', $this->config(['db' => 'postgres']));

		self::assertStringContainsString('image: postgres:16', file_get_contents($paths['compose']));
		self::assertStringContainsString('dbms: postgres', file_get_contents($paths['install_config']));
		self::assertStringContainsString('docker-php-ext-install pgsql pdo_pgsql', file_get_contents($paths['dockerfile']));
	}

	public function testWritesSqliteRuntimeFiles(): void
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		mkdir($project->boardPath('lite'), 0775, true);

		$paths = (new DockerComposeWriter($project))->write('lite', $this->config(['db' => 'sqlite']));

		self::assertStringContainsString('image: busybox', file_get_contents($paths['compose']));
		self::assertStringContainsString('dbms: sqlite3', file_get_contents($paths['install_config']));
		self::assertStringContainsString('dbhost: "/var/www/html/store/phpbb.sqlite"', file_get_contents($paths['install_config']));
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
}
