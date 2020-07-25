<?php
/**
 *
 * @package quickinstall
 * @copyright (c) 2017 phpBB Limited
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

class qi_version_helper
{
	/**
	 * @var string Host
	 */
	protected $host;

	/**
	 * @var string Path to file
	 */
	protected $path;

	/**
	 * @var string File name
	 */
	protected $file;

	/**
	 * @var bool Use SSL or not
	 */
	protected $use_ssl = false;

	/**
	 * @var string Current version installed
	 */
	protected $current_version;

	/**
	 * @var null|string Null to not force stability, 'unstable' or 'stable' to
	 *					force the corresponding stability
	 */
	protected $force_stability;

	/** @var qi_file_downloader */
	protected $file_downloader;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->file_downloader = new qi_file_downloader();
	}

	/**
	 * Set location to the file
	 *
	 * @param string $host Host (e.g. version.phpbb.com)
	 * @param string $path Path to file (e.g. /phpbb)
	 * @param string $file File name (Default: versions.json)
	 * @param bool $use_ssl Use SSL or not (Default: false)
	 * @return qi_version_helper
	 */
	public function set_file_location($host, $path, $file, $use_ssl = false)
	{
		$this->host = $host;
		$this->path = $path;
		$this->file = $file;
		$this->use_ssl = $use_ssl;

		return $this;
	}

	/**
	 * Set current version
	 *
	 * @param string $version The current version
	 * @return qi_version_helper
	 */
	public function set_current_version($version)
	{
		$this->current_version = $version;

		return $this;
	}

	/**
	 * Over-ride the stability to force check to include unstable versions
	 *
	 * @param null|string Null to not force stability, 'unstable' or 'stable' to
	 * 						force the corresponding stability
	 * @return qi_version_helper
	 */
	public function force_stability($stability)
	{
		$this->force_stability = $stability;

		return $this;
	}

	/**
	 * Wrapper for version_compare() that allows using uppercase A and B
	 * for alpha and beta releases.
	 *
	 * See http://www.php.net/manual/en/function.version-compare.php
	 *
	 * @param string $version1		First version number
	 * @param string $version2		Second version number
	 * @param string $operator		Comparison operator (optional)
	 *
	 * @return mixed				Boolean (true, false) if comparison operator is specified.
	 *								Integer (-1, 0, 1) otherwise.
	 */
	public function compare($version1, $version2, $operator = null)
	{
		return phpbb_functions::phpbb_version_compare($version1, $version2, $operator);
	}

	/**
	 * Check whether or not a version is "stable"
	 *
	 * Stable means only numbers OR a pl release
	 *
	 * @param string $version
	 * @return bool Bool true or false
	 */
	public function is_stable($version)
	{
		$matches = false;
		preg_match('/^[\d.]+/', $version, $matches);

		if (empty($matches[0]))
		{
			return false;
		}

		return $this->compare($version, $matches[0], '>=');
	}

	/**
	 * Get latest version update data array
	 *
	 * @return array Array of version data, or empty array if no update is available or found.
	 */
	public function get_update()
	{
		try
		{
			$updates = $this->get_suggested_updates();
			return array_shift($updates);
		}
		catch (RuntimeException $e)
		{
			return array();
		}
	}

	/**
	 * Obtains the latest version information
	 *
	 * @param bool $force_update Ignores cached data. Defaults to false.
	 * @param bool $force_cache Force the use of the cache. Override $force_update.
	 * @return array
	 * @throws RuntimeException
	 */
	public function get_suggested_updates($force_update = false, $force_cache = false)
	{
		$versions = $this->get_versions_matching_stability($force_update, $force_cache);

		$self = $this;
		$current_version = $this->current_version;

		// Filter out any versions less than or equal to the current version
		return array_filter($versions, function($data) use ($self, $current_version) {
			return $self->compare($data['current'], $current_version, '>');
		});
	}

	/**
	 * Obtains the latest version information matching the stability of the current install
	 *
	 * @param bool $force_update Ignores cached data. Defaults to false.
	 * @param bool $force_cache Force the use of the cache. Override $force_update.
	 * @return array Version info
	 * @throws RuntimeException
	 */
	public function get_versions_matching_stability($force_update = false, $force_cache = false)
	{
		$info = $this->get_versions($force_update, $force_cache);

		if ($this->force_stability !== null)
		{
			return ($this->force_stability === 'unstable') ? $info['unstable'] : $info['stable'];
		}

		return $this->is_stable($this->current_version) ? $info['stable'] : $info['unstable'];
	}

	/**
	 * Obtains the latest version information
	 *
	 * @param bool $force_update Ignores cached data. Defaults to false.
	 * @param bool $force_cache Force the use of the cache. Override $force_update.
	 * @return array Version info, includes stable and unstable data
	 * @throws RuntimeException
	 */
	public function get_versions($force_update = false, $force_cache = false)
	{
		$cache_file = 'versioncheck' . $this->path;

		$info = $this->cache_get($cache_file);

		if ($info === false && $force_cache)
		{
			throw new RuntimeException('VERSIONCHECK_FAIL');
		}
		if ($info === false || $force_update)
		{
			$info = $this->file_downloader->get($this->host, $this->path, $this->file, $this->use_ssl ? 443 : 80);
			$error_string = $this->file_downloader->get_error_string();

			if (!empty($error_string))
			{
				throw new RuntimeException($error_string);
			}

			$info = json_decode($info, true);

			// Sanitize any data we retrieve from a server
			if (!empty($info))
			{
				$json_sanitizer = function (&$value, $key) {
					legacy_set_var($value, $value, gettype($value));
				};
				array_walk_recursive($info, $json_sanitizer);
			}

			if (empty($info['stable']) && empty($info['unstable']))
			{
				throw new RuntimeException('VERSIONCHECK_FAIL');
			}

			$info['stable'] = empty($info['stable']) ? array() : $info['stable'];
			$info['unstable'] = empty($info['unstable']) ? $info['stable'] : $info['unstable'];

			$this->cache_put($cache_file, $info);
		}

		return $info;
	}

	/**
	 * Put data in the cache
	 *
	 * @param string $handle Name of the cache file
	 * @param mixed $data The data to store
	 */
	protected function cache_put($handle, $data)
	{
		$filename = $this->get_cache_path($handle);

		if ($fp = @fopen($filename, 'wb'))
		{
			@flock($fp, LOCK_EX);
			@fwrite ($fp, json_encode($data));
			@flock($fp, LOCK_UN);
			@fclose($fp);

			chmod($filename, 0777);
		}
	}

	/**
	 * Get data from the cache
	 *
	 * @param string $handle Name of the cache file
	 * @return bool|mixed The cached data if it exists, false otherwise
	 */
	protected function cache_get($handle)
	{
		$filename = $this->get_cache_path($handle);

		if (is_file($filename) && (filemtime($filename) > strtotime('24 hours ago')))
		{
			if (!($file_contents = file_get_contents($filename)))
			{
				return false;
			}

			if (($data = json_decode($file_contents, true)) === null)
			{
				return false;
			}

			return $data;
		}

		return false;
	}

	/**
	 * Get the pathname for a cache file
	 *
	 * @param string $handle Name of the cache file
	 * @return string The path and name of the cache file
	 */
	protected function get_cache_path($handle)
	{
		global $settings;

		return $settings->get_cache_dir() . 'data_' . str_replace('/', '_', $handle) . '.json';
	}
}

class qi_file_downloader
{
	/** @var string Error string */
	protected $error_string = '';

	/** @var int Error number */
	protected $error_number = 0;

	/**
	 * Retrieve contents from remotely stored file
	 *
	 * @param string	$host			File host
	 * @param string	$directory		Directory file is in
	 * @param string	$filename		Filename of file to retrieve
	 * @param int		$port			Port to connect to; default: 80
	 * @param int		$timeout		Connection timeout in seconds; default: 6
	 *
	 * @return mixed File data as string if file can be read and there is no
	 *			timeout, false if there were errors or the connection timed out
	 *
	 * @throws RuntimeException() If data can't be retrieved and no error
	 *		message is returned
	 */
	public function get($host, $directory, $filename, $port = 80, $timeout = 6)
	{
		// Set default values for error variables
		$this->error_number = 0;
		$this->error_string = '';

		if ($socket = @fsockopen(($port == 443 ? 'tls://' : '') . $host, $port, $this->error_number, $this->error_string, $timeout))
		{
			@fwrite($socket, "GET $directory/$filename HTTP/1.0\r\n");
			@fwrite($socket, "HOST: $host\r\n");
			@fwrite($socket, "Connection: close\r\n\r\n");

			$timer_stop = time() + $timeout;
			stream_set_timeout($socket, $timeout);

			$file_info = '';
			$get_info = false;

			while (!@feof($socket))
			{
				if ($get_info)
				{
					$file_info .= @fread($socket, 1024);
				}
				else
				{
					$line = @fgets($socket, 1024);
					if ($line === "\r\n")
					{
						$get_info = true;
					}
					else if (stripos($line, '404 not found') !== false)
					{
						throw new RuntimeException('FILE_NOT_FOUND');
					}
				}

				$stream_meta_data = stream_get_meta_data($socket);

				if (!empty($stream_meta_data['timed_out']) || time() >= $timer_stop)
				{
					throw new RuntimeException('FSOCK_TIMEOUT');
				}
			}
			@fclose($socket);
		}
		else
		{
			if ($this->error_string)
			{
				$this->error_string = utf8_convert_message($this->error_string);
				return false;
			}

			throw new RuntimeException('FSOCK_DISABLED');
		}

		return $file_info;
	}

	/**
	 * Get error string
	 *
	 * @return string Error string
	 */
	public function get_error_string()
	{
		return $this->error_string;
	}

	/**
	 * Get error number
	 *
	 * @return int Error number
	 */
	public function get_error_number()
	{
		return $this->error_number;
	}
}
