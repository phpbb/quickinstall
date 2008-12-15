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
 * qi_manage module
 */
class qi_manage
{
	public function __construct()
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
					$current_item = $quickinstall_path . 'boards/' . $item;

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

				$d = dir($quickinstall_path . 'boards');
				while (false !== ($file = $d->read()))
				{
					if (in_array($file, array('.', '..', '.svn', '.htaccess'), true) || is_file($quickinstall_path . 'boards/' . $file))
					{
						continue;
					}

					$template->assign_block_vars('row', array(
						'BOARD_NAME'	=> htmlspecialchars($file),
						'BOARD_URL'		=> $quickinstall_path . 'boards/' . urlencode($file),
					));
				}
				$d->close();

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