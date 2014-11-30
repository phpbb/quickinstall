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
if (!defined('IN_QUICKINSTALL'))
{
	exit;
}

function create_board_warning($title, $text, $page)
{
	global $settings, $phpEx;

	$args =  'page='			. urlencode($page);
	$args .= '&error_title='	. urlencode($title);
	$args .= '&error_msg='		. urlencode($text);
	$args .= '&error='	. 1;

	foreach ($_POST as $key => $value)
	{
		if (!empty($value))
		{
			$args .= "&$key=" . urlencode($value);
		}
	}

	$url = "index.$phpEx?$args";
	qi::redirect($url);
}

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
 * Get a list of alternative environments.
 */
function get_alternative_env($selected_option = '')
{
	global $user, $quickinstall_path;

	$selected = (empty($selected_option)) ? ' selected="selected"' : '';
	$alt_env = "<option value=''$selected>{$user->lang['DEFAULT_ENV']}</option>";
	$d = dir($quickinstall_path . 'sources/phpBB3_alt');
	while (false !== ($file = $d->read()))
	{
		// Ignore everything that starts with a dot.
		if ($file[0] === '.' || is_file($quickinstall_path . 'sources/phpBB3_alt/' . $file))
		{
			continue;
		}

		$selected	= ($file == $selected_option) ? ' selected="selected"' : '';
		$file	= htmlspecialchars($file);

		$alt_env .= "<option{$selected}>$file</option>";
	}
	$d->close();

	return($alt_env);
}

/**
 * Get a list of installed boards.
 */
function get_installed_boards()
{
	global $settings, $template;

	$boards_dir = $settings->get_boards_dir();
	$boards_arr = scandir($boards_dir);

	$s_have_boards = false;

	// list of boards
	$boards_arr = scandir($settings->get_boards_dir());
	foreach ($boards_arr as $board)
	{
		if ($board[0] === '.' || is_file($boards_dir . $board))
		{
			continue;
		}

		$s_have_boards = true;

		$template->assign_block_vars('board_row', array(
			'BOARD_NAME'	=> htmlspecialchars($board),
			'BOARD_URL'		=> $settings->get_boards_url() . urlencode($board),
		));
	}

	$template->assign_var('S_HAVE_BOARDS', $s_have_boards);
}

function db_connect($db_data = '')
{
	global $phpbb_root_path, $phpEx, $sql_db, $db, $quickinstall_path, $settings;

	$db_data = (empty($db_data)) ? $settings->get_db_data() : $db_data;

	list($dbms, $dbhost, $dbuser, $dbpasswd, $dbport) = $db_data;

	// If we get here and the extension isn't loaded it should be safe to just go ahead and load it
	$available_dbms = get_available_dbms($dbms);

	if (!isset($available_dbms[$dbms]['DRIVER']))
	{
		trigger_error("The $dbms dbms is either not supported, or the php extension for it could not be loaded.", E_USER_ERROR);
	}

	// Instantiate the database
	if (defined('PHPBB_31'))
	{
		$dbal = substr($available_dbms[$dbms]['DRIVER'], strrpos($available_dbms[$dbms]['DRIVER'], '\\') + 1);
		// Load the appropriate database class if not already loaded.
		if (!class_exists('dbal_' . $dbal . '_qi'))
		{
			// now the quickinstall dbal extension
			include($quickinstall_path . 'includes/db/31/' . $dbal . '.' . $phpEx);
		}

		$sql_db = 'dbal_' . $dbal  . '_qi';
	}
	else
	{
		// Load the appropriate database class if not already loaded.
		if (!class_exists('dbal_' . $available_dbms[$dbms]['DRIVER'] . '_qi'))
		{
			// phpBB dbal class.
			include($phpbb_root_path . 'includes/db/' . $available_dbms[$dbms]['DRIVER'] . '.' . $phpEx);

			// now the quickinstall dbal extension
			include($quickinstall_path . 'includes/db/' . $available_dbms[$dbms]['DRIVER'] . '.' . $phpEx);
		}
		$sql_db = 'dbal_' . $available_dbms[$dbms]['DRIVER'] . '_qi';
	}

	$db = new $sql_db();

	if (defined('PHPBB_31'))
	{
		$db->sql_connect($dbhost, $dbuser, $dbpasswd, $settings->get_config('dbname') , $dbport, false, false);
	}
	else
	{
		$db->sql_connect($dbhost, $dbuser, $dbpasswd, false, $dbport, false, false);
	}

	$db->sql_return_on_error(true);

	return($db);
}

/**
 * Not tested yet
 */
function db_close($db = false)
{
	if (empty($db))
	{
		// This should not be needed but keep it while testing.
		global $db;
	}

	$db->sql_close();
}
