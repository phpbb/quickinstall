<?php
/**
*
* @package quickinstall
* @copyright (c) 2012 phpBB Group
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
* Postgres dbal extension
* @package dbal
*/
class dbal_postgres_qi extends dbal_postgres
{
	public function sql_connect($dbhost, $dbuser, $dbpass, $dbname, $dbport = false, $persistency = false, $new_link=  false)
	{
		if ($dbname === false)
		{
			// PostgreSQL requires a database to connect to.
			// postgres is the default database as described in the manual:
			// http://www.postgresql.org/docs/9.0/static/manage-ag-templatedbs.html
			$dbname = 'postgres';
		}
		return parent::sql_connect($dbhost, $dbuser, $dbpass, $dbname, $dbport, $persistency, $new_link);
	}

	/**
	 * Updates value of a sequence.
	 */
	public function update_sequence($sequence_name, $value)
	{
	      $result = $this->sql_query("select setval('$sequence_name', '$value')");
	      $this->sql_freeresult($result);
	}
}
