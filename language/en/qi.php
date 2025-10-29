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
	'ADMIN_NAME_FEEDBACK'	=> 'Please choose an admin account username.',
	'ADMIN_PASS'			=> 'Administrator password',
	'ADMIN_PASS_EXPLAIN'	=> 'The password to assign to the admin account created for your boards.',
	'ADMIN_PASS_FEEDBACK'	=> 'Please choose an admin account password.',
	'ADMIN_SETTINGS'		=> 'Admin account',
	'ALT_ENV'				=> 'Alternate environment',
	'ALT_ENV_EXPLAIN'		=> 'Alternate phpBB environments are available if you have added additional phpBB3 boards to the <code>sources/phpBB3_alt/</code> directory.',

	'BACK_TOP'				=> 'Back to the top',
	'BOARD_CONFIG_OPTIONS'	=> 'Board configuration options',
	'BOARD_CONFIG_SETTINGS'	=> 'Board configuration',
	'BOARD_DBNAME'			=> 'Board database and directory name',
	'BOARD_DBNAME_FEEDBACK'	=> 'Please choose a unique and valid name.',
	'BOARD_DBNAME_EXPLAIN'	=> 'This will be used to name the database and the folder containing the board.',
	'BOARD_DESC'			=> 'Board description',
	'BOARD_DESC_EXPLAIN'	=> 'The default description for your boards.',
	'BOARD_DESC_PLACEHOLDER'=> 'Testing Board created by QuickInstall',
	'BOARD_EMAIL'			=> 'Board email',
	'BOARD_EMAIL_EXPLAIN'	=> 'The contact email address for your created boards.',
	'BOARD_NAME'			=> 'Board name',
	'BOARD_NAME_EXPLAIN'	=> 'The default site name that will be used for your boards.',
	'BOARD_NAME_PLACEHOLDER'=> 'Testing Board',
	'BOARDS'				=> 'Boards',
	'BOARDS_DIR'			=> 'Boards directory',
	'BOARDS_DIR_EXPLAIN'	=> 'The directory where your boards will be created. PHP needs to have write permissions to this directory.',
	'BOARDS_DIR_MISSING'	=> '<strong>Boards directory</strong> “<code>%s</code>” does not exist or is not writeable.',
	'BOARDS_LIST'			=> 'My Boards',
	'BOARDS_URL'			=> 'Boards URL prefix',
	'BOARDS_URL_EXPLAIN'	=> 'URL prefix to the boards directory. If you specified an absolute directory in the boards directory setting above, you may need to provide a domain and/or path here that leads to the boards directory. If boards directory is a relative path, you may just copy it here.',

	'CACHE_DIR'				=> 'Cache directory',
	'CACHE_DIR_EXPLAIN'		=> 'The directory where QuickInstall stores temporary files. PHP needs to have write permissions to this directory.',
	'CACHE_DIR_MISSING'		=> '<strong>Cache directory</strong> “<code>%s</code>” does not exist or is not writeable.',
	'CANNOT_DELETE_LAST_PROFILE'	=> 'You can not delete the last profile.',
	'CHANGELOG'				=> 'Changelog',
	'CHUNK_POST'			=> 'Post chunk',
	'CHUNK_POST_EXPLAIN'	=> 'The number of posts that will be sent to the database in each query. Default: 1000.',
	'CHUNK_SETTINGS'		=> 'Chunk settings',
	'CHUNK_SETTINGS_EXPLAIN'	=> 'QuickInstall tries to reduce the number of queries generated from creating posts, topics and users by using chunks. The chunk size affects the time it takes to populate a board. There is no general setting that is perfect for everybody. If you do a lot of populating with QuickInstall you might want to experiment with these settings. Larger chunks may use too much memory while smaller chunks will query the DB more often. We have found the default settings to be the best compromise.',
	'CHUNK_TOPIC'			=> 'Topic chunk',
	'CHUNK_TOPIC_EXPLAIN'	=> 'The number of topics that will be sent to the database in each query. Default: 2000.',
	'CHUNK_USER'			=> 'User chunk',
	'CHUNK_USER_EXPLAIN'	=> 'The number of users that will be sent to the database in each query. Default: 5000.',
	'CONFIG_BUTTON'			=> 'Click here to see the configuration.',
	'CONFIG_EMPTY'			=> 'The config array was empty. This is probably worth a bug report.',
	'CONFIG_IS_DISPLAYED'	=> 'Configuration is displayed below. You can try manually writing it into a file in the settings directory.<br />Make sure the file name ends in <code>.json</code> for example <code>settings/main.json</code>.',
	'CONFIG_NOT_WRITABLE'	=> 'The <code>settings/</code> directory is not writable.',
	'CONFIG_NOT_WRITTEN'	=> 'The <code>settings/%s.json</code> file could not be written.',
	'CONFIG_WARNING'		=> 'Click the button below to see the configuration. <strong>Warning:</strong> passwords you entered will be displayed.',
	'COOKIE_DOMAIN'			=> 'Cookie domain',
	'COOKIE_DOMAIN_EXPLAIN'	=> 'Usually <code>localhost</code>.',
	'COOKIE_SECURE'			=> 'Cookie secure',
	'COOKIE_SECURE_EXPLAIN'	=> 'If your server is running via SSL set this to yes, otherwise leave this set to no to prevent server errors during redirects.',
	'COPY_CONFIG'			=> 'Copy configuration to clipboard',
	'COPY_DIR_ERROR'		=> 'Directory “<code>%s</code>” could not be created.',
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
	'DBPASSWD_FEEDBACK'	=> 'Please enter your database server password.',
	'DBPORT'			=> 'Database server port',
	'DBPORT_EXPLAIN'	=> 'Leave this blank unless you know your web server operates on a non-standard port.',
	'DBUSER'			=> 'Database username',
	'DBUSER_EXPLAIN'	=> 'Your database user. This needs to be a user with permissions to create new databases.',
	'DBUSER_FEEDBACK'	=> 'Please enter your database server user name.',
	'DEFAULT_ENV'		=> 'Default environment (latest phpBB)',
	'DEFAULT_LANG'		=> 'Default language',
	'DEFAULT_LANG_EXPLAIN'	=> 'The default language that will be used for your boards. Language packs need to be in <code>sources/phpBB3/language/</code> to be available in this list.',
	'DELETE'			=> 'Delete',
	'DELETE_COOKIES'	=> 'Delete all phpBB cookies',
	'DELETE_COOKIES_CONFIRM' => 'This will log you out of all phpBB boards on this server. Are you sure you want to continue?',
	'DELETE_COOKIES_TITLE'	=> 'Over time and many QI board installs, loads of cookies that may no longer be needed can build up in your server.',
	'DELETE_FILES_IF_EXIST'	=> 'Delete files if they exist',
	'DELETE_FILES_IF_EXIST_EXPLAIN'	=> 'Have &quot;Delete files if they exist&quot; checked by default when creating boards.',
	'DELETE_PROFILE'	=> 'Delete profile',
	'DELETE_SELECTED'	=> 'Delete selected',
	'DIR_EXISTS'		=> 'The directory &quot;<strong>%s</strong>&quot; already exists.',
	'DIR_FILE_SETTINGS'	=> 'Directories and Files',
	'DOCS_LONG'			=> 'Documentation',
	'DOCS_SHORT'		=> 'Docs',
	'DOWNLOAD'			=> 'Download',
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
	'ERROR_PHP_UNSUPPORTED'	=> 'You are running an unsupported PHP version. phpBB QuickInstall only supports PHP version 5.4.7 and newer.',
	'ERROR_PHPBB_NOT_FOUND'	=> 'phpBB could not be located.<br /><br />You must download a copy of phpBB from <a href="https://www.phpbb.com/downloads/">https://www.phpbb.com/downloads/</a>, extract it and copy the phpBB3 folder to QuickInstall‘s <code>sources</code> directory.<br /><br />QuickInstall supports phpBB 3.0 - 4.0.',

	'GENERAL_ERROR'		=> 'General Error',
	'GITHUB'			=> 'GitHub',
	'GOTO_BOARD'		=> 'Open “%s” in a new browser window',
	'GRANT_PERMISSIONS'	=> 'Grant additional permissions',
	'GRANT_PERMISSIONS_EXPLAIN'	=> 'For example, 0060 for Group read/write permissions on all board files and directories.',

	'IF_EMPTY_EXPLAIN'		=> 'If empty the value stored in the current profile will be used.',
	'IF_LEAVE_EMPTY'		=> 'If you leave this empty you will have to fill it in when you create a board.',
	'INSTALL_OPTIONS'		=> 'Install options',
	'INSTALL_STYLES'		=> 'Install additional styles',
	'INSTALL_STYLES_EXPLAIN'	=> 'Enables/installs all styles found in <code>sources/phpBB3/styles</code>. Styles missing their required parent style will be ignored.',
	'INSTALL_QI'			=> 'Install QuickInstall',
	'INSTALL_WELCOME'		=> 'Welcome to QuickInstall, a tool for quickly installing phpBB boards for testing and development.<br /><br />Some default settings have been loaded below. You should fill the <code>Database username</code> and <code>Database password</code> fields if you want them to be stored by QuickInstall. But it is also a good idea to check the rest of the settings.<br /><br />Make sure the <code>boards</code>, <code>cache</code> and <code>settings</code> directories exist in the QuickInstall root directory and are writable by PHP.<br /><br />Once you save these settings they will be stored as the &quot;default&quot; profile. Optionally, you may enter your own unique profile name in the <code>Save as new profile</code> field.',
	'IS_NOT_VALID'			=> '<strong>%s</strong> is not valid.',
	'IS_REQUIRED'			=> '<strong>%s</strong> is required.',
	'REQUIRED'				=> 'Required',

	'JAVASCRIPT_DISABLED_ALERT'	=> '<strong>Javascript is disabled!</strong> Please enable Javascript for full functionality.',

	'LANGUAGE_PACK_MISSING'	=> 'The source phpBB board does not have a valid language pack. Please download a fresh copy of phpBB and try again.',
	'LOAD'					=> 'Load',
	'LOG_INSTALL_INSTALLED_QI'	=> '<strong>Installed by phpBB QuickInstall version %s</strong>',
	'LOREM_IPSUM'			=> 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',

	'MAKE_WRITABLE'			=> 'Make files world writable',
	'MAKE_WRITABLE_EXPLAIN'	=> 'Grants 0666 permission to all board files and directories, giving read and write access to everyone.',
	'MAKE_WRITABLE_BOARD'	=> 'Make files world writable',
	'MARK_FOR_DELETION'		=> 'Mark for deletion',
	'MAX'					=> 'Max',
	'MIN'					=> 'Min',
	'MINOR_MISHAP'			=> 'Something went wrong 🤔',

	'NEED_EMAIL_DOMAIN'		=> 'A e-mail domain is needed to create test users',
	'NEED_WRITABLE'			=> 'QuickInstall needs the <code>boards</code>, <code>cache</code> and <code>settings</code> directories to be writable all the time.<br />The <code>settings</code> directory must always be in the QuickInstall root path.',
	'NO'					=> 'No',
	'NO_ALT_ENV'			=> 'No alternative environments found.',
	'NO_ALT_ENV_FOUND'		=> 'The specified alternative environment <strong>%s</strong> could not be found.', // %s is the missing environment name
	'NO_BOARDS'				=> 'You have no boards.',
	'NO_DB'					=> 'No database selected.',
	'NO_MODULE'				=> 'The module <code>%s</code> could not be loaded.',
	'NO_PASSWORD'			=> 'No password',
	'NO_PHPINFO_AVAILABLE'	=> 'No PHP information could be collected.',
	'NO_PROFILES'			=> 'No profiles found.',
	'NO_DBPASSWD_ERR'		=> 'You have set a db password and checked no password. You can’t both <strong>have</strong> and <strong>not have</strong> a password',
	'NUM_CATS'				=> 'Number of categories',
	'NUM_CATS_EXPLAIN'		=> 'The number of forum categories to create.',
	'NUM_FORUMS'			=> 'Number of forums',
	'NUM_FORUMS_EXPLAIN'	=> 'The number of forums to create, they will be spread evenly over the created categories.',
	'NUM_NEW_GROUP'			=> 'Newly registered users',
	'NUM_NEW_GROUP_EXPLAIN'	=> 'The number of users to place in the newly registered group. If this number is larger than the number of users, it will be ignored.',
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
	'OPTIONS'				=> 'Options',
	'OTHER_CONFIG'			=> 'Additional board config settings',
	'OTHER_CONFIG_EXPLAIN'	=> 'Config settings entered here will be updated in the config table or added to the config table if they don’t exist yet. <u>Make sure to spell correctly.</u> This can also be edited when creating the boards.<br /><br />Type one config setting per line in a semicolon <kbd>;</kbd> separated list e.g.: <kbd>config-name;config-setting;is-dynamic</kbd>. If the setting is not dynamic then the dynamic part is not needed. Lines starting with a <kbd>#</kbd> are considered comments and not added to the DB.<br /><br />Example:<br /><kbd>session_length;999999</kbd><br /><kbd>load_tplcompile;1;1</kbd><br /><kbd># This is a comment</kbd>',

	'PHPBB_QI_TEXT'		=> 'phpBB<small><sup>&reg;</sup></small> QuickInstall',
	'PHPBB_QI_FULLLINK'	=> '<a href="https://www.phpbb.com/customise/db/official_tool/phpbb3_quickinstall/">phpBB<small><sup>&reg;</sup></small> QuickInstall</a> %s for phpBB 3.0 - 4.0',
	'PHPBB_QI_TITLE'	=> 'phpBB&reg; QuickInstall',

	'PHP_INCOMPATIBLE'	=> 'The board you are trying to use, phpBB %1$s, is not compatible with PHP %2$s. Refer to the Documentation for our compatibility grid.',
	'PHPINFO'			=> 'PHP info',
	'PHPINFO_TITLE'		=> 'PHP information',
	'PHPINFO_EXPLAIN'	=> 'This page lists information on the version of PHP installed on this server. It includes details of loaded modules, available variables and default settings. This information may be useful when diagnosing problems. Please be aware that some hosting companies will limit what information is displayed here for security reasons. You are advised to not give out any details on this page except when asked by official phpBB team members.',
	'PLAIN_TEXT'		=> 'Note: QuickInstall stores passwords and usernames as plain text.',
	'POPULATE'			=> 'Populate board',
	'POPULATE_EXPLAIN'	=> 'Populates the board with the number of users, forums, posts and topics you specify below. Note that the more users, forums, posts and topics you specify, the longer it will take to process.',
	'POPULATE_EXPLAIN_BRIEF' => 'Populate the board with users, forums, posts and topics.',
	'POPULATE_OPTIONS'	=> 'Populate options',
	'POWERED_BY_PHPBB'	=> 'Powered by <a href="https://www.phpbb.com/">phpBB</a><sup>&reg;</sup> Forum Software &copy; phpBB Limited',
	'PROFILES'			=> 'Profiles',

	'QI_LANG'			=> 'Select QuickInstall language',
	'QI_LANG_EXPLAIN'	=> 'Select a language for QuickInstall. Languages are stored in the <code>language/</code> directory.',
	'QI_MANAGE_HEADINGS'=> 'Click on the headings below to access optional configurations. Changes made below are not saved to the current profile.',
	'QI_MANAGE_PROFILE'	=> 'Manage profiles',
	'QI_SETTINGS'		=> 'QuickInstall settings',
	'QI_TZ'				=> 'Time zone',
	'QI_TZ_EXPLAIN'		=> 'Set the default time zone for your boards.',
	'QUICKINSTALL'		=> 'QuickInstall',
	'QUICKINSTALL_LOGO'	=> 'Quick%sInstall',

	'REDIRECT'			=> 'Redirect',
	'REDIRECT_EXPLAIN'	=> 'Redirect to new board after it is created.',
	'REDIRECT_BOARD'	=> 'Redirect to new board',
	'RESET'				=> 'Reset',

	'SAVE'					=> 'Save',
	'SAVE_PROFILE'			=> 'Save as new profile',
	'SAVE_PROFILE_EXPLAIN'	=> 'Enter a name to create a new profile with these settings, or leave this field blank to update the current profile. If a profile of the same name already exists, it will be overwritten.<br /><br />Allowed characters: <kbd>A-Z a-z 0-9 - _ .</kbd>',
	'SAVE_SETTINGS'			=> 'Save profile',
	'SEARCH_HERE'			=> 'Search here...',
	'SELECT_ALL'			=> 'Select all',
	'SET_DEFAULT_STYLE'		=> 'Set default style',
	'SET_DEFAULT_STYLE_EXPLAIN'	=> 'Enter the name of the style you want to use as the default style. The name can be found in the <code>styles/[style name]/style.cfg</code> file. Defaults to prosilver if empty or the style can’t be installed.',
	'SETTINGS_FAILURE'		=> 'The following errors occurred:',
	'SETTINGS_SECTIONS'		=> 'Settings',
	'SETTINGS_SUCCESS'		=> 'Your settings were successfully saved.',
	'SERVER_SETTINGS'		=> 'Server',
	'SERVER_NAME'			=> 'Server name',
	'SERVER_NAME_EXPLAIN'	=> 'Usually <code>localhost</code> since QuickInstall is <strong>not</strong> intended for public servers.',
	'SERVER_PORT'			=> 'Server port',
	'SERVER_PORT_EXPLAIN'	=> 'Usually <code>80</code>.',
	'SHOW_CONFIRM'			=> 'Confirm delete',
	'SHOW_CONFIRM_EXPLAIN'	=> 'Show a confirmation alert before deleting boards and profiles.',
	'SMTP_AUTH'				=> 'SMTP authentication method',
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
	'TOGGLE_NAVIGATION'		=> 'Toggle navigation',

	'UPDATE_AVAILABLE'		=> 'An update is available.',

	'VERSION_CHECK_TITLE'	=> 'QI %1$s is available. You are using QI %2$s. Click to download the latest version.',

	'WORKING_ON_IT'			=> 'We’re working on it...',
	'WORKING_ON_IT_EXPLAIN'	=> 'This may take a few minutes.',

	'YES'	=> 'Yes',

	'COLON'	=> ':',

	// Database connection test
	'DB_TEST_TYPE_REQUIRED'		=> 'Database type is required',
	'DB_TEST_HOST_REQUIRED'		=> 'Database host is required',
	'DB_TEST_CONNECTION_SUCCESS'	=> 'Database connection successful',
	'DB_TEST_CONNECTION_FAILED'	=> 'Connection failed',
	'DB_TEST_SQLITE3_AVAILABLE'	=> 'SQLite3 extension is available',
	'DB_TEST_SQLITE_AVAILABLE'	=> 'SQLite extension is available',
	'DB_TEST_SQLITE_NOT_AVAILABLE'	=> 'SQLite extension not available',
	'TEST_DATABASE_CONNECTION'	=> 'Test Database Connection',
));
