<?php

namespace QuickInstall\Sandbox;

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

				case 'source:remove':
					return $this->sourceRemove($argv);

				case 'source:prune':
					return $this->sourcePrune();

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

				case 'style:mount':
					return $this->styleMount($argv);

				case 'style:unmount':
					return $this->styleUnmount($argv);

				case 'style:list':
					return $this->styleList($argv);

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
			throw new \InvalidArgumentException('Usage: qi source:add <version|branch> [--git] [--url URL] [--allow-external]');
		}

		$record = (new SourceService($this->project))->add($version, $cli->has('git'), $cli->option('url'), $cli->has('allow-external'));

		echo "Registered phpBB source {$record['version']} ({$record['type']})\n";
		$this->nextStep("fetch it with Composer/Git into {$record['path']}");
		return 0;
	}

	private function sourceList(): int
	{
		$sources = (new SourceService($this->project))->list();

		if (!$sources)
		{
			echo "No sources registered\n";
			return 0;
		}

		$this->printTable(
			['Source', 'Version', 'Type', 'Status', 'Downloaded', 'Used By', 'Path'],
			array_map(static function ($source) {
				return [
					$source['source_key'] ?? $source['version'],
					$source['version'],
					$source['type'],
					$source['status'] ?? '-',
					!empty($source['downloaded']) ? 'yes' : 'no',
					!empty($source['used_by']) ? implode(', ', $source['used_by']) : '-',
					$source['path'],
				];
			}, $sources)
		);

		return 0;
	}

	private function sourceRemove(array $args): int
	{
		$cli = CommandLine::parse($args);
		$version = $cli->argument(0);
		if ($version === null)
		{
			throw new \InvalidArgumentException('Usage: qi source:remove <version|source> [--force]');
		}

		$removed = (new SourceService($this->project))->remove($version, $cli->has('force'));
		echo "Removed source: {$removed['source']['source_key']}\n";
		if (!empty($removed['used_by']))
		{
			echo "Warning: source was referenced by board(s): " . implode(', ', $removed['used_by']) . "\n";
		}

		return 0;
	}

	private function sourcePrune(): int
	{
		$removed = (new SourceService($this->project))->prune();
		if (!$removed)
		{
			echo "No unused sources to prune\n";
			return 0;
		}

		foreach ($removed as $source)
		{
			echo "Removed source: {$source['source_key']}\n";
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

		$record = (new SourceService($this->project))->fetch($version);

		echo "Fetched phpBB source: {$record['path']}\n";
		return 0;
	}

	private function phpbbList(): int
	{
		foreach ((new SourceService($this->project))->supportedVersions() as $row)
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
			throw new \InvalidArgumentException('Usage: qi board:create <name> [--phpbb VERSION] [--db mariadb|mysql|postgres|sqlite] [--port PORT] [--populate PRESET] [--replace]');
		}

		$version = $cli->option('phpbb', 'latest');
		$db = $cli->option('db', 'mariadb');
		$db = $db === 'sqlite3' ? 'sqlite' : $db;
		$port = (int) $cli->option('port', '8080');
		$populate = $cli->option('populate', 'none');
		$this->validateBoardCreateOptions($db, $port, $populate);

		$created = (new BoardService($this->project))->create($name, $version, $db, $port, $populate, $cli->has('replace'));
		$paths = $created['paths'];

		echo "Created board scaffold: $name\n";
		echo "Compose: {$paths['compose']}\n";
		echo "Install config: {$paths['install_config']}\n";
		echo "URL after start: http://localhost:$port/\n";
		if ($populate !== 'none')
		{
			echo "Populate preset: $populate (runs on board:start)\n";
		}
		$this->nextStep("php bin/qi board:start $name");
		return 0;
	}

	private function boardList(): int
	{
		$boards = (new BoardService($this->project))->list();
		if (!$boards)
		{
			echo "No boards created\n";
			return 0;
		}

		$this->printTable(
			['Name', 'Status', 'phpBB', 'PHP', 'DB', 'Populate', 'URL'],
			array_map(static function ($board) {
				return [
					$board['name'],
					$board['status'],
					$board['phpbb'],
					$board['php'],
					$board['db'],
					$board['populate'] ?? 'none',
					$board['url'],
				];
			}, $boards)
		);

		return 0;
	}

	private function printTable(array $headers, array $rows): void
	{
		$widths = array_map('strlen', $headers);
		foreach ($rows as $row)
		{
			foreach ($row as $index => $value)
			{
				$widths[$index] = max($widths[$index], strlen((string) $value));
			}
		}

		$this->printTableRow($headers, $widths);
		$this->printTableRow(array_map(static function ($width) {
			return str_repeat('-', $width);
		}, $widths), $widths);

		foreach ($rows as $row)
		{
			$this->printTableRow($row, $widths);
		}
	}

	private function printTableRow(array $row, array $widths): void
	{
		$cells = [];
		foreach ($row as $index => $value)
		{
			$cells[] = str_pad((string) $value, $widths[$index]);
		}

		echo implode('  ', $cells) . "\n";
	}

	private function nextStep(string $text): void
	{
		echo "\n" . $this->style('NEXT:', '1;33') . " " . $this->style($text, '1') . "\n";
	}

	private function style(string $text, string $code): string
	{
		if (!$this->supportsAnsi())
		{
			return $text;
		}

		return "\033[" . $code . "m" . $text . "\033[0m";
	}

	private function supportsAnsi(): bool
	{
		if (getenv('NO_COLOR') !== false)
		{
			return false;
		}

		if (function_exists('posix_isatty') && defined('STDOUT'))
		{
			return posix_isatty(STDOUT);
		}

		return PHP_SAPI === 'cli';
	}

	private function boardStart(array $args): int
	{
		$name = $this->boardName($args, 'Usage: qi board:start <name>');
		$board = (new BoardService($this->project))->start($name);
		echo "Started board: $name\n";
		echo "URL: {$board['url']}\n";
		return 0;
	}

	private function boardStop(array $args): int
	{
		$name = $this->boardName($args, 'Usage: qi board:stop <name>');
		(new BoardService($this->project))->stop($name);
		echo "Stopped board: $name\n";
		return 0;
	}

	private function boardDestroy(array $args): int
	{
		$name = $this->boardName($args, 'Usage: qi board:destroy <name>');
		(new BoardService($this->project))->destroy($name);
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
		$this->validatePreset($preset);
		if ($seed < 1)
		{
			throw new \InvalidArgumentException('--seed must be a positive integer.');
		}
		if ($cli->has('reset') && $cli->has('replace'))
		{
			throw new \InvalidArgumentException('Use --reset or --replace, not both.');
		}
		$action = $cli->has('reset') ? 'reset' : ($cli->has('replace') ? 'replace' : 'seed');

		(new BoardService($this->project))->seed($name, $preset, $seed, $action);
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

	private function validateBoardCreateOptions(string $db, int $port, string $populate): void
	{
		if (!in_array($db, ['mariadb', 'mysql', 'postgres', 'sqlite'], true))
		{
			throw new \InvalidArgumentException('--db must be one of: mariadb, mysql, postgres, sqlite.');
		}

		if ($port < 1 || $port > 65535)
		{
			throw new \InvalidArgumentException('--port must be between 1 and 65535.');
		}

		if ($populate !== 'none')
		{
			$this->validatePreset($populate);
		}

		if ($db === 'sqlite' && $populate !== 'none')
		{
			throw new \InvalidArgumentException('SQLite boards currently support --populate none only. Use mariadb, mysql, or postgres for fixture seeding.');
		}
	}

	private function validatePreset(string $preset): void
	{
		if (!in_array($preset, ['tiny', 'extension-dev', 'load-test', 'random'], true))
		{
			throw new \InvalidArgumentException('Preset must be one of: tiny, extension-dev, load-test, random.');
		}
	}

	private function extMount(array $args): int
	{
		$cli = CommandLine::parse($args);
		$board = $cli->argument(0);
		$source = $cli->argument(1);
		if ($board === null || $source === null)
		{
			throw new \InvalidArgumentException('Usage: qi ext:mount <board> <path> [--copy] [--allow-external]');
		}

		$mounted = (new ExtensionManager($this->project))->mount($board, $source, $cli->has('copy'), $cli->has('allow-external'));
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
			'styles' => [],
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

	private function styleMount(array $args): int
	{
		$cli = CommandLine::parse($args);
		$board = $cli->argument(0);
		$source = $cli->argument(1);
		if ($board === null || $source === null)
		{
			throw new \InvalidArgumentException('Usage: qi style:mount <board> <path> [--copy] [--allow-external]');
		}

		$mounted = (new StyleManager($this->project))->mount($board, $source, $cli->has('copy'), $cli->has('allow-external'));
		$this->refreshBoardIfRunning($board);
		echo "Mounted {$mounted['name']} on $board ({$mounted['mode']})\n";
		echo "Source: {$mounted['source']}\n";
		echo "Target: {$mounted['target']}\n";
		return 0;
	}

	private function styleUnmount(array $args): int
	{
		$cli = CommandLine::parse($args);
		$board = $cli->argument(0);
		$name = $cli->argument(1);
		if ($board === null || $name === null)
		{
			throw new \InvalidArgumentException('Usage: qi style:unmount <board> <style>');
		}

		$styles = new StyleManager($this->project);
		$target = $styles->unmount($board, $name);
		$this->refreshBoardIfRunning($board);
		$styles->cleanupStaleTarget($board, $name);
		echo "Unmounted $name from $board\n";
		echo "Removed: $target\n";
		return 0;
	}

	private function styleList(array $args): int
	{
		$board = $this->boardName($args, 'Usage: qi style:list <board>');
		$mounted = (new StyleManager($this->project))->list($board);

		if (!$mounted)
		{
			echo "No styles mounted for board: $board\n";
			return 0;
		}

		foreach ($mounted as $style)
		{
			echo "{$style['name']}\t{$style['mode']}\t{$style['source']}\n";
		}

		return 0;
	}

	private function help(): void
	{
		echo <<<TXT
QuickInstall CLI prototype

Commands:
  qi init
  qi source:add <version|branch> [--git] [--url URL] [--allow-external]
  qi source:fetch <version|branch>
  qi source:list
  qi source:remove <version|source> [--force]
  qi source:prune
  qi phpbb:list
  qi board:create <name> [--phpbb VERSION] [--db mariadb|mysql|postgres|sqlite] [--port PORT] [--populate PRESET] [--replace]
  qi board:list
  qi board:start <name>
  qi board:stop <name>
  qi board:destroy <name>
  qi board:seed <name> [--preset tiny|extension-dev|load-test|random] [--seed N] [--reset|--replace]
  qi ext:mount <board> <path> [--copy] [--allow-external]
  qi ext:unmount <board> <vendor/extension>
  qi ext:list <board>
  qi style:mount <board> <path> [--copy] [--allow-external]
  qi style:unmount <board> <style>
  qi style:list <board>

Examples:
  qi source:add 3.3.17
  qi source:add master --git --url https://github.com/phpbb/phpbb.git
  qi board:create test --phpbb 3.3.17 --db mariadb --port 8081 --populate extension-dev
  qi board:start test
  qi board:seed test --preset extension-dev --seed 1
  qi ext:mount test extensions/vendor/extname
  qi style:mount test styles/stylename

TXT;
	}
}
