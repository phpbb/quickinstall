<?php
/**
 *
 * QuickInstall CLI
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace QuickInstall\Sandbox;

use InvalidArgumentException;

/** Defines accepted, visible, and deprecated fixture presets. */
class SeedPresetCatalog
{
	/** @return string[] */
	public static function accepted(): array
	{
		return ['tiny', 'development', 'extension-dev', 'load-test', 'random'];
	}

	/** @return string[] */
	public static function visible(): array
	{
		return ['tiny', 'development', 'load-test', 'random'];
	}

	public static function supportsSqlitePopulate(string $preset): bool
	{
		return $preset === 'none' || self::supportsSqliteSeed($preset);
	}

	public static function supportsSqliteSeed(string $preset): bool
	{
		return in_array($preset, ['tiny', 'development'], true);
	}

	public static function validate(string $preset): void
	{
		if (!in_array($preset, self::accepted(), true))
		{
			throw new InvalidArgumentException('Preset must be one of: ' . implode(', ', self::visible()) . '.');
		}
	}

	public static function isDeprecated(string $preset): bool
	{
		return $preset === 'extension-dev';
	}

	public static function deprecationMessage(): string
	{
		return 'Preset extension-dev is deprecated and hidden from preset lists; use development for comprehensive development fixtures.';
	}
}
