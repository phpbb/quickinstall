<?php
/**
 * @package
 * @copyright (c) 2007 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

/**
* @ignore
*/
if (!defined('IN_QUICKINSTALL'))
{
	exit;
}

/**
 * Class with needed functions from phpBB.
 * They are all more or less directly copied from phpBB 3.0.
 * To not need to include any phpBB functions before trying to install a board.
 * And to be able to support both phpBB 3.0 and 3.1. And eventually 3.2 and so on.
 * Have them in a class to not collide with phpBB functions having the same name
 * This class is to be used statically.
 */
class phpbb_functions
{
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
		if (substr(strtolower(@php_sapi_name()), 0, 3) === 'cgi')
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
			$root_path = self::phpbb_realpath(dirname(__FILE__) . '/../');
		}

		return str_replace(array($root_path, '\\'), array('[ROOT]', '/'), $errfile);
	}

	/**
	* A wrapper for realpath
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
		if (substr($realpath, -1) == DIRECTORY_SEPARATOR)
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
		return ($path[0] == '/' || (DIRECTORY_SEPARATOR == '\\' && preg_match('#^[a-z]:[/\\\]#i', $path))) ? true : false;
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
		$path_prefix = '';

		// Determine what sort of path we have
		if (self::is_absolute($path))
		{
			$absolute = true;

			if ($path[0] == '/')
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
		for ($i = 0, $max = sizeof($bits); $i < $max; $i++)
		{
			// @todo Optimise
			if ($bits[$i] == '..' )
			{
				if (isset($bits[$i - 1]))
				{
					if ($bits[$i - 1] != '..')
					{
						// We found a .. and we are able to traverse upwards, lets do it!
						unset($bits[$i]);
						unset($bits[$i - 1]);
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

		$max = sizeof($bits) - 1;

		// Check if we are able to resolve symlinks, Windows cannot.
		$symlink_resolve = (function_exists('readlink')) ? true : false;

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
		if (substr($resolved, -1) == DIRECTORY_SEPARATOR)
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
		unset($backtrace[0]); // The first only shows this file/function.
		unset($backtrace[1]); // The second shows qi::msg_handler().


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
}
