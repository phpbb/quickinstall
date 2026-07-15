<?php

namespace QuickInstall\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\BrowserLauncher;

class BrowserLauncherTest extends TestCase
{
	/** @dataProvider commandProvider */
	public function testUsesPlatformBrowserCommand(string $osFamily, array $expected): void
	{
		$captured = null;
		$launcher = new BrowserLauncher($osFamily, static function (array $command) use (&$captured): bool {
			$captured = $command;
			return true;
		});

		self::assertTrue($launcher->open('http://127.0.0.1:8079/'));
		self::assertSame($expected, $captured);
	}

	public static function commandProvider(): array
	{
		$url = 'http://127.0.0.1:8079/';
		return [
			'macOS' => ['Darwin', ['open', $url]],
			'Windows' => ['Windows', ['cmd.exe', '/d', '/s', '/c', 'start', '', $url]],
			'Linux' => ['Linux', ['xdg-open', $url]],
		];
	}

	public function testReturnsFalseForUnsupportedPlatform(): void
	{
		$launcher = new BrowserLauncher('Unknown', static function (): bool {
			self::fail('Unsupported platforms must not execute a command.');
		});

		self::assertFalse($launcher->open('http://127.0.0.1:8079/'));
	}
}
