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
 * qi_main module
 */
class qi_main
{
	public function __construct()
	{
		global $db, $template, $user, $settings;
		global $quickinstall_path, $phpbb_root_path, $phpEx, $config;

		// list of boards
		$boards_arr = scandir($settings->get_boards_dir());
		$s_has_forums = false;
		foreach ($boards_arr as $board)
		{
			if (in_array($board, array('.', '..', '.svn', '.htaccess', '.git'), true) || is_file($settings->get_boards_dir() . $board))
			{
				continue;
			}

			$s_has_forums = true;

			$template->assign_block_vars('row', array(
				'BOARD_NAME'	=> htmlspecialchars($board),
				'BOARD_URL'		=> $settings->get_boards_url() . urlencode($board),
			));
		}

		// list of alternate enviroments
		$alt_env = '<option value="">' . $user->lang['DEFAULT_ENV'] . '</option>';
		$d = dir($quickinstall_path . 'sources/phpBB3_alt');
		while (false !== ($file = $d->read()))
		{
			if (in_array($file, array('.', '..', '.svn', '.htaccess'), true) || is_file($quickinstall_path . 'sources/phpBB3_alt/' . $file))
			{
				continue;
			}

			$alt_env .= '<option>' . htmlspecialchars($file) . '</option>';
		}
		$d->close();

		// Assign index specific vars
		$template->assign_vars(array(
			'S_IN_INSTALL'	=> false,
			'S_IN_SETTINGS'	=> false,
			'S_HAS_FORUMS'	=> $s_has_forums,

			'U_CREATE'			=> qi::url('create'),
			'U_CHOOSE_PROFILE'	=> qi::url('main', array('mode' => 'change_profile')),

			'TABLE_PREFIX'	=> htmlspecialchars($settings->get_config('table_prefix', '')),
			'DB_PERFIX'		=> htmlspecialchars($settings->get_config('db_prefix', '')),
			'SITE_NAME'		=> $settings->get_config('site_name', ''),
			'SITE_DESC'		=> $settings->get_config('site_desc', ''),
			'PROFILE_OPTIONS'	=> $settings->get_profiles(),

			'S_AUTOMOD'		=> $settings->get_config('automod', 0),
			'S_MAKE_WRITABLE'	=> $settings->get_config('make_writable', 0),
			'S_POPULATE'	=> $settings->get_config('populate', 0),
			'S_REDIRECT'	=> $settings->get_config('redirect', 0),
			'S_SUBSILVER'	=> $settings->get_config('subsilver', 0),

			'ALT_ENV'		=> $alt_env,

			'PAGE_MAIN'		=> true,

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

		// Output page  $settings->get_config_part('', )
		qi::page_header($user->lang['QI_MAIN'], $user->lang['QI_MAIN_ABOUT']);

		$template->set_filenames(array(
			'body' => 'main_body.html')
		);

		qi::page_footer();
	}
}
