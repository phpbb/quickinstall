<?php
/**
*
* qi [English]
*
* @package quickinstall
* @version $Id$
* @copyright (c) 2007, 2008 eviL3
* @copyright (c) 2010 Jari Kanerva (tumba25)
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
	'ABOUT_QUICKINSTALL' => 'About phpBB3 QuickInstall',
	'ADMIN_EMAIL' => 'Admin email',
	'ADMIN_EMAIL_EXPLAIN' => 'Admin email to use for your forums',
	'ADMIN_NAME' => 'Administrator username',
	'ADMIN_NAME_EXPLAIN' => 'The default admin username to use at your forums. This can be changed when forums are created.',
	'ADMIN_PASS' => 'Administrator password',
	'ADMIN_PASS_EXPLAIN' => 'The default admin password to use at your forums. This can be changed when forums are created.',
	'ALT_ENV' => 'Alternate environment',
	'AUTOMOD' => 'AutoMOD',
	'AUTOMOD_EXPLAIN' => 'Set install AutoMOD as yes by default. This can be changed when you create a forum.',
	'AUTOMOD_INSTALL' => 'Install AutoMOD',

	'BACK_TO_MAIN' => '<a href="%s">Return back to the main page</a>',
	'BACK_TO_MANAGE' => '<a href="%s">Return back to the management page</a>',
	'BOARD_CREATED' => 'Board created successfully!',
	'BOARD_DBNAME' => 'Board database and directory name',
	'BOARD_DESC' => 'Board description',
	'BOARD_EMAIL' => 'Board email',
	'BOARD_EMAIL_EXPLAIN' => 'Sender email for your created forums.',
	'BOARD_NAME' => 'Board name',
	'BOARDS_DELETED' => 'The boards were deleted successfully.',
	'BOARDS_DELETED_TITLE' => 'Boards deleted',
	'BOARDS_DIR' => 'Boards directory',
	'BOARDS_DIR_EXPLAIN' => 'The directory where your forums will be created. PHP needs to have write permissions to this directory.',
	'BOARDS_LIST' => 'List of boards',
	'BOARDS_NOT_WRITABLE' => 'The boards directory is not writable.',

	'CACHE_NOT_WRITABLE' => 'The cache directory is not writable.',
	'CHANGELOG' => 'Changelog',
	'CHECK_ALL' => 'Check all',
	'CONFIG_EMPTY' => 'The config array was empty. This is probably worth a bug report.',
	'CONFIG_NOT_WRITABLE' => 'The qi_config.php file is not writable.',
	'COOKIE_DOMAIN' => 'Cookie domain',
	'COOKIE_DOMAIN_EXPLAIN' => 'This should typically be localhost.',
	'COOKIE_SECURE' => 'Cookie secure',
	'COOKIE_SECURE_EXPLAIN' => 'If your server is running via SSL set this to enabled else leave as disabled. Having this enabled and not running via SSL will result in server errors during redirects.',

	'DB_EXISTS' => 'The database %s already exists.',
	'DB_PREFIX' => 'Database table prefix',
	'DB_PREFIX_EXPLAIN' => 'This is added before all database names to avoid overwriting databases not used by QuickInstall.',
	'DBHOST' => 'Database server',
	'DBHOST_EXPLAIN' => 'Usually localhost.',
	'DBMS' => 'DBMS',
	'DBMS_EXPLAIN' => 'Your database system. If you are unsure set it to MySQL.',
	'DBPASSWD' => 'Database password',
	'DBPASSWD_EXPLAIN' => 'The password for your database user',
	'DBPORT' => 'Database port',
	'DBPORT_EXPLAIN' => 'Can mostly be left empty.',
	'DBUSER' => 'Database user',
	'DBUSER_EXPLAIN' => 'Your database user. This needs to be a user with permissions to create new databases.',
	'DEFAULT' => 'default',
	'DEFAULT_ENV' => 'Default environment (latest phpBB)',
	'DEFAULT_LANG' => 'Default language',
	'DEFAULT_LANG_EXPLAIN' => 'This language will be used for the created forums.',
	'DELETE' => 'Delete',
	'DELETE_FILES_IF_EXIST' => 'Delete files if they exist',
	'DIR_EXISTS' => 'The directory %s already exists.',
	'DISABLED' => 'Disabled',
	'DROP_DB_IF_EXISTS' => 'Drop database if it exists',

	'EMAIL_ENABLE' => 'Enable email',
	'EMAIL_ENABLE_EXPLAIN' => 'Enable board wide emails. For a local test forum this would typically be off, unless you test the emails.',
	'ENABLED' => 'Enabled',

	'GENERAL_ERROR' => 'General Error',

	'IN_SETTINGS' => 'Manage your QuickInstall settings.',
	'INCLUDE_MODS' => 'Include MODs',
	'INCLUDE_MODS_EXPLAIN' => 'Select folders from the sources/mods/ folder in this list, those files will then be copied to your new board’s root dir, also overwriting old files (so you can have premodded boards in here for example). If you select “None”, it will not be used (because it’s a pain to deselect items).',
	'INSTALL_BOARD' => 'Install a board',
	'INSTALL_QI' => 'Install QuickInstall',

	'LICENSE' => 'License?',
	'LICENSE_EXPLAIN' => 'This script is released under the terms of the <a href="license.txt">GNU General Public License version 2</a>. This is mainly because it uses large portions of phpBB’s code, which is also released under this license, and requires any modifications to use it too. But also because it’s a great license that keeps free software free :).',
	'LOG_INSTALL_INSTALLED_QI'	=> '<strong>Installed by phpBB Quickinstall version %s</strong>',

	'MAKE_WRITABLE' => 'Make files world writable',
	'MAKE_WRITABLE_EXPLAIN' => 'Set files, config.php, and directories world writable by default. This can be changed when you create a forum.',
	'MANAGE_BOARDS' => 'Manage boards',
	'MIGHT_TAKE_LONG' => '<strong>Please note:</strong> Creation of the board can take a while, perhaps even a minute or longer, so <strong>don’t</strong> submit the form twice.',

	'NEED_WRITABLE' => 'QuickInstall needs the boards and cache directories to be writable all the time.<br />The qi_config.php only needs to be writable for the installation of QuickInstall.',
	'NO' => 'No',
	'NO_ALT_ENV' => 'The specified alternative environment doesn’t exist.',
	'NO_BOARDS' => 'You have no boards.',
	'NO_DB' => 'No database selected.',
	'NO_IMPACT_WIN' => 'This setting has no impact on Windows systems.',
	'NO_MODULE' => 'The module %s could not be loaded.',
	'NO_PASSWORD' => 'No password',
	'NO_DBPASSWD_ERR' => 'You have set a db password and checked no password. You can’t both <strong>have</strong> and <strong>not have</strong> a password',
	'NONE' => 'None',

	'ONLY_LOCAL' => 'Please note: QuickInstall is only intended to be used locally.<br />It should not be used on a web server accessible via the internet.',
	'OPTIONS' => 'Options',
	'OPTIONS_ADVANCED' => 'Advanced options',

	'POPULATE' => 'Populate board',
	'POPULATE_MAIN_EXPLAIN' => 'Users: tester x, Password: 123456',
	'POPULATE_EXPLAIN' => 'Populates the board with 5 users (tester 1 - 5), some topics and posts. The users will get password 123456 and email username@your-boards-domain. This can be changed when you create a forum.',

	'QI_ABOUT' => 'About',
	'QI_ABOUT_ABOUT' => 'Big brother loves you and wants you to be happy.',
	'QI_DST' => 'Daylight saving time',
	'QI_DST_EXPLAIN' => 'Do you want daylight saving time to be on or off?',
	'QI_LANG' => 'QuickInstall language',
	'QI_LANG_EXPLAIN' => 'The language that QuickInstall should use. There needs to be a directory with this name in language/. This language will also be used as default language for your forums if that language exists in sources/phpBB3/language/.',
	'QI_MAIN' => 'Main page',
	'QI_MAIN_ABOUT' => 'Install a new board here.<br /><br />“Board database name” is the only field you have to fill, the others get filled with default values from <em>includes/qi_config.php</em>.<br /><br />Click on “Advanced options” for more settings.',
	'QI_MANAGE' => 'Manage boards',
	'QI_MANAGE_ABOUT' => 'o_O',
	'QI_TZ' => 'Time zone',
	'QI_TZ_EXPLAIN' => 'Your time zone. It will be the defaut time zone for the created forums. -1, 0, 1 etc.',
	'QUICKINSTALL' => 'phpBB QuickInstall',

	'REDIRECT' => 'Redirect',
	'REDIRECT_EXPLAIN' => 'Set redirect to new forums as yes by default. This can be changed when you create a forum.',
	'REDIRECT_BOARD' => 'Redirect to new board',
	'REQUIRED' => 'is required',
	'RESET' => 'Reset',

	'SELECT' => 'Select',
	'SETTINGS' => 'Settings',
	'SETTINGS_FAILURE' => 'There were errors, take a look in the box below.',
	'SETTINGS_SUCCESS' => 'Your settings were successfully saved.',
	'SERVER_NAME' => 'Server name',
	'SERVER_NAME_EXPLAIN' => 'This should typically be localhost since QuickInstall is <strong>not</strong> intended for public servers.',
	'SERVER_PORT' => 'Server port',
	'SERVER_PORT_EXPLAIN' => 'Usually 80.',
	'SITE_DESC' => 'Site description',
	'SITE_DESC_EXPLAIN' => 'The default description for your forum(s). This can be changed when forums are created.',
	'SITE_NAME' => 'Site name',
	'SITE_NAME_EXPLAIN' => 'The default site name that will be used for your forums. This can be changed when forums are created.',
	'SMTP_AUTH' => 'Authentication method for SMTP',
	'SMTP_AUTH_EXPLAIN' => 'Only used if a username/password is set.',
	'SMTP_DELIVERY' => 'Use SMTP server for e-mail',
	'SMTP_DELIVERY_EXPLAIN' => 'Select “Yes” if you want or have to send e-mail via a named server instead of the local mail function.',
	'SMTP_HOST' => 'SMTP server address',
	'SMTP_HOST_EXPLAIN' => 'The address of the SMTP server you want to use',
	'SMTP_PASS' => 'SMTP password',
	'SMTP_PASS_EXPLAIN' => 'Only enter a password if your SMTP server requires it.',
	'SMTP_PORT' => 'SMTP server port',
	'SMTP_PORT_EXPLAIN' => 'Only change this if you know your SMTP server is on a different port.',
	'SMTP_USER' => 'SMTP username',
	'SMTP_USER_EXPLAIN' => 'Only enter a username if your SMTP server requires it.',
	'STAR_MANDATORY' => '* = mandatory',
	'SUBMIT' => 'Submit',
	'SUBSILVER' => 'Install Subsilver2',
	'SUBSILVER_EXPLAIN' => 'Select if you want the Subsilver2 theme to be installed and if you want it to be the default style. This can be changed when you create a forum.',
	'SUCCESS' => 'Success',

	'TABLE_PREFIX' => 'Table prefix',
	'TABLE_PREFIX_EXPLAIN' => 'The table prefix that will be used for your forums. You can change this in the advanced options when you create new forums.',

	'UNCHECK_ALL' => 'Uncheck all',
	'UP_TO_DATE' => 'Big brother says you are up to date.',
	'UP_TO_DATE_NOT' => 'Big brother says you are not up to date.',
	'UPDATE_CHECK_FAILED' => 'Big brother’s version check failed.',
	'UPDATE_TO' => '<a href="%1$s">Update to version %2$s.</a>',

	'YES' => 'Yes',

	'VERSION_CHECK' => 'Big brother version check',
	'VISIT_BOARD' => '<a href="%s">Visit the board</a>',

	'WHAT' => 'What?',
	'WHAT_EXPLAIN' => 'phpBB3 QuickInstall is a script to quickly install phpBB. Pretty obvious... ;-)',
	'WHO_ELSE' => 'Who else?',
	'WHO_ELSE_EXPLAIN' => '<ul><li>' . implode('</li><li>', array(
		'Credits go to the phpBB team, especially the development team which created such a wonderful piece of software.',
		'Thanks to the phpBB.com MOD team (especially Josh, aka “A_Jelly_Doughnut”) for AutoMOD, which is included in this package.',
		'Thanks to Mike TUMS for the nice logo!',
		'Thanks to the beta testers!',
		'Thanks to the phpBB community including phpBB.com, startrekguide.com and phpBBModders.net!',
	)) . '</li></ul>',
	'WHO_WHEN' => 'Who? When?',
	'WHO_WHEN_EXPLAIN' => 'phpBB3 QuickInstall was originally created by Igor “eviL&lt;3” Wiedler in the summer of 2007. It was partially rewritten by him in march 2008.<br />Since March 2010 this project is mantained by Jari “Tumba25” Kanerva.',
	'WHY' => 'Why?',
	'WHY_EXPLAIN' => 'Just as with phpBB2, if you do a lot of modding (creating modifications), you cannot put all MODs into a single phpBB installation. So it’s best to have separate installations. Now the problem is that it’s a pain to copy the files and go through the installation process every time. To speed up this process, quickinstall was born.',
));

?>