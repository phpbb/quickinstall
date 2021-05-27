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
		global $settings, $phpEx;

		$delete = qi_request_var('delete', false);

		if ($delete)
		{
			$select = qi_request_var('select', array(0 => ''), true);
			$boards = count($select);
			$error = array();

			foreach ($select as $item)
			{
				$current_item = $settings->get_boards_dir() . $item;

				// First get config-file data for the board
				$cfg_file = $current_item . '/config.' . $phpEx;
				$dbhost = $dbport = $dbname = $dbuser = $dbpasswd = $dbms = '';
				if (file_exists($cfg_file))
				{
					include $cfg_file;
				}

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
				$header = $boards > 1 ? 'ERROR_DEL_BOARDS' : 'ERROR_DEL_FILES';

				$message = '<h5>' . qi::lang($header) . '</h5>';
				foreach ($error as $row)
				{
					$message .= '<p>' . htmlspecialchars($row) . '</p>';
				}

				if (strlen($message) > 1024)
				{
					// We need to define $msg_long_text here to circumvent text stripping.
					global $msg_long_text;
					$msg_long_text = $message;

					trigger_error(false, E_USER_NOTICE);
				}

				trigger_error($message, E_USER_NOTICE);
			}
		}
	}
}
