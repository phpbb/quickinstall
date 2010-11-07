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
if (!defined('IN_QUICKINSTALL'))
{
	exit;
}

/**
 * validate_settings()
 * Some nubs might edit the settings manually.
 * We need to make sure they are ok.
 *
 * @param array settings
 * @return string $error
 */
function validate_settings(&$config)
{
	global $user;

	$config['no_dbpasswd'] = (empty($config['no_dbpasswd']) || $config['no_dbpasswd'] != 1) ? 0 : 1;
	// Lets check the required settings...
	$error = '';
	$error .= ($config['dbms'] == '') ? $user->lang['DBMS'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
	$error .= ($config['dbhost'] == '') ? $user->lang['DBHOST'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
	$error .= ($config['dbuser'] == '') ? $user->lang['DBUSER'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
	$error .= ($config['dbpasswd'] == '' && !$config['no_dbpasswd']) ? $user->lang['DBPASSWD'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
	$error .= ($config['dbpasswd'] != '' && $config['no_dbpasswd']) ? $user->lang['NO_DBPASSWD_ERR'] . '<br />' : '';
	$error .= ($config['table_prefix'] == '') ? $user->lang['TABLE_PREFIX'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
	$error .= ($config['boards_dir'] == '') ? $user->lang['BOARDS_DIR'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
	$error .= ($config['qi_lang'] == '') ? $user->lang['QI_LANG'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
	$error .= ($config['qi_tz'] == '') ? $user->lang['QI_TZ'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
	$error .= ($config['db_prefix'] == '') ? $user->lang['DB_PREFIX'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
	$error .= ($config['admin_name'] == '') ? $user->lang['ADMIN_NAME'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
	$error .= ($config['admin_pass'] == '') ? $user->lang['ADMIN_PASS'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
	$error .= ($config['admin_email'] == '') ? $user->lang['ADMIN_EMAIL'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
	$error .= ($config['site_name'] == '') ? $user->lang['SITE_NAME'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
	$error .= ($config['server_name'] == '') ? $user->lang['SERVER_NAME'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
	$error .= ($config['server_port'] == '') ? $user->lang['SERVER_PORT'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
	$error .= ($config['cookie_domain'] == '') ? $user->lang['COOKIE_DOMAIN'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
	$error .= ($config['board_email'] == '') ? $user->lang['BOARD_EMAIL'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
	$error .= ($config['default_lang'] == '') ? $user->lang['DEFAULT_LANG'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';

	return($error);
}


/**
 * get_settings()
 * Reads the settings from file.
 *
 * @return array
 */
function get_settings()
{
	global $quickinstall_path, $phpEx;

	$config = file($quickinstall_path . 'qi_config.cfg');

	if (empty($config))
	{
		// Better to return an array since we at this moment don't know if some other things needs that.
		return (array());
	}

	$qi_config = array();
	// Let's split the config.
	foreach ($config as $row)
	{
		if (empty($row))
		{
			continue;
		}

		$row = rtrim($row);

		// I'm to tired for regexp right now..
		$i = strpos($row, '=');
		$key = substr($row, 0, $i);
		$value = substr($row, $i + 1);

		$qi_config = array_merge($qi_config, array($key => $value));
	}

	// Make sure the selected language exists.
	if (!file_exists($quickinstall_path . 'language/' . $qi_config['qi_lang'] . '/qi.' . $phpEx))
	{
		// Assume English exists.
		$qi_config['qi_lang'] = 'en';
	}

	// Temporary fix for the MySQLi error.
	$qi_config['dbms'] = ($qi_config['dbms'] == 'mysqli') ? 'mysql' : $qi_config['dbms'];

	return($qi_config);
}

/**
 * update_settings
 * Saves config to file.
 *
 * @param array
 * @return string, error
 */
function update_settings(&$config)
{
	global $quickinstall_path, $phpEx, $user;

	// The config cant be empty
	if (empty($config))
	{
		return($user->lang['CONFIG_EMPTY']);
	}

	$error = validate_settings($config);

	if (!empty($error))
	{
		return($error);
	}

	// Let's make sure our boards dir ends with a slash.
	$config['boards_dir'] = (substr($config['boards_dir'], -1) == '/') ? $config['boards_dir'] : $config['boards_dir'] . '/';

	$cfg_string = '';

	foreach ($config as $key => $value)
	{
		$cfg_string .= $key . '=' . $value . "\n";
	}

	file_put_contents($quickinstall_path . 'qi_config.cfg', $cfg_string);
}


?>