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

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

/** Copies the unified preset runtime package into board runtime state. */
class SeederWriter
{
	private Project $project;

	public function __construct(Project $project)
	{
		$this->project = $project;
	}

	public function write(string $name): string
	{
		$source = __DIR__ . '/SeedRuntime';
		$target = $this->project->runtimePath($name) . '/seed-runtime';
		if (!is_dir($source))
		{
			throw new RuntimeException("Seed runtime missing: $source");
		}

		$this->project->deleteTree($target);
		if (!mkdir($target, 0775, true) && !is_dir($target))
		{
			throw new RuntimeException("Unable to create seed runtime directory: $target");
		}

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST
		);
		foreach ($iterator as $item)
		{
			$relative = substr($item->getPathname(), strlen($source) + 1);
			$destination = $target . '/' . $relative;
			if ($item->isDir())
			{
				if (!is_dir($destination) && !mkdir($destination, 0775, true) && !is_dir($destination))
				{
					throw new RuntimeException("Unable to create seed runtime directory: $destination");
				}
				continue;
			}

			if (!copy($item->getPathname(), $destination))
			{
				throw new RuntimeException("Unable to copy seed runtime file: $destination");
			}
		}

		return $target;
	}
}
