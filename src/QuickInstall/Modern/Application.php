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

				case 'phpbb:list':
					return $this->phpbbList();

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

				case 'ext:mount':
					return $this->extMount($argv);

				case 'ext:unmount':
					return $this->extUnmount($argv);

				case 'ext:list':
					return $this->extList($argv);

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
			$sourceKey = $source['source_key'] ?? $source['version'];
			$constraint = $source['constraint'] ?? '-';
			$status = $source['status'] ?? '-';
			echo "$sourceKey\t{$source['version']}\t$constraint\t{$source['type']}\t$status\t{$source['path']}\n";
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

		$source = new SourceProvider($this->project);
		$record = $source->ensure($version);

		echo "Fetched phpBB source: {$record['path']}\n";
		return 0;
	}

	private function phpbbList(): int
	{
		foreach ((new VersionMatrix())->list() as $row)
		{
			echo "{$row['selector']}\t{$row['status']}\tPHP {$row['php']}\t{$row['resolves_to']}\t{$row['notes']}\n";
		}

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
		$selection = $matrix->resolve($version);
		$runtime = ['php' => $selection['php']];
		$source = (new SourceProvider($this->project))->ensure($version);

		$boardDir = $this->project->boardPath($name);
		if (!is_dir($boardDir) && !mkdir($boardDir, 0775, true))
		{
			throw new \RuntimeException("Unable to create board directory: $boardDir");
		}

		$writer = new DockerComposeWriter($this->project);
		$paths = $writer->write($name, [
			'phpbb' => $version,
			'phpbb_source' => $source['source_key'],
			'php' => $runtime['php'],
			'db' => $db,
			'port' => $port,
			'populate' => $populate,
			'admin_name' => 'admin',
			'admin_pass' => 'password',
			'admin_email' => 'admin@example.test',
			'board_email' => 'board@example.test',
			'extensions' => [],
		]);

		$this->project->appendBoard([
			'name' => $name,
			'phpbb' => $source['version'],
			'phpbb_source' => $source['source_key'],
			'phpbb_branch' => $source['phpbb_branch'],
			'php' => $runtime['php'],
			'db' => $db,
			'port' => $port,
			'url' => "http://localhost:$port/",
			'path' => $boardDir,
			'populate' => $populate,
			'extensions' => [],
			'created_at' => gmdate('c'),
		]);

		echo "Created board scaffold: $name\n";
		echo "Compose: {$paths['compose']}\n";
		echo "Install config: {$paths['install_config']}\n";
		echo "URL after start: http://localhost:$port/\n";
		if ($populate !== 'none')
		{
			echo "Populate preset: $populate (runs on board:start)\n";
		}
		echo "Next: php bin/qi board:start $name\n";
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
			$populate = $board['populate'] ?? 'none';
			echo "{$board['name']}\t$status\t{$board['phpbb']}\tPHP {$board['php']}\t{$board['db']}\tpopulate:$populate\t{$board['url']}\n";
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
			throw new \InvalidArgumentException('Usage: qi board:seed <name> [--preset tiny|extension-dev|load-test|random] [--seed N] [--reset|--replace]');
		}

		$preset = $cli->option('preset', 'extension-dev');
		$seed = (int) $cli->option('seed', '1');
		if ($cli->has('reset') && $cli->has('replace'))
		{
			throw new \InvalidArgumentException('Use --reset or --replace, not both.');
		}
		$action = $cli->has('reset') ? 'reset' : ($cli->has('replace') ? 'replace' : 'seed');

		(new BoardRunner($this->project))->seed($name, $preset, $seed, $action);
		echo ucfirst($action) . " completed for board: $name\n";
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

	private function extMount(array $args): int
	{
		$cli = CommandLine::parse($args);
		$board = $cli->argument(0);
		$source = $cli->argument(1);
		if ($board === null || $source === null)
		{
			throw new \InvalidArgumentException('Usage: qi ext:mount <board> <path> [--copy]');
		}

		$mounted = (new ExtensionManager($this->project))->mount($board, $source, $cli->has('copy'));
		$this->refreshBoardIfRunning($board);
		echo "Mounted {$mounted['name']} on $board ({$mounted['mode']})\n";
		echo "Source: {$mounted['source']}\n";
		echo "Target: {$mounted['target']}\n";
		return 0;
	}

	private function extUnmount(array $args): int
	{
		$cli = CommandLine::parse($args);
		$board = $cli->argument(0);
		$name = $cli->argument(1);
		if ($board === null || $name === null)
		{
			throw new \InvalidArgumentException('Usage: qi ext:unmount <board> <vendor/extension>');
		}

		$extensions = new ExtensionManager($this->project);
		$target = $extensions->unmount($board, $name);
		$this->refreshBoardIfRunning($board);
		$extensions->cleanupStaleTarget($board, $name);
		echo "Unmounted $name from $board\n";
		echo "Removed: $target\n";
		return 0;
	}

	private function refreshBoardIfRunning(string $board): void
	{
		$runner = new BoardRunner($this->project);
		(new DockerComposeWriter($this->project))->write($board, $this->runtimeConfig($this->project->board($board)));
		if ($runner->status($board) === 'running')
		{
			$runner->recreateWeb($board);
			$runner->purgeCache($board);
		}
	}

	private function runtimeConfig(array $board): array
	{
		if (empty($board['port']) && !empty($board['url']))
		{
			$port = parse_url($board['url'], PHP_URL_PORT);
			$board['port'] = $port ?: 80;
		}

		return $board + [
			'admin_name' => 'admin',
			'admin_pass' => 'password',
			'admin_email' => 'admin@example.test',
			'board_email' => 'board@example.test',
			'populate' => 'none',
			'extensions' => [],
		];
	}

	private function extList(array $args): int
	{
		$board = $this->boardName($args, 'Usage: qi ext:list <board>');
		$mounted = (new ExtensionManager($this->project))->list($board);

		if (!$mounted)
		{
			echo "No extensions mounted for board: $board\n";
			return 0;
		}

		foreach ($mounted as $extension)
		{
			echo "{$extension['name']}\t{$extension['mode']}\t{$extension['source']}\n";
		}

		return 0;
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
  qi phpbb:list
  qi board:create <name> [--phpbb VERSION] [--db mariadb|mysql|postgres|sqlite] [--port PORT] [--populate PRESET]
  qi board:list
  qi board:start <name>
  qi board:stop <name>
  qi board:destroy <name>
  qi board:seed <name> [--preset tiny|extension-dev|load-test|random] [--seed N] [--reset|--replace]
  qi ext:mount <board> <path> [--copy]
  qi ext:unmount <board> <vendor/extension>
  qi ext:list <board>

Examples:
  qi source:add 3.3.17
  qi source:add master --git --url https://github.com/phpbb/phpbb.git
  qi board:create test --phpbb 3.3.17 --db mariadb --port 8081 --populate extension-dev
  qi board:start test
  qi board:seed test --preset extension-dev --seed 1
  qi ext:mount test extensions/vendor/extname

TXT;
	}
}
