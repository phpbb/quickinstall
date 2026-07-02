<?php
/**
 *
 * QuickInstall CLI
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace QuickInstall\Sandbox;

use InvalidArgumentException;
use RuntimeException;

class Application
{
	private Project $project;

	public function __construct(string $root)
	{
		$this->project = new Project($root);
	}

	public function run(array $argv): int
	{
		array_shift($argv);
		$command = array_shift($argv) ?: 'help';
		if (!in_array($command, ['help', '--help', '-h'], true) && (in_array('--help', $argv, true) || in_array('-h', $argv, true)))
		{
			$this->help([$command]);
			return 0;
		}

		try
		{
			switch ($command)
			{
				case 'help':
				case '--help':
				case '-h':
					$this->help($argv);
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
		catch (InvalidArgumentException $e)
		{
			fwrite(STDERR, $e->getMessage() . "\n");
			return 1;
		}
		catch (RuntimeException $e)
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
			throw new InvalidArgumentException('Usage: qi source:add <version|branch> [--git] [--url URL] [--allow-external]');
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
			throw new InvalidArgumentException('Usage: qi source:remove <version|source> [--force]');
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
			throw new InvalidArgumentException('Usage: qi source:fetch <version|branch>');
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
			throw new InvalidArgumentException('Usage: qi board:create <name> [--phpbb VERSION] [--db mariadb|mysql|postgres|sqlite] [--port PORT] [--populate PRESET] [--replace]');
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
			throw new InvalidArgumentException('Usage: qi board:seed <name> [--preset tiny|extension-dev|load-test|random] [--seed N] [--reset|--replace]');
		}

		$preset = $cli->option('preset', 'extension-dev');
		$seed = (int) $cli->option('seed', '1');
		$this->validatePreset($preset);
		if ($seed < 1)
		{
			throw new InvalidArgumentException('--seed must be a positive integer.');
		}
		if ($cli->has('reset') && $cli->has('replace'))
		{
			throw new InvalidArgumentException('Use --reset or --replace, not both.');
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
			throw new InvalidArgumentException($usage);
		}

		return $name;
	}

	private function validateBoardCreateOptions(string $db, int $port, string $populate): void
	{
		if (!in_array($db, ['mariadb', 'mysql', 'postgres', 'sqlite'], true))
		{
			throw new InvalidArgumentException('--db must be one of: mariadb, mysql, postgres, sqlite.');
		}

		if ($port < 1 || $port > 65535)
		{
			throw new InvalidArgumentException('--port must be between 1 and 65535.');
		}

		if ($populate !== 'none')
		{
			$this->validatePreset($populate);
		}

		if ($db === 'sqlite' && $populate !== 'none')
		{
			throw new InvalidArgumentException('SQLite boards currently support --populate none only. Use mariadb, mysql, or postgres for fixture seeding.');
		}
	}

	private function validatePreset(string $preset): void
	{
		if (!in_array($preset, ['tiny', 'extension-dev', 'load-test', 'random'], true))
		{
			throw new InvalidArgumentException('Preset must be one of: tiny, extension-dev, load-test, random.');
		}
	}

	private function extMount(array $args): int
	{
		$cli = CommandLine::parse($args);
		$board = $cli->argument(0);
		$source = $cli->argument(1);
		if ($board === null || $source === null)
		{
			throw new InvalidArgumentException('Usage: qi ext:mount <board> <path> [--copy] [--recursive] [--allow-external]');
		}
		if ($cli->has('recursive') && $cli->has('copy'))
		{
			throw new InvalidArgumentException('--recursive cannot be combined with --copy. Mount recursively with bind mode, or copy individual extensions.');
		}

		return $this->mountResources('extension', new ExtensionManager($this->project), $board, $source, $cli->has('copy'), $cli->has('recursive'), $cli->has('allow-external'));
	}

	private function extUnmount(array $args): int
	{
		$cli = CommandLine::parse($args);
		$board = $cli->argument(0);
		$name = $cli->argument(1);
		if ($board === null || $name === null)
		{
			throw new InvalidArgumentException('Usage: qi ext:unmount <board> <vendor/extension>');
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
			throw new InvalidArgumentException('Usage: qi style:mount <board> <path> [--copy] [--recursive] [--allow-external]');
		}
		if ($cli->has('recursive') && $cli->has('copy'))
		{
			throw new InvalidArgumentException('--recursive cannot be combined with --copy. Mount recursively with bind mode, or copy individual styles.');
		}

		return $this->mountResources('style', new StyleManager($this->project), $board, $source, $cli->has('copy'), $cli->has('recursive'), $cli->has('allow-external'));
	}

	private function styleUnmount(array $args): int
	{
		$cli = CommandLine::parse($args);
		$board = $cli->argument(0);
		$name = $cli->argument(1);
		if ($board === null || $name === null)
		{
			throw new InvalidArgumentException('Usage: qi style:unmount <board> <style>');
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

	private function printBulkMountResult(string $type, string $board, array $mounted, array $errors): void
	{
		if (!$mounted && !$errors)
		{
			echo "No {$type}s found for board: $board\n";
			return;
		}

		foreach ($mounted as $item)
		{
			echo "Mounted {$item['name']} on $board ({$item['mode']})\n";
		}

		foreach ($errors as $error)
		{
			fwrite(STDERR, "Skipped $type: $error\n");
		}
	}

	private function mountResources(string $type, object $manager, string $board, string $source, bool $copy, bool $recursive, bool $allowExternal): int
	{
		if ($recursive)
		{
			$this->project->board($board);
			$mounted = [];
			$errors = [];
			foreach ($manager->discover($source, $allowExternal) as $path)
			{
				try
				{
					$mounted[] = $manager->mount($board, $path, false, true);
				}
				catch (RuntimeException | InvalidArgumentException $e)
				{
					$errors[] = "$path: " . $e->getMessage();
				}
			}

			if ($mounted)
			{
				$this->refreshBoardIfRunning($board);
			}
			$this->printBulkMountResult($type, $board, $mounted, $errors);
			return $errors ? 1 : 0;
		}

		$mounted = $manager->mount($board, $source, $copy, $allowExternal);
		$this->refreshBoardIfRunning($board);
		echo "Mounted {$mounted['name']} on $board ({$mounted['mode']})\n";
		echo "Source: {$mounted['source']}\n";
		echo "Target: {$mounted['target']}\n";
		return 0;
	}

	private function help(array $args = []): void
	{
		$command = $args[0] ?? null;
		$commands = $this->helpCommands();
		if ($command !== null)
		{
			$this->helpCommand($command, $commands);
			return;
		}

		echo "QuickInstall CLI\n";
		echo "Create disposable local phpBB boards with Docker.\n\n";
		echo "Usage:\n";
		echo "  qi <command> [arguments] [options]\n";
		echo "  qi help [command]\n\n";
		echo "Common workflow:\n";
		echo "  qi board:create demo --phpbb 3.3 --db mariadb --port 8081 --populate extension-dev\n";
		echo "  qi board:start demo\n\n";

		foreach ($commands as $group => $items)
		{
			echo "$group:\n";
			$width = max(array_map('strlen', array_keys($items)));
			foreach ($items as $name => $help)
			{
				echo '  ' . str_pad($name, $width) . '  ' . $help['summary'] . "\n";
			}
			echo "\n";
		}

		echo "Run `qi help <command>` for usage and options.\n";
		echo "Full guide: docs/sandbox-cli.md\n";
	}

	private function helpCommand(string $command, array $groups): void
	{
		foreach ($groups as $items)
		{
			if (!isset($items[$command]))
			{
				continue;
			}

			$help = $items[$command];
			echo "{$help['title']}\n\n";
			echo "Usage:\n";
			echo "  qi {$help['usage']}\n\n";
			echo "Description:\n";
			echo "  {$help['description']}\n";
			if (!empty($help['arguments']))
			{
				echo "\nArguments:\n";
				$this->printHelpRows($help['arguments']);
			}
			if (!empty($help['options']))
			{
				echo "\nOptions:\n";
				$this->printHelpRows($help['options']);
			}
			if (!empty($help['examples']))
			{
				echo "\nExamples:\n";
				foreach ($help['examples'] as $example)
				{
					echo "  qi $example\n";
				}
			}
			echo "\n";
			return;
		}

		echo "Unknown command: $command\n\n";
		$this->help();
	}

	private function printHelpRows(array $rows): void
	{
		$width = max(array_map('strlen', array_keys($rows)));
		foreach ($rows as $name => $description)
		{
			echo '  ' . str_pad($name, $width) . '  ' . $description . "\n";
		}
	}

	private function helpCommands(): array
	{
		return [
			'Board commands' => [
				'board:create' => [
					'title' => 'board:create',
					'usage' => 'board:create <name> [--phpbb VERSION] [--db mariadb|mysql|postgres|sqlite] [--port PORT] [--populate PRESET] [--replace]',
					'summary' => 'Create a board scaffold and Docker runtime.',
					'description' => 'Creates a local board definition, downloads the requested phpBB source if needed, and prepares Docker files. Run board:start after this.',
					'arguments' => [
						'<name>' => 'Required board name. Use a short local name such as demo or extdev.',
					],
					'options' => [
						'--phpbb VERSION' => 'phpBB selector. Examples: latest, 3.3, 3.3.17, 3.2, master. Default: latest.',
						'--db DB' => 'Database engine. One of: mariadb, mysql, postgres, sqlite. Default: mariadb.',
						'--port PORT' => 'Local browser port. Default: 8080.',
						'--populate PRESET' => 'Seed preset. One of: none, tiny, extension-dev, load-test, random. Default: none.',
						'--replace' => 'Destroy an existing board with the same name before creating the new one.',
					],
					'examples' => [
						'board:create demo --phpbb 3.3 --db mariadb --port 8081',
						'board:create extdev --phpbb 3.3.17 --populate extension-dev',
					],
				],
				'board:start' => [
					'title' => 'board:start',
					'usage' => 'board:start <name>',
					'summary' => 'Start Docker and install the board if needed.',
					'description' => 'Starts the board containers, runs phpBB install on first start, applies the configured populate preset once, and waits for the board URL to respond.',
					'arguments' => [
						'<name>' => 'Required board name.',
					],
					'examples' => [
						'board:start demo',
					],
				],
				'board:stop' => [
					'title' => 'board:stop',
					'usage' => 'board:stop <name>',
					'summary' => 'Stop a running board.',
					'description' => 'Stops the board containers without deleting board files or database files.',
					'arguments' => [
						'<name>' => 'Required board name.',
					],
					'examples' => [
						'board:stop demo',
					],
				],
				'board:destroy' => [
					'title' => 'board:destroy',
					'usage' => 'board:destroy <name>',
					'summary' => 'Delete a board and its Docker image.',
					'description' => 'Removes the board files, runtime files, database files, containers, local Docker image, and board registry entry.',
					'arguments' => [
						'<name>' => 'Required board name.',
					],
					'examples' => [
						'board:destroy demo',
					],
				],
				'board:list' => [
					'title' => 'board:list',
					'usage' => 'board:list',
					'summary' => 'Show created boards and running status.',
					'description' => 'Lists known boards with phpBB version, PHP version, database type, populate preset, URL, and running status.',
					'examples' => [
						'board:list',
					],
				],
				'board:seed' => [
					'title' => 'board:seed',
					'usage' => 'board:seed <name> [--preset tiny|extension-dev|load-test|random] [--seed N] [--reset|--replace]',
					'summary' => 'Add, replace, or remove fixture content.',
					'description' => 'Seeds categories, forums, users, topics, and replies on an installed board. SQLite boards only support reset.',
					'arguments' => [
						'<name>' => 'Required board name.',
					],
					'options' => [
						'--preset PRESET' => 'Fixture preset. One of: tiny, extension-dev, load-test, random. Default: extension-dev.',
						'--seed N' => 'Positive random seed number for repeatable fixture shape. Default: 1.',
						'--replace' => 'Remove existing QuickInstall seed data, then seed again.',
						'--reset' => 'Remove existing QuickInstall seed data without adding new data.',
					],
					'examples' => [
						'board:seed demo --preset extension-dev --seed 1',
						'board:seed demo --preset extension-dev --replace',
					],
				],
			],
			'Extension commands' => [
				'ext:mount' => [
					'title' => 'ext:mount',
					'usage' => 'ext:mount <board> <path> [--copy] [--recursive] [--allow-external]',
					'summary' => 'Mount one or more extensions into a board.',
					'description' => 'Mounts a phpBB extension from the extensions drop zone. Running boards are refreshed automatically.',
					'arguments' => [
						'<board>' => 'Required board name.',
						'<path>' => 'Extension path, or a directory to scan when --recursive is used.',
					],
					'options' => [
						'--copy' => 'Copy one extension instead of bind-mounting it.',
						'--recursive' => 'Find and bind-mount all extensions below <path>. Cannot be combined with --copy.',
						'--allow-external' => 'Allow trusted paths outside the extensions drop zone.',
					],
					'examples' => [
						'ext:mount demo extensions/vendor/extname',
						'ext:mount demo extensions --recursive',
					],
				],
				'ext:unmount' => [
					'title' => 'ext:unmount',
					'usage' => 'ext:unmount <board> <vendor/extension>',
					'summary' => 'Remove a mounted extension from a board.',
					'description' => 'Removes the extension mount or copied extension files, refreshes the board if running, and clears stale targets.',
					'arguments' => [
						'<board>' => 'Required board name.',
						'<vendor/extension>' => 'Extension name from composer.json, such as phpbb/foo.',
					],
					'examples' => [
						'ext:unmount demo vendor/extname',
					],
				],
				'ext:list' => [
					'title' => 'ext:list',
					'usage' => 'ext:list <board>',
					'summary' => 'Show extensions mounted on a board.',
					'description' => 'Lists mounted extensions, mount mode, and source path for one board.',
					'arguments' => [
						'<board>' => 'Required board name.',
					],
					'examples' => [
						'ext:list demo',
					],
				],
			],
			'Style commands' => [
				'style:mount' => [
					'title' => 'style:mount',
					'usage' => 'style:mount <board> <path> [--copy] [--recursive] [--allow-external]',
					'summary' => 'Mount one or more styles into a board.',
					'description' => 'Mounts a phpBB style from the styles drop zone. Running boards are refreshed automatically.',
					'arguments' => [
						'<board>' => 'Required board name.',
						'<path>' => 'Style path, or a directory to scan when --recursive is used.',
					],
					'options' => [
						'--copy' => 'Copy one style instead of bind-mounting it.',
						'--recursive' => 'Find and bind-mount all styles below <path>. Cannot be combined with --copy.',
						'--allow-external' => 'Allow trusted paths outside the styles drop zone.',
					],
					'examples' => [
						'style:mount demo styles/stylename',
						'style:mount demo styles --recursive',
					],
				],
				'style:unmount' => [
					'title' => 'style:unmount',
					'usage' => 'style:unmount <board> <style>',
					'summary' => 'Remove a mounted style from a board.',
					'description' => 'Removes the style mount or copied style files, refreshes the board if running, and clears stale targets.',
					'arguments' => [
						'<board>' => 'Required board name.',
						'<style>' => 'Style folder name.',
					],
					'examples' => [
						'style:unmount demo stylename',
					],
				],
				'style:list' => [
					'title' => 'style:list',
					'usage' => 'style:list <board>',
					'summary' => 'Show styles mounted on a board.',
					'description' => 'Lists mounted styles, mount mode, and source path for one board.',
					'arguments' => [
						'<board>' => 'Required board name.',
					],
					'examples' => [
						'style:list demo',
					],
				],
			],
			'Source commands' => [
				'source:add' => [
					'title' => 'source:add',
					'usage' => 'source:add <version|branch> [--git] [--url URL] [--allow-external]',
					'summary' => 'Register a phpBB source.',
					'description' => 'Registers a phpBB release, branch, or Git source. Most users can skip this because board:create registers normal sources automatically.',
					'arguments' => [
						'<version|branch>' => 'Version selector or source name, such as 3.3.17 or master.',
					],
					'options' => [
						'--git' => 'Register a Git source instead of a Composer release source.',
						'--url URL' => 'Git repository URL. Defaults to the official phpBB repository for Git sources.',
						'--allow-external' => 'Allow a trusted non-phpBB Git URL.',
					],
					'examples' => [
						'source:add 3.3.17',
						'source:add master --git --url https://github.com/phpbb/phpbb.git',
					],
				],
				'source:fetch' => [
					'title' => 'source:fetch',
					'usage' => 'source:fetch <version|branch>',
					'summary' => 'Download a registered source.',
					'description' => 'Downloads or updates the source under .qi/sources. Normal board:create flows fetch automatically when needed.',
					'arguments' => [
						'<version|branch>' => 'Registered source selector.',
					],
					'examples' => [
						'source:fetch 3.3.17',
					],
				],
				'source:list' => [
					'title' => 'source:list',
					'usage' => 'source:list',
					'summary' => 'Show registered and downloaded sources.',
					'description' => 'Lists sources, download status, paths, and boards currently using each source.',
					'examples' => [
						'source:list',
					],
				],
				'source:remove' => [
					'title' => 'source:remove',
					'usage' => 'source:remove <version|source> [--force]',
					'summary' => 'Delete one source.',
					'description' => 'Deletes one source from .qi/sources and removes it from the source registry.',
					'arguments' => [
						'<version|source>' => 'Registered source selector.',
					],
					'options' => [
						'--force' => 'Remove even if existing boards reference the source.',
					],
					'examples' => [
						'source:remove 3.3.17',
					],
				],
				'source:prune' => [
					'title' => 'source:prune',
					'usage' => 'source:prune',
					'summary' => 'Delete unused sources.',
					'description' => 'Removes all downloaded sources that are not referenced by existing boards.',
					'examples' => [
						'source:prune',
					],
				],
				'phpbb:list' => [
					'title' => 'phpbb:list',
					'usage' => 'phpbb:list',
					'summary' => 'Show supported phpBB selectors.',
					'description' => 'Lists supported phpBB version selectors, PHP compatibility, and resolution notes.',
					'examples' => [
						'phpbb:list',
					],
				],
			],
			'Workspace commands' => [
				'init' => [
					'title' => 'init',
					'usage' => 'init',
					'summary' => 'Create the .qi workspace folders.',
					'description' => 'Initializes QuickInstall local state folders. Other commands also initialize the workspace when needed.',
					'examples' => [
						'init',
					],
				],
			],
		];
	}
}
