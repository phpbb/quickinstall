<?php

namespace QuickInstall\Modern;

class SourceProvider
{
	private Project $project;

	public function __construct(Project $project)
	{
		$this->project = $project;
	}

	public function add(string $version, string $type, ?string $url): array
	{
		if (!in_array($type, ['composer', 'git'], true))
		{
			throw new \InvalidArgumentException("Unsupported source type: $type");
		}
		$selection = (new VersionMatrix())->resolve($version, $type === 'git');

		$sources = $this->project->readJson('sources.json', []);
		$record = [
			'version' => $selection['version'],
			'source_key' => $selection['source_key'],
			'constraint' => $selection['constraint'],
			'branch' => $selection['branch'],
			'phpbb_branch' => $selection['phpbb_branch'],
			'php' => $selection['php'],
			'status' => $selection['status'],
			'type' => $type,
			'package' => $type === 'composer' ? 'phpbb/phpbb' : null,
			'url' => $url ?: ($type === 'git' ? 'https://github.com/phpbb/phpbb.git' : null),
			'path' => $this->project->sourcePath($selection['source_key']),
			'registered_at' => gmdate('c'),
		];

		$sources[$selection['source_key']] = $record;
		$this->project->writeJson('sources.json', $sources);

		return $record;
	}

	public function ensure(string $version): array
	{
		$selection = (new VersionMatrix())->resolve($version);
		$sources = $this->project->readJson('sources.json', []);
		if (!isset($sources[$selection['source_key']]))
		{
			echo "Registering phpBB source: $version\n";
			$sources[$selection['source_key']] = $this->add($version, 'composer', null);
		}

		$source = $sources[$selection['source_key']];
		$source = $this->withSelectionDefaults($source, $selection);
		if (!file_exists($source['path'] . '/common.php'))
		{
			echo "Fetching phpBB source: $version\n";
			$this->fetch($source);
		}

		return $source;
	}

	private function withSelectionDefaults(array $source, array $selection): array
	{
		return $source + [
			'version' => $selection['version'],
			'source_key' => $selection['source_key'],
			'constraint' => $selection['constraint'],
			'branch' => $selection['branch'],
			'phpbb_branch' => $selection['phpbb_branch'],
			'php' => $selection['php'],
			'status' => $selection['status'],
			'path' => $this->project->sourcePath($selection['source_key']),
		];
	}

	public function fetch(array $source): void
	{
		$path = $source['path'];
		$parent = dirname($path);
		if (!is_dir($parent) && !mkdir($parent, 0775, true))
		{
			throw new \RuntimeException("Unable to create source directory: $parent");
		}

		if (is_dir($path) && $this->hasFiles($path))
		{
			if (file_exists($path . '/common.php'))
			{
				return;
			}

			echo "Removing incomplete source path: $path\n";
			$this->project->deleteTree($path);
		}

		if ($source['type'] === 'git')
		{
			$url = $source['url'] ?: 'https://github.com/phpbb/phpbb.git';
			$this->run([
				'git',
				'clone',
				'--branch',
				$source['branch'] ?: $source['version'],
				'--depth',
				'1',
				$url,
				$path,
			], dirname($path));

			$this->run(['composer', 'install', '--no-interaction', '--ignore-platform-reqs'], $path);
			return;
		}

		if (is_dir($path))
		{
			rmdir($path);
		}

		$command = [
			'composer',
			'create-project',
			'phpbb/phpbb',
			$path,
			'--no-interaction',
			'--ignore-platform-reqs',
		];
		if (!empty($source['constraint']))
		{
			array_splice($command, 4, 0, [$source['constraint']]);
		}

		$this->run($command, dirname($path));
	}

	private function hasFiles(string $path): bool
	{
		$files = scandir($path);
		return $files !== false && count(array_diff($files, ['.', '..'])) > 0;
	}

	private function run(array $command, string $cwd): void
	{
		echo '$ ' . implode(' ', array_map('escapeshellarg', $command)) . "\n";

		$descriptor = [
			0 => ['file', '/dev/null', 'r'],
			1 => defined('STDOUT') ? constant('STDOUT') : ['file', 'php://output', 'w'],
			2 => defined('STDERR') ? constant('STDERR') : ['file', 'php://stderr', 'w'],
		];

		$process = proc_open($command, $descriptor, $pipes, $cwd);
		if (!is_resource($process))
		{
			throw new \RuntimeException('Unable to start command: ' . $command[0]);
		}

		$status = proc_close($process);
		if ($status !== 0)
		{
			throw new \RuntimeException("Command failed with exit code $status: {$command[0]}" . $this->commandHint($command[0]));
		}
	}

	private function commandHint(string $command): string
	{
		if ($command === 'composer')
		{
			return "\nInstall Composer or make sure composer is available in PATH.";
		}

		if ($command === 'git')
		{
			return "\nInstall Git or make sure git is available in PATH.";
		}

		return '';
	}
}
