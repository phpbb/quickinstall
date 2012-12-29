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
* MSSQL dbal extension
* @package dbal
*/
class dbal_mssql_qi extends dbal_mssql
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

		@ini_set('mssql.charset', 'UTF-8');
		@ini_set('mssql.textlimit', 2147483647);
		@ini_set('mssql.textsize', 2147483647);

		$this->db_connect_id = ($this->persistency) ? @mssql_pconnect($this->server, $this->user, $sqlpassword, $new_link) : @mssql_connect($this->server, $this->user, $sqlpassword, $new_link);

		return ($this->db_connect_id) ? $this->db_connect_id : $this->sql_error('');
	}

	/**
	 * Select a database
	 *
	 * @param string $dbname
	 */
	function sql_select_db($dbname)
	{
		return @mssql_select_db($dbname, $this->db_connect_id);
	}

	/**
	 * Updates value of a sequence.
	 * Does nothing in this dbal.
	 */
	public function update_sequence($sequence_name, $value)
	{
	}
}
