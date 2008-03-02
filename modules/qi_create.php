<?php
/**
*
* @package quickinstall
* @version $Id$
* @copyright (c) 2007 eviL3
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
	function qi_create()
	{
		global $db, $user, $auth;
		global $quickinstall_path, $phpbb_root_path, $phpEx, $config, $qi_config, $available_dbms, $msg_title;

		$config = array_merge($config, array(
			'rand_seed'				=> md5(mt_rand()),
			'rand_seed_last_update'	=> time(),
		));

		// load installer lang
		qi::add_lang('install');

		// request variables
		$dbname			= htmlspecialchars_decode(request_var('dbname', '', true));
		$redirect		= request_var('redirect', true);
		$drop_db		= request_var('drop_db', false);
		$delete_files	= request_var('delete_files', false);
		$include_mods	= request_var('include_mods', array(0 => ''), true);
		foreach (array('site_name', 'site_desc', 'table_prefix', 'admin_name', 'admin_pass') as $r)
		{
			if ($_r = request_var($r, '', true))
			{
				$qi_config[$r] = $_r;
			}
		}

		// overwrite some of them ;)
		$user->lang = array_merge($user->lang, array(
			'CONFIG_SITE_DESC'	=> $qi_config['site_desc'],
			'CONFIG_SITENAME'	=> $qi_config['site_name'],
		));

		// if "none" is selected, reset
		if (in_array($user->lang['NONE'], $include_mods))
		{
			$include_mods = array();
		}

		// smaller ^^
		list($dbms, $table_prefix) = array(&$qi_config['dbms'], &$qi_config['table_prefix']);

		if (!$dbname)
		{
			trigger_error('NO_DB');
		}

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

		// If mysql is chosen, we need to adjust the schema filename slightly to reflect the correct version. ;)
		if ($dbms == 'mysql')
		{
			if (version_compare($db->mysql_version, '4.1.3', '>='))
			{
				$available_dbms[$dbms]['SCHEMA'] .= '_41';
			}
			else
			{
				$available_dbms[$dbms]['SCHEMA'] .= '_40';
			}
		}

		// Ok we have the db info go ahead and read in the relevant schema
		// and work on building the table
		$dbms_schema = $phpbb_root_path . 'install/schemas/' . $available_dbms[$dbms]['SCHEMA'] . '_schema.sql';

		// How should we treat this schema?
		$remove_remarks = $available_dbms[$dbms]['COMMENTS'];
		$delimiter = $available_dbms[$dbms]['DELIM'];

		$sql_query = @file_get_contents($dbms_schema);

		$sql_query = preg_replace('#phpbb_#i', $table_prefix, $sql_query);

		$remove_remarks($sql_query);

		$sql_query = split_sql_file($sql_query, $delimiter);

		foreach ($sql_query as $sql)
		{
			if (!$db->sql_query($sql))
			{
				$error = $db->sql_error();
				trigger_error($error['message']);
			}
		}
		unset($sql_query);

		// Ok tables have been built, let's fill in the basic information
		$sql_query = file_get_contents($phpbb_root_path . 'install/schemas/schema_data.sql');

		// Deal with any special comments
		switch ($dbms)
		{
			case 'mssql':
			case 'mssql_odbc':
				$sql_query = preg_replace('#\# MSSQL IDENTITY (phpbb_[a-z_]+) (ON|OFF) \##s', 'SET IDENTITY_INSERT \1 \2;', $sql_query);
			break;

			case 'postgres':
				$sql_query = preg_replace('#\# POSTGRES (BEGIN|COMMIT) \##s', '\1; ', $sql_query);
			break;
		}

		// Change prefix
		$sql_query = preg_replace('#phpbb_#i', $table_prefix, $sql_query);

		// Change language strings...
		$sql_query = preg_replace_callback('#\{L_([A-Z0-9\-_]*)\}#s', 'adjust_language_keys_callback_qi', $sql_query);

		// Since there is only one schema file we know the comment style and are able to remove it directly with remove_remarks
		remove_remarks($sql_query);
		$sql_query = split_sql_file($sql_query, ';');

		foreach ($sql_query as $sql)
		{
			if (!$db->sql_query($sql))
			{
				$error = $db->sql_error();
				trigger_error($error['message']);
			}
		}
		unset($sql_query);

		$current_time = time();
		$user_ip = (!empty($_SERVER['REMOTE_ADDR'])) ? htmlspecialchars($_SERVER['REMOTE_ADDR']) : '';

		$script_name = (!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : getenv('PHP_SELF');
		if (!$script_name)
		{
			$script_name = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');
		}

		// Replace backslashes and doubled slashes (could happen on some proxy setups)
		$script_name = str_replace(array('\\', '//'), '/', $script_name);
		$script_path = trim(dirname($script_name));

		if ($script_path !== '/')
		{
			// Adjust destination path (no trailing slash)
			if (substr($script_path, -1) == '/')
			{
				$script_path = substr($script_path, 0, -1);
			}

			$script_path = str_replace(array('../', './'), '', $script_path);

			if ($script_path[0] != '/')
			{
				$script_path = '/' . $script_path;
			}
		}

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

		$error = false;
		$search = new fulltext_native($error);

		$sql = 'SELECT post_id, post_subject, post_text, poster_id, forum_id
			FROM ' . POSTS_TABLE;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$search->index('post', $row['post_id'], $row['post_text'], $row['post_subject'], $row['poster_id'], $row['forum_id']);
		}
		$db->sql_freeresult($result);


		$this->add_modules();
		$this->add_language();

		// add bots
		$sql = 'SELECT group_id
			FROM ' . GROUPS_TABLE . "
			WHERE group_name = 'BOTS'";
		$result = $db->sql_query($sql);
		$group_id = (int) $db->sql_fetchfield('group_id');
		$db->sql_freeresult($result);

		if (!$group_id)
		{
			// If we reach this point then something has gone very wrong
			trigger_error('NO_GROUP');
		}

		if (!function_exists('user_add'))
		{
			include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		}

		foreach ($this->bot_list as $bot_name => $bot_ary)
		{
			$user_row = array(
				'user_type'				=> USER_IGNORE,
				'group_id'				=> $group_id,
				'username'				=> $bot_name,
				'user_regdate'			=> time(),
				'user_password'			=> '',
				'user_colour'			=> '9E8DA7',
				'user_email'			=> '',
				'user_lang'				=> $qi_config['default_lang'],
				'user_style'			=> 1,
				'user_timezone'			=> 0,
				'user_dateformat'		=> $user->lang['default_dateformat'],
				'user_allow_massemail'	=> 0,
			);

			$user_id = user_add($user_row);

			if (!$user_id)
			{
				// If we can't insert this user then continue to the next one to avoid inconsistant data
				trigger_error('Unable to insert bot into users table');
				continue;
			}

			$sql = 'INSERT INTO ' . BOTS_TABLE . ' ' . $db->sql_build_array('INSERT', array(
				'bot_active'	=> 1,
				'bot_name'		=> (string) $bot_name,
				'user_id'		=> (int) $user_id,
				'bot_agent'		=> (string) $bot_ary[0],
				'bot_ip'		=> (string) $bot_ary[1],
			));

			$result = $db->sql_query($sql);
		}

		// SQL done, now get those files over ;)
		$board_dir = $quickinstall_path . $qi_config['boards_dir'] . $dbname . '/';

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

		file_functions::copy_dir($quickinstall_path . 'sources/phpBB3/', $board_dir);

		if ($dbms == 'sqlite' || $dbms == 'firebird')
		{
			$qi_config['dbhost'] = $qi_config['database_prefix'] . $dbname;

			// temp remove some
			list($qi_config['database_prefix'], $dbname, $temp1, $temp2) = array('', '', &$qi_config['database_prefix'], &$dbname);
		}

		// Write to config.php ;)
		$config_data = "<?php\n";
		$config_data .= "// phpBB3 quickinstall auto-generated configuration file\n// Do not change anything in this file!\n";
		$config_data .= "\$dbms = '" . $available_dbms[$dbms]['DRIVER'] . "';\n";
		$config_data .= "\$dbhost = '{$qi_config['dbhost']}';\n";
		$config_data .= "\$dbport = '{$qi_config['dbport']}';\n";
		$config_data .= "\$dbname = '{$qi_config['database_prefix']}$dbname';\n";
		$config_data .= "\$dbuser = '{$qi_config['dbuser']}';\n";
		$config_data .= "\$dbpasswd = '{$qi_config['dbpasswd']}';\n\n";
		$config_data .= "\$table_prefix = '$table_prefix';\n";
		$config_data .= "\$acm_type = 'file';\n";
		$config_data .= "@define('PHPBB_INSTALLED', true);\n";
		$config_data .= "@define('DEBUG', true);\n";
		$config_data .= "@define('DEBUG_EXTRA', true);\n";
		$config_data .= '?' . '>'; // Done this to prevent highlighting editors getting confused!
		file_put_contents($board_dir . 'config.' . $phpEx, $config_data);

		if ($dbms == 'sqlite' || $dbms == 'firebird')
		{
			// and now restore
			list($qi_config['database_prefix'], $dbname) = array(&$temp1, &$temp2);
		}

		// clean up
		file_functions::delete_files($board_dir, array('Thumbs.db', 'DS_Store', 'CVS', '.svn'));

		// remove install dir
		file_functions::delete_dir($board_dir . 'install/');

		// now our custom mods
		if (sizeof($include_mods))
		{
			foreach ($include_mods as $mod)
			{
				file_functions::copy_dir($quickinstall_path . 'sources/mods/' . $mod . '/', $board_dir);
			}
		}

		// login
		$user->session_begin();
		$auth->login($qi_config['admin_name'], $qi_config['admin_pass'], false, true, true);

		// add log entry :D
		$user->ip = &$user_ip;
		add_log('admin', 'LOG_INSTALL_INSTALLED', 'QuickInstall');

		if ($dbms == 'sqlite' || $dbms == 'firebird')
		{
			// copy the temp db over
			file_functions::copy_file($quickinstall_path . 'cache/' . $qi_config['database_prefix'] . $dbname, $board_dir . $qi_config['database_prefix'] . $dbname);
			$db->sql_select_db($board_dir . $qi_config['database_prefix'] . $dbname);
		}

		// don't forget our hooks
		if ($dh = opendir($quickinstall_path . 'includes/hooks/'))
		{
			while (false !== ($file = readdir($dh)))
			{
				if (!preg_match('#hook_(.*)\.' . preg_quote($phpEx, '#') . '#s', $file))
				{
					continue;
				}

				@include($quickinstall_path . 'includes/hooks/' . $file);
			}
			closedir($dh);
		}

		// if he wants to be redirected, redirect him
		if ($redirect)
		{
			qi::redirect($board_dir);
		}

		// and now spit out the awful "error" :)
		$msg_title = 'SUCCESS';
		trigger_error($user->lang['BOARD_CREATED'] . '<br /><br />' . sprintf($user->lang['VISIT_BOARD'], $board_dir));
	}

	/**
	* Populate the module tables (taken from install_install)
	*/
	function add_modules()
	{
		global $db, $phpbb_root_path, $phpEx;

		include_once($phpbb_root_path . 'includes/acp/acp_modules.' . $phpEx);

		$_module = &new acp_modules();
		$module_classes = array('acp', 'mcp', 'ucp');

		// Add categories
		foreach ($module_classes as $module_class)
		{
			$categories = array();

			// Set the module class
			$_module->module_class = $module_class;

			foreach ($this->module_categories[$module_class] as $cat_name => $subs)
			{
				$module_data = array(
					'module_basename'	=> '',
					'module_enabled'	=> 1,
					'module_display'	=> 1,
					'parent_id'			=> 0,
					'module_class'		=> $module_class,
					'module_langname'	=> $cat_name,
					'module_mode'		=> '',
					'module_auth'		=> '',
				);

				// Add category
				$_module->update_module_data($module_data, true);

				// Check for last sql error happened
				if ($db->sql_error_triggered)
				{
					$error = $db->sql_error($db->sql_error_sql);
					trigger_error($error['message']);
				}

				$categories[$cat_name]['id'] = (int) $module_data['module_id'];
				$categories[$cat_name]['parent_id'] = 0;

				// Create sub-categories...
				if (is_array($subs))
				{
					foreach ($subs as $level2_name)
					{
						$module_data = array(
							'module_basename'	=> '',
							'module_enabled'	=> 1,
							'module_display'	=> 1,
							'parent_id'			=> (int) $categories[$cat_name]['id'],
							'module_class'		=> $module_class,
							'module_langname'	=> $level2_name,
							'module_mode'		=> '',
							'module_auth'		=> '',
						);

						$_module->update_module_data($module_data, true);

						// Check for last sql error happened
						if ($db->sql_error_triggered)
						{
							$error = $db->sql_error($db->sql_error_sql);
							trigger_error($error['message']);
						}

						$categories[$level2_name]['id'] = (int) $module_data['module_id'];
						$categories[$level2_name]['parent_id'] = (int) $categories[$cat_name]['id'];
					}
				}
			}

			// Get the modules we want to add... returned sorted by name
			$module_info = $_module->get_module_infos('', $module_class);

			foreach ($module_info as $module_basename => $fileinfo)
			{
				foreach ($fileinfo['modes'] as $module_mode => $row)
				{
					foreach ($row['cat'] as $cat_name)
					{
						if (!isset($categories[$cat_name]))
						{
							continue;
						}
						$module_data = array(
							'module_basename'	=> $module_basename,
							'module_enabled'	=> 1,
							'module_display'	=> (isset($row['display'])) ? $row['display'] : 1,
							'parent_id'			=> (int) $categories[$cat_name]['id'],
							'module_class'		=> $module_class,
							'module_langname'	=> $row['title'],
							'module_mode'		=> $module_mode,
							'module_auth'		=> $row['auth'],
						);

						$_module->update_module_data($module_data, true);

						// Check for last sql error happened
						if ($db->sql_error_triggered)
						{
							$error = $db->sql_error($db->sql_error_sql);
							trigger_error($error['message']);
						}
					}
				}
			}

			// Move some of the modules around since the code above will put them in the wrong place
			if ($module_class == 'acp')
			{
				// Move main module 4 up...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'main'
						AND module_class = 'acp'
						AND module_mode = 'main'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_up', 4);

				// Move permissions intro screen module 4 up...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'permissions'
						AND module_class = 'acp'
						AND module_mode = 'intro'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_up', 4);

				// Move manage users screen module 5 up...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'users'
						AND module_class = 'acp'
						AND module_mode = 'overview'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_up', 5);
			}

			if ($module_class == 'ucp')
			{
				// Move attachment module 4 down...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'attachments'
						AND module_class = 'ucp'
						AND module_mode = 'attachments'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_down', 4);
			}

			// And now for the special ones
			// (these are modules which appear in multiple categories and thus get added manually to some for more control)
			if (isset($this->module_extras[$module_class]))
			{
				foreach ($this->module_extras[$module_class] as $cat_name => $mods)
				{
					$sql = 'SELECT module_id, left_id, right_id
						FROM ' . MODULES_TABLE . "
						WHERE module_langname = '" . $db->sql_escape($cat_name) . "'
							AND module_class = '" . $db->sql_escape($module_class) . "'";
					$result = $db->sql_query_limit($sql, 1);
					$row2 = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					foreach ($mods as $mod_name)
					{
						$sql = 'SELECT *
							FROM ' . MODULES_TABLE . "
							WHERE module_langname = '" . $db->sql_escape($mod_name) . "'
								AND module_class = '" . $db->sql_escape($module_class) . "'
								AND module_basename <> ''";
						$result = $db->sql_query_limit($sql, 1);
						$row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						$module_data = array(
							'module_basename'	=> $row['module_basename'],
							'module_enabled'	=> $row['module_enabled'],
							'module_display'	=> $row['module_display'],
							'parent_id'			=> (int) $row2['module_id'],
							'module_class'		=> $row['module_class'],
							'module_langname'	=> $row['module_langname'],
							'module_mode'		=> $row['module_mode'],
							'module_auth'		=> $row['module_auth'],
						);

						$_module->update_module_data($module_data, true);

						// Check for last sql error happened
						if ($db->sql_error_triggered)
						{
							$error = $db->sql_error($db->sql_error_sql);
							trigger_error($error['message']);
						}
					}
				}
			}

			$_module->remove_cache_file();
		}
	}

	/**
	* Populate the language tables (taken from install_install)
	*/
	function add_language()
	{
		global $db, $phpbb_root_path, $phpEx;

		$dir = @opendir($phpbb_root_path . 'language');

		if (!$dir)
		{
			trigger_error('Unable to access the language directory');
		}

		while (($file = readdir($dir)) !== false)
		{
			$path = $phpbb_root_path . 'language/' . $file;

			if ($file == '.' || $file == '..' || is_link($path) || is_file($path) || $file == 'CVS')
			{
				continue;
			}

			if (is_dir($path) && file_exists($path . '/iso.txt'))
			{
				$lang_file = file("{$phpbb_root_path}language/$file/iso.txt");

				$lang_pack = array(
					'lang_iso'			=> basename($path),
					'lang_dir'			=> basename($path),
					'lang_english_name'	=> trim(htmlspecialchars($lang_file[0])),
					'lang_local_name'	=> trim(htmlspecialchars($lang_file[1], ENT_COMPAT, 'UTF-8')),
					'lang_author'		=> trim(htmlspecialchars($lang_file[2], ENT_COMPAT, 'UTF-8')),
				);

				$db->sql_query('INSERT INTO ' . LANG_TABLE . ' ' . $db->sql_build_array('INSERT', $lang_pack));

				if ($db->sql_error_triggered)
				{
					$error = $db->sql_error($db->sql_error_sql);
					trigger_error($error['message']);
				}

				$valid_localized = array(
					'icon_back_top', 'icon_contact_aim', 'icon_contact_email', 'icon_contact_icq', 'icon_contact_jabber', 'icon_contact_msnm', 'icon_contact_pm', 'icon_contact_yahoo', 'icon_contact_www', 'icon_post_delete', 'icon_post_edit', 'icon_post_info', 'icon_post_quote', 'icon_post_report', 'icon_user_online', 'icon_user_offline', 'icon_user_profile', 'icon_user_search', 'icon_user_warn', 'button_pm_forward', 'button_pm_new', 'button_pm_reply', 'button_topic_locked', 'button_topic_new', 'button_topic_reply',
				);

				$sql_ary = array();

				$sql = 'SELECT *
					FROM ' . STYLES_IMAGESET_TABLE;
				$result = $db->sql_query($sql);

				while ($imageset_row = $db->sql_fetchrow($result))
				{
					if (@file_exists("{$phpbb_root_path}styles/{$imageset_row['imageset_path']}/imageset/{$lang_pack['lang_iso']}/imageset.cfg"))
					{
						$cfg_data_imageset_data = parse_cfg_file("{$phpbb_root_path}styles/{$imageset_row['imageset_path']}/imageset/{$lang_pack['lang_iso']}/imageset.cfg");
						foreach ($cfg_data_imageset_data as $image_name => $value)
						{
							if (strpos($value, '*') !== false)
							{
								if (substr($value, -1, 1) === '*')
								{
									list($image_filename, $image_height) = explode('*', $value);
									$image_width = 0;
								}
								else
								{
									list($image_filename, $image_height, $image_width) = explode('*', $value);
								}
							}
							else
							{
								$image_filename = $value;
								$image_height = $image_width = 0;
							}

							if (strpos($image_name, 'img_') === 0 && $image_filename)
							{
								$image_name = substr($image_name, 4);
								if (in_array($image_name, $valid_localized))
								{
									$sql_ary[] = array(
										'image_name'		=> (string) $image_name,
										'image_filename'	=> (string) $image_filename,
										'image_height'		=> (int) $image_height,
										'image_width'		=> (int) $image_width,
										'imageset_id'		=> (int) $imageset_row['imageset_id'],
										'image_lang'		=> (string) $lang_pack['lang_iso'],
									);
								}
							}
						}
					}
				}
				$db->sql_freeresult($result);

				if (sizeof($sql_ary))
				{
					$db->sql_multi_insert(STYLES_IMAGESET_DATA_TABLE, $sql_ary);

					if ($db->sql_error_triggered)
					{
						$error = $db->sql_error($db->sql_error_sql);
						trigger_error($error['message']);
					}
				}
			}
		}
		closedir($dir);
	}

	/**
	 * (taken from install_install)
	 */
	var $bot_list = array(
		'AdsBot [Google]'			=> array('AdsBot-Google', ''),
		'Alexa [Bot]'				=> array('ia_archiver', ''),
		'Alta Vista [Bot]'			=> array('Scooter/', ''),
		'Ask Jeeves [Bot]'			=> array('Ask Jeeves', ''),
		'Baidu [Spider]'			=> array('Baiduspider+(', ''),
		'Exabot [Bot]'				=> array('Exabot/', ''),
		'FAST Enterprise [Crawler]'	=> array('FAST Enterprise Crawler', ''),
		'FAST WebCrawler [Crawler]'	=> array('FAST-WebCrawler/', ''),
		'Francis [Bot]'				=> array('http://www.neomo.de/', ''),
		'Gigabot [Bot]'				=> array('Gigabot/', ''),
		'Google Adsense [Bot]'		=> array('Mediapartners-Google/', ''),
		'Google Desktop'			=> array('Google Desktop', ''),
		'Google Feedfetcher'		=> array('Feedfetcher-Google', ''),
		'Google [Bot]'				=> array('Googlebot', ''),
		'Heise IT-Markt [Crawler]'	=> array('heise-IT-Markt-Crawler', ''),
		'Heritrix [Crawler]'		=> array('heritrix/1.', ''),
		'IBM Research [Bot]'		=> array('ibm.com/cs/crawler', ''),
		'ICCrawler - ICjobs'		=> array('ICCrawler - ICjobs', ''),
		'ichiro [Crawler]'			=> array('ichiro/2', ''),
		'Majestic-12 [Bot]'			=> array('MJ12bot/', ''),
		'Metager [Bot]'				=> array('MetagerBot/', ''),
		'MSN NewsBlogs'				=> array('msnbot-NewsBlogs/', ''),
		'MSN [Bot]'					=> array('msnbot/', ''),
		'MSNbot Media'				=> array('msnbot-media/', ''),
		'NG-Search [Bot]'			=> array('NG-Search/', ''),
		'Nutch [Bot]'				=> array('http://lucene.apache.org/nutch/', ''),
		'Nutch/CVS [Bot]'			=> array('NutchCVS/', ''),
		'OmniExplorer [Bot]'		=> array('OmniExplorer_Bot/', ''),
		'Online link [Validator]'	=> array('online link validator', ''),
		'psbot [Picsearch]'			=> array('psbot/0', ''),
		'Seekport [Bot]'			=> array('Seekbot/', ''),
		'Sensis [Crawler]'			=> array('Sensis Web Crawler', ''),
		'SEO Crawler'				=> array('SEO search Crawler/', ''),
		'Seoma [Crawler]'			=> array('Seoma [SEO Crawler]', ''),
		'SEOSearch [Crawler]'		=> array('SEOsearch/', ''),
		'Snappy [Bot]'				=> array('Snappy/1.1 ( http://www.urltrends.com/ )', ''),
		'Steeler [Crawler]'			=> array('http://www.tkl.iis.u-tokyo.ac.jp/~crawler/', ''),
		'Synoo [Bot]'				=> array('SynooBot/', ''),
		'Telekom [Bot]'				=> array('crawleradmin.t-info@telekom.de', ''),
		'TurnitinBot [Bot]'			=> array('TurnitinBot/', ''),
		'Voyager [Bot]'				=> array('voyager/1.0', ''),
		'W3 [Sitesearch]'			=> array('W3 SiteSearch Crawler', ''),
		'W3C [Linkcheck]'			=> array('W3C-checklink/', ''),
		'W3C [Validator]'			=> array('W3C_*Validator', ''),
		'WiseNut [Bot]'				=> array('http://www.WISEnutbot.com', ''),
		'YaCy [Bot]'				=> array('yacybot', ''),
		'Yahoo MMCrawler [Bot]'		=> array('Yahoo-MMCrawler/', ''),
		'Yahoo Slurp [Bot]'			=> array('Yahoo! DE Slurp', ''),
		'Yahoo [Bot]'				=> array('Yahoo! Slurp', ''),
		'YahooSeeker [Bot]'			=> array('YahooSeeker/', ''),
	);

	/**
	 * (taken from install_install)
	 */
	var $module_categories = array(
		'acp'	=> array(
			'ACP_CAT_GENERAL'		=> array(
				'ACP_QUICK_ACCESS',
				'ACP_BOARD_CONFIGURATION',
				'ACP_CLIENT_COMMUNICATION',
				'ACP_SERVER_CONFIGURATION',
			),
			'ACP_CAT_FORUMS'		=> array(
				'ACP_MANAGE_FORUMS',
				'ACP_FORUM_BASED_PERMISSIONS',
			),
			'ACP_CAT_POSTING'		=> array(
				'ACP_MESSAGES',
				'ACP_ATTACHMENTS',
			),
			'ACP_CAT_USERGROUP'		=> array(
				'ACP_CAT_USERS',
				'ACP_GROUPS',
				'ACP_USER_SECURITY',
			),
			'ACP_CAT_PERMISSIONS'	=> array(
				'ACP_GLOBAL_PERMISSIONS',
				'ACP_FORUM_BASED_PERMISSIONS',
				'ACP_PERMISSION_ROLES',
				'ACP_PERMISSION_MASKS',
			),
			'ACP_CAT_STYLES'		=> array(
				'ACP_STYLE_MANAGEMENT',
				'ACP_STYLE_COMPONENTS',
			),
			'ACP_CAT_MAINTENANCE'	=> array(
				'ACP_FORUM_LOGS',
				'ACP_CAT_DATABASE',
			),
			'ACP_CAT_SYSTEM'		=> array(
				'ACP_AUTOMATION',
				'ACP_GENERAL_TASKS',
				'ACP_MODULE_MANAGEMENT',
			),
			'ACP_CAT_DOT_MODS'		=> null,
		),
		'mcp'	=> array(
			'MCP_MAIN'		=> null,
			'MCP_QUEUE'		=> null,
			'MCP_REPORTS'	=> null,
			'MCP_NOTES'		=> null,
			'MCP_WARN'		=> null,
			'MCP_LOGS'		=> null,
			'MCP_BAN'		=> null,
		),
		'ucp'	=> array(
			'UCP_MAIN'			=> null,
			'UCP_PROFILE'		=> null,
			'UCP_PREFS'			=> null,
			'UCP_PM'			=> null,
			'UCP_USERGROUPS'	=> null,
			'UCP_ZEBRA'			=> null,
		),
	);

	/**
	 * (taken from install_install)
	 */
	var $module_extras = array(
		'acp'	=> array(
			'ACP_QUICK_ACCESS' => array(
				'ACP_MANAGE_USERS',
				'ACP_GROUPS_MANAGE',
				'ACP_MANAGE_FORUMS',
				'ACP_MOD_LOGS',
				'ACP_BOTS',
				'ACP_PHP_INFO',
			),
			'ACP_FORUM_BASED_PERMISSIONS' => array(
				'ACP_FORUM_PERMISSIONS',
				'ACP_FORUM_MODERATORS',
				'ACP_USERS_FORUM_PERMISSIONS',
				'ACP_GROUPS_FORUM_PERMISSIONS',
			),
		),
	);
}

?>