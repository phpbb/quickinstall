<?php
/**
 *
 * QuickInstall development fixtures
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace QuickInstallSeed;

use RuntimeException;

class SeedContext
{
	public const PASSWORD = 'password';

	public $db;
	public $user;
	public $auth;
	public $config;
	public $container;
	public $root;
	public $phpEx;
	public $preset;
	public $seed;
	public $prefix;
	public $manifest;
	private $originalUser;

	public function __construct($db, $user, $auth, $config, $container, string $root, string $phpEx, string $preset, int $seed)
	{
		$this->db = $db;
		$this->user = $user;
		$this->auth = $auth;
		$this->config = $config;
		$this->container = $container;
		$this->root = rtrim($root, '/') . '/';
		$this->phpEx = $phpEx;
		$this->preset = $preset;
		$this->seed = $seed;
		$this->prefix = $preset === 'development'
			? sprintf('[QI %d] ', $seed)
			: sprintf('[QI %s %d] ', $preset, $seed);
		$this->resetManifest();
		$this->originalUser = $user->data;
	}

	public function resetManifest(): void
	{
		$this->manifest = [
			'version' => 3,
			'preset' => $this->preset,
			'seed' => $this->seed,
			'created_at' => gmdate('c'),
			'ids' => [],
			'files' => [],
			'storage_files' => [],
			'meta' => [],
		];
	}

	public function status(string $message): void
	{
		echo $message . "\n";
		flush();
	}

	public function manifestPath(): string
	{
		$preset = preg_replace('/[^A-Za-z0-9._-]/', '_', $this->preset);
		return $this->root . 'store/qi-' . $preset . '-seed-' . $this->seed . '.json';
	}

	public function loadManifest(): bool
	{
		$path = $this->manifestPath();
		if (!is_file($path))
		{
			return false;
		}

		$data = json_decode((string) file_get_contents($path), true);
		if (!is_array($data) || ($data['preset'] ?? '') !== $this->preset || (int) ($data['seed'] ?? 0) !== $this->seed)
		{
			throw new RuntimeException("Invalid seed manifest: $path");
		}
		$data['version'] = 3;
		$data['storage_files'] = $data['storage_files'] ?? [];
		$this->manifest = $data;
		return true;
	}

	public function saveManifest(): void
	{
		$path = $this->manifestPath();
		$directory = dirname($path);
		if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory))
		{
			throw new RuntimeException("Unable to create seed manifest directory: $directory");
		}

		$json = json_encode($this->manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		if ($json === false || file_put_contents($path, $json . "\n", LOCK_EX) === false)
		{
			throw new RuntimeException("Unable to write seed manifest: $path");
		}
	}

	public function addId(string $type, int $id): void
	{
		if ($id < 1)
		{
			return;
		}
		if (!isset($this->manifest['ids'][$type]))
		{
			$this->manifest['ids'][$type] = [];
		}
		$this->manifest['ids'][$type][] = $id;
		$this->manifest['ids'][$type] = array_values(array_unique(array_map('intval', $this->manifest['ids'][$type])));
	}

	public function ids(string $type): array
	{
		return array_values(array_unique(array_map('intval', $this->manifest['ids'][$type] ?? [])));
	}

	public function addFile(string $path): void
	{
		$path = str_replace('\\', '/', $path);
		if (strpos($path, $this->root) !== 0)
		{
			throw new RuntimeException("Refusing to register seed file outside phpBB root: $path");
		}
		$this->manifest['files'][] = $path;
		$this->manifest['files'] = array_values(array_unique($this->manifest['files']));
	}

	public function writeFile(string $storageName, string $relativePath, string $absolutePath, string $content): void
	{
		$serviceName = 'storage.' . $storageName;
		if ($this->container->has($serviceName))
		{
			$storage = $this->container->get($serviceName);
			if (!$storage->exists($relativePath))
			{
				$stream = fopen('php://temp', 'w+b');
				if ($stream === false || fwrite($stream, $content) !== strlen($content) || rewind($stream) === false)
				{
					if (is_resource($stream))
					{
						fclose($stream);
					}
					throw new RuntimeException("Unable to prepare generated storage file: $relativePath");
				}
				try
				{
					$storage->write($relativePath, $stream);
				}
				finally
				{
					fclose($stream);
				}
			}
			$this->addStorageFile($storageName, $relativePath);
		}
		else if (file_put_contents($absolutePath, $content, LOCK_EX) === false)
		{
			throw new RuntimeException("Unable to write generated file: $absolutePath");
		}

		$this->addFile($absolutePath);
	}

	public function storageDirectory(string $storageName, string $fallback): string
	{
		return trim((string) ($this->config['storage\\' . $storageName . '\\config\\path'] ?? $fallback), '/');
	}

	public function reconcileStorageFiles(): void
	{
		$paths = [
			'attachment' => $this->storageDirectory('attachment', (string) ($this->config['upload_path'] ?? 'files')),
			'avatar' => $this->storageDirectory('avatar', (string) ($this->config['avatar_path'] ?? 'images/avatars/upload')),
		];
		foreach ($this->manifest['files'] ?? [] as $absolutePath)
		{
			$absolutePath = str_replace('\\', '/', (string) $absolutePath);
			foreach ($paths as $storageName => $directory)
			{
				$prefix = $this->root . $directory . '/';
				if (strpos($absolutePath, $prefix) !== 0)
				{
					continue;
				}
				$this->trackStorageFile($storageName, substr($absolutePath, strlen($prefix)), $absolutePath);
			}
		}
	}

	public function storageFilesExist(): bool
	{
		foreach ($this->manifest['storage_files'] ?? [] as $file)
		{
			$storageName = (string) ($file['storage'] ?? '');
			$relativePath = (string) ($file['path'] ?? '');
			$serviceName = 'storage.' . $storageName;
			if (!$this->container->has($serviceName)
				|| !$this->container->get($serviceName)->exists($relativePath)
				|| ($this->isLocalStorage($storageName) && !is_file($this->storagePath($storageName, $relativePath))))
			{
				return false;
			}
		}
		return true;
	}

	public function storageFileCount(): int
	{
		return count($this->manifest['storage_files'] ?? []);
	}

	public function usesStorage(): bool
	{
		return $this->container->has('storage.attachment') || $this->container->has('storage.avatar');
	}

	private function addStorageFile(string $storageName, string $relativePath): void
	{
		$file = [
			'storage' => $storageName,
			'path' => $relativePath,
		];
		foreach ($this->manifest['storage_files'] ?? [] as $registered)
		{
			if ($registered === $file)
			{
				return;
			}
		}
		$this->manifest['storage_files'][] = $file;
	}

	private function trackStorageFile(string $storageName, string $relativePath, string $absolutePath): void
	{
		$serviceName = 'storage.' . $storageName;
		if (!$this->container->has($serviceName))
		{
			return;
		}
		$storage = $this->container->get($serviceName);
		if (!$storage->exists($relativePath))
		{
			if (!is_file($absolutePath) || !$this->container->has('storage.file_tracker'))
			{
				throw new RuntimeException("Unable to register generated storage file: $relativePath");
			}
			$this->container->get('storage.file_tracker')->track_file($storageName, $relativePath, (int) filesize($absolutePath));
		}
		$this->addStorageFile($storageName, $relativePath);
	}

	private function isLocalStorage(string $storageName): bool
	{
		return (string) ($this->config['storage\\' . $storageName . '\\provider'] ?? '') === 'phpbb\\storage\\provider\\local';
	}

	private function storagePath(string $storageName, string $relativePath): string
	{
		$directory = trim((string) ($this->config['storage\\' . $storageName . '\\config\\path'] ?? ''), '/');
		return $this->root . ($directory !== '' ? $directory . '/' : '') . $relativePath;
	}

	public function setMeta(string $name, $value): void
	{
		$this->manifest['meta'][$name] = $value;
	}

	public function meta(string $name, $default = null)
	{
		return $this->manifest['meta'][$name] ?? $default;
	}

	public function groupId(string $name): int
	{
		$sql = 'SELECT group_id FROM ' . GROUPS_TABLE . "
			WHERE group_name = '" . $this->db->sql_escape($name) . "'";
		$result = $this->db->sql_query_limit($sql, 1);
		$id = (int) $this->db->sql_fetchfield('group_id');
		$this->db->sql_freeresult($result);
		return $id;
	}

	public function founderId(): int
	{
		$result = $this->db->sql_query_limit(
			'SELECT user_id FROM ' . USERS_TABLE . ' WHERE user_type = ' . USER_FOUNDER . ' ORDER BY user_id ASC',
			1
		);
		$id = (int) $this->db->sql_fetchfield('user_id');
		$this->db->sql_freeresult($result);
		return $id ?: 2;
	}

	public function banTable(): string
	{
		if (defined('BANS_TABLE'))
		{
			return constant('BANS_TABLE');
		}
		if (defined('BANLIST_TABLE'))
		{
			return constant('BANLIST_TABLE');
		}
		throw new RuntimeException('Unable to resolve the phpBB bans table.');
	}

	public function usesModernBans(): bool
	{
		return defined('BANS_TABLE');
	}

	public function switchUser(int $userId): void
	{
		$result = $this->db->sql_query_limit('SELECT * FROM ' . USERS_TABLE . ' WHERE user_id = ' . $userId, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		if (!$row)
		{
			throw new RuntimeException("Unable to switch seed user context: $userId");
		}

		$this->user->data = array_merge($this->user->data, $row);
		$this->user->data['is_registered'] = true;
		$this->auth->acl($this->user->data);
	}

	public function restoreUser(): void
	{
		$this->user->data = $this->originalUser;
		$this->auth->acl($this->user->data);
	}

	public function deleteRegisteredFiles(): void
	{
		foreach (array_reverse($this->manifest['storage_files'] ?? []) as $file)
		{
			$serviceName = 'storage.' . ($file['storage'] ?? '');
			$relativePath = (string) ($file['path'] ?? '');
			if ($relativePath !== '' && $this->container->has($serviceName))
			{
				$storage = $this->container->get($serviceName);
				if ($storage->exists($relativePath))
				{
					$storage->delete($relativePath);
				}
			}
		}
		foreach (array_reverse($this->manifest['files'] ?? []) as $path)
		{
			$path = str_replace('\\', '/', (string) $path);
			if (strpos($path, $this->root) !== 0)
			{
				continue;
			}
			if (is_file($path) && !unlink($path))
			{
				throw new RuntimeException("Unable to delete seed file: $path");
			}
		}
	}
}

abstract class BaseBuilder
{
	protected $context;

	public function __construct(SeedContext $context)
	{
		$this->context = $context;
	}

	protected function rowExists(string $table, string $column, int $id): bool
	{
		$result = $this->context->db->sql_query_limit(
			"SELECT $column FROM $table WHERE $column = $id",
			1
		);
		$exists = (bool) $this->context->db->sql_fetchrow($result);
		$this->context->db->sql_freeresult($result);
		return $exists;
	}
}

class AssetFactory
{
	public static function png(int $width, int $height, int $seed): string
	{
		$raw = '';
		$r1 = 35 + (($seed * 37) % 190);
		$g1 = 35 + (($seed * 59) % 190);
		$b1 = 35 + (($seed * 71) % 190);
		$r2 = 255 - $r1;
		$g2 = 255 - $g1;
		$b2 = 255 - $b1;

		for ($y = 0; $y < $height; $y++)
		{
			$raw .= "\x00";
			for ($x = 0; $x < $width; $x++)
			{
				$cellX = (int) floor($x * 5 / max(1, $width));
				$cellY = (int) floor($y * 5 / max(1, $height));
				$mirrorX = min($cellX, 4 - $cellX);
				$foreground = (($mirrorX * 11 + $cellY * 7 + $seed) % 3) !== 0;
				$raw .= chr($foreground ? $r1 : $r2);
				$raw .= chr($foreground ? $g1 : $g2);
				$raw .= chr($foreground ? $b1 : $b2);
			}
		}

		$header = pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0);
		return "\x89PNG\r\n\x1a\n"
			. self::chunk('IHDR', $header)
			. self::chunk('IDAT', gzcompress($raw, 9))
			. self::chunk('IEND', '');
	}

	public static function zip(int $seed): string
	{
		if (!class_exists('ZipArchive'))
		{
			throw new RuntimeException('Development attachment generation requires ZipArchive.');
		}

		$path = tempnam(sys_get_temp_dir(), 'qi-dev-');
		if ($path === false)
		{
			throw new RuntimeException('Unable to create temporary development ZIP.');
		}

		$zip = new \ZipArchive();
		if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true)
		{
			unlink($path);
			throw new RuntimeException('Unable to create development ZIP.');
		}
		$zip->addFromString('README.txt', "QuickInstall sample archive\n\nThis ZIP file is attached by the development seed.\nSeed: $seed\n");
		$zip->addFromString('sample/example-data.txt', "This is a plain-text file inside the sample archive.\n");
		$zip->addFromString('unicode/café.txt', "This filename includes a Unicode character.\n");
		$zip->close();

		$content = file_get_contents($path);
		unlink($path);
		if ($content === false)
		{
			throw new RuntimeException('Unable to read generated development ZIP.');
		}
		return $content;
	}

	private static function chunk(string $type, string $data): string
	{
		return pack('N', strlen($data)) . $type . $data . pack('N', crc32($type . $data));
	}
}
