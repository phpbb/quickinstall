<?php
/**
 *
 * QuickInstall sandbox customisation unmount service
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace QuickInstall\Sandbox;

use RuntimeException;

/** Cleans phpBB state before removing extension or style mounts. */
class CustomisationUnmountService
{
	private Project $project;
	private BoardRunner $runner;
	private ?Output $output;

	public function __construct(Project $project, ?Output $output = null, ?BoardRunner $runner = null)
	{
		$this->project = $project;
		$this->output = $output;
		$this->runner = $runner ?: new BoardRunner($project, $output);
	}

	public function extension(ExtensionManager $manager, string $board, string $name): string
	{
		$this->cleanInstalledBoard($board, static function (BoardRunner $runner) use ($board, $name): void {
			$runner->uninstallExtension($board, $name);
		});

		return $this->remove($manager, $board, $name);
	}

	public function style(StyleManager $manager, string $board, string $name): string
	{
		$this->cleanInstalledBoard($board, static function (BoardRunner $runner) use ($board, $name): void {
			$runner->uninstallStyle($board, $name);
		});

		return $this->remove($manager, $board, $name);
	}

	private function cleanInstalledBoard(string $board, callable $cleanup): void
	{
		$this->project->board($board);
		$config = $this->project->boardPath($board) . '/config.php';
		if (!is_file($config) || filesize($config) === 0)
		{
			return;
		}

		$status = $this->runner->status($board);
		if ($status !== 'running')
		{
			throw new RuntimeException("Board must be fully running to safely uninstall customisations: $board (status: $status)");
		}

		$cleanup($this->runner);
	}

	private function remove(CustomisationManagerInterface $manager, string $board, string $name): string
	{
		$target = $manager->unmount($board, $name);
		(new BoardRefreshService($this->project, $this->output, $this->runner))->refreshIfRunning($board);
		$manager->cleanupStaleTarget($board, $name);

		return $target;
	}
}
