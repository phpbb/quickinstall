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
* MySQL dbal extension
* @package dbal
*/
class dbal_mysql_qi extends \phpbb\db\driver\mysql
{
	public $mysql_version;

	/**
	* Connect to server
	*/
	public function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false, $new_link = false)
	{
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->server = $sqlserver . (($port) ? ':' . $port : '');
		$this->dbname = $database;

		$this->sql_layer = 'mysql4';

		$this->db_connect_id = ($this->persistency) ? @mysql_pconnect($this->server, $this->user, $sqlpassword, $new_link) : @mysql_connect($this->server, $this->user, $sqlpassword, $new_link);

		if ($this->db_connect_id)
		{
			// Determine what version we are using and if it natively supports UNICODE
			$this->mysql_version = $this->sql_server_info(true);

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
	 * @return bool
	 */
	public function sql_select_db($dbname)
	{
		return @mysql_select_db($dbname, $this->db_connect_id);
	}
}
