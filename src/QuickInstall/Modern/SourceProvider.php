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
		$this->project->assertName($version, 'version');
		if (!in_array($type, ['composer', 'git'], true))
		{
			throw new \InvalidArgumentException("Unsupported source type: $type");
		}

		$sources = $this->project->readJson('sources.json', []);
		$record = [
			'version' => $version,
			'type' => $type,
			'package' => $type === 'composer' ? 'phpbb/phpbb' : null,
			'url' => $url ?: ($type === 'git' ? 'https://github.com/phpbb/phpbb.git' : null),
			'path' => $this->project->sourcePath($version),
			'registered_at' => gmdate('c'),
		];

		$sources[$version] = $record;
		$this->project->writeJson('sources.json', $sources);

		return $record;
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
			throw new \RuntimeException("Source path already exists and is not empty: $path");
		}

		if ($source['type'] === 'git')
		{
			$url = $source['url'] ?: 'https://github.com/phpbb/phpbb.git';
			$this->run([
				'git',
				'clone',
				'--branch',
				$source['version'],
				'--depth',
				'1',
				$url,
				$path,
			], dirname($path));

			$this->run(['composer', 'install', '--no-interaction'], $path);
			return;
		}

		if (is_dir($path))
		{
			rmdir($path);
		}

		$this->run([
			'composer',
			'create-project',
			'phpbb/phpbb',
			$path,
			$source['version'],
			'--no-interaction',
		], dirname($path));
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
			0 => STDIN,
			1 => STDOUT,
			2 => STDERR,
		];

		$process = proc_open($command, $descriptor, $pipes, $cwd);
		if (!is_resource($process))
		{
			throw new \RuntimeException('Unable to start command: ' . $command[0]);
		}

		$status = proc_close($process);
		if ($status !== 0)
		{
			throw new \RuntimeException("Command failed with exit code $status: {$command[0]}");
		}
	}
}
