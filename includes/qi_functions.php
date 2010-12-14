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
 * Validates the db name to not contain any unwanted chars.
 * @param string $dbname, string to validate.
 * @return string $dbname, validated name.
 */
function validate_dbname($dbname, $first_char = false)
{
	if (empty($dbname))
	{
		// Nothing to validate, this should already have been catched.
		return('');
	}

	// Try to replace some chars whit their valid equivalents
	$chars_int_src  = array('å', 'ä', 'ö', 'š', 'ž', 'Ÿ', 'à', 'á', 'â', 'ã', 'ä', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Š', 'Ž', 'Ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý');
	$chars_int_dest = array('a', 'a', 'o', 's', 'z', 'y', 'a', 'a', 'a', 'a', 'a', 'e', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'e', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'S', 'Z', 'Y', 'A', 'A', 'A', 'A', 'A', 'A', 'E', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'E', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y');
	$dbname = str_replace($chars_int_src, $chars_int_dest, $dbname);

	// Replace these with a underscore.
	$chars_replace = array(' ', '&', '/', '–', '-', '.');
	$dbname = str_replace($chars_replace, '_', $dbname);

	// Just drop remaining non valid chars.
	$dbname = preg_replace('/[^A-Za-z0-9_]*/', '', $dbname);

	// make sure that the first char is not a underscore if set.
	$prefix = ($first_char && $dbname[0] == '_') ? 'qi' : '';

	return($prefix . $dbname);
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

	$error .= ($config['db_prefix'] != validate_dbname($config['db_prefix'], true)) ? $user->lang['DB_PREFIX'] . ' ' . $user->lang['IS_NOT_VALID'] . '<br />' : '';

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
	global $quickinstall_path, $phpEx, $user;

	if (!file_exists($quickinstall_path . 'qi_config.cfg'))
	{
		trigger_error('qi_config.cfg not found. Make sure that you have renamed qi_config_sample.cfg to qi_config.cfg.');
	}

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

		$row = trim($row);
		$cfg_row = explode('=', $row);

		if (empty($cfg_row[0]))
		{
			continue;
		}

		$key = trim($cfg_row[0]);

		// Handle config values containing a = char.
		if (sizeof($cfg_row) > 2)
		{
			unset($cfg_row[0]);
			$value = implode('=', $cfg_row);
		}
		else
		{
			$value = (isset($cfg_row[1])) ? $cfg_row[1] : '';
		}

		$qi_config[$key] = $value;
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

	if (!empty($config['qi_lang']) && $config['qi_lang'] != $user->lang['USER_LANG'])
	{
		if (file_exists($quickinstall_path . 'language/' . $config['qi_lang']))
		{
			$user->lang = $config['qi_lang'];
			qi::add_lang(array('qi', 'phpbb'), $quickinstall_path . 'language/' . $config['qi_lang'] . '/');
		}
	}

	file_put_contents($quickinstall_path . 'qi_config.cfg', $cfg_string);
}

/**
 * Generate a lang select for the settings page.
 */
function gen_lang_select($language = '')
{
	global $quickinstall_path, $phpEx, $user, $template;

	$lang_dir = scandir($quickinstall_path . 'language');
	$lang_arr = array();

	foreach ($lang_dir as $lang_path)
	{
		if (file_exists($quickinstall_path . 'language/' . $lang_path . '/phpbb.' . $phpEx))
		{
			include($quickinstall_path . 'language/' . $lang_path . '/phpbb.' . $phpEx);

			if (!empty($language) && $language == $lang['USER_LANG'])
			{
				$s_selected = true;
			}
			else
			{
				$s_selected = false;
			}

			$template->assign_block_vars('lang_row', array(
				'LANG_CODE' => $lang['USER_LANG'],
				'LANG_NAME' => $lang['USER_LANG_LONG'],
				'S_SELECTED' => $s_selected,
			));
			unset($lang);
		}
	}
}

?>