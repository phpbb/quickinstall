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
 * Encapsulates quickinstall settings.
 * Provides settings validation and updating.
 */
class settings
{
	/**
	 * Array with configuration settings.
	 * @private
	 */
	var $config;

	/**
	 * Holds errors resulting from validation.
	 */
	var $error;

	/**
	 * Constructor.
	 *
	 * Initializes settings instance given configuration settings array.
	 */
	function settings($config)
	{
		$this->set_config($config);
	}

	/**
	 * Updates configuration settings.
	 */
	function set_config($config)
	{
		$this->config = $config;
	}

	function get_config()
	{
		return $this->config;
	}

	function get_cache_dir()
	{
		global $quickinstall_path;
		if (empty($this->config['cache_dir']))
		{
			$cache_dir = $quickinstall_path . 'cache/';
		}
		else
		{
			$cache_dir = $this->config['cache_dir'];
		}
		return $cache_dir;
	}

	function get_boards_dir()
	{
		global $quickinstall_path;
		if (empty($this->config['boards_dir']))
		{
			$boards_dir = $quickinstall_path . 'boards/';
		}
		else
		{
			$boards_dir = $this->config['boards_dir'];
		}
		return $boards_dir;
	}

	function get_boards_url()
	{
		global $quickinstall_path;
		if (empty($this->config['boards_url']))
		{
			$boards_url = $quickinstall_path . 'boards/';
		}
		else
		{
			$boards_url = $this->config['boards_url'];
			/*
			if (!preg_match('|^\w+://|', $boards_url))
			{
				$boards_url = $quickinstall_path . $boards_url;
			}
			*/
		}
		return $boards_url;
	}

	/**
	 * Validates settings.
	 *
	 * If validation fails, the errors are available in $error property.
	 *
	 * Some nubs might edit the settings manually.
	 * We need to make sure they are ok.
	 *
	 * @return boolean
	 */
	function validate()
	{
		global $user, $quickinstall_path;

		// The config cannot be empty
		if (empty($this->config))
		{
			$this->error = $user->lang['CONFIG_EMPTY'];
			return false;
		}

		foreach ($this->config as &$value)
		{
			$value = htmlspecialchars_decode($value);
		}

		$this->config['no_dbpasswd'] = (empty($this->config['no_dbpasswd'])) ? 0 : 1;
		// Lets check the required settings...
		$error = '';
		$error .= ($this->config['dbms'] == '') ? $user->lang['DBMS'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
		$error .= ($this->config['dbhost'] == '') ? $user->lang['DBHOST'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
		$error .= ($this->config['dbuser'] == '') ? $user->lang['DBUSER'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
		$error .= ($this->config['dbpasswd'] == '' && !$this->config['no_dbpasswd']) ? $user->lang['DBPASSWD'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
		$error .= ($this->config['dbpasswd'] != '' && $this->config['no_dbpasswd']) ? $user->lang['NO_DBPASSWD_ERR'] . '<br />' : '';
		$error .= ($this->config['table_prefix'] == '') ? $user->lang['TABLE_PREFIX'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
		$error .= ($this->config['qi_lang'] == '') ? $user->lang['QI_LANG'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
		$error .= ($this->config['qi_tz'] == '') ? $user->lang['QI_TZ'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
		$error .= ($this->config['db_prefix'] == '') ? $user->lang['DB_PREFIX'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
		$error .= ($this->config['admin_name'] == '') ? $user->lang['ADMIN_NAME'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
		$error .= ($this->config['admin_pass'] == '') ? $user->lang['ADMIN_PASS'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
		$error .= ($this->config['admin_email'] == '') ? $user->lang['ADMIN_EMAIL'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
		$error .= ($this->config['site_name'] == '') ? $user->lang['SITE_NAME'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
		$error .= ($this->config['server_name'] == '') ? $user->lang['SERVER_NAME'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
		$error .= ($this->config['server_port'] == '') ? $user->lang['SERVER_PORT'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
		$error .= ($this->config['cookie_domain'] == '') ? $user->lang['COOKIE_DOMAIN'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
		$error .= ($this->config['board_email'] == '') ? $user->lang['BOARD_EMAIL'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';
		$error .= ($this->config['default_lang'] == '') ? $user->lang['DEFAULT_LANG'] . ' ' . $user->lang['REQUIRED'] . '<br />' : '';

		$error .= ($this->config['db_prefix'] != validate_dbname($this->config['db_prefix'], true)) ? $user->lang['DB_PREFIX'] . ' ' . $user->lang['IS_NOT_VALID'] . '<br />' : '';

		if ($this->config['cache_dir'] == '')
		{
			$error .= $user->lang['CACHE_DIR'] . ' ' . $user->lang['REQUIRED'] . '<br />';
		}
		else if (!file_exists($this->get_cache_dir()) || !is_writable($this->get_cache_dir()))
		{
			// The cache dir needs to both exist and be writeable.
			$cache_dir_error = sprintf($user->lang['CACHE_DIR_MISSING'], $this->get_cache_dir());
			$error .= $cache_dir_error . '<br />';
		}

		if ($this->config['boards_dir'] == '')
		{
			$error .= $user->lang['BOARDS_DIR'] . ' ' . $user->lang['REQUIRED'] . '<br />';
		}
		else if (!file_exists($this->get_boards_dir()) || !is_writable($this->get_boards_dir()))
		{
			// The boards dir needs to both exist and be writeable.
			$boards_dir_error = sprintf($user->lang['BOARDS_DIR_MISSING'], $this->get_boards_dir());
			$error .= $boards_dir_error . '<br />';
		}

		// SQLite needs a writable and existing directory
		if ($this->config['dbms'] == 'sqlite')
		{
			if (!file_exists($this->config['dbhost']) || !is_writable($this->config['dbhost']) || !is_dir($this->config['dbhost']))
			{
				$error .= $user->lang['SQLITE_PATH_MISSING'] . '<br />';
			}
			else
			{
				// Make sure the directory ends with a slash if we use SQLite
				$this->config['dbhost'] = (substr($this->config['dbhost'], -1) == '/') ? $this->config['dbhost'] : $this->config['dbhost'] . '/';
			}
		}

		if ($this->config['boards_url'] == '')
		{
			$error .= $user->lang['BOARDS_URL'] . ' ' . $user->lang['REQUIRED'] . '<br />';
		}

		$this->error = $error;
		return empty($error);
	}

	/**
	 * Adjusts configuration settings.
	 *
	 * Users can enter the same information in different ways.
	 * This function transforms settings to the canonical representation
	 * that the rest of the code expects.
	 */
	function adjust()
	{
		// Let's make sure our boards dir ends with a slash.
		$this->config['boards_dir'] = (substr($this->config['boards_dir'], -1) == '/') ? $this->config['boards_dir'] : $this->config['boards_dir'] . '/';
		$this->config['boards_url'] = (substr($this->config['boards_url'], -1) == '/') ? $this->config['boards_url'] : $this->config['boards_url'] . '/';
	}

	/**
	 * Serializes configuration settings into a string suitable for
	 * writing to the configuration file.
	 */
	function get_config_text()
	{
		$cfg_string = '';

		foreach ($this->config as $key => $value)
		{
			$cfg_string .= $key . '=' . $value . "\n";
		}

		return $cfg_string;
	}

	/**
	 * Writes configuration settings to the configuration file.
	 */
	function write($config_text)
	{
		global $quickinstall_path;

		return file_put_contents($quickinstall_path . 'qi_config.cfg', $config_text) !== false;
	}

	/**
	 * Updates settings.
	 *
	 * Adjusts settings to canonical representation and validates them.
	 * If validation passes writes serialized settings to the
	 * configuration file.
	 *
	 * @param array
	 * @return string, error
	 */
	function update()
	{
		$this->adjust();
		$this->config_text = $this->get_config_text();
		$this->apply_language();
		return $this->write($this->config_text);
	}

	/**
	 * Applies language selected by user to quickinstall.
	 */
	function apply_language()
	{
		global $quickinstall_path, $user;

		if (!empty($this->config['qi_lang']) && $this->config['qi_lang'] != $user->lang['USER_LANG'])
		{
			if (file_exists($quickinstall_path . 'language/' . $this->config['qi_lang']))
			{
				$user->lang = $this->config['qi_lang'];
				qi::add_lang(array('qi', 'phpbb'), $quickinstall_path . 'language/' . $this->config['qi_lang'] . '/');
			}
		}
	}
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

function db_connect()
{
	global $qi_config, $phpbb_root_path, $phpEx, $sql_db, $db;

	foreach (array('dbms', 'dbhost', 'dbuser', 'dbpasswd', 'dbport') as $var)
	{
		$$var = $qi_config[$var];
	}

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
}

?>