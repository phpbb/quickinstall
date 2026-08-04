<?php
/**
 *
 * QuickInstall seed runtime
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 * @noinspection PhpDefineCanBeReplacedWithConstInspection
 */

use QuickInstallSeed\SeedContext;
use QuickInstallSeed\Seeder;

if (PHP_SAPI !== 'cli')
{
	fwrite(STDERR, "QuickInstall seed runtime may only run from the CLI.\n");
	exit(1);
}

if (getenv('QUICKINSTALL_SEED_RUNTIME') !== '1')
{
	fwrite(STDERR, "QuickInstall seed runtime must be launched by QuickInstall.\n");
	exit(1);
}

if (!isset($argv[1], $argv[2], $argv[3]))
{
	fwrite(STDERR, "Invalid QuickInstall seed runtime invocation: required arguments are missing.\n");
	exit(1);
}

$preset = (string) $argv[1];
$seed = (int) $argv[2];
$action = (string) $argv[3];
if (!in_array($preset, ['tiny', 'development', 'extension-dev', 'load-test', 'random'], true))
{
	fwrite(STDERR, "Unknown seed preset: $preset\n");
	exit(1);
}
if ($seed < 1)
{
	fwrite(STDERR, "Seed must be a positive integer.\n");
	exit(1);
}
if (!in_array($action, ['seed', 'reset', 'replace'], true))
{
	fwrite(STDERR, "Unknown seed action: $action\n");
	exit(1);
}

$phpbb_root_path = rtrim((string) getcwd(), '/') . '/';
$phpEx = 'php';
define('IN_PHPBB', true);

require_once $phpbb_root_path . 'common.' . $phpEx;

/** @var \phpbb\user $user */
/** @var \phpbb\auth\auth $auth */
/** @var \phpbb\config\config $config */
/** @var \phpbb\db\driver\driver_interface $db */
/** @var \Symfony\Component\DependencyInjection\ContainerInterface $phpbb_container */

require_once $phpbb_root_path . 'includes/functions_user.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_content.' . $phpEx;
require_once $phpbb_root_path . 'includes/message_parser.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_posting.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_admin.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_acp.' . $phpEx;

$user->session_begin();
$result = $db->sql_query_limit(
	'SELECT * FROM ' . USERS_TABLE . ' WHERE user_type = ' . USER_FOUNDER . ' ORDER BY user_id ASC',
	1
);
$founder = $db->sql_fetchrow($result);
$db->sql_freeresult($result);
if (!$founder)
{
	fwrite(STDERR, "Seed runtime requires a founder account.\n");
	exit(1);
}
$user->data = array_merge($user->data, $founder);
$user->data['is_registered'] = true;
$auth->acl($user->data);

require_once __DIR__ . '/SeedContext.php';
require_once __DIR__ . '/SeedPlan.php';
require_once __DIR__ . '/UserBuilder.php';
require_once __DIR__ . '/ForumBuilder.php';
require_once __DIR__ . '/ContentBuilder.php';
require_once __DIR__ . '/StateBuilder.php';
require_once __DIR__ . '/DevelopmentSeeder.php';
require_once __DIR__ . '/VolumeSeeder.php';
require_once __DIR__ . '/Seeder.php';

try
{
	$context = new SeedContext(
		$db,
		$user,
		$auth,
		$config,
		$phpbb_container,
		$phpbb_root_path,
		$phpEx,
		$preset,
		$seed
	);
	(new Seeder($context))->run($action);
	exit(0);
}
catch (Throwable $exception)
{
	fwrite(STDERR, 'Seed failed: ' . $exception->getMessage() . "\n");
	exit(1);
}
