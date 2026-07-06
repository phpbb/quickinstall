<?php

namespace QuickInstall\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\VersionMatrix;

class VersionMatrixTest extends TestCase
{
	public function testResolvesSupportedSelectors(): void
	{
		$matrix = new VersionMatrix();

		self::assertSame('3.3', $matrix->resolve('latest')['source_key']);
		self::assertSame('8.1', $matrix->resolve('3.3.x')['php']);
		self::assertSame('7.1', $matrix->resolve('3.2.11')['php']);
		self::assertSame('4.0', $matrix->resolve('master')['phpbb_branch']);
		self::assertSame('8.2', $matrix->resolve('4.0.0')['php']);
	}

	public function testRejectsUnsupportedSelectors(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('phpBB 3.1.12 is not supported');

		(new VersionMatrix())->resolve('3.1.12');
	}

	public function testGitSelectorUsesBranchAsSourceKey(): void
	{
		$selection = (new VersionMatrix())->resolve('feature/foo', true);

		self::assertSame('feature/foo', $selection['branch']);
		self::assertSame('feature-foo', $selection['source_key']);
		self::assertSame('experimental', $selection['status']);
	}
}
