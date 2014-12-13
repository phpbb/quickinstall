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
		global $db, $template, $user, $settings, $s_docs;
		global $quickinstall_path, $phpbb_root_path, $phpEx, $config;

		if (legacy_request_var('page', '') == 'docs')
		{
			$s_about	= false;
			$s_docs		= true;
		}
		else
		{
			$s_about	= true;
			$s_docs		= false;
		}

		if ($s_about)
		{
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
		}
		else // if ($s_docs)
		{
			$doc_file = $quickinstall_path . 'readme.txt';

			if (file_exists($doc_file))
			{
				$file_ary	= file($doc_file, FILE_IGNORE_NEW_LINES);
				$preg_url	= '!(http|http)(s)?:\/\/[a-zA-Z0-9.?&_/]+!';
				$end		= sizeof($file_ary);

				// We do not want the first line.
				for ($i = 1; $i < $end; $i++)
				{
					if (empty($file_ary[$i]) && empty($file_ary[$i + 1]))
					{
						$i = $i + 2;
						$doc_row = "</p>\n<h3>{$file_ary[$i]}</h3>\n<p>";
					}
					else if (empty($file_ary[$i]) && !empty($file_ary[$i + 1]))
					{
						$doc_row = "<br /><br />\n";
					}
					else
					{
						if(preg_match($preg_url, $file_ary[$i], $url))
						{
							$doc_row = preg_replace($preg_url, "<a href=\"\\0\">\\0</a>",$file_ary[$i]);
						}
						else
						{
							$doc_row = "$file_ary[$i]\n";
						}
					}

					$template->assign_block_vars('doc_row', array(
						'ROW' => (!empty($doc_row)) ? $doc_row : '',
					));
				}

				$template->assign_block_vars('doc_row', array(
					'ROW' => '</p>',
				));
/*
				foreach ($data as $row)
				{
					if (empty($row))
					{
						if ($p_start && $empty_row == 1)
						{
							$doc_row = "</p>\n";
							$p_start = false;
						}
						else if ($p_start && $empty_row == 0)
						{
							$doc_row = "<br />\n";
						}

						$empty_row++;
					}
					else if ($empty_row == 2)
					{
						$doc_row = str_replace(':', '', $row);
						$doc_row = "<h3>$doc_row</h3>\n<p>";
						$p_start = true;
						$empty_row = 0;
					}
					else
					{
						if(preg_match($preg_url, $row, $url))
						{
							$doc_row = preg_replace($preg_url, "<a href=\"\\0\">\\0</a>",$row);
						}
						else
						{
							$doc_row = "$row\n";
						}

						$empty_row = 0;
					}

					$template->assign_block_vars('doc_row', array(
						'ROW' => (!empty($doc_row)) ? $doc_row : '',
					));

					$doc_row = '';
				}
*/

			}
		}

		$template->assign_vars(array(
			'S_ABOUT'	=> $s_about,
			'S_DOCS'	=> $s_docs,
		));

		// Output page
		qi::page_header((($s_about) ? $user->lang['QI_ABOUT'] : $user->lang['QI_ABOUT']), ' ');

		$template->set_filenames(array(
			'body' => 'about_body.html',
		));

		qi::page_footer();
	}
}
