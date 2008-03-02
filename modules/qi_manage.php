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
 * qi_manage module
 */
class qi_manage
{
	function qi_manage()
	{
		global $db, $template, $user;
		global $quickinstall_path, $phpbb_root_path, $phpEx, $config, $qi_config, $msg_title;
		
		$action = request_var('action', '');
		$delete = request_var('delete', false);
		
		if ($delete)
		{
			$action = 'delete';
		}
		
		switch ($action)
		{
			case 'delete':
				
				$select = request_var('select', array(0 => ''));
				
				foreach ($select as $item)
				{
					$current_item = $qi_config['boards_dir'] . $item;
					
					$db->sql_query('DROP DATABASE IF EXISTS ' . $qi_config['database_prefix'] . $item);
					
					if (!file_exists($current_item) || !is_dir($current_item))
					{
						continue;
					}
					
					file_functions::delete_dir($current_item);
				}
				
				$msg_title = 'BOARDS_DELETED_TITLE';
				trigger_error($user->lang['BOARDS_DELETED'] . '<br /><br />' . sprintf($user->lang['BACK_TO_MANAGE'], qi::url('manage')));
				
				break;
				
			default:
				
				// list of boards
				
				$d = dir($qi_config['boards_dir']);
				while (false !== ($file = $d->read()))
				{
					if (in_array($file, array('.', '..', '.svn', '.htaccess')) || is_file($qi_config['boards_dir'] . $file))
					{
						continue;
					}
					
					$template->assign_block_vars('row', array(
						'BOARD_NAME'	=> htmlspecialchars($file),
						'BOARD_URL'		=> $qi_config['boards_dir'] . urlencode($file),
					));
				}
				$d->close();
				
				$template->assign_vars(array(
				));
				
				// Output page
				qi::page_header($user->lang['QI_MANAGE'], $user->lang['QI_MANAGE_ABOUT']);
				
				$template->set_filenames(array(
					'body' => 'manage_body.html')
				);
				
				qi::page_footer();
				
				break;
		}
	}
}

?>