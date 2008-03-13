<?php
/**
*
* @package quickinstall
* @version $Id$
* @copyright (c) 2007, 2008 eviL3
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
define('IN_QUICKINSTALL', true);

$quickinstall_path = './';
$phpbb_root_path = $quickinstall_path . 'sources/phpBB3/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

// Report all errors, except notices
//error_reporting(E_ALL);
error_reporting(E_ALL ^ E_NOTICE);

if (version_compare(PHP_VERSION, '5.1.0') < 0)
{
	die('You are running an unsupported PHP version. Please upgrade to PHP 5.1.0 or higher before trying to do anything with phpBB 3.0');
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
	set_magic_quotes_runtime(0);

	// Be paranoid with passed vars
	if (@ini_get('register_globals') == '1' || strtolower(@ini_get('register_globals')) == 'on')
	{
		deregister_globals();
	}

	define('STRIP', (get_magic_quotes_gpc()) ? true : false);
}

// Try to override some limits - maybe it helps some...
@set_time_limit(0);
@ini_set('memory_limit', '128M');

// Include scripts for quickinstall
require($quickinstall_path . 'includes/qi_config.' . $phpEx);
require($quickinstall_path . 'includes/functions_quickinstall.' . $phpEx);
require($quickinstall_path . 'includes/functions_files.' . $phpEx);
require($quickinstall_path . 'includes/functions_module.' . $phpEx);

foreach (array('dbms', 'dbhost', 'dbuser', 'dbpasswd', 'dbport', 'table_prefix') as $var)
{
	$$var = &$qi_config[$var];
}

// Set PHP error handler to ours
set_error_handler(array('qi', 'msg_handler'));

if (!defined('QI_INSTALLED'))
{
	// quickinstall isn't installed yet
	$msg_title = 'Z0MG! qi is not installed yet :O';
	trigger_error('You have not modified the configuration of phpBB QuickInstall yet, go to includes/qi_config.php and adjust the settings, then uncomment the QI_INSTALLED line as explained.', E_USER_ERROR);
}

// Include essential scripts
include($phpbb_root_path . 'includes/auth.' . $phpEx);
include($phpbb_root_path . 'includes/acm/acm_file.' . $phpEx);
include($phpbb_root_path . 'includes/cache.' . $phpEx);
require($phpbb_root_path . 'includes/constants.' . $phpEx);
require($phpbb_root_path . 'includes/functions.' . $phpEx);
require($phpbb_root_path . 'includes/functions_install.' . $phpEx);
require($phpbb_root_path . 'includes/functions_template.' . $phpEx);
include($phpbb_root_path . 'includes/session.' . $phpEx);
include($phpbb_root_path . 'includes/template.' . $phpEx);
require($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);

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
$user		= new user();
$auth		= new auth();
$cache		= new cache();
$template	= new template();
$module		= new module_handler($quickinstall_path . 'modules/', 'qi_');

// Set some standard variables we want to force
$config = array(
	'load_tplcompile'	=> '1',
);

// change tpl path
$template->set_custom_template('style', 'qi');

// overwrite
$cache->cache_dir = $quickinstall_path . 'cache/';
$template->cachepath = $quickinstall_path . 'cache/tpl_qi_';

// load lang
qi::add_lang(array('qi', 'phpbb'));

// Load the main module
$module->load(request_var('mode', 'main'), 'qi_main');

?>