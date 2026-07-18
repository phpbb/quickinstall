<?php
/**
 *
 * QuickInstall sandbox style uninstaller writer
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace QuickInstall\Sandbox;

use RuntimeException;

/** Writes a phpBB-bootstrapped script that safely removes an installed style. */
class StyleUninstallerWriter
{
	private Project $project;

	public function __construct(Project $project)
	{
		$this->project = $project;
	}

	public function write(string $board): string
	{
		$path = $this->project->runtimePath($board) . '/style-uninstall.php';
		$directory = dirname($path);
		if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory))
		{
			throw new RuntimeException("Unable to create runtime directory: $directory");
		}

		if (file_put_contents($path, $this->script(), LOCK_EX) === false)
		{
			throw new RuntimeException("Unable to write style uninstaller: $path");
		}

		return $path;
	}

	private function script(): string
	{
		return <<<'PHP'
<?php
define('IN_PHPBB', true);
$phpbb_root_path = '/var/www/html/';
$phpEx = 'php';
require $phpbb_root_path . 'common.' . $phpEx;

$path = $argv[1] ?? '';
if ($path === '' || !preg_match('/^[A-Za-z0-9_-]+$/', $path))
{
	fwrite(STDERR, "Invalid style path.\n");
	exit(1);
}

$sql = 'SELECT * FROM ' . STYLES_TABLE . " WHERE style_path = '" . $db->sql_escape($path) . "'";
$result = $db->sql_query($sql);
$style = $db->sql_fetchrow($result);
$db->sql_freeresult($result);
if (!$style)
{
	exit(0);
}

$id = (int) $style['style_id'];
$default = (int) $config['default_style'];
if ($id === $default || strtolower($path) === 'prosilver')
{
	fwrite(STDERR, "Cannot uninstall the default or prosilver style.\n");
	exit(1);
}

$sql = 'SELECT style_id FROM ' . STYLES_TABLE . ' WHERE style_parent_id = ' . $id
	. " OR style_parent_tree = '" . $db->sql_escape($path) . "'";
$result = $db->sql_query($sql);
$child = $db->sql_fetchrow($result);
$db->sql_freeresult($result);
if ($child)
{
	fwrite(STDERR, "Cannot uninstall a style with installed child styles.\n");
	exit(1);
}

$db->sql_transaction('begin');
$db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_style = ' . $default . ' WHERE user_style = ' . $id);
$db->sql_query('DELETE FROM ' . STYLES_TABLE . ' WHERE style_id = ' . $id);
$db->sql_transaction('commit');
$cache->purge();
PHP;
	}
}
