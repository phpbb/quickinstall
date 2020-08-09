<?php
/**
*
* @package quickinstall
* @copyright (c) 2010 phpBB Limited
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

class qi_settings
{
	public function __construct()
	{
		global $settings, $template, $user, $quickinstall_path, $mode, $alt_env, $alt_env_missing;

		$saved = false;
		$config_text = '';
		$errors = [];
		if ($mode === 'update_settings')
		{
			$qi_config = @utf8_normalize_nfc(qi_request_var('qi_config', array('' => ''), true));

			$profile = $settings->set_settings($qi_config);

			if ($settings->validate())
			{
				if (is_writable($quickinstall_path . 'settings'))
				{
					if ($settings->save_profile())
					{
						$settings->set_profile_cookie($profile);
						$saved = true;
					}
					else
					{
						$errors[] = sprintf($user->lang['CONFIG_NOT_WRITTEN'], $profile);
						$errors[] = $user->lang['CONFIG_IS_DISPLAYED'];
						$config_text = $settings->get_config_text();
					}
				}
				else
				{
					$errors[] = $user->lang['CONFIG_NOT_WRITABLE'];
					$errors[] = $user->lang['CONFIG_IS_DISPLAYED'];
					$config_text = $settings->get_config_text();
				}
			}
			else
			{
				$errors = $settings->get_errors();
			}
		}
		else if ($alt_env_missing)
		{
			$errors[] = sprintf($user->lang['NO_ALT_ENV_FOUND'], $alt_env);
		}

		$template->assign_vars(array(
			'S_BOARDS_WRITABLE'		=> is_writable($settings->get_boards_dir()),
			'S_CACHE_WRITABLE'		=> is_writable($settings->get_cache_dir()),
			'S_CONFIG_WRITABLE'		=> is_writable($quickinstall_path . 'settings'),
			'S_HAS_PROFILES'		=> $settings->get_profiles(),
			'S_IN_INSTALL'			=> $settings->is_install(),
			'S_SETTINGS_SUCCESS'	=> $saved && empty($errors),
			'S_SETTINGS_ERRORS'		=> $errors,
			'S_SETTINGS'			=> true,

			'U_UPDATE_SETTINGS'	=> qi::url('settings', array('mode' => 'update_settings')),
			'U_CHOOSE_PROFILE'	=> qi::url('settings', array('mode' => 'change_profile')),

			'SAVE_PROFILE'	=> $settings->get_profile(),
			'TABLE_PREFIX'	=> $settings->get_config('table_prefix', ''),
			'SITE_NAME'		=> $settings->get_config('site_name', ''),
			'SITE_DESC'		=> $settings->get_config('site_desc', ''),
			'ALT_ENV'		=> !empty($alt_env) ? $alt_env : false,
			'PROFILES'		=> $settings->get_profiles(),
			'QI_LANG'		=> qi::get_lang_select("{$quickinstall_path}language/", 'qi_lang', 'lang'),
			'PHPBB_LANG'	=> qi::get_lang_select("{$quickinstall_path}sources/phpBB3/language/", 'default_lang'),

			'CONFIG_TEXT'   => htmlspecialchars($config_text),

			// Config settings
			'CHUNK_POST'			=> $settings->get_config('chunk_post', 0),
			'CHUNK_TOPIC'			=> $settings->get_config('chunk_topic', 0),
			'CHUNK_USER'			=> $settings->get_config('chunk_user', 0),
			'CONFIG_ADMIN_EMAIL'	=> $settings->get_config('admin_email', ''),
			'CONFIG_ADMIN_NAME'		=> $settings->get_config('admin_name', ''),
			'CONFIG_ADMIN_PASS'		=> $settings->get_config('admin_pass', ''),
			'CONFIG_ALT_ENV'		=> get_alternative_env($settings->get_config('alt_env', '')),
			'CONFIG_BOARD_EMAIL'	=> $settings->get_config('board_email', ''),
			'CONFIG_BOARDS_DIR'		=> $settings->get_boards_dir(),
			'CONFIG_BOARDS_URL'		=> $settings->get_boards_url(),
			'CONFIG_CACHE_DIR'		=> $settings->get_cache_dir(),
			'CONFIG_COOKIE_DOMAIN'	=> $settings->get_config('cookie_domain', ''),
			'CONFIG_COOKIE_SECURE'	=> $settings->get_config('cookie_secure', 0),
			'CONFIG_DB_PREFIX'		=> $settings->get_config('db_prefix', ''),
			'CONFIG_DBHOST'			=> $settings->get_config('dbhost', ''),
			'CONFIG_DBMS'			=> gen_dbms_options($settings->get_config('dbms', '')),
			'CONFIG_DBPASSWD'		=> $settings->get_config('dbpasswd', ''),
			'CONFIG_DBPORT'			=> $settings->get_config('dbport', ''),
			'CONFIG_DBUSER'			=> $settings->get_config('dbuser', ''),
			'CONFIG_DEFAULT_STYLE'	=> $settings->get_config('default_style', ''),
			'CONFIG_DELETE_FILES'	=> $settings->get_config('delete_files', 0),
			'CONFIG_DEBUG'			=> $settings->get_config('debug', 0),
			'CONFIG_DROP_DB'		=> $settings->get_config('drop_db', 0),
			'CONFIG_EMAIL_ENABLE'	=> $settings->get_config('email_enable', 0),
			'CONFIG_GRANT_PERMISSIONS'	=> $settings->get_config('grant_permissions', ''),
			'CONFIG_INSTALL_STYLES'	=> $settings->get_config('install_styles', 0),
			'CONFIG_MAKE_WRITABLE'	=> $settings->get_config('make_writable', 0),
			'CONFIG_NO_PASSWORD'	=> $settings->get_config('no_dbpasswd', 0),
			'CONFIG_POPULATE'		=> $settings->get_config('populate', 0),
			'CONFIG_QI_DST'			=> $settings->get_config('qi_dst', 0),
			'CONFIG_QI_TZ'			=> $settings->get_config('qi_tz', ''),
			'CONFIG_REDIRECT'		=> $settings->get_config('redirect', 0),
			'CONFIG_SERVER_NAME'	=> $settings->get_config('server_name', ''),
			'CONFIG_SERVER_PORT'	=> $settings->get_config('server_port', ''),
			'CONFIG_SITE_DESC'		=> $settings->get_config('site_desc', ''),
			'CONFIG_SITE_NAME'		=> $settings->get_config('site_name', ''),
			'CONFIG_SMTP_AUTH'		=> $settings->get_config('smtp_auth', ''),
			'CONFIG_SMTP_DELIVERY'	=> $settings->get_config('smtp_delivery', 0),
			'CONFIG_SMTP_HOST'		=> $settings->get_config('smtp_host', ''),
			'CONFIG_SMTP_PASS'		=> $settings->get_config('smtp_pass', ''),
			'CONFIG_SMTP_PORT'		=> $settings->get_config('smtp_port', 0),
			'CONFIG_SMTP_USER'		=> $settings->get_config('smtp_user', ''),
			'CONFIG_TABLE_PREFIX'	=> $settings->get_config('table_prefix', ''),
			'CONFIG_NUM_USERS'		=> $settings->get_config('num_users', 0),
			'CONFIG_NUM_NEW_GROUP'	=> $settings->get_config('num_new_group', 0),
			'CONFIG_CREATE_ADMIN'	=> $settings->get_config('create_admin', 0),
			'CONFIG_CREATE_MOD'		=> $settings->get_config('create_mod', 0),
			'CONFIG_NUM_CATS'		=> $settings->get_config('num_cats', 0),
			'CONFIG_NUM_FORUMS'		=> $settings->get_config('num_forums', 0),
			'CONFIG_NUM_TOPICS_MIN'	=> $settings->get_config('num_topics_min', 0),
			'CONFIG_NUM_TOPICS_MAX'	=> $settings->get_config('num_topics_max', 0),
			'CONFIG_NUM_REPLIES_MIN'=> $settings->get_config('num_replies_min', 0),
			'CONFIG_NUM_REPLIES_MAX'=> $settings->get_config('num_replies_max', 0),
			'CONFIG_EMAIL_DOMAIN'	=> $settings->get_config('email_domain', ''),

			'TIMEZONE_OPTIONS'		=> qi_timezone_select($user, $settings->get_config('qi_tz', 'UTC')),

			'OTHER_CONFIG'			=> $settings->get_config('other_config', ''),

			'SEL_LANG'				=> empty($errors) ? $settings->get_config('qi_lang', 'en') : '',
		));

		// Output page
		qi::page_header('PROFILES');

		qi::page_display('settings_body');
	}
}
