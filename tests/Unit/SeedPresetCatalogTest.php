<?php

namespace QuickInstall\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\SeedPresetCatalog;

class SeedPresetCatalogTest extends TestCase
{
	public function testDevelopmentIsVisible(): void
	{
		self::assertContains('development', SeedPresetCatalog::visible());
		self::assertNotContains('extension-dev', SeedPresetCatalog::visible());
	}

	public function testDeprecatedExtensionPresetRemainsAccepted(): void
	{
		SeedPresetCatalog::validate('extension-dev');

		self::assertContains('extension-dev', SeedPresetCatalog::accepted());
		self::assertTrue(SeedPresetCatalog::isDeprecated('extension-dev'));
		self::assertStringContainsString('use development', SeedPresetCatalog::deprecationMessage());
	}

	public function testSqliteSupportsOnlyLightweightPresets(): void
	{
		self::assertTrue(SeedPresetCatalog::supportsSqlitePopulate('none'));
		self::assertTrue(SeedPresetCatalog::supportsSqlitePopulate('tiny'));
		self::assertTrue(SeedPresetCatalog::supportsSqliteSeed('development'));
		self::assertFalse(SeedPresetCatalog::supportsSqliteSeed('none'));
		self::assertFalse(SeedPresetCatalog::supportsSqliteSeed('extension-dev'));
		self::assertFalse(SeedPresetCatalog::supportsSqliteSeed('load-test'));
		self::assertFalse(SeedPresetCatalog::supportsSqliteSeed('random'));
	}

	public function testUnknownPresetListsOnlyVisibleChoices(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('tiny, development, load-test, random');

		SeedPresetCatalog::validate('unknown');
	}
}
