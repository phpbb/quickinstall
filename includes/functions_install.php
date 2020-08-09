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
 * Dummy p_master
 */
class p_master_dummy
{
	public $module_url;

	public function db_error($message, $sql = false, $line = false, $file = false)
	{
		trigger_error($message);
	}

	public function error($message, $sql = false, $line = false, $file = false)
	{
		trigger_error($message);
	}

	public function redirect($url)
	{
		qi::redirect($url);
	}
}

/**
 * Dummy module
 */
class module
{
}

function get_db_tools($db)
{
	if (defined('PHPBB_32'))
	{
		$factory = new \phpbb\db\tools\factory();
		return $factory->get($db);
	}

	return new \phpbb\db\tools($db);
}

function load_schema($install_path = '', $install_dbms = false)
{
	if (defined('PHPBB_31'))
	{
		load_schema_31($install_path, $install_dbms);
	}
	else
	{
		load_schema_30($install_path, $install_dbms);
	}
}

function load_schema_31($install_path = '', $install_dbms = false)
{
	global $db, $settings, $table_prefix, $phpbb_root_path, $phpEx;

	static $available_dbms = false;

	if ($install_dbms === false)
	{
		$dbms = $settings->get_config('dbms', '');
		$install_dbms = $dbms;
	}

	if (!$available_dbms)
	{
		$available_dbms = qi_get_available_dbms($install_dbms);

		// If mysql is chosen, we need to adjust the schema filename slightly to reflect the correct version. ;)
		if ($install_dbms == 'mysql')
		{
			if (version_compare($db->sql_server_info(true), '4.1.3', '>='))
			{
				$available_dbms[$install_dbms]['SCHEMA'] .= '_41';
			}
			else
			{
				$available_dbms[$install_dbms]['SCHEMA'] .= '_40';
			}
		}
	}

	// Ok we have the db info go ahead and read in the relevant schema
	// and work on building the table
	$dbms_schema = $install_path . $available_dbms[$install_dbms]['SCHEMA'] . '_schema.sql';

	// How should we treat this schema?
	$delimiter = $available_dbms[$install_dbms]['DELIM'];

	if (file_exists($dbms_schema))
	{
		$sql_query = file_get_contents($dbms_schema);
		$sql_query = preg_replace('#phpbb_#i', $table_prefix, $sql_query);
		$sql_query = qi_remove_comments($sql_query);
		$sql_query = qi_split_sql_file($sql_query, $delimiter);

		foreach ($sql_query as $sql)
		{
			$db->sql_query($sql);
		}
		unset($sql_query);
	}

	// Ok we have the db info go ahead and work on building the table
	if (file_exists($install_path . 'schema.json'))
	{
		$db_table_schema = file_get_contents($install_path . 'schema.json');
		$db_table_schema = json_decode($db_table_schema, true);
	}
	else
	{
		$table_prefix = 'phpbb_';

		if (!defined('CONFIG_TABLE'))
		{
			// We need to include the constants file for the table constants
			// when we generate the schema from the migration files.
			include($phpbb_root_path . 'includes/constants.' . $phpEx);
		}

		if (defined('PHPBB_40'))
		{
			$finder = new \phpbb\finder($phpbb_root_path, null, $phpEx);
		}
		else
		{
			$finder = new \phpbb\finder(new \phpbb\filesystem(), $phpbb_root_path, null, $phpEx);
		}

		$classes = $finder->core_path('phpbb/db/migration/data/')
			->get_classes();

		if (!file_exists($phpbb_root_path . 'phpbb\db\driver\sqlite.' . $phpEx))
		{
			$sqlite_db = new \phpbb\db\driver\sqlite3();
		}
		else
		{
			$sqlite_db = new \phpbb\db\driver\sqlite();
		}

		$db_tools = get_db_tools($sqlite_db);

		$args = array($classes, new \phpbb\config\config(array()), $sqlite_db, $db_tools, $phpbb_root_path, $phpEx, $table_prefix);
		if (defined('PHPBB_40'))
		{
			$tables_data = \Symfony\Component\Yaml\Yaml::parseFile($phpbb_root_path . '/config/default/container/tables.yml');
			$tables = [];

			foreach ($tables_data['parameters'] as $parameter => $table)
			{
				$tables[str_replace('tables.', '', $parameter)] = str_replace('%core.table_prefix%', $table_prefix, $table);
			}

			$args[] = $tables;
		}

		$class = new ReflectionClass('\\phpbb\\db\\migration\\schema_generator');
		$schema_generator = $class->newInstanceArgs($args);

		$db_table_schema = $schema_generator->get_schema();
	}

	if (!defined('CONFIG_TABLE'))
	{
		// CONFIG_TABLE is required by sql_create_index() to check the
		// length of index names. However table_prefix is not defined
		// here yet, so we need to create the constant ourselves.
		define('CONFIG_TABLE', $table_prefix . 'config');
	}

	$db_tools = get_db_tools($db);
	foreach ($db_table_schema as $table_name => $table_data)
	{
		$db_tools->sql_create_table(
			$table_prefix . substr($table_name, 6),
			$table_data
		);
	}

	// Ok tables have been built, let's fill in the basic information
	$sql_query = file_get_contents($install_path . 'schema_data.sql');

	// Deal with any special comments and characters
	switch ($install_dbms)
	{
		case 'mssql':
		case 'mssql_odbc':
		case 'mssqlnative':
			$sql_query = preg_replace('#\# MSSQL IDENTITY (phpbb_[a-z_]+) (ON|OFF) \##s', 'SET IDENTITY_INSERT \1 \2;', $sql_query);
			break;

		case 'postgres':
			$sql_query = preg_replace('#\# POSTGRES (BEGIN|COMMIT) \##s', '\1; ', $sql_query);
			break;

		case 'mysql':
		case 'mysqli':
			$sql_query = str_replace('\\', '\\\\', $sql_query);
			break;
	}

	// Change prefix
	$sql_query = preg_replace('# phpbb_([^\s]*) #i', ' ' . $table_prefix . '\1 ', $sql_query);

	// Change language strings...
	$sql_query = preg_replace_callback('#\{L_([A-Z0-9\-_]*)\}#s', 'qi_adjust_language_keys_callback', $sql_query);

	$sql_query = qi_remove_comments($sql_query);
	$sql_query = qi_split_sql_file($sql_query, ';');

	foreach ($sql_query as $sql)
	{
		$db->sql_query($sql);
	}
	unset($sql_query);
}

/**
 * Load a schema (and execute)
 *
 * @param string $install_path
 */
function load_schema_30($install_path = '', $install_dbms = false)
{
	global $settings, $db, $table_prefix;

	static $available_dbms = false;

	if ($install_dbms === false)
	{
		$dbms = $settings->get_config('dbms', '');
		$install_dbms = $dbms;
	}

	if (!function_exists('get_available_dbms') && !defined('PHPBB_32'))
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'includes/functions_install.' . $phpEx);
	}

	if (!$available_dbms)
	{
		$available_dbms = qi_get_available_dbms($install_dbms);

		if ($install_dbms == 'mysql')
		{
			if (version_compare($db->mysql_version, '4.1.3', '>='))
			{
				$available_dbms[$install_dbms]['SCHEMA'] .= '_41';
			}
			else
			{
				$available_dbms[$install_dbms]['SCHEMA'] .= '_40';
			}
		}
	}

	$remove_remarks = $available_dbms[$install_dbms]['COMMENTS'];
	$delimiter = $available_dbms[$install_dbms]['DELIM'];

	$dbms_schema = $install_path . $available_dbms[$install_dbms]['SCHEMA'] . '_schema.sql';

	if (file_exists($dbms_schema))
	{
		$sql_query = @file_get_contents($dbms_schema);

		$sql_query = preg_replace('#phpbb_#i', $table_prefix, $sql_query);

		$remove_remarks($sql_query);

		$sql_query = qi_split_sql_file($sql_query, $delimiter);

		foreach ($sql_query as $sql)
		{
			$db->sql_query($sql);
		}
		unset($sql_query);
	}

	if (file_exists($install_path . 'schema_data.sql'))
	{
		$sql_query = file_get_contents($install_path . 'schema_data.sql');

		switch ($install_dbms)
		{
			case 'mssql':
			case 'mssql_odbc':
				$sql_query = preg_replace('#\# MSSQL IDENTITY (phpbb_[a-z_]+) (ON|OFF) \##s', 'SET IDENTITY_INSERT \1 \2;', $sql_query);
			break;

			case 'postgres':
				$sql_query = preg_replace('#\# POSTGRES (BEGIN|COMMIT) \##s', '\1; ', $sql_query);
			break;
		}

		$sql_query = preg_replace('# phpbb_([^\s]*) #i', ' ' . $table_prefix . '\1 ', $sql_query);
		$sql_query = preg_replace_callback('#\{L_([A-Z0-9\-_]*)\}#s', 'adjust_language_keys_callback', $sql_query);

		remove_remarks($sql_query);

		$sql_query = qi_split_sql_file($sql_query, ';');

		foreach ($sql_query as $sql)
		{
			$db->sql_query($sql);
		}
		unset($sql_query);
	}
}

function qi_split_sql_file($sql, $delimiter)
{
	if (defined('PHPBB_32'))
	{
		global $phpbb_root_path;
		$database = new \phpbb\install\helper\database(new \phpbb\filesystem\filesystem(), $phpbb_root_path);
		return call_user_func(array($database, 'split_sql_file'), $sql, $delimiter);
	}
	else
	{
		return call_user_func('split_sql_file', $sql, $delimiter);
	}
}

function qi_remove_comments($input)
{
	if (defined('PHPBB_32'))
	{
		global $phpbb_root_path;
		$database = new \phpbb\install\helper\database(new \phpbb\filesystem\filesystem(), $phpbb_root_path);
		return call_user_func(array($database, 'remove_comments'), $input);
	}
	else
	{
		return call_user_func('phpbb_remove_comments', $input);
	}
}

function qi_adjust_language_keys_callback($matches)
{
	if (!empty($matches[1]))
	{
		global $lang, $db;

		return (!empty($lang[$matches[1]])) ? $db->sql_escape($lang[$matches[1]]) : $db->sql_escape($matches[1]);
	}
}

