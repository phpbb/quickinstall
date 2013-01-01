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
* MySQLi dbal extension
* @package dbal
*/
class dbal_mysqli_qi extends dbal_mysqli
{
	/**
	* Connect to server
	*/
	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false , $new_link = false)
	{
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->server = $sqlserver;
		$this->dbname = $database;
		$port = (!$port) ? NULL : $port;

		$this->sql_layer = 'mysql_41';

		// Persistant connections not supported by the mysqli extension?
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
	 */
	function sql_select_db($dbname)
	{
		return @mysqli_select_db($this->db_connect_id, $dbname);
	}

	/**
	 * Updates value of a sequence.
	 * Does nothing in this dbal.
	 */
	public function update_sequence($sequence_name, $value)
	{
	}
}
