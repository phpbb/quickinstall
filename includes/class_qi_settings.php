<?php
/**
*
* @package quickinstall
* @copyright (c) 2011 phpBB Limited
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
 * Encapsulates quickinstall settings.
 * Provides settings validation and updating.
 */
class settings
{
	/**
	 * Cookie with the latest used profile name as payload.
	 */
	const QI_PROFILE_COOKIE = 'qi_profile';

	/**
	 * Array with configuration settings.
	 * @private
	 */
	var $config = array();

	/**
	 * Holds errors.
	 * Only language keys are stored so errors can be set before we have a user.
	 * $user->lang[$error_row] is up to the caller.
	 * If more than one language key is used they are separated with a | (vertical bar).
	 * Or if the first key contains a %, the sprintf args are separated with a |.
	 */
	var $error = array();

	/**
	 * Bool true if the settings need to be converted to the new style
	 * and that could not be done automatically
	 */
	var $manual_convert = false;

	/**
	 * Bool true if the settings where automatically converted to the new style
	 * and the user is not informed yet.
	 */
	var $is_converted = false;

	/**
	 * Array with info about updated config. Since QI v1.2.0
	 */
	var $update_text		= array();
	var $updated_profile	= '';

	/**
	 * True if there is no config and the user needs to go to install.
	 */
	var $install = false;

	/**
	 * The current profile
	 */
	var $profile = 'default';

	/**
	 * @var \phpbb\user
	 */
	protected $user;

	/**
	 * @var string QI root path
	 */
	protected $qi_path;

	/**
	 * @var string PHP extension
	 */
	protected $php_ext;

	/**
	 * Reads the settings and populates $this->config.
	 *
	 * @param string $profile
	 * @param string $mode
	 */
	function __construct($profile = '', $mode = '')
	{
		global $quickinstall_path, $phpEx;

		$this->qi_path = $quickinstall_path;
		$this->php_ext = $phpEx;

		$delete_profile = qi_request_var('delete-profile', false);

		if (!empty($delete_profile))
		{
			$profile = $this->delete_profile($profile);
		}

		if (!empty($profile) && is_readable("{$this->qi_path}settings/$profile.cfg"))
		{
			$config = file("{$this->qi_path}settings/$profile.cfg", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			$this->profile = $profile;
			$this->set_profile_cookie($profile);
		}
		else if (!empty($_COOKIE[self::QI_PROFILE_COOKIE]) && is_readable("{$this->qi_path}settings/{$_COOKIE[self::QI_PROFILE_COOKIE]}.cfg"))
		{
			// Get the previously used profile.
			$config = file("{$this->qi_path}settings/{$_COOKIE[self::QI_PROFILE_COOKIE]}.cfg", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			$this->profile = $_COOKIE[self::QI_PROFILE_COOKIE];
		}
		else
		{
			// The profile cookie is empty, not set or the profile file is not found or not readable.
			// Check if we have a settings directory.
			if (file_exists($this->qi_path . 'settings'))
			{
				// Read the directory and give the first file we get if there are any.
				$files = scandir($this->qi_path . 'settings');

				$cfg_file = '';
				foreach ($files as $file)
				{
					if ($file[0] === '.' || substr($file, -4) !== '.cfg' || !is_readable("{$this->qi_path}settings/$file"))
					{
						continue;
					}

					$cfg_file = "{$this->qi_path}settings/$file";
					$this->profile = str_replace('.cfg', '', $file);
					break;
				}

				if (!empty($cfg_file))
				{
					$config = file($cfg_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
				}
			}
		}

		if (empty($config) && is_readable($this->qi_path . 'qi_config.cfg'))
		{
			// Still no config, but the old style congfig file is available.
			$config = file($this->qi_path . 'qi_config.cfg');

			if (!empty($config))
			{
				// Send the config to be converted and stored if possible.
				// if manual_convert is true the config will be empty.
				$this->manual_convert = $this->convert($config);

				return;
			}
		}
		else if (!empty($config))
		{
			// The config array needs to be converted to an associative array.
			$this->set_config_array($config);
			$this->config = $this->check_updates($this->config);

			if (!empty($this->update_text))
			{
				$this->update();
			}

			return;
		}

		// If we reach this point there is no config.
		// Most likely a new user so load the default settings and go to install.
		if (!function_exists('get_default_settings'))
		{
			include("{$this->qi_path}includes/default_settings.{$this->php_ext}");
		}

		$this->config = get_default_settings();
		$this->install = ($mode != 'update_settings') ? true : false;
	}

	/**
	 * Checks for changed or added config fields.
	 * Newer changes on top so we don't have to step through all each time.
	 */
	protected function check_updates($config)
	{
		if (!is_numeric($config['qi_tz']))
		{
			return $config;
		}

		/**
		 * Move this part to the first check when such is added.
		 *
		 * From here
		 */
		if (!function_exists('get_default_settings'))
		{
			include("{$this->qi_path}includes/default_settings.{$this->php_ext}");
		}
		$new_config = get_default_settings();
		$this->updated_profile = $this->profile;
		/**
		 * To here
		 */

		$config['qi_tz'] = $new_config['qi_tz'];
		unset($config['qi_dst']);

		$this->update_text[] = 'TIMEZONE_UPDATED';
		$this->update_text[] = 'DST_REMOVED';

		return $config;
	}

	/**
	 * Updates users all profiles if there are more than one.
	 */
	public function update_profiles()
	{
		$this->get_user();

		$cfg_bak	= $this->config;
		$profil_bak	= $this->profile;
		$path		= $this->qi_path . 'settings';
		$dh			= opendir($path);
		$update_msg	= '';

		while (($file = readdir($dh)) !== false)
		{
			if (empty($file) || $file[0] === '.' || substr($file, -4) !== '.cfg' || !is_readable("$path/$file"))
			{
				continue;
			}

			$config = file("$path/$file", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			$this->profile = substr($file, 0, -4);
			$this->set_config_array($config);
			$this->config = $this->check_updates($this->config);

			if (!empty($this->update_text))
			{
				$this->update();
				$this->update_text = array();
				$update_msg .= '<li>' . $this->profile . '</li>';
			}
		}
		closedir($dh);

		$this->config	= $cfg_bak;
		$this->profile	= $profil_bak;

		if (!empty($update_msg))
		{
			$update_msg = "<ul>$update_msg</ul>";

			gen_error_msg($update_msg, $this->user->lang['PROFILES_UPDATED']);
		}
	}

	/**
	 * Adjusts configuration settings.
	 *
	 * Users can enter the same information in different ways.
	 * This function transforms settings to the canonical representation
	 * that the rest of the code expects.
	 */
	protected function adjust()
	{
		// Let's make sure our boards dir ends with a slash.
		$this->config['boards_dir'] = (substr($this->config['boards_dir'], -1) === '/') ? $this->config['boards_dir'] : $this->config['boards_dir'] . '/';
		$this->config['boards_url'] = (substr($this->config['boards_url'], -1) === '/') ? $this->config['boards_url'] : $this->config['boards_url'] . '/';
	}

	/**
	 * Applies language selected by user to quickinstall.
	 */
	public function apply_language($lang = '')
	{
		if ($this->get_user() === null)
		{
			return;
		}

		$lang = (empty($lang)) ? $this->config['qi_lang'] : $lang;
		$lang = (file_exists("{$this->qi_path}language/$lang")) ? $lang : 'en';

		if (!empty($this->user))
		{
			$this->user->lang = (file_exists("{$this->qi_path}language/$lang")) ? $lang : 'en';
		}

		// Need to make sure 'en' exists too.
		if (file_exists("{$this->qi_path}language/$lang"))
		{
			qi::add_lang(array('qi', 'phpbb'), "{$this->qi_path}language/$lang/");
		}
		else
		{
			trigger_error('Neither your selected language or English found. Make sure that you have at least the English language files in QI_PATH/language/', E_USER_ERROR);
		}
	}

	/**
	 * Converts a old style "qi_config.cfg" config to the new profiles.
	 * Tries to write settings/main.cfg and set the QI profile cookie.
	 * If the file writing fails the cookie is not set.
	 * The new and old cfg file syntax is the same.
	 *
	 * @param array $config from qi_config.cfg
	 * @return true if the profile file could not be written or false for no errors.
	 */
	protected function convert($config)
	{
		// First convert the numeric array to a associative array and get it into $this->config.
		$this->set_config_array($config);

		// check for config fields added or changed since the "old" style.
		include("{$this->qi_path}includes/default_settings.{$this->php_ext}");
		$new_config = get_default_settings();

		foreach ($new_config as $key => $value)
		{
			if (empty($this->config[$key]))
			{
				$this->config[$key] = $value;
			}
			else if (gettype($this->config[$key]) !== gettype($value))
			{
				$this->config[$key] = $value;
			}
		}

		// The config array needs to be converted to a string.
		$this->profile = 'default';
		if ($this->update() !== false)
		{
			$this->set_profile_cookie($this->profile);
			$this->is_converted = true;
			$this->error[] = 'CONFIG_CONVERTED';
			return false;
		}

		return true;
	}

	protected function delete_profile($profile)
	{
		// First scan the existing profiles to find one to replace the deleted profile.
		if (file_exists($this->qi_path . 'settings'))
		{
			// Read the directory and give the first file we get if, there are any.
			$files = scandir($this->qi_path . 'settings');

			$cfg_file = '';
			foreach ($files as $file)
			{
				if ($file[0] === '.' || substr($file, -4) !== '.cfg' || !is_readable("{$this->qi_path}settings/$file") || $file == "$profile.cfg")
				{
					continue;
				}

				$cfg_file = "{$this->qi_path}settings/$file";
				$this->profile = str_replace('.cfg', '', $file);
				break;
			}

			if (!empty($cfg_file))
			{
				@unlink("{$this->qi_path}settings/$profile.cfg");
				return $this->profile;
			}

			$this->error[] = 'CANNOT_DELETE_LAST_PROFILE';
		}
		else
		{
			$this->error[] = 'SETTINGS_NOT_WRITABLE';
		}

		return $profile;
	}

	public function get_boards_dir()
	{
		return empty($this->config['boards_dir']) ? $this->qi_path . 'boards/' : $this->config['boards_dir'];
	}

	public function get_boards_url()
	{
		return empty($this->config['boards_url']) ? $this->qi_path . 'boards/' : $this->config['boards_url'];
	}

	public function get_cache_dir()
	{
		return empty($this->config['cache_dir']) ? $this->qi_path . 'cache/' : $this->config['cache_dir'];
	}

	/**
	 * Get a config setting or request a post/get var
	 *
	 * @param string $name, config/var name.
	 * @param mixed $default, 0 (zero) or '' to tell what to cast it to.
	 */
	public function get_config($name, $default = '', $multibyte = false, $cookie = false)
	{
		// First check if we have a post/get var, or a cookie if that has been selected.
		if ($cookie)
		{
			if (is_string($default))
			{
				// Using isset() on strings might give a undesired result.
				$exist = (!empty($_REQUEST[$name])) ? true : false;
			}
			else
			{
				$exist = (isset($_REQUEST[$name])) ? true : false;
			}
		}
		else
		{
			if (is_string($default))
			{
				$exist = (!empty($_GET[$name]) || !empty($_POST[$name])) ? true : false;
			}
			else
			{
				$exist = (isset($_GET[$name]) || isset($_POST[$name])) ? true : false;
			}
		}

		if ($exist)
		{
			$return = qi_request_var($name, $default, $multibyte, $cookie);
		}
		else
		{
			// Nothing from request_var. Do we have a config setting?
			if (!empty($this->config[$name]))
			{
				// Make sure to cast the config value to the same type as $default.
				if (is_int($default))
				{
					$return = (int) $this->config[$name];
				}
				else if (is_bool($default))
				{
					$return = (bool) $this->config[$name];
				}
				else if (is_string($default))
				{
					$return = (string) $this->config[$name];
				}
				else if (is_float($default))
				{
					$return = (float) $this->config[$name];
				}
				else
				{
					// Something went wrong.
					$return = $default;
				}
			}
			else
			{
				$return = $default;
			}
		}

		return $return;
	}

	/**
	 * Serializes configuration settings into a string suitable for
	 * writing to the configuration file.
	 */
	public function get_config_text()
	{
		$cfg_string = '';

		// I need this for the current bug hunting.
		// I'll try to remove it before I push anything.
		ksort($this->config);

		foreach ($this->config as $key => $value)
		{
			$cfg_string .= $key . '=' . $value . "\n";
		}

		return $cfg_string;
	}

	/**
	 * Get the vars needed to connect to the DB.
	 *
	 * @return array with DB connect data.
	 */
	public function get_db_data()
	{
		/**
		 * The order in this array is important, don't change it.
		 * The callers uses list() to set its DB vars.
		 * list() only works with numerical arrays.
		 */
		$db_vars = array(
			$this->get_config('dbms'),
			$this->get_config('dbhost'),
			$this->get_config('dbuser'),
			$this->get_config('dbpasswd'),
			$this->get_config('dbport'),
		);

		return $db_vars;
	}

	/**
	 * Returns an array containing translated errors, or false for no error.
	 */
	public function get_errors()
	{
		$this->get_user();

		if (empty($this->error))
		{
			// Yay, no errors.
			return false;
		}

		$errors = [];
		foreach ($this->error as $row)
		{
			if (strpos($row, '|') === false)
			{
				// Simple only one language key.
				$errors[] = $this->user->lang[$row];
			}
			else
			{
				// More than one language key.
				$err_ary = explode('|', $row);
				$format  = $this->user->lang[$err_ary[0]];

				if (strpos($format, '%') === false)
				{
					// No formatting, just pack them together.
					foreach ($err_ary as &$err_row)
					{
						$err_row = $this->user->lang[$err_row];
					}
					unset($err_row);

					$errors[] = implode(' ', $err_ary);
				}
				else if (count($err_ary) > 1)
				{
					// Formated language string.
					unset($err_ary[0]);
					$errors[] = vsprintf($format, $err_ary);
				}
				else
				{
					$errors[] = $this->user->lang[$err_ary[0]];
				}
			}
		}

		// Empty the errors.
		$this->error = [];
		return $errors;
	}

	/**
	 * Generate a lang select for the settings page.
	 */
	public function get_lang_select($lang_path, $config_var, $get_var = '')
	{
		// Make sure $source_path ends with a slash.
		$lang_path .= (substr($lang_path, -1) !== '/') ? '/' : '';

		// Need to assume that English always is available.
		if ($get_var && !empty($_GET[$get_var]))
		{
			$lang = qi_request_var($get_var, '');
			$user_lang = ($lang && file_exists($lang_path . $lang)) ? $lang : 'en';
		}
		else
		{
			$user_lang = $this->get_config($config_var, 'en');
			$user_lang = (file_exists($lang_path . $user_lang)) ? $user_lang : 'en';
		}

		$lang_arr = scandir($lang_path);
		$lang_options = [];

		foreach ($lang_arr as $lang)
		{
			if ($lang[0] === '.' || !is_dir($lang_path . $lang))
			{
				continue;
			}

			$file = "$lang_path/$lang/iso.txt";

			if (file_exists($file))
			{
				$rows = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

				// Always show the English language name, except for the "active" language.
				$lang_options[] = [
					'name' => ($lang === $user_lang) ? $rows[1] : $rows[0],
					'value' => $lang,
					'selected' => $lang === $user_lang,
				];
			}
		}

		return $lang_options;
	}

	public function get_other_config($array = false)
	{
		$other_config = qi_request_var('other_config', '');

		if (empty($other_config))
		{
			if (empty($this->config['other_config']))
			{
				return '';
			}

			$other_config = unserialize($this->config['other_config']);
			$other_config = (!$array) ? implode("\n", $other_config) : $other_config;
		}
		else if ($array)
		{
			$other_config = explode("\n", $other_config);
		}

		return $other_config;
	}

	/**
	 * Scans the settings directory and return options for a profile select.
	 */
	public function get_profiles()
	{
		static $profiles;

		if ($profiles !== null)
		{
			return $profiles;
		}

		if (file_exists($this->qi_path . 'settings'))
		{
			// Read the directory and give the first file we get if there are any.
			$files = scandir($this->qi_path . 'settings');

			$profiles = [];

			if (is_array($files))
			{
				natcasesort($files);
			}

			foreach ($files as $file)
			{
				if (strpos($file, '.') == 0 || substr($file, -4) !== '.cfg' || !is_readable("{$this->qi_path}settings/$file"))
				{
					continue;
				}

				$cfg_name = str_replace('.cfg', '', $file);
				$profiles[$cfg_name] = $cfg_name === $this->profile;
			}

			return $profiles;
		}

		$this->error[] = 'SETTINGS_NOT_WRITABLE';
		return false;
	}

	/**
	 * There is no setting for server_protocol ATM,
	 * but there might be in the future so let's keep this for now.
	 */
	public function get_server_protocol()
	{
		/*
		$server_protocol = (!empty($this->config['server_protocol'])) ? $this->config['server_protocol'] : 'http://';
		return $server_protocol;
		*/

		return 'http://';
	}

	/**
	 * Updates configuration settings.
	 */
	public function set_config($config)
	{
		$profile = qi_request_var('save_profile', '');

		if (!empty($profile))
		{
			$profile = str_replace(' ', '_', $profile);
			$profile = preg_replace('/[^A-Za-z0-9_.\-]*/', '', $profile);
			$this->profile = $profile;
		}

		$profile = (!empty($profile)) ? $profile : $this->profile;
		$this->config = $config;
		return $profile;
	}

	/**
	 * Receives a numerical array from a config file and converts it to a associative array
	 * and sets $this->config.
	 *
	 * @param array $config, a numerical array directly from the config or profile file.
	 * @return void
	 */
	protected function set_config_array($config)
	{
		if (empty($config) || !is_array($config))
		{
			$this->config = array();
			return;
		}

		$qi_config = array();

		// Let's split the config.
		foreach ($config as $row)
		{
			$row = trim($row);
			if (empty($row))
			{
				continue;
			}

			// Someone might have edited the settings manually so make sure there is no leading or trailing white-space.
			$cfg_row	= explode('=', $row);
			$cfg_row[0]	= trim($cfg_row[0]);

			// This should never happen unless the config was manually edited.
			if (empty($cfg_row[0]))
			{
				continue;
			}

			$key = $cfg_row[0];

			// Handle config values containing a = char.
			if (count($cfg_row) > 2)
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
		if (!file_exists("{$this->qi_path}language/{$qi_config['qi_lang']}/qi.{$this->php_ext}"))
		{
			// Assume English exists.
			$qi_config['qi_lang'] = 'en';
		}

		$this->config = $qi_config;
	}

	/**
	 * Sets the profile cookie with a profile name.
	 *
	 * @param string $profile, profile name.
	 */
	public function set_profile_cookie($profile = 'default')
	{
		// A Julian year == 365.25 days * 86,400 seconds
		$expire_time = time() + 31557600;
		setcookie(self::QI_PROFILE_COOKIE, $profile, $expire_time);
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
	public function update()
	{
		$this->adjust();
		$this->config_text = $this->get_config_text();
		$this->apply_language();
		return $this->write($this->config_text);
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
	public function validate()
	{
		// The config cannot be empty
		if (empty($this->config))
		{
			$this->error[] = 'CONFIG_EMPTY';
			return false;
		}

		foreach ($this->config as &$value)
		{
			$value = htmlspecialchars_decode($value);
		}
		unset($value);

		$this->config['no_dbpasswd'] = (empty($this->config['no_dbpasswd'])) ? 0 : 1;
		// Lets check the required settings...
		$error = array();
		$error[] = ($this->config['dbms'] == '') ? 'DBMS|IS_REQUIRED' : '';
		$error[] = ($this->config['dbhost'] == '') ? 'DBHOST|IS_REQUIRED' : '';
		$error[] = ($this->config['dbpasswd'] != '' && $this->config['no_dbpasswd']) ? 'NO_DBPASSWD_ERR' : '';
		$error[] = ($this->config['table_prefix'] == '') ? 'TABLE_PREFIX|IS_REQUIRED' : '';
		$error[] = ($this->config['qi_lang'] == '') ? 'QI_LANG|IS_REQUIRED' : '';
		$error[] = ($this->config['qi_tz'] == '') ? 'QI_TZ|IS_REQUIRED' : '';
		$error[] = ($this->config['db_prefix'] == '') ? 'DB_PREFIX|IS_REQUIRED' : '';
		$error[] = ($this->config['admin_email'] == '') ? 'ADMIN_EMAIL|IS_REQUIRED' : '';
		$error[] = ($this->config['site_name'] == '') ? 'SITE_NAME|IS_REQUIRED' : '';
		$error[] = ($this->config['server_name'] == '') ? 'SERVER_NAME|IS_REQUIRED' : '';
		$error[] = ($this->config['server_port'] == '') ? 'SERVER_PORT|IS_REQUIRED' : '';
		$error[] = ($this->config['board_email'] == '') ? 'BOARD_EMAIL|IS_REQUIRED' : '';
		$error[] = ($this->config['default_lang'] == '') ? 'DEFAULT_LANG|IS_REQUIRED' : '';

		$error[] = ($this->config['db_prefix'] != validate_dbname($this->config['db_prefix'], true)) ? 'DB_PREFIX|IS_NOT_VALID' : '';

		if ($this->config['cache_dir'] == '')
		{
			$error[] = 'CACHE_DIR|IS_REQUIRED';
		}
		else if (!file_exists($this->get_cache_dir()) || !is_writable($this->get_cache_dir()))
		{
			// The cache dir needs to both exist and be writeable.
			$error[] = 'CACHE_DIR_MISSING|' . $this->get_cache_dir();
		}
		else
		{
			$this->config['cache_dir'] .= (substr($this->config['cache_dir'], -1) !== '/') ? '/' : '';
		}

		if ($this->config['boards_dir'] == '')
		{
			$error[] = 'BOARDS_DIR|IS_REQUIRED';
		}
		else if (!file_exists($this->get_boards_dir()) || !is_writable($this->get_boards_dir()))
		{
			// The boards dir needs to both exist and be writeable.
			$error[] = 'BOARDS_DIR_MISSING|' . $this->get_boards_dir();
		}
		else
		{
			$this->config['boards_dir'] .= (substr($this->config['boards_dir'], -1) !== '/') ? '/' : '';
		}

		// SQLite needs a writable and existing directory
		if (in_array($this->config['dbms'], array('sqlite', 'sqlite3')))
		{
			if (!file_exists($this->config['dbhost']) || !is_writable($this->config['dbhost']) || !is_dir($this->config['dbhost']))
			{
				$error[] = 'SQLITE_PATH_MISSING';
			}
			else
			{
				// Make sure the directory ends with a slash if we use SQLite
				$this->config['dbhost'] = (substr($this->config['dbhost'], -1) === '/') ? $this->config['dbhost'] : $this->config['dbhost'] . '/';
			}
		}

		if ($this->config['boards_url'] == '')
		{
			$error[] = 'BOARDS_URL|IS_REQUIRED';
		}
		else
		{
			$this->config['boards_url'] .= (substr($this->config['boards_url'], -1) !== '/') ? '/' : '';
		}

		foreach ($error as $key => $value)
		{
			if (empty($value))
			{
				unset($error[$key]);
			}
		}

		if (empty($error))
		{
			return true;
		}

		$this->error = array_merge($this->error, $error);

		return false;
	}

	/**
	 * Writes configuration settings to the configuration file.
	 */
	protected function write($config_text)
	{
		$profile = $this->profile;

		$profile_file = "{$this->qi_path}settings/$profile.cfg";

		if ((file_exists($profile_file) && !is_writable($profile_file)) || !is_writable("{$this->qi_path}settings/"))
		{
			return false;
		}

		$res = file_put_contents($profile_file, $config_text);

		if ($res !== false)
		{
			// Make sure install is false when the settings have been successfully saved.
			$this->install = false;
		}

		return $res;
	}

	/**
	 * Get the global user object. Should be called whenever the user is needed,
	 * since in this procedural code base, it doesn't exist when this class is
	 * instantiated but could exist later on when it's member methods are called.
	 * This is gross!
	 *
	 * @return \phpbb\user
	 */
	protected function get_user()
	{
		if ($this->user === null)
		{
			global $user;
			$this->user = $user;
		}

		return $this->user;
	}
}
