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
 * qi_about module
 */
class qi_about
{
	public function __construct()
	{
		global $template, $user, $quickinstall_path, $phpEx;

		include $quickinstall_path . 'includes/Parsedown/Parsedown.' . $phpEx;
		$Parsedown = new Parsedown();

		// GET README
		$doc_file = $quickinstall_path . 'README.md';
		if (file_exists($doc_file))
		{
			$doc_body = file_get_contents($doc_file);
			$template->assign_var('DOC_BODY', $Parsedown->text($doc_body));
		}

		// GET CHANGELOG
		$changelog_file = $quickinstall_path . 'CHANGELOG.md';
		if (file_exists($changelog_file))
		{
			// let's get the changelog :)
			$data = file($changelog_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

			// We do not want the first line.
			unset($data[0]);

			foreach ($data as $row)
			{
				$row = ltrim($row);

				if (strpos($row, '#') === 0)
				{
					$key = substr($row, 3);

					$template->assign_block_vars('history', array(
						'CHANGES_SINCE'	=> $key,

						'U_CHANGES'	=> strtolower(str_replace(array(' ', '.'), array('-', ''), $key)),
					));
				}
				else if (strpos($row, '-') === 0)
				{
					$change = substr($row, 2);

					$template->assign_block_vars('history.changelog', array(
						'CHANGE'	=> $Parsedown->line($change),
					));
				}
			}
		}

		$template->assign_var('S_DOCS', true);

		// Output page
		qi::page_header($user->lang['DOCS_LONG']);

		qi::page_display('about_body');
	}
}
