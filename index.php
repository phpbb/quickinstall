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

function legacy_set_var(&$result, $var, $type, $multibyte = false)
{
	settype($var, $type);
	$result = $var;

	if ($type == 'string')
	{
		$result = trim(htmlspecialchars(str_replace(array("\r\n", "\r", "\0"), array("\n", "\n", ''), $result), ENT_COMPAT, 'UTF-8'));

		if (!empty($result))
		{
			// Make sure multibyte characters are wellformed
			if ($multibyte)
			{
				if (!preg_match('/^./u', $result))
				{
					$result = '';
				}
			}
			else
			{
				// no multibyte, allow only ASCII (0-127)
				$result = preg_replace('/[\x80-\xFF]/', '?', $result);
			}
		}

		$result = (STRIP) ? stripslashes($result) : $result;
	}
}

function legacy_request_var($var_name, $default, $multibyte = false, $cookie = false)
{
	if (!$cookie && isset($_COOKIE[$var_name]))
	{
		if (!isset($_GET[$var_name]) && !isset($_POST[$var_name]))
		{
			return (is_array($default)) ? array() : $default;
		}
		$_REQUEST[$var_name] = isset($_POST[$var_name]) ? $_POST[$var_name] : $_GET[$var_name];
	}

	$super_global = ($cookie) ? '_COOKIE' : '_REQUEST';
	if (!isset($GLOBALS[$super_global][$var_name]) || is_array($GLOBALS[$super_global][$var_name]) != is_array($default))
	{
		return (is_array($default)) ? array() : $default;
	}

	$var = $GLOBALS[$super_global][$var_name];
	if (!is_array($default))
	{
		$type = gettype($default);
	}
	else
	{
		list($key_type, $type) = each($default);
		$type = gettype($type);
		$key_type = gettype($key_type);
		if ($type == 'array')
		{
			reset($default);
			$default = current($default);
			list($sub_key_type, $sub_type) = each($default);
			$sub_type = gettype($sub_type);
			$sub_type = ($sub_type == 'array') ? 'NULL' : $sub_type;
			$sub_key_type = gettype($sub_key_type);
		}
	}

	if (is_array($var))
	{
		$_var = $var;
		$var = array();

		foreach ($_var as $k => $v)
		{
			legacy_set_var($k, $k, $key_type);
			if ($type == 'array' && is_array($v))
			{
				foreach ($v as $_k => $_v)
				{
					if (is_array($_v))
					{
						$_v = null;
					}
					legacy_set_var($_k, $_k, $sub_key_type, $multibyte);
					legacy_set_var($var[$k][$_k], $_v, $sub_type, $multibyte);
				}
			}
			else
			{
				if ($type == 'array' || is_array($v))
				{
					$v = null;
				}
				legacy_set_var($var[$k], $v, $type, $multibyte);
			}
		}
	}
	else
	{
		legacy_set_var($var, $var, $type, $multibyte);
	}

	return $var;
}

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

// Let's get the config.
$page		= legacy_request_var('page', 'main');
$mode		= legacy_request_var('mode', '');
$profile	= legacy_request_var('qi_profile', '');

$settings = new settings($profile, $mode);

// We need some phpBB functions too.
$alt_env = $settings->get_config('alt_env', '');
if ($alt_env !== '')
{
	$phpbb_root_path = "{$quickinstall_path}sources/phpBB3_alt/$alt_env/";
}

if (file_exists($phpbb_root_path . 'phpbb/class_loader.' . $phpEx))
{
	define('PHPBB_31', true);

	require($phpbb_root_path . 'phpbb/class_loader.' . $phpEx);
	$phpbb_class_loader = new \phpbb\class_loader('phpbb\\', $phpbb_root_path . 'phpbb/', $phpEx);
	$phpbb_class_loader->register();
}

require($phpbb_root_path . 'includes/functions.' . $phpEx);
require($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);

$delete_profile = (isset($_POST['delete-profile'])) ? true : false;

// Need to set prefix here before constants.php are included.
$table_prefix = $settings->get_config('table_prefix');

require($phpbb_root_path . 'includes/constants.' . $phpEx);
require($phpbb_root_path . 'includes/functions_install.' . $phpEx);

if (defined('PHPBB_31'))
{
	$user	= new \phpbb\user();
	$auth	= new \phpbb\auth\auth();
	$cache	= new \phpbb\cache\driver\file();

	require($phpbb_root_path . 'vendor/autoload.' . $phpEx);
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
$template = new template();
$template->set_custom_template('style', 'qi');

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
$module		= new module_handler($quickinstall_path . 'modules/', 'qi_');

// Set some standard variables we want to force
if (defined('PHPBB_31'))
{
	$config = new \phpbb\config\config(array(
		'load_tplcompile'	=> '1',
	));
	set_config(false, false, false, $config);
	set_config_count(null, null, null, $config);
}
else
{
	$config = array(
		'load_tplcompile'	=> '1',
	);
}

// overwrite
$cache->cache_dir = $settings->get_cache_dir();
$template->cachepath = $cache->cache_dir . 'tpl_qi_';

// Load the main module
$module->load($page, 'qi_main');
