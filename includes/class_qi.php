<?php
/**
*
* @package quickinstall
* @copyright (c) 2007 phpBB Group
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
	public static function page_header($page_title = '', $page_about = '')
	{
		if (defined('HEADER_INC'))
		{
			return;
		}

		define('HEADER_INC', true);
		global $template, $user, $phpbb_root_path, $quickinstall_path, $settings, $page, $mode;

		$template->assign_vars(array(
			'PAGE_TITLE'	=> $page_title,
			'PAGE_ABOUT'	=> $page_about,
			'T_THEME_PATH'	=> 'style',
			'T_IMAGE_PATH'	=> $quickinstall_path . 'style/images/',

			'U_ABOUT'		=> self::url('about'),
			'U_MANAGE'		=> self::url('manage'),
			'U_MAIN'		=> self::url('main'),
			'U_PHPINFO'		=> self::url('phpinfo'),
			'U_SETTINGS'	=> self::url('settings'),

			'S_ABOUT'		=> ($page == 'about') ? true : false,
			'S_MANAGE'		=> ($page == 'manage') ? true : false,
			'S_MAIN'		=> ($page == 'main') ? true : false,
			'S_PHPINFO'		=> ($page == 'phpinfo') ? true : false,
			'S_SETTINGS'	=> ($page == 'settings') ? true : false,

			'S_CONTENT_DIRECTION'	=> $user->lang['DIRECTION'],
			'S_CONTENT_ENCODING'	=> 'UTF-8',
			'S_USER_LANG'			=> $user->lang['USER_LANG'],

			'S_SHOW_CONFIRM'	=> $settings->get_config('show_confirm', 0),

			'TRANSLATION_INFO'	=> $user->lang['TRANSLATION_INFO'],
			'QI_VERSION'		=> QI_VERSION,
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
	public static function page_footer()
	{
		global $db, $template;

		$template->display('body');

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
		$server_name = (!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : getenv('SERVER_NAME');
		$server_port = (!empty($_SERVER['SERVER_PORT'])) ? (int) $_SERVER['SERVER_PORT'] : (int) getenv('SERVER_PORT');
		$secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 1 : 0;

		$script_name = (!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : getenv('PHP_SELF');
		if (!$script_name)
		{
			$script_name = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');
		}

		// Replace backslashes and doubled slashes (could happen on some proxy setups)
		$script_name = str_replace(array('\\', '//'), '/', $script_name);
		$script_path = trim(dirname($script_name));

		$url = (($secure) ? 'https://' : 'http://') . $server_name;

		if ($server_port && (($secure && $server_port <> 443) || (!$secure && $server_port <> 80)))
		{
			$url .= ':' . $server_port;
		}

		// Make sure script path ends with a slash.
		$script_path .= (substr($script_path, -1) != '/') ? '/' : '';

		$url .= $script_path . $page;
		header('Location: ' . $url);
		exit;
	}

	/**
	* Add Language Items
	*
	* @param mixed $lang_set specifies the language entries to include
	*/
	public static function add_lang($lang_set, $lang_path = false)
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
	protected static function set_lang(&$lang, $lang_file, $lang_path = false)
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
	* Format user date
	*/
	public static function format_date($gmepoch, $format = false, $forcedate = false)
	{
		global $user, $settings;
		static $midnight;

		$lang_dates = $user->lang['datetime'];
		$format = (!$format) ? $user->lang['default_dateformat'] : $format;

		// Short representation of month in format
		if ((strpos($format, '\M') === false && strpos($format, 'M') !== false) || (strpos($format, '\r') === false && strpos($format, 'r') !== false))
		{
			$lang_dates['May'] = $lang_dates['May_short'];
		}

		unset($lang_dates['May_short']);

		if (!$midnight)
		{
			list($d, $m, $y) = explode(' ', gmdate('j n Y', time() + $settings->get_config('qi_tz', 0) + $settings->get_config('qi_dst', 0)));
			$midnight = gmmktime(0, 0, 0, $m, $d, $y) - $settings->get_config('qi_tz', 0) - $settings->get_config('qi_dst', 0);
		}

		if (strpos($format, '|') === false || ($gmepoch < $midnight - 86400 && !$forcedate) || ($gmepoch > $midnight + 172800 && !$forcedate))
		{
			return strtr(@gmdate(str_replace('|', '', $format), $gmepoch + $settings->get_config('qi_tz', 0) + $settings->get_config('qi_dst', 0)), $lang_dates);
		}

		if ($gmepoch > $midnight + 86400 && !$forcedate)
		{
			$format = substr($format, 0, strpos($format, '|')) . '||' . substr(strrchr($format, '|'), 1);
			return str_replace('||', $user->lang['datetime']['TOMORROW'], strtr(@gmdate($format, $gmepoch + $settings->get_config('qi_tz', 0) + $settings->get_config('qi_dst', 0)), $lang_dates));
		}
		else if ($gmepoch > $midnight && !$forcedate)
		{
			$format = substr($format, 0, strpos($format, '|')) . '||' . substr(strrchr($format, '|'), 1);
			return str_replace('||', $user->lang['datetime']['TODAY'], strtr(@gmdate($format, $gmepoch + $settings->get_config('qi_tz', 0) + $settings->get_config('qi_dst', 0)), $lang_dates));
		}
		else if ($gmepoch > $midnight - 86400 && !$forcedate)
		{
			$format = substr($format, 0, strpos($format, '|')) . '||' . substr(strrchr($format, '|'), 1);
			return str_replace('||', $user->lang['datetime']['YESTERDAY'], strtr(@gmdate($format, $gmepoch + $settings->get_config('qi_tz', 0) + $settings->get_config('qi_dst', 0)), $lang_dates));
		}

		return strtr(@gmdate(str_replace('|', '', $format), $gmepoch + $settings->get_config('qi_tz', 0) + $settings->get_config('qi_dst', 0)), $lang_dates);
	}

	public static function url($page, $params = false)
	{
		global $quickinstall_path, $phpEx;

		if (is_array($params))
		{
			$_params = '';
			foreach ($params as $name => $value)
			{
				$_params .= urlencode($name) . '=' . urlencode($value);
			}
			$params = &$_params;
		}

		return $quickinstall_path . 'index.' . $phpEx . '?page=' . $page . ($params ? ('&amp;' . $params) : '');
	}

	/**
	 * Error and message handler, call with trigger_error if reqd.
	 * Mostly borrowed from phpBB includes/functions.php.
	 */
	public static function msg_handler($errno, $msg_text, $errfile, $errline)
	{
		global $phpEx, $phpbb_root_path, $msg_title, $msg_long_text, $quickinstall_path;
		global $user;

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
				if (!empty($user) && !empty($user->lang))
				{
					$msg_text = (!empty($user->lang[$msg_text])) ? $user->lang[$msg_text] : $msg_text;
					$msg_title = (!isset($msg_title)) ? $user->lang['GENERAL_ERROR'] : ((!empty($user->lang[$msg_title])) ? $user->lang[$msg_title] : $msg_title);

					$l_return_index = sprintf($user->lang['GO_QI_MAIN'], '<a href="' . qi::url('main') . '">', '</a> &bull; ');
					$l_return_index .= sprintf($user->lang['GO_QI_SETTINGS'], '<a href="' . qi::url('settings') . '">', '</a>');
				}
				else
				{
					$msg_title = 'General Error';
					$l_return_index = '<a href="' . qi::url('main') . '">Go to QuickInstall main page</a> &bull; ';
					$l_return_index .= '<a href="' . qi::url('settings') . '">Go to settings</a> &bull; ';
				}

				$backtrace = phpbb_functions::get_backtrace();
				if ($backtrace)
				{
					$msg_text .= '<br /><br />BACKTRACE<br />' . $backtrace;
				}

				send_status_line(503, 'Service Unavailable');

				echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
				echo '<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">';
				echo '<head>';
				echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
				echo '<title>' . $msg_title . '</title>';
				echo '<link href="' . $quickinstall_path . 'style/style.css" rel="stylesheet" type="text/css" media="screen" />';
				echo '</head>';
				echo '<body id="errorpage">';
				echo '<div id="wrap">';
				echo '	<div id="page-header">';
				echo '		' . $l_return_index;
				echo '	</div>';

				echo '	<div id="page-body">';
				echo '		<div id="acp">';
				echo '			<div class="panel">';
				echo '				<span class="corners-top"><span></span></span>';
				echo '				<div id="content">';
				echo '				<h1>' . $msg_title . '</h1>';
				echo '				<div>' . $msg_text . '</div>';
				echo '				</div>';
				echo '				<div style="padding-left: 10px;">';
				echo '					' . $l_return_index;
				echo '				</div>';
				echo '				<span class="corners-bottom"><span></span></span>';
				echo '			</div>';
				echo '		</div>';
				echo '	</div>';

				echo '	<div id="page-footer">';
				echo '		Powered by <a href="https://www.phpbb.com/customise/db/official_tool/phpbb3_quickinstall/">phpBB QuickInstall</a> {QI_VERSION} for phpBB 3.0.x &copy; <a href="http://www.phpbb.com/">phpBB Group</a>';
				echo '	</div>';
				echo '</div>';
				echo '</body>';
				echo '</html>';

				// As a pre-caution... some setups display a blank page if the flush() is not there.
				(ob_get_level() > 0) ? @ob_flush() : @flush();

				// On a fatal error (and E_USER_ERROR *is* fatal) we never want other scripts to continue and force an exit here.
				exit;
			break;
		}

		// If we notice an error not handled here we pass this back to PHP by returning false
		// This may not work for all php versions
		return(false);
	}
}
