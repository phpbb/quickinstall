<?php
/**
*
* @package quickinstall
* @copyright (c) 2007 phpBB Group
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
//error_reporting(E_ALL);
$level = E_ALL ^ E_NOTICE;
if (version_compare(PHP_VERSION, '5.4.0-dev', '>='))
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

if (version_compare(PHP_VERSION, '5.5.0', '>='))
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

if (version_compare(PHP_VERSION, '5.2.0', '<'))
{
	die('You are running an unsupported PHP version. phpBB QuickInstall only supports PHP version 5.2.0 and newer.');
}

// If we are on PHP >= 6.0.0 we do not need some code
if (version_compare(PHP_VERSION, '6.0.0-dev', '>='))
{
	/**
	* @ignore
	*/
	define('STRIP', false);
}
else
{
	define('STRIP', (get_magic_quotes_gpc()) ? true : false);
}

// Try to override some limits - maybe it helps some...
@set_time_limit(0);
@ini_set('memory_limit', '128M');

// Include scripts for quickinstall
require("{$quickinstall_path}includes/qi_constants.$phpEx");
require("{$quickinstall_path}includes/class_phpbb_functions.$phpEx");
require("{$quickinstall_path}includes/class_qi.$phpEx");
require("{$quickinstall_path}includes/class_qi_settings.$phpEx");
require("{$quickinstall_path}includes/qi_functions.$phpEx");
require("{$quickinstall_path}includes/functions_files.$phpEx");
require("{$quickinstall_path}includes/functions_module.$phpEx");
require("{$quickinstall_path}includes/template.$phpEx");

// Set PHP error handler to ours
set_error_handler(array('qi', 'msg_handler'), E_ALL);

// Make sure we have phpBB.
if (!file_exists($quickinstall_path . 'sources/phpBB3/common.' . $phpEx))
{
	trigger_error('phpBB not found. You need to download phpBB3 and extract it in sources/');
}

// We need some phpBB functions too.
require($phpbb_root_path . 'includes/functions.' . $phpEx);
require($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);

$page		= request_var('page', 'main');
$mode		= request_var('mode', '');
$profile	= request_var('qi_profile', '');
$delete_profile = (isset($_POST['delete-profile'])) ? true : false;

// Let's get the config.
$settings = new settings($profile, $mode);

// Need to set prefix here before constants.php are included.
$table_prefix = $settings->get_config('table_prefix');

require($phpbb_root_path . 'includes/constants.' . $phpEx);
require($phpbb_root_path . 'includes/auth.' . $phpEx);
require($phpbb_root_path . 'includes/acm/acm_file.' . $phpEx);
require($phpbb_root_path . 'includes/cache.' . $phpEx);
require($phpbb_root_path . 'includes/functions_install.' . $phpEx);
require($phpbb_root_path . 'includes/session.' . $phpEx);

// We need to set the template here.
$template = new template();
$template->set_custom_template('style', 'qi');

// Create the user.
$user = new user();

// If there is a language selected in the dropdown menu in settings it's sent as GET, then igonre the hidden POST field.
if (isset($_GET['lang']))
{
	$language = request_var('lang', '');
}
else if (!empty($_POST['sel_lang']))
{
	$language = request_var('sel_lang', '');
}
else
{
	$language = '';
}

$settings->apply_language($language);

// Probably best place to validate the settings
$settings->validate();
$error = $settings->get_error();

$page = (empty($error)) ? $page : 'settings';

if ($settings->install || $settings->is_converted || $mode == 'update_settings' || $page == 'settings')
{
	$page = 'settings';
	require($quickinstall_path . 'includes/qi_settings.' . $phpEx);
}

// now create a module_handler object
$auth		= new auth();
$cache		= new cache();
$module		= new module_handler($quickinstall_path . 'modules/', 'qi_');

// Set some standard variables we want to force
$config = array(
	'load_tplcompile'	=> '1',
);

// overwrite
$cache->cache_dir = $settings->get_cache_dir();
$template->cachepath = $cache->cache_dir . 'tpl_qi_';

// Load the main module
$module->load($page, 'qi_main');
