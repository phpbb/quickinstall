<?php
/**
*
* @package quickinstall
* @version $Id$
* @copyright (c) 2007, 2008 eviL3
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
		$d = dir($qi_config['boards_dir']);
		while (false !== ($file = $d->read()))
		{
			if (in_array($file, array('.', '..', '.svn', '.htaccess'), true) || is_file($qi_config['boards_dir'] . $file))
			{
				continue;
			}

			$template->assign_block_vars('row', array(
				'BOARD_NAME'	=> htmlspecialchars($file),
				'BOARD_URL'		=> $qi_config['boards_dir'] . urlencode($file),
			));
		}
		$d->close();

		// Assign index specific vars
		$template->assign_vars(array(
			'U_CREATE'		=> qi::url('create'),

			'TABLE_PREFIX'	=> htmlspecialchars($qi_config['table_prefix']),
			'SITE_NAME'		=> $qi_config['site_name'],
			'SITE_DESC'		=> $qi_config['site_desc'],

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