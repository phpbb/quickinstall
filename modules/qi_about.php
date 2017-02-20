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

		include $quickinstall_path . 'includes/ParseDown.' . $phpEx;
		$Parsedown = new Parsedown();

		$s_docs = legacy_request_var('page', '') === 'docs';

		if ($s_docs)
		{
			$doc_file = $quickinstall_path . 'README.md';

			if (file_exists($doc_file))
			{
				$doc_body = file_get_contents($doc_file);
				$template->assign_var('DOC_BODY', $Parsedown->text($doc_body));
			}
		}
		else
		{
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

					if ($row[0] === '#')
					{
						$key = substr($row, 3);

						$template->assign_block_vars('history', array(
							'CHANGES_SINCE'	=> $key,

							'U_CHANGES'	=> strtolower(str_replace(' ', '-', $key)),
						));
					}
					else if ($row[0] === '-')
					{
						$change = substr($row, 2);

						$template->assign_block_vars('history.changelog', array(
							'CHANGE'	=> $Parsedown->line($change),
						));
					}
				}
			}
		}

		$template->assign_vars(array(
			'S_DOCS'	=> $s_docs,
			'S_ABOUT'	=> !$s_docs,
		));

		// Output page
		qi::page_header(($s_docs ? $user->lang['DOCS_LONG'] : $user->lang['QI_ABOUT']), ' ');

		$template->set_filenames(array(
			'body' => 'about_body.html',
		));

		qi::page_footer();
	}
}
