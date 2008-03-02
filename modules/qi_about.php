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
 * qi_about module
 */
class qi_about
{
	function qi_about()
	{
		global $db, $template, $user;
		global $quickinstall_path, $phpbb_root_path, $phpEx, $config, $qi_config;
		
		if ($qi_config['version_check'])
		{
			// we use this for get_remote_file()
			include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
			
			// Get current and latest version
			$errstr = '';
			$errno = 0;
	
			$info = get_remote_file('phpbbmodders.net', '/mods/phpbb_quickinstall_3.0.x', 'updatecheck.txt', $errstr, $errno);
	
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
			include($quickinstall_path . 'includes/functions_xml.' . $phpEx);
			
			$xml_parser = new simple_parser();
			$xml_parser->parse(file_get_contents($changelog_file));
			
			foreach ($xml_parser->data['history'][0]['child']['entry'] as &$entry)
			{
				list($year, $month, $day) = explode('-', $entry['child']['date'][0]['data']);
				
				$template->assign_block_vars('history', array(
					'DATE'		=> qi::format_date(mktime(null, null, null, intval($month), intval($day), intval($year)), 'Y-m-d'),
					'VERSION'	=> $entry['child']['version'][0]['data'],
				));
				
				foreach ($entry['child']['changelog'][0]['child']['change'] as &$change)
				{
					$template->assign_block_vars('history.changelog', array(
						'CHANGE'	=> htmlspecialchars($change['data']),
					));
				}
			}
		}
		
		$template->assign_vars(array(
			'S_ALLOW_VERSION_CHECK'	=> $qi_config['version_check'],
			'S_ALLOW_CHANGELOG'		=> $use_changelog,
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