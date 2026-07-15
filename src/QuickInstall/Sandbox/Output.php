<?php
/**
 *
 * QuickInstall sandbox output
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace QuickInstall\Sandbox;

/** Minimal output boundary shared by CLI, Dashboard, and subprocess services. */
interface Output
{
	public function write(string $message): void;

	public function error(string $message): void;
}
