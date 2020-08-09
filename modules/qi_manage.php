<?php
/**
*
* @package quickinstall
* @copyright (c) 2007 phpBB Limited
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
		global $user, $settings, $phpEx, $msg_title;

		$action = qi_request_var('action', '');
		$delete = qi_request_var('delete', false);

		if ($delete)
		{
			$action = 'delete';
		}

		switch ($action)
		{
			case 'delete':
				$select = qi_request_var('select', array(0 => ''), true);
				$boards = count($select);
				$error = array();

				foreach ($select as $item)
				{
					$current_item = $settings->get_boards_dir() . $item;

					// Need to get the dbname from the board.
					@include($current_item . '/config.php');

					// Attempt to delete the board from filesystem
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

					// Attempt to delete the database
					if (!empty($dbname) && !empty($dbhost) && !empty($dbms) && empty($error))
					{
						$dbms = (strpos($dbms, '\\') !== false) ? substr(strrchr($dbms, '\\'), 1) : $dbms;

						if (in_array($dbms, array('sqlite', 'sqlite3')))
						{
							$db_file = $dbhost . $dbname;

							if (file_exists($db_file))
							{
								// Assuming the DB file is created by PHP, then PHP should also have permissions to delete it.
								@unlink($db_file);
							}
							else if (file_exists($dbhost))
							{
								// Assuming the DB file is created by PHP, then PHP should also have permissions to delete it.
								@unlink($dbhost);
							}
						}
						else if (!empty($dbuser) && !empty($dbpasswd))
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
				}

				if (empty($error))
				{
					// Just return to main page after successful deletion.
					qi::redirect('index.' . $phpEx);
				}
				else
				{
					$msg_title = $user->lang['GENERAL_ERROR'];

					$msg_explain = $boards > 1 ? $user->lang['ERROR_DEL_BOARDS'] : $user->lang['ERROR_DEL_FILES'];

					$msg_text = '';
					foreach ($error as $row)
					{
						$msg_text = '<p>' . htmlspecialchars($row) . '</p>';
					}

					gen_error_msg($msg_text, $msg_title, $msg_explain);
				}
			break;
		}
	}
}
