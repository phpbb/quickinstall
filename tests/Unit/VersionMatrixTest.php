<?php

namespace QuickInstall\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use QuickInstall\Sandbox\VersionMatrix;

class VersionMatrixTest extends TestCase
{
	public function testListReportsPhp82ForSupportedStableLine(): void
	{
		$versions = (new VersionMatrix())->list();

		self::assertSame('8.2', $versions[0]['php']);
		self::assertSame('8.2', $versions[1]['php']);
		self::assertStringContainsString('3.3.0-3.3.5 use PHP 7.4', $versions[1]['notes']);
	}

	public function testPhpbb33SelectorsDoNotUsePhp81(): void
	{
		$matrix = new VersionMatrix();
		$selectors = ['latest', '3.3', '3.3.x'];
		for ($patch = 0; $patch <= 19; $patch++)
		{
			$selectors[] = "3.3.$patch";
		}

		foreach ($selectors as $selector)
		{
			self::assertNotSame('8.1', $matrix->resolve($selector)['php']);
		}
	}

	/**
	 * @dataProvider supportedSelectorProvider
	 */
	public function testResolvesSupportedSelectors(string $selector, array $expected): void
	{
		$selection = (new VersionMatrix())->resolve($selector);

		foreach ($expected as $key => $value)
		{
			self::assertSame($value, $selection[$key]);
		}
	}

	/**
	 * @dataProvider unsupportedSelectorProvider
	 */
	public function testRejectsUnsupportedSelectors(string $selector, string $message): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage($message);

		(new VersionMatrix())->resolve($selector);
	}

	/**
	 * @dataProvider gitSelectorProvider
	 */
	public function testGitSelectorUsesBranchAsSourceKey(string $selector, string $sourceKey, string $branch, string $phpbbBranch, ?string $php): void
	{
		$selection = (new VersionMatrix())->resolve($selector, true);

		self::assertSame($branch, $selection['branch']);
		self::assertSame($sourceKey, $selection['source_key']);
		self::assertSame($phpbbBranch, $selection['phpbb_branch']);
		self::assertSame($php, $selection['php']);
		self::assertSame('experimental', $selection['status']);
	}

	public function supportedSelectorProvider(): array
	{
		return [
			'latest' => ['latest', ['source_key' => '3.3', 'constraint' => '3.3.*', 'php' => '8.2']],
			'3.3 shorthand' => ['3.3', ['source_key' => '3.3', 'constraint' => '3.3.*', 'phpbb_branch' => '3.3']],
			'3.3.x shorthand' => ['3.3.x', ['source_key' => '3.3', 'constraint' => '3.3.*', 'php' => '8.2']],
			'exact 3.3.0 release' => ['3.3.0', ['source_key' => '3.3.0', 'constraint' => '3.3.0', 'php' => '7.4']],
			'exact 3.3.3 release' => ['3.3.3', ['source_key' => '3.3.3', 'constraint' => '3.3.3', 'php' => '7.4']],
			'exact 3.3.5 release' => ['3.3.5', ['source_key' => '3.3.5', 'constraint' => '3.3.5', 'php' => '7.4']],
			'exact 3.3.6 release' => ['3.3.6', ['source_key' => '3.3.6', 'constraint' => '3.3.6', 'php' => '8.2']],
			'exact 3.3.9 release' => ['3.3.9', ['source_key' => '3.3.9', 'constraint' => '3.3.9', 'php' => '8.2']],
			'exact 3.3.10 release' => ['3.3.10', ['source_key' => '3.3.10', 'constraint' => '3.3.10', 'php' => '8.2']],
			'exact 3.3 release' => ['3.3.14', ['source_key' => '3.3.14', 'constraint' => '3.3.14', 'php' => '8.2']],
			'3.2 shorthand' => ['3.2', ['source_key' => '3.2', 'constraint' => '3.2.*', 'php' => '7.1']],
			'exact 3.2 release' => ['3.2.11', ['source_key' => '3.2.11', 'constraint' => '3.2.11', 'php' => '7.1']],
			'master' => ['master', ['source_key' => 'master', 'constraint' => 'dev-master', 'phpbb_branch' => '4.0']],
			'main alias' => ['main', ['source_key' => 'master', 'constraint' => 'dev-master', 'phpbb_branch' => '4.0']],
			'4.0.x alias' => ['4.0.x', ['source_key' => 'master', 'constraint' => 'dev-master', 'php' => '8.2']],
			'exact 4.0 release' => ['4.0.0', ['source_key' => '4.0.0', 'constraint' => '4.0.0', 'php' => '8.2']],
		];
	}

	public function unsupportedSelectorProvider(): array
	{
		return [
			'empty' => ['', 'Missing phpBB version'],
			'legacy 3.0' => ['3.0.14', 'phpBB 3.0.14 is not supported'],
			'legacy 3.1' => ['3.1.12', 'phpBB 3.1.12 is not supported'],
			'unknown' => ['2.0.23', 'Unsupported phpBB selector: 2.0.23'],
			'invalid text' => ['banana', 'Unsupported phpBB selector: banana'],
		];
	}

	public function gitSelectorProvider(): array
	{
		return [
			'feature branch' => ['feature/foo', 'feature-foo', 'feature/foo', 'custom', null],
			'master' => ['master', 'master', 'master', '4.0', '8.2'],
			'main' => ['main', 'main', 'main', '4.0', '8.2'],
			'exact 4.0 git tag' => ['4.0.0', '4.0.0', '4.0.0', '4.0', '8.2'],
		];
	}
}
