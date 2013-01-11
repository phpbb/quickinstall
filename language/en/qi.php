<?php
/**
*
* qi [English]
*
* @package quickinstall
* @copyright (c) 2007 phpBB Group
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
	'ABOUT_SECTIONS'		=> 'About sections',
	'ADMIN_EMAIL'			=> 'Admin email',
	'ADMIN_EMAIL_EXPLAIN'	=> 'Admin email to use for your boards',
	'ADMIN_NAME'			=> 'Administrator username',
	'ADMIN_NAME_EXPLAIN'	=> 'The default admin username to use at your boards. This can be changed when boards are created.',
	'ADMIN_PASS'			=> 'Administrator password',
	'ADMIN_PASS_EXPLAIN'	=> 'The default admin password to use at your boards. This can be changed when boards are created.',
	'ADMIN_SETTINGS'		=> 'Admin settings',
	'ALT_ENV'				=> 'Alternate environment',
	'AUTOMOD'				=> 'AutoMOD',
	'AUTOMOD_EXPLAIN'		=> 'Set install AutoMOD as yes by default.',
	'AUTOMOD_INSTALL'		=> 'Install AutoMOD',

	'BACK_TO_MAIN'			=> '<a href="%s">Return back to the main page</a>',
	'BACK_TO_MANAGE'		=> '<a href="%s">Return back to the management page</a>',
	'BACK_TOP'				=> 'Back to the top',
	'BOARD_CREATED'			=> 'Board created successfully!',
	'BOARD_DBNAME'			=> 'Board database and directory name',
	'BOARD_DESC'			=> 'Board description',
	'BOARD_EMAIL'			=> 'Board email',
	'BOARD_EMAIL_EXPLAIN'	=> 'Sender email for your created boards.',
	'BOARD_NAME'			=> 'Board name',
	'BOARDS_DELETED'		=> 'The boards were deleted successfully.',
	'BOARDS_DELETED_TITLE'	=> 'Boards deleted',
	'BOARDS_DIR'			=> 'Boards directory',
	'BOARDS_DIR_EXPLAIN'	=> 'The directory where your boards will be created. PHP needs to have write permissions to this directory.',
	'BOARDS_DIR_MISSING'	=> 'The directory &quot;%s&quot; does not exist or is not writeable.',
	'BOARDS_LIST'			=> 'List of boards',
	'BOARDS_NOT_WRITABLE'	=> 'The boards directory is not writable.',
	'BOARDS_URL'			=> 'Boards URL prefix',
	'BOARDS_URL_EXPLAIN'	=> 'URL prefix to the boards directory. If you specify an absolute directory in the boards directory setting above, you may need to provide a domain and/or path here that leads to the boards directory. If boards directory is a relative path, you may just copy it here.',

	'CACHE_DIR'				=> 'Cache directory',
	'CACHE_DIR_EXPLAIN'		=> 'The directory where quickinstall stores various files. PHP needs to have write permissions to this directory.',
	'CACHE_DIR_MISSING'		=> 'The directory &quot;%s&quot; does not exist or is not writeable.',
	'CACHE_NOT_WRITABLE'	=> 'The cache directory is not writable.',
	'CANNOT_DELETE_LAST_PROFILE'	=> 'You can not delete the last profile.',
	'CHANGELOG'				=> 'Changelog',
	'CHECK_ALL'				=> 'Check all',
	'CHUNK_POST'			=> 'Post chunk',
	'CHUNK_POST_EXPLAIN'	=> 'The number of posts that will be sent to the database in each query.',
	'CHUNK_SETTINGS'	=> 'Chunk settings',
	'CHUNK_SETTINGS_EXPLAIN'	=> 'QuickInstall tries to reduce the number of queries it generates by creating posts, topics and users in chunks. The chunk sizes affects the time it takes to populate a board. There is no general setting that is perfect for everybody. If you do a lot of populating with QuickInstall you might want to experiment with these settings. Too large chunks will use to much memory and to small will query the DB to often. I believe the default settings to be the best compromise.',
	'CHUNK_TOPIC'			=> 'Topic chunk',
	'CHUNK_TOPIC_EXPLAIN'	=> 'The number of topics that will be sent to the database in each query.',
	'CHUNK_USER'			=> 'User chunk',
	'CHUNK_USER_EXPLAIN'	=> 'The number of users that will be sent to the database in each query.',
	'CONFIG_BUTTON'			=> 'Click here to see the configuration.',
	'CONFIG_CONVERTED'		=> 'Your configuration has been updated from the old style with one config file to the new style where you can save profiles. It has been saved with the name &quot;default&quot;.<br />You can now save settings for different profiles and load them when you create a board.',
	'CONFIG_EMPTY'			=> 'The config array was empty. This is probably worth a bug report.',
	'CONFIG_IS_DISPLAYED'	=> 'Configuration is displayed below. You can try manually writing it into a file in the settings direcotry.<br />Make sure the file name ends in &quot;.cfg&quot; for example &quot;settings/main.cfg&quot;.',
	'CONFIG_NOT_WRITABLE'	=> 'The &quot;settings/&quot; directory is not writable.',
	'CONFIG_NOT_WRITTEN'	=> 'The &quot;settings/%s.cfg&quot; file could not be written.',
	'CONFIG_OPTIONS'		=> 'Config options',
	'CONFIG_SETTINGS'		=> 'Site config settings',
	'CONFIG_SETTINGS_EXPLAIN'	=> 'The settings here are board wide settings for your phpBB board. These can also be set when new boards are created.',
	'CONFIG_WARNING'		=> 'Click the button below to see the configuration. <strong>Warning:</strong> passwords you entered will be displayed.',
	'COOKIE_DOMAIN'			=> 'Cookie domain',
	'COOKIE_DOMAIN_EXPLAIN'	=> 'This should typically be localhost.',
	'COOKIE_SECURE'			=> 'Cookie secure',
	'COOKIE_SECURE_EXPLAIN'	=> 'If your server is running via SSL set this to enabled else leave as disabled. Having this enabled and not running via SSL will result in server errors during redirects.',
	'CREATE_ADMIN'			=> 'Create admin',
	'CREATE_ADMIN_EXPLAIN'	=> 'Set to yes if you want one admin created, this will not be a founder. This will be tester_1.',
	'CREATE_MOD'			=> 'Create moderator',
	'CREATE_MOD_EXPLAIN'	=> 'Set to yes if you want one global moderator created. This will be tester_1 or tester_2 if a admin is selected.',

	'DB_EXISTS'			=> 'The database &quot;%s&quot; already exists.',
	'DB_PREFIX'			=> 'Database prefix',
	'DB_PREFIX_EXPLAIN'	=> 'This is added before all database names to avoid overwriting databases not used by QuickInstall.',
	'DB_SETTINGS'		=> 'Database settings',
	'DBHOST'			=> 'Database server',
	'DBHOST_EXPLAIN'	=> 'Usually &quot;localhost&quot;.<br />If you use SQLite this needs to be the absolute path to a directory where your web server has write permissions.',
	'DBMS'				=> 'DBMS',
	'DBMS_EXPLAIN'		=> 'Your database system. If you are unsure set it to MySQL.',
	'DBPASSWD'			=> 'Database password',
	'DBPASSWD_EXPLAIN'	=> 'The password for your database user.',
	'DBPORT'			=> 'Database port',
	'DBPORT_EXPLAIN'	=> 'Can mostly be left empty.',
	'DBUSER'			=> 'Database user',
	'DBUSER_EXPLAIN'	=> 'Your database user. This needs to be a user with permissions to create new databases.',
	'DEFAULT'			=> 'default',
	'DEFAULT_ENV'		=> 'Default environment (latest phpBB)',
	'DEFAULT_LANG'		=> 'Default language',
	'DEFAULT_LANG_EXPLAIN'	=> 'This language will be used for the created boards. The language pack needs to be in &quot;sources/phpBB3/language&quot; to be visible in the list.',
	'DELETE'			=> 'Delete',
	'DELETE_FILES_IF_EXIST'	=> 'Delete files if they exist',
	'DELETE_FILES_IF_EXIST_EXPLAIN'	=> 'Have &quot;Delete files if they exist&quot; checked by default when creating boards.',
	'DELETE_PROFILE'	=> 'Delete profile',
	'DELETE_PROFILE_EXPLAIN'	=> 'Deletes the selected profile.<br /><strong>Note: This cannot be undone.</strong>',
	'DIR_EXISTS'		=> 'The directory &quot;%s&quot; already exists.',
	'DIR_URL_SETTINGS'	=> 'Directory and URL settings',
	'DISABLED'			=> 'Disabled',
	'DROP_DB_IF_EXISTS'	=> 'Drop database if it exists',
	'DROP_DB_IF_EXISTS_EXPLAIN'	=> 'Have &quot;Drop database if it exists&quot; checked by default when creating boards.',

	'EMAIL_DOMAIN'			=> 'Email domain',
	'EMAIL_DOMAIN_EXPLAIN'	=> 'The email domain to use for the testers. Their email will be tester_x@&lt;domain.com&gt;.',
	'EMAIL_ENABLE'			=> 'Enable email',
	'EMAIL_ENABLE_EXPLAIN'	=> 'Enable board wide emails. For a local test board this would typically be off, unless you test the emails.',
	'EMAIL_SETTINGS'		=> 'E-mail settings',
	'ENABLED'				=> 'Enabled',
	'ERRORS'				=> 'There were errors',
	'ERROR_DEL_BOARDS'		=> 'The following boards could not be deleted',
	'ERROR_DEL_FILES'		=> 'The following files could not be deleted',

	'FUNCTIONS_MODS_MISSING'	=> '&quot;includes/functions_mods.php&quot; not found.',
	'FORGOT_THIS'				=> 'You forgot this!',

	'GENERAL_ERROR'		=> 'General Error',
	'GO'				=> 'Go',
	'GO_QI_MAIN'		=> '%sGo to QuickInstall main page%s',
	'GO_QI_SETTINGS'	=> '%sGo to settings%s',
	'GRANT_PERMISSIONS'	=> 'Grant additional permissions',
	'GRANT_PERMISSIONS_EXPLAIN'	=> '(e.g. 0060 for group read/write)',

	'IF_EMPTY_EXPLAIN'		=> 'If empty the default from config will be used.',
	'IF_LEAVE_EMPTY'		=> 'If you leave this empty you will have to fill it in when you create a board.',
	'IN_SETTINGS'			=> 'Manage your QuickInstall settings.',
	'INCLUDE_MODS'			=> 'Include MODs',
	'INCLUDE_MODS_EXPLAIN'	=> 'Select folders from the &quot;sources/mods/&quot; folder in this list, those files will then be copied to your new board’s root dir, also overwriting old files (so you can have premodded boards in here for example). If you select &quot;None&quot;, it will not be used (because it’s a pain to deselect items).',
	'INSTALL_BOARD'			=> 'Install a board',
	'INSTALL_QI'			=> 'Install QuickInstall',
	'IS_NOT_VALID'			=> 'Is not valid.',

	'LANG_SELECT'		=> 'Language select',
	'LICENSE'			=> 'License?',
	'LICENSE_EXPLAIN'	=> 'This script is released under the terms of the <a href="license.txt">GNU General Public License version 2</a>. This is mainly because it uses large portions of phpBB’s code, which is also released under this license, and requires any modifications to use it too. But also because it’s a great license that keeps free software free :).',
	'LOG_INSTALL_INSTALLED_QI'	=> '<strong>Installed by phpBB Quickinstall version %s</strong>',

	'MAKE_WRITABLE'			=> 'Make files world writable',
	'MAKE_WRITABLE_EXPLAIN'	=> 'Set files, &quot;config.php&quot;, and directories world writable by default.',
	'MAKE_WRITABLE_BOARD'	=> 'Make files world writable',
	'MAKE_WRITABLE_BOARD_EXPLAIN'	=> '(same as granting permissions of 0666)',
	'MANAGE_BOARDS'			=> 'Manage boards',
	'MAX'					=> 'Max',
	'MIGHT_TAKE_LONG'		=> '<strong>Please note:</strong> Creation of the board can take a while, perhaps even a minute or longer, so <strong>don’t</strong> submit the form twice.',
	'MIN'					=> 'Min',

	'NEED_CONVERT'		=> 'Your config file needs to be converted to the new settings style with profiles. Make sure the &quot;settings&quot; directory exists and is writable by PHP. Then click submit.',
	'NEED_EMAIL_DOMAIN'	=> 'A e-mail domain is needed to create test users',
	'NEED_WRITABLE'		=> 'QuickInstall needs the &quot;boards&quot; and &quot;cache&quot; directories to be writable all the time.<br />The &quot;settings&quot; directory needs to be in the QuickInstall root path and it also needs to be writable.',
	'NO'				=> 'No',
	'NO_ALT_ENV'		=> 'The specified alternative environment doesn’t exist.',
	'NO_AUTOMOD'		=> '<strong>AutoMOD not found in the sources directory.</strong><br />You need to download AutoMOD and copy the contents of the &quot;root&quot; directory to &quot;sources/automod&quot;. If you use AutoMOD 1.0.0. it is the contents of the &quot;upload&quot; directory.',
	'NO_AUTOMOD_TITLE'	=> 'AutoMOD not found',
	'NO_BOARDS'			=> 'You have no boards.',
	'NO_DB'				=> 'No database selected.',
	'NO_IMPACT_WIN'		=> 'This setting has no impact on Windows systems older than Win7.',
	'NO_MODULE'			=> 'The module &quot;%s&quot; could not be loaded.',
	'NO_PASSWORD'		=> 'No password',
	'NO_DBPASSWD_ERR'	=> 'You have set a db password and checked no password. You can’t both <strong>have</strong> and <strong>not have</strong> a password',
	'NONE'				=> 'None',
	'NUM_CATS'			=> 'Number of categories',
	'NUM_CATS_EXPLAIN'	=> 'The number of forum categories to create.',
	'NUM_FORUMS'		=> 'Number of forums',
	'NUM_FORUMS_EXPLAIN'	=> 'The number of forums to create, they will be spread evenly over the created categories.',
	'NUM_NEW_GROUP'		=> 'Newly registered',
	'NUM_NEW_GROUP_EXPLAIN'	=> 'The number of users to place in the newly registered group.<br />If this number is larger thant the number of users, all new users will be in the newly registered group.',
	'NUM_REPLIES'		=> 'Number of replies',
	'NUM_REPLIES_EXPLAIN'	=> 'The number of replies. Each topic will receive a random number between these max and min values of replies.',
	'NUM_TOPICS'		=> 'Number of topics',
	'NUM_TOPICS_EXPLAIN'	=> 'The number of topics to create in each forum. Each forum will get a random number of topics between these max and min values.',
	'NUM_USERS'			=> 'Number of users',
	'NUM_USERS_EXPLAIN'	=> 'The number of users to populate your new board with.<br />They will get the username Tester_x (x is 1 to num_users). They will all get the password "123456"',

	'OFF'					=> 'Off',
	'ON'					=> 'On',
	'ONLY_LOCAL'			=> '<strong>Welcome to QuickInstall (QI)</strong>, a tool to quickly install a phpBB board for testing.<br /><br />Some default settings have been loaded below. The only things you need to enter are &quot;Database user&quot; and &quot;Database password&quot; if you want those to be stored by QuickInstall. But it would be a good idea to also check the rest of the settings.<br />Make sure the &quot;boards&quot;, &quot;cache&quot; and &quot;settings&quot; directories exist and are writable by PHP.<br /><br />Once you have checked the settings and required directories simply click on the &quot;Submit&quot; button and this profile will be saved under the name &quot;default&quot;. If you want some other name you can enter it in the &quot;Save as new profile&quot; field.<div class="errorbox"><strong>Please note</strong>: QuickInstall is only intended to be used locally and should not be used on a web server accessible via the internet (public web server). <strong>If you decide to use it on a public web server it is entirely at your own risk.</strong> There is no support provided if using QuickInstall up on a public web server.</div>',
	'OPTIONS'				=> 'Options',
	'OPTIONS_ADVANCED'		=> 'Advanced options',
	'OTHER_CONFIG'			=> 'Other board config settings',
	'OTHER_CONFIG_EXPLAIN'	=> 'These will be updated in the config table or added to the config table if they don’t exist yet. So make sure to spell correctly. These can also be edited when creating the boards.<br />One config setting per line in a semicolon &quot;;&quot; separated list. Config-name; config-setting; dynamic, if the setting is not dynamic the dynamic part is not needed. Lines starting with a # are considered comments and not added to the DB. Example:<br />load_tplcompile;1;1<br />session_length;999999<br /># this is a comment',
	'OTHER_SETTINGS'		=> 'Other settings',

	'PHPINFO'			=> 'PHP information',
	'PHPINFO_EXPLAIN'	=> 'This page lists information on the version of PHP installed on this server. It includes details of loaded modules, available variables and default settings. This information may be useful when diagnosing problems. Please be aware that some hosting companies will limit what information is displayed here for security reasons. You are advised to not give out any details on this page except when asked by official team members on the support forums.',
	'PLAIN_TEXT'		=> '<strong>Note</strong>: QuickInstall stores passwords and usernames as plain text.',
	'POPULATE'			=> 'Populate board',
	'POPULATE_EXPLAIN'	=> 'Populates the board with the number of users, forums, posts and topics you specify below. Do note that the more users, forums, posts and topics you want, the longer time the forum creation will take.<br />All these settings can be changed when you create a board.',
	'POPULATE_MAIN_EXPLAIN'	=> 'Users: tester x, Password: 123456',
	'POPULATE_OPTIONS'	=> 'Populate options',
	'POPULATE_SETTINGS'	=> 'Populate settings',
	'PROFILE'			=> 'Profile',
	'PROFILES'			=> 'Profiles',

	'QI_ABOUT'			=> 'About QuickInstall',
	'QI_DST'			=> 'Daylight saving time',
	'QI_DST_EXPLAIN'	=> 'Do you want daylight saving time to be on or off?',
	'QI_LANG'			=> 'Select QuickInstall language',
	'QI_LANG_EXPLAIN'	=> 'The language will be visible when the language pack is available in the directory &quot;language/&quot;',
	'QI_MAIN'			=> 'Install boards',
	'QI_MAIN_ABOUT'		=> 'Install a new board here.<br /><br />&quot;Board database and directory name:&quot; is the only field you have to fill, the others get filled with default values from &quot;includes/default_settings.php&quot;.<br /><br />Click on “Advanced options” for more settings.',
	'QI_MANAGE'			=> 'Manage boards',
	'QI_MANAGE_ABOUT'	=> '&nbsp;',
	'QI_MANAGE_PROFILE'	=> 'Manage profiles',
	'QI_TZ'				=> 'Time zone',
	'QI_TZ_EXPLAIN'		=> 'Your time zone. It will be the defaut time zone for the created boards. -1, 0, 1 etc.',
	'QUICKINSTALL'		=> 'phpBB QuickInstall',

	'REDIRECT'			=> 'Redirect',
	'REDIRECT_EXPLAIN'	=> 'Set redirect to new boards as yes by default.',
	'REDIRECT_BOARD'	=> 'Redirect to new board',
	'REQUIRED'			=> 'is required',
	'RESET'				=> 'Reset',
	'RETURN_MANAGE'		=> 'Return to the Manage tab',

	'SAVE_PROFILE'		=> 'Save as new profile',
	'SAVE_PROFILE_EXPLAIN'	=> 'Write the name for a new profile for these settings. Allowed chars are A-Z, a-z, 0-9, &quot;-&quot; (minus sign), &quot;_&quot; (underscore) and &quot;.&quot; (dot)<br /><strong>Note: If a profile with this name already exists, it will be overwritten.</strong>',
	'SAVE_RESTORE'		=> 'Save/Restore',
	'SELECT'			=> 'Select',
	'SELECT_PROFILE'	=> 'Select profile',
	'SETTINGS'			=> 'Settings',
	'SETTINGS_FAILURE'	=> 'There were errors, take a look in the box below.',
	'SETTINGS_NOT_WRITABLE'	=> 'The settings directory do not exist, is not a directory or is not writable.',
	'SETTINGS_SECTIONS'	=> 'Settings sections',
	'SETTINGS_SUCCESS'	=> 'Your settings were successfully saved.',
	'SERVER_COOKIE_SETTINGS'	=> 'Server and cookie settings',
	'SERVER_NAME'		=> 'Server name',
	'SERVER_NAME_EXPLAIN'	=> 'This should typically be localhost since QuickInstall is <strong>not</strong> intended for public servers.',
	'SERVER_PORT'		=> 'Server port',
	'SERVER_PORT_EXPLAIN'	=> 'Usually &quot;80&quot;.',
	'SHOW_CONFIRM'		=> 'Confirm delete',
	'SHOW_CONFIRM_EXPLAIN'	=> 'Show confirm requester when deleting boards (forums) and profiles.',
	'SITE_DESC'			=> 'Site description',
	'SITE_DESC_EXPLAIN'	=> 'The default description for your board(s). This can be changed when boards are created.',
	'SITE_NAME'			=> 'Site name',
	'SITE_NAME_EXPLAIN'	=> 'The default site name that will be used for your boards. This can be changed when boards are created.',
	'SMTP_AUTH'			=> 'Authentication method for SMTP',
	'SMTP_AUTH_EXPLAIN'	=> 'Only used if a username/password is set.',
	'SMTP_DELIVERY'		=> 'Use SMTP server for e-mail',
	'SMTP_DELIVERY_EXPLAIN'	=> 'Select &quot;Yes&quot; if you want or have to send e-mail via a named server instead of the local mail function.',
	'SMTP_HOST'			=> 'SMTP server address',
	'SMTP_HOST_EXPLAIN'	=> 'The address of the SMTP server you want to use',
	'SMTP_PASS'			=> 'SMTP password',
	'SMTP_PASS_EXPLAIN'	=> 'Only enter a password if your SMTP server requires it.',
	'SMTP_PORT'			=> 'SMTP server port',
	'SMTP_PORT_EXPLAIN'	=> 'Only change this if you know your SMTP server is on a different port.',
	'SMTP_USER'			=> 'SMTP username',
	'SMTP_USER_EXPLAIN'	=> 'Only enter a username if your SMTP server requires it.',
	'SQLITE_PATH_MISSING'	=> 'The entered database server path is either missing or not writeable.',
	'STAR_MANDATORY'	=> '* = mandatory',
	'SUBMIT'			=> 'Submit',
	'SUBSILVER'			=> 'Install Subsilver2',
	'SUBSILVER_EXPLAIN'	=> 'Select if you want the Subsilver2 theme to be installed and if you want it to be the default style.',
	'SUCCESS'			=> 'Success',
	'SURE_DELETE_PROFILE'	=> 'Are you sure you want to delete this profile? It cannot be undone.',
	'SURE_DELETE_BOARDS'	=> 'Are you sure you want to delete these boards/this board? It cannot be undone.',

	'TABLE_PREFIX'			=> 'Table prefix',
	'TABLE_PREFIX_EXPLAIN'	=> 'The table prefix that will be used for your boards. You can change this in the advanced options when you create new boards.',
	'TEST_CAT_NAME'			=> 'Test category %d',
	'TEST_FORUM_NAME'		=> 'Test forum %d',
	'TEST_POST_START'		=> 'Test post %d', // This will be on the first line in each post and then filled with lorem ipsum.
	'TEST_TOPIC_TITLE'		=> 'Test topic %d',
	'THESE_CAN_CHANGE'		=> 'These settings can be changed when you create a board.',
	'THIS_CAN_CHANGE'		=> 'This can be changed when you create a board.',
	'TIME_SETTINGS'			=> 'Time settings',

	'UNCHECK_ALL'		=> 'Uncheck all',

	'YES'	=> 'Yes',

	'WHAT'				=> 'What?',
	'WHAT_EXPLAIN'		=> 'phpBB3 QuickInstall is a script to quickly install phpBB. Pretty obvious... ;-)',
	'WHO_ELSE'			=> 'Who else?',
	'WHO_ELSE_EXPLAIN'	=> '<ul><li>' . implode('</li><li>', array(
		'Credits go to the phpBB team, especially the development team which created such a wonderful piece of software.',
		'Thanks to the phpBB.com MOD team (especially Josh, aka “A_Jelly_Doughnut”) for AutoMOD.',
		'Thanks to Mike TUMS for the nice logo!',
		'Thanks to the beta testers!',
		'Thanks to the phpBB community including phpBB.com, startrekguide.com and phpBBModders.net!',
	)) . '</li></ul>',
	'WHO_WHEN'			=> 'Who? When?',
	'WHO_WHEN_EXPLAIN'	=> 'phpBB3 QuickInstall was originally created by Igor “igorw” Wiedler in the summer of 2007. It was partially rewritten by him in march 2008.<br />Since March 2010 this project is mantained by Jari “tumba25” Kanerva.',
	'WHY'				=> 'Why?',
	'WHY_EXPLAIN'		=> 'Just as with phpBB2, if you do a lot of modding (creating modifications), you cannot put all MODs into a single phpBB installation. So it’s best to have separate installations. Now the problem is that it’s a pain to copy the files and go through the installation process every time. To speed up this process, quickinstall was born.',
));
