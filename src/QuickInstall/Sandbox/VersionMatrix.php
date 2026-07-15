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

/** Maps friendly phpBB selectors to source constraints and PHP runtimes. */
class VersionMatrix
{
	public function list(): array
	{
		return [
			['selector' => 'latest', 'resolves_to' => '3.3.*', 'status' => 'supported', 'php' => '8.1', 'notes' => 'Default supported stable line'],
			['selector' => '3.3 / 3.3.x', 'resolves_to' => '3.3.* or exact tag', 'status' => 'supported', 'php' => '8.1', 'notes' => 'Recommended; exact 3.3.0-3.3.4 use PHP 7.4'],
			['selector' => '3.2 / 3.2.x', 'resolves_to' => '3.2.* or exact tag', 'status' => 'supported', 'php' => '7.1', 'notes' => 'Legacy-modern'],
			['selector' => '4.0.x / master', 'resolves_to' => 'exact tag or dev-master', 'status' => 'experimental', 'php' => '8.2', 'notes' => 'Installer may change upstream'],
			['selector' => '3.0.x / 3.1.x', 'resolves_to' => '-', 'status' => 'unsupported', 'php' => '-', 'notes' => 'Use legacy web app'],
		];
	}

	public function resolve(string $requested, bool $git = false): array
	{
		$requested = trim($requested);
		if ($requested === '')
		{
			throw new InvalidArgumentException('Missing phpBB version');
		}

		if (preg_match('/^3\.[01](\.|$)/', $requested))
		{
			throw new InvalidArgumentException("phpBB $requested is not supported by QuickInstall CLI. Use phpBB 3.2+ or the legacy web app for phpBB 3.0/3.1.");
		}

		if ($git)
		{
			return [
				'version' => $requested,
				'source_key' => preg_replace('/[^A-Za-z0-9._-]/', '-', $requested),
				'constraint' => null,
				'branch' => $requested,
				'phpbb_branch' => preg_match('/^4\.|^(master|main)$/', $requested) ? '4.0' : 'custom',
				'php' => preg_match('/^4\.|^(master|main)$/', $requested) ? '8.2' : null,
				'status' => 'experimental',
			];
		}

		if ($requested === 'latest')
		{
			return [
				'version' => 'latest',
				'source_key' => '3.3',
				'constraint' => '3.3.*',
				'branch' => '3.3',
				'phpbb_branch' => '3.3',
				'php' => '8.1',
				'status' => 'supported',
			];
		}

		if ($requested === '3.3' || $requested === '3.3.x')
		{
			return [
				'version' => '3.3',
				'source_key' => '3.3',
				'constraint' => '3.3.*',
				'branch' => '3.3',
				'phpbb_branch' => '3.3',
				'php' => '8.1',
				'status' => 'supported',
			];
		}

		if ($requested === '3.2' || $requested === '3.2.x')
		{
			return [
				'version' => '3.2',
				'source_key' => '3.2',
				'constraint' => '3.2.*',
				'branch' => '3.2',
				'phpbb_branch' => '3.2',
				'php' => '7.1',
				'status' => 'supported',
			];
		}

		if ($requested === 'master' || $requested === 'main' || $requested === 'dev-master' || $requested === '4.0.x')
		{
			return [
				'version' => $requested,
				'source_key' => 'master',
				'constraint' => 'dev-master',
				'branch' => 'master',
				'phpbb_branch' => '4.0',
				'php' => '8.2',
				'status' => 'experimental',
			];
		}

		if (preg_match('/^3\.3\.\d+/', $requested))
		{
			return [
				'version' => $requested,
				'source_key' => $requested,
				'constraint' => $requested,
				'branch' => '3.3',
				'phpbb_branch' => '3.3',
				'php' => version_compare($requested, '3.3.5', '<') ? '7.4' : '8.1',
				'status' => 'supported',
			];
		}

		if (preg_match('/^3\.2\.\d+/', $requested))
		{
			return [
				'version' => $requested,
				'source_key' => $requested,
				'constraint' => $requested,
				'branch' => '3.2',
				'phpbb_branch' => '3.2',
				'php' => '7.1',
				'status' => 'supported',
			];
		}

		if (preg_match('/^4\.0\./', $requested))
		{
			return [
				'version' => $requested,
				'source_key' => $requested,
				'constraint' => $requested,
				'branch' => '4.0',
				'phpbb_branch' => '4.0',
				'php' => '8.2',
				'status' => 'experimental',
			];
		}

		throw new InvalidArgumentException("Unsupported phpBB selector: $requested. Use latest, 3.3, 3.3.x, 3.2, 3.2.x, 4.0.x, or master.");
	}
}
