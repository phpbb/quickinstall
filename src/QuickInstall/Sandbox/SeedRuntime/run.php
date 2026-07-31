<?php
/**
 *
 * QuickInstall development fixtures
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (PHP_SAPI !== 'cli')
{
	fwrite(STDERR, "Development fixtures may only run from the CLI.\n");
	exit(1);
}

$seed = (int) ($argv[1] ?? 1);
$action = $argv[2] ?? 'seed';
if ($seed < 1)
{
	fwrite(STDERR, "Seed must be a positive integer.\n");
	exit(1);
}

$phpbb_root_path = rtrim((string) getcwd(), '/') . '/';
$phpEx = 'php';
define('IN_PHPBB', true);

require_once $phpbb_root_path . 'common.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_user.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_content.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_posting.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_admin.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_acp.' . $phpEx;

$serverName = (string) ($config['server_name'] ?? '');
if (!in_array($serverName, ['localhost', '127.0.0.1', '::1'], true)
	&& !preg_match('/\.(test|local|dev|localhost)$/i', $serverName))
{
	fwrite(STDERR, "Development fixtures are restricted to local boards.\n");
	exit(1);
}

$user->session_begin();
$result = $db->sql_query_limit(
	'SELECT * FROM ' . USERS_TABLE . ' WHERE user_type = ' . USER_FOUNDER . ' ORDER BY user_id ASC',
	1
);
$founder = $db->sql_fetchrow($result);
$db->sql_freeresult($result);
if (!$founder)
{
	fwrite(STDERR, "Development fixtures require a founder account.\n");
	exit(1);
}
$user->data = array_merge($user->data, $founder);
$user->data['is_registered'] = true;
$auth->acl($user->data);

require_once __DIR__ . '/SeedContext.php';
require_once __DIR__ . '/UserBuilder.php';
require_once __DIR__ . '/ForumBuilder.php';
require_once __DIR__ . '/ContentBuilder.php';
require_once __DIR__ . '/StateBuilder.php';
require_once __DIR__ . '/DevelopmentSeeder.php';

try
{
	$context = new \QuickInstallSeed\SeedContext(
		$db,
		$user,
		$auth,
		$config,
		$phpbb_container,
		$phpbb_root_path,
		$phpEx,
		$seed
	);
	(new \QuickInstallSeed\DevelopmentSeeder($context))->run($action);
	exit(0);
}
catch (\Throwable $exception)
{
	fwrite(STDERR, 'Development seed failed: ' . $exception->getMessage() . "\n");
	exit(1);
}
