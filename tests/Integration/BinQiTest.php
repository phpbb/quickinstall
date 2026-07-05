<?php

namespace QuickInstall\Tests\Integration;

use PHPUnit\Framework\TestCase;
use QuickInstall\Tests\Support\CliProcessTrait;

class BinQiTest extends TestCase
{
	use CliProcessTrait;

	public function testHelpSmokeTest(): void
	{
		$result = $this->runCli(['help']);

		self::assertSame(0, $result['exit_code']);
		self::assertStringContainsString('QuickInstall CLI', $result['stdout']);
		self::assertSame('', $result['stderr']);
	}

	public function testInvalidBoardCreateSmokeTestDoesNotRequireWorkspaceMutation(): void
	{
		$result = $this->runCli(['board:create', 'demo', '--port', '70000']);

		self::assertSame(1, $result['exit_code']);
		self::assertSame('', $result['stdout']);
		self::assertStringContainsString('--port must be between 1 and 65535.', $result['stderr']);
	}
}
