<?php

namespace QuickInstall\Modern;

class BoardService
{
	private Project $project;

	public function __construct(Project $project)
	{
		$this->project = $project;
	}

	public function create(string $name, string $version = 'latest', string $db = 'mariadb', int $port = 8080, string $populate = 'none'): array
	{
		$this->project->init();
		$selection = (new VersionMatrix())->resolve($version);
		$source = (new SourceProvider($this->project))->ensure($version);

		$boardDir = $this->project->boardPath($name);
		if (!is_dir($boardDir) && !mkdir($boardDir, 0775, true))
		{
			throw new \RuntimeException("Unable to create board directory: $boardDir");
		}

		$config = [
			'phpbb' => $version,
			'phpbb_source' => $source['source_key'],
			'php' => $selection['php'],
			'db' => $db,
			'port' => $port,
			'populate' => $populate,
			'admin_name' => 'admin',
			'admin_pass' => 'password',
			'admin_email' => 'admin@example.test',
			'board_email' => 'board@example.test',
			'extensions' => [],
			'styles' => [],
		];

		$paths = (new DockerComposeWriter($this->project))->write($name, $config);
		$board = [
			'name' => $name,
			'phpbb' => $source['version'],
			'phpbb_source' => $source['source_key'],
			'phpbb_branch' => $source['phpbb_branch'],
			'php' => $selection['php'],
			'db' => $db,
			'port' => $port,
			'url' => "http://localhost:$port/",
			'path' => $boardDir,
			'populate' => $populate,
			'extensions' => [],
			'styles' => [],
			'created_at' => gmdate('c'),
		];

		$this->project->appendBoard($board);

		return ['board' => $board, 'paths' => $paths];
	}

	public function list(): array
	{
		$runner = new BoardRunner($this->project);
		$boards = [];
		foreach ($this->project->boards() as $board)
		{
			$board['status'] = $runner->status($board['name']);
			$board['populate'] = $board['populate'] ?? 'none';
			$boards[] = $board;
		}

		return $boards;
	}

	public function start(string $name): array
	{
		(new BoardRunner($this->project))->start($name);
		return $this->project->board($name);
	}

	public function stop(string $name): void
	{
		(new BoardRunner($this->project))->stop($name);
	}

	public function destroy(string $name): void
	{
		(new BoardRunner($this->project))->destroy($name);
	}

	public function seed(string $name, string $preset, int $seed, string $action): void
	{
		$board = $this->project->board($name);
		if (($board['db'] ?? '') === 'sqlite' && $action !== 'reset')
		{
			throw new \InvalidArgumentException('SQLite boards do not support fixture seeding. Use --reset to remove partial seed data, or use mariadb, mysql, or postgres for seeded boards.');
		}

		(new BoardRunner($this->project))->seed($name, $preset, $seed, $action);
	}
}
