<?php

namespace QuickInstall\Modern;

class DockerComposeWriter
{
	private Project $project;

	public function __construct(Project $project)
	{
		$this->project = $project;
	}

	public function write(string $name, array $config): array
	{
		$runtimeDir = $this->project->workspacePath('runtime/' . $name);
		if (!is_dir($runtimeDir) && !mkdir($runtimeDir, 0775, true))
		{
			throw new \RuntimeException("Unable to create runtime directory: $runtimeDir");
		}

		$installConfig = $runtimeDir . '/install-config.yml';
		$compose = $runtimeDir . '/compose.yml';
		$dockerfile = $runtimeDir . '/Dockerfile';
		$entrypoint = $runtimeDir . '/entrypoint.sh';

		file_put_contents($installConfig, $this->installConfig($name, $config));
		file_put_contents($compose, $this->compose($name, $config));
		file_put_contents($dockerfile, $this->dockerfile($config));
		file_put_contents($entrypoint, $this->entrypoint($config));
		chmod($entrypoint, 0755);

		return [
			'compose' => $compose,
			'install_config' => $installConfig,
			'dockerfile' => $dockerfile,
			'entrypoint' => $entrypoint,
		];
	}

	private function installConfig(string $name, array $config): string
	{
		$dbms = $this->dbms($config['db']);
		$dbhost = $config['db'] === 'sqlite' ? '/var/www/html/store/phpbb.sqlite' : 'db';
		$dbport = $config['db'] === 'postgres' ? '5432' : ($config['db'] === 'sqlite' ? '' : '3306');

		return <<<YAML
installer:
  admin:
    name: {$config['admin_name']}
    password: {$config['admin_pass']}
    email: {$config['admin_email']}
  board:
    lang: en
    name: "{$name}"
    description: "QuickInstall sandbox"
  database:
    dbms: $dbms
    dbhost: "$dbhost"
    dbport: "$dbport"
    dbuser: phpbb
    dbpasswd: phpbb
    dbname: phpbb
    table_prefix: phpbb_
  email:
    enabled: false
    smtp_delivery: false
    smtp_host: ""
    smtp_port: 25
    smtp_auth: PLAIN
    smtp_user: ""
    smtp_pass: ""
  server:
    cookie_secure: false
    server_protocol: http://
    force_server_vars: true
    server_name: localhost
    server_port: {$config['port']}
    script_path: /
  extensions: []

YAML;
	}

	private function compose(string $name, array $config): string
	{
		$dbService = $this->databaseService($config['db'], $name);
		$sourcePath = $this->project->sourcePath($config['phpbb_source'] ?? $config['phpbb']);
		$boardPath = $this->project->boardPath($name);
		$dbPath = $this->project->workspacePath('db/' . $name);
		if (!is_dir($dbPath) && !mkdir($dbPath, 0775, true))
		{
			throw new \RuntimeException("Unable to create database directory: $dbPath");
		}

		return <<<YAML
services:
  web:
    build:
      context: .
      args:
        PHP_VERSION: "{$config['php']}"
    working_dir: /var/www/html
    ports:
      - "{$config['port']}:80"
    volumes:
      - {$sourcePath}:/opt/phpbb-source:ro
      - {$boardPath}:/var/www/html
      - ./install-config.yml:/opt/quickinstall/install-config.yml:ro
      - ./entrypoint.sh:/opt/quickinstall/entrypoint.sh:ro
    entrypoint: ["/bin/sh", "/opt/quickinstall/entrypoint.sh"]
    depends_on:
      db:
        condition: service_healthy
    environment:
      QUICKINSTALL_PHPBB_VERSION: "{$config['phpbb']}"
      QUICKINSTALL_POPULATE: "{$config['populate']}"

$dbService

YAML;
	}

	private function dockerfile(array $config): string
	{
		$extensionInstall = $config['db'] === 'postgres' ? 'docker-php-ext-install pgsql pdo_pgsql' : 'docker-php-ext-install mysqli pdo_mysql';

		return <<<DOCKERFILE
ARG PHP_VERSION=8.1
FROM php:\${PHP_VERSION}-apache

RUN apt-get update \\
    && apt-get install -y --no-install-recommends git unzip libpq-dev \\
    && $extensionInstall \\
    && rm -rf /var/lib/apt/lists/*

DOCKERFILE;
	}

	private function entrypoint(array $config): string
	{
		return <<<'SH'
set -eu

if [ ! -f /var/www/html/common.php ]; then
	if [ ! -f /opt/phpbb-source/common.php ]; then
		echo "phpBB source missing at /opt/phpbb-source. Run qi source:fetch first."
		exit 1
	fi

	cp -R /opt/phpbb-source/. /var/www/html/
	chown -R www-data:www-data /var/www/html
fi

if [ -f /var/www/html/config.php ] && [ ! -s /var/www/html/config.php ]; then
	rm -f /var/www/html/config.php
fi

if [ ! -s /var/www/html/config.php ] && [ -f /var/www/html/install/phpbbcli.php ]; then
	php /var/www/html/install/phpbbcli.php install /opt/quickinstall/install-config.yml
	rm -rf /var/www/html/install
	chown -R www-data:www-data /var/www/html
fi

apache2-foreground
SH;
	}

	private function databaseService(string $db, string $name): string
	{
		$dbPath = '../../db/' . $name;
		switch ($db)
		{
			case 'mysql':
				$image = 'mysql:8.0';
				break;
			case 'postgres':
				return <<<YAML
  db:
    image: postgres:16
    environment:
      POSTGRES_DB: phpbb
      POSTGRES_USER: phpbb
      POSTGRES_PASSWORD: phpbb
    volumes:
      - $dbPath:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U phpbb -d phpbb"]
      interval: 5s
      timeout: 5s
      retries: 20
YAML;
			case 'sqlite':
				return <<<YAML
  db:
    image: busybox
    command: ["sh", "-c", "sleep infinity"]
    healthcheck:
      test: ["CMD", "true"]
      interval: 5s
      timeout: 5s
      retries: 1
YAML;
			case 'mariadb':
			default:
				$image = 'mariadb:10.11';
				break;
		}

		return <<<YAML
  db:
    image: $image
    environment:
      MYSQL_DATABASE: phpbb
      MYSQL_USER: phpbb
      MYSQL_PASSWORD: phpbb
      MYSQL_ROOT_PASSWORD: root
    volumes:
      - $dbPath:/var/lib/mysql
    healthcheck:
      test: ["CMD-SHELL", "mysqladmin ping -h localhost -u root -proot"]
      interval: 5s
      timeout: 5s
      retries: 20
YAML;
	}

	private function dbms(string $db): string
	{
		switch ($db)
		{
			case 'postgres':
				return 'postgres';
			case 'sqlite':
				return 'sqlite3';
			case 'mysql':
			case 'mariadb':
			default:
				return 'mysqli';
		}
	}
}
