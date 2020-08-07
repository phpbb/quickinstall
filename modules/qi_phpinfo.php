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
 * qi_phpinfo module
 *
 * Shamelessly borrowed from phpBB.
 */
class qi_phpinfo
{
	public function __construct()
	{
		global $template, $user;

		ob_start();
		phpinfo(INFO_GENERAL | INFO_CONFIGURATION | INFO_MODULES | INFO_VARIABLES);
		$phpinfo = ob_get_clean();
		$phpinfo = trim($phpinfo);

		preg_match_all('#<body[^>]*>(.*)</body>#si', $phpinfo, $output);

		if (empty($phpinfo) || empty($output))
		{
			trigger_error($user->lang['NO_PHPINFO_AVAILABLE'], E_USER_WARNING);
		}

		$output = $output[1][0];

		// expose_php can make the image not exist
		if (preg_match('#<a[^>]*><img[^>]*></a>#', $output))
		{
			$output = preg_replace('#<tr class="v"><td>(.*?<a[^>]*><img[^>]*></a>)(.*?)</td></tr>#s', '<tr class="row1"><td><table class="type2"><tr><td>\2</td><td>\1</td></tr></table></td></tr>', $output);
		}
		else
		{
			$output = preg_replace('#<tr class="v"><td>(.*?)</td></tr>#s', '<tr class="row1"><td><table class="type2"><tr><td>\1</td></tr></table></td></tr>', $output);
		}
		$output = preg_replace('#<table[^>]*>#i', '<table class="table table-bordered">', $output);
		$output = preg_replace('#<img border="0"#i', '<img', $output);
		$output = str_replace(array('class="e"', 'class="v"', 'class="h"', '<hr />', '<font', '</font>'), array('', '', '', '', '<span', '</span>'), $output);
		// Add searchable class to all but the 1st table
		$output = preg_replace_callback('#<table class="(.*)">#', function($matches) {
			static $index = 0;
			if ($index++ === 0)
			{
				return $matches[0];
			}
			return '<table class="' . $matches[1] . ' searchable">';
		}, $output);
		// Process all the anchors for the menu
		$anchor = '#<a name="(.*)">(.*)</a>#';
		preg_match_all($anchor, $output, $matches);
		foreach ($matches[1] as $key => $match)
		{
			$template->assign_block_vars('phpinfo', array(
				'U_ANCHOR'	=> $matches[1][$key],
				'TITLE'		=> $matches[2][$key],
			));
		}
		$output = preg_replace($anchor, '<span class="anchor" id="$1">$2</span>', $output);

		// Fix invalid anchor names (eg "module_Zend Optimizer")
		$output = preg_replace_callback('#<a name="([^"]+)">#', array($this, 'remove_spaces'), $output);

		if (empty($output))
		{
			trigger_error($user->lang['NO_PHPINFO_AVAILABLE'], E_USER_WARNING);
		}

		$orig_output = $output;

		preg_match_all('#<div class="center">(.*)</div>#siU', $output, $output);
		$output = (!empty($output[1][0])) ? $output[1][0] : $orig_output;

		$template->assign_vars(array(
			'S_PHPINFO'	=> true,
			'PHPINFO'	=> $output,
		));

		// Output page
		qi::page_header('PHPINFO');

		qi::page_display('phpinfo_body');
	}

	public function remove_spaces($matches)
	{
		return '<a name="' . str_replace(' ', '_', $matches[1]) . '">';
	}
}
