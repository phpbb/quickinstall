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
 * Class with functions usefull for qi. Some stuff is from the install functions, this class is to be used statically.
 */
class qi
{
	/**
	* Output the standard page header
	*/
	public static function page_header($page_title = '')
	{
		if (defined('HEADER_INC'))
		{
			return;
		}

		define('HEADER_INC', true);
		global $template;

		$update = self::get_update();

		$template->assign_vars(array(
			'PAGE_TITLE'	=> self::lang($page_title),
			'T_THEME_PATH'	=> 'style',

			'U_DOCS'		=> self::url('docs'),
			'U_MANAGE'		=> self::url('manage'),
			'U_MAIN'		=> self::url('main'),
			'U_PHPINFO'		=> self::url('phpinfo'),
			'U_SETTINGS'	=> self::url('settings'),

			'S_CONTENT_DIRECTION'	=> self::lang('DIRECTION'),
			'S_USER_LANG'			=> self::lang('USER_LANG'),

			'TRANSLATION_INFO'	=> self::lang('TRANSLATION_INFO'),
			'QI_VERSION'		=> self::current_version(),

			'VERSION_CHECK_TITLE'	=> !empty($update) ? self::lang('VERSION_CHECK_TITLE', $update['current'], self::current_version()) : '',
			'VERSION_CHECK_CURRENT'	=> !empty($update) ? $update['current'] : '',
			'U_VERSION_CHECK_URL'	=> !empty($update) ? $update['download'] : '',
		));

		header('Content-type: text/html; charset=UTF-8');
		header('Cache-Control: private, no-cache="set-cookie"');
		header('Expires: 0');
		header('Pragma: no-cache');

		return;
	}

	/**
	* Output the standard page footer
	*/
	public static function page_display($filename)
	{
		global $db, $template;

		$template->display($filename);

		// Close our DB connection.
		if (!empty($db) && is_object($db))
		{
			$db->sql_close();
		}

		exit;
	}

	/**
	* Generate an HTTP/1.1 header to redirect the user to another page
	* This is used during the installation when we do not have a database available to call the normal redirect function
	* @param string $page The page to redirect to relative to the qi root path
	*/
	public static function redirect($page)
	{
		if (strpos($page, 'http://') === 0 || strpos($page, 'https://') === 0)
		{
			// Assume we have a fully qualified URL. And we are done.
			header('Location: ' . $page);
			exit;
		}

		$server_name = (!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : getenv('SERVER_NAME');
		$server_port = (!empty($_SERVER['SERVER_PORT'])) ? (int) $_SERVER['SERVER_PORT'] : (int) getenv('SERVER_PORT');
		$secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

		$script_name = (!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : getenv('PHP_SELF');
		if (!$script_name)
		{
			$script_name = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');
		}

		// Replace backslashes and doubled slashes (could happen on some proxy setups)
		$script_name = str_replace(array('\\', '//'), '/', $script_name);
		$script_path = trim(dirname($script_name));

		$url = ($secure ? 'https://' : 'http://') . $server_name;

		if ($server_port && (($secure && $server_port !== 443) || (!$secure && $server_port !== 80)))
		{
			$url .= ':' . $server_port;
		}

		// Make sure script path ends with a slash.
		$script_path .= (substr($script_path, -1) !== '/') ? '/' : '';

		// Since $script_path ends with a slash we don't want $page to start with one.
		$page = ltrim($page, '/');

		$url .= $script_path . $page;
		header('Location: ' . $url);
		exit;
	}

	/**
	 * Translate the language key. Perform substitution if args are provided.
	 *
	 * @return string
	 */
	public static function lang()
	{
		global $user;

		$args = func_get_args();
		$key = array_shift($args);

		if (!self::lang_key_exists($key))
		{
			return $key;
		}

		$lang = $user->lang[$key];

		return count($args) ? vsprintf($lang, $args) : $lang;
	}

	/**
	 * Check if a lang key exists
	 *
	 * @param string $key
	 * @return bool
	 */
	public static function lang_key_exists($key)
	{
		global $user;

		return isset($user->lang[$key]);
	}

	/**
	 * Applies language selected by user to QI.
	 *
	 * @param string $lang
	 */
	public static function apply_lang($lang = '')
	{
		global $quickinstall_path;

		$lang = ($lang !== '' && file_exists("{$quickinstall_path}language/$lang")) ? $lang : 'en';
		if ($lang === 'en' && !file_exists("{$quickinstall_path}language/$lang"))
		{
			trigger_error('Either your selected language or the English language files that came with QuickInstall could not be found. Make sure that you have at least the English language files in QI_PATH/language/', E_USER_ERROR);
		}

		self::add_lang(['qi', 'phpbb'], "{$quickinstall_path}language/$lang/");
	}

	/**
	* Add Language Items
	*
	* @param mixed $lang_set specifies the language entries to include
	*/
	public static function add_lang($lang_set, $lang_path = '')
	{
		global $user;

		$user->lang = (!empty($user->lang)) ? $user->lang : 'en';
		if (is_array($lang_set))
		{
			foreach ($lang_set as $key => $lang_file)
			{
				// Please do not delete this line.
				// We have to force the type here, else [array] language inclusion will not work
				$key = (string) $key;

				if (!is_array($lang_file))
				{
					self::set_lang($user->lang, $lang_file, $lang_path);
				}
				else
				{
					self::add_lang($lang_file, $lang_path);
				}
			}
			unset($lang_set);
		}
		else if ($lang_set)
		{
			self::set_lang($user->lang, $lang_set, $lang_path);
		}
	}

	/**
	* Set language entry (called by add_lang)
	* @access private
	*/
	protected static function set_lang(&$lang, $lang_file, $lang_path = '')
	{
		global $phpEx, $settings, $quickinstall_path;

		if (empty($lang_path))
		{
			$lang = $settings->get_config('qi_lang', 'en');
			$lang_path = "{$quickinstall_path}language/$lang/";
		}

		if (!file_exists($lang_path) || !is_dir($lang_path))
		{
			trigger_error("Could not find language $lang_path", E_USER_ERROR);
		}

		$language_filename = "{$lang_path}$lang_file.$phpEx";

		if ((@include($language_filename)) === false)
		{
			trigger_error("Language file $language_filename couldn't be opened.", E_USER_ERROR);
		}
	}

	/**
	 * Generate a lang select for the settings page.
	 *
	 * @param string $lang_path
	 * @param string $config_var
	 * @param string $get_var
	 * @return array
	 */
	public static function get_lang_select($lang_path, $config_var, $get_var = '')
	{
		global $settings;

		// Make sure $source_path ends with a slash.
		file_functions::append_slash($lang_path);

		// Need to assume that English always is available.
		if ($get_var && !empty($_GET[$get_var]))
		{
			$lang = qi_request_var($get_var, '');
			$user_lang = ($lang && file_exists($lang_path . $lang)) ? $lang : 'en';
		}
		else
		{
			$user_lang = $settings->get_config($config_var, 'en');
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

			$lang_iso = $lang_path . $lang . DIRECTORY_SEPARATOR . 'iso.txt'; // for phpBB 3.x languages
			$lang_composer = $lang_path . $lang . DIRECTORY_SEPARATOR . 'composer.json'; // for phpBB 4.0 languages

			if (file_exists($lang_iso))
			{
				$rows = file($lang_iso, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

				$english_name = $rows[0];
				$local_name = $rows[1];
			}
			else if (file_exists($lang_composer))
			{
				global $phpbb_root_path;
				$language_helper = new \phpbb\language\language_file_helper($phpbb_root_path);

				try
				{
					$lang_pack = $language_helper->get_language_data_from_composer_file($lang_composer);
				}
				catch (\DomainException $e)
				{
					trigger_error('LANGUAGE_PACK_MISSING', E_USER_WARNING);
				}

				$english_name = $lang_pack['name'];
				$local_name = $lang_pack['local_name'];
			}
			else
			{
				// worst case just use the iso name if nothing above worked
				$local_name = $english_name = $lang;
			}

			// Always show the English language name, except for the "active" language.
			$lang_options[] = [
				'name' => ($lang === $user_lang) ? $local_name : $english_name,
				'value' => $lang,
				'selected' => $lang === $user_lang,
			];
		}

		return $lang_options;
	}

	public static function url($page, $params = array())
	{
		global $quickinstall_path, $phpEx;

		if (!empty($params))
		{
			array_walk($params, function (&$value, $name) {
				$value = urlencode($name) . '=' . urlencode($value);
			});
		}

		return $quickinstall_path . 'index.' . $phpEx . '?page=' . $page . (!empty($params) ? ('&amp;' . implode('&amp;', $params)) : '');
	}

	/**
	 * Error and message handler, call with trigger_error if reqd.
	 * Mostly borrowed from phpBB includes/functions.php.
	 */
	public static function msg_handler($errno, $msg_text, $errfile, $errline)
	{
		global $phpEx, $phpbb_root_path, $msg_title, $msg_long_text, $quickinstall_path, $user;

		// Do not display notices if we suppress them via @
		if (error_reporting() == 0 && $errno != E_USER_ERROR && $errno != E_USER_WARNING && $errno != E_USER_NOTICE)
		{
			return;
		}

		// Message handler is stripping text. In case we need it, we are possible to define long text...
		if (isset($msg_long_text) && $msg_long_text && !$msg_text)
		{
			$msg_text = $msg_long_text;
		}

		if (!defined('E_DEPRECATED'))
		{
			define('E_DEPRECATED', 8192);
		}

		switch ($errno)
		{
			case E_NOTICE:
			case E_WARNING:
				// Check the error reporting level and return if the error level does not match
				// If DEBUG is defined the default level is E_ALL
				if (($errno & ((defined('DEBUG')) ? E_ALL : error_reporting())) == 0)
				{
					return;
				}

				// remove complete path to installation, with the risk of changing backslashes meant to be there
				$errfile = str_replace(array(phpbb_functions::phpbb_realpath($phpbb_root_path), '\\'), array('', '/'), $errfile);
				$msg_text = str_replace(array(phpbb_functions::phpbb_realpath($phpbb_root_path), '\\'), array('', '/'), $msg_text);
				$error_name = ($errno === E_WARNING) ? 'PHP Warning' : 'PHP Notice';

				echo '<b>[QI Debug] ' . $error_name . '</b>: in file <b>' . $errfile . '</b> on line <b>' . $errline . '</b>: <b>' . $msg_text . '</b><br />' . "\n";

				return;
			break;

			case E_USER_ERROR:
			case E_USER_WARNING:
			case E_USER_NOTICE:
				if ($user === null)
				{
					$user = new stdClass();
				}

				if (empty($user->lang))
				{
					$lang = [];
					include "{$quickinstall_path}language/en/qi.$phpEx";
					$user->lang = $lang;
					unset($lang);
				}

				$msg_text = self::lang($msg_text);

				if ($errno === E_USER_ERROR)
				{
					$backtrace = phpbb_functions::get_backtrace();
					if ($backtrace)
					{
						$msg_text .= '<br /><br />BACKTRACE<br />' . $backtrace;
					}
				}

				if (self::is_ajax())
				{
					self::ajax_response(['responseText' => $msg_text]);
				}

				phpbb_functions::send_status_line(503, 'Service Unavailable');

				if (!class_exists('twig'))
				{
					require("{$quickinstall_path}includes/twig.$phpEx");
				}

				$template = new twig($user, false, $quickinstall_path);

				$template->assign_vars([
					'ERROR_MSG_TITLE'	=> isset($msg_title) ? self::lang($msg_title) : self::lang('GENERAL_ERROR'),
					'ERROR_MSG_TEXT'	=> $msg_text,
					'QI_VERSION'		=> self::current_version(),
					'U_MAIN'			=> self::url('main'),
					'U_SETTINGS'		=> self::url('settings'),
					'U_DOCS'			=> self::url('docs'),
					'U_PHPINFO'			=> self::url('phpinfo'),
					'T_THEME_PATH'		=> $quickinstall_path . 'style',
					'S_HAS_PROFILES'	=> true,
				]);

				$template->display('error');

				// As a pre-caution... some setups display a blank page if the flush() is not there.
				(ob_get_level() > 0) ? @ob_flush() : @flush();

				// On a fatal error (and E_USER_ERROR *is* fatal) we never want other scripts to continue and force an exit here.
				exit;
			break;
		}

		// If we notice an error not handled here we pass this back to PHP by returning false
		// This may not work for all php versions
		return false;
	}

	/**
	 * Is an AJAX request active
	 *
	 * @return bool
	 */
	public static function is_ajax()
	{
		return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
	}

	/**
	 * Send an ajax response and exit
	 *
	 * @param array $response
	 */
	public static function ajax_response($response)
	{
		echo json_encode($response);
		exit;
	}

	/**
	 * Get version update data
	 *
	 * @return array
	 */
	public static function get_update()
	{
		global $quickinstall_path, $phpEx;

		if (!class_exists('qi_version_helper'))
		{
			include "{$quickinstall_path}includes/qi_version_helper.$phpEx";
		}

		$version_helper = new qi_version_helper();

		return $version_helper
			->set_current_version(self::current_version())
			->force_stability('stable')
			->set_file_location('www.phpbb.com', '/customise/db/official_tool/phpbb3_quickinstall', 'version_check')
			->get_update();
	}

	/**
	 * Get the current version of QuickInstall from composer.json
	 *
	 * @return string
	 */
	public static function current_version()
	{
		global $quickinstall_path;

		static $composerJson = null;

		if ($composerJson === null) {
			$composerJson = file_get_contents("{$quickinstall_path}composer.json");
			$composerJson = json_decode($composerJson, true);
		}

		return $composerJson["version"];
	}

	/**
	 * Set/unset a cookie
	 *
	 * @param string $name The name of the cookie to set/unset
	 * @param string $value The value to give the cookie. No value will delete cookie.
	 */
	public static function set_cookie($name, $value = '')
	{
		$time = $value === '' ? '-1 year' : '+1 year';
		setcookie($name, $value, strtotime($time));
	}

	/**
	 * Delete all cookies by name
	 *
	 * @param string $name The cookie name; whole name or just the beginning
	 */
	public static function delete_cookies($name)
	{
		if (isset($_SERVER['HTTP_COOKIE']) && $name)
		{
			$cookies = explode(';', $_SERVER['HTTP_COOKIE']);
			foreach($cookies as $cookie)
			{
				$crumbs = explode('=', $cookie);
				$crumb = trim($crumbs[0]);
				if (strpos($crumb, $name) === 0)
				{
					self::set_cookie($name);
				}
			}
		}
	}
}
