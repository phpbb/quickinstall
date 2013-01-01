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
* MySQL dbal extension
* @package dbal
*/
class dbal_mysql_qi extends dbal_mysql
{
	/**
	* Connect to server
	*/
	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false, $new_link = false)
	{
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->server = $sqlserver . (($port) ? ':' . $port : '');
		$this->dbname = $database;

		$this->sql_layer = 'mysql_40';

		$this->db_connect_id = ($this->persistency) ? @mysql_pconnect($this->server, $this->user, $sqlpassword, $new_link) : @mysql_connect($this->server, $this->user, $sqlpassword, $new_link);

		if ($this->db_connect_id)
		{
			// Determine what version we are using and if it natively supports UNICODE
			$this->mysql_version = mysql_get_server_info($this->db_connect_id);

			if (version_compare($this->mysql_version, '4.1.3', '>='))
			{
				@mysql_query("SET NAMES 'utf8'", $this->db_connect_id);
			}
			else if (version_compare($this->mysql_version, '4.0.0', '<'))
			{
				$this->sql_layer = 'mysql';
			}

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
		return @mysql_select_db($dbname, $this->db_connect_id);
	}

	/**
	 * Updates value of a sequence.
	 * Does nothing in this dbal.
	 */
	public function update_sequence($sequence_name, $value)
	{
	}
}
