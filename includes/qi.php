<?php
/**
*
* @package quickinstall
* @copyright (c) 2007 phpBB Limited
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
 * Class with functions useful for qi. Some stuff is from the install functions, this class is to be used statically.
 */
class qi
{
	/** @var string The prefix name of phpBB board cookies */
	const PHPBB_COOKIE_PREFIX = 'phpbb3_';

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
		global $template, $quickinstall_path;

		$update = self::get_update();

		$template->assign_vars(array(
			'PAGE_TITLE'	=> self::lang($page_title),
			'QI_ROOT_PATH'	=> $quickinstall_path,

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
		qi_file::append_slash($lang_path);

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
				$errfile = str_replace(array(self::phpbb_realpath($phpbb_root_path), '\\'), array('', '/'), $errfile);
				$msg_text = str_replace(array(self::phpbb_realpath($phpbb_root_path), '\\'), array('', '/'), $msg_text);
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
					$backtrace = self::get_backtrace();
					if ($backtrace)
					{
						$msg_text .= '<br /><br />BACKTRACE<br />' . $backtrace;
					}
				}

				if (self::is_ajax())
				{
					self::ajax_response(['responseText' => $msg_text]);
				}

				self::send_status_line(503, 'Service Unavailable');

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
					'QI_ROOT_PATH'		=> $quickinstall_path,
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
			->set_file_location('www.phpbb.com', '/customise/db/official_tool/phpbb3_quickinstall', 'version_check', true)
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
					self::set_cookie($crumb);
				}
			}
		}
	}

	/**
	 * Outputs correct status line header.
	 *
	 * Depending on php sapi one of the two following forms is used:
	 *
	 * Status: 404 Not Found
	 *
	 * HTTP/1.x 404 Not Found
	 *
	 * HTTP version is taken from HTTP_VERSION environment variable,
	 * and defaults to 1.0.
	 *
	 * Sample usage:
	 *
	 * send_status_line(404, 'Not Found');
	 *
	 * @param int $code HTTP status code
	 * @param string $message Message for the status code
	 * @return void
	 */
	public static function send_status_line($code, $message)
	{
		if (stripos(PHP_SAPI, 'cgi') === 0)
		{
			// in theory, we shouldn't need that due to php doing it. Reality offers a differing opinion, though
			header("Status: $code $message", true, $code);
		}
		else
		{
			if (!empty($_SERVER['SERVER_PROTOCOL']))
			{
				$version = $_SERVER['SERVER_PROTOCOL'];
			}
			else
			{
				$version = 'HTTP/1.0';
			}
			header("$version $code $message", true, $code);
		}
	}

	/**
	 * Removes absolute path to phpBB root directory from error messages
	 * and converts backslashes to forward slashes.
	 *
	 * @param string $errfile	Absolute file path
	 *							(e.g. /var/www/phpbb3/phpBB/includes/functions.php)
	 *							Please note that if $errfile is outside of the phpBB root,
	 *							the root path will not be found and can not be filtered.
	 * @return string			Relative file path
	 *							(e.g. /includes/functions.php)
	 */
	public static function phpbb_filter_root_path($errfile)
	{
		static $root_path;

		if (empty($root_path))
		{
			$root_path = self::phpbb_realpath(__DIR__ . '/../');
		}

		return str_replace(array($root_path, '\\'), array('[ROOT]', '/'), $errfile);
	}

	/**
	 * A wrapper for realpath
	 *
	 * @param string $path
	 * @return bool|false|mixed|string
	 */
	public static function phpbb_realpath($path)
	{
		$realpath = realpath($path);

		// Strangely there are provider not disabling realpath but returning strange values. :o
		// We at least try to cope with them.
		if ($realpath === $path || $realpath === false)
		{
			return self::phpbb_own_realpath($path);
		}

		// Check for DIRECTORY_SEPARATOR at the end (and remove it!)
		if (substr($realpath, -1) === DIRECTORY_SEPARATOR)
		{
			$realpath = substr($realpath, 0, -1);
		}

		return $realpath;
	}

	/**
	 * Checks if a path ($path) is absolute or relative
	 *
	 * @param string $path Path to check absoluteness of
	 * @return boolean
	 */
	public static function is_absolute($path)
	{
		return $path[0] === '/' || (DIRECTORY_SEPARATOR === '\\' && preg_match('#^[a-z]:[/\\\]#i', $path));
	}

	/**
	 * @author Chris Smith <chris@project-minerva.org>
	 * @copyright 2006 Project Minerva Team
	 * @param string $path The path which we should attempt to resolve.
	 * @return mixed
	 */
	public static function phpbb_own_realpath($path)
	{
		// Now to perform funky shizzle

		// Switch to use UNIX slashes
		$path = str_replace(DIRECTORY_SEPARATOR, '/', $path);

		// Determine what sort of path we have
		if (self::is_absolute($path))
		{
			$absolute = true;

			if ($path[0] === '/')
			{
				// Absolute path, *NIX style
				$path_prefix = '';
			}
			else
			{
				// Absolute path, Windows style
				// Remove the drive letter and colon
				$path_prefix = $path[0] . ':';
				$path = substr($path, 2);
			}
		}
		else
		{
			// Relative Path
			// Prepend the current working directory
			if (function_exists('getcwd'))
			{
				// This is the best method, hopefully it is enabled!
				$path = str_replace(DIRECTORY_SEPARATOR, '/', getcwd()) . '/' . $path;
				$absolute = true;
				if (preg_match('#^[a-z]:#i', $path))
				{
					$path_prefix = $path[0] . ':';
					$path = substr($path, 2);
				}
				else
				{
					$path_prefix = '';
				}
			}
			else if (isset($_SERVER['SCRIPT_FILENAME']) && !empty($_SERVER['SCRIPT_FILENAME']))
			{
				// Warning: If chdir() has been used this will lie!
				// Warning: This has some problems sometime (CLI can create them easily)
				$path = str_replace(DIRECTORY_SEPARATOR, '/', dirname($_SERVER['SCRIPT_FILENAME'])) . '/' . $path;
				$absolute = true;
				$path_prefix = '';
			}
			else
			{
				// We have no way of getting the absolute path, just run on using relative ones.
				$absolute = false;
				$path_prefix = '.';
			}
		}

		// Remove any repeated slashes
		$path = preg_replace('#/{2,}#', '/', $path);

		// Remove the slashes from the start and end of the path
		$path = trim($path, '/');

		// Break the string into little bits for us to nibble on
		$bits = explode('/', $path);

		// Remove any . in the path, renumber array for the loop below
		$bits = array_values(array_diff($bits, array('.')));

		// Lets get looping, run over and resolve any .. (up directory)
		for ($i = 0, $max = count($bits); $i < $max; $i++)
		{
			// @todo Optimise
			if ($bits[$i] === '..' )
			{
				if (isset($bits[$i - 1]))
				{
					if ($bits[$i - 1] !== '..')
					{
						// We found a .. and we are able to traverse upwards, lets do it!
						unset($bits[$i], $bits[$i - 1]);
						$i -= 2;
						$max -= 2;
						$bits = array_values($bits);
					}
				}
				else if ($absolute) // ie. !isset($bits[$i - 1]) && $absolute
				{
					// We have an absolute path trying to descend above the root of the filesystem
					// ... Error!
					return false;
				}
			}
		}

		// Prepend the path prefix
		array_unshift($bits, $path_prefix);

		$resolved = '';

		$max = count($bits) - 1;

		// Check if we are able to resolve symlinks, Windows cannot.
		$symlink_resolve = function_exists('readlink');

		foreach ($bits as $i => $bit)
		{
			if (@is_dir("$resolved/$bit") || ($i == $max && @is_file("$resolved/$bit")))
			{
				// Path Exists
				if ($symlink_resolve && is_link("$resolved/$bit") && ($link = readlink("$resolved/$bit")))
				{
					// Resolved a symlink.
					$resolved = $link . (($i == $max) ? '' : '/');
					continue;
				}
			}
			else
			{
				// Something doesn't exist here!
				// This is correct realpath() behaviour but sadly open_basedir and safe_mode make this problematic
				// return false;
			}
			$resolved .= $bit . (($i == $max) ? '' : '/');
		}

		// @todo If the file exists fine and open_basedir only has one path we should be able to prepend it
		// because we must be inside that basedir, the question is where...
		// @internal The slash in is_dir() gets around an open_basedir restriction
		if (!@file_exists($resolved) || (!@is_dir($resolved . '/') && !is_file($resolved)))
		{
			return false;
		}

		// Put the slashes back to the native operating systems slashes
		$resolved = str_replace('/', DIRECTORY_SEPARATOR, $resolved);

		// Check for DIRECTORY_SEPARATOR at the end (and remove it!)
		if (substr($resolved, -1) === DIRECTORY_SEPARATOR)
		{
			return substr($resolved, 0, -1);
		}

		return $resolved; // We got here, in the end!
	}

	/**
	 * Return a nicely formatted backtrace.
	 *
	 * Turns the array returned by debug_backtrace() into HTML markup.
	 * Also filters out absolute paths to phpBB root.
	 *
	 * @return string	HTML markup
	 */
	public static function get_backtrace()
	{
		$output = '<div style="font-family: monospace;">';
		$backtrace = debug_backtrace();

		// We skip the first two,
		unset($backtrace[0], $backtrace[1]); // The first only shows this file/function.
		// The second shows qi::msg_handler().

		foreach ($backtrace as $trace)
		{
			// Strip the current directory from path
			$trace['file'] = (empty($trace['file'])) ? '(not given by php)' : htmlspecialchars(self::phpbb_filter_root_path($trace['file']));
			$trace['line'] = (empty($trace['line'])) ? '(not given by php)' : $trace['line'];

			// Only show function arguments for include etc.
			// Other parameters may contain sensible information
			$argument = '';
			if (!empty($trace['args'][0]) && in_array($trace['function'], array('include', 'require', 'include_once', 'require_once')))
			{
				$argument = htmlspecialchars(self::phpbb_filter_root_path($trace['args'][0]));
			}

			$trace['class'] = (!isset($trace['class'])) ? '' : $trace['class'];
			$trace['type'] = (!isset($trace['type'])) ? '' : $trace['type'];

			$output .= '<br />';
			$output .= '<b>FILE:</b> ' . $trace['file'] . '<br />';
			$output .= '<b>LINE:</b> ' . ((!empty($trace['line'])) ? $trace['line'] : '') . '<br />';

			$output .= '<b>CALL:</b> ' . htmlspecialchars($trace['class'] . $trace['type'] . $trace['function']);
			$output .= '(' . (($argument !== '') ? "'$argument'" : '') . ')<br />';
		}
		$output .= '</div>';
		return $output;
	}

	/**
	 * Wrapper for version_compare() that allows using uppercase A and B
	 * for alpha and beta releases.
	 *
	 * See http://www.php.net/manual/en/function.version-compare.php
	 *
	 * @param string $version1 First version number
	 * @param string $version2 Second version number
	 * @param string $operator Comparison operator (optional)
	 *
	 * @return bool|int        Boolean (true, false) if comparison operator is specified.
	 *                         Integer (-1, 0, 1) otherwise.
	 */
	public static function phpbb_version_compare($version1, $version2, $operator = null)
	{
		$version1 = strtolower($version1);
		$version2 = strtolower($version2);

		if (null === $operator)
		{
			return version_compare($version1, $version2);
		}
		return version_compare($version1, $version2, $operator);
	}

	/**
	 * Check phpBB compatibility with the PHP environment
	 *
	 * phpBB 3.1.x is not compat with PHP >= 7 (700000)
	 * phpBB 3.2.0-3.2.1 is not compat with PHP >= 7.2 (702000)
	 * phpBB 3.2.x is not compat with PHP >= 7.3 (703000)
	 * phpBB 3.3.x is not compat with PHP < 7.1.3 (70103)
	 * phpBB 4.0.x is not compat with PHP < 7.3 (703000)
	 *
	 * @param string $phpbb_version Check a given phpBB version. If none given, will check QI's loaded phpBB.
	 *
	 * @return bool
	 */
	public static function php_phpbb_incompatible($phpbb_version = '')
	{
		if ($phpbb_version)
		{
			return
				(PHP_VERSION_ID >= 70000 && self::phpbb_version_compare($phpbb_version, '3.2', '<')) ||
				(PHP_VERSION_ID >= 70200 && self::phpbb_version_compare($phpbb_version, '3.2.2', '<')) ||
				(PHP_VERSION_ID >= 70300 && self::phpbb_version_compare($phpbb_version, '3.3', '<')) ||
				(PHP_VERSION_ID < 70103 && self::phpbb_version_compare($phpbb_version, '3.3', '>=')) ||
				(PHP_VERSION_ID < 70300 && self::phpbb_version_compare($phpbb_version, '4.0', '>='))
			;
		}

		return
			(PHP_VERSION_ID >= 70000 && !defined('PHPBB_32')) ||
			(PHP_VERSION_ID >= 70200 && self::phpbb_version_compare(PHPBB_VERSION, '3.2.2', '<')) ||
			(PHP_VERSION_ID >= 70300 && !defined('PHPBB_33')) ||
			(PHP_VERSION_ID < 70103 && defined('PHPBB_33')) ||
			(PHP_VERSION_ID < 70300 && defined('PHPBB_40'))
		;
	}
}
