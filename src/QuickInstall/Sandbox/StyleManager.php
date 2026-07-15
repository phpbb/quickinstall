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

/** Discovers, copies, binds, lists, and removes phpBB styles. */
class StyleManager
{
	private Project $project;

	public function __construct(Project $project)
	{
		$this->project = $project;
	}

	/** Mounts one style using its validated source directory name. */
	public function mount(string $board, string $source, bool $copy = false, bool $allowExternal = false): array
	{
		$boardConfig = $this->project->board($board);
		$sourcePath = $this->resolvePath($source, $allowExternal);
		if (!is_dir($sourcePath))
		{
			throw new InvalidArgumentException("Style source is not a directory: $source");
		}

		$name = $this->styleName($sourcePath);
		$target = $this->project->boardPath($board) . '/styles/' . $name;
		$styles = $boardConfig['styles'] ?? [];
		foreach (array_keys($styles) as $mountedName)
		{
			if ($this->project->namesEqual((string) $mountedName, $name) && (string) $mountedName !== $name)
			{
				throw new InvalidArgumentException("Style is already mounted with different letter case: $mountedName");
			}
		}

		if (file_exists($target) || is_link($target))
		{
			if (!$copy && (is_link($target) || !$this->isStylePath($target)))
			{
				$this->project->deleteTree($target);
			}
			else if (!$copy && isset($styles[$name]) && ($styles[$name]['mode'] ?? '') === 'bind')
			{
				$styles[$name] = ['mode' => 'bind', 'source' => $sourcePath];
				$boardConfig['styles'] = $styles;
				$this->project->appendBoard($boardConfig);

				return ['name' => $name, 'source' => $sourcePath, 'target' => '/var/www/html/styles/' . $name, 'mode' => 'bind'];
			}
			else
			{
				throw new RuntimeException("Style target already exists: $target");
			}
		}

		if ($copy)
		{
			$this->project->copyTree($sourcePath, $target);
			$mode = 'copy';
			$styles[$name] = ['mode' => 'copy', 'source' => $target];
		}
		else
		{
			$mode = 'bind';
			$styles[$name] = ['mode' => 'bind', 'source' => $sourcePath];
			$target = '/var/www/html/styles/' . $name;
		}

		$boardConfig['styles'] = $styles;
		$this->project->appendBoard($boardConfig);

		return ['name' => $name, 'source' => $sourcePath, 'target' => $target, 'mode' => $mode];
	}

	public function discover(string $source, bool $allowExternal = false): array
	{
		$sourcePath = $this->resolvePath($source, $allowExternal);
		if (!is_dir($sourcePath))
		{
			throw new InvalidArgumentException("Style search path is not a directory: $source");
		}

		$found = [];
		$this->discoverStyles($sourcePath, $found);
		sort($found);

		return $found;
	}

	/** Removes copied files or registry state for a bind mount. */
	public function unmount(string $board, string $name): string
	{
		$boardConfig = $this->project->board($board);
		$this->assertStyleName($name);
		$target = $this->project->boardPath($board) . '/styles/' . $name;
		$styles = $boardConfig['styles'] ?? [];
		$isBind = isset($styles[$name]) && ($styles[$name]['mode'] ?? '') === 'bind';

		if (!isset($styles[$name]) && !file_exists($target) && !is_link($target))
		{
			throw new InvalidArgumentException("Style is not mounted: $name");
		}

		if (!$isBind)
		{
			$this->project->deleteTree($target);
			$this->project->removeEmptyParents(dirname($target), $this->project->boardPath($board) . '/styles');
		}

		unset($styles[$name]);
		$boardConfig['styles'] = $styles;
		$this->project->appendBoard($boardConfig);

		return $isBind ? '/var/www/html/styles/' . $name : $target;
	}

	public function cleanupStaleTarget(string $board, string $name): void
	{
		$this->assertStyleName($name);
		$target = $this->project->boardPath($board) . '/styles/' . $name;
		$this->project->deleteTree($target);
		$this->project->removeEmptyParents(dirname($target), $this->project->boardPath($board) . '/styles');
	}

	public function list(string $board): array
	{
		$boardConfig = $this->project->board($board);
		$mounted = [];
		foreach (($boardConfig['styles'] ?? []) as $name => $style)
		{
			$mounted[$name] = [
				'name' => $name,
				'mode' => $style['mode'] ?? 'bind',
				'target' => '/var/www/html/styles/' . $name,
				'source' => $style['source'] ?? '',
			];
		}

		return array_values($mounted);
	}

	private function resolvePath(string $path, bool $allowExternal): string
	{
		return $this->project->resolveDropZonePath(
			$path,
			$this->project->customisationsPath(),
			$allowExternal,
			"Style path must be under customisations/. Use --allow-external only for trusted local paths."
		);
	}

	private function styleName(string $sourcePath): string
	{
		if (!$this->isStylePath($sourcePath))
		{
			throw new InvalidArgumentException("Style source must contain style.cfg: $sourcePath");
		}

		$name = basename($sourcePath);
		$this->assertStyleName($name);

		return $name;
	}

	private function isStylePath(string $path): bool
	{
		return is_file($path . '/style.cfg');
	}

	private function assertStyleName(string $name): void
	{
		if (!preg_match('/^[A-Za-z0-9_-]+$/', $name))
		{
			throw new InvalidArgumentException("Invalid style name: $name");
		}
	}

	private function discoverStyles(string $path, array &$found): void
	{
		if ($this->isStylePath($path))
		{
			$found[] = realpath($path) ?: $path;
			return;
		}

		foreach (scandir($path) ?: [] as $item)
		{
			if ($item === '.' || $item === '..')
			{
				continue;
			}

			$child = $path . '/' . $item;
			if (is_dir($child) && !is_link($child))
			{
				$this->discoverStyles($child, $found);
			}
		}
	}

}
