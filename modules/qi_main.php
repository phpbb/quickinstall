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
		global $quickinstall_path, $phpbb_root_path, $phpEx, $config, $qi_config;

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
			'U_CREATE'		=> qi::url('create'),
			'S_IN_INSTALL' => false,
			'S_IN_SETTINGS' => false,
			'S_HAS_FORUMS' => $s_has_forums,

			'TABLE_PREFIX'	=> htmlspecialchars($qi_config['table_prefix']),
			'DB_PERFIX'		=> htmlspecialchars($qi_config['db_prefix']),
			'SITE_NAME'		=> $qi_config['site_name'],
			'SITE_DESC'		=> $qi_config['site_desc'],

			'S_AUTOMOD' => (empty($qi_config['automod'])) ? false : true,
			'S_MAKE_WRITABLE' => (empty($qi_config['make_writable'])) ? false : true,
			'S_POPULATE' => (empty($qi_config['populate'])) ? false : true,
			'S_REDIRECT' => (empty($qi_config['redirect'])) ? false : true,
			'S_SUBSILVER' => (empty($qi_config['subsilver'])) ? false : $qi_config['subsilver'],

			'ALT_ENV'		=> $alt_env,

			'PAGE_MAIN'		=> true,

			// Populate settings.
			'NUM_USERS' => (!empty($qi_config['num_users'])) ? $qi_config['num_users'] : 0,
			'NUM_NEW_GROUP' => (!empty($qi_config['num_new_group'])) ? $qi_config['num_new_group'] : 0,
			'CREATE_MOD' => (!empty($qi_config['create_mod'])) ? 1 : 0,
			'CREATE_ADMIN' => (!empty($qi_config['create_admin'])) ? 1 : 0,
			'NUM_CATS' => (!empty($qi_config['num_cats'])) ? $qi_config['num_cats'] : 0,
			'NUM_FORUMS' => (!empty($qi_config['num_forums'])) ? $qi_config['num_forums'] : 0,
			'NUM_TOPICS_MIN' => (!empty($qi_config['num_topics_min'])) ? $qi_config['num_topics_min'] : 0,
			'NUM_TOPICS_MAX' => (!empty($qi_config['num_topics_max'])) ? $qi_config['num_topics_max'] : 0,
			'NUM_REPLIES_MIN' => (!empty($qi_config['num_replies_min'])) ? $qi_config['num_replies_min'] : 0,
			'NUM_REPLIES_MAX' => (!empty($qi_config['num_replies_max'])) ? $qi_config['num_replies_max'] : 0,
			'EMAIL_DOMAIN' => (!empty($qi_config['email_domain'])) ? $qi_config['email_domain'] : '',
			'GRANT_PERMISSIONS' => (!empty($qi_config['grant_permissions'])) ? $qi_config['grant_permissions'] : '',
		));

		// Output page
		qi::page_header($user->lang['QI_MAIN'], $user->lang['QI_MAIN_ABOUT']);

		$template->set_filenames(array(
			'body' => 'main_body.html')
		);

		qi::page_footer();
	}
}

?>
