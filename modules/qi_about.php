<?php
/**
*
* @package quickinstall
* @copyright (c) 2007 phpBB Group
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
 * qi_about module
 */
class qi_about
{
	public function __construct()
	{
		global $db, $template, $user, $settings;
		global $quickinstall_path, $phpbb_root_path, $phpEx, $config;

		$changelog_file = $quickinstall_path . 'CHANGELOG';
		if (file_exists($changelog_file))
		{
			// let's get the changelog :)
			$data = file($changelog_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

			// We do not want the first line.
			unset($data[0]);

			foreach ($data as $row)
			{
				$row = ltrim($row);

				if ($row[0] == '-')
				{
					$key = substr($row, 2);

					$template->assign_block_vars('history', array(
						'CHANGES_SINCE'	=> $key,

						'U_CHANGES'	=> strtolower(str_replace(' ', '-', $key)),
					));
				}
				else
				{
					$change = substr($row, 2);

					$template->assign_block_vars('history.changelog', array(
						'CHANGE'	=> htmlspecialchars($change),
					));
				}
			}
		}

		$template->assign_vars(array(
			'S_IN_INSTALL'	=> false,
		));

		// Output page
		qi::page_header($user->lang['QI_ABOUT'], ' ');

		$template->set_filenames(array(
			'body' => 'about_body.html',
		));

		qi::page_footer();
	}
}
