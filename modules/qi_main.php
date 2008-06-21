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

		// Assign index specific vars
		$template->assign_vars(array(
			'U_CREATE'		=> qi::url('create'),

			'TABLE_PREFIX'	=> htmlspecialchars($qi_config['table_prefix']),
			'SITE_NAME'		=> $qi_config['site_name'],
			'SITE_DESC'		=> $qi_config['site_desc'],
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