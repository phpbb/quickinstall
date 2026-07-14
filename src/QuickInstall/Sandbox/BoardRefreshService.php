<?php
/**
 *
 * QuickInstall sandbox board refresh service
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace QuickInstall\Sandbox;

class BoardRefreshService
{
	private Project $project;
	private BoardRunner $runner;

	public function __construct(Project $project, ?Output $output = null, ?BoardRunner $runner = null)
	{
		$this->project = $project;
		$this->runner = $runner ?: new BoardRunner($project, $output);
	}

	public function refreshIfRunning(string $board): void
	{
		(new DockerComposeWriter($this->project))->write($board, $this->runtimeConfig($this->project->board($board)));
		if ($this->runner->status($board) === 'running')
		{
			$this->runner->recreateWeb($board);
			$this->runner->purgeCache($board);
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
			'debug' => false,
			'extensions' => [],
			'styles' => [],
		];
	}
}
