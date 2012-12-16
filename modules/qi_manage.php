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
		global $template, $user, $settings;
		global $quickinstall_path, $phpbb_root_path, $phpEx, $config, $msg_title;

		$action = request_var('action', '');
		$delete = request_var('delete', false);

		if ($delete)
		{
			$action = 'delete';
		}

		$template->assign_vars(array(
			'S_IN_INSTALL'	=> false,
			'S_IN_SETTINGS'	=> false,
			'PAGE_MAIN'		=> false,
		));

		switch ($action)
		{
			case 'delete':
				$select = request_var('select', array(0 => ''), true);
				$boards = sizeof($select);
				$error = array();

				foreach ($select as $item)
				{
					$current_item = $settings->get_boards_dir() . $item;

					// Need to get the dbname from the board.
					@include($current_item . '/config.php');

					if (!empty($dbname))
					{
						if ($dbms == 'sqlite')
						{
							$db_file = $dbhost . $dbname;

							if (file_exists($db_file))
							{
								// Assuming the DB file is created by PHP, then PHP should also have permissions to delete it.
								@unlink($db_file);
							}
						}
						else
						{
							// The order here is important, don't change it.
							$db_vars = array(
								$dbms,
								$dbhost,
								$dbuser,
								$dbpasswd,
								$dbport,
							);

							$db = db_connect($db_vars);
							$db->sql_query('DROP DATABASE IF EXISTS ' . $dbname);
							db_close($db); // Might give a error since the DB it deleted, needs to be more tested.
						}
					}

					if (!file_exists($current_item) || !is_dir($current_item))
					{
						continue;
					}

					file_functions::delete_dir($current_item);

					if (!empty(file_functions::$error))
					{
						if ($boards > 1)
						{
							$error[] = $current_item;
							file_functions::$error = array();
						}
						else
						{
							$error = file_functions::$error;
						}
					}
				}

				if (empty($error))
				{
					// Just return to main page after succesfull deletion.
					qi::redirect('index.' . $phpEx);
				}
				else
				{
					foreach ($error as $row)
					{
						$template->assign_block_vars('row', array(
							'ERROR'	=> htmlspecialchars($row),
						));
					}

					$template->assign_var('L_THE_ERROR', (($boards > 1) ? $user->lang['ERROR_DEL_BOARDS'] : $user->lang['ERROR_DEL_FILES']));

					qi::page_header($user->lang['QI_MANAGE'], $user->lang['QI_MANAGE_ABOUT']);

					$template->set_filenames(array(
						'body' => 'errors_body.html'
					));

					qi::page_footer();
				}
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

					qi::page_header($user->lang['QI_MANAGE'], $user->lang['QI_MANAGE_ABOUT']);

					$template->assign_block_vars('row', array(
						'BOARD_NAME'	=> htmlspecialchars($board),
						'BOARD_URL'		=> $settings->get_boards_url() . urlencode($board),
					));
				}

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
