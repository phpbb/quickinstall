<?php

namespace QuickInstall\Sandbox;

class StyleManager
{
	private Project $project;

	public function __construct(Project $project)
	{
		$this->project = $project;
	}

	public function mount(string $board, string $source, bool $copy = false, bool $allowExternal = false): array
	{
		$boardConfig = $this->project->board($board);
		$sourcePath = $this->resolvePath($source, $allowExternal);
		if (!is_dir($sourcePath))
		{
			throw new \InvalidArgumentException("Style source is not a directory: $source");
		}

		$name = $this->styleName($sourcePath);
		$target = $this->project->boardPath($board) . '/styles/' . $name;
		$styles = $boardConfig['styles'] ?? [];

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
				throw new \RuntimeException("Style target already exists: $target");
			}
		}

		if ($copy)
		{
			$parent = dirname($target);
			if (!is_dir($parent) && !mkdir($parent, 0775, true))
			{
				throw new \RuntimeException("Unable to create style target parent: $parent");
			}

			$this->copyTree($sourcePath, $target);
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

	public function unmount(string $board, string $name): string
	{
		$boardConfig = $this->project->board($board);
		$this->assertStyleName($name);
		$target = $this->project->boardPath($board) . '/styles/' . $name;
		$styles = $boardConfig['styles'] ?? [];
		$isBind = isset($styles[$name]) && ($styles[$name]['mode'] ?? '') === 'bind';

		if (!isset($styles[$name]) && !file_exists($target) && !is_link($target))
		{
			throw new \InvalidArgumentException("Style is not mounted: $name");
		}

		if (!$isBind)
		{
			$this->project->deleteTree($target);
			$this->removeEmptyParents(dirname($target), $this->project->boardPath($board) . '/styles');
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
		$this->removeEmptyParents(dirname($target), $this->project->boardPath($board) . '/styles');
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
		$candidates = [
			$path,
			$this->project->rootPath($path),
			$this->project->stylesPath() . '/' . ltrim($path, '/'),
		];

		foreach ($candidates as $candidate)
		{
			$real = realpath($candidate);
			if ($real !== false && ($allowExternal || $this->isUnder($real, $this->project->stylesPath())))
			{
				return $real;
			}
		}

		throw new \InvalidArgumentException("Style path must be under styles/. Use --allow-external only for trusted local paths.");
	}

	private function isUnder(string $path, string $parent): bool
	{
		$parent = realpath($parent);
		return $parent !== false && ($path === $parent || strpos($path, $parent . '/') === 0);
	}

	private function styleName(string $sourcePath): string
	{
		if (!$this->isStylePath($sourcePath))
		{
			throw new \InvalidArgumentException("Style source must contain style.cfg: $sourcePath");
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
			throw new \InvalidArgumentException("Invalid style name: $name");
		}
	}

	private function copyTree(string $source, string $target): void
	{
		if (is_dir($target))
		{
			throw new \RuntimeException("Copy target already exists: $target");
		}

		if (!mkdir($target, 0775, true))
		{
			throw new \RuntimeException("Unable to create copy target: $target");
		}

		foreach (scandir($source) ?: [] as $item)
		{
			if ($item === '.' || $item === '..')
			{
				continue;
			}

			$src = $source . '/' . $item;
			$dst = $target . '/' . $item;
			if (is_dir($src) && !is_link($src))
			{
				$this->copyTree($src, $dst);
			}
			else if (!copy($src, $dst))
			{
				throw new \RuntimeException("Unable to copy $src to $dst");
			}
		}
	}

	private function removeEmptyParents(string $path, string $stop): void
	{
		while ($path !== $stop && is_dir($path) && count(array_diff(scandir($path) ?: [], ['.', '..'])) === 0)
		{
			rmdir($path);
			$path = dirname($path);
		}
	}
}
