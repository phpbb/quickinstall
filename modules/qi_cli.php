<?php
/**
*
* @package quickinstall
* @copyright (c) 2026 phpBB Limited
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
 * qi_cli module
 */
class qi_cli
{
	public function run()
	{
		global $template, $quickinstall_path;

		$doc_file = $quickinstall_path . 'docs/sandbox-cli.md';
		if (file_exists($doc_file))
		{
			$doc_body = file_get_contents($doc_file);
			foreach (qi::get_markdown_anchors($doc_body, 'cli') as $anchor)
			{
				$template->assign_block_vars('cli_nav', $anchor);
			}

			$template->assign_var('CLI_BODY', qi::render_markdown($doc_body, 'cli'));
		}

		$template->assign_var('S_CLI', true);

		qi::page_header('CLI_DOCS');

		qi::page_display('cli_body');
	}
}
