<?php

namespace QuickInstall\Tests\Support;

trait TempProjectTrait
{
	private array $temporaryPaths = [];

	protected function createTempProjectRoot(): string
	{
		$root = sys_get_temp_dir() . '/qi-test-' . bin2hex(random_bytes(8));
		if (!mkdir($root, 0775, true) && !is_dir($root))
		{
			throw new \RuntimeException("Unable to create temporary test root: $root");
		}

		$this->temporaryPaths[] = $root;
		return $root;
	}

	protected function tearDown(): void
	{
		foreach (array_reverse($this->temporaryPaths) as $path)
		{
			$this->removeTree($path);
		}

		$this->temporaryPaths = [];
	}

	private function removeTree(string $path): void
	{
		if (!file_exists($path) && !is_link($path))
		{
			return;
		}

		$realTmp = realpath(sys_get_temp_dir());
		$realPath = realpath($path);
		if ($realTmp === false || $realPath === false || ($realPath !== $realTmp && !str_starts_with($realPath, $realTmp . DIRECTORY_SEPARATOR)))
		{
			throw new \RuntimeException("Refusing to remove non-temporary path: $path");
		}

		if (is_file($path) || is_link($path))
		{
			unlink($path);
			return;
		}

		foreach (scandir($path) ?: [] as $item)
		{
			if ($item === '.' || $item === '..')
			{
				continue;
			}

			$this->removeTree($path . DIRECTORY_SEPARATOR . $item);
		}

		rmdir($path);
	}
}
