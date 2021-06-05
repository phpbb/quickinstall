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
* SQLite3 dbal extension
* @package dbal
*/
class dbal_sqlite3_qi extends \phpbb\db\driver\sqlite3
{
	/**
	 * Used for $this->server
	 */
	protected $port;

	/**
	* Connect to server
	*/
	public function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false , $new_link = false)
	{
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->server = $sqlserver . ($port ? ':' . $port : '');
		$this->dbname = $database;

		$this->port = $port;

		$this->sql_layer = 'sqlite3';

		// connect to db
		$this->sql_select_db();

		return $this->db_connect_id ? true : array('message' => $this->connect_error);
	}

	/**
	 * Select a database
	 *
	 * @param string $dbname
	 * @return bool
	 */
	public function sql_select_db($dbname = '')
	{
		if (!file_exists($dbname))
		{
			return false;
		}

		$this->server = $dbname ? ($dbname . ($this->port ? ':' . $this->port : '')) : $this->server;

		try
		{
			$this->dbo = new \SQLite3($this->server,SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
			$this->dbo->busyTimeout(60000);
			$this->db_connect_id = true;
		}
		catch (\Exception $e)
		{
			$this->connect_error = $e->getMessage();
			$this->db_connect_id = false;
		}

		return $this->db_connect_id;
	}

	/**
	 * Create a database
	 *
	 * @param string $dbname
	 */
	public function sql_create_db($dbname)
	{
		if (!file_exists($dbname))
		{
			// if file doesn't exist, attempt to create it.
			if (!is_writable(dirname($dbname)))
			{
				trigger_error('SQLite: unable to write to dir ' . dirname($dbname), E_USER_ERROR);
			}

			$fp = @fopen($dbname, 'ab');
			@fclose($fp);
			@chmod($dbname, 0777);
		}
	}
}
