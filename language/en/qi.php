<?php
/**
*
* qi [English]
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
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'ABOUT_QUICKINSTALL'	=> 'About phpBB3 QuickInstall',
	'ADMIN_NAME'			=> 'Administrator username',
	'ADMIN_PASS'			=> 'Administrator password',

	'BACK_TO_MAIN'			=> '<a href="%s">Return back to the main page</a>',
	'BACK_TO_MANAGE'		=> '<a href="%s">Return back to the management page</a>',
	'BLINKY'				=> 'Blinky',
	'BOARD_CREATED'			=> 'Board created successfully!',
	'BOARD_DBNAME'			=> 'Board database name',
	'BOARD_DESC'			=> 'Board description',
	'BOARD_NAME'			=> 'Board name',
	'BOARDS_DELETED'		=> 'The boards were deleted successfully.',
	'BOARDS_DELETED_TITLE'	=> 'Boards deleted',

	'CHANGELOG'				=> 'Changelog',

	'DB_PREFIX'				=> 'Database table prefix',
	'DELETE'				=> 'Delete',
	'DELETE_FILES_IF_EXIST'	=> 'Delete files if they exist',
	'DIR_EXISTS'			=> 'The directory %s already exists.',
	'DROP_DB_IF_EXISTS'		=> 'Drop database if it exists',

	'ENABLED'			=> 'Enabled',

	'GENERAL_ERROR'		=> 'General Error',

	'INCLUDE_MODS'			=> 'Include MODs',
	'INCLUDE_MODS_EXPLAIN'	=> 'Select folders from the sources/mods/ folder in this list, those files will then be copied to your new board’s root dir, also overwriting old files (so you can have premodded boards in here for example). If you select “None”, it will not be used (because it’s a pain to deselect items).',
	'INSTALL_BOARD'			=> 'Install a board',

	'LICENSE'			=> 'License?',
	'LICENSE_EXPLAIN'	=> 'This script is released under the terms of the <a href="license.txt">GNU General Public License version 2</a>. This is mainly because it uses large portions of phpBB’s code, which is also released under this license, and requires any modifications to use it too. But also because it’s a great license that keeps free software free :).',

	'MANAGE_BOARDS'		=> 'Manage boards',
	'MIGHT_TAKE_LONG'	=> '<strong>Please note:</strong> Creation of the board can take a while, perhaps even a minute or longer, so don’t submit the form twice.',

	'NO_BOARDS'			=> 'You have no boards.',
	'NO_DB'				=> 'No database selected.',
	'NO_MODULE'			=> 'The module %s could not be loaded.',
	'NONE'				=> 'None',

	'OPTIONS'			=> 'Options',
	'OPTIONS_ADVANCED'	=> 'Advanced options',

	'QI_ABOUT'			=> 'About',
	'QI_ABOUT_ABOUT'	=> 'Big brother loves you and wants you to be happy.',
	'QI_MAIN'			=> 'Main page',
	'QI_MAIN_ABOUT'		=> 'Install a new board here.<br /><br />“Board database name” is the only field you have to fill, the others get filled with default values from <em>includes/qi_config.php</em>.<br /><br />Click on &quot;Advanced options&quot; for more settings.',
	'QI_MANAGE'			=> 'Manage boards',
	'QI_MANAGE_ABOUT'	=> 'o_O',
	'QUICKINSTALL'		=> 'phpBB QuickInstall',

	'REDIRECT'			=> 'Redirect',

	'SELECT'			=> 'Select',
	'STAR_MANDATORY'	=> '* = mandatory',
	'SUBMIT'			=> 'Submit',
	'SUCCESS'			=> 'Success',

	'UP_TO_DATE'			=> 'Big brother says you are up to date.',
	'UP_TO_DATE_NOT'		=> 'Big brother says you are not up to date.',
	'UPDATE_CHECK_FAILED'	=> 'Big brother’s version check failed.',
	'UPDATE_TO'				=> '<a href="%1$s">Update to version %2$s.</a>',

	'VERSION_CHECK'		=> 'Big brother version check',
	'VISIT_BOARD'		=> '<a href="%s">Visit the board</a>',

	'WHAT'				=> 'What?',
	'WHAT_EXPLAIN'		=> 'phpBB3 QuickInstall is a script to quickly install phpBB. Pretty obvious... ;-)',
	'WHO_ELSE'			=> 'Who else?',
	'WHO_ELSE_EXPLAIN'	=> '<ul><li>' . implode('</li><li>', array(
		'Credits go to the phpBB team, especially the development team which created such a wonderful software.',
		'Thanks to the phpBB.com MOD team (especially josh) for blinky, which is included in this package.',
		'Thanks to Mike TUMS for the nice logo!',
		'Thanks to the beta testers!',
		'Thanks to the phpBB community including phpBB.com, startrekguide.com and phpBBModders.net!',
	)) . '</li></ul>',
	'WHO_WHEN'			=> 'Who? When?',
	'WHO_WHEN_EXPLAIN'	=> 'phpBB3 QuickInstall was created by Igor &quot;eviL&lt;3&quot; Wiedler in the summer of 2007. It was partially rewritten in march 2008.',
	'WHY'				=> 'Why?',
	'WHY_EXPLAIN'		=> 'Just as with phpBB2, if you do a lot of modding (creating modifications), you cannot put all MODs into a single phpBB installation. So it’s best to have separate installations. Now the problem is that it’s a pain to copy the files and go through the installation process every time. To speed up this process, quickinstall was born.',
));

?>