<?php
/**
 *
 * QuickInstall browser launcher
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace QuickInstall\Sandbox;

/** Opens a URL in the operating system's default browser. */
class BrowserLauncher
{
	private string $osFamily;
	private $executor;

	public function __construct(?string $osFamily = null, ?callable $executor = null)
	{
		$this->osFamily = $osFamily ?: PHP_OS_FAMILY;
		$this->executor = $executor;
	}

	public function open(string $url): bool
	{
		$command = $this->command($url);
		if ($command === null)
		{
			return false;
		}

		if ($this->executor !== null)
		{
			return (bool) call_user_func($this->executor, $command);
		}

		$nullDevice = $this->osFamily === 'Windows' ? 'NUL' : '/dev/null';
		$descriptor = [
			0 => ['file', $nullDevice, 'r'],
			1 => ['file', $nullDevice, 'w'],
			2 => ['file', $nullDevice, 'w'],
		];
		$process = @proc_open($command, $descriptor, $pipes);
		if (!is_resource($process))
		{
			return false;
		}

		return proc_close($process) === 0;
	}

	private function command(string $url): ?array
	{
		switch ($this->osFamily)
		{
			case 'Darwin':
				return ['open', $url];

			case 'Windows':
				return ['cmd.exe', '/d', '/s', '/c', 'start', '', $url];

			case 'Linux':
			case 'BSD':
				return ['xdg-open', $url];
		}

		return null;
	}
}
