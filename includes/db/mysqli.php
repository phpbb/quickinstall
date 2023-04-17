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
* MySQLi dbal extension
* @package dbal
*/
class dbal_mysqli_qi extends \phpbb\db\driver\mysqli
{
	/**
	* Connect to server
	*/
	public function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false , $new_link = false)
	{
		/*
		 * As of PHP 8.1 MySQLi default error mode is set to MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT
		 * See https://wiki.php.net/rfc/mysqli_default_errmode
		 * Since phpBB implements own SQL errors handling, explicitly set it back to MYSQLI_REPORT_OFF
		 */
		if (PHP_VERSION_ID >= 80100)
		{
			@mysqli_report(MYSQLI_REPORT_OFF);
		}

		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->server = $sqlserver;
		$this->dbname = $database;
		$port = (!$port) ? null : $port;

		$this->sql_layer = 'mysql_41';

		// Persistent connections not supported by the mysqli extension?
		$this->db_connect_id = @mysqli_connect($this->server, $this->user, $sqlpassword, null, $port);

		if ($this->db_connect_id)
		{
			@mysqli_query($this->db_connect_id, "SET NAMES 'utf8'");
			return $this->db_connect_id;
		}

		return $this->sql_error('');
	}

	/**
	 * Select a database
	 *
	 * @param string $dbname
	 * @return bool
	 */
	public function sql_select_db($dbname)
	{
		return @mysqli_select_db($this->db_connect_id, $dbname);
	}
}
