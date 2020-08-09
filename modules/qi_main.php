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
 * qi_main module
 */
class qi_main
{
	public function __construct()
	{
		global $template, $user, $settings;

		get_installed_boards();

		// Some error?
		if ($settings->get_config('error', 0))
		{
			$error_msg = $settings->get_config('error_msg', '', true);
			$error_msg = htmlspecialchars_decode($error_msg);
			$template->assign_var('ERROR_MSG', $error_msg);
		}

		// Assign index specific vars
		$template->assign_vars(array(
			'U_CREATE'			=> qi::url('create'),
			'U_CHOOSE_PROFILE'	=> qi::url('main', array('mode' => 'change_profile')),

			'TABLE_PREFIX'		=> $settings->get_config('table_prefix', ''),
			'DB_PERFIX'			=> $settings->get_config('db_prefix', ''),
			'SITE_NAME'			=> $settings->get_config('site_name', ''),
			'SITE_DESC'			=> $settings->get_config('site_desc', ''),
			'PROFILES'			=> $settings->get_profiles(),
			'DBNAME'			=> $settings->get_config('dbname', ''),
			'INSTALL_STYLES'	=> $settings->get_config('install_styles', 0),
			'DEFAULT_STYLE'		=> $settings->get_config('default_style', ''),

			'S_ERROR'		=> $settings->get_config('error', 0),
			'ERROR_TITLE'	=> $settings->get_config('error_title', '', true),

			'S_DELETE_FILES'=> $settings->get_config('delete_files', 0),
			'S_DEBUG'		=> $settings->get_config('debug', 0),
			'S_DROP_DB'		=> $settings->get_config('drop_db', 0),
			'S_MAKE_WRITABLE'	=> $settings->get_config('make_writable', 0),
			'S_POPULATE'	=> $settings->get_config('populate', 0),
			'S_REDIRECT'	=> $settings->get_config('redirect', 0),

			'S_ADMIN_NAME'	=> $settings->get_config('admin_name', false),
			'S_ADMIN_PASS'	=> $settings->get_config('admin_pass', false),
			'S_DBPASSWD'	=> $settings->get_config('dbpasswd', false),
			'S_NODBPASSWD'	=> $settings->get_config('no_dbpasswd', false),
			'S_DBUSER'		=> $settings->get_config('dbuser', false),
			'S_MAIN'		=> true,

			'ALT_ENV'		=> get_alternative_env($settings->get_config('alt_env', '')),

			'PHPBB_VERSION'	=> qi_get_phpbb_version(),

			// Chunk settings
			'CHUNK_POST'	=> $settings->get_config('chunk_post', 0),
			'CHUNK_TOPIC'	=> $settings->get_config('chunk_topic', 0),
			'CHUNK_USER'	=> $settings->get_config('chunk_user', 0),

			// Populate settings.
			'NUM_USERS'			=> $settings->get_config('num_users', 0),
			'NUM_NEW_GROUP'		=> $settings->get_config('num_new_group', 0),
			'CREATE_MOD'		=> $settings->get_config('create_mod', 0),
			'CREATE_ADMIN'		=> $settings->get_config('create_admin', 0),
			'NUM_CATS'			=> $settings->get_config('num_cats', 0),
			'NUM_FORUMS'		=> $settings->get_config('num_forums', 0),
			'NUM_TOPICS_MIN'	=> $settings->get_config('num_topics_min', 0),
			'NUM_TOPICS_MAX'	=> $settings->get_config('num_topics_max', 0),
			'NUM_REPLIES_MIN'	=> $settings->get_config('num_replies_min', 0),
			'NUM_REPLIES_MAX'	=> $settings->get_config('num_replies_max', 0),
			'EMAIL_DOMAIN'		=> $settings->get_config('email_domain', ''),
			'GRANT_PERMISSIONS'	=> $settings->get_config('grant_permissions', ''),
			'OTHER_CONFIG'		=> $settings->get_config('other_config', ''),
		));

		// Output page
		qi::page_header('QI_MANAGE');

		qi::page_display('main_body');
	}
}
