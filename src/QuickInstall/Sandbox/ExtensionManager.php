<?php

namespace QuickInstall\Sandbox;

class ExtensionManager
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
			throw new \InvalidArgumentException("Extension source is not a directory: $source");
		}

		$name = $this->extensionName($sourcePath);
		[$vendor, $extension] = explode('/', $name, 2);
		$target = $this->project->boardPath($board) . '/ext/' . $vendor . '/' . $extension;
		$extensions = $boardConfig['extensions'] ?? [];

		if (file_exists($target) || is_link($target))
		{
			if (!$copy && (is_link($target) || !is_file($target . '/composer.json')))
			{
				$this->project->deleteTree($target);
			}
			else if (!$copy && isset($extensions[$name]) && ($extensions[$name]['mode'] ?? '') === 'bind')
			{
				$extensions[$name] = ['mode' => 'bind', 'source' => $sourcePath];
				$boardConfig['extensions'] = $extensions;
				$this->project->appendBoard($boardConfig);

				return ['name' => $name, 'source' => $sourcePath, 'target' => '/var/www/html/ext/' . $name, 'mode' => 'bind'];
			}
			else
			{
				throw new \RuntimeException("Extension target already exists: $target");
			}
		}

		if ($copy)
		{
			$parent = dirname($target);
			if (!is_dir($parent) && !mkdir($parent, 0775, true))
			{
				throw new \RuntimeException("Unable to create extension target parent: $parent");
			}

			$this->copyTree($sourcePath, $target);
			$mode = 'copy';
			$extensions[$name] = ['mode' => 'copy', 'source' => $target];
		}
		else
		{
			$mode = 'bind';
			$extensions[$name] = ['mode' => 'bind', 'source' => $sourcePath];
			$target = '/var/www/html/ext/' . $name;
		}

		$boardConfig['extensions'] = $extensions;
		$this->project->appendBoard($boardConfig);

		return ['name' => $name, 'source' => $sourcePath, 'target' => $target, 'mode' => $mode];
	}

	public function unmount(string $board, string $name): string
	{
		$boardConfig = $this->project->board($board);
		$this->assertExtensionName($name);
		$target = $this->project->boardPath($board) . '/ext/' . $name;
		$extensions = $boardConfig['extensions'] ?? [];
		$isBind = isset($extensions[$name]) && ($extensions[$name]['mode'] ?? '') === 'bind';

		if (!isset($extensions[$name]) && !file_exists($target) && !is_link($target))
		{
			throw new \InvalidArgumentException("Extension is not mounted: $name");
		}

		if (!$isBind)
		{
			$this->project->deleteTree($target);
			$this->removeEmptyParents(dirname($target), $this->project->boardPath($board) . '/ext');
		}

		unset($extensions[$name]);
		$boardConfig['extensions'] = $extensions;
		$this->project->appendBoard($boardConfig);

		return $isBind ? '/var/www/html/ext/' . $name : $target;
	}

	public function cleanupStaleTarget(string $board, string $name): void
	{
		$this->assertExtensionName($name);
		$target = $this->project->boardPath($board) . '/ext/' . $name;
		$this->project->deleteTree($target);
		$this->removeEmptyParents(dirname($target), $this->project->boardPath($board) . '/ext');
	}


	public function list(string $board): array
	{
		$boardConfig = $this->project->board($board);
		$mounted = [];
		foreach (($boardConfig['extensions'] ?? []) as $name => $extension)
		{
			$mounted[$name] = [
				'name' => $name,
				'mode' => $extension['mode'] ?? 'bind',
				'target' => '/var/www/html/ext/' . $name,
				'source' => $extension['source'] ?? '',
			];
		}

		$extPath = $this->project->boardPath($board) . '/ext';
		if (!is_dir($extPath))
		{
			return array_values($mounted);
		}

		foreach (scandir($extPath) ?: [] as $vendor)
		{
			if ($vendor === '.' || $vendor === '..' || !is_dir($extPath . '/' . $vendor))
			{
				continue;
			}

			foreach (scandir($extPath . '/' . $vendor) ?: [] as $extension)
			{
				if ($extension === '.' || $extension === '..')
				{
					continue;
				}

				$path = $extPath . '/' . $vendor . '/' . $extension;
				if (!is_dir($path) && !is_link($path))
				{
					continue;
				}

				$name = $vendor . '/' . $extension;
				if (isset($mounted[$name]))
				{
					continue;
				}

				if (!is_file($path . '/composer.json'))
				{
					continue;
				}

				$mounted[$name] = [
					'name' => $name,
					'mode' => is_link($path) ? 'symlink' : 'copy',
					'target' => $path,
					'source' => is_link($path) ? readlink($path) : $path,
				];
			}
		}

		return array_values($mounted);
	}

	private function resolvePath(string $path, bool $allowExternal): string
	{
		$candidates = [
			$path,
			$this->project->rootPath($path),
			$this->project->extensionsPath() . '/' . ltrim($path, '/'),
		];

		foreach ($candidates as $candidate)
		{
			$real = realpath($candidate);
			if ($real !== false && ($allowExternal || $this->isUnder($real, $this->project->extensionsPath())))
			{
				return $real;
			}
		}

		throw new \InvalidArgumentException("Extension path must be under extensions/. Use --allow-external only for trusted local paths.");
	}

	private function isUnder(string $path, string $parent): bool
	{
		$parent = realpath($parent);
		return $parent !== false && ($path === $parent || strpos($path, $parent . '/') === 0);
	}

	private function extensionName(string $sourcePath): string
	{
		$composer = $sourcePath . '/composer.json';
		if (!is_file($composer))
		{
			throw new \InvalidArgumentException("Extension source must contain composer.json: $sourcePath");
		}

		$data = json_decode((string) file_get_contents($composer), true);
		if (!is_array($data) || empty($data['name']))
		{
			throw new \InvalidArgumentException("Extension composer.json must contain a name like vendor/extension: $composer");
		}

		$name = strtolower((string) $data['name']);
		$this->assertExtensionName($name);

		return $name;
	}

	private function assertExtensionName(string $name): void
	{
		if (!preg_match('/^[a-z0-9_.-]+\/[a-z0-9_.-]+$/', $name))
		{
			throw new \InvalidArgumentException("Invalid extension name: $name");
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
