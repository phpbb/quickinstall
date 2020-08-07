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

/**
 * Generate DBMS select options for the settings tab.
 * Checks that each extension is loaded.
 *
 * @param string $selected the DBMS to set as selected
 * @return array the options
 */
function gen_dbms_options($selected = 'mysqli')
{
	$dbms_ary = array(
		'mysqli'	=> array(
			'LABEL'		=> 'MySQLi',
			'MODULE'	=> 'mysqli',
		),
		'mysql'		=> array(
			'LABEL'		=> 'MySQL',
			'MODULE'	=> 'mysql',
		),
		'mssql'		=>	array(
			'LABEL'		=> 'MS SQL Server',
			'MODULE'	=> 'mssql',
		),
		'postgres'	=> array(
			'LABEL'		=> 'PostgreSQL 8.3+',
			'MODULE'	=> 'pgsql',
		),
		'sqlite'	=> array(
			'LABEL'		=> 'SQLite',
			'MODULE'	=> 'sqlite',
		),
		'sqlite3'		=> array(
			'LABEL'			=> 'SQLite3',
			'MODULE'		=> 'sqlite3',
		),
	);

	$options = [];
	foreach ($dbms_ary as $dbms => $dbms_info)
	{
		if (extension_loaded($dbms_info['MODULE']))
		{
			$options[] = [
				'name' => $dbms_info['LABEL'],
				'value' => $dbms,
				'selected' => $dbms === $selected,
			];
		}
	}

	return $options;
}

/**
* Borrowed from phpBB 3.1 includes/functions.php/phpbb_format_timezone_offset()
* To have it available for 3.0.x too.
*
* Format the timezone offset with hours and minutes
*
* @param	int		$tz_offset	Timezone offset in seconds
* @param	bool	$show_null	Whether null offsets should be shown
* @return	string		Normalized offset string:	-7200 => -02:00
*													16200 => +04:30
*/
function qi_format_timezone_offset($tz_offset, $show_null = false)
{
	$sign = ($tz_offset < 0) ? '-' : '+';
	$time_offset = abs($tz_offset);

	if ($time_offset == 0 && $show_null == false)
	{
		return '';
	}

	$offset_seconds	= $time_offset % 3600;
	$offset_minutes	= $offset_seconds / 60;
	$offset_hours	= ($time_offset - $offset_seconds) / 3600;

	return sprintf("%s%02d:%02d", $sign, $offset_hours, $offset_minutes);
}

/**
* Borrowed from phpBB 3.1 includes/functions.php/phpbb_tz_select_compare()
* To have it available for 3.0.x too.
*
* Compares two time zone labels.
* Arranges them in increasing order by timezone offset.
* Places UTC before other timezones in the same offset.
*/
function qi_tz_select_compare($a, $b)
{
	$a_sign = $a[3];
	$b_sign = $b[3];
	if ($a_sign != $b_sign)
	{
		return $a_sign === '-' ? -1 : 1;
	}

	$a_offset = substr($a, 4, 5);
	$b_offset = substr($b, 4, 5);
	if ($a_offset == $b_offset)
	{
		$a_name = substr($a, 12);
		$b_name = substr($b, 12);
		if ($a_name === $b_name)
		{
			return 0;
		}

		if ($a_name === 'UTC')
		{
			return -1;
		}

		if ($b_name === 'UTC')
		{
			return 1;
		}

		return $a_name < $b_name ? -1 : 1;
	}

	if ($a_sign === '-')
	{
		return $a_offset > $b_offset ? -1 : 1;
	}

	return $a_offset < $b_offset ? -1 : 1;
}

/**
 * Mostly borrowed from phpBB 3.1 includes/functions.php/phpbb_timezone_select()
 * To have it available for 3.0.x too.
 *
 * Options to pick a timezone and date/time
 *
 * @param \phpbb\user $user     Object of the current user
 * @param string      $default  A timezone to select
 * @param boolean     $truncate Shall we truncate the options text
 *
 * @return string Returns the options for the time selector.
 */
function qi_timezone_select($user, $default = '', $truncate = false)
{
	date_default_timezone_set('UTC');
	$unsorted_timezones = DateTimeZone::listIdentifiers();
	$timezones = array();

	foreach ($unsorted_timezones as $timezone)
	{
		$tz = date_create(date('d M Y, H:i'), timezone_open($timezone));
		$offset = date_offset_get($tz);
		$current_time = date('d M Y, H:i', (time() + $offset));
		$offset_string = qi_format_timezone_offset($offset, true);

		$timezones['UTC' . $offset_string . ' - ' . $timezone] = array(
			'tz'		=> $timezone,
			'offset'	=> $offset_string,
			'current'	=> $current_time,
		);

		if ($timezone === $default)
		{
			$default_offset = 'UTC' . $offset_string;
		}
	}
	unset($unsorted_timezones);

	uksort($timezones, 'qi_tz_select_compare');

	$tz_select = $opt_group = '';

	foreach ($timezones as $key => $timezone)
	{
		if ($opt_group != $timezone['offset'])
		{
			// Generate tz_select for backwards compatibility
			$tz_select .= ($opt_group) ? '</optgroup>' : '';
			$tz_select .= '<optgroup label="' . $user->lang(array('timezones', 'UTC_OFFSET_CURRENT'), $timezone['offset'], $timezone['current']) . '">';
			$opt_group = $timezone['offset'];
		}

		$label = $timezone['tz'];
		if (isset($user->lang['timezones'][$label]))
		{
			$label = $user->lang['timezones'][$label];
		}
		$title = $user->lang(array('timezones', 'UTC_OFFSET_CURRENT'), $timezone['offset'], $label);

		if ($truncate)
		{
			$label = truncate_string($label, 50, 255, false, '...');
		}

		// Also generate timezone_select for backwards compatibility
		$selected = ($timezone['tz'] === $default) ? ' selected="selected"' : '';
		$tz_select .= '<option title="' . $title . '" value="' . $timezone['tz'] . '"' . $selected . '>' . $label . '</option>';
	}
	$tz_select .= '</optgroup>';

	return $tz_select;
}

function gen_error_msg($msg_text, $msg_title = 'GENERAL_ERROR', $msg_explain = '')
{
	global $quickinstall_path, $user, $phpEx;

	if ($user !== null && !empty($user->lang))
	{
		$lang = $user->lang;
	}
	else
	{
		$lang = [];
		include "{$quickinstall_path}language/en/qi.$phpEx";
	}

	phpbb_functions::send_status_line(503, 'Service Unavailable');

	if (!class_exists('twig'))
	{
		require("{$quickinstall_path}includes/twig.$phpEx");
	}

	$template = new twig($user, false, $quickinstall_path);

	$template->assign_vars([
		'QI_PATH'              => $quickinstall_path,
		'MSG_TITLE'            => $lang[$msg_title],
		'MSG_TEXT'             => $lang[$msg_text],
		'MSG_EXPLAIN'          => $lang[$msg_explain],
		'RETURN_LINKS'         => sprintf($lang['GO_QI_MAIN'], '<a href="' . qi::url('main') . '">', '</a>') . ' &bull; ' . sprintf($lang['GO_QI_SETTINGS'], '<a href="' . qi::url('settings') . '">', '</a>'),
		'QI_VERSION'           => qi::current_version(),
		'L_QUICKINSTALL'       => $lang['QUICKINSTALL'],
		'L_PHPBB_QI_TEXT'      => $lang['PHPBB_QI_TEXT'],
		'L_FOR_PHPBB_VERSIONS' => $lang['FOR_PHPBB_VERSIONS'],
		'L_POWERED_BY_PHPBB'   => $lang['POWERED_BY_PHPBB'],
	]);

	$template->display('error');

	exit;
}

function create_board_warning($msg_title, $msg_text, $page)
{
	global $phpEx;

	$args =  'page='			. urlencode($page);
	$args .= '&error_title='	. urlencode($msg_title);
	$args .= '&error_msg='		. urlencode($msg_text);
	$args .= '&error='	. 1;

	foreach ($_POST as $key => $value)
	{
		if (!empty($value))
		{
			$args .= "&$key=" . urlencode($value);
		}
	}

	$url = "index.$phpEx?$args";
	if (qi::is_ajax())
	{
		qi::ajax_response(array('redirect' => $url));
	}
	qi::redirect($url);
}

function legacy_set_var(&$result, $var, $type, $multibyte = false)
{
	settype($var, $type);
	$result = $var;

	if ($type === 'string')
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

function qi_request_var($var_name, $default, $multibyte = false, $cookie = false)
{
	$method = (function_exists('request_var')) ? 'request_var' : 'legacy_request_var';

	return $method($var_name, $default, $multibyte, $cookie);
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
	if (!isset($GLOBALS[$super_global][$var_name]) || is_array($GLOBALS[$super_global][$var_name]) !== is_array($default))
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
		if ($type === 'array')
		{
			reset($default);
			$default = current($default);
			list($sub_key_type, $sub_type) = each($default);
			$sub_type = gettype($sub_type);
			$sub_type = ($sub_type === 'array') ? 'NULL' : $sub_type;
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
			if ($type === 'array' && is_array($v))
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
				if ($type === 'array' || is_array($v))
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
 * Validates the db name, to not contain any unwanted chars.
 * @param string $dbname, string to validate.
 * @param bool $first_char, if true adds 'qi' before $dbname if it starts with a underline.
 * @param bool $path, if true allows hyphen and dot. Otherwise they will be replaced with underline.
 * @return string $dbname, validated name.
 */
function validate_dbname($dbname, $first_char = false, $path = false)
{
	if (empty($dbname))
	{
		// Nothing to validate, this should already have been catched.
		return '';
	}

	// Try to replace some chars whit their valid equivalents
	$chars_int_src  = array('å', 'ä', 'ö', 'š', 'ž', 'Ÿ', 'à', 'á', 'â', 'ã', 'ä', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Š', 'Ž', 'Ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý');
	$chars_int_dest = array('a', 'a', 'o', 's', 'z', 'y', 'a', 'a', 'a', 'a', 'a', 'e', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'e', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'S', 'Z', 'Y', 'A', 'A', 'A', 'A', 'A', 'A', 'E', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'E', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y');
	$dbname = str_replace($chars_int_src, $chars_int_dest, $dbname);

	// Replace these with a underscore.
	$chars_replace = ($path) ? array(' ', '&', '/', '–') : array(' ', '&', '/', '–', '-', '.');
	$dbname = str_replace($chars_replace, '_', $dbname);

	// Just drop remaining non valid chars.
	$dbname = preg_replace('/[^A-Za-z0-9-_.]*/', '', $dbname);

	// make sure that the first char is not a underscore if set.
	$prefix = ($first_char && $dbname[0] === '_') ? 'qi' : '';

	return $prefix . $dbname;
}

/**
 * Get an array of alternative environments for select menus.
 */
function get_alternative_env($selected_option = '')
{
	global $user, $quickinstall_path;

	$alt_envs = [
		[
			'name' => $user->lang['DEFAULT_ENV'],
			'value' => '',
			'selected' => empty($selected_option),
		],
	];

	$dh = dir($quickinstall_path . 'sources/phpBB3_alt');
	while (false !== ($file = $dh->read()))
	{
		// Ignore everything that starts with a dot.
		if ($file[0] === '.' || is_file($quickinstall_path . 'sources/phpBB3_alt/' . $file))
		{
			continue;
		}

		$value = htmlspecialchars($file);
		$alt_envs[] = [
			'name' => $value,
			'value' => $value,
			'selected' => $file === $selected_option,
		];
	}
	$dh->close();

	return $alt_envs;
}

/**
 * Get a list of installed boards.
 */
function get_installed_boards()
{
	global $settings, $template, $phpEx;

	// list of boards
	$boards_dir = $settings->get_boards_dir();
	$boards_arr = scandir($boards_dir, SCANDIR_SORT_NONE);
	natcasesort($boards_arr); // Sort the tables in a natural order 10 > 9

	foreach ($boards_arr as $board)
	{
		if ($board[0] === '.' || is_file($boards_dir . $board))
		{
			continue;
		}

		$version = '';
		// Try to find out phpBB version.
		if (file_exists("{$boards_dir}$board/includes/constants.$phpEx"))
		{
			$rows = file("{$boards_dir}$board/includes/constants.$phpEx", FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);

			foreach ($rows as $row)
			{
				if (($pos = strpos($row, "'PHPBB_VERSION', '")) !== false)
				{
					$pos += 18;
					$version = substr($row, $pos, -3);
					break;
				}
			}
			unset($rows);
		}

		$template->assign_block_vars('board_row', array(
			'BOARD_NAME'	=> htmlspecialchars($board),
			'BOARD_URL'		=> $settings->get_boards_url() . urlencode($board),
			'VERSION'		=> $version,
		));
	}
}

function db_connect($db_data = '')
{
	global $phpbb_root_path, $phpEx, $sql_db, $db, $quickinstall_path, $settings;

	if (empty($db_data))
	{
		list($dbms, $dbhost, $dbuser, $dbpasswd, $dbport) = $settings->get_db_data();
		// When db_data is empty, it means the db does not exist yet, so for postgres
		// we need to set dbname to false so the driver can connect to the postgres db
		$dbname = ($dbms !== 'postgres') ? $settings->get_config('dbname') : false;
	}
	else
	{
		list($dbms, $dbhost, $dbuser, $dbpasswd, $dbport) = $db_data;
		$dbname = $settings->get_config('dbname');
	}

	// If we get here and the extension isn't loaded it should be safe to just go ahead and load it
	$available_dbms = qi_get_available_dbms($dbms);

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
		$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false, false);
	}
	else
	{
		$db->sql_connect($dbhost, $dbuser, $dbpasswd, false, $dbport, false, false);
	}

	$db->sql_return_on_error(true);

	return $db;
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

function qi_get_available_dbms($dbms)
{
	if (defined('PHPBB_32'))
	{
		global $phpbb_root_path;
		$database = new \phpbb\install\helper\database(new \phpbb\filesystem\filesystem(), $phpbb_root_path);
		return call_user_func(array($database, 'get_available_dbms'), $dbms);
	}

	return call_user_func('get_available_dbms', $dbms);
}

function qi_get_phpbb_version()
{
	if (defined('PHPBB_40'))
	{
		return '4.0';
	}

	if (defined('PHPBB_33'))
	{
		return '3.3';
	}

	if (defined('PHPBB_32'))
	{
		return '3.2';
	}

	if (defined('PHPBB_31'))
	{
		return '3.1';
	}

	return '3.0';
}
