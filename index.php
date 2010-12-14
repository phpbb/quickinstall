<?php
/**
*
* @package quickinstall
* @version $Id$
* @copyright (c) 2007, 2008 eviL3
* @copyright (c) 2010 Jari Kanerva (tumba25)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
error_reporting(E_ALL ^ E_NOTICE);

if (version_compare(PHP_VERSION, '5.2.0') < 0)
{
	die('You are running an unsupported PHP version. Please upgrade to PHP 5.2.0 or higher before trying to do anything with phpBB 3.0');
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
require($quickinstall_path . 'includes/qi_constants.' . $phpEx);
require($quickinstall_path . 'includes/functions_quickinstall.' . $phpEx);
require($quickinstall_path . 'includes/qi_functions.' . $phpEx);
require($quickinstall_path . 'includes/functions_files.' . $phpEx);
require($quickinstall_path . 'includes/functions_module.' . $phpEx);
require($quickinstall_path . 'includes/template.' . $phpEx);

// Set PHP error handler to ours
set_error_handler(array('qi', 'msg_handler'), E_ALL);

// Make sure we have phpBB.
if (!file_exists($quickinstall_path . 'sources/phpBB3'))
{
	trigger_error('phpBB not found. You need to download phpBB3 and extract it in sources/');
}

// Let's get the config.
$qi_config = get_settings();

foreach (array('dbms', 'dbhost', 'dbuser', 'dbpasswd', 'dbport', 'table_prefix') as $var)
{
	$$var = $qi_config[$var];
}

// We need some phpBB functions to.
require($phpbb_root_path . 'includes/functions.' . $phpEx);
require($phpbb_root_path . 'includes/constants.' . $phpEx);
require($phpbb_root_path . 'includes/auth.' . $phpEx);
require($phpbb_root_path . 'includes/acm/acm_file.' . $phpEx);
require($phpbb_root_path . 'includes/cache.' . $phpEx);
require($phpbb_root_path . 'includes/functions_install.' . $phpEx);
require($phpbb_root_path . 'includes/session.' . $phpEx);
require($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);

$mode = request_var('mode', 'main');
$qi_install = (empty($qi_config)) ? true : false;

// We need to set the template here.
$template = new template();
$template->set_custom_template('style', 'qi');

// Create the user.
$user = new user();

// Get and set the language.
$language = (!empty($qi_config['qi_lang'])) ? $qi_config['qi_lang'] : 'en';
$language = request_var('lang', $language);

$user->lang = (file_exists($quickinstall_path . 'language/' . $language)) ? $language : 'en';
qi::add_lang(array('qi', 'phpbb'), $quickinstall_path . 'language/' . $user->lang . '/');

// Probably best place to validate the settings
$error = validate_settings($qi_config);
$mode = (empty($error)) ? $mode : (($mode == 'update_settings') ? 'update_settings' : 'settings');

if ($qi_install || $mode == 'update_settings' || $mode == 'settings')
{
	require($quickinstall_path . 'includes/qi_settings.' . $phpEx);
}

// Just put these here temporary. I'll change to use the constants later... Maybe tomorrow or so...
$qi_config['version_check'] = false;
$qi_config['qi_version'] = QI_VERSION;
$qi_config['phpbb_version'] = PHPBB_VERSION;
$qi_config['automod_version'] = AUTOMOD_VERSION;

// If we get here and the extension isn't loaded it should be safe to just go ahead and load it
$available_dbms = get_available_dbms($dbms);

if (!isset($available_dbms[$dbms]['DRIVER']))
{
	trigger_error("The $dbms dbms is either not supported, or the php extension for it could not be loaded.", E_USER_ERROR);
}

// Load the appropriate database class if not already loaded
include($phpbb_root_path . 'includes/db/' . $available_dbms[$dbms]['DRIVER'] . '.' . $phpEx);

// now the quickinstall dbal extension
include($quickinstall_path . 'includes/db/' . $available_dbms[$dbms]['DRIVER'] . '.' . $phpEx);

// Instantiate the database
$sql_db = 'dbal_' . $available_dbms[$dbms]['DRIVER'] . '_qi';
$db = new $sql_db();
$db->sql_connect($dbhost, $dbuser, $dbpasswd, false, $dbport, false, false);
$db->sql_return_on_error(true);

// now create a module_handler object
$auth		= new auth();
$cache		= new cache();
$module		= new module_handler($quickinstall_path . 'modules/', 'qi_');

// Set some standard variables we want to force
$config = array(
	'load_tplcompile'	=> '1',
);

// overwrite
$cache->cache_dir = $quickinstall_path . 'cache/';
$template->cachepath = $quickinstall_path . 'cache/tpl_qi_';

// load lang
qi::add_lang(array('qi', 'phpbb'));

// Load the main module
$module->load($mode, 'qi_main');

?>