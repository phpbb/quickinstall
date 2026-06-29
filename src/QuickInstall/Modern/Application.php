<?php

namespace QuickInstall\Modern;

class Application
{
	private string $root;
	private Project $project;

	public function __construct(string $root)
	{
		$this->root = $root;
		$this->project = new Project($root);
	}

	public function run(array $argv): int
	{
		array_shift($argv);
		$command = array_shift($argv) ?: 'help';

		try
		{
			switch ($command)
			{
				case 'help':
				case '--help':
				case '-h':
					$this->help();
					return 0;

				case 'init':
					return $this->init();

				case 'source:add':
					return $this->sourceAdd($argv);

				case 'source:list':
					return $this->sourceList();

				case 'source:fetch':
					return $this->sourceFetch($argv);

				case 'board:create':
					return $this->boardCreate($argv);

				case 'board:list':
					return $this->boardList();

				case 'board:start':
					return $this->boardStart($argv);

				case 'board:stop':
					return $this->boardStop($argv);

				case 'board:destroy':
					return $this->boardDestroy($argv);

				case 'board:seed':
					return $this->boardSeed($argv);

				default:
					fwrite(STDERR, "Unknown command: $command\n\n");
					$this->help();
					return 1;
			}
		}
		catch (\InvalidArgumentException $e)
		{
			fwrite(STDERR, $e->getMessage() . "\n");
			return 1;
		}
		catch (\RuntimeException $e)
		{
			fwrite(STDERR, $e->getMessage() . "\n");
			return 1;
		}
	}

	private function init(): int
	{
		$this->project->init();
		echo "Created .qi workspace\n";
		return 0;
	}

	private function sourceAdd(array $args): int
	{
		$cli = CommandLine::parse($args);
		$version = $cli->argument(0);
		if ($version === null)
		{
			throw new \InvalidArgumentException('Usage: qi source:add <version|branch> [--git] [--url URL]');
		}

		$this->project->init();
		$source = new SourceProvider($this->project);
		$record = $source->add($version, $cli->has('git') ? 'git' : 'composer', $cli->option('url'));

		echo "Registered phpBB source {$record['version']} ({$record['type']})\n";
		echo "Next: fetch it with Composer/Git into {$record['path']}\n";
		return 0;
	}

	private function sourceList(): int
	{
		$sources = $this->project->readJson('sources.json', []);

		if (!$sources)
		{
			echo "No sources registered\n";
			return 0;
		}

		foreach ($sources as $source)
		{
			echo "{$source['version']}\t{$source['type']}\t{$source['path']}\n";
		}

		return 0;
	}

	private function sourceFetch(array $args): int
	{
		$cli = CommandLine::parse($args);
		$version = $cli->argument(0);
		if ($version === null)
		{
			throw new \InvalidArgumentException('Usage: qi source:fetch <version|branch>');
		}

		$sources = $this->project->readJson('sources.json', []);
		if (!isset($sources[$version]))
		{
			throw new \InvalidArgumentException("Source is not registered: $version");
		}

		$source = new SourceProvider($this->project);
		$source->fetch($sources[$version]);

		echo "Fetched phpBB source: {$sources[$version]['path']}\n";
		return 0;
	}

	private function boardCreate(array $args): int
	{
		$cli = CommandLine::parse($args);
		$name = $cli->argument(0);
		if ($name === null)
		{
			throw new \InvalidArgumentException('Usage: qi board:create <name> [--phpbb VERSION] [--db mariadb|mysql|postgres|sqlite] [--port PORT] [--populate PRESET]');
		}

		$version = $cli->option('phpbb', 'latest');
		$db = $cli->option('db', 'mariadb');
		$port = (int) $cli->option('port', '8080');
		$populate = $cli->option('populate', 'none');

		$this->project->init();
		$matrix = new VersionMatrix();
		$runtime = $matrix->runtimeFor($version);
		$sourcePath = $this->project->sourcePath($version);
		if (!file_exists($sourcePath . '/common.php'))
		{
			throw new \RuntimeException("phpBB source is missing for $version. Run: php bin/qi source:fetch $version");
		}

		$boardDir = $this->project->boardPath($name);
		if (!is_dir($boardDir) && !mkdir($boardDir, 0775, true))
		{
			throw new \RuntimeException("Unable to create board directory: $boardDir");
		}

		$writer = new DockerComposeWriter($this->project);
		$paths = $writer->write($name, [
			'phpbb' => $version,
			'php' => $runtime['php'],
			'db' => $db,
			'port' => $port,
			'populate' => $populate,
			'admin_name' => 'admin',
			'admin_pass' => 'password',
			'admin_email' => 'admin@example.test',
			'board_email' => 'board@example.test',
		]);

		$this->project->appendBoard([
			'name' => $name,
			'phpbb' => $version,
			'php' => $runtime['php'],
			'db' => $db,
			'url' => "http://localhost:$port/",
			'path' => $boardDir,
			'created_at' => gmdate('c'),
		]);

		echo "Created board scaffold: $name\n";
		echo "Compose: {$paths['compose']}\n";
		echo "Install config: {$paths['install_config']}\n";
		echo "URL after start: http://localhost:$port/\n";
		echo "Next: docker compose -f {$paths['compose']} up -d\n";
		return 0;
	}

	private function boardList(): int
	{
		$boards = $this->project->boards();
		if (!$boards)
		{
			echo "No boards created\n";
			return 0;
		}

		$runner = new BoardRunner($this->project);
		foreach ($boards as $board)
		{
			$status = $runner->status($board['name']);
			echo "{$board['name']}\t$status\t{$board['phpbb']}\tPHP {$board['php']}\t{$board['db']}\t{$board['url']}\n";
		}

		return 0;
	}

	private function boardStart(array $args): int
	{
		$name = $this->boardName($args, 'Usage: qi board:start <name>');
		(new BoardRunner($this->project))->start($name);
		$board = $this->project->board($name);
		echo "Started board: $name\n";
		echo "URL: {$board['url']}\n";
		return 0;
	}

	private function boardStop(array $args): int
	{
		$name = $this->boardName($args, 'Usage: qi board:stop <name>');
		(new BoardRunner($this->project))->stop($name);
		echo "Stopped board: $name\n";
		return 0;
	}

	private function boardDestroy(array $args): int
	{
		$name = $this->boardName($args, 'Usage: qi board:destroy <name>');
		(new BoardRunner($this->project))->destroy($name);
		echo "Destroyed board: $name\n";
		return 0;
	}

	private function boardSeed(array $args): int
	{
		$cli = CommandLine::parse($args);
		$name = $cli->argument(0);
		if ($name === null)
		{
			throw new \InvalidArgumentException('Usage: qi board:seed <name> [--preset tiny|extension-dev|load-test] [--seed N]');
		}

		$preset = $cli->option('preset', 'extension-dev');
		$seed = (int) $cli->option('seed', '1');

		(new BoardRunner($this->project))->seed($name, $preset, $seed);
		echo "Seeded board: $name\n";
		return 0;
	}

	private function boardName(array $args, string $usage): string
	{
		$cli = CommandLine::parse($args);
		$name = $cli->argument(0);
		if ($name === null)
		{
			throw new \InvalidArgumentException($usage);
		}

		return $name;
	}

	private function help(): void
	{
		echo <<<TXT
QuickInstall CLI prototype

Commands:
  qi init
  qi source:add <version|branch> [--git] [--url URL]
  qi source:fetch <version|branch>
  qi source:list
  qi board:create <name> [--phpbb VERSION] [--db mariadb|mysql|postgres|sqlite] [--port PORT] [--populate PRESET]
  qi board:list
  qi board:start <name>
  qi board:stop <name>
  qi board:destroy <name>
  qi board:seed <name> [--preset tiny|extension-dev|load-test] [--seed N]

Examples:
  qi source:add 3.3.17
  qi source:add master --git --url https://github.com/phpbb/phpbb.git
  qi board:create test --phpbb 3.3.17 --db mariadb --port 8081 --populate extension-dev
  qi board:start test
  qi board:seed test --preset extension-dev --seed 1

TXT;
	}
}
