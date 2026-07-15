<?php
/**
 *
 * QuickInstall sandbox buffered output
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace QuickInstall\Sandbox;

/** Captures subprocess output for Dashboard responses and tests. */
class BufferedOutput implements Output
{
	private string $output = '';
	private string $errorOutput = '';

	public function write(string $message): void
	{
		$this->output .= $message;
	}

	public function error(string $message): void
	{
		$this->errorOutput .= $message;
	}

	public function output(): string
	{
		return $this->output;
	}

	public function errorOutput(): string
	{
		return $this->errorOutput;
	}

	public function all(): string
	{
		return $this->output . $this->errorOutput;
	}
}
