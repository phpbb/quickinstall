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

	public function __construct(Project $project)
	{
		$this->project = $project;
	}

	public function add(string $version, string $type, ?string $url, bool $allowExternal = false): array
	{
		if (!in_array($type, ['composer', 'git'], true))
		{
			throw new InvalidArgumentException("Unsupported source type: $type");
		}
		$selection = (new VersionMatrix())->resolve($version, $type === 'git');
		$url = $url ?: ($type === 'git' ? 'https://github.com/phpbb/phpbb.git' : null);
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
			echo "Registering phpBB source: $version\n";
			$sources[$selection['source_key']] = $this->add($version, 'composer', null);
		}

		$source = $sources[$selection['source_key']];
		$source = $this->withSelectionDefaults($source, $selection);
		if (!file_exists($source['path'] . '/common.php'))
		{
			echo "Fetching phpBB source: $version\n";
			$this->fetch($source);
		}
		$source = $this->withInstalledSourceMetadata($source, $selection['php']);
		$sources[$source['source_key']] = $source;
		$this->project->writeJson('sources.json', $sources);

		return $source;
	}

	private function ensureRegisteredSource(array $source): array
	{
		$source += [
			'path' => $this->project->sourcePath($source['source_key']),
			'php'  => '8.1',
		];
		if (!file_exists($source['path'] . '/common.php'))
		{
			echo "Fetching phpBB source: {$source['source_key']}\n";
			$this->fetch($source);
		}

		$source = $this->withInstalledSourceMetadata($source, $source['php']);
		$sources = $this->project->readJson('sources.json', []);
		$sources[$source['source_key']] = $source;
		$this->project->writeJson('sources.json', $sources);

		return $source;
	}

	private function ensureFloating(string $version, array $selection): array
	{
		$sources = $this->project->readJson('sources.json', []);
		$source = $this->withSelectionDefaults($sources[$selection['source_key']] ?? [], $selection);
		$source['type'] = $source['type'] ?? 'composer';
		$source['package'] = $source['type'] === 'composer' ? 'phpbb/phpbb' : ($source['package'] ?? null);
		$source['url'] = $source['url'] ?? ($source['type'] === 'git' ? 'https://github.com/phpbb/phpbb.git' : null);

		if (!isset($sources[$selection['source_key']]))
		{
			echo "Resolving phpBB source: $version\n";
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

		echo "Fetching phpBB source: $version\n";
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
			if (!rename($tempSource['path'], $actualPath))
			{
				throw new RuntimeException("Unable to move source into place: $actualPath");
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

	private function removeUnusedFloatingSource(array &$sources, string $sourceKey, string $resolvedPath): bool
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

	private function recordForResolvedSource(array $source, array $selection, string $requested, string $actualVersion, string $actualKey, string $actualPath): array
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

	private function withSelectionDefaults(array $source, array $selection): array
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

			echo "Removing incomplete source path: $path\n";
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
			rmdir($path);
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

	private function normalizeGitSourceRoot(string $path): void
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
		if (!rename($appRoot, $temporaryAppRoot))
		{
			throw new RuntimeException("Unable to prepare Git source root: $appRoot");
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
			if (!rename($source, $target))
			{
				throw new RuntimeException("Unable to move Git source file into place: $source");
			}
		}

		$this->project->deleteTree($temporaryAppRoot);
	}

	private function hasFiles(string $path): bool
	{
		$files = scandir($path);
		return $files !== false && count(array_diff($files, ['.', '..'])) > 0;
	}

	private function installedPhpbbVersion(string $path): string
	{
		$phpbbCli = $path . '/install/phpbbcli.php';
		if (is_file($phpbbCli) && preg_match("/define\\('PHPBB_VERSION',\\s*'([^']+)'\\)/", (string) file_get_contents($phpbbCli), $matches))
		{
			return $matches[1];
		}

		throw new RuntimeException("Unable to determine phpBB version from source: $path");
	}

	private function withInstalledSourceMetadata(array $source, string $defaultPhp): array
	{
		$requirement = $this->phpRequirement($source['path'] ?? '');
		if ($requirement !== null)
		{
			$source['php_requirement'] = $requirement;
			$source['php'] = $this->runtimeForRequirement($defaultPhp, $requirement);
		}

		return $source;
	}

	private function phpRequirement(string $path): ?string
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

	private function runtimeForRequirement(string $defaultPhp, string $requirement): string
	{
		$minimum = $this->minimumPhpFromRequirement($requirement);
		if ($minimum === null || version_compare($defaultPhp, $minimum, '>='))
		{
			return $defaultPhp;
		}

		return $minimum;
	}

	private function minimumPhpFromRequirement(string $requirement): ?string
	{
		if (!preg_match_all('/(?<![0-9])(?:(>=|>|<=|<|!=|=|==|\\^|~)\\s*)?([0-9]+\\.[0-9]+)(?:\\.[0-9]+)?(?![0-9])/', $requirement, $matches, PREG_SET_ORDER))
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

	private function isFloatingSelection(array $selection): bool
	{
		return in_array($selection['constraint'], ['3.3.*', '3.2.*', 'dev-master'], true);
	}

	private function sourceInUse(string $sourceKey): bool
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

	private function sourceKey(string $value): string
	{
		return preg_replace('/[^A-Za-z0-9._-]/', '-', $value);
	}

	private function latestComposerVersion(string $constraint): ?string
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

	private function composerCommand(array $arguments): array
	{
		if ($this->isCommandAvailable('composer'))
		{
			return array_merge(['composer'], $arguments);
		}

		$phar = $this->project->rootPath('composer.phar');
		if (is_file($phar))
		{
			return array_merge([PHP_BINARY, $phar], $arguments);
		}

		throw new RuntimeException('Composer is not available. Install Composer or restore composer.phar in the QuickInstall project root.');
	}

	private function isCommandAvailable(string $command): bool
	{
		foreach (explode(PATH_SEPARATOR, (string) getenv('PATH')) as $path)
		{
			$candidate = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $command;
			if (is_file($candidate) && is_executable($candidate))
			{
				return true;
			}
		}

		return false;
	}

	private function run(array $command, string $cwd): void
	{
		echo '$ ' . implode(' ', array_map('escapeshellarg', $command)) . "\n";

		$descriptor = [
			0 => ['file', '/dev/null', 'r'],
			1 => defined('STDOUT') ? constant('STDOUT') : ['file', 'php://output', 'w'],
			2 => defined('STDERR') ? constant('STDERR') : ['file', 'php://stderr', 'w'],
		];

		$process = proc_open($command, $descriptor, $pipes, $cwd);
		if (!is_resource($process))
		{
			throw new RuntimeException('Unable to start command: ' . $command[0]);
		}

		$status = proc_close($process);
		if ($status !== 0)
		{
			throw new RuntimeException("Command failed with exit code $status: {$command[0]}" . $this->commandHint($command));
		}
	}

	private function capture(array $command, string $cwd): array
	{
		$descriptor = [
			0 => ['file', '/dev/null', 'r'],
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		];

		$process = proc_open($command, $descriptor, $pipes, $cwd);
		if (!is_resource($process))
		{
			return ['status' => 1, 'output' => ''];
		}

		$output = stream_get_contents($pipes[1]);
		stream_get_contents($pipes[2]);
		fclose($pipes[1]);
		fclose($pipes[2]);

		return [
			'status' => proc_close($process),
			'output' => (string) $output,
		];
	}

	private function commandHint(array $command): string
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
