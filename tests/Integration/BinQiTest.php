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

	public function testHelpIncludesDoctorCommand(): void
	{
		$result = $this->runCli(['help', 'doctor']);

		self::assertSame(0, $result['exit_code']);
		self::assertStringContainsString('Checks that required host tools are available', $result['stdout']);
	}

	public function testInvalidBoardCreateSmokeTestDoesNotRequireWorkspaceMutation(): void
	{
		$result = $this->runCli(['board:create', 'demo', '--port', '70000']);

		self::assertSame(1, $result['exit_code']);
		self::assertSame('', $result['stdout']);
		self::assertStringContainsString('--port must be between 1 and 65535.', $result['stderr']);
	}

	public function testUiStatusSmokeTestLoadsUiServerService(): void
	{
		$result = $this->runCli(['ui:status']);

		self::assertContains($result['exit_code'], [0, 1]);
		self::assertStringContainsString('QuickInstall sandbox UI', $result['stdout']);
		self::assertSame('', $result['stderr']);
	}

	public function testExtMountSmokeTestLoadsCustomisationMountService(): void
	{
		$result = $this->runCli(['ext:mount', 'missing-board', 'customisations/missing-extension']);

		self::assertSame(1, $result['exit_code']);
		self::assertSame('', $result['stdout']);
		self::assertStringContainsString('Unknown board: missing-board', $result['stderr']);
	}
}
