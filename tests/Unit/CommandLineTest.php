<?php

namespace QuickInstall\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\CommandLine;

class CommandLineTest extends TestCase
{
	public function testParsesArgumentsFlagsAndOptions(): void
	{
		$cli = CommandLine::parse([
			'board',
			'customisations/acme/demo',
			'--copy',
			'--port',
			'8081',
			'--db=mysql',
		]);

		self::assertSame('board', $cli->argument(0));
		self::assertSame('customisations/acme/demo', $cli->argument(1));
		self::assertTrue($cli->has('copy'));
		self::assertSame('8081', $cli->option('port'));
		self::assertSame('mysql', $cli->option('db'));
		self::assertSame('fallback', $cli->option('missing', 'fallback'));
	}

	public function testFlagDoesNotConsumeFollowingOption(): void
	{
		$cli = CommandLine::parse(['demo', '--debug', '--replace']);

		self::assertSame('demo', $cli->argument(0));
		self::assertTrue($cli->has('debug'));
		self::assertTrue($cli->has('replace'));
		self::assertNull($cli->option('debug'));
	}
}
