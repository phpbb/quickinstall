<?php

namespace QuickInstall\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\Project;
use QuickInstall\Sandbox\SeederWriter;
use QuickInstall\Tests\Support\TempProjectTrait;

require_once dirname(__DIR__, 2) . '/src/QuickInstall/Sandbox/SeedRuntime/SeedContext.php';

class SeederPackageTest extends TestCase
{
	use TempProjectTrait;

	public function testWritesUnifiedSeederPackage(): void
	{
		$project = new Project($this->createTempProjectRoot());
		$project->init();
		mkdir($project->runtimePath('demo'), 0775, true);

		$path = (new SeederWriter($project))->write('demo');

		self::assertSame($project->runtimePath('demo') . '/seed-runtime', $path);
		foreach ([
			'run.php',
			'Seeder.php',
			'SeedContext.php',
			'SeedPlan.php',
			'VolumeSeeder.php',
			'UserBuilder.php',
			'ForumBuilder.php',
			'ContentBuilder.php',
			'StateBuilder.php',
			'DevelopmentSeeder.php',
		] as $file)
		{
			self::assertFileExists($path . '/' . $file);
		}
		$run = file_get_contents($path . '/run.php');
		self::assertStringContainsString("getenv('QUICKINSTALL_SEED_RUNTIME') !== '1'", $run);
		self::assertStringContainsString('!isset($argv[1], $argv[2], $argv[3])', $run);
		self::assertStringContainsString("'includes/message_parser.'", $run);
		self::assertFileDoesNotExist($path . '/StandardSeeder.php');
		self::assertStringContainsString("'25 users'", file_get_contents($path . '/DevelopmentSeeder.php'));
		self::assertStringContainsString("'90 posts'", file_get_contents($path . '/DevelopmentSeeder.php'));
		self::assertStringContainsString("'tiny' =>", file_get_contents($path . '/VolumeSeeder.php'));
		self::assertStringContainsString("'extension-dev' =>", file_get_contents($path . '/VolumeSeeder.php'));
		self::assertStringContainsString("'load-test' =>", file_get_contents($path . '/VolumeSeeder.php'));
		self::assertStringContainsString("'random' =>", file_get_contents($path . '/VolumeSeeder.php'));
		self::assertStringContainsString('one manifest, reset, and finalization lifecycle', file_get_contents($path . '/Seeder.php'));
		self::assertStringContainsString('AssetFactory::png(80, 80', file_get_contents($path . '/UserBuilder.php'));
		self::assertStringContainsString("public const PASSWORD = 'password';", file_get_contents($path . '/SeedContext.php'));
		self::assertStringContainsString('Password: password', file_get_contents($path . '/ForumBuilder.php'));
		self::assertStringContainsString('Read forum', file_get_contents($path . '/ForumBuilder.php'));
		self::assertStringContainsString('This forum is marked as read', file_get_contents($path . '/ForumBuilder.php'));
		self::assertStringContainsString('Announcement topic', file_get_contents($path . '/ContentBuilder.php'));
		self::assertStringContainsString('Pagination topic', file_get_contents($path . '/ContentBuilder.php'));
		self::assertStringContainsString("'LOG_LOCK'", file_get_contents($path . '/ContentBuilder.php'));
		self::assertStringContainsString("'LOG_MOVE'", file_get_contents($path . '/ContentBuilder.php'));
		self::assertStringContainsString("addId('logs', \$logId)", file_get_contents($path . '/ContentBuilder.php'));
		self::assertStringContainsString("ids('logs')", file_get_contents($path . '/Seeder.php'));
		self::assertStringContainsString('[size=50]Smaller text[/size]', file_get_contents($path . '/ContentBuilder.php'));
		self::assertStringContainsString('[list=a]', file_get_contents($path . '/ContentBuilder.php'));
		self::assertStringContainsString('new \\parse_message($message)', file_get_contents($path . '/SeedContext.php'));
		self::assertStringContainsString('$this->parseMessage($message)', file_get_contents($path . '/ContentBuilder.php'));
		self::assertStringContainsString('$this->parseMessage($message)', file_get_contents($path . '/StateBuilder.php'));
		self::assertStringContainsString("'bbcode_uid' => \$parser->bbcode_uid", file_get_contents($path . '/ContentBuilder.php'));
		self::assertStringContainsString("'bbcode_uid' => \$parser->bbcode_uid", file_get_contents($path . '/StateBuilder.php'));
		self::assertStringContainsString('reply with three quote levels', file_get_contents($path . '/ContentBuilder.php'));
		self::assertStringContainsString('SupercalifragilisticexpialidociousSupercalifragilisticexpialidocious', file_get_contents($path . '/ContentBuilder.php'));
		self::assertStringContainsString('Unread private message', file_get_contents($path . '/StateBuilder.php'));
		self::assertStringContainsString("sprintf('[QI %d] '", file_get_contents($path . '/SeedContext.php'));
		self::assertStringContainsString("defined('BANS_TABLE')", file_get_contents($path . '/SeedContext.php'));
		self::assertStringContainsString("'storage_files' => []", file_get_contents($path . '/SeedContext.php'));
		self::assertStringContainsString("has('storage.attachment')", file_get_contents($path . '/SeedContext.php'));
		self::assertStringContainsString("has('search.backend_factory')", file_get_contents($path . '/StateBuilder.php'));
	}

	public function testGeneratedPngIsDeterministicAndValid(): void
	{
		$first = \QuickInstallSeed\AssetFactory::png(80, 80, 42);
		$second = \QuickInstallSeed\AssetFactory::png(80, 80, 42);
		$different = \QuickInstallSeed\AssetFactory::png(80, 80, 43);

		self::assertSame($first, $second);
		self::assertNotSame($first, $different);
		self::assertSame("\x89PNG\r\n\x1a\n", substr($first, 0, 8));
		self::assertGreaterThan(100, strlen($first));
	}

	public function testBuilderUsesAvailableLastInsertedIdMethod(): void
	{
		$modernDb = new class {
			public function sql_last_inserted_id(): int
			{
				return 42;
			}

			public function sql_nextid(): int
			{
				throw new \RuntimeException('Deprecated method should not be called.');
			}
		};
		$legacyDb = new class {
			public function sql_nextid(): int
			{
				return 24;
			}
		};

		self::assertSame(42, $this->lastInsertedId($modernDb));
		self::assertSame(24, $this->lastInsertedId($legacyDb));
	}

	public function testSeedContextUsesPresetSpecificIdentityAndManifest(): void
	{
		$container = new class {
			public function has(string $name): bool
			{
				return false;
			}
		};
		$user = (object) ['data' => []];
		$root = $this->createTempProjectRoot() . '/';
		$normalizedRoot = str_replace('\\', '/', $root);

		$development = new \QuickInstallSeed\SeedContext(null, $user, null, [], $container, $root, 'php', 'development', 4);
		$tiny = new \QuickInstallSeed\SeedContext(null, $user, null, [], $container, $root, 'php', 'tiny', 4);

		self::assertSame('[QI 4] ', $development->prefix);
		self::assertSame('[QI tiny 4] ', $tiny->prefix);
		self::assertSame($normalizedRoot . 'store/qi-development-seed-4.json', $development->manifestPath());
		self::assertSame($normalizedRoot . 'store/qi-tiny-seed-4.json', $tiny->manifestPath());
		self::assertSame('tiny', $tiny->manifest['preset']);
		self::assertSame(3, $tiny->manifest['version']);
	}

	public function testGeneratedZipContainsOnlyLocalFixtureFiles(): void
	{
		if (!class_exists('ZipArchive'))
		{
			self::markTestSkipped('ZipArchive is unavailable.');
		}

		$content = \QuickInstallSeed\AssetFactory::zip(7);
		$path = tempnam(sys_get_temp_dir(), 'qi-seed-test-');
		file_put_contents($path, $content);
		$zip = new \ZipArchive();
		self::assertTrue($zip->open($path));
		self::assertSame("QuickInstall sample archive\n\nThis ZIP file is attached by the development seed.\nSeed: 7\n", $zip->getFromName('README.txt'));
		self::assertNotFalse($zip->getFromName('sample/example-data.txt'));
		self::assertNotFalse($zip->getFromName('unicode/café.txt'));
		$zip->close();
		unlink($path);
	}

	public function testGeneratedFilesUsePhpBbStorageWhenAvailable(): void
	{
		$root = $this->createTempProjectRoot();
		$directory = $root . '/files';
		mkdir($directory, 0775, true);
		$path = $directory . '/fixture.png';
		$storage = new class($path) {
			private string $path;
			private array $tracked = [];

			public function __construct(string $path)
			{
				$this->path = $path;
			}

			public function exists(string $path): bool
			{
				return isset($this->tracked[$path]);
			}

			public function write(string $path, $stream): void
			{
				file_put_contents($this->path, stream_get_contents($stream));
				$this->tracked[$path] = true;
			}

			public function delete(string $path): void
			{
				unlink($this->path);
				unset($this->tracked[$path]);
			}
		};
		$container = new class($storage) {
			private $storage;

			public function __construct($storage)
			{
				$this->storage = $storage;
			}

			public function has(string $name): bool
			{
				return $name === 'storage.attachment';
			}

			public function get(string $name)
			{
				return $this->storage;
			}
		};
		$user = (object) ['data' => []];
		$windowsRoot = str_replace('/', '\\', $root);
		$context = new \QuickInstallSeed\SeedContext(null, $user, null, [], $container, $windowsRoot, 'php', 'development', 1);

		$context->writeFile('attachment', 'fixture.png', $path, 'fixture data');
		$context->addFile(str_replace('/', '\\', $path));

		self::assertSame(rtrim(str_replace('\\', '/', $root), '/') . '/', $context->root);
		self::assertSame([str_replace('\\', '/', $path)], $context->manifest['files']);
		self::assertSame('fixture data', file_get_contents($path));
		self::assertSame([
			[
				'storage' => 'attachment',
				'path' => 'fixture.png',
			],
		], $context->manifest['storage_files']);
		self::assertSame(1, $context->storageFileCount());
		self::assertTrue($context->storageFilesExist());

		$context->deleteRegisteredFiles();

		self::assertFileDoesNotExist($path);
		self::assertFalse($storage->exists('fixture.png'));
	}

	private function lastInsertedId($db): int
	{
		$context = new \QuickInstallSeed\SeedContext(null, (object) ['data' => []], null, [], null, '/', 'php', 'tiny', 1);
		$context->db = $db;
		$builder = new class($context) extends \QuickInstallSeed\BaseBuilder {
			public function getLastInsertedId(): int
			{
				return $this->lastInsertedId();
			}
		};

		return $builder->getLastInsertedId();
	}
}
