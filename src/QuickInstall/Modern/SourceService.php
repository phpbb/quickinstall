<?php

namespace QuickInstall\Modern;

class SourceService
{
	private Project $project;

	public function __construct(Project $project)
	{
		$this->project = $project;
	}

	public function add(string $version, bool $git = false, ?string $url = null): array
	{
		$this->project->init();
		return (new SourceProvider($this->project))->add($version, $git ? 'git' : 'composer', $url);
	}

	public function list(): array
	{
		return $this->project->readJson('sources.json', []);
	}

	public function fetch(string $version): array
	{
		return (new SourceProvider($this->project))->ensure($version);
	}

	public function supportedVersions(): array
	{
		return (new VersionMatrix())->list();
	}
}
