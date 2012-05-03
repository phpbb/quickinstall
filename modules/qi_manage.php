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
 * qi_manage module
 */
class qi_manage
{
	public function __construct()
	{
		global $db, $template, $user, $settings;
		global $quickinstall_path, $phpbb_root_path, $phpEx, $config, $qi_config, $msg_title;

		db_connect();

		$action = request_var('action', '');
		$delete = request_var('delete', false);

		if ($delete)
		{
			$action = 'delete';
		}

		switch ($action)
		{
			case 'delete':
				$select = request_var('select', array(0 => ''), true);

				foreach ($select as $item)
				{
					$current_item = $settings->get_boards_dir() . $item;

					// Need to get the dbname from the board.
					@include($current_item . '/config.php');

					if (!empty($dbname))
					{
						if ($qi_config['dbms'] == 'sqlite')
						{
							$db_file = $qi_config['dbhost'] . $dbname;

							if (file_exists($db_file))
							{
								unlink($db_file);
							}
						}
						else
						{
							$db->sql_query('DROP DATABASE IF EXISTS ' . $dbname);
						}
					}

					if (!file_exists($current_item) || !is_dir($current_item))
					{
						continue;
					}

					file_functions::delete_dir($current_item);
				}

				// Just return to main page after succesfull deletion.
				qi::redirect('index.' . $phpEx);
			break;

			default:

				// list of boards
				$boards_arr = scandir($settings->get_boards_dir());
				foreach ($boards_arr as $board)
				{
					if (in_array($board, array('.', '..', '.svn', '.htaccess', '.git'), true) || is_file($settings->get_boards_dir() . $board))
					{
						continue;
					}

					$template->assign_block_vars('row', array(
						'BOARD_NAME'	=> htmlspecialchars($board),
						'BOARD_URL'		=> $settings->get_boards_url() . urlencode($board),
					));
				}

				$template->assign_vars(array(
					'S_IN_INSTALL' => false,
					'S_IN_SETTINGS' => false,
					'PAGE_MAIN'		=> false,
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
