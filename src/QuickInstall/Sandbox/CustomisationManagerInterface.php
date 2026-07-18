<?php
/**
 *
 * QuickInstall sandbox customisation manager contract
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace QuickInstall\Sandbox;

/** Common operations supported by extension and style managers. */
interface CustomisationManagerInterface
{
	public function mount(string $board, string $source, bool $copy = false, bool $allowExternal = false): array;

	public function discover(string $source, bool $allowExternal = false): array;

	public function unmount(string $board, string $name): string;

	public function cleanupStaleTarget(string $board, string $name): void;
}
