<?php
/**
*
* @package quickinstall
* @version $Id$
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

$attempted = false;
$config_text = '';
if ($mode == 'update_settings')
{
	// Time to save some settings.
	$qi_config = utf8_normalize_nfc(request_var('qi_config', array('' => ''), true));

	$settings = new settings($qi_config);
	$attempted = true;
	$valid = false;
	$error = '';
	$saved = false;
	if ($settings->validate())
	{
		$valid = true;
		if (is_writable($quickinstall_path . 'qi_config.cfg'))
		{
			if ($settings->update())
			{
				$saved = true;
			}
			else
			{
				$error .= $user->lang['CONFIG_NOT_WRITTEN'] . '<br />';
				$error .= $user->lang['CONFIG_IS_DISPLAYED'] . '<br />';
				$config_text = $settings->get_config_text();
			}
		}
		else
		{
			$error .= $user->lang['CONFIG_NOT_WRITABLE'] . '<br />';
			$error .= $user->lang['CONFIG_IS_DISPLAYED'] . '<br />';
			$config_text = $settings->get_config_text();
		}
	}
	else
	{
		$error = $settings->error;
	}
	// configuration may have been modified by settings.
	$qi_config = $settings->get_config();

	if (empty($error))
	{
		$s_settings_success = true;
		$language = $qi_config['qi_lang'];
	}
	else
	{
		$s_settings_failure = true;
	}
	$qi_install = false;
}

gen_lang_select($language);

if ($qi_install)
{
	// Fill some default vaues.
}

$template->assign_vars(array(
	'S_BOARDS_WRITABLE' => is_writable($settings->get_boards_dir()),
	'S_CACHE_WRITABLE' => is_writable($settings->get_cache_dir()),
	'S_CONFIG_WRITABLE' => is_writable($quickinstall_path . 'qi_config.cfg'),
	'S_IN_INSTALL' => $qi_install,
	'S_IN_SETTINGS' => true,
	'S_SETTINGS_SUCCESS' => ($attempted && $saved) ? true : false,
	'S_SETTINGS_FAILURE' => ($attempted && !$saved) ? true : false,

	'ERROR' => (!empty($error)) ? ((!$qi_install) ? $error : '') : '',
	'CONFIG_TEXT' => $config_text,

	'U_UPDATE_SETTINGS'		=> qi::url('update_settings'),

	'TABLE_PREFIX'	=> htmlspecialchars($qi_config['table_prefix']),
	'SITE_NAME'		=> $qi_config['site_name'],
	'SITE_DESC'		=> $qi_config['site_desc'],
	'ALT_ENV'		=> (!empty($alt_env)) ? $alt_env : false,
	'PAGE_MAIN'		=> false,

	'CONFIG_SAVED'  => $saved,
	'CONFIG_TEXT'   => htmlspecialchars($config_text),

	// Config settings
	'CONFIG_ADMIN_EMAIL' => (!empty($qi_config['admin_email'])) ? $qi_config['admin_email'] : '',
	'CONFIG_ADMIN_NAME' => (!empty($qi_config['admin_name'])) ? $qi_config['admin_name'] : '',
	'CONFIG_ADMIN_PASS' => (!empty($qi_config['admin_pass'])) ? $qi_config['admin_pass'] : '',
	'CONFIG_AUTOMOD' => (isset($qi_config['automod'])) ? $qi_config['automod'] : 1,
	'CONFIG_BOARD_EMAIL' => (!empty($qi_config['board_email'])) ? $qi_config['board_email'] : '',
	'CONFIG_BOARDS_DIR' => (!empty($qi_config['boards_dir'])) ? $qi_config['boards_dir'] : 'boards/',
	'CONFIG_BOARDS_URL' => (!empty($qi_config['boards_url'])) ? $qi_config['boards_url'] : 'boards/',
	'CONFIG_CACHE_DIR' => (!empty($qi_config['cache_dir'])) ? $qi_config['cache_dir'] : 'cache/',
	'CONFIG_COOKIE_DOMAIN' => (!empty($qi_config['cookie_domain'])) ? $qi_config['cookie_domain'] : 'localhost',
	'CONFIG_COOKIE_SECURE' => (!empty($qi_config['cookie_secure'])) ? $qi_config['cookie_secure'] : 0,
	'CONFIG_DB_PREFIX' => (!empty($qi_config['db_prefix'])) ? $qi_config['db_prefix'] : 'qi_',
	'CONFIG_DBHOST' => (!empty($qi_config['dbhost'])) ? $qi_config['dbhost'] : 'localhost',
	'CONFIG_DBMS' => (!empty($qi_config['dbms'])) ? $qi_config['dbms'] : 'mysql',
	'CONFIG_DBPASSWD' => (!empty($qi_config['dbpasswd'])) ? $qi_config['dbpasswd'] : '',
	'CONFIG_DBPORT' => (!empty($qi_config['dbport'])) ? $qi_config['dbport'] : '',
	'CONFIG_DBUSER' => (!empty($qi_config['dbuser'])) ? $qi_config['dbuser'] : '',
	'CONFIG_DEFAULT_LANG' => (!empty($qi_config['default_lang'])) ? $qi_config['default_lang'] : 'en',
	'CONFIG_EMAIL_ENABLE' => (!empty($qi_config['email_enable'])) ? $qi_config['email_enable'] : 0,
	'CONFIG_GRANT_PERMISSIONS' => (!empty($qi_config['grant_permissions'])) ? $qi_config['grant_permissions'] : '',
	'CONFIG_MAKE_WRITABLE' => (!empty($qi_config['make_writable'])) ? $qi_config['make_writable'] : 0,
	'CONFIG_NO_PASSWORD' => (isset($qi_config['no_dbpasswd'])) ? $qi_config['no_dbpasswd'] : 0,
	'CONFIG_POPULATE' => (isset($qi_config['populate'])) ? $qi_config['populate'] : 0,
	'CONFIG_QI_DST' => (!empty($qi_config['qi_dst'])) ? $qi_config['qi_dst'] : 0,
	'CONFIG_QI_TZ' => (!empty($qi_config['qi_tz'])) ? $qi_config['qi_tz'] : 0,
	'CONFIG_REDIRECT' => (isset($qi_config['redirect'])) ? $qi_config['redirect'] : 1,
	'CONFIG_SERVER_NAME' => (!empty($qi_config['server_name'])) ? $qi_config['server_name'] : 'localhost',
	'CONFIG_SERVER_PORT' => (!empty($qi_config['server_port'])) ? $qi_config['server_port'] : '80',
	'CONFIG_SITE_DESC' => (!empty($qi_config['site_desc'])) ? $qi_config['site_desc'] : 'eviLs testing hood',
	'CONFIG_SITE_NAME' => (!empty($qi_config['site_name'])) ? $qi_config['site_name'] : 'Testing Board',
	'CONFIG_SMTP_AUTH' => (!empty($qi_config['smtp_auth'])) ? $qi_config['smtp_auth'] : 'PLAIN',
	'CONFIG_SMTP_DELIVERY' => (!empty($qi_config['smtp_delivery'])) ? $qi_config['smtp_delivery'] : 0,
	'CONFIG_SMTP_HOST' => (!empty($qi_config['smtp_host'])) ? $qi_config['smtp_host'] : '',
	'CONFIG_SMTP_PASS' => (!empty($qi_config['smtp_pass'])) ? $qi_config['smtp_pass'] : '',
	'CONFIG_SMTP_PORT' => (!empty($qi_config['smtp_port'])) ? $qi_config['smtp_port'] : 25,
	'CONFIG_SMTP_USER' => (!empty($qi_config['smtp_user'])) ? $qi_config['smtp_user'] : '',
	'CONFIG_SUBSILVER' => (isset($qi_config['subsilver'])) ? $qi_config['subsilver'] : 0,
	'CONFIG_TABLE_PREFIX' => (!empty($qi_config['table_prefix'])) ? $qi_config['table_prefix'] : 'phpbb_',
	'CONFIG_NUM_USERS' => (isset($qi_config['num_users'])) ? $qi_config['num_users'] : 150,
	'CONFIG_NUM_NEW_GROUP' => (isset($qi_config['num_new_group'])) ? $qi_config['num_new_group'] : 50,
	'CONFIG_CREATE_ADMIN' => (!empty($qi_config['create_admin'])) ? 1 : 0,
	'CONFIG_CREATE_MOD' => (!empty($qi_config['create_mod'])) ? 1 : 0,
	'CONFIG_NUM_CATS' => (isset($qi_config['num_cats'])) ? $qi_config['num_cats'] : 2,
	'CONFIG_NUM_FORUMS' => (isset($qi_config['num_forums'])) ? $qi_config['num_forums'] : 10,
	'CONFIG_NUM_TOPICS_MIN' => (isset($qi_config['num_topics_min'])) ? $qi_config['num_topics_min'] : 1,
	'CONFIG_NUM_TOPICS_MAX' => (isset($qi_config['num_topics_max'])) ? $qi_config['num_topics_max'] : 25,
	'CONFIG_NUM_REPLIES_MIN' => (isset($qi_config['num_replies_min'])) ? $qi_config['num_replies_min'] : 1,
	'CONFIG_NUM_REPLIES_MAX' => (isset($qi_config['num_replies_max'])) ? $qi_config['num_replies_max'] : 15,
	'CONFIG_EMAIL_DOMAIN' => (isset($qi_config['email_domain'])) ? $qi_config['email_domain'] : '',
	'SEL_LANG' => (!empty($language)) ? $language : '',
));

// Output page
qi::page_header($user->lang['SETTINGS'], $user->lang['QI_MAIN_ABOUT']);

$template->set_filenames(array(
	'body' => 'settings_body.html')
);

qi::page_footer();

?>