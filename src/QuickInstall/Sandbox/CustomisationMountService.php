<?php
/**
 *
 * QuickInstall sandbox customisation mount service
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace QuickInstall\Sandbox;

use InvalidArgumentException;
use RuntimeException;

/** Coordinates single or recursive extension/style mounts and board refreshes. */
class CustomisationMountService
{
	private Project $project;
	private ?Output $output;

	public function __construct(Project $project, ?Output $output = null)
	{
		$this->project = $project;
		$this->output = $output;
	}

	/** Returns mounted items and per-item errors; recursive mounts are best effort. */
	public function mount(CustomisationManagerInterface $manager, string $board, string $source, bool $copy = false, bool $recursive = false, bool $allowExternal = false): array
	{
		if ($recursive)
		{
			return $this->mountRecursive($manager, $board, $source, $allowExternal);
		}

		$mounted = [$manager->mount($board, $source, $copy, $allowExternal)];
		$this->refreshBoardIfRunning($board);

		return ['mounted' => $mounted, 'errors' => [], 'recursive' => false];
	}

	private function mountRecursive(CustomisationManagerInterface $manager, string $board, string $source, bool $allowExternal): array
	{
		$this->project->board($board);
		$mounted = [];
		$errors = [];
		foreach ($manager->discover($source, $allowExternal) as $path)
		{
			try
			{
				$mounted[] = $manager->mount($board, $path, false, $allowExternal);
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

		return ['mounted' => $mounted, 'errors' => $errors, 'recursive' => true];
	}

	private function refreshBoardIfRunning(string $board): void
	{
		(new BoardRefreshService($this->project, $this->output))->refreshIfRunning($board);
	}
}
