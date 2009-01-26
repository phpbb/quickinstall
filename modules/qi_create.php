<?php
/**
*
* @package quickinstall
* @version $Id$
* @copyright (c) 2007, 2008 eviL3
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
 * qi_create module
 */
class qi_create
{
	public function __construct()
	{
		global $db, $user, $auth, $cache;
		global $quickinstall_path, $phpbb_root_path, $phpEx, $config, $qi_config, $msg_title;

		// include installation functions
		include($quickinstall_path . 'includes/functions_install.' . $phpEx);

		$config = array_merge($config, array(
			'rand_seed'				=> md5(mt_rand()),
			'rand_seed_last_update'	=> time(),
		));

		// load installer lang
		qi::add_lang('phpbb');

		// phpbb's install uses $lang instead of $user->lang
		// need to use $GLOBALS here
		$GLOBALS['lang'] = &$user->lang;
		global $lang;

		// request variables
		$dbname			= htmlspecialchars_decode(request_var('dbname', '', true));
		$redirect		= request_var('redirect', true);
		$drop_db		= request_var('drop_db', false);
		$delete_files	= request_var('delete_files', false);
		$blinky			= request_var('blinky', false);
		$alt_env		= request_var('alt_env', '');
		foreach (array('site_name', 'site_desc', 'table_prefix', 'admin_name', 'admin_pass') as $r)
		{
			if ($_r = request_var($r, '', true))
			{
				$qi_config[$r] = $_r;
			}
		}
		
		if ($alt_env !== '' && !file_exists($quickinstall_path . 'sources/phpBB3_alt/' . $alt_env))
		{
			trigger_error('NO_ALT_ENV');
		}

		// overwrite some of them ;)
		$user->lang = array_merge($user->lang, array(
			'CONFIG_SITE_DESC'	=> $qi_config['site_desc'],
			'CONFIG_SITENAME'	=> $qi_config['site_name'],
		));

		// smaller ^^
		list($dbms, $table_prefix) = array(&$qi_config['dbms'], &$qi_config['table_prefix']);

		// check if we have a board db (and folder) name
		if (!$dbname)
		{
			trigger_error('NO_DB');
		}

		// copy all of our files
		$board_dir = $quickinstall_path . 'boards/' . $dbname . '/';

		if (file_exists($board_dir))
		{
			if ($delete_files)
			{
				file_functions::delete_dir($board_dir);
			}
			else
			{
				trigger_error(sprintf($user->lang['DIR_EXISTS'], $board_dir));
			}
		}

		file_functions::copy_dir($quickinstall_path . 'sources/' . ($alt_env === '' ? 'phpBB3/' : "phpBB3_alt/$alt_env/"), $board_dir);

		// copy extra files
		file_functions::copy_dir($quickinstall_path . 'sources/extra/', $board_dir);

		if ($dbms == 'sqlite' || $dbms == 'firebird')
		{
			$qi_config['dbhost'] = $qi_config['database_prefix'] . $dbname;

			// temp remove some
			list($qi_config['database_prefix'], $dbname, $temp1, $temp2) = array('', '', &$qi_config['database_prefix'], &$dbname);
		}

		// Write to config.php ;)
		$config_data = "<?php\n";
		$config_data .= "// phpBB 3.0.x auto-generated configuration file\n// Do not change anything in this file!\n";
		$config_data_array = array(
			'dbms'				=> $dbms,
			'dbhost'			=> $qi_config['dbhost'],
			'dbport'			=> $qi_config['dbport'],
			'dbname'			=> $qi_config['database_prefix'] . $dbname,
			'dbuser'			=> $qi_config['dbuser'],
			'dbpasswd'			=> htmlspecialchars_decode($qi_config['dbpasswd']),
			'table_prefix'		=> $table_prefix,
			'acm_type'			=> 'file',
			'load_extensions'	=> '',
		);

		foreach ($config_data_array as $key => $value)
		{
			$config_data .= "\${$key} = '" . str_replace("'", "\\'", str_replace('\\', '\\\\', $value)) . "';\n";
		}
		unset($config_data_array);

		$config_data .= "\n@define('PHPBB_INSTALLED', true);\n";
		$config_data .= "@define('DEBUG', true);\n";
		$config_data .= "@define('DEBUG_EXTRA', true);\n";
		$config_data .= '?' . '>'; // Done this to prevent highlighting editors getting confused!
		file_put_contents($board_dir . 'config.' . $phpEx, $config_data);

		if ($dbms == 'sqlite' || $dbms == 'firebird')
		{
			// and now restore
			list($qi_config['database_prefix'], $dbname) = array(&$temp1, &$temp2);
		}

		// update phpbb_root_path
		$phpbb_root_path = $board_dir;

		if ($drop_db)
		{
			$db->sql_query('DROP DATABASE IF EXISTS ' . $qi_config['database_prefix'] . $dbname);
		}

		if ($dbms == 'sqlite' || $dbms == 'firebird')
		{
			$db->sql_query('CREATE DATABASE ' . $quickinstall_path . 'cache/' . $qi_config['database_prefix'] . $dbname);
			$db->sql_select_db($quickinstall_path . 'cache/' . $qi_config['database_prefix'] . $dbname);
		}
		else
		{
			$db->sql_query('CREATE DATABASE ' . $qi_config['database_prefix'] . $dbname);
			$db->sql_select_db($qi_config['database_prefix'] . $dbname);
		}

		// include install lang fom phpbb
		qi::add_lang('install', $phpbb_root_path . 'language/' . $qi_config['default_lang'] . '/');

		// perform sql
		load_schema($phpbb_root_path . 'install/schemas/', $dbms);

		$current_time = time();
		$user_ip = (!empty($_SERVER['REMOTE_ADDR'])) ? htmlspecialchars($_SERVER['REMOTE_ADDR']) : '';

		$script_path = (!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : getenv('PHP_SELF');
		if (!$script_path)
		{
			$script_path = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');
		}

		// Replace backslashes and doubled slashes (could happen on some proxy setups)
		$script_path = str_replace(array('\\', '//'), '/', $script_path);
		$script_path = trim(dirname($script_path));

		// add the dbname to script path
		$script_path .= '/boards/' . $dbname;

		$config_ary = array(
			'board_startdate'	=> $current_time,
			'default_lang'		=> $qi_config['default_lang'],
			'server_name'		=> $qi_config['server_name'],
			'server_port'		=> $qi_config['server_port'],
			'board_email'		=> $qi_config['board_email'],
			'board_contact'		=> $qi_config['board_email'],
			'cookie_domain'		=> $qi_config['cookie_domain'],
			'default_dateformat'=> $user->lang['default_dateformat'],
			'email_enable'		=> $qi_config['email_enable'],
			'smtp_delivery'		=> $qi_config['smtp_delivery'],
			'smtp_host'			=> $qi_config['smtp_host'],
			'smtp_auth_method'	=> $qi_config['smtp_auth'],
			'smtp_username'		=> $qi_config['smtp_user'],
			'smtp_password'		=> $qi_config['smtp_pass'],
			'cookie_secure'		=> $qi_config['cookie_secure'],
			'script_path'		=> $script_path,
			'server_protocol'	=> $qi_config['server_protocol'],
			'newest_username'	=> $qi_config['admin_name'],
			'avatar_salt'		=> md5(mt_rand()),
			'cookie_name'		=> 'phpbb3_' . strtolower(gen_rand_string(5)),
		);

		if (@extension_loaded('gd') || can_load_dll('gd'))
		{
			$config_ary['captcha_gd'] = 1;
		}

		foreach ($config_ary as $config_name => $config_value)
		{
			set_config($config_name, $config_value);
		}

		// Set default config and post data, this applies to all DB's
		$sql_ary = array(
			"UPDATE {$table_prefix}users
				SET username = '" . $db->sql_escape($qi_config['admin_name']) . "', user_password='" . $db->sql_escape(md5($qi_config['admin_pass'])) . "', user_ip = '" . $db->sql_escape($user_ip) . "', user_lang = '" . $db->sql_escape($qi_config['default_lang']) . "', user_email='" . $db->sql_escape($qi_config['board_email']) . "', user_dateformat='" . $db->sql_escape($user->lang['default_dateformat']) . "', user_email_hash = " . (crc32($qi_config['board_email']) . strlen($qi_config['board_email'])) . ", username_clean = '" . $db->sql_escape(utf8_clean_string($qi_config['admin_name'])) . "'
				WHERE username = 'Admin'",

			"UPDATE {$table_prefix}moderator_cache
				SET username = '" . $db->sql_escape($qi_config['admin_name']) . "'
				WHERE username = 'Admin'",

			"UPDATE {$table_prefix}forums
				SET forum_last_poster_name = '" . $db->sql_escape($qi_config['admin_name']) . "'
				WHERE forum_last_poster_name = 'Admin'",

			"UPDATE {$table_prefix}topics
				SET topic_first_poster_name = '" . $db->sql_escape($qi_config['admin_name']) . "', topic_last_poster_name = '" . $db->sql_escape($qi_config['admin_name']) . "'
				WHERE topic_first_poster_name = 'Admin'
					OR topic_last_poster_name = 'Admin'",

			"UPDATE {$table_prefix}users
				SET user_regdate = $current_time",

			"UPDATE {$table_prefix}posts
				SET post_time = $current_time, poster_ip = '" . $db->sql_escape($user_ip) . "'",

			"UPDATE {$table_prefix}topics
				SET topic_time = $current_time, topic_last_post_time = $current_time",

			"UPDATE {$table_prefix}forums
				SET forum_last_post_time = $current_time",
		);

		foreach ($sql_ary as $sql)
		{
			if (!$db->sql_query($sql))
			{
				$error = $db->sql_error();
				trigger_error($error['message']);
			}
		}

		// main sql is done, let's get a fresh $config array
		$sql = 'SELECT *
			FROM ' . CONFIG_TABLE;
		$result = $db->sql_query($sql);

		$config = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$config[$row['config_name']] = $row['config_value'];
		}
		$db->sql_freeresult($result);

		// no templates though :P
		$config['load_tplcompile'] = '1';

		// build search index
		include_once($phpbb_root_path . 'includes/search/fulltext_native.' . $phpEx);

		$search = new fulltext_native($error = false);

		$sql = 'SELECT post_id, post_subject, post_text, poster_id, forum_id
			FROM ' . POSTS_TABLE;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$search->index('post', $row['post_id'], $row['post_text'], $row['post_subject'], $row['poster_id'], $row['forum_id']);
		}
		$db->sql_freeresult($result);

		// extended phpbb install script
		include($phpbb_root_path . 'install/install_install.' . $phpEx);
		include($quickinstall_path . 'includes/install_install_qi.' . $phpEx);

		$install = new install_install_qi($p_master = new p_master_dummy());
		$install->set_data(array(
			'dbms'				=> $dbms,
			'dbhost'			=> $qi_config['dbhost'],
			'dbport'			=> $qi_config['dbport'],
			'dbuser'			=> $qi_config['dbuser'],
			'dbpasswd'			=> $qi_config['dbpasswd'],
			'dbname'			=> $dbname,
			'table_prefix'		=> $table_prefix,
			'default_lang'		=> $qi_config['default_lang'],
			'admin_name'		=> $qi_config['admin_name'],
			'admin_pass1'		=> $qi_config['admin_pass'],
			'admin_pass2'		=> $qi_config['admin_pass'],
			'board_email1'		=> $qi_config['board_email'],
			'board_email2'		=> $qi_config['board_email'],
			'email_enable'		=> $qi_config['email_enable'],
			'smtp_delivery'		=> $qi_config['smtp_delivery'],
			'smtp_host'			=> $qi_config['smtp_host'],
			'smtp_auth'			=> $qi_config['smtp_auth'],
			'smtp_user'			=> $qi_config['smtp_user'],
			'smtp_pass'			=> $qi_config['smtp_pass'],
			'cookie_secure'		=> $qi_config['cookie_secure'],
			'server_protocol'	=> $qi_config['server_protocol'],
			'server_name'		=> $qi_config['server_name'],
			'server_port'		=> $qi_config['server_port'],
			'script_path'		=> $script_path,
		));
		$install->add_modules(false, false);
		$install->add_language(false, false);
		$install->add_bots(false, false);

		// now blinky (easymod)
		if ($blinky)
		{
			// todo: add blinky code
			file_functions::copy_dir($quickinstall_path . 'sources/blinky/', $board_dir);

			require("{$phpbb_root_path}install/install_automod.$phpEx");
			require("{$quickinstall_path}includes/install_automod_qi.$phpEx");
			require("{$phpbb_root_path}includes/functions_convert.$phpEx");
			require("{$phpbb_root_path}includes/functions_mods.$phpEx");
			require("{$phpbb_root_path}includes/functions_transfer.$phpEx");
			
			// some stuff josh added... >_<
			global $current_version;
			$current_version = $qi_config['automod_version'];

			// add some language entries to prevent notices
			$user->lang += array(
				'FILE_EDITS'	=> '',
				'NEXT_STEP'		=> '',
			);

			$install = new install_automod_qi($p_master = new p_master_dummy());
			$install->set_data(array(
				'method'	=> '',
				'host'		=> '',
				'username'	=> '',
				'root_path'	=> '',
				'port'		=> 21,
				'timeout'	=> 10,
			));
			$install->add_config(false, false);
			load_schema($phpbb_root_path . 'install/schemas/automod/', $dbms);
			$install->add_modules(false, false);
		}

		// login
		$user->session_begin();
		$auth->login($qi_config['admin_name'], $qi_config['admin_pass'], false, true, true);

		// add log entry :D
		$user->ip = &$user_ip;
		add_log('admin', 'LOG_INSTALL_INSTALLED_QI', $qi_config['qi_version']);

		if ($dbms == 'sqlite' || $dbms == 'firebird')
		{
			// copy the temp db over
			file_functions::copy_file($quickinstall_path . 'cache/' . $qi_config['database_prefix'] . $dbname, $board_dir . $qi_config['database_prefix'] . $dbname);
			$db->sql_select_db($board_dir . $qi_config['database_prefix'] . $dbname);
		}

		// clean up
		file_functions::delete_files($board_dir, array('Thumbs.db', 'DS_Store', 'CVS', '.svn'));

		// remove install dir and develop
		file_functions::delete_dir($board_dir . 'install/');
		file_functions::delete_dir($board_dir . 'develop/');

		// purge cache
		$cache->purge();

		// if he wants to be redirected, redirect him
		if ($redirect)
		{
			qi::redirect($board_dir);
		}

		// and now spit out the awful "error" :)
		$msg_title = 'SUCCESS';
		trigger_error($user->lang['BOARD_CREATED'] . '<br /><br />' . sprintf($user->lang['VISIT_BOARD'], $board_dir));
	}
}

?>
