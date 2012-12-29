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
* SQLite dbal extension
* @package dbal
*/
class dbal_sqlite_qi extends dbal_sqlite
{
	/**
	 * Connection error
	 *
	 * @var string
	 */
	var $error = '';

	/**
	 * Used for $this->server
	 */
	var $port;

	/**
	* Connect to server
	*/
	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false , $new_link = false)
	{
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->server = $sqlserver . (($port) ? ':' . $port : '');
		$this->dbname = $database;

		$this->port = $port;

		// connect to db
		$this->sql_select_db($this->server);

		return ($this->db_connect_id) ? true : array('message' => $error);
	}

	/**
	 * Select a database
	 *
	 * @param string $dbname
	 */
	function sql_select_db($dbname)
	{
		if (!file_exists($dbname))
		{
			return(false);
			// if file doesn't exist, attempt to create it.
			if (!is_writable(dirname($dbname)))
			{
				trigger_error('SQLite: unable to write to dir ' . dirname($dbname), E_USER_ERROR);
			}

			$fp = @fopen($dbname, 'a');
			@fclose($fp);
			@chmod($dbname, 0777);
		}

		$this->server = $dbname . (($this->port) ? ':' . $this->port : '');

		$this->db_connect_id = ($this->persistency) ? @sqlite_popen($this->server, 0666, $this->error) : @sqlite_open($this->server, 0666, $this->error);

		if ($this->db_connect_id)
		{
			@sqlite_query('PRAGMA short_column_names = 1', $this->db_connect_id);
		}

		return $this->db_connect_id;
	}

	/**
	 * Select a database
	 *
	 * @param string $dbname
	 */
	function sql_create_db($dbname)
	{
		if (!file_exists($dbname))
		{
			// if file doesn't exist, attempt to create it.
			if (!is_writable(dirname($dbname)))
			{
				trigger_error('SQLite: unable to write to dir ' . dirname($dbname), E_USER_ERROR);
			}

			$fp = @fopen($dbname, 'a');
			@fclose($fp);
			@chmod($dbname, 0777);
		}

		return $this->db_connect_id;
	}

	/**
	 * Updates value of a sequence.
	 * Does nothing in this dbal.
	 */
	public function update_sequence($sequence_name, $value)
	{
	}
}
