<?php
/**
*
* @package quickinstall
* @copyright (c) 2007 phpBB Limited
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
 * qi_docs module
 */
class qi_docs
{
	public function run()
	{
		global $template, $quickinstall_path;

		// GET README
		$doc_file = $quickinstall_path . 'README.md';
		if (file_exists($doc_file))
		{
			$doc_body = file_get_contents($doc_file);
			$doc_body = $this->add_readme_anchors($doc_body);
			$doc_body = Parsedown::instance()->text($doc_body);
			$doc_body = str_replace(
				['<table>', '<blockquote>', '<h2>'],
				['<table class="table table-sm table-striped">', '<blockquote class="callout callout-warning">', '<h2 class="border-bottom pt-3 pb-2">'],
				$doc_body
			);
			$template->assign_var('DOC_BODY', $doc_body);
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
					$change = str_replace(
						['[Fix]', '[Change]', '[Feature]'],
						['<span class="badge bg-primary">Fix</span>', '<span class="badge bg-warning">Change</span>', '<span class="badge bg-success">Feature</span>'],
						$change);

					$template->assign_block_vars('history.changelog', array(
						'CHANGE'	=> Parsedown::instance()->line($change),
					));
				}
			}
		}

		$template->assign_var('S_DOCS', true);

		// Output page
		qi::page_header('DOCS_LONG');

		qi::page_display('docs_body');
	}

	private function add_readme_anchors($doc_body)
	{
		$anchors = array();
		$lines = preg_split('/\R/', $doc_body);
		$in_code_block = false;

		foreach ($lines as $index => $line)
		{
			if (preg_match('/^\s*(```|~~~)/', $line))
			{
				$in_code_block = !$in_code_block;
			}

			if ($in_code_block || !preg_match('/^ {0,3}##(?!#)[ \t]+(.+)$/', $line, $matches))
			{
				continue;
			}

			$title = preg_replace('/\s+#+\s*$/', '', trim($matches[1]));
			$anchor = $this->generate_anchor($title, 'readme');

			if (isset($anchors[$anchor]))
			{
				$anchors[$anchor]++;
				$anchor .= '-' . $anchors[$anchor];
			}
			else
			{
				$anchors[$anchor] = 1;
			}

			$lines[$index] = '<div id="' . $anchor . '" class="anchor"></div>' . "\n\n" . $line;
		}

		return implode("\n", $lines);
	}

	private function generate_anchor($title, $prefix)
	{
		$anchor = html_entity_decode(strip_tags($title), ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$anchor = strtolower($anchor);
		$anchor = preg_replace('/[^\pL\pN]+/u', '-', $anchor);
		$anchor = trim($anchor, '-');

		return $prefix . '-' . ($anchor ?: 'section');
	}
}
