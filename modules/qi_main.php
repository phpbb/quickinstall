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
		global $db, $template, $user;
		global $quickinstall_path, $phpbb_root_path, $phpEx, $config, $qi_config;

		// list of boards
		$boards_arr = scandir($quickinstall_path . $qi_config['boards_dir']);
		$s_has_forums = false;
		foreach ($boards_arr as $board)
		{
			if (in_array($board, array('.', '..', '.svn', '.htaccess', '.git'), true) || is_file($quickinstall_path . 'boards/' . $board))
			{
				continue;
			}

			$s_has_forums = true;

			$template->assign_block_vars('row', array(
				'BOARD_NAME'	=> htmlspecialchars($board),
				'BOARD_URL'		=> $quickinstall_path . $qi_config['boards_dir'] . urlencode($board),
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
			'SITE_NAME'		=> $qi_config['site_name'],
			'SITE_DESC'		=> $qi_config['site_desc'],

			'S_AUTOMOD' => (empty($qi_config['automod'])) ? false : true,
			'S_MAKE_WRITABLE' => (empty($qi_config['make_writable'])) ? false : true,
			'S_POPULATE' => (empty($qi_config['populate'])) ? false : true,
			'S_REDIRECT' => (empty($qi_config['redirect'])) ? false : true,
			'S_SUBSILVER' => (empty($qi_config['subsilver'])) ? false : $qi_config['subsilver'],

			'ALT_ENV'		=> $alt_env,

			'PAGE_MAIN'		=> true,
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