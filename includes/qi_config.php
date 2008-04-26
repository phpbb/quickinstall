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
if (!defined('IN_QUICKINSTALL'))
{
	exit;
}

/**
 * quickinstall configuration
 */
$qi_config = array(
	/**
	 * These are the database settings.
	 * dbms should be either firebird, mysqli, mysql, mssql, mssql_odbc, oracle, postgres, sqlite, if you are unsure, put in mysql
	 * if you're running this on localhost (which you should be!), dbhost and dbport can be left empty (in most cases)
	 * dbuser and dbpass are your database connection details, table_prefix should stay phpbb_, unless you know what you're doing
	 */
	'dbms'				=> 'mysqli',
	'dbhost'			=> '',
	'dbport'			=> '',
	'dbuser'			=> '',
	'dbpasswd'			=> '',

	'table_prefix'		=> 'phpbb_',

	/**
	 * do not modify this line!
	 */
	'qi_version'		=> '1.0.8',

	/**
	 * this is for lazy me who forgets to change it in the footer
	 */
	'phpbb_version'		=> '3.0.1',

	/**
	 * boards_dir is the folder where your boards are saved in
	 * it should be boards/ for the most, it must have a trailing slash.
	 *
	 * qi_lang is the language of quickinstall, there must be a folder
	 * with this name inside languages/
	 *
	 * qi_tz is the timezone you are in, so you have '-1', '0', '1', etc
	 * qi_dst - are we in daylight saving time or not? '1' = yes, '0' = no
	 *
	 * set version_check to false, if you don't want quickinstall to check
	 * for a new version
	 *
	 * database_prefix is added before all the databases to prevent overwriting
	 * databases not used by qi.
	 */
	'boards_dir'		=> 'boards/',
	'qi_lang'			=> 'en',
	'qi_tz'				=> '0',
	'qi_dst'			=> '0',
	'version_check'		=> true,
	'database_prefix'	=> 'qi_',

	/**
	 * These are the default settings for the new phpbb install, they are quite self::explainatory :D
	 * Make sure you enter html as html, 'eviL<3' becomes 'eviL&lt;3', make sure you don't leave admin_name
	 * or admin_pass empty.
	 */
	'site_name'			=> 'Testing Board',
	'site_desc'			=> 'eviLs testing hood',
	'admin_email'		=> 'evil@phpbbmodders.net',
	'default_lang'		=> 'en',
	'server_name'		=> 'localhost',
	'server_port'		=> 80,
	'cookie_domain'		=> '',
	'board_email'		=> '',
	'email_enable'		=> 0,
	'smtp_delivery'		=> 0,
	'smtp_host'			=> '',
	'smtp_auth'			=> '',
	'smtp_user'			=> '',
	'smtp_pass'			=> '',
	'cookie_secure'		=> 0,
	'server_protocol'	=> '',
	'admin_name'		=> 'admin',
	'admin_pass'		=> 'admin',
);

/**
 * Hello, okay, now we can get started, in order to start using phpBB Quickinstall
 * you have to remove // from the line below, so it should look like this:
 * @define('QI_INSTALLED', true);
 */
//@define('QI_INSTALLED', true);
@define('DEBUG', true);

?>