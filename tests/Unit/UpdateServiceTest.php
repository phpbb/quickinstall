<?php

namespace QuickInstall\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\Project;
use QuickInstall\Sandbox\UpdateService;
use QuickInstall\Tests\Support\TempProjectTrait;

class UpdateServiceTest extends TestCase
{
	use TempProjectTrait;

	public function testReturnsLatestStableUpdateAndCachesResult(): void
	{
		$root = $this->projectRoot('1.7.0');
		(new Project($root))->init();
		$endpoint = $this->versionEndpoint([
			'stable' => [
				['current' => '1.7.0', 'download' => 'https://example.com/current'],
				['current' => '1.8.0', 'download' => 'https://example.com/download'],
			],
			'unstable' => [
				['current' => '2.0.0-a1', 'download' => 'https://example.com/alpha'],
			],
		]);

		$update = (new UpdateService(new Project($root), $endpoint))->getUpdate();

		self::assertSame('1.8.0', $update['current']);
		self::assertSame('https://example.com/download', $update['download']);
		self::assertFileExists($root . '/.qi/update-check.json');
	}

	public function testFreshCacheAvoidsEndpointFetch(): void
	{
		$root = $this->projectRoot('1.7.0');
		$project = new Project($root);
		$project->init();
		$project->writeJson('update-check.json', [
			'checked_at' => time(),
			'current_version' => '1.7.0',
			'update' => ['current' => '1.8.0', 'download' => 'https://example.com/cached'],
			'error' => null,
		]);

		$update = (new UpdateService($project, $root . '/missing-version-file.json'))->getUpdate();

		self::assertSame('1.8.0', $update['current']);
		self::assertSame('https://example.com/cached', $update['download']);
	}

	public function testFailureIsCachedWithoutSurfacingUpdate(): void
	{
		$root = $this->projectRoot('1.7.0');
		$project = new Project($root);
		$project->init();

		$update = (new UpdateService($project, $root . '/missing-version-file.json'))->getUpdate();
		$cache = $project->readJson('update-check.json', []);

		self::assertNull($update);
		self::assertSame(null, $cache['update']);
		self::assertSame('VERSIONCHECK_FAIL', $cache['error']);
	}

	public function testMissingWorkspaceAbortsWithoutCreatingCache(): void
	{
		$root = $this->projectRoot('1.7.0');
		$endpoint = $this->versionEndpoint([
			'stable' => [
				['current' => '1.8.0', 'download' => 'https://example.com/download'],
			],
		]);

		$update = (new UpdateService(new Project($root), $endpoint))->getUpdate();

		self::assertNull($update);
		self::assertDirectoryDoesNotExist($root . '/.qi');
	}

	private function projectRoot(string $version): string
	{
		$root = $this->createTempProjectRoot();
		file_put_contents($root . '/composer.json', json_encode(['version' => $version]));
		return $root;
	}

	private function versionEndpoint(array $data): string
	{
		$path = $this->createTempProjectRoot() . '/version-check.json';
		file_put_contents($path, json_encode($data));
		return $path;
	}
}
