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

/** Parses positional arguments, long options, and boolean flags. */
class CommandLine
{
	private array $args = [];
	private array $options = [];

	public static function parse(array $tokens): self
	{
		$cli = new self();
		$count = count($tokens);

		/** @noinspection ForeachInvariantsInspection */
		for ($i = 0; $i < $count; $i++)
		{
			$token = $tokens[$i];
			if (!str_starts_with($token, '--'))
			{
				$cli->args[] = $token;
				continue;
			}

			$option = substr($token, 2);
			$value = true;

			if (str_contains($option, '='))
			{
				[$option, $value] = explode('=', $option, 2);
			}
			else if (isset($tokens[$i + 1]) && !str_starts_with($tokens[$i + 1], '--'))
			{
				$value = $tokens[++$i];
			}

			$cli->options[$option] = $value;
		}

		return $cli;
	}

	public function argument(int $index): ?string
	{
		return $this->args[$index] ?? null;
	}

	public function option(string $name, ?string $default = null): ?string
	{
		return isset($this->options[$name]) && $this->options[$name] !== true ? (string) $this->options[$name] : $default;
	}

	public function has(string $name): bool
	{
		return isset($this->options[$name]);
	}
}
