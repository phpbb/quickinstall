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
 * qi_about module
 */
class qi_about
{
	public function __construct()
	{
		global $db, $template, $user, $settings;
		global $quickinstall_path, $phpbb_root_path, $phpEx, $config, $qi_config;

		if ($qi_config['version_check'])
		{
			// we use this for get_remote_file()
			include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);

			// Get current and latest version
			$errstr = '';
			$errno = 0;

			$info = get_remote_file('phpbbmodders.net', '/files/updatecheck', 'quickinstall.txt', $errstr, $errno);

			if ($info !== false)
			{
				list($latest_version, $announcement_url) = explode("\n", $info);
				$up_to_date = (version_compare(str_replace('rc', 'RC', strtolower(QI_VERSION)), str_replace('rc', 'RC', strtolower($latest_version)), '<')) ? false : true;

				$template->assign_vars(array(
					'UP_TO_DATE'	=> $up_to_date,
					'L_UPDATE'		=> sprintf($user->lang['UPDATE_TO'], $announcement_url, $latest_version),
				));
			}
		}

		$changelog_file = $quickinstall_path . 'CHANGELOG';
		if ($use_changelog = file_exists($changelog_file))
		{
			// let's get the changelog :)
			$data = file($changelog_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

			// We do not want the first line.
			unset($data[0]);

			$changes_ary = array();
			$key = 0; // Make sure the key is set to something.
			foreach ($data as $row)
			{
				$row = ltrim($row);

				if ($row[0] == '-')
				{
					$key = substr($row, 2);
					$changes_ary[$key] = array();
				}
				else
				{
					$changes_ary[$key][] = substr($row, 2);
				}
			}

			foreach ($changes_ary as $key => $entry)
			{
				$template->assign_block_vars('history', array(
					'CHANGES_SINCE'	=> $key,
				));

				foreach ($entry as $change)
				{
					$template->assign_block_vars('history.changelog', array(
						'CHANGE'	=> htmlspecialchars($change),
					));
				}
			}
		}

		$template->assign_vars(array(
			'S_IN_INSTALL' => false,
			'S_IN_SETTINGS' => false,
			'S_ALLOW_VERSION_CHECK'	=> $qi_config['version_check'],
			'S_ALLOW_CHANGELOG'		=> $use_changelog,
			'PAGE_MAIN'		=> false,
		));

		// Output page
		qi::page_header($user->lang['QI_ABOUT'], $user->lang['QI_ABOUT_ABOUT']);

		$template->set_filenames(array(
			'body' => 'about_body.html')
		);

		qi::page_footer();
	}
}
