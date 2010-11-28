<?php
/**
*
* @package quickinstall
* @version $Id$
* @copyright (c) 2007, 2008 eviL3
* @copyright (c) 2010 Jari Kanerva (tumba25)
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
		$dbname = htmlspecialchars_decode(request_var('dbname', '', true));
		$redirect = request_var('redirect', false);
		$drop_db = request_var('drop_db', false);
		$delete_files = request_var('delete_files', false);
		$automod = request_var('automod', false);
		$make_writable = request_var('make_writable', false);
		$populate = request_var('populate', false);
		$subsilver = request_var('subsilver', 0);
		$alt_env = request_var('alt_env', '');
		$pop_data = request_var('pop_data', array('' => ''));

		// Some populate checking
		if ($populate)
		{
			if (empty($pop_data['num_users']) && empty($pop_data['num_cats']) && empty($pop_data['num_forums']) && empty($pop_data['num_topics']) && empty($pop_data['num_replies']))
			{
				// populate with nothing?
				$populate = false;
			}
			else
			{
				$pop_data['email_domain'] = trim($pop_data['email_domain']);
				if (!empty($pop_data['num_users']) && empty($pop_data['email_domain']))
				{
					trigger_error($user->lang['NEED_EMAIL_DOMAIN'], E_USER_ERROR);
				}
			}
		}

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

		// Set up our basic founder.
		$user->data['user_id'] = 2; //
		$user->data['username'] = $qi_config['admin_name'];
		$user->data['user_colour'] = 'AA0000';

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

		// Set the new board as root path.
		$board_dir = $quickinstall_path . 'boards/' . $dbname . '/';
		$phpbb_root_path = $board_dir;
		if (!defined('PHPBB_ROOT_PATH'))
		{
			define('PHPBB_ROOT_PATH', $board_dir);
		}

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

		// copy all of our files
		file_functions::copy_dir($quickinstall_path . 'sources/' . ($alt_env === '' ? 'phpBB3/' : "phpBB3_alt/$alt_env/"), $board_dir);

		if ($make_writable)
		{
			chmod($board_dir, 0777);
		}

		// copy extra files
		file_functions::copy_dir($quickinstall_path . 'sources/extra/', $board_dir);

		if ($dbms == 'sqlite' || $dbms == 'firebird')
		{
			$qi_config['dbhost'] = $qi_config['db_prefix'] . $dbname;

			// temp remove some
			list($qi_config['db_prefix'], $dbname, $temp1, $temp2) = array('', '', &$qi_config['db_prefix'], &$dbname);
		}

		// Set the new board as language path to get language files from outside phpBB
		$user->set_custom_lang_path($phpbb_root_path . 'language/');

		// Write to config.php ;)
		$config_data = "<?php\n";
		$config_data .= "// phpBB 3.0.x auto-generated configuration file\n// Do not change anything in this file!\n";
		$config_data_array = array(
			'dbms'				=> $dbms,
			'dbhost'			=> $qi_config['dbhost'],
			'dbport'			=> $qi_config['dbport'],
			'dbname'			=> $qi_config['db_prefix'] . $dbname,
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
			list($qi_config['db_prefix'], $dbname) = array(&$temp1, &$temp2);
		}

		// update phpbb_root_path
		$phpbb_root_path = $board_dir;

		if ($drop_db)
		{
			$db->sql_query('DROP DATABASE IF EXISTS ' . $qi_config['db_prefix'] . $dbname);
		}
		else
		{
			// Check if the database exists.
			if ($dbms == 'sqlite' || $dbms == 'firebird')
			{
				$db_check = $db->sql_select_db($quickinstall_path . 'cache/' . $qi_config['db_prefix'] . $dbname);
			}
			else
			{
				$db_check = $db->sql_select_db($qi_config['db_prefix'] . $dbname);
			}

			if ($db_check)
			{
				trigger_error(sprintf($user->lang['DB_EXISTS'], $qi_config['db_prefix'] . $dbname));
			}
		}

		if ($dbms == 'sqlite' || $dbms == 'firebird')
		{
			$db->sql_query('CREATE DATABASE ' . $quickinstall_path . 'cache/' . $qi_config['db_prefix'] . $dbname);
			$db->sql_select_db($quickinstall_path . 'cache/' . $qi_config['db_prefix'] . $dbname);
		}
		else
		{
			$db->sql_query('CREATE DATABASE ' . $qi_config['db_prefix'] . $dbname);
			$db->sql_select_db($qi_config['db_prefix'] . $dbname);
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
			'server_protocol'	=> (!empty($qi_config['server_protocol'])) ? $qi_config['server_protocol'] : 'http://',
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

			"UPDATE {$table_prefix}config
				SET config_value = '" . $db->sql_escape($qi_config['site_name']) . "'
				WHERE config_name = 'sitename'",

			"UPDATE {$table_prefix}config
				SET config_value = '" . $db->sql_escape($qi_config['site_desc']) . "'
				WHERE config_name = 'site_desc'",

			// Recompile stale style components needs to be on. This is a testing board.
			"UPDATE {$table_prefix}config
				SET config_value = '1'
				WHERE config_name = 'load_tplcompile'",
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
			'server_protocol'	=> (!empty($qi_config['server_protocol'])) ? $qi_config['server_protocol'] : 'http://',
			'server_name'		=> $qi_config['server_name'],
			'server_port'		=> $qi_config['server_port'],
			'script_path'		=> $script_path,
		));
		$install->add_modules(false, false);
		$install->add_language(false, false);
		$install->add_bots(false, false);

		// now automod (easymod)
		if ($automod)
		{
			// todo: add automod code
			file_functions::copy_dir($quickinstall_path . 'sources/automod/', $board_dir);

			// include AutoMOD lanugage files.
			if (file_exists($phpbb_root_path . 'language/' . $user->lang . '/mods/info_acp_modman.' . $phpEx))
			{
				include($phpbb_root_path . 'language/' . $user->lang . '/mods/info_acp_modman.' . $phpEx);
			}
			else
			{
				include("{$phpbb_root_path}language/en/mods/info_acp_modman.$phpEx");
			}

			unset($GLOBALS['lang']);
			$GLOBALS['lang'] = &$user->lang;
			global $lang;

			require("{$phpbb_root_path}install/install_automod.$phpEx");
			require("{$phpbb_root_path}includes/functions_convert.$phpEx");
			require("{$phpbb_root_path}includes/functions_transfer.$phpEx");

			// some stuff josh added... >_<
			global $current_version;
			$current_version = $qi_config['automod_version'];

			// add some language entries to prevent notices
			$user->lang += array(
				'FILE_EDITS'	=> '',
				'NEXT_STEP'		=> '',
			);
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
			file_functions::copy_file($quickinstall_path . 'cache/' . $qi_config['db_prefix'] . $dbname, $board_dir . $qi_config['db_prefix'] . $dbname);
			$db->sql_select_db($board_dir . $qi_config['db_prefix'] . $dbname);
		}

		// clean up
		file_functions::delete_files($board_dir, array('Thumbs.db', 'DS_Store', 'CVS', '.svn', '.git'));

		// remove install dir, develop and umil
		file_functions::delete_dir($board_dir . 'install/');
		file_functions::delete_dir($board_dir . 'develop/');
		file_functions::delete_dir($board_dir . 'umil/');

		// Install Subsilver2
		if ($subsilver)
		{
			if (!class_exists('bitfield'))
			{
				include($phpbb_root_path . 'includes/functions_content.' . $phpEx);
			}
			include($phpbb_root_path . 'includes/acp/acp_styles.' . $phpEx);
			$acp_styles = new acp_styles();
			$acp_styles->main(0, '');

			// Mostly copied from includes/acp/acp_styles.php
			$reqd_template = $reqd_theme = $reqd_imageset = false;
			$error = $installcfg = $style_row = array();
			$element_ary = array('template' => STYLES_TEMPLATE_TABLE, 'theme' => STYLES_THEME_TABLE, 'imageset' => STYLES_IMAGESET_TABLE);

			$install_path = 'subsilver2';
			$root_path = $phpbb_root_path . 'styles/subsilver2/';
			$cfg_file = $root_path . 'style.cfg';
			$installcfg = parse_cfg_file($cfg_file);

			if (!sizeof($installcfg))
			{
				continue;
			}

			$name		= $installcfg['name'];
			$copyright	= $installcfg['copyright'];
			$version	= $installcfg['version'];

			$style_row = array(
				'style_id'			=> 0,
				'template_id'		=> 0,
				'theme_id'			=> 0,
				'imageset_id'		=> 0,
				'style_name'		=> $installcfg['name'],
				'template_name'	=> $installcfg['name'],
				'theme_name'		=> $installcfg['name'],
				'imageset_name'	=> $installcfg['name'],
				'template_copyright'	=> $installcfg['copyright'],
				'theme_copyright'			=> $installcfg['copyright'],
				'imageset_copyright'	=> $installcfg['copyright'],
				'style_copyright'			=> $installcfg['copyright'],
				'store_db'			=> 0,
				'style_active'	=> 1,
				'style_default'	=> ($subsilver == 2) ? 1 : 0,
			);

			$acp_styles->install_style($error, 'install', $root_path, $style_row['style_id'], $style_row['style_name'], $install_path, $style_row['style_copyright'], $style_row['style_active'], $style_row['style_default'], $style_row);

			unset($error);
		}

		// Add some random users and posts.
		if ($populate)
		{
			include($quickinstall_path . 'includes/functions_populate.' . $phpEx);
			$pop = new populate($pop_data);

/**
// * Just commented out for now. Will remove later.

			include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
			if (!class_exists('bitfield'))
			{
				include($phpbb_root_path . 'includes/functions_content.' . $phpEx);
			}
			include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

			// Save our admin.
			$admin_user_id = $user->data['user_id'];
			$admin_username = $user->data['username'];
			$admin_user_colour = $user->data['user_colour'];

			// There is already 1 topic and 1 post.
			$posts = $topics = 1;
			$user_cnt = $reg_group = $mod_group = 0;

			$sql = 'SELECT group_id, group_name
					FROM ' . GROUPS_TABLE . "
					WHERE group_name = 'REGISTERED'
					OR group_name = 'GLOBAL_MODERATORS'";
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				if ($row['group_name'] == 'REGISTERED')
				{
					$reg_group = (int) $row['group_id'];
				}
				else if ($row['group_name'] == 'GLOBAL_MODERATORS')
				{
					$mod_group = (int) $row['group_id'];
				}
			}
			$db->sql_freeresult($result);

			$users = array();
			// No test0
			for ($i = 1; $i < 6; $i++)
			{
				$user_row = array(
					'username' => 'tester ' . $i,
					'user_password' => md5('123456'),
					'user_email' => 'test' . $i . '@slaskpost.se',
					'group_id' => $reg_group,
					'user_timezone' => $qi_config['qi_tz'],
					'user_dst' => $qi_config['qi_dst'],
					'user_lang' => $qi_config['default_lang'],
					'user_type' => USER_NORMAL,
					'user_actkey' => '',
					'user_ip' => '0.0.0.0',
					'user_regdate' => time(),
				);
				$user_row['user_id'] = user_add($user_row);

				// Save users for our posts.
				$users[] = $user_row;
				$user_cnt++;
			}

			// To be sure, get some forum data from the first forum (should be forum id 2).
			$sql = 'SELECT forum_id, forum_parents, forum_name
					FROM ' . FORUMS_TABLE . '
					WHERE forum_type = ' . FORUM_POST . '
					LIMIT 1';
			$result = $db->sql_query($sql);
			$forum_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$message_parser = new parse_message();

			// Let's add 5 topics and 5 replies to each.
			for ($i = 0; $i < 5; $i++)
			{
				$user->data['user_id'] = $users[$i]['user_id'];
				$user->data['username'] = $users[$i]['username'];
				$user->data['user_colour'] = '';

				$topic_id = $post_id = 0;
				$poll = array();

				$message = (string) 'This is the fantastic QI test topic ' . ($i + 1);
				$data = $this->post_data('post', $message, $topic_id, $post_id, $users[$i], $forum_row, $message_parser);

				submit_post('post', (string) 'Test topic ' . ($i + 1), $users[$i]['username'], POST_NORMAL, $poll, $data, true);

				$topics++;
				$posts++;

				// And now reply.
				foreach ($users as $user_row)
				{
					$topic_id = $data['topic_id'];
					$post_id = $data['post_id'];

					$user->data['user_id'] = $user_row['user_id'];
					$user->data['username'] = $user_row['username'];


					$message = (string) 'This a reply from the fantastic QI. In test topic ' . ($i + 1);
					$data = $this->post_data('reply', $message, $topic_id, $post_id, $users[$i], $forum_row, $message_parser);

					submit_post('reply', (string) 'Test topic ' . ($i + 1), $user_row['username'], POST_NORMAL, $poll, $data, true);

					$posts++;
				}
			}

			// Restore our admin.
			$user->data['user_id'] = $admin_user_id;
			$user->data['username'] = $admin_username;
			$user->data['user_colour'] = $admin_user_colour;

			$sql = 'UPDATE ' . TOPICS_TABLE . '
				SET topic_approved = 1,
				topic_replies = ' . $user_cnt . ',
				topic_replies_real = ' . $user_cnt . '
				WHERE topic_approved = 0';
			$result = $db->sql_query($sql);

			$sql = 'UPDATE ' . POSTS_TABLE . '
				SET post_approved = 1
				WHERE post_approved = 0';
			$db->sql_query($sql);

			$sql = 'UPDATE ' . FORUMS_TABLE . '
				SET forum_topics = ' . $topics . ',
				forum_posts = ' . $posts . '
				WHERE forum_id = ' . (int) $forum_row['forum_id'];
			$db->sql_query($sql);

			$sql = 'UPDATE ' . CONFIG_TABLE . "
				SET config_value = '$topics'
				WHERE config_name = 'num_topics'";
			$db->sql_query($sql);

			$sql = 'UPDATE ' . CONFIG_TABLE . "
				SET config_value = '$posts'
				WHERE config_name = 'num_posts'";
			$db->sql_query($sql);

			// The test users has posted a equal ammount and there where one post to start with.
			$user_posts = (int) ($posts - 1) / $user_cnt;
			foreach ($users as $user_row)
			{
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_posts = ' . $user_posts . ',
					user_lastpost_time = ' . time() . '
					WHERE user_id = ' . (int) $user_row['user_id'];
				$db->sql_query($sql);
			}

			// Make the first user a global moderator.
			if (!empty($users[0]))
			{
				$user_row = $users[0];
				$error = group_user_add($mod_group, $user_row['user_id'], false, false, true, 1);
				if (!empty($error))
				{
					var_dump($error);
					exit;
				}
			}
*/
		}

		// purge cache
		$cache->purge();

		// Make all files world writable.
		if ($make_writable)
		{
			file_functions::make_writable($board_dir);
		}

		// if he wants to be redirected, redirect him
		if ($redirect)
		{
			qi::redirect($board_dir);
		}

		// On succces just return to main page.
		qi::redirect('index.' . $phpEx);
	}

	// Fills the post data array.
	private function post_data($mode, $message, $topic_id, $post_id, $user_row, $forum_row, $message_parser)
	{
		$message .= "\n" . 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';
		$message_parser->message = $message;
		$message_parser->parse(true, true, true);

		// 'reply' 'post'
		$data = array(
			'topic_title'			=>  '',
			'topic_first_post_id'	=> 0,
			'topic_last_post_id'	=> 0,
			'topic_time_limit'		=> 0,
			'topic_attachment'		=> 0,
			'post_id'				=> $post_id,
			'topic_id'				=> $topic_id,
			'forum_id'				=> (int) $forum_row['forum_id'],
			'icon_id'				=> 0,
			'poster_id'				=> $user_row['user_id'],
			'enable_sig'			=> true,
			'enable_bbcode'			=> true,
			'enable_smilies'		=> true,
			'enable_urls'			=> true,
			'enable_indexing'		=> true,
			'message_md5'			=> (string) md5($message),
			'post_time'				=> time(),
			'post_checksum'			=> '',
			'post_edit_reason'		=> '',
			'post_edit_user'		=> 0,
			'forum_parents'			=> $forum_row['forum_parents'],
			'forum_name'			=> $forum_row['forum_name'],
			'notify'				=> false,
			'notify_set'			=> 0,
			'poster_ip'				=> '0.0.0.0',
			'post_edit_locked'		=> 0,
			'bbcode_bitfield'		=> $message_parser->bbcode_bitfield,
			'bbcode_uid'			=> $message_parser->bbcode_uid,
			'message'				=> $message_parser->message,
			'attachment_data'		=> $message_parser->attachment_data,
			'filename_data'			=> $message_parser->filename_data,

			'topic_approved'		=> 1,
			'post_approved'			=> 1,
		);

		return($data);
	}
}

?>
