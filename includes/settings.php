<?php
/**
 *
 * @package quickinstall
 * @copyright (c) 2007, 2020 phpBB Limited
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

class settings
{
	/** @var string The QI settings profile */
	protected $profile = 'default';

	/** @var array An array containing the QI settings */
	protected $settings = [];

	/** @var array An array of errors (as language keys) that occur */
	protected $errors = [];

	/** @var bool True if there are no settings and the user needs to go to install */
	protected $install = false;

	/** @var \phpbb\user A phpBB user object */
	protected $user;

	/** @var string Path to QI root */
	protected $qi_path;

	/**
	 * Settings constructor
	 *
	 * @param string $quickinstall_path Path to QI root
	 */
	public function __construct($quickinstall_path)
	{
		$this->qi_path = $quickinstall_path;
	}

	/**
	 * Import a settings profile into the settings property.
	 *
	 * @param string $profile The name of a settings profile
	 * @param bool   $cookie  Are we loading a profile from a cookie?
	 * @return bool Returns true on successful import, false on failure.
	 */
	public function import_profile($profile = '', $cookie = false)
	{
		// Load requested profile into settings property
		if ($profile !== '')
		{
			$settings = $this->load_profile("{$this->qi_path}settings/$profile.json");

			if (empty($settings))
			{
				return false;
			}

			$this->settings = $settings;
			$this->profile = $profile;
			if (!$cookie)
			{
				$this->set_profile_cookie($profile);
			}

			return true;
		}

		// No profile, let's try to load recursively from a cookie
		if (!$cookie && ($profile = $this->get_profile_cookie()) !== '' && $this->import_profile($profile, true))
		{
			return true;
		}

		// No cookie, lets try to load recursively the first of any available profiles
		if ((($profile = $this->find_first_profile()) !== '') && $this->import_profile($profile))
		{
			return true;
		}

		// Still no profile found. Maybe we have old .cfg files to update?
		if ($this->convert_cfg_to_json() && $this->import_profile())
		{
			return true;
		}

		// If we reach this point there are no profiles.
		// Most likely a new user so load the default settings and go to install.
		$this->settings = $this->load_profile("{$this->qi_path}includes/default_settings.json");
		$this->install  = qi_request_var('mode', '') !== 'update_settings';

		return true;
	}

	/**
	 * Load a profile settings document
	 *
	 * @param string $profile Name of a settings profile JSON document
	 * @return array|false Return array of settings, or false if something went wrong
	 */
	protected function load_profile($profile)
	{
		return json_decode(@file_get_contents($profile), true);
	}

	/**
	 * Get the name of the first profile found in the settings directory
	 *
	 * @return string Name of found settings profile, or empty
	 */
	protected function find_first_profile()
	{
		return (($profiles = $this->get_profiles()) !== false) ? array_keys($profiles)[0] : '';
	}

	/**
	 * Validates settings.
	 * If validation fails, the errors are available in $errors property.
	 *
	 * @return bool True if valid, false if any validation failed.
	 */
	public function validate()
	{
		if (empty($this->settings))
		{
			$this->errors[] = 'CONFIG_EMPTY';
			return false;
		}

		$this->settings = array_map('htmlspecialchars_decode', $this->settings);

		$validation_errors = [];

		// Lets check simple required string settings...
		foreach (['cache_dir', 'boards_dir', 'boards_url', 'dbms', 'dbhost', 'table_prefix', 'qi_lang', 'qi_tz', 'db_prefix', 'admin_email', 'site_name', 'server_name', 'server_port', 'board_email', 'default_lang'] as $setting)
		{
			if ($this->settings[$setting] === '')
			{
				$validation_errors[] = ['IS_REQUIRED', strtoupper($setting)];
			}
		}

		// Validate database password setting
		if ($this->settings['dbpasswd'] !== '' && $this->settings['no_dbpasswd'])
		{
			$validation_errors[] = 'NO_DBPASSWD_ERR';
		}

		// Validate database prefix
		if ($this->settings['db_prefix'] !== validate_dbname($this->settings['db_prefix'], true))
		{
			$validation_errors[] = ['IS_NOT_VALID', 'DB_PREFIX'];
		}

		// Validate cache directory
		file_functions::append_slash($this->settings['cache_dir']);
		if (!file_exists($this->get_cache_dir()) || !is_writable($this->get_cache_dir()))
		{
			$validation_errors[] = ['CACHE_DIR_MISSING', $this->get_cache_dir()];
		}

		// Validate boards directory
		file_functions::append_slash($this->settings['boards_dir']);
		if (!file_exists($this->get_boards_dir()) || !is_writable($this->get_boards_dir()))
		{
			$validation_errors[] = ['BOARDS_DIR_MISSING', $this->get_boards_dir()];
		}

		// Adjust boards URL path
		file_functions::append_slash($this->settings['boards_url']);

		// SQLite needs a writable and existing directory
		if (in_array($this->settings['dbms'], ['sqlite', 'sqlite3']))
		{
			file_functions::append_slash($this->settings['dbhost']);
			if (!file_exists($this->settings['dbhost']) || !is_writable($this->settings['dbhost']) || !is_dir($this->settings['dbhost']))
			{
				$validation_errors[] = 'SQLITE_PATH_MISSING';
			}
		}

		$this->errors = array_merge($this->errors, $validation_errors);

		return empty($validation_errors);
	}

	/**
	 * Save a profile
	 *
	 * @param string $profile Name of profile
	 * @param array  $settings The settings data array
	 * @return bool True or false if profile was saved
	 */
	public function save_profile($profile = '', $settings = [])
	{
		$profile  = $profile  !== '' ? $profile  : $this->profile;
		$settings = $settings !== [] ? $settings : $this->settings;

		$profile_file = "{$this->qi_path}settings/{$profile}.json";

		$saved = file_functions::make_file($profile_file, $this->encode_settings($settings));

		// Make install false if settings have been successfully saved.
		if ($saved !== false)
		{
			$this->install = false;
		}

		return $saved;
	}

	/**
	 * Delete a profile
	 *
	 * @param string $profile Name of profile
	 * @param string $ext     The file extension (default is json)
	 */
	public function delete_profile($profile, $ext = 'json')
	{
		file_functions::delete_file("{$this->qi_path}settings/$profile.$ext");
	}

	/**
	 * Get the name of the current profile
	 *
	 * @return string
	 */
	public function get_profile()
	{
		return $this->profile;
	}

	/**
	 * Scans the settings directory and returns an array all setting profiles,
	 * typically for use in select menus.
	 * [
	 *   'profileName' => true/false is the current/selected profile
	 * ]
	 *
	 * @return array|false Array of profiles or false if nothing found.
	 */
	public function get_profiles()
	{
		if (($files = $this->find_profiles()) !== false)
		{
			$profiles = [];

			foreach ($files as $file)
			{
				$cfg_name = pathinfo($file, PATHINFO_FILENAME);
				$profiles[$cfg_name] = $cfg_name === $this->profile;
			}

			return $profiles;
		}

		return false;
	}

	/**
	 * Scan settings directory for files
	 *
	 * @param string $type The file type by extension, default is json.
	 * @return array|false Array of files found or false if nothing found.
	 */
	protected function find_profiles($type = 'json')
	{
		if (($files = scandir("{$this->qi_path}settings")) !== false)
		{
			foreach ($files as $key => $file)
			{
				if ($file[0] === '.' || pathinfo($file, PATHINFO_EXTENSION) !== $type || !is_readable("{$this->qi_path}settings/$file"))
				{
					unset($files[$key]);
				}
			}

			sort($files, SORT_NATURAL | SORT_FLAG_CASE);
		}

		return !empty($files) ? $files : false;
	}

	/**
	 * Set a cookie storing a profile name
	 *
	 * @param string $value The name of a profile
	 */
	public function set_profile_cookie($value)
	{
		$time = $value === '' ? '-1 year' : '+1 year';
		setcookie('qi_profile', $value, strtotime($time));
	}

	/**
	 * Get a cookie holding a profile name
	 *
	 * @return string The name of a profile
	 */
	public function get_profile_cookie()
	{
		return isset($_COOKIE['qi_profile']) ? $_COOKIE['qi_profile'] : '';
	}

	/**
	 * Convert older .cfg profiles to .json
	 *
	 * @return bool True if profiles were converted, false if nothing happened.
	 */
	public function convert_cfg_to_json()
	{
		if (($cfg_files = $this->find_profiles('cfg')) === false)
		{
			return false;
		}

		foreach ($cfg_files as $file)
		{
			$data = file("{$this->qi_path}settings/$file", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			$settings = [];

			foreach ($data as $row)
			{
				if (($row = trim($row)) === '')
				{
					continue;
				}

				$parts = explode('=', $row, 2);

				if (($key = trim($parts[0])) === '')
				{
					continue;
				}

				if ($key === 'other_config')
				{
					$parts[1] = implode("\n", unserialize($parts[1]));
				}

				$settings[$key] = isset($parts[1]) ? trim($parts[1]) : '';
			}

			$profile = pathinfo($file, PATHINFO_FILENAME);
			if ($this->save_profile($profile, $settings))
			{
				$this->delete_profile($profile, 'cfg');
			}
		}

		return true;
	}

	/**
	 * Get a config setting or request a post/get var
	 *
	 * @param string $name      config/var name.
	 * @param mixed  $default   Default value and type cast, 0 or ''
	 * @param bool   $multibyte Allow multibyte strings?
	 * @return mixed The config value requested, or the default value if not found
	 */
	public function get_config($name, $default = '', $multibyte = false)
	{
		// First check if we have a post/get var
		if (is_string($default))
		{
			$request = !empty($_GET[$name]) || !empty($_POST[$name]);
		}
		else
		{
			$request = isset($_GET[$name]) || isset($_POST[$name]);
		}

		if ($request)
		{
			return qi_request_var($name, $default, $multibyte);
		}

		// Nothing from post/get. Do we have a config setting?
		if (!empty($this->settings[$name]))
		{
			$type = gettype($default);
			if (in_array($type, ['string', 'integer', 'double', 'boolean']))
			{
				$setting = $this->settings[$name];
				settype($setting, $type);
				return $setting;
			}
		}

		return $default;
	}

	/**
	 * Update the settings property with new values
	 *
	 * @param array $data An array of settings
	 * @return string The profile name (new profile if one was saved, otherwise current profile)
	 */
	public function set_settings($data)
	{
		$this->settings = $data;

		$profile = qi_request_var('save_profile', '');

		if ($profile !== '')
		{
			// Replace/remove illegal characters
			$this->profile = preg_replace(['/\s+/', '/[^A-Za-z0-9_.\-]*/'], ['_', ''], $profile);
		}

		return $this->profile;
	}

	/**
	 * Get the boards directory path
	 *
	 * @return string Board directory path from settings, otherwise QI's default
	 */
	public function get_boards_dir()
	{
		return empty($this->settings['boards_dir']) ? "{$this->qi_path}boards/" : $this->settings['boards_dir'];
	}

	/**
	 * Get the boards URL path
	 *
	 * @return string Board URL path from settings, otherwise QI's default
	 */
	public function get_boards_url()
	{
		return empty($this->settings['boards_url']) ? "{$this->qi_path}boards/" : $this->settings['boards_url'];
	}

	/**
	 * Get the cache directory path
	 *
	 * @return string Cache directory path from settings, otherwise QI's default
	 */
	public function get_cache_dir()
	{
		return empty($this->settings['cache_dir']) ? "{$this->qi_path}cache/" : $this->settings['cache_dir'];
	}

	/**
	 * Get the server protocol
	 *
	 * @return string Server protocol from settings, otherwise QI's default
	 */
	public function get_server_protocol()
	{
		//There is no setting for server_protocol ATM, but there might be in the future so let's keep this for now.
		return empty($this->settings['server_protocol']) ? 'http://' : $this->settings['server_protocol'];
	}

	/**
	 * Get the database connection settings
	 *
	 * @return array Database connection settings
	 */
	public function get_db_data()
	{
		// The order in this array is important, don't change it.
		// The caller uses list() to set DB vars. list() only works with numerical arrays.
		return [
			$this->get_config('dbms'),
			$this->get_config('dbhost'),
			$this->get_config('dbuser'),
			$this->get_config('dbpasswd'),
			$this->get_config('dbport'),
		];
	}

	/**
	 * Get the current settings in JSON format
	 *
	 * @return false|string
	 */
	public function get_config_text()
	{
		return $this->encode_settings($this->settings);
	}

	/**
	 * Translate errors
	 *
	 * @return array An array containing translated errors, or empty for no error.
	 */
	public function get_errors()
	{
		$errors = [];

		if (count($this->errors))
		{
			$user = $this->get_user();
			foreach ($this->errors as $error)
			{
				if (is_array($error))
				{
					$key = array_shift($error);
					$errors[] = vsprintf($user->lang[$key], array_map(function($i) use($user) {
						return isset($user->lang[$i]) ? $user->lang[$i] : $i;
					}, $error));
				}
				else
				{
					$errors[] = $user->lang[$error];
				}
			}

			$this->errors = []; // Empty the errors.
		}

		return $errors;
	}

	/**
	 * Convert settings array into pretty JSON
	 *
	 * @param array $settings An array of settings data
	 * @return false|string   The JSON data string, or false if there was some error
	 */
	protected function encode_settings($settings)
	{
		return json_encode($settings, JSON_PRETTY_PRINT);
	}

	/**
	 * @return bool
	 */
	public function is_install()
	{
		return $this->install;
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
