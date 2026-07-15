<?php
/**
 *
 * QuickInstall sandbox stream output
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace QuickInstall\Sandbox;

/** Writes service and subprocess messages to separate output streams. */
class StreamOutput implements Output
{
	private $stdout;
	private $stderr;

	public function __construct($stdout = null, $stderr = null)
	{
		$this->stdout = $stdout ?: (defined('STDOUT') ? STDOUT : fopen('php://output', 'wb'));
		$this->stderr = $stderr ?: (defined('STDERR') ? STDERR : fopen('php://stderr', 'wb'));
	}

	public function write(string $message): void
	{
		fwrite($this->stdout, $message);
	}

	public function error(string $message): void
	{
		fwrite($this->stderr, $message);
	}

	public function stdout()
	{
		return $this->stdout;
	}

	public function stderr()
	{
		return $this->stderr;
	}
}
