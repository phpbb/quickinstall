<?php

namespace QuickInstall\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\SeedPresetCatalog;

class SeedPresetCatalogTest extends TestCase
{
	public function testDevelopmentIsVisibleAndDefault(): void
	{
		self::assertSame('development', SeedPresetCatalog::defaultSeed());
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

	public function testUnknownPresetListsOnlyVisibleChoices(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('tiny, development, load-test, random');

		SeedPresetCatalog::validate('unknown');
	}
}
