<?php
/** 
*
* @package quickinstall
* @version $Id$
* @copyright (c) 2007 eviL3
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
	function qi_main()
	{
		global $db, $template, $user;
		global $quickinstall_path, $phpbb_root_path, $phpEx, $config, $qi_config;
		
		$mods_dir = $quickinstall_path . 'sources/mods/';
		if (file_exists($mods_dir) && is_dir($mods_dir))
		{
			$d = dir($mods_dir);
			while (false !== ($file = $d->read()))
			{
				if (in_array($file, array('.', '..', '.svn', '.htaccess')) || is_file($file))
				{
					continue;
				}
				
				$template->assign_block_vars('mods', array(
					'MOD_NAME'	=> htmlspecialchars($file),
				));
			}
			$d->close();
		}
		
		// Assign index specific vars
		$template->assign_vars(array(
			'U_CREATE'		=> qi::url('create'),
			
			'TABLE_PREFIX'	=> htmlspecialchars($qi_config['table_prefix']),
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