<?php

namespace QuickInstall\Sandbox;

class Project
{
	private string $root;
	private string $workspace;

	public function __construct(string $root)
	{
		$this->root = rtrim($root, '/');
		$this->workspace = $this->root . '/.qi';
	}

	public function init(): void
	{
		$extensionsPath = $this->extensionsPath();
		if (!is_dir($extensionsPath) && !mkdir($extensionsPath, 0775, true))
		{
			throw new \RuntimeException("Unable to create $extensionsPath");
		}

		$stylesPath = $this->stylesPath();
		if (!is_dir($stylesPath) && !mkdir($stylesPath, 0775, true))
		{
			throw new \RuntimeException("Unable to create $stylesPath");
		}

		foreach (['', '/sources', '/boards', '/runtime', '/db', '/cache'] as $dir)
		{
			$path = $this->workspace . $dir;
			if (!is_dir($path) && !mkdir($path, 0775, true))
			{
				throw new \RuntimeException("Unable to create $path");
			}
		}

		foreach (['sources.json' => [], 'boards.json' => []] as $file => $default)
		{
			$path = $this->workspace . '/' . $file;
			if (!file_exists($path))
			{
				$this->writeJson($file, $default);
			}
		}
	}

	public function workspacePath(string $path = ''): string
	{
		return $this->workspace . ($path === '' ? '' : '/' . ltrim($path, '/'));
	}

	public function rootPath(string $path = ''): string
	{
		return $this->root . ($path === '' ? '' : '/' . ltrim($path, '/'));
	}

	public function extensionsPath(): string
	{
		return $this->rootPath('extensions');
	}

	public function stylesPath(): string
	{
		return $this->rootPath('styles');
	}

	public function boardPath(string $name): string
	{
		$this->assertName($name, 'board');
		return $this->workspacePath('boards/' . $name);
	}

	public function sourcePath(string $version): string
	{
		$this->assertName($version, 'version');
		return $this->workspacePath('sources/phpbb-' . $version);
	}

	public function readJson(string $file, array $default): array
	{
		$path = $this->workspacePath($file);
		if (!file_exists($path))
		{
			return $default;
		}

		$data = json_decode((string) file_get_contents($path), true);
		return is_array($data) ? $data : $default;
	}

	public function writeJson(string $file, array $data): void
	{
		$path = $this->workspacePath($file);
		$encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
		if (file_put_contents($path, $encoded) === false)
		{
			throw new \RuntimeException("Unable to write $path");
		}
	}

	public function appendBoard(array $board): void
	{
		$boards = $this->readJson('boards.json', []);
		$boards[$board['name']] = $board;
		$this->writeJson('boards.json', $boards);
	}

	public function boards(): array
	{
		return $this->readJson('boards.json', []);
	}

	public function board(string $name): array
	{
		$boards = $this->boards();
		if (!isset($boards[$name]))
		{
			throw new \InvalidArgumentException("Unknown board: $name");
		}

		return $boards[$name];
	}

	public function removeBoard(string $name): void
	{
		$boards = $this->boards();
		unset($boards[$name]);
		$this->writeJson('boards.json', $boards);
	}

	public function runtimePath(string $name): string
	{
		$this->assertName($name, 'board');
		return $this->workspacePath('runtime/' . $name);
	}

	public function composePath(string $name): string
	{
		return $this->runtimePath($name) . '/compose.yml';
	}

	public function dbPath(string $name): string
	{
		$this->assertName($name, 'board');
		return $this->workspacePath('db/' . $name);
	}

	public function deleteTree(string $path): void
	{
		if (!file_exists($path) && !is_link($path))
		{
			return;
		}
		$this->assertWorkspacePath($path);

		if (is_file($path) || is_link($path))
		{
			if (!unlink($path))
			{
				throw new \RuntimeException("Unable to delete $path");
			}
			return;
		}

		$items = scandir($path);
		if ($items === false)
		{
			throw new \RuntimeException("Unable to scan $path");
		}

		foreach ($items as $item)
		{
			if ($item === '.' || $item === '..')
			{
				continue;
			}

			$this->deleteTree($path . '/' . $item);
		}

		if (!rmdir($path))
		{
			throw new \RuntimeException("Unable to delete $path");
		}
	}

	private function assertWorkspacePath(string $path): void
	{
		$path = $this->normalizeAbsolutePath($path);
		$workspace = $this->normalizeAbsolutePath($this->workspace);
		if ($path !== $workspace && strpos($path, $workspace . '/') !== 0)
		{
			throw new \RuntimeException("Refusing to delete path outside QuickInstall workspace: $path");
		}
	}

	private function normalizeAbsolutePath(string $path): string
	{
		if ($path === '')
		{
			return $this->root;
		}
		if ($path[0] !== '/')
		{
			$path = $this->rootPath($path);
		}

		$parts = [];
		foreach (explode('/', $path) as $part)
		{
			if ($part === '' || $part === '.')
			{
				continue;
			}
			if ($part === '..')
			{
				array_pop($parts);
				continue;
			}
			$parts[] = $part;
		}

		return '/' . implode('/', $parts);
	}

	public function assertName(string $name, string $label): void
	{
		if (!preg_match('/^[A-Za-z0-9._-]+$/', $name))
		{
			throw new \InvalidArgumentException("Invalid $label: $name");
		}
	}
}
