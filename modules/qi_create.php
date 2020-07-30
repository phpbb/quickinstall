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
 * qi_create module
 */
class qi_create
{
	public function __construct()
	{
		global $db, $db_tools, $user, $auth, $cache, $settings, $table_prefix;
		global $quickinstall_path, $phpbb_root_path, $phpEx, $config;

		// include installation functions
		include($quickinstall_path . 'includes/functions_install.' . $phpEx);
		// postgres uses remove_comments function which is defined in functions_admin
		include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);

		// phpBB 3.1.x is not compat with PHP 7 (700000)
		// phpBB 3.2.0-3.2.1 is not compat with PHP 7.2 (702000)
		// phpBB 3.2.x is not compat with PHP 7.3 (703000)
		if ((PHP_VERSION_ID >= 70000 && !defined('PHPBB_32')) ||
			(PHP_VERSION_ID >= 70200 && phpbb_version_compare(PHPBB_VERSION, '3.2.2', '<')) ||
			(PHP_VERSION_ID >= 70300 && !defined('PHPBB_33'))
		)
		{
			create_board_warning($user->lang['MINOR_MISHAP'], sprintf($user->lang['PHP7_INCOMPATIBLE'], PHPBB_VERSION, PHP_VERSION), 'main');
		}

		if (defined('PHPBB_31'))
		{
			$config->set('rand_seed', md5(mt_rand()));
			$config->set('rand_seed_last_update', time());
		}
		else
		{
			$config = array_merge($config, array(
				'rand_seed'				=> md5(mt_rand()),
				'rand_seed_last_update'	=> time(),
			));
		}

		// load installer lang
//		qi::add_lang('phpbb');

		// phpbb's install uses $lang instead of $user->lang
		// need to use $GLOBALS here
		$GLOBALS['lang'] = &$user->lang;
		global $lang;

		// Request some variables that are used several times here.
		$dbms		= $settings->get_config('dbms');
		$dbhost		= $settings->get_config('dbhost');
		$db_prefix	= validate_dbname($settings->get_config('db_prefix'), true);
		$dbname		= validate_dbname($settings->get_config('dbname'), true);
		$dbport		= $settings->get_config('dbport');

		$dbpasswd	= $settings->get_config('dbpasswd');
		$dbuser		= $settings->get_config('dbuser');

		$site_dir	= validate_dbname($settings->get_config('dbname'), true, true);
		$site_name	= $settings->get_config('site_name', '', true);
		$site_desc	= $settings->get_config('site_desc', '', true);

		$admin_name	= $settings->get_config('admin_name', '', true);
		$admin_pass	= $settings->get_config('admin_pass', '', true);

		$alt_env	= $settings->get_config('alt_env', '');

		if ($alt_env !== '' && (!file_exists("{$quickinstall_path}sources/phpBB3_alt/$alt_env") || is_file("{$quickinstall_path}sources/phpBB3_alt/$alt_env")))
		{
			create_board_warning($user->lang['MINOR_MISHAP'], $user->lang['NO_ALT_ENV_FOUND'], 'main');
		}

		// Set up our basic founder.
		$user->data['user_id'] = 2; //
		$user->data['username'] = $admin_name;
		$user->data['user_colour'] = 'AA0000';

		// overwrite some of them ;)
		$user->lang = array_merge($user->lang, array(
			'CONFIG_SITE_DESC'	=> $site_desc,
			'CONFIG_SITENAME'	=> $site_name,
		));

		// check if we have a board db (and directory) name
		if (!$dbname)
		{
			create_board_warning($user->lang['MINOR_MISHAP'], $user->lang['NO_DB'], 'main');
		}

		// Set the new board as root path.
		$board_dir = $settings->get_boards_dir() . $site_dir . '/';
		$board_url = $settings->get_boards_url() . $site_dir . '/';
		$phpbb_root_path = $board_dir;

		if (!defined('PHPBB_ROOT_PATH'))
		{
			define('PHPBB_ROOT_PATH', $board_dir);
		}

		if (file_exists($board_dir))
		{
			if ($settings->get_config('delete_files', false))
			{
				file_functions::delete_dir($board_dir);
			}
			else
			{
				create_board_warning($user->lang['MINOR_MISHAP'], sprintf($user->lang['DIR_EXISTS'], $board_dir), 'main');
			}
		}

		// copy all of our files
		try
		{
			file_functions::copy_dir($quickinstall_path . 'sources/' . ($alt_env === '' ? 'phpBB3/' : "phpBB3_alt/$alt_env/"), $board_dir);
		}
		catch (RuntimeException $e)
		{
			create_board_warning($user->lang['MINOR_MISHAP'], sprintf($user->lang[$e->getMessage()], $board_dir), 'main');
		}

		if (!defined('PHPBB_31'))
		{
			// copy qi's lang file for the log
			$qi_lang = $settings->get_config('qi_lang');
			if (file_exists("{$quickinstall_path}language/$qi_lang/info_acp_qi.$phpEx") && file_exists("{$board_dir}language/$qi_lang"))
			{
				copy("{$quickinstall_path}language/$qi_lang/info_acp_qi.$phpEx", "{$board_dir}language/$qi_lang/mods/info_acp_qi.$phpEx");
			}
			else
			{
				copy("{$quickinstall_path}language/en/info_acp_qi.$phpEx", "{$board_dir}language/en/mods/info_acp_qi.$phpEx");
			}
		}

		if (in_array($dbms, array('sqlite', 'sqlite3')))
		{
			$dbhost = $dbhost . $db_prefix . $dbname;
		}

		// Set the new board as language path to get language files from outside phpBB
		//$user->set_custom_lang_path($phpbb_root_path . 'language/');
		$user->lang_path = $phpbb_root_path . 'language/';
		if (substr($user->lang_path, -1) != '/')
		{
			$user->lang_path .= '/';
		}

		// Write to config.php ;)
		$config_version = qi_get_phpbb_version();
		$config_data = "<?php\n";
		$config_data .= "// phpBB $config_version.x auto-generated configuration file\n// Do not change anything in this file!\n";

		$config_data_array = array(
			'$dbhost' => $dbhost,
			'$dbport' => $settings->get_config('dbport'),
			'$dbname' =>  $db_prefix . $dbname,
			'$dbuser' => $dbuser,
			'$dbpasswd' => htmlspecialchars_decode($dbpasswd),
			'$table_prefix' => $table_prefix,
		);

		if (defined('PHPBB_31'))
		{
			$config_data_array['$dbms'] = "phpbb\\\\db\\\\driver\\\\$dbms";
			$config_data_array['$acm_type'] = 'phpbb\\\\cache\\\\driver\\\\file';
			$config_data_array['$phpbb_adm_relative_path'] = 'adm/';
		}
		else
		{
			$config_data_array['$dbms'] = $dbms;
			$config_data_array['$acm_type'] = 'file';
			$config_data_array['$load_extensions'] = '';
		}

		foreach ($config_data_array as $key => $value)
		{
			$config_data .= "$key = '$value';\n";
		}
		unset($config_data_array);

		$s_debug = !$settings->get_config('debug', 0) ? '//' : '';

		$config_data .= "\n@define('PHPBB_INSTALLED', true);\n";
		if (defined('PHPBB_33'))
		{
			$config_data .= "@define('PHPBB_ENVIRONMENT', 'production');\n";
			$config_data .= "//@define('DEBUG_CONTAINER', true);\n";

			if (empty($s_debug))
			{
				$debug_data = array(
					'debug.load_time'             => 'true',
					'debug.memory'                => 'true',
					'debug.sql_explain'           => 'true',
					'debug.show_errors'           => 'true',
					'debug.exceptions'            => 'true',
					'twig.debug'                  => 'false',
					'twig.auto_reload'            => 'false',
					'twig.enable_debug_extension' => 'false',
				);
				$dump = "\nparameters:\n    " . implode("\n    ", array_map(function ($key, $value) {
					return "$key: $value";
				}, array_keys($debug_data), $debug_data)) . "\n";
				file_put_contents($board_dir . 'config/production/config.yml', $dump, FILE_APPEND);
			}
		}
		else if (defined('PHPBB_32'))
		{
			$config_data .= "@define('PHPBB_ENVIRONMENT', 'production');\n";
			$config_data .= "$s_debug@define('PHPBB_DISPLAY_LOAD_TIME', true);\n";
			$config_data .= "//@define('DEBUG_CONTAINER', true);\n";
		}
		else if (defined('PHPBB_31'))
		{
			$config_data .= "$s_debug@define('PHPBB_DISPLAY_LOAD_TIME', true);\n";
			$config_data .= "$s_debug@define('DEBUG', true);\n";
			$config_data .= "//@define('DEBUG_CONTAINER', true);\n";
		}
		else
		{
			$config_data .= "$s_debug@define('DEBUG', true);\n";
			$config_data .= "$s_debug@define('DEBUG_EXTRA', true);\n";
			$config_data .= '?' . '>'; // Done this to prevent highlighting editors getting confused!
		}
		file_put_contents($board_dir . 'config.' . $phpEx, $config_data);

		$db = db_connect();

		if (defined('PHPBB_32'))
		{
			$factory = new \phpbb\db\tools\factory();
			$db_tools = $factory->get($db);
		}
		else if (defined('PHPBB_31'))
		{
			$db_tools = new \phpbb\db\tools($db);
		}
		else
		{
			if (!class_exists('phpbb_db_tools'))
			{
				include $phpbb_root_path . 'includes/db/db_tools.' . $phpEx;
			}
			$db_tools = new phpbb_db_tools($db);
		}

		if ($settings->get_config('drop_db', 0))
		{
			$db->sql_query('DROP DATABASE IF EXISTS ' . $db_prefix . $dbname);
		}
		else
		{
			// Check if the database exists.
			switch ($dbms)
			{
				case 'sqlite':
				case 'sqlite3':
					$db_check = $db->sql_select_db($dbhost);
				break;
				case 'postgres':
					global $sql_db;

					$error_collector_class = (defined('PHPBB_31')) ? '\phpbb\error_collector' : 'phpbb_error_collector';

					if (!class_exists($error_collector_class))
					{
						include $phpbb_root_path . 'includes/error_collector.' . $phpEx;
					}

					$error_collector = new $error_collector_class;
					$error_collector->install();
					$db_check_conn = new $sql_db();
					$db_check_conn->sql_connect($dbhost, $dbuser, $dbpasswd, $db_prefix . $dbname, $dbport, false, false);
					$error_collector->uninstall();
					$db_check = count($error_collector->errors) == 0;
				break;
				default:
					$db_check = $db->sql_select_db($db_prefix . $dbname);
				break;
			}

			if ($db_check)
			{
				create_board_warning($user->lang['MINOR_MISHAP'], sprintf($user->lang['DB_EXISTS'], $db_prefix . $dbname), 'main');
			}
		}

		switch ($dbms)
		{
			case 'sqlite':
			case 'sqlite3':
				$db->sql_create_db($dbhost);
				$db->sql_select_db($dbhost);
			break;
			case 'postgres':
				global $sql_db;
				$db->sql_query('CREATE DATABASE ' . $db_prefix . $dbname);
				$db = new $sql_db();
				$db->sql_connect($dbhost, $dbuser, $dbpasswd, $db_prefix . $dbname, $dbport, false, false);
				$db->sql_return_on_error(true);
			break;
			default:
				$db->sql_query('CREATE DATABASE ' . $db_prefix . $dbname);
				$db->sql_select_db($db_prefix . $dbname);
			break;
		}

		// include install lang from phpbb. But only if it exists
		$default_lang = $settings->get_config('default_lang');
		$selected_lang = $phpbb_root_path . "language/$default_lang/";
		if (file_exists($selected_lang))
		{
			qi::add_lang('install', $selected_lang);
		}
		else
		{
			// Assume that English is always available
			$default_lang = 'en';
			qi::add_lang('install', $phpbb_root_path . 'language/en/');
		}

		// perform sql
		load_schema($phpbb_root_path . 'install/schemas/', $dbms);

		$current_time = time();
		$user_ip = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
		$user_ip = htmlspecialchars($user_ip);

		$script_path = (!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : getenv('PHP_SELF');
		if (!$script_path)
		{
			$script_path = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');
		}

		// Replace backslashes and doubled slashes (could happen on some proxy setups)
		$script_path = trim(dirname($script_path));
		$script_path = str_replace(array('\\', '//'), '/', $script_path);

		// Make sure $script_path ends with a slash (/).
		$script_path = (substr($script_path, -1) != '/') ? $script_path . '/' : $script_path;
		$script_path .= $settings->get_boards_dir() . $site_dir . '/';

		$config_ary = array(
			'board_startdate'	=> $current_time,
			'default_lang'		=> $default_lang,
			'server_name'		=> $settings->get_config('server_name'),
			'server_port'		=> $settings->get_config('server_port', 0),
			'board_email'		=> $settings->get_config('board_email'),
			'board_contact'		=> $settings->get_config('board_email'),
			'cookie_domain'		=> $settings->get_config('cookie_domain'),
			'default_dateformat'=> $user->lang['default_dateformat'],
			'email_enable'		=> $settings->get_config('email_enable', 0),
			'smtp_delivery'		=> $settings->get_config('smtp_delivery', 0),
			'smtp_host'			=> $settings->get_config('smtp_host'),
			'smtp_auth_method'	=> $settings->get_config('smtp_auth'),
			'smtp_username'		=> $settings->get_config('smtp_user'),
			'smtp_port'			=> $settings->get_config('smtp_port', 0),
			'smtp_password'		=> $settings->get_config('smtp_pass'),
			'cookie_secure'		=> $settings->get_config('cookie_secure', 0),
			'script_path'		=> $script_path,
			'server_protocol'	=> $settings->get_server_protocol(),
			'newest_username'	=> $admin_name,
			'avatar_salt'		=> md5(mt_rand()),
			'cookie_name'		=> 'phpbb3_' . strtolower(gen_rand_string(5)),
		);

		if (defined('PHPBB_31'))
		{
			$config_ary['board_timezone'] = $settings->get_config('qi_tz', '');
			$tz_data = "user_timezone = '{$config_ary['board_timezone']}'";
		}
		else
		{
			$tz		= new DateTimeZone($settings->get_config('qi_tz', ''));
			$tz_ary	= $tz->getTransitions(time());
			$offset	= (float) $tz_ary[0]['offset'] / 3600;	// 3600 seconds = 1 hour.
			$dst	= ($tz_ary[0]['isdst']) ? 1 : 0;

			$tz_data = "user_timezone = $offset, user_dst = $dst";
			$config_ary['user_timezone'] = $offset;
			$config_ary['user_dst'] = $dst;

			unset($tz_ary, $tz, $offset, $dst);
		}

		if (@extension_loaded('gd') || can_load_dll('gd'))
		{
			$config_ary['captcha_gd'] = 1;
		}

		if (defined('PHPBB_31'))
		{
			$current_config = $config;
			$config = new \phpbb\config\db($db, $cache, "{$table_prefix}config");
			set_config(false, false, false, $config);

			foreach ($current_config as $key => $value)
			{
				$config->set($key, $value);
			}
		}

		foreach ($config_ary as $config_name => $config_value)
		{
			set_config($config_name, $config_value);
		}

		// Set default config and post data, this applies to all DB's
		$sql_ary = array(
			"UPDATE {$table_prefix}users
				SET username		= '" . $db->sql_escape($admin_name) . "',
					user_password	= '" . $db->sql_escape(md5($admin_pass)) . "',
					user_ip			= '" . $db->sql_escape($user_ip) . "',
					user_lang		= '" . $db->sql_escape($default_lang) . "',
					user_email		= '" . $db->sql_escape($settings->get_config('board_email')) . "',
					user_dateformat	= '" . $db->sql_escape($user->lang['default_dateformat']) . "',
					username_clean	= '" . $db->sql_escape(utf8_clean_string($admin_name)) . "',
					" . (!defined('PHPBB_33') || $db_tools->sql_column_exists("{$table_prefix}users", 'user_email_hash') ? 'user_email_hash = ' . phpbb_email_hash($settings->get_config('board_email')) . ',' : '') . "
					$tz_data
				WHERE username = 'Admin'",

			"UPDATE {$table_prefix}moderator_cache
				SET username = '" . $db->sql_escape($admin_name) . "'
				WHERE username = 'Admin'",

			"UPDATE {$table_prefix}forums
				SET forum_last_poster_name = '" . $db->sql_escape($admin_name) . "'
				WHERE forum_last_poster_name = 'Admin'",

			"UPDATE {$table_prefix}topics
				SET topic_first_poster_name = '" . $db->sql_escape($admin_name) . "', topic_last_poster_name = '" . $db->sql_escape($admin_name) . "'
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
				SET config_value = '" . $db->sql_escape($site_name) . "'
				WHERE config_name = 'sitename'",

			"UPDATE {$table_prefix}config
				SET config_value = '" . $db->sql_escape($site_desc) . "'
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

		if (defined('PHPBB_31'))
		{
			$config = new \phpbb\config\config(array());
		}
		else
		{
			$config = array();
		}

		while ($row = $db->sql_fetchrow($result))
		{
			$config[$row['config_name']] = $row['config_value'];
		}
		$db->sql_freeresult($result);

		// Set other optional config data.
		// Have it here to not having to do a query for each key
		// to see if it is a update or insert.
		if (($other_config = $settings->get_config('other_config')) !== '')
		{
			$other_config = explode("\n", $other_config);
			foreach ($other_config as $config_row)
			{
				// First check if this is a comment.
				if (strpos($config_row, '#') === 0)
				{
					continue;
				}

				$row_ary = explode(';', $config_row);

				if (empty($row_ary[0]) || empty($row_ary[1]))
				{
					continue;
				}

				$config_name	= trim($row_ary[0]);
				$config_value	= trim($row_ary[1]);
				$is_dynamic		= (!empty($row_ary[2])) ? 1 : 0;

				$sql_ary = array(
					'config_name'	=> $config_name,
					'config_value'	=> $config_value,
					'is_dynamic'	=> $is_dynamic,
				);

				if ((defined('PHPBB_31') && $config->offsetExists($config_name)) || (!defined('PHPBB_31') && array_key_exists($config_name, $config)))
				{
					$sql = "UPDATE {$table_prefix}config
						SET " . $db->sql_build_array('UPDATE', $sql_ary) . "
						WHERE config_name = '$config_name'";

					if (!$db->sql_query($sql))
					{
						$error = $db->sql_error();
						trigger_error($error['message']);
					}
				}
				else
				{
					$sql = "INSERT INTO {$table_prefix}config " . $db->sql_build_array('INSERT', $sql_ary);

					if (!$db->sql_query($sql))
					{
						$error = $db->sql_error();
						trigger_error($error['message']);
					}
				}

				// Update the config array.
				$config[$config_name] = $config_value;
			}

			unset($other_config);
		}

		// no templates though :P
		$config['load_tplcompile'] = '1';

		// extended phpbb install script
		if (!defined('PHPBB_32'))
		{
			include($phpbb_root_path . 'install/install_install.' . $phpEx);
			include($quickinstall_path . 'includes/install_install_qi.' . $phpEx);
		}

		if (defined('PHPBB_32'))
		{
			$container_builder = new \phpbb\di\container_builder($phpbb_root_path, $phpEx);
			$container = $container_builder
				->with_environment('installer')
				->without_extensions()
				->without_cache()
				->with_custom_parameters([
					'core.disable_super_globals' => false,
					'installer.create_config_file.options' => [
						'debug' => true,
						'environment' => 'production',
					],
					'cache.driver.class' => 'phpbb\cache\driver\file'
				])
				->without_compiled_container()
				->get_container();

			$container->register('installer.install_finish.notify_user')->setSynthetic(true);
			$container->set('installer.install_finish.notify_user', null);
			$container->compile();

			$language = $container->get('language');
			$language->add_lang(array('common', 'acp/common', 'acp/board', 'install', 'posting'));

			$iohandler_factory = $container->get('installer.helper.iohandler_factory');
			$iohandler_factory->set_environment('cli');
			$iohandler = $iohandler_factory->get();

			$output = new \Symfony\Component\Console\Output\NullOutput();
			$style = new \Symfony\Component\Console\Style\SymfonyStyle(
				new \Symfony\Component\Console\Input\ArrayInput(array()),
				$output
			);
			$iohandler->set_style($style, $output);

			$installer = $container->get('installer.installer.install');
			$installer->set_iohandler($iohandler);

			// Set data
			$iohandler->set_input('admin_name', $admin_name);
			$iohandler->set_input('admin_pass1', $admin_pass);
			$iohandler->set_input('admin_pass2', $admin_pass);
			$iohandler->set_input('board_email', $settings->get_config('board_email'));
			$iohandler->set_input('submit_admin', 'submit');

			$iohandler->set_input('default_lang', $default_lang);
			$iohandler->set_input('board_name', $site_name);
			$iohandler->set_input('board_description', $site_desc);
			$iohandler->set_input('submit_board', 'submit');

			$iohandler->set_input('dbms', $dbms);
			$iohandler->set_input('dbhost', $dbhost);
			$iohandler->set_input('dbport', $settings->get_config('dbport'));
			$iohandler->set_input('dbuser', $dbuser);
			$iohandler->set_input('dbpasswd', $dbpasswd);
			$iohandler->set_input('dbname', $dbname);
			$iohandler->set_input('table_prefix', $table_prefix);
			$iohandler->set_input('submit_database', 'submit');

			$iohandler->set_input('email_enable', $settings->get_config('email_enable', 0));
			$iohandler->set_input('smtp_delivery', $settings->get_config('smtp_delivery', 0));
			$iohandler->set_input('smtp_host', $settings->get_config('smtp_host'));
			$iohandler->set_input('smtp_auth', $settings->get_config('smtp_auth'));
			$iohandler->set_input('smtp_user', $settings->get_config('smtp_user'));
			$iohandler->set_input('smtp_pass', $settings->get_config('smtp_pass'));
			$iohandler->set_input('submit_email', 'submit');

			$iohandler->set_input('cookie_secure', $settings->get_config('cookie_secure', 0));
			$iohandler->set_input('server_protocol', $settings->get_server_protocol());
			$iohandler->set_input('force_server_vars', 'http://');
			$iohandler->set_input('server_name', $settings->get_config('server_name'));
			$iohandler->set_input('server_port', $settings->get_config('server_port'));
			$iohandler->set_input('script_path', $script_path);
			$iohandler->set_input('submit_server', 'submit');

			// Update the lang array with keys loaded for the installer
			$user->lang = array_merge($user->lang, $language->get_lang_array());

			// Storing the user object temporarily because it is
			// altered by the installer processes below...not sure why?
			$current_user = $user;

			// Suppress errors because constants.php is added again in these objects
			// leading to debug notices about the constants already being defined.
			@$container->get('installer.install_finish.populate_migrations')->run();
			@$container->get('installer.install_data.add_modules')->run();
			@$container->get('installer.install_data.add_languages')->run();
			@$container->get('installer.install_data.add_bots')->run();

			$container->reset();

			// Set some services in the container that may be needed later
			global $phpbb_container, $phpbb_log, $phpbb_dispatcher, $request, $passwords_manager;
			global $symfony_request, $phpbb_filesystem;

			$phpbb_container = $container->get('installer.helper.container_factory');
			$phpbb_dispatcher = $phpbb_container->get('dispatcher');
			$phpbb_log = $phpbb_container->get('log');

			$request = $phpbb_container->get('request');
			$request->enable_super_globals();

			$passwords_manager = $phpbb_container->get('passwords.manager');
			$symfony_request = $phpbb_container->get('symfony_request');
			$phpbb_filesystem = $phpbb_container->get('filesystem');

			// Restore user object to original state
			$user = $current_user;
			unset($current_user);

			// get search for 3.2.x
			$search_error_msg = false;
			$search = new \phpbb\search\fulltext_native($search_error_msg, $phpbb_root_path, $phpEx, $auth, $config, $db, $user, $phpbb_dispatcher);
		}
		else if (defined('PHPBB_31'))
		{
			global $phpbb_container, $phpbb_config_php_file, $phpbb_log, $phpbb_dispatcher, $request, $passwords_manager;
			global $symfony_request, $phpbb_filesystem;

			$phpbb_config_php_file = new \phpbb\config_php_file($phpbb_root_path, $phpEx);
			$phpbb_container_builder = new \phpbb\di\container_builder($phpbb_config_php_file, $phpbb_root_path, $phpEx);
			$phpbb_container_builder->set_inject_config(false);
			$phpbb_container_builder->set_dump_container(false);
			$phpbb_container_builder->set_use_extensions(false);
			$phpbb_container_builder->set_compile_container(false);
			$phpbb_container_builder->set_custom_parameters(array(
				'core.table_prefix' => $table_prefix,
				'core.root_path' => $phpbb_root_path,
				'core.php_ext' => $phpEx,
				'core.adm_relative_path' => 'adm/',
			));

			$phpbb_container = $phpbb_container_builder->get_container();

			$phpbb_container->register('dbal.conn')->setSynthetic(true);
			$phpbb_container->register('dbal.conn.driver')->setSynthetic(true);
			$phpbb_container->register('cache.driver')->setSynthetic(true);
			$phpbb_container->register('cache')->setSynthetic(true);
			$phpbb_container->register('auth')->setSynthetic(true);
			$phpbb_container->register('user')->setSynthetic(true);

			$phpbb_container->compile();

			$phpbb_container->set('dbal.conn', $db);
			$phpbb_container->set('cache.driver', $cache);
			$phpbb_container->set('user', $user);
			$phpbb_container->set('auth', $auth);

			$cache	= new \phpbb\cache\service($cache, $config, $db, $phpbb_root_path, $phpEx);
			$phpbb_container->set('cache', $cache);

			$phpbb_dispatcher = $phpbb_container->get('dispatcher');
			$phpbb_log = $phpbb_container->get('log');

			$request = $phpbb_container->get('request');
			$request->enable_super_globals();

			$passwords_manager = $phpbb_container->get('passwords.manager');
			$symfony_request = $phpbb_container->get('symfony_request');
			$phpbb_filesystem = $phpbb_container->get('filesystem');

			// Populate migrations table.
			$install = new install_install($p_master = new p_master_dummy());
			$install->populate_migrations($phpbb_container->get('ext.manager'), $phpbb_container->get('migrator'));
			unset($install);

			// get search for 3.1
			$search = new \phpbb\search\fulltext_native($error = false, $phpbb_root_path, $phpEx, $auth, $config, $db, $user, null);
		}

		// get search for 3.0
		if (!isset($search))
		{
			if (!class_exists('fulltext_native'))
			{
				include_once($phpbb_root_path . 'includes/search/fulltext_native.' . $phpEx);
			}

			$search = new fulltext_native($error = false);
		}

		$this->build_search_index($db, $search);

		if (!defined('PHPBB_32'))
		{
			$install = new install_install_qi($p_master = new p_master_dummy());
			$install->set_data(array(
				'dbms'				=> $dbms,
				'dbhost'			=> $dbhost,
				'dbport'			=> $settings->get_config('dbport'),
				'dbuser'			=> $dbuser,
				'dbpasswd'			=> $dbpasswd,
				'dbname'			=> $dbname,
				'table_prefix'		=> $table_prefix,
				'default_lang'		=> $default_lang,
				'admin_name'		=> $admin_name,
				'admin_pass1'		=> $admin_pass,
				'admin_pass2'		=> $admin_pass,
				'board_email1'		=> $settings->get_config('board_email'),
				'board_email2'		=> $settings->get_config('board_email'),
				'email_enable'		=> $settings->get_config('email_enable', 0),
				'smtp_delivery'		=> $settings->get_config('smtp_delivery', 0),
				'smtp_host'			=> $settings->get_config('smtp_host'),
				'smtp_auth'			=> $settings->get_config('smtp_auth'),
				'smtp_user'			=> $settings->get_config('smtp_user'),
				'smtp_pass'			=> $settings->get_config('smtp_pass'),
				'cookie_secure'		=> $settings->get_config('cookie_secure', 0),
				'server_protocol'	=> $settings->get_server_protocol(),
				'server_name'		=> $settings->get_config('server_name'),
				'server_port'		=> $settings->get_config('server_port'),
				'script_path'		=> $script_path,
			));
			$install->add_modules(false, false);
			$install->add_language(false, false);
			$install->add_bots(false, false);
		}

		// clean up
		file_functions::delete_files($board_dir, array('Thumbs.db', 'DS_Store', 'CVS', '.svn', '.git'));

		// remove install dir, develop and umil
		file_functions::delete_dir($board_dir . 'install/');
		file_functions::delete_dir($board_dir . 'develop/');
		file_functions::delete_dir($board_dir . 'umil/');

		// copy extra user added files
		try
		{
			file_functions::copy_dir($quickinstall_path . 'sources/extra/', $board_dir);
		}
		catch (RuntimeException $e)
		{
			create_board_warning($user->lang['MINOR_MISHAP'], sprintf($user->lang[$e->getMessage()], $board_dir), 'main');
		}

		// Install styles
		if ($settings->get_config('install_styles', 0))
		{
			include($phpbb_root_path . 'includes/acp/acp_styles.' . $phpEx);

			if (!class_exists('bitfield'))
			{
				include($phpbb_root_path . 'includes/functions_content.' . $phpEx);
			}

			if (defined('PHPBB_31'))
			{
				include($quickinstall_path . 'includes/class_31_styles.' . $phpEx);

				new class_31_styles();
			}
			else
			{
				include($quickinstall_path . 'includes/class_30_styles.' . $phpEx);

				new class_30_styles();
			}
		}

		// Add some random users and posts. Revisit.
		if ($settings->get_config('populate', false))
		{
			include($quickinstall_path . 'includes/functions_populate.' . $phpEx);
			new populate();
		}

		// add log entry :D
		$user->ip = &$user_ip;
		if (defined('PHPBB_31'))
		{
			add_log('admin', sprintf($user->lang['LOG_INSTALL_INSTALLED_QI'], qi::current_version()));
		}
		else
		{
			add_log('admin', 'LOG_INSTALL_INSTALLED_QI', qi::current_version());
		}

		// purge cache
		$cache->purge();

		// Make all files world writable.
		if ($settings->get_config('make_writable', false))
		{
			file_functions::make_writable($board_dir);
		}

		// Grant additional permissions
		if (($grant_permissions = octdec($settings->get_config('grant_permissions', 0))) != 0)
		{
			file_functions::grant_permissions($board_dir, $grant_permissions);
		}

		// if he/she wants to be redirected, we'll do that.
		if ($settings->get_config('redirect', false))
		{
			// Log him/her in first.
			$user->session_begin();
			$auth->login($admin_name, $settings->get_config('admin_pass'), false, true, true);
			if (qi::is_ajax())
			{
				qi::ajax_response(array('redirect' => $board_url));
			}
			qi::redirect($board_url);
		}

		// On succces just return to main page.
		if (qi::is_ajax())
		{
			qi::ajax_response(array('redirect' => 'index.' . $phpEx));
		}
		qi::redirect('index.' . $phpEx);
	}

	protected function build_search_index($db, $search)
	{
		$sql = 'SELECT post_id, post_subject, post_text, poster_id, forum_id
			FROM ' . POSTS_TABLE;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$search->index('post', $row['post_id'], $row['post_text'], $row['post_subject'], $row['poster_id'], $row['forum_id']);
		}
		$db->sql_freeresult($result);
	}
}
