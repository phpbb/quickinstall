<?php
/**
*
* qi [English]
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

	'BACK_TOP'				=> 'Back to the top',
	'BOARD_CONFIG_OPTIONS'	=> 'Board config options',
	'BOARD_CONFIG_SETTINGS'	=> 'Board config settings',
	'BOARD_CONFIG_SETTINGS_EXPLAIN'	=> 'These are board wide settings for your phpBB boards.',
	'BOARD_DBNAME'			=> 'Board database and directory name',
	'BOARD_DESC'			=> 'Board description',
	'BOARD_EMAIL'			=> 'Board email',
	'BOARD_EMAIL_EXPLAIN'	=> 'Sender email for your created boards.',
	'BOARD_NAME'			=> 'Board name',
	'BOARDS'				=> 'Boards',
	'BOARDS_DIR'			=> 'Boards directory',
	'BOARDS_DIR_EXPLAIN'	=> 'The directory where your boards will be created. PHP needs to have write permissions to this directory.',
	'BOARDS_DIR_MISSING'	=> 'The directory &quot;%s&quot; does not exist or is not writeable.',
	'BOARDS_LIST'			=> 'List of boards',
	'BOARDS_URL'			=> 'Boards URL prefix',
	'BOARDS_URL_EXPLAIN'	=> 'URL prefix to the boards directory. If you specified an absolute directory in the boards directory setting above, you may need to provide a domain and/or path here that leads to the boards directory. If boards directory is a relative path, you may just copy it here.',

	'CACHE_DIR'				=> 'Cache directory',
	'CACHE_DIR_EXPLAIN'		=> 'The directory where QuickInstall stores various files. PHP needs to have write permissions to this directory.',
	'CACHE_DIR_MISSING'		=> 'The directory &quot;%s&quot; does not exist or is not writeable.',
	'CANNOT_DELETE_LAST_PROFILE'	=> 'You can not delete the last profile.',
	'CHANGELOG'				=> 'Changelog',
	'CHECK_ALL'				=> 'Check all',
	'CHUNK_POST'			=> 'Post chunk',
	'CHUNK_POST_EXPLAIN'	=> 'The number of posts that will be sent to the database in each query. Default: 1000.',
	'CHUNK_SETTINGS'	=> 'Chunk settings',
	'CHUNK_SETTINGS_EXPLAIN'	=> 'QuickInstall tries to reduce the number of queries generated from creating posts, topics and users by using chunks. The chunk size affects the time it takes to populate a board. There is no general setting that is perfect for everybody. If you do a lot of populating with QuickInstall you might want to experiment with these settings. Larger chunks may use too much memory while smaller chunks will query the DB more often. We have found the default settings to be the best compromise.',
	'CHUNK_TOPIC'			=> 'Topic chunk',
	'CHUNK_TOPIC_EXPLAIN'	=> 'The number of topics that will be sent to the database in each query. Default: 2000.',
	'CHUNK_USER'			=> 'User chunk',
	'CHUNK_USER_EXPLAIN'	=> 'The number of users that will be sent to the database in each query. Default: 5000.',
	'CONFIG_BUTTON'			=> 'Click here to see the configuration.',
	'CONFIG_CONVERTED'		=> 'Your configuration has been updated from the old style with one config file to the new style where you can save profiles. It has been saved with the name &quot;default&quot;.<br />You can now save settings for different profiles and load them when you create a board.',
	'CONFIG_EMPTY'			=> 'The config array was empty. This is probably worth a bug report.',
	'CONFIG_IS_DISPLAYED'	=> 'Configuration is displayed below. You can try manually writing it into a file in the settings direcotry.<br />Make sure the file name ends in &quot;.cfg&quot; for example &quot;settings/main.cfg&quot;.',
	'CONFIG_NOT_WRITABLE'	=> 'The &quot;settings/&quot; directory is not writable.',
	'CONFIG_NOT_WRITTEN'	=> 'The &quot;settings/%s.cfg&quot; file could not be written.',
	'CONFIG_WARNING'		=> 'Click the button below to see the configuration. <strong>Warning:</strong> passwords you entered will be displayed.',
	'COOKIE_DOMAIN'			=> 'Cookie domain',
	'COOKIE_DOMAIN_EXPLAIN'	=> 'This should typically be &quot;localhost&quot;.',
	'COOKIE_SECURE'			=> 'Cookie secure',
	'COOKIE_SECURE_EXPLAIN'	=> 'If your server is running via SSL set this to yes, otherwise leave this set to no to prevent server errors during redirects.',
	'CREATE_ADMIN'			=> 'Create admin',
	'CREATE_ADMIN_EXPLAIN'	=> 'Create one admin. This will not be a founder. This will be tester_1.',
	'CREATE_BOARD'			=> 'Create board',
	'CREATE_MOD'			=> 'Create moderator',
	'CREATE_MOD_EXPLAIN'	=> 'Create one global moderator. This will be tester_1 (or tester_2 if create admin is enabled).',

	'DB_EXISTS'			=> 'The database &quot;<strong>%s</strong>&quot; already exists.',
	'DB_PREFIX'			=> 'Database prefix',
	'DB_PREFIX_EXPLAIN'	=> 'This is added before all database names to avoid overwriting databases not used by QuickInstall.',
	'DB_SETTINGS'		=> 'Database settings',
	'DBHOST'			=> 'Database host',
	'DBHOST_EXPLAIN'	=> 'Usually &quot;localhost&quot;.<br />If you use SQLite this needs to be the absolute path to a directory where your web server has write permissions.',
	'DBMS'				=> 'DBMS',
	'DBMS_EXPLAIN'		=> 'Your database system. If you are unsure set it to MySQL.',
	'DBPASSWD'			=> 'Database password',
	'DBPASSWD_EXPLAIN'	=> 'The password for your database user.',
	'DBPORT'			=> 'Database port',
	'DBPORT_EXPLAIN'	=> 'Can usually be left empty.',
	'DBUSER'			=> 'Database user',
	'DBUSER_EXPLAIN'	=> 'Your database user. This needs to be a user with permissions to create new databases.',
	'DEFAULT_ENV'		=> 'Default environment (latest phpBB)',
	'DEFAULT_LANG'		=> 'Default language',
	'DEFAULT_LANG_EXPLAIN'	=> 'This language will be used for the created boards. The language pack needs to be in <code>sources/phpBB3/language/</code> to be visible in the list.',
	'DELETE'			=> 'Delete',
	'DELETE_FILES_IF_EXIST'	=> 'Delete files if they exist',
	'DELETE_FILES_IF_EXIST_EXPLAIN'	=> 'Have &quot;Delete files if they exist&quot; checked by default when creating boards.',
	'DELETE_PROFILE'	=> 'Delete profile',
	'DELETE_PROFILE_EXPLAIN'	=> 'Deletes the selected profile. This cannot be undone.',
	'DELETE_SELECTED'	=> 'Delete selected',
	'DIR_EXISTS'		=> 'The directory &quot;<strong>%s</strong>&quot; already exists.',
	'DIR_URL_SETTINGS'	=> 'Directory and URL settings',
	'DOCS_LONG'			=> 'Documentation',
	'DROP_DB_IF_EXISTS'	=> 'Drop database if it exists',
	'DROP_DB_IF_EXISTS_EXPLAIN'	=> 'Have &quot;Drop database if it exists&quot; checked by default when creating boards.',

	'EMAIL_DOMAIN'			=> 'Email domain',
	'EMAIL_DOMAIN_EXPLAIN'	=> 'The email domain to use for the testers. Their email will be tester_x@&lt;domain.com&gt;.',
	'EMAIL_ENABLE'			=> 'Enable email',
	'EMAIL_ENABLE_EXPLAIN'	=> 'Enable board wide emails. For a local test board this would typically be off, unless you test the emails.',
	'EMAIL_SETTINGS'		=> 'E-mail settings',
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
	'GRANT_PERMISSIONS_EXPLAIN'	=> 'e.g., 0060 for group read/write.',

	'IF_EMPTY_EXPLAIN'		=> 'If empty the default from config will be used.',
	'IF_LEAVE_EMPTY'		=> 'If you leave this empty you will have to fill it in when you create a board.',
	'INSTALL_STYLES'		=> 'Install additional styles',
	'INSTALL_STYLES_EXPLAIN'	=> 'Install styles found in <code>[source]/styles/</code>. Note only styles with the required parent style available can be installed.',
	'INSTALL_QI'			=> 'Install QuickInstall',
	'IS_NOT_VALID'			=> 'Is not valid.',

	'LOG_INSTALL_INSTALLED_QI'	=> '<strong>Installed by phpBB Quickinstall version %s</strong>',

	'MAKE_WRITABLE'			=> 'Make files world writable',
	'MAKE_WRITABLE_EXPLAIN'	=> 'Set files, &quot;config.php&quot;, and directories world writable by default.',
	'MAKE_WRITABLE_BOARD'	=> 'Make files world writable',
	'MAKE_WRITABLE_BOARD_EXPLAIN'	=> 'Sets file permissions to 0666.',
	'MANAGE_BOARDS'			=> 'Manage boards',
	'MAX'					=> 'Max',
	'MIGHT_TAKE_LONG'		=> '<strong>Please note:</strong><br />Creation of the board can take a while, perhaps even a minute or longer, so <strong>don’t</strong> submit the form twice.',
	'MIN'					=> 'Min',
	'MINOR_MISHAP'			=> 'Minor mishap',
	'MISC_SETTINGS'			=> 'Misc settings',

	'NEED_CONVERT'			=> 'Your config file needs to be converted to the new settings style with profiles. Make sure the &quot;settings&quot; directory exists and is writable by PHP. Then click submit.',
	'NEED_EMAIL_DOMAIN'		=> 'A e-mail domain is needed to create test users',
	'NEED_WRITABLE'			=> 'QuickInstall needs the &quot;boards&quot; and &quot;cache&quot; directories to be writable all the time.<br />The &quot;settings&quot; directory needs to be in the QuickInstall root path and it also needs to be writable.',
	'NO'					=> 'No',
	'NO_ALT_ENV'			=> 'No alternative environments found.',
	'NO_ALT_ENV_FOUND'		=> 'The specified alternative environment <strong>%s</strong> don’t exist.', // %s is the missing environment name
	'NO_AUTOMOD'			=> '<strong>AutoMOD not found in the sources directory.</strong><br />You need to download AutoMOD and copy the contents of the &quot;root&quot; directory to &quot;sources/automod&quot;. If you use AutoMOD 1.0.0. it is the contents of the &quot;upload&quot; directory.',
	'NO_AUTOMOD_TITLE'		=> 'AutoMOD not found',
	'NO_BOARDS'				=> 'You have no boards.',
	'NO_DB'					=> 'No database selected.',
	'NO_IMPACT_WIN'			=> 'This setting has no impact on Windows systems older than Win7.',
	'NO_MODULE'				=> 'The module &quot;%s&quot; could not be loaded.',
	'NO_PASSWORD'			=> 'No password',
	'NO_PHPINFO_AVAILABLE'	=> 'No PHP information could be collected.',
	'NO_PROFILES'			=> 'No profiles found.',
	'NO_DBPASSWD_ERR'		=> 'You have set a db password and checked no password. You can’t both <strong>have</strong> and <strong>not have</strong> a password',
	'NUM_CATS'				=> 'Number of categories',
	'NUM_CATS_EXPLAIN'		=> 'The number of forum categories to create.',
	'NUM_FORUMS'			=> 'Number of forums',
	'NUM_FORUMS_EXPLAIN'	=> 'The number of forums to create, they will be spread evenly over the created categories.',
	'NUM_NEW_GROUP'			=> 'Newly registered',
	'NUM_NEW_GROUP_EXPLAIN'	=> 'The number of users to place in the newly registered group. If this number is larger than the number of users, all new users will be placed in the newly registered group.',
	'NUM_REPLIES'			=> 'Number of replies',
	'NUM_REPLIES_EXPLAIN'	=> 'The number of replies. Each topic will receive a random number of replies between these max and min values.',
	'NUM_TOPICS'			=> 'Number of topics',
	'NUM_TOPICS_EXPLAIN'	=> 'The number of topics to create in each forum. Each forum will get a random number of topics between these max and min values.',
	'NUM_USERS'				=> 'Number of users',
	'NUM_USERS_EXPLAIN'		=> 'The number of users to populate your new board with. They will be assigned the username <kbd>tester_x</kbd> (x is from 1 to number of users) and they will all use the password <kbd>123456</kbd>',

	'OFF'					=> 'Off',
	'ON'					=> 'On',
	'ONLY_30'				=> 'Only available for phpBB 3.0.x.',
	'ONLY_31'				=> 'Only available for phpBB 3.1.x.',
	'ONLY_32'				=> 'Only available for phpBB 3.2.x.',
	'ONLY_LOCAL'			=> '<strong>Welcome to QuickInstall (QI)</strong>, a tool to quickly install a phpBB board for testing.<br /><br />Some default settings have been loaded below. The only things you need to enter are &quot;Database user&quot; and &quot;Database password&quot; if you want those to be stored by QuickInstall. But it would be a good idea to also check the rest of the settings.<br /><br />Make sure the &quot;boards&quot;, &quot;cache&quot; and &quot;settings&quot; directories exist and are writable by PHP.<br /><br />Once you have checked the settings and required directories simply click on the &quot;Submit&quot; button and this profile will be saved under the name &quot;Default&quot;. If you want some other name you can enter it in the &quot;Save as new profile&quot; field.<div class="alert alert-warning">QuickInstall is only intended to be used locally and should not be used on a web server accessible via the internet (public web server). <strong>If you decide to use it on a public web server it is entirely at your own risk.</strong> There is no support provided for using QuickInstall on public web servers.</div>',
	'ONLY_SUBSILVER'		=> 'Only subsilver2',
	'OPTIONS'				=> 'Options',
	'OTHER_CONFIG'			=> 'Other board config settings',
	'OTHER_CONFIG_EXPLAIN'	=> 'Config settings entered here will be updated in the config table or added to the config table if they don’t exist yet. <u>Make sure to spell correctly.</u> This can also be edited when creating the boards.<br /><br />Type one config setting per line in a semicolon <kbd>;</kbd> separated list e.g., <kbd>config-name;config-setting;dynamic</kbd>. If the setting is not dynamic then the dynamic part is not needed. Lines starting with a <kbd>#</kbd> are considered comments and not added to the DB.<br /><br />Example:<br /><kbd>session_length;999999</kbd><br /><kbd>load_tplcompile;1;1</kbd><br /><kbd># A comment</kbd>',

	'PHP7_INCOMPATIBLE'	=> 'The board you are trying to install is not compatible with PHP 7. You are using PHP %s.',
	'PHPINFO'			=> 'PHP info',
	'PHPINFO_EXPLAIN'	=> '<h1>PHP information</h1>This page lists information on the version of PHP installed on this server. It includes details of loaded modules, available variables and default settings. This information may be useful when diagnosing problems.<br /><br />Please be aware that some hosting companies will limit what information is displayed here for security reasons.<br /><br />You are advised to only give out details on this page on a need to know basis.',
	'PLAIN_TEXT'		=> '<strong>Note</strong>: QuickInstall stores passwords and usernames as plain text.',
	'POPULATE'			=> 'Populate board',
	'POPULATE_EXPLAIN'	=> 'Populates the board with the number of users, forums, posts and topics you specify below. Note that the more users, forums, posts and topics you specify, the longer it will take to process.',
	'POPULATE_MAIN_EXPLAIN'	=> 'Users: tester_x, Password: 123456',
	'POPULATE_OPTIONS'	=> 'Populate options',
	'POPULATE_SETTINGS'	=> 'Populate settings',
	'PROFILES'			=> 'Profiles',

	'QI_ABOUT'			=> 'About',
	'QI_LANG'			=> 'Select QuickInstall language',
	'QI_LANG_EXPLAIN'	=> 'The language will be visible when the language pack is available in the directory &quot;language/&quot;',
	'QI_MANAGE'			=> 'Manage boards',
	'QI_MANAGE_ABOUT'	=> '&quot;Board database and directory name:&quot; is the only field you have to fill, the others get filled with values from the selected profile.',
	'QI_MANAGE_HEADINGS'=> 'Click on the headings below for more options. Changes set here are not saved to the current profile.',
	'QI_MANAGE_PROFILE'	=> 'Manage QuickInstall profiles',
	'QI_SETTINGS'		=> 'QuickInstall settings',
	'QI_TZ'				=> 'Time zone',
	'QI_TZ_EXPLAIN'		=> 'Your time zone. It will be the default time zone for the created boards. (For the 3.0.x branch it will be converted to numerical timezone and DST.)',
	'QUICKINSTALL'		=> 'phpBB QuickInstall',

	'REDIRECT'			=> 'Redirect',
	'REDIRECT_EXPLAIN'	=> 'Redirect to new board after it is created.',
	'REDIRECT_BOARD'	=> 'Redirect to new board',
	'REQUIRED'			=> 'Required',
	'RESET'				=> 'Reset',
	'RETURN_MAIN'		=> 'Return to the Boards tab',
	'RETURN_MANAGE'		=> 'Return to the Manage tab',

	'SAVE'					=> 'Save',
	'SAVE_PROFILE'			=> 'Save as new profile',
	'SAVE_PROFILE_EXPLAIN'	=> 'Enter a name for these new profile settings. Allowed characters are <kbd>A-Z</kbd>, <kbd>a-z</kbd>, <kbd>0-9</kbd>, <kbd>-</kbd>, <kbd>_</kbd>, <kbd>.</kbd><br /><br />Note: If a profile with this name already exists, it will be overwritten.',
	'SAVE_RESTORE'			=> 'Save/Restore',
	'SEARCH_HERE'			=> 'Search here...',
	'SELECT_PROFILE'		=> 'Select profile',
	'SET_DEFAULT_STYLE'		=> 'Set default style',
	'SET_DEFAULT_STYLE_EXPLAIN'	=> 'Enter the name of the style you want to use as the default style. The name can be found in the <code>styles/[style name]/style.cfg</code> file. Defaults to prosilver if empty or the style can’t be installed.',
	'SETTINGS_FAILURE'		=> 'The following errors were detected:',
	'SETTINGS_NOT_WRITABLE'	=> 'The settings directory do not exist, is not a directory or is not writable.',
	'SETTINGS_SECTIONS'		=> 'Settings sections',
	'SETTINGS_SUCCESS'		=> 'Your settings were successfully saved.',
	'SERVER_COOKIE_SETTINGS'	=> 'Server and cookie settings',
	'SERVER_NAME'			=> 'Server name',
	'SERVER_NAME_EXPLAIN'	=> 'This should typically be &quot;localhost&quot; since QuickInstall is <strong>not</strong> intended for public servers.',
	'SERVER_PORT'			=> 'Server port',
	'SERVER_PORT_EXPLAIN'	=> 'Usually &quot;80&quot;.',
	'SHOW_CONFIRM'			=> 'Confirm delete',
	'SHOW_CONFIRM_EXPLAIN'	=> 'Show confirm alert when deleting boards and profiles.',
	'SITE_DESC'				=> 'Site description',
	'SITE_DESC_EXPLAIN'		=> 'The default description for your board(s). This can be changed when boards are created.',
	'SITE_NAME'				=> 'Site name',
	'SITE_NAME_EXPLAIN'		=> 'The default site name that will be used for your board(s). This can be changed when boards are created.',
	'SMTP_AUTH'				=> 'Authentication method for SMTP',
	'SMTP_AUTH_EXPLAIN'		=> 'Only used if an SMTP username and password is set.',
	'SMTP_DELIVERY'			=> 'Use SMTP server for e-mail',
	'SMTP_DELIVERY_EXPLAIN'	=> 'Select &quot;Yes&quot; if you want or have to send e-mail via a named server instead of the local mail function.',
	'SMTP_HOST'				=> 'SMTP server address',
	'SMTP_HOST_EXPLAIN'		=> 'The address of the SMTP server you want to use',
	'SMTP_PASS'				=> 'SMTP password',
	'SMTP_PASS_EXPLAIN'		=> 'Only enter a password if your SMTP server requires it.',
	'SMTP_PORT'				=> 'SMTP server port',
	'SMTP_PORT_EXPLAIN'		=> 'Only change this if you know your SMTP server is on a different port.',
	'SMTP_USER'				=> 'SMTP username',
	'SMTP_USER_EXPLAIN'		=> 'Only enter a username if your SMTP server requires it.',
	'SQLITE_PATH_MISSING'	=> 'The entered database server path is either missing or not writeable.',
	'STAR_MANDATORY'		=> '* Required',
	'SUBMIT'				=> 'Submit',
	'SUCCESS'				=> 'Success',
	'SURE_DELETE_PROFILE'	=> 'Are you sure you want to delete the selected profile? This cannot be undone.',
	'SURE_DELETE_BOARDS'	=> 'Are you sure you want to delete the selected boards? This cannot be undone.',

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
	'WHAT_EXPLAIN'		=> 'phpBB3 QuickInstall is a tool to quickly setup phpBB boards. Pretty obvious... 😉',
	'WHO_ELSE'			=> 'Who else?',
	'WHO_ELSE_EXPLAIN'	=> '<ul><li>' . implode('</li><li>', array(
		'Credits go to the phpBB team, especially the development team which created such a wonderful piece of software.',
		'Thanks to the phpBB.com MOD team (especially Josh, aka “A_Jelly_Doughnut”) for AutoMOD.',
		'Thanks to the beta testers!',
		'Thanks to the phpBB community including phpBB.com, startrekguide.com and phpBBModders.net!',
	)) . '</li></ul>',
	'WHO_WHEN'			=> 'Who? When?',
	'WHO_WHEN_EXPLAIN'	=> 'phpBB3 QuickInstall was originally created by Igor “igorw” Wiedler in the summer of 2007. It was partially rewritten by him in March 2008. From March 2010 to March 2015 the project was mantained by Jari “tumba25” Kanerva. The project is now maintained by the phpBB Extensions Team.',
	'WHY'				=> 'Why?',
	'WHY_EXPLAIN'		=> 'The days of phpBB 2.x and 3.0 were all about modding (creating modifications). Authors could not effectively develop and test all their MODs in a single phpBB installation. QuickInstall was born to speed up and simplify the process of creating separate fresh installations for each of their MODs. Now, in the era of extensions, QuickInstall is still just as useful for rapidly generating fresh installations to safely install, develop and test extensions in.',

	// Config updated strings.
	'UPDATED_EXPLAIN'	=> 'Your profile has been updated to this version of QI (%s). The changes made are defined below. They have been set to default values. also defined below.<br />You might want to look into the Settings page (link at the bottom) and set them to your desired values. If you have more than one profile, just press the button below to get all profiles updated.', // %s will be replaced with QI version.
	'PROFILE_UPDATED'	=> 'Profile &quot;%s&quot; updated', // %s will be replaced by a profile name.
	'PROFILES_UPDATED'	=> 'The following profiles has been updated',
	'UPDATE_PROFILES'	=> 'Update profiles',

	'DST_REMOVED'		=>	'The DST setting has been removed (qi_dst).',
	'TIMEZONE_UPDATED'	=>	'Your timezone setting has been updated from numerical to string (qi_tz), default is UTC.',

));
