<?php
/**
*
* @package quickinstall
* @copyright (c) 2007 phpBB Limited
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
define('IN_QUICKINSTALL', true);
define('IN_INSTALL', true);

$quickinstall_path = './';
$phpbb_root_path = $quickinstall_path . 'sources/phpBB3/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

// Report all errors, except notices
$level = E_ALL ^ E_NOTICE;

// Include scripts for quickinstall
require("{$quickinstall_path}includes/qi_constants.$phpEx");
require("{$quickinstall_path}includes/qi.$phpEx");
require("{$quickinstall_path}includes/settings.$phpEx");
require("{$quickinstall_path}includes/qi_functions.$phpEx");
require("{$quickinstall_path}includes/qi_file.$phpEx");
require("{$quickinstall_path}includes/qi_module.$phpEx");
require("{$quickinstall_path}includes/twig.$phpEx");
require("{$quickinstall_path}vendor/autoload.$phpEx");

if (PHP_VERSION_ID >= 50400)
{
	// PHP 5.4 adds E_STRICT to E_ALL.
	// Our utf8 normalizer triggers E_STRICT output on PHP 5.4.
	// Unfortunately it cannot be made E_STRICT-clean while
	// continuing to work on PHP 4.
	// Therefore, in phpBB 3.0.x we disable E_STRICT on PHP 5.4+,
	// while phpBB 3.1 will fix utf8 normalizer.
	// E_STRICT is defined starting with PHP 5
	if (!defined('E_STRICT'))
	{
		define('E_STRICT', 2048);
	}
	$level &= ~E_STRICT;
}

if (PHP_VERSION_ID >= 50500)
{
	// The /e modifier is deprecated as of PHP 5.5.0 according to php.net.
	// it is used in phpBB 3.0.x file: includes\functions_content.php
	// That is needed to work on PHP 4.x
	// It will be fixed in phpBB 3.1
	if (!defined('E_DEPRECATED'))
	{
		define('E_DEPRECATED', 8192);
	}
	$level &= ~E_DEPRECATED;
}
error_reporting($level);

// If we are on PHP5 we may need to define STRIP to strip slashes
define('STRIP', PHP_VERSION_ID < 60000 && get_magic_quotes_gpc());

// Try to override some limits - maybe it helps some...
@set_time_limit(0);
@ini_set('memory_limit', '128M');

// Set PHP error handler to ours
set_error_handler(array('qi', 'msg_handler'), E_ALL);

// Check PHP version
if (PHP_VERSION_ID < 50407)
{
	trigger_error('ERROR_PHP_UNSUPPORTED');
}

// Make sure we have phpBB.
if (!file_exists($quickinstall_path . 'sources/phpBB3/common.' . $phpEx))
{
	trigger_error('ERROR_PHPBB_NOT_FOUND');
}

// Let's get the config.
$page		= qi_request_var('page', 'main');
$mode		= qi_request_var('mode', '');
$profile	= qi_request_var('qi_profile', '');

$settings = new settings($quickinstall_path);

// delete settings profile if requested
if (qi_request_var('delete-profile', false) !== false)
{
	$settings->delete_profile($profile);
	$profile = '';
}

// delete all phpbb cookies if requested
if (qi_request_var('delete-cookies', '') === qi::PHPBB_COOKIE_PREFIX)
{
	qi::delete_cookies(qi::PHPBB_COOKIE_PREFIX);
}

// load settings profile
$settings->import_profile($profile);

// We need some phpBB functions too.
$alt_env = $settings->get_config('alt_env', '');
$alt_env_missing = false;
if ($alt_env !== '')
{
	if (file_exists("{$quickinstall_path}sources/phpBB3_alt/$alt_env/") && is_dir("{$quickinstall_path}sources/phpBB3_alt/$alt_env/"))
	{
		$phpbb_root_path = "{$quickinstall_path}sources/phpBB3_alt/$alt_env/";
	}
	else
	{
		$alt_env_missing = true;
	}
}

$phpbb_version = get_phpbb_version($phpbb_root_path);

if (file_exists($phpbb_root_path . 'phpbb/class_loader.' . $phpEx))
{
	define('PHPBB_31', true);

	require($phpbb_root_path . 'phpbb/class_loader.' . $phpEx);
	$phpbb_class_loader = new \phpbb\class_loader('phpbb\\', $phpbb_root_path . 'phpbb/', $phpEx);
	$phpbb_class_loader->register();

	require($phpbb_root_path . 'vendor/autoload.' . $phpEx);
}

if (!file_exists($phpbb_root_path . 'includes/functions_install.' . $phpEx) || version_compare($phpbb_version, '3.2', '>='))
{
	define('PHPBB_32', true);
}

if (file_exists($phpbb_root_path . 'vendor-ext') || version_compare($phpbb_version, '4.0', '>='))
{
	define('PHPBB_40', true);
}

require($phpbb_root_path . 'includes/functions.' . $phpEx);
require($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);

if (!function_exists('phpbb_email_hash') || version_compare($phpbb_version, '3.3', '>='))
{
	define('PHPBB_33', true);
}

// Need to set prefix here before constants.php are included.
$table_prefix = $settings->get_config('table_prefix', '');

require($phpbb_root_path . 'includes/constants.' . $phpEx);

if (!defined('PHPBB_32'))
{
	require($phpbb_root_path . 'includes/functions_install.' . $phpEx);
}

if (defined('PHPBB_31'))
{
	if (defined('PHPBB_32'))
	{
		$cache_dir = $quickinstall_path . $settings->get_config('cache_dir', '');
		$cache	   = new \phpbb\cache\driver\file($cache_dir);
		$user	   = new \phpbb\user(
			new \phpbb\language\language(
				new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx)),
			'\phpbb\datetime'
		);
	}
	else
	{
		$user	= new \phpbb\user('\phpbb\datetime');
		$cache	= new \phpbb\cache\driver\file();
	}

	$auth	= new \phpbb\auth\auth();
	require($phpbb_root_path . 'includes/functions_compatibility.' . $phpEx);
}
else
{
	require($phpbb_root_path . 'includes/acm/acm_file.' . $phpEx);
	require($phpbb_root_path . 'includes/auth.' . $phpEx);
	require($phpbb_root_path . 'includes/cache.' . $phpEx);
	require($phpbb_root_path . 'includes/session.' . $phpEx);

	// Create the user.
	$user	= new user();
	$auth	= new auth();
	$cache	= new cache();
}

// We need to set the template here.
$template = new twig($user, $settings->get_cache_dir(), $quickinstall_path);

// If there is a language selected in the dropdown menu in settings it's sent as GET, then igonre the hidden POST field.
if (isset($_GET['lang']))
{
	$language = qi_request_var('lang', '');
}
else if (!empty($_POST['sel_lang']))
{
	$language = qi_request_var('sel_lang', '');
}
else
{
	$language = $settings->get_config('qi_lang', '');
}
qi::apply_lang($language);

$profiles = $settings->get_profiles();

// Probably best place to validate the settings
$settings->validate();
$errors = $settings->get_errors();

// Set some standard variables we want to force
if (defined('PHPBB_31'))
{
	$config = new \phpbb\config\config(array(
		'load_tplcompile'	=> '1',
	));
	qi_set_config(false, false, false, $config);
	qi_set_config_count(null, null, null, $config);
}
else
{
	$config = array(
		'load_tplcompile'	=> '1',
	);
}

// update cache path
$template->set_cachepath($settings->get_cache_dir());

// force going to the settings page
if (!empty($errors) || $alt_env_missing || (empty($profiles) && ($page === 'main' || $page === '')) || ($page === 'main' && $settings->is_install()))
{
	$page = 'settings';
}

// Hide manage boards if there is no saved config.
$template->assign_var('S_IN_INSTALL', $settings->is_install());
$template->assign_var('S_HAS_PROFILES', $profiles);

// now create a qi_module object
$module	= new qi_module($quickinstall_path . 'modules/', 'qi_');

// Load the main module
$module->load($page, 'qi_main');
