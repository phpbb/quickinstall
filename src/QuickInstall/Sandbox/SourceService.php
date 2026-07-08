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

class SourceService
{
	private Project $project;
	private ?Output $output;

	public function __construct(Project $project, ?Output $output = null)
	{
		$this->project = $project;
		$this->output = $output;
	}

	public function list(): array
	{
		$usage = $this->usageBySource();
		$sources = [];
		foreach ($this->project->readJson('sources.json', []) as $key => $source)
		{
			$sourceKey = $source['source_key'] ?? $key;
			$source['source_key'] = $sourceKey;
			$source['downloaded'] = is_file(($source['path'] ?? '') . '/common.php');
			$source['used_by'] = $usage[$sourceKey] ?? [];
			$sources[] = $source;
		}

		return $sources;
	}

	public function fetch(string $version, bool $git = false, ?string $url = null, bool $allowExternal = false): array
	{
		$this->project->init();
		$provider = new SourceProvider($this->project, $this->output);
		if ($git)
		{
			$provider->add($version, 'git', $url, $allowExternal);
		}

		return $provider->ensure($version);
	}

	public function supportedVersions(): array
	{
		return (new VersionMatrix())->list();
	}

	public function remove(string $version, bool $force = false): array
	{
		$source = $this->source($version);
		$usedBy = $this->usageBySource()[$source['source_key']] ?? [];
		if ($usedBy && !$force)
		{
			throw new InvalidArgumentException("Source {$source['source_key']} is used by board(s): " . implode(', ', $usedBy) . ". Destroy those boards first, or use --force.");
		}

		$this->removeRecordAndFiles($source);
		return ['source' => $source, 'used_by' => $usedBy];
	}

	public function prune(): array
	{
		$usage = $this->usageBySource();
		$removed = [];
		foreach ($this->project->readJson('sources.json', []) as $key => $source)
		{
			$sourceKey = $source['source_key'] ?? $key;
			if (!empty($usage[$sourceKey]))
			{
				continue;
			}

			$source['source_key'] = $sourceKey;
			$this->removeRecordAndFiles($source);
			$removed[] = $source;
		}

		return $removed;
	}

	private function source(string $version): array
	{
		$sources = $this->project->readJson('sources.json', []);
		if (isset($sources[$version]))
		{
			return $sources[$version] + ['source_key' => $version];
		}

		$selection = (new VersionMatrix())->resolve($version);
		if (isset($sources[$selection['source_key']]))
		{
			return $sources[$selection['source_key']] + ['source_key' => $selection['source_key']];
		}

		throw new InvalidArgumentException("Unknown source: $version");
	}

	private function removeRecordAndFiles(array $source): void
	{
		$sources = $this->project->readJson('sources.json', []);
		unset($sources[$source['source_key']]);
		$this->project->writeJson('sources.json', $sources);

		if (!empty($source['path']))
		{
			$this->project->deleteTree($source['path']);
		}
	}

	private function usageBySource(): array
	{
		$usage = [];
		foreach ($this->project->boards() as $board)
		{
			$sourceKey = $board['phpbb_source'] ?? null;
			if (!$sourceKey)
			{
				continue;
			}

			$usage[$sourceKey][] = $board['name'] ?? '(unknown)';
		}

		return $usage;
	}
}
