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

use RuntimeException;

class UpdateService
{
	private const ENDPOINT = 'https://www.phpbb.com/customise/db/official_tool/phpbb3_quickinstall/version_check';
	private const CACHE_FILE = 'update-check.json';
	private const SUCCESS_TTL = 86400;
	private const ERROR_TTL = 3600;
	private const TIMEOUT = 3;

	private Project $project;
	private ?string $endpoint;

	public function __construct(Project $project, ?string $endpoint = null)
	{
		$this->project = $project;
		$this->endpoint = $endpoint ?: self::ENDPOINT;
	}

	public function getUpdate(): ?array
	{
		if (!is_dir($this->project->workspacePath()))
		{
			return null;
		}

		try
		{
			// Use fresh cache before touching network.
			$cached = $this->cachedUpdate();
			if ($cached !== false)
			{
				return $cached;
			}

			// Fetch legacy version metadata, then cache normalized result.
			$data = $this->fetchVersionData();
			$update = $this->latestUpdate($data);
			$this->writeCache([
				'checked_at' => time(),
				'current_version' => $this->currentVersion(),
				'update' => $update,
				'error' => null,
			]);

			return $update;
		}
		catch (RuntimeException $e)
		{
			// Cache failures briefly to avoid repeated slow checks.
			$this->writeCache([
				'checked_at' => time(),
				'current_version' => $this->safeCurrentVersion(),
				'update' => null,
				'error' => $e->getMessage(),
			]);
			return null;
		}
	}

	public function currentVersion(): string
	{
		$composerPath = $this->project->rootPath('composer.json');
		if (!is_file($composerPath))
		{
			$composerPath = dirname(__DIR__, 3) . '/composer.json';
		}

		$data = json_decode((string) file_get_contents($composerPath), true);
		if (!is_array($data) || empty($data['version']))
		{
			throw new RuntimeException('Unable to read QuickInstall version.');
		}

		return (string) $data['version'];
	}

	private function safeCurrentVersion(): string
	{
		try
		{
			return $this->currentVersion();
		}
		catch (RuntimeException $e)
		{
			return '';
		}
	}

	private function cachedUpdate()
	{
		$cache = $this->readCache();
		if (!$cache)
		{
			return false;
		}
		if (($cache['current_version'] ?? '') !== $this->currentVersion())
		{
			return false;
		}

		// Expire failures sooner so transient network problems recover.
		$checkedAt = (int) ($cache['checked_at'] ?? 0);
		$ttl = empty($cache['error']) ? self::SUCCESS_TTL : self::ERROR_TTL;
		if ($checkedAt <= 0 || $checkedAt + $ttl < time())
		{
			return false;
		}

		return is_array($cache['update'] ?? null) ? $cache['update'] : null;
	}

	private function fetchVersionData(): array
	{
		// Keep UI and CLI responsive if phpBB.com is unavailable.
		$context = stream_context_create([
			'http' => [
				'timeout' => self::TIMEOUT,
				'ignore_errors' => true,
			],
		]);
		$json = @file_get_contents($this->endpoint, false, $context);
		if ($json === false || $json === '')
		{
			throw new RuntimeException('VERSIONCHECK_FAIL');
		}

		$data = json_decode($json, true);
		if (!is_array($data) || empty($data['stable']) || !is_array($data['stable']))
		{
			throw new RuntimeException('VERSIONCHECK_FAIL');
		}

		return $data;
	}

	private function latestUpdate(array $data): ?array
	{
		$currentVersion = $this->currentVersion();
		$updates = [];

		// Match legacy stable-only behavior.
		foreach ($data['stable'] as $update)
		{
			if (!is_array($update) || empty($update['current']))
			{
				continue;
			}
			if (version_compare((string) $update['current'], $currentVersion, '>'))
			{
				$updates[] = [
					'current' => (string) $update['current'],
					'download' => (string) ($update['download'] ?? ''),
				];
			}
		}

		return $updates ? array_pop($updates) : null;
	}

	private function readCache(): ?array
	{
		$data = $this->project->readJson(self::CACHE_FILE, []);
		return $data ?: null;
	}

	private function writeCache(array $data): void
	{
		if (!is_dir($this->project->workspacePath()))
		{
			return;
		}

		$this->project->writeJson(self::CACHE_FILE, $data);
	}
}
