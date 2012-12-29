<?php
/**
*
* @package quickinstall
* @copyright (c) 2007 phpBB Group
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
		global $db, $template, $user, $settings;
		global $quickinstall_path, $phpbb_root_path, $phpEx, $config;

		get_installed_boards();

		// Assign index specific vars
		$template->assign_vars(array(
			'S_IN_INSTALL'	=> false,

			'U_CREATE'			=> qi::url('create'),
			'U_CHOOSE_PROFILE'	=> qi::url('main', array('mode' => 'change_profile')),

			'TABLE_PREFIX'	=> $settings->get_config('table_prefix', ''),
			'DB_PERFIX'		=> htmlspecialchars($settings->get_config('db_prefix', '')),
			'SITE_NAME'		=> $settings->get_config('site_name', ''),
			'SITE_DESC'		=> $settings->get_config('site_desc', ''),
			'PROFILE_OPTIONS'	=> $settings->get_profiles(),

			'S_AUTOMOD'		=> $settings->get_config('automod', 0),
			'S_DELETE_FILES'=> $settings->get_config('delete_files', 0),
			'S_DROP_DB'		=> $settings->get_config('drop_db', 0),
			'S_MAKE_WRITABLE'	=> $settings->get_config('make_writable', 0),
			'S_POPULATE'	=> $settings->get_config('populate', 0),
			'S_REDIRECT'	=> $settings->get_config('redirect', 0),
			'S_SUBSILVER'	=> $settings->get_config('subsilver', 0),

			'S_ADMIN_NAME'	=> $settings->get_config('admin_name', false),
			'S_ADMIN_PASS'	=> $settings->get_config('admin_pass', false),
			'S_DBPASSWD'	=> $settings->get_config('dbpasswd', false),
			'S_NODBPASSWD'	=> $settings->get_config('no_dbpasswd', false),
			'S_DBUSER'		=> $settings->get_config('dbuser', false),

			'ALT_ENV'		=> get_alternative_env($settings->get_config('alt_env')),

			// Chunk settings
			'CHUNK_POST'	=> $settings->get_config('chunk_post', CHUNK_POST),
			'CHUNK_TOPIC'	=> $settings->get_config('chunk_topic', CHUNK_TOPIC),
			'CHUNK_USER'	=> $settings->get_config('chunk_user', CHUNK_USER),

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
			'OTHER_CONFIG'		=> $settings->get_other_config(),
		));

		// Output page
		qi::page_header($user->lang['QI_MAIN'], $user->lang['QI_MAIN_ABOUT']);

		$template->set_filenames(array(
			'body' => 'main_body.html')
		);

		qi::page_footer();
	}
}
