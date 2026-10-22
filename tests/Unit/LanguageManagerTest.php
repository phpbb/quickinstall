<?php

namespace QuickInstall\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\LanguageManager;
use QuickInstall\Sandbox\Project;
use QuickInstall\Tests\Support\TempProjectTrait;

class LanguageManagerTest extends TestCase
{
	use TempProjectTrait;

	public function testMountsAndListsPhpbb3LanguageFromFolderIso(): void
	{
		[$project, $root] = $this->projectWithBoard();
		$source = $this->phpbb3Language($root, 'de', 'customisations/languages/de');

		$mounted = (new LanguageManager($project))->mount('demo', $source);

		self::assertSame('de', $mounted['name']);
		self::assertSame('bind', $mounted['mode']);
		self::assertSame('/var/www/html/language/de', $mounted['target']);
		self::assertSame($source, (new LanguageManager($project))->list('demo')[0]['source']);
	}

	public function testMountsPhpbb4LanguageFromComposerIso(): void
	{
		[$project, $root] = $this->projectWithBoard();
		$source = $this->phpbb4Language($root, 'pt_br', 'customisations/languages/portuguese');

		$mounted = (new LanguageManager($project))->mount('demo', $source);

		self::assertSame('pt_br', $mounted['name']);
		self::assertSame('/var/www/html/language/pt_br', $mounted['target']);
	}

	public function testCopyMountCopiesLanguageFilesIntoBoard(): void
	{
		[$project, $root] = $this->projectWithBoard();
		$source = $this->phpbb3Language($root, 'fr', 'customisations/languages/fr');
		file_put_contents($source . '/common.php', '<?php');

		$mounted = (new LanguageManager($project))->mount('demo', $source, true);

		self::assertSame('copy', $mounted['mode']);
		self::assertFileExists($project->boardPath('demo') . '/language/fr/common.php');
	}

	public function testUnmountRemovesCopiedLanguageAndMetadata(): void
	{
		[$project, $root] = $this->projectWithBoard();
		$source = $this->phpbb3Language($root, 'fr', 'customisations/languages/fr');
		$manager = new LanguageManager($project);
		$manager->mount('demo', $source, true);

		$removed = $manager->unmount('demo', 'fr');

		self::assertSame($project->boardPath('demo') . '/language/fr', $removed);
		self::assertDirectoryDoesNotExist($removed);
		self::assertSame([], $manager->list('demo'));
	}

	public function testUnmountBindLanguageRemovesMetadataOnly(): void
	{
		[$project, $root] = $this->projectWithBoard();
		$source = $this->phpbb3Language($root, 'de', 'customisations/languages/de');
		$manager = new LanguageManager($project);
		$manager->mount('demo', $source);

		$removed = $manager->unmount('demo', 'de');

		self::assertSame('/var/www/html/language/de', $removed);
		self::assertDirectoryExists($source);
		self::assertSame([], $manager->list('demo'));
	}

	public function testDiscoversNestedPhpbb3AndPhpbb4Languages(): void
	{
		[$project, $root] = $this->projectWithBoard();
		$de = $this->phpbb3Language($root, 'de', 'customisations/group/de');
		$fr = $this->phpbb4Language($root, 'fr', 'customisations/group/french');

		self::assertSame([$de, $fr], (new LanguageManager($project))->discover('group'));
	}

	public function testRejectsPhpbb3LanguageWhoseFolderIsNotAnIso(): void
	{
		[$project, $root] = $this->projectWithBoard();
		$source = $this->phpbb3Language($root, 'not a language', 'customisations/languages/not a language');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid language ISO');

		(new LanguageManager($project))->mount('demo', $source);
	}

	public function testRejectsPhpbb4ComposerWithoutLanguageIso(): void
	{
		[$project, $root] = $this->projectWithBoard();
		$source = $root . '/customisations/languages/missing';
		mkdir($source, 0775, true);
		file_put_contents($source . '/composer.json', '{}');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('must contain extra.language-iso');

		(new LanguageManager($project))->mount('demo', $source);
	}

	public function testRejectsInvalidPhpbb4ComposerJson(): void
	{
		[$project, $root] = $this->projectWithBoard();
		$source = $root . '/customisations/languages/invalid';
		mkdir($source, 0775, true);
		file_put_contents($source . '/composer.json', '{invalid');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('contains invalid JSON');

		(new LanguageManager($project))->mount('demo', $source);
	}

	public function testDiscoverySkipsInvalidPhpbb4ComposerJson(): void
	{
		[$project, $root] = $this->projectWithBoard();
		$valid = $this->phpbb4Language($root, 'fr', 'customisations/group/french');
		$invalid = $root . '/customisations/group/invalid';
		mkdir($invalid, 0775, true);
		file_put_contents($invalid . '/composer.json', '{invalid');

		self::assertSame([$valid], (new LanguageManager($project))->discover('group'));
	}

	public function testRejectsExternalLanguageUnlessAllowed(): void
	{
		[$project, $root] = $this->projectWithBoard();
		$source = $this->phpbb3Language($root, 'de', 'external/languages/de');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Language path must be under customisations/');

		(new LanguageManager($project))->mount('demo', $source);
	}

	private function projectWithBoard(): array
	{
		$root = $this->createTempProjectRoot();
		$project = new Project($root);
		$project->init();
		mkdir($project->boardPath('demo'), 0775, true);
		$project->appendBoard([
			'name' => 'demo',
			'phpbb' => '3.3.19',
			'phpbb_source' => '3.3.19',
			'php' => '8.2',
			'db' => 'mariadb',
			'port' => 8081,
			'url' => 'http://localhost:8081/',
			'extensions' => [],
			'styles' => [],
			'languages' => [],
		]);

		return [$project, $root];
	}

	private function phpbb3Language(string $root, string $iso, string $relativePath): string
	{
		$path = $root . '/' . $relativePath;
		mkdir($path, 0775, true);
		file_put_contents($path . '/iso.txt', "German\nDeutsch\nphpBB\n");

		return realpath($path);
	}

	private function phpbb4Language(string $root, string $iso, string $relativePath): string
	{
		$path = $root . '/' . $relativePath;
		mkdir($path, 0775, true);
		file_put_contents($path . '/composer.json', json_encode([
			'name' => 'phpbb/phpbb-language-' . $iso,
			'type' => 'phpbb-language',
			'extra' => ['language-iso' => $iso],
		], JSON_PRETTY_PRINT));

		return realpath($path);
	}
}
