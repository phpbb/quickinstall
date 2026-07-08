<?php

namespace QuickInstall\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\CommandLine;

class CommandLineTest extends TestCase
{
	/**
	 * @dataProvider parseProvider
	 */
	public function testParsesArgumentsFlagsAndOptions(array $input, array $arguments, array $options, array $flags): void
	{
		$cli = CommandLine::parse($input);

		foreach ($arguments as $index => $value)
		{
			self::assertSame($value, $cli->argument($index));
		}
		foreach ($options as $name => $value)
		{
			self::assertSame($value, $cli->option($name));
		}
		foreach ($flags as $name)
		{
			self::assertTrue($cli->has($name));
			self::assertNull($cli->option($name));
		}
		self::assertSame('fallback', $cli->option('missing', 'fallback'));
	}

	public function parseProvider(): array
	{
		return [
			'arguments, flag, separate option, equals option' => [
				['board', 'customisations/acme/demo', '--copy', '--port', '8081', '--db=mysql'],
				['board', 'customisations/acme/demo'],
				['port' => '8081', 'db' => 'mysql'],
				['copy'],
			],
			'adjacent flags do not consume each other' => [
				['demo', '--debug', '--replace'],
				['demo'],
				[],
				['debug', 'replace'],
			],
			'option value may look like a path' => [
				['source:add', '--url', 'https://example.test/phpbb.git', '--allow-external'],
				['source:add'],
				['url' => 'https://example.test/phpbb.git'],
				['allow-external'],
			],
			'option without value at end is a flag' => [
				['board:create', 'demo', '--replace'],
				['board:create', 'demo'],
				[],
				['replace'],
			],
		];
	}
}
