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

class SourceProvider
{
	private Project $project;
	private Output $output;
	private ProcessRunner $processRunner;

	public function __construct(Project $project, ?Output $output = null, ?ProcessRunner $processRunner = null)
	{
		$this->project = $project;
		$this->output = $output ?: new BufferedOutput();
		$this->processRunner = $processRunner ?: new ProcessRunner($this->output);
	}

	public function add(string $version, string $type, ?string $url, bool $allowExternal = false): array
	{
		if (!in_array($type, ['composer', 'git'], true))
		{
			throw new InvalidArgumentException("Unsupported source type: $type");
		}
		$selection = (new VersionMatrix())->resolve($version, $type === 'git');
		$url = $url ?: ($type === 'git' ? 'https://github.com/phpbb/phpbb.git' : null);
		if ($type === 'git')
		{
			$this->validateGitUrl((string) $url);
		}
		if ($type === 'git' && !$allowExternal && $url !== 'https://github.com/phpbb/phpbb.git')
		{
			throw new InvalidArgumentException('Custom Git source URLs can run Composer code on your host. Use --allow-external only for trusted phpBB forks.');
		}

		$sources = $this->project->readJson('sources.json', []);
		$record = [
			'version' => $selection['version'],
			'source_key' => $selection['source_key'],
			'constraint' => $selection['constraint'],
			'branch' => $selection['branch'],
			'phpbb_branch' => $selection['phpbb_branch'],
			'php' => $selection['php'],
			'status' => $selection['status'],
			'type' => $type,
			'package' => $type === 'composer' ? 'phpbb/phpbb' : null,
			'url' => $url,
			'path' => $this->project->sourcePath($selection['source_key']),
			'registered_at' => gmdate('c'),
		];

		$sources[$selection['source_key']] = $record;
		$this->project->writeJson('sources.json', $sources);

		if ($type === 'composer' && $this->isFloatingSelection($selection))
		{
			return $this->ensure($version);
		}

		return $record;
	}

	private function validateGitUrl(string $url): void
	{
		if (!str_ends_with($url, '.git'))
		{
			throw new InvalidArgumentException('Git URL must point to a repository clone URL ending in .git.');
		}
	}

	public function ensure(string $version): array
	{
		$sources = $this->project->readJson('sources.json', []);
		try
		{
			$selection = (new VersionMatrix())->resolve($version);
		}
		catch (InvalidArgumentException $e)
		{
			if (isset($sources[$version]))
			{
				return $this->ensureRegisteredSource($sources[$version] + ['source_key' => $version]);
			}

			throw $e;
		}

		if ($this->isFloatingSelection($selection))
		{
			return $this->ensureFloating($version, $selection);
		}

		if (!isset($sources[$selection['source_key']]))
		{
			$this->output->write("Registering phpBB source: $version\n");
			$sources[$selection['source_key']] = $this->add($version, 'composer', null);
		}

		$source = $sources[$selection['source_key']];
		$source = $this->withSelectionDefaults($source, $selection);
		if (!file_exists($source['path'] . '/common.php'))
		{
			$this->output->write("Fetching phpBB source: $version\n");
			$this->fetch($source);
		}
		$source = $this->withInstalledSourceMetadata($source, $selection['php']);
		$sources[$source['source_key']] = $source;
		$this->project->writeJson('sources.json', $sources);

		return $source;
	}

	protected function ensureRegisteredSource(array $source): array
	{
		$source += [
			'path' => $this->project->sourcePath($source['source_key']),
		];
		if (!file_exists($source['path'] . '/common.php'))
		{
			$this->output->write("Fetching phpBB source: {$source['source_key']}\n");
			$this->fetch($source);
		}

		$source = $this->withInstalledSourceMetadata($source, $source['php'] ?? null);
		$sources = $this->project->readJson('sources.json', []);
		$sources[$source['source_key']] = $source;
		$this->project->writeJson('sources.json', $sources);

		return $source;
	}

	protected function ensureFloating(string $version, array $selection): array
	{
		$sources = $this->project->readJson('sources.json', []);
		$source = $this->withSelectionDefaults($sources[$selection['source_key']] ?? [], $selection);
		$source['type'] = $source['type'] ?? 'composer';
		$source['package'] = $source['type'] === 'composer' ? 'phpbb/phpbb' : ($source['package'] ?? null);
		$source['url'] = $source['url'] ?? ($source['type'] === 'git' ? 'https://github.com/phpbb/phpbb.git' : null);

		if (!isset($sources[$selection['source_key']]))
		{
			$this->output->write("Resolving phpBB source: $version\n");
		}

		$resolvedVersion = null;
		if ($source['type'] === 'composer' && $selection['constraint'] !== 'dev-master')
		{
			$resolvedVersion = $this->latestComposerVersion($selection['constraint']);
			if ($resolvedVersion !== null)
			{
				$resolvedKey = $this->sourceKey($resolvedVersion);
				$resolvedPath = $this->project->sourcePath($resolvedKey);
				if (is_file($resolvedPath . '/common.php'))
				{
					if (!isset($sources[$resolvedKey]))
					{
						$sources[$resolvedKey] = $this->recordForResolvedSource($source, $selection, $version, $resolvedVersion, $resolvedKey, $resolvedPath);
					}
					$sources[$resolvedKey] = $this->withInstalledSourceMetadata($sources[$resolvedKey], $selection['php']);
					$this->project->writeJson('sources.json', $sources);

					$this->removeUnusedFloatingSource($sources, $selection['source_key'], $resolvedPath);
					return $sources[$resolvedKey];
				}
			}
		}

		$tempKey = '_tmp-' . $selection['source_key'] . '-' . str_replace('.', '', uniqid('', true));
		$tempSource = $source;
		$tempSource['source_key'] = $tempKey;
		$tempSource['path'] = $this->project->sourcePath($tempKey);
		if ($resolvedVersion !== null)
		{
			$tempSource['constraint'] = $resolvedVersion;
		}

		$this->output->write("Fetching phpBB source: $version\n");
		try
		{
			$this->fetch($tempSource);
		}
		catch (RuntimeException $e)
		{
			if (file_exists($tempSource['path']) || is_link($tempSource['path']))
			{
				$this->project->deleteTree($tempSource['path']);
			}

			throw $e;
		}

		$actualVersion = $this->installedPhpbbVersion($tempSource['path']);
		$actualKey = $this->sourceKey($actualVersion);
		$actualPath = $this->project->sourcePath($actualKey);

		if (is_dir($actualPath) && file_exists($actualPath . '/common.php'))
		{
			$this->project->deleteTree($tempSource['path']);
		}
		else
		{
			if (file_exists($actualPath) || is_link($actualPath))
			{
				$this->project->deleteTree($actualPath);
			}
			error_clear_last();
			if (!@rename($tempSource['path'], $actualPath))
			{
				throw new RuntimeException("Unable to move source into place: $actualPath" . $this->lastFilesystemError());
			}
		}

		$sources = $this->project->readJson('sources.json', []);
		$record = $this->recordForResolvedSource($source, $selection, $version, $actualVersion, $actualKey, $actualPath);
		$record['registered_at'] = $sources[$actualKey]['registered_at'] ?? $record['registered_at'];

		$sources[$actualKey] = $record;
		if ($this->removeUnusedFloatingSource($sources, $selection['source_key'], $actualPath))
		{
			return $record;
		}

		$this->project->writeJson('sources.json', $sources);
		return $record;
	}

	protected function removeUnusedFloatingSource(array &$sources, string $sourceKey, string $resolvedPath): bool
	{
		if (!isset($sources[$sourceKey]) || $this->sourceInUse($sourceKey))
		{
			return false;
		}

		$floatingPath = $sources[$sourceKey]['path'] ?? $this->project->sourcePath($sourceKey);
		unset($sources[$sourceKey]);
		$this->project->writeJson('sources.json', $sources);
		if ($floatingPath !== $resolvedPath && (file_exists($floatingPath) || is_link($floatingPath)))
		{
			$this->project->deleteTree($floatingPath);
		}

		return true;
	}

	protected function recordForResolvedSource(array $source, array $selection, string $requested, string $actualVersion, string $actualKey, string $actualPath): array
	{
		return $this->withInstalledSourceMetadata([
			'version' => $actualVersion,
			'source_key' => $actualKey,
			'constraint' => $source['type'] === 'composer' && $selection['constraint'] !== 'dev-master' ? $actualVersion : $selection['constraint'],
			'branch' => $selection['branch'],
			'phpbb_branch' => $selection['phpbb_branch'],
			'php' => $selection['php'],
			'status' => $selection['status'],
			'type' => $source['type'],
			'package' => $source['type'] === 'composer' ? 'phpbb/phpbb' : null,
			'url' => $source['url'],
			'path' => $actualPath,
			'resolved_from' => $requested,
			'registered_at' => gmdate('c'),
			'fetched_at' => gmdate('c'),
		], $selection['php']);
	}

	protected function withSelectionDefaults(array $source, array $selection): array
	{
		return $source + [
			'version' => $selection['version'],
			'source_key' => $selection['source_key'],
			'constraint' => $selection['constraint'],
			'branch' => $selection['branch'],
			'phpbb_branch' => $selection['phpbb_branch'],
			'php' => $selection['php'],
			'status' => $selection['status'],
			'path' => $this->project->sourcePath($selection['source_key']),
		];
	}

	public function fetch(array $source): void
	{
		$path = $source['path'];
		$parent = dirname($path);
		if (!is_dir($parent) && !mkdir($parent, 0775, true) && !is_dir($parent))
		{
			throw new RuntimeException("Unable to create source directory: $parent");
		}

		if (is_dir($path) && $this->hasFiles($path))
		{
			if (file_exists($path . '/common.php'))
			{
				return;
			}

			$this->output->write("Removing incomplete source path: $path\n");
			$this->project->deleteTree($path);
		}

		if ($source['type'] === 'git')
		{
			$url = $source['url'] ?: 'https://github.com/phpbb/phpbb.git';
			$this->run([
				'git',
				'clone',
				'--branch',
				$source['branch'] ?: $source['version'],
				'--depth',
				'1',
				$url,
				$path,
			], dirname($path));

			$this->normalizeGitSourceRoot($path);
			$this->run($this->composerCommand(['install', '--no-interaction', '--ignore-platform-reqs']), $path);
			return;
		}

		if (is_dir($path))
		{
			error_clear_last();
			if (!@rmdir($path) && is_dir($path))
			{
				throw new RuntimeException("Unable to remove empty source directory: $path" . $this->lastFilesystemError());
			}
		}

		$command = $this->composerCommand([
			'create-project',
			'phpbb/phpbb',
			$path,
			'--no-interaction',
			'--ignore-platform-reqs',
		]);
		if (!empty($source['constraint']))
		{
			$insertAt = count($command) - 2;
			array_splice($command, $insertAt, 0, [$source['constraint']]);
		}

		$this->run($command, dirname($path));
	}

	protected function normalizeGitSourceRoot(string $path): void
	{
		if (is_file($path . '/composer.json') && is_file($path . '/common.php'))
		{
			return;
		}

		$appRoot = $path . '/phpBB';
		if (!is_file($appRoot . '/composer.json') || !is_file($appRoot . '/common.php'))
		{
			throw new RuntimeException("Git source does not contain phpBB at the repository root or phpBB/ subdirectory: $path");
		}

		$temporaryAppRoot = $path . '/.quickinstall-app-root';
		if (file_exists($temporaryAppRoot) || is_link($temporaryAppRoot))
		{
			$this->project->deleteTree($temporaryAppRoot);
		}
		error_clear_last();
		if (!@rename($appRoot, $temporaryAppRoot))
		{
			throw new RuntimeException("Unable to prepare Git source root: $appRoot" . $this->lastFilesystemError());
		}

		foreach (scandir($path) ?: [] as $item)
		{
			if ($item === '.' || $item === '..' || $item === '.git' || $item === basename($temporaryAppRoot))
			{
				continue;
			}

			$this->project->deleteTree($path . '/' . $item);
		}

		foreach (scandir($temporaryAppRoot) ?: [] as $item)
		{
			if ($item === '.' || $item === '..')
			{
				continue;
			}

			$source = $temporaryAppRoot . '/' . $item;
			$target = $path . '/' . $item;
			if (file_exists($target) || is_link($target))
			{
				throw new RuntimeException("Unable to normalize Git source. Target already exists: $target");
			}
			error_clear_last();
			if (!@rename($source, $target))
			{
				throw new RuntimeException("Unable to move Git source file into place: $source" . $this->lastFilesystemError());
			}
		}

		$this->project->deleteTree($temporaryAppRoot);
	}

	protected function hasFiles(string $path): bool
	{
		$files = scandir($path);
		return $files !== false && count(array_diff($files, ['.', '..'])) > 0;
	}

	private function lastFilesystemError(): string
	{
		$error = error_get_last();
		$message = is_array($error) ? trim((string) ($error['message'] ?? '')) : '';
		return $message !== '' ? ": $message" : '';
	}

	protected function installedPhpbbVersion(string $path): string
	{
		$version = $this->detectedPhpbbVersion($path);
		if ($version !== null)
		{
			return $version;
		}

		throw new RuntimeException("Unable to determine phpBB version from source: $path");
	}

	protected function withInstalledSourceMetadata(array $source, ?string $defaultPhp): array
	{
		$detectedVersion = $this->detectedPhpbbVersion($source['path'] ?? '');
		if ($detectedVersion !== null)
		{
			$source = $this->withDetectedPhpbbMetadata($source, $detectedVersion);
			$defaultPhp = $source['php'] ?? null;
			if (($source['type'] ?? '') === 'git' && ($defaultPhp === null || $defaultPhp === ''))
			{
				throw new RuntimeException("Unable to determine PHP runtime from phpBB version for Git source: {$source['path']}");
			}
		}
		else if (($source['type'] ?? '') === 'git')
		{
			throw new RuntimeException("Unable to determine phpBB version from Git source: {$source['path']}");
		}

		$requirement = $this->phpRequirement($source['path'] ?? '');
		if ($requirement !== null)
		{
			$source['php_requirement'] = $requirement;
			$source['php'] = $this->runtimeForRequirement($defaultPhp, $requirement);
		}

		return $source;
	}

	protected function detectedPhpbbVersion(string $path): ?string
	{
		$phpbbCli = $path . '/install/phpbbcli.php';
		if (is_file($phpbbCli) && preg_match("/define\\('PHPBB_VERSION',\\s*'([^']+)'\\)/", (string) file_get_contents($phpbbCli), $matches))
		{
			return $matches[1];
		}

		return null;
	}

	protected function withDetectedPhpbbMetadata(array $source, string $detectedVersion): array
	{
		$source['detected_phpbb_version'] = $detectedVersion;
		if (!preg_match('/^(\d+\.\d+\.\d+)/', $detectedVersion, $matches))
		{
			return $source;
		}

		try
		{
			$selection = (new VersionMatrix())->resolve($matches[1]);
		}
		catch (InvalidArgumentException $e)
		{
			return $source;
		}

		$source['phpbb_branch'] = $selection['phpbb_branch'];
		$source['php'] = $selection['php'];

		return $source;
	}

	protected function phpRequirement(string $path): ?string
	{
		$composer = $path . '/composer.json';
		if (!is_file($composer))
		{
			return null;
		}

		$data = json_decode((string) file_get_contents($composer), true);
		if (!is_array($data) || empty($data['require']['php']) || !is_string($data['require']['php']))
		{
			return null;
		}

		return $data['require']['php'];
	}

	protected function runtimeForRequirement(?string $defaultPhp, string $requirement): ?string
	{
		$minimum = $this->minimumPhpFromRequirement($requirement);
		if ($defaultPhp === null || $defaultPhp === '')
		{
			return $minimum;
		}
		if ($minimum === null || version_compare($defaultPhp, $minimum, '>='))
		{
			return $defaultPhp;
		}

		return $minimum;
	}

	protected function minimumPhpFromRequirement(string $requirement): ?string
	{
		if (!preg_match_all('/(?<!\d)(?:(>=|>|<=|<|!=|=|==|\\^|~)\\s*)?(\d+\\.\d+)(?:\\.\d+)?(?!\d)/', $requirement, $matches, PREG_SET_ORDER))
		{
			return null;
		}

		$minimum = null;
		foreach ($matches as $match)
		{
			$operator = $match[1] ?? '';
			if (in_array($operator, ['<', '<=', '!='], true))
			{
				continue;
			}

			$version = $match[2];
			if ($minimum === null || version_compare($version, $minimum, '<'))
			{
				$minimum = $version;
			}
		}

		return $minimum;
	}

	protected function isFloatingSelection(array $selection): bool
	{
		return in_array($selection['constraint'], ['3.3.*', '3.2.*', 'dev-master'], true);
	}

	protected function sourceInUse(string $sourceKey): bool
	{
		foreach ($this->project->boards() as $board)
		{
			if (($board['phpbb_source'] ?? null) === $sourceKey)
			{
				return true;
			}
		}

		return false;
	}

	protected function sourceKey(string $value): string
	{
		return preg_replace('/[^A-Za-z0-9._-]/', '-', $value);
	}

	protected function latestComposerVersion(string $constraint): ?string
	{
		if (!str_ends_with($constraint, '.*'))
		{
			return null;
		}

		$result = $this->capture($this->composerCommand(['show', 'phpbb/phpbb', $constraint, '--all', '--format=json']), $this->project->rootPath());
		if ($result['status'] !== 0)
		{
			return null;
		}

		$data = json_decode($result['output'], true);
		if (!is_array($data) || empty($data['versions']) || !is_array($data['versions']))
		{
			return null;
		}

		$prefix = substr($constraint, 0, -1);
		$latest = null;
		foreach ($data['versions'] as $version)
		{
			if (!is_string($version) || !str_starts_with($version, $prefix) || !preg_match('/^\d+\.\d+\.\d+$/', $version))
			{
				continue;
			}
			if ($latest === null || version_compare($version, $latest, '>'))
			{
				$latest = $version;
			}
		}

		return $latest;
	}

	protected function composerCommand(array $arguments): array
	{
		$composer = $this->findCommand('composer');
		if ($composer !== null)
		{
			return array_merge([$composer], $arguments);
		}

		$phar = $this->project->rootPath('composer.phar');
		if (is_file($phar))
		{
			return array_merge([PHP_BINARY, $phar], $arguments);
		}

		throw new RuntimeException('Composer is not available. Install Composer or restore composer.phar in the QuickInstall project root.');
	}

	protected function isCommandAvailable(string $command): bool
	{
		return $this->findCommand($command) !== null;
	}

	protected function findCommand(string $command): ?string
	{
		$extensions = [''];
		if (PHP_OS_FAMILY === 'Windows')
		{
			$extensions = array_merge($extensions, array_filter(array_map('strtolower', explode(';', (string) getenv('PATHEXT')))));
			if (!in_array('.bat', $extensions, true))
			{
				$extensions[] = '.bat';
			}
			if (!in_array('.cmd', $extensions, true))
			{
				$extensions[] = '.cmd';
			}
			if (!in_array('.exe', $extensions, true))
			{
				$extensions[] = '.exe';
			}
		}

		foreach (explode(PATH_SEPARATOR, (string) getenv('PATH')) as $path)
		{
			foreach ($extensions as $extension)
			{
				$candidate = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $command . $extension;
				if (is_file($candidate) && (PHP_OS_FAMILY === 'Windows' || is_executable($candidate)))
				{
					return $candidate;
				}
			}
		}

		return null;
	}

	protected function run(array $command, string $cwd): void
	{
		try
		{
			$this->processRunner->run($command, $cwd);
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage() . $this->commandHint($command), 0, $e);
		}
	}

	protected function capture(array $command, string $cwd): array
	{
		$result = $this->processRunner->capture($command, $cwd);

		return [
			'status' => $result['exit_code'],
			'output' => $result['output'],
		];
	}

	protected function commandHint(array $command): string
	{
		if (($command[0] ?? '') === 'composer' || basename((string) ($command[1] ?? '')) === 'composer.phar')
		{
			return "\nInstall Composer, make sure composer is available in PATH, or restore composer.phar in the QuickInstall project root.";
		}

		if (($command[0] ?? '') === 'git')
		{
			return "\nInstall Git or make sure git is available in PATH.";
		}

		return '';
	}
}
