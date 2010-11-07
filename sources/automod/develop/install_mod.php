<?php
/**
*
* @package automod
* @version $Id$
* @copyright (c) 2009 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
*
*/

/**
* install a MOD using the command-line
* copy me to ..
*/

// make sure it's run through cli
if (php_sapi_name() != 'cli')
{
	exit("This script must be executed from the command-line\n");
}

// args to be taken
$args = array(
	'board'		=> getcwd(),
	'mod_file'	=> false,
	'verbose'	=> false,
);

// parse the args
for ($i = 1; $i < sizeof($argv); $i++)
{
	if ($argv[$i][0] == '-')
	{
		switch ($argv[$i])
		{
			case '-b':
			case '--board':
				if (!isset($argv[$i + 1]))
				{
					echo "Error: no value supplied for argument {$argv[$i]}\n";
					exit;
				}
				
				if (!is_dir($argv[$i + 1]))
				{
					echo "Error: given board does not exist\n";
					exit;
				}
				
				$args['board'] = $argv[++$i];
				
				if (substr($args['board'], -1) != '/')
				{
					$args['board'] .= '/';
				}
			break;
			case '-v':
			case '--verbose':
				$args['verbose'] = true;
			break;
			case '-h':
			case '--help':
				echo "USAGE: install_mod [OPTIONS] MOD_FILE\n";
				echo "\n";
				echo "OPTIONS:\n";
				echo "	-b, --board       path to the phpBB installation, defaults to pwd\n";
				echo "	-v, --verbose     say what's happening, defaults to false\n";
				echo "	-h, --help        display this message\n";
				exit;
			break;
			default:
				echo "unknown option '{$argv[$i]}'\n";
				exit;
			break;
		}
	}
	else
	{
		if (!is_file($argv[$i]))
		{
			echo "Error: given MOD_FILE does not exist\n";
			exit;
		}
		
		$args['mod_file'] = $argv[$i];
	}
}

// validate args
if ($args['mod_file'] === false)
{
	// mod_file must be set
	echo "USAGE: install_mod [OPTIONS] MOD_FILE\n";
	exit;
}

if (!file_exists($args['board'] . 'config.php'))
{
	echo "Error: given board is no phpBB install\n";
	exit;
}

// set up some vars
$phpbb_root_path = $args['board'];
$phpEx = 'php';
$mod_path = $args['mod_file'];
define('IN_PHPBB', true);

include('./language/en/acp/mods.' . $phpEx);
$user->lang = &$lang;

// hack some config stuff
require($phpbb_root_path . 'config.' . $phpEx);
//require($phpbb_root_path . 'includes/cache.' . $phpEx);
require($phpbb_root_path . 'includes/constants.' . $phpEx);
require($phpbb_root_path . 'includes/db/' . $dbms . '.' . $phpEx);

$db = new $sql_db();
$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false, defined('PHPBB_DB_NEW_LINK') ? PHPBB_DB_NEW_LINK : false);
unset($dbpasswd);

$config['am_dir_perms'] = 755;
$config['am_file_perms'] = 644;

// -------------------------- begin stealing from acp_mods

// include automod files
include("{$phpbb_root_path}includes/functions_transfer.$phpEx");
include("./includes/editor.$phpEx");
include("./includes/functions_mods.$phpEx");
include("./includes/mod_parser.$phpEx");

$edited_root = '../../mod/';
$backup_root = '';

$editor = new editor_direct();
$editor->create_edited_root($edited_root);

$parser = new parser('xml');
$parser->set_file($mod_path);
$actions = $parser->get_actions();

$mod_installed = true;

// not all MODs will have edits (!)
if (isset($actions['EDITS']))
{
	foreach ($actions['EDITS'] as $filename => $edits)
	{
		// see if the file to be opened actually exists
		if (!file_exists("$phpbb_root_path$filename"))
		{
			$mod_installed = false;

			continue;
		}
		else
		{
			$status = $editor->open_file($filename, $backup_root);
			if (is_string($status))
			{
				$mod_installed = false;
				continue;
			}

			foreach ($edits as $finds)
			{
				$comment = '';
				foreach ($finds as $find => $commands)
				{
					if (isset($finds['comment']) && !$comment && $finds['comment'] != $user->lang['UNKNOWN_MOD_COMMENT'])
					{
						$comment = $finds['comment'];
						unset($finds['comment']);
					}

					if ($find == 'comment')
					{
						continue;
					}

					$offset_ary = $editor->find($find);

					// special case for FINDs with no action associated
					if (is_null($commands))
					{
						continue;
					}

					foreach ($commands as $type => $contents)
					{
						if (!$offset_ary)
						{
							$offset_ary['start'] = $offset_ary['end'] = false;
						}

						$status = false;
						$contents_orig = $contents;

						switch (strtoupper($type))
						{
							case 'AFTER ADD':
								$status = $editor->add_string($find, $contents, 'AFTER', $offset_ary['start'], $offset_ary['end']);
							break;

							case 'BEFORE ADD':
								$status = $editor->add_string($find, $contents, 'BEFORE', $offset_ary['start'], $offset_ary['end']);
							break;

							case 'INCREMENT':
							case 'OPERATION':
								//$contents = "";
								$status = $editor->inc_string($find, '', $contents);
							break;

							case 'REPLACE WITH':
								$status = $editor->replace_string($find, $contents, $offset_ary['start'], $offset_ary['end']);
							break;

							case 'IN-LINE-EDIT':
								// these aren't quite as straight forward.  Still have multi-level arrays to sort through
								$inline_comment = '';
								foreach ($contents as $inline_edit_id => $inline_edit)
								{
									if ($inline_edit_id === 'inline-comment')
									{
										// This is a special case for tucking comments in the array
										if ($inline_edit != $user->lang['UNKNOWN_MOD_INLINE-COMMENT'])
										{
											$inline_comment = $inline_edit;
										}
										continue;
									}
								
									foreach ($inline_edit as $inline_find => $inline_commands)
									{
										foreach ($inline_commands as $inline_action => $inline_contents)
										{
											// inline finds are pretty contancerous, so so them in the loop
											$line = $editor->inline_find($find, $inline_find, $offset_ary['start'], $offset_ary['end']);
											if (!$line)
											{
												// find failed
												$status = $mod_installed = false;
												continue 2;
											}

											$inline_contents = $inline_contents[0];
											$contents_orig = $inline_find;

											switch (strtoupper($inline_action))
											{
												case 'IN-LINE-':
													$editor->last_string_offset = $line['string_offset'] + $line['find_length'] - 1;
													$status = true;
													continue;
												break;

												case 'IN-LINE-BEFORE-ADD':
													$status = $editor->inline_add($find, $inline_find, $inline_contents, 'BEFORE', $line['array_offset'], $line['string_offset'], $line['find_length']);
												break;

												case 'IN-LINE-AFTER-ADD':
													$status = $editor->inline_add($find, $inline_find, $inline_contents, 'AFTER', $line['array_offset'], $line['string_offset'], $line['find_length']);
												break;

												case 'IN-LINE-REPLACE':
												case 'IN-LINE-REPLACE-WITH':
													$status = $editor->inline_replace($find, $inline_find, $inline_contents, $line['array_offset'], $line['string_offset'], $line['find_length']);
												break;

												case 'IN-LINE-OPERATION':
													$status = $editor->inc_string($find, $inline_find, $inline_contents);
												break;

												default:
													trigger_error("Error, unrecognised command $inline_action"); // ERROR!
												break;
											}
										}

										if (!$status)
										{
											$mod_installed = false;
										}

										$editor->close_inline_edit();
									}
								}
							break;

							default:
								trigger_error("Error, unrecognised command $type"); // ERROR!
							break;
						}

						if (!$status)
						{
							$mod_installed = false;
						}
					}
				}

				$editor->close_edit();
			}
		}

		$status = $editor->close_file("{$edited_root}$filename");
		if (is_string($status))
		{
			echo "ERROR: $status\n";

			$mod_installed = false;
		}
	}
} // end foreach

// Move included files
if (isset($actions['NEW_FILES']) && !empty($actions['NEW_FILES']) && $change && ($mod_installed || $force_install))
{
	foreach ($actions['NEW_FILES'] as $source => $target)
	{
		$status = $editor->copy_content($mod_root . str_replace('*.*', '', $source), str_replace('*.*', '', $target));

		if ($status !== true && !is_null($status))
		{
			$mod_installed = false;
		}
	}
}


// Perform SQL queries last -- Queries usually cannot be done a second
// time, so do them only if the edits were successful.  Still complies
// with the MODX spec in this location
if (!empty($actions['SQL']) && ($mod_installed || $force_install || ($display && !$change)))
{
	$template->assign_var('S_SQL', true);

	parser::parse_sql($actions['SQL']);

	$db->sql_return_on_error(true);

	foreach ($actions['SQL'] as $query)
	{
		if ($change)
		{
			$query_success = $db->sql_query($query);

			if (!$query_success)
			{
				$error = $db->sql_error();
				
				if ($args['verbose'])
				{
					echo "Error: SQL Error\n";
					echo "$query\n";
					echo "{$error['message']}\n";
					exit;
				}

				$mod_installed = false;
			}
		}
	}

	$db->sql_return_on_error(false);
}

// Move edited files back
$status = $editor->commit_changes($edited_root, '');

if (is_string($status))
{
	echo "ERROR: $status\n";
	exit;
}

if (!$mod_installed)
{
	echo "ERROR: mod install failed\n";
	exit;
}

// end of script
$db->sql_close();
