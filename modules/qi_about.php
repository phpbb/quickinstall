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
				$up_to_date = (version_compare(str_replace('rc', 'RC', strtolower($qi_config['qi_version'])), str_replace('rc', 'RC', strtolower($latest_version)), '<')) ? false : true;

				$template->assign_vars(array(
					'UP_TO_DATE'	=> $up_to_date,
					'L_UPDATE'		=> sprintf($user->lang['UPDATE_TO'], $announcement_url, $latest_version),
				));
			}
		}

		$changelog_file = $quickinstall_path . 'changelog.xml';
		if ($use_changelog = file_exists($changelog_file))
		{
			// let's get the changelog :)
			$automod_path = '';
			if (file_exists($quickinstall_path . 'sources/automod/includes'))
			{
				// Let's assume they copied the contents.
				$automod_path = $quickinstall_path . 'sources/automod/';
			}
			else if (file_exists($quickinstall_path . 'sources/automod/root/includes'))
			{
				// They copied to complete root to automod instead of its contents.
				$automod_path = $quickinstall_path . 'sources/automod/root/';
			}
			else if (file_exists($quickinstall_path . 'sources/automod/upload/includes'))
			{
				// They copied to complete upload directory to automod instead of its contents.
				$automod_path = $quickinstall_path . 'sources/automod/upload/';
			}
			else
			{
				trigger_error($user->lang['NO_AUTOMOD']);
			}

			include($automod_path . 'includes/mod_parser.' . $phpEx);

			$xml_parser = new xml_array();
			$data = $xml_parser->parse($changelog_file, file_get_contents($changelog_file));

			foreach ($data[0]['children']['ENTRY'] as &$entry)
			{
				list($year, $month, $day) = explode('-', $entry['children']['DATE'][0]['data']);

				$template->assign_block_vars('history', array(
					'DATE'		=> qi::format_date(mktime(null, null, null, intval($month), intval($day), intval($year)), 'Y-m-d'),
					'VERSION'	=> $entry['children']['VERSION'][0]['data'],
				));

				foreach ($entry['children']['CHANGELOG'][0]['children']['CHANGE'] as &$change)
				{
					$template->assign_block_vars('history.changelog', array(
						'CHANGE'	=> htmlspecialchars($change['data']),
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

?>