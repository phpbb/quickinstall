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
	'ADMIN_EMAIL'			=> 'Administrator email',
	'ADMIN_EMAIL_EXPLAIN'	=> 'The email to assign to the admin account created for your boards.',
	'ADMIN_NAME'			=> 'Administrator username',
	'ADMIN_NAME_EXPLAIN'	=> 'The username to assign to the admin account created for your boards.',
	'ADMIN_PASS'			=> 'Administrator password',
	'ADMIN_PASS_EXPLAIN'	=> 'The password to assign to the admin account created for your boards.',
	'ADMIN_SETTINGS'		=> 'Admin account',
	'ALT_ENV'				=> 'Alternate environment',
	'ALT_ENV_EXPLAIN'		=> 'Alternate phpBB environments are available if you have added additional phpBB3 boards to the <code>sources/phpBB3_alt/</code> directory.',
	'AUTOMOD'				=> 'AutoMOD',
	'AUTOMOD_INSTALL'		=> 'Install AutoMOD',

	'BACK_TOP'				=> 'Back to the top',
	'BOARD_CONFIG_OPTIONS'	=> 'Board configuration options',
	'BOARD_CONFIG_SETTINGS'	=> 'Board configuration',
	'BOARD_DBNAME'			=> 'Board database and directory name',
	'BOARD_DESC'			=> 'Board description',
	'BOARD_EMAIL'			=> 'Board email',
	'BOARD_EMAIL_EXPLAIN'	=> 'The contact email address for your created boards.',
	'BOARD_NAME'			=> 'Board name',
	'BOARDS'				=> 'Boards',
	'BOARDS_DIR'			=> 'Boards directory',
	'BOARDS_DIR_EXPLAIN'	=> 'The directory where your boards will be created. PHP needs to have write permissions to this directory.',
	'BOARDS_DIR_MISSING'	=> 'The directory &quot;%s&quot; does not exist or is not writeable.',
	'BOARDS_LIST'			=> 'Installed boards',
	'BOARDS_URL'			=> 'Boards URL prefix',
	'BOARDS_URL_EXPLAIN'	=> 'URL prefix to the boards directory. If you specified an absolute directory in the boards directory setting above, you may need to provide a domain and/or path here that leads to the boards directory. If boards directory is a relative path, you may just copy it here.',

	'CACHE_DIR'				=> 'Cache directory',
	'CACHE_DIR_EXPLAIN'		=> 'The directory where QuickInstall stores temporary files. PHP needs to have write permissions to this directory.',
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
	'COOKIE_DOMAIN_EXPLAIN'	=> 'Usually <code>localhost</code>.',
	'COOKIE_SECURE'			=> 'Cookie secure',
	'COOKIE_SECURE_EXPLAIN'	=> 'If your server is running via SSL set this to yes, otherwise leave this set to no to prevent server errors during redirects.',
	'CREATE_ADMIN'			=> 'Create admin',
	'CREATE_ADMIN_EXPLAIN'	=> 'Create one admin. This will not be the founder. This will be <code>tester_1</code>.',
	'CREATE_BOARD'			=> 'Create board',
	'CREATE_MOD'			=> 'Create moderator',
	'CREATE_MOD_EXPLAIN'	=> 'Create one global moderator. This will be <code>tester_1</code> (or <code>tester_2</code> if create admin is enabled).',
	'CURRENT_PROFILE'		=> 'Current profile',

	'DB_EXISTS'			=> 'The database &quot;<strong>%s</strong>&quot; already exists.',
	'DB_PREFIX'			=> 'Database prefix',
	'DB_PREFIX_EXPLAIN'	=> 'This prefix is added to database names to avoid overwriting databases not used by QuickInstall.',
	'DB_SETTINGS'		=> 'Database',
	'DBHOST'			=> 'Database server hostname',
	'DBHOST_EXPLAIN'	=> 'Usually <code>localhost</code> For SQLite, enter the full path to a directory where your web server has write permissions.',
	'DBMS'				=> 'Database type',
	'DBMS_EXPLAIN'		=> 'Your database system. If you are unsure try MySQLi.',
	'DBPASSWD'			=> 'Database password',
	'DBPASSWD_EXPLAIN'	=> 'The password for your database user.',
	'DBPORT'			=> 'Database server port',
	'DBPORT_EXPLAIN'	=> 'Leave this blank unless you know your web server operates on a non-standard port.',
	'DBUSER'			=> 'Database username',
	'DBUSER_EXPLAIN'	=> 'Your database user. This needs to be a user with permissions to create new databases.',
	'DEFAULT_ENV'		=> 'Default environment (latest phpBB)',
	'DEFAULT_LANG'		=> 'Default language',
	'DEFAULT_LANG_EXPLAIN'	=> 'The default language that will be used for your boards. Language packs need to be in <code>sources/phpBB3/language/</code> to be available in this list.',
	'DELETE'			=> 'Delete',
	'DELETE_FILES_IF_EXIST'	=> 'Delete files if they exist',
	'DELETE_FILES_IF_EXIST_EXPLAIN'	=> 'Have &quot;Delete files if they exist&quot; checked by default when creating boards.',
	'DELETE_PROFILE'	=> 'Delete profile',
	'DELETE_SELECTED'	=> 'Delete selected',
	'DIR_EXISTS'		=> 'The directory &quot;<strong>%s</strong>&quot; already exists.',
	'DIR_FILE_SETTINGS'	=> 'Directories and Files',
	'DOCS_LONG'			=> 'Documentation',
	'DROP_DB_IF_EXISTS'	=> 'Drop database if it exists',
	'DROP_DB_IF_EXISTS_EXPLAIN'	=> 'Have &quot;Drop database if it exists&quot; checked by default when creating boards.',

	'EMAIL_DOMAIN'			=> 'Email domain',
	'EMAIL_DOMAIN_EXPLAIN'	=> 'The email domain to use for the populated users. Their email will be <code>tester_x@&lt;domain.com&gt;</code>.',
	'EMAIL_ENABLE'			=> 'Enable email',
	'EMAIL_ENABLE_EXPLAIN'	=> 'Enable board wide emails. For a local test board this would typically be off, unless you test the emails.',
	'EMAIL_SETTINGS'		=> 'Board E-mail',
	'ENABLE_DEBUG'			=> 'Enable Debug',
	'ENABLE_DEBUG_EXPLAIN'	=> 'Display load time, memory usage, query stats and enhanced error reporting.',
	'ERRORS'				=> 'There were errors',
	'ERROR_DEL_BOARDS'		=> 'The following boards could not be deleted',
	'ERROR_DEL_FILES'		=> 'The following files could not be deleted',

	'FUNCTIONS_MODS_MISSING'	=> '&quot;includes/functions_mods.php&quot; not found.',
	'FORGOT_THIS'				=> 'You forgot this!',
	'FOR_PHPBB_VERSIONS'		=> 'for phpBB 3.0 - 4.0',

	'GENERAL_ERROR'		=> 'General Error',
	'GO'				=> 'Go',
	'GO_QI_MAIN'		=> '%sGo to QuickInstall main page%s',
	'GO_QI_SETTINGS'	=> '%sGo to settings%s',
	'GRANT_PERMISSIONS'	=> 'Grant additional permissions',
	'GRANT_PERMISSIONS_EXPLAIN'	=> 'e.g., 0060 for group read/write.',

	'IF_EMPTY_EXPLAIN'		=> 'If empty the default from config will be used.',
	'IF_LEAVE_EMPTY'		=> 'If you leave this empty you will have to fill it in when you create a board.',
	'INSTALL_OPTIONS'		=> 'Install options',
	'INSTALL_STYLES'		=> 'Install additional styles',
	'INSTALL_STYLES_EXPLAIN'	=> 'Install all styles found in <code>[source]/styles</code>. Styles missing their required parent style will be ignored.',
	'INSTALL_QI'			=> 'Install QuickInstall',
	'INSTALL_WELCOME'		=> 'Welcome to QuickInstall, a tool for quickly installing phpBB boards for testing and development.<br /><br />Some default settings have been loaded below. The only fields you should enter are <code>Database user</code> and <code>Database password</code> if you want those to be stored by QuickInstall. But it is also a good idea to check the rest of the settings.<br /><br />Make sure the <code>boards</code>, <code>cache</code> and <code>settings</code> directories exist in the QuickInstall root directory and are writable by PHP.<br /><br />Once you save these settings they will be stored as the &quot;default&quot; profile. Optionally, you may enter your own unique profile name in the <code>Save as new profile</code> field.',
	'IS_NOT_VALID'			=> 'Is not valid.',
	'REQUIRED'				=> 'Required',

	'LOG_INSTALL_INSTALLED_QI'	=> '<strong>Installed by phpBB Quickinstall version %s</strong>',

	'MAKE_WRITABLE'			=> 'Make files world writable',
	'MAKE_WRITABLE_EXPLAIN'	=> 'Set files, &quot;config.php&quot;, and directories world writable by default.',
	'MAKE_WRITABLE_BOARD'	=> 'Make files world writable',
	'MAKE_WRITABLE_BOARD_EXPLAIN'	=> 'Sets file permissions to 0666.',
	'MANAGE_BOARDS'			=> 'Manage boards',
	'MAX'					=> 'Max',
	'MIGHT_TAKE_LONG'		=> 'Creation of the board can take several seconds or minutes. Do not submit the form twice.',
	'MIN'					=> 'Min',
	'MINOR_MISHAP'			=> 'Minor mishap',

	'NEED_CONVERT'			=> 'Your config file needs to be converted to the new settings style with profiles. Make sure the &quot;settings&quot; directory exists and is writable by PHP. Then click submit.',
	'NEED_EMAIL_DOMAIN'		=> 'A e-mail domain is needed to create test users',
	'NEED_WRITABLE'			=> 'QuickInstall needs the &quot;boards&quot; and &quot;cache&quot; directories to be writable all the time.<br />The &quot;settings&quot; directory needs to be in the QuickInstall root path and it also needs to be writable.',
	'NO'					=> 'No',
	'NO_ALT_ENV'			=> 'No alternative environments found.',
	'NO_ALT_ENV_FOUND'		=> 'The specified alternative environment <strong>%s</strong> don’t exist.', // %s is the missing environment name
	'NO_AUTOMOD'			=> '<strong>AutoMOD not found in the sources directory.</strong><br />You need to download AutoMOD and copy the contents of the <code>root</code> directory to <code>sources/automod</code>. If you use AutoMOD 1.0.0. it is the contents of the <code>upload</code> directory.',
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
	'NUM_NEW_GROUP'			=> 'Newly registered users',
	'NUM_NEW_GROUP_EXPLAIN'	=> 'The number of users to place in the newly registered group. If this number is larger than the number of users, all new users will be placed in the newly registered group.',
	'NUM_REPLIES'			=> 'Number of replies',
	'NUM_REPLIES_EXPLAIN'	=> 'The number of replies. Each topic will receive a random number of replies between these min and max values.',
	'NUM_TOPICS'			=> 'Number of topics',
	'NUM_TOPICS_EXPLAIN'	=> 'The number of topics to create in each forum. Each forum will get a random number of topics between these min and max values.',
	'NUM_USERS'				=> 'Number of users',
	'NUM_USERS_EXPLAIN'		=> 'The number of users to populate your new board with. They will be assigned the username <code>tester_x</code> (x is from 1 to number of users) and they will all use the password <code>123456</code>.',

	'OFF'					=> 'Off',
	'ON'					=> 'On',
	'ONLY_30'				=> 'Only available for phpBB 3.0.x.',
	'ONLY_31'				=> 'Only available for phpBB 3.1.x.',
	'ONLY_32'				=> 'Only available for phpBB 3.2.x.',
	'ONLY_LOCAL'			=> 'QuickInstall is only intended to be used locally and should not be used on a web server accessible via the internet (public web server). <strong>If you decide to use it on a public web server it is entirely at your own risk.</strong> There is no support provided for using QuickInstall on public web servers.',
	'ONLY_SUBSILVER'		=> 'Only subsilver2',
	'OPTIONS'				=> 'Options',
	'OTHER_CONFIG'			=> 'Additional board config settings',
	'OTHER_CONFIG_EXPLAIN'	=> 'Config settings entered here will be updated in the config table or added to the config table if they don’t exist yet. <u>Make sure to spell correctly.</u> This can also be edited when creating the boards.<br /><br />Type one config setting per line in a semicolon <kbd>;</kbd> separated list e.g., <kbd>config-name;config-setting;dynamic</kbd>. If the setting is not dynamic then the dynamic part is not needed. Lines starting with a <kbd>#</kbd> are considered comments and not added to the DB.<br /><br />Example:<br /><kbd>session_length;999999</kbd><br /><kbd>load_tplcompile;1;1</kbd><br /><kbd># A comment</kbd>',

	'PHPBB_QI_TEXT'		=> 'phpBB<small><sup>&reg;</sup></small> QuickInstall',
	'PHPBB_QI_TITLE'	=> 'phpBB&reg; QuickInstall',

	'PHP7_INCOMPATIBLE'	=> 'The board you are trying to install, phpBB %1$s, is not compatible with PHP %2$s.',
	'PHPINFO'			=> 'PHP info',
	'PHPINFO_TITLE'		=> 'PHP information',
	'PHPINFO_EXPLAIN'	=> 'This page lists information on the version of PHP installed on this server. It includes details of loaded modules, available variables and default settings. This information may be useful when diagnosing problems.<br /><br />Please be aware that some hosting companies will limit what information is displayed here for security reasons.<br /><br />You are advised to only give out details on this page on a need to know basis.',
	'PLAIN_TEXT'		=> 'Note: QuickInstall stores passwords and usernames as plain text.',
	'POPULATE'			=> 'Populate board',
	'POPULATE_EXPLAIN'	=> 'Populates the board with the number of users, forums, posts and topics you specify below. Note that the more users, forums, posts and topics you specify, the longer it will take to process.',
	'POPULATE_OPTIONS'	=> 'Populate options',
	'POWERED_BY_PHPBB'	=> 'Powered by phpBB<sup>&reg;</sup> Forum Software &copy; <a href="https://www.phpbb.com/">phpBB Limited</a>',
	'PROFILES'			=> 'Profiles',

	'QI_LANG'			=> 'Select QuickInstall language',
	'QI_LANG_EXPLAIN'	=> 'Select a language for QuickInstall. The available languages are stored in the directory <code>language/</code>',
	'QI_MANAGE'			=> 'Manage boards',
	'QI_MANAGE_ABOUT'	=> '&quot;Board database and directory name:&quot; is the only field you have to fill, the others get filled with values from the selected profile.',
	'QI_MANAGE_HEADINGS'=> 'Click on the headings below for more options. Changes set here are not saved to the current profile.',
	'QI_MANAGE_PROFILE'	=> 'Manage profiles',
	'QI_SETTINGS'		=> 'QuickInstall settings',
	'QI_TZ'				=> 'Time zone',
	'QI_TZ_EXPLAIN'		=> 'Set the default time zone for your boards.',
	'QUICKINSTALL'		=> 'QuickInstall',

	'REDIRECT'			=> 'Redirect',
	'REDIRECT_EXPLAIN'	=> 'Redirect to new board after it is created.',
	'REDIRECT_BOARD'	=> 'Redirect to new board',
	'RESET'				=> 'Reset',

	'SAVE'					=> 'Save',
	'SAVE_PROFILE'			=> 'Save as new profile',
	'SAVE_PROFILE_EXPLAIN'	=> 'Enter a name to create a new profile with these settings, or leave this field blank to update the current profile.<br /><br />Allowed characters: <kbd>A-Z a-z 0-9 - _ .</kbd><br /><br />Note: If a profile of the same name already exists, it will be overwritten.',
	'SAVE_SETTINGS'			=> 'Save profile',
	'SEARCH_HERE'			=> 'Search here...',
	'SET_DEFAULT_STYLE'		=> 'Set default style',
	'SET_DEFAULT_STYLE_EXPLAIN'	=> 'Enter the name of the style you want to use as the default style. The name can be found in the <code>styles/[style name]/style.cfg</code> file. Defaults to prosilver if empty or the style can’t be installed.',
	'SETTINGS_FAILURE'		=> 'The following errors were detected',
	'SETTINGS_NOT_WRITABLE'	=> 'The settings directory does not exist, is not a directory or is not writable.',
	'SETTINGS_SECTIONS'		=> 'Settings',
	'SETTINGS_SUCCESS'		=> 'Your settings were successfully saved.',
	'SERVER_SETTINGS'		=> 'Server',
	'SERVER_NAME'			=> 'Server name',
	'SERVER_NAME_EXPLAIN'	=> 'Usually <code>localhost</code> since QuickInstall is <strong>not</strong> intended for public servers.',
	'SERVER_PORT'			=> 'Server port',
	'SERVER_PORT_EXPLAIN'	=> 'Usually <code>80</code>.',
	'SHOW_CONFIRM'			=> 'Confirm delete',
	'SHOW_CONFIRM_EXPLAIN'	=> 'Show a confirmation alert before deleting boards and profiles.',
	'SITE_DESC'				=> 'Site description',
	'SITE_DESC_EXPLAIN'		=> 'The default description for your boards.',
	'SITE_NAME'				=> 'Site name',
	'SITE_NAME_EXPLAIN'		=> 'The default site name that will be used for your boards.',
	'SMTP_AUTH'				=> 'Authentication method for SMTP',
	'SMTP_AUTH_EXPLAIN'		=> 'Only used if an SMTP username and password is set.',
	'SMTP_DELIVERY'			=> 'Use SMTP server for e-mail',
	'SMTP_DELIVERY_EXPLAIN'	=> 'Enable this if you want or have to send e-mail via a named server instead of the local mail function.',
	'SMTP_HOST'				=> 'SMTP server address',
	'SMTP_HOST_EXPLAIN'		=> 'The address of the SMTP server you want to use',
	'SMTP_PASS'				=> 'SMTP password',
	'SMTP_PASS_EXPLAIN'		=> 'Only enter a password if your SMTP server requires it.',
	'SMTP_PORT'				=> 'SMTP server port',
	'SMTP_PORT_EXPLAIN'		=> 'Only change this if you know your SMTP server is on a different port.',
	'SMTP_USER'				=> 'SMTP username',
	'SMTP_USER_EXPLAIN'		=> 'Only enter a username if your SMTP server requires it.',
	'SQLITE_PATH_MISSING'	=> 'The entered database server path is either missing or not writeable.',
	'SUBMIT'				=> 'Submit',
	'SUCCESS'				=> 'Success',
	'SURE_DELETE_PROFILE'	=> 'Are you sure you want to delete the selected profile? This cannot be undone.',
	'SURE_DELETE_BOARDS'	=> 'Are you sure you want to delete the selected boards? This cannot be undone.',

	'TABLE_PREFIX'			=> 'Table prefix',
	'TABLE_PREFIX_EXPLAIN'	=> 'This prefix will be added to your board’s database table names.',
	'TEST_CAT_NAME'			=> 'Test category %d',
	'TEST_FORUM_NAME'		=> 'Test forum %d',
	'TEST_POST_START'		=> 'Test post %d', // This will be on the first line in each post and then filled with lorem ipsum.
	'TEST_TOPIC_TITLE'		=> 'Test topic %d',
	'THESE_CAN_CHANGE'		=> 'These settings can be changed when you create a board.',
	'THIS_CAN_CHANGE'		=> 'This can be changed when you create a board.',
	'TIME_SETTINGS'			=> 'Time settings',

	'UNCHECK_ALL'			=> 'Uncheck all',
	'UPDATE_AVAILABLE'		=> 'Update available',

	'VERSION_CHECK_TITLE'	=> 'QI %1$s is available. You are using QI %2$s. Click to download the latest version.',

	'WORKING_ON_IT'		=> 'We’re working on it...',

	'YES'	=> 'Yes',

	// Config updated strings.
	'UPDATED_EXPLAIN'	=> 'Your profile has been updated to this version of QuickInstall (%s). The changes made are defined below. They have been set to default values. also defined below.<br />You might want to look into the Settings page (link at the bottom) and set them to your desired values. If you have more than one profile, just press the button below to get all profiles updated.', // %s will be replaced with QI version.
	'PROFILE_UPDATED'	=> 'Profile &quot;%s&quot; updated', // %s will be replaced by a profile name.
	'PROFILES_UPDATED'	=> 'The following profiles has been updated',
	'UPDATE_PROFILES'	=> 'Update profiles',

	'DST_REMOVED'		=>	'The DST setting has been removed (qi_dst).',
	'TIMEZONE_UPDATED'	=>	'Your timezone setting has been updated from numerical to string (qi_tz), default is UTC.',

	'COLON'				=> ':',
));
