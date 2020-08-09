# QuickInstall Changelog

## Version 1.5.0
- [Change] Config profiles in the `settings` directory are now in JSON format. Existing profiles in the old text format will automatically be converted to JSON.
- [Change] Prettied up the documentation a bit.
- [Change] Always show profiles in a drop-down menu, even when there is only one, for consistency. Also, naturally/case-insensitively sort them.
- [Change] Now, when updating a saved profile, the name of the current profile will appear in the Save Profile's input field.
- [Change] Relocated some error messages and alerts to the top of the page.
- [Change] Improve appearances of some notices in the QI interface.
- [Change] Literally countless code improvements, removing or fixing ancient code constructs and bad code smells, including completely rewriting the settings class from the ground up.
- [Change] Updated Bootstrap to 4.5.2.
- [Fix] Improved appearance of the Installed boards list in the sidebar and fixed layout issues it had with really long or really short board names.

## Version 1.4.2
- [Fix] Fixed Delete buttons broken in version 1.4.1.

## Version 1.4.1
- [Change] Replaced use of Emoji with BS icons for a more cohesive and consistent UI.
- [Change] Improved all Buttons in the QI UI.
- [Change] Removed QI version constant. QI's version number now maintained in `composer.json`.
- [Change] Removed global QI constants. Now `qi_constants.php` is used just for debugging.
- [Fix] Fixed an issue where phpBB 3.1.0 to 3.2.1 boards would break QuickInstall's new Twig template engine.
- [Fix] Fixed the behavior of the Check all boards toggle switch.
- [Fix] Fixed responsive issues with the nav bar at the top of QI.

## Version 1.4.0
- [Change] Re-styled some of QI's UI elements including sidebar menus, buttons and various other form elements.
- [Change] Migrated QI's Bootstrap framework to version 4.5.
- [Change] Bootstrap framework now managed as an NPM library, located in the new `develop` folder. Contributors can use NPM commands to update, customise, compile and deploy Bootstrap to QI (see README).
- [Change] Migrated QI's templating framework to Twig. All QI html files now use Twig syntax. Contributors can use Composer commands to update Twig, located in the `vendor` directory.
- [Change] Dropped support for installing AutoMOD into 3.0.x boards from QuickInstall. AutoMOD can still be installed manually for 3.0.x boards.
- [Change] Dropped the *subsilver2 only* option from the Install additional styles setting.
- [Change] Added a `DEBUG` constant to `qi_constants.php`. Contributors can un-comment it while developing QI to disable template caching.
- [Fix] Fixed an issue with the migration schema generator that blocked QI from working with phpBB 4.0.0-dev.
- [Fix] Fixed some issues with language vars not working on error pages.

## Version 1.3.7
- [Fix] Improved PHP version compatibility checks to make sure the version of phpBB you are attempting to install will work with your version of PHP.
- [Change] The About page has been merged and streamlined with the Documentation page.
- [Change] Official phpBB logos have been added to QI's headers and page titles.

## Version 1.3.6
- [Feature] Added a new setting to enable/disable Debug mode on boards. Disable it for a production board, or enable it for a development board.
- [Fix] Fixed compatibility issues with phpBB 3.3.0-b2.
- [Fix] Fixed compatibility issues with phpBB 4.0.0-a1-dev.
- [Fix] Fixed compatibility issues with SQLite and phpBB 3.2 (or newer).
- [Fix] Fixed the config.php files so they have the relevant DEBUG related definitions for a given phpBB board version.
- [Change] Added badge-styling to the phpBB board version number displayed next to the board name in the QI sidebar.

## Version 1.3.5
- [Fix] Fixed an issue where search results of pre-populated posts showed unparsed BBCode XML tags.
- [Fix] Fixed a PHP notice that popped up in QI's templating system with PHP 7.2.

## Version 1.3.4
- [Fix] Fixed an issue introduced in 1.3.3 that caused a fatal error while installing 3.0 boards.

## Version 1.3.3
- [Fix] phpBB 3.2.3 can now be installed with QI without failing and getting hung up.

## Version 1.3.2
- [Change] Sort profiles naturally in drop down lists.
- [Fix] Corrected potential problems with how URLs are generated internally.

## Version 1.3.1
- [Feature] Added QI version update check. New version notifications will be available in the header nav-bar.
- [Fix] Correctly allow dot in board directory name.

## Version 1.3.0
- [Change] QuickInstall has a new paint-job, with a fresh modern look...a flat, responsive, prosilver inspired theme.
- [Change] Rewrote all javascript functionality from the ground up, using jQuery.
- [Change] Updated the README/Documentation and About information.
- [Change] Updated most of the language files to fix typos and improve clarity.
- [Change] Use a common template file for general error message handling.
- [Change] Prevent an issue when deleting boards where files can't be deleted if the DB connection errors.
- [Change] Removed 'eviLs testing hood' as the default site description value, replaced with 'QuickInstall sandbox'.
- [Change] Changed the default setting for showing confirmation alerts when deleting boards and profiles to true.
- [Change] Use the gen_error_msg() function instead of compiling an error template when board deletion has an error.
- [Change] Removed the default case from qi_manage since it is no longer used (there is no 'manage' mode).
- [Change] Removed duplicated phpBB copyright from the page footers.
- [Change] Moved "Set default style" setting from main options to Misc options as it isn't commonly used.
- [Fix] Fixed several probable bugs discovered (undefined/unused variables, etc.).
- [Fix] Fixed an issue where the Boards tab would not appear after initial installation was saved.
- [Fix] Fixed an issue where the settings template was not being stored in the cache as expected nor allowed to recompile.
- [Fix] Removed dots from anchor links in the changelog as they caused problems with ScrollSpy.
- [Feature] Added a new AJAX working/loading indicator when creating boards for a better UX.
- [Feature] PHP Info page now has sidebar navigation and a new live search feature, making it much easier to navigate.
- [Feature] Use ScrollSpy to highlight sidebar nav links that correspond with the visible page sections.
- [Feature] Added ability to copy the raw config data that is presented when the settings.cfg can not be written.
- [Feature] Use cache busting for CSS and JS using QI version to ensure upgrades get fresh assets.

## Version 1.2.5
- [Fix] Issue #75 Fixed issues with creating boards using a PostgreSQL database.
- [Fix] Corrected an issue where clicking the Documentation tabs led to PHP errors.

## Version 1.2.4
- [Fix] Issue #71 Converted README and CHANGELOG to markdown. Correctly parse markdown in the Documentation and About tabs.
- [Fix] Issue #69 Correctly handle backslashes in the generated config.php file for phpBB >= 3.1.

## Version 1.2.3
- [Fix] Issue #64 Fix incorrect post count totals.
- [Fix] Issue #63 Correctly calculate dates for users, posts, and the board start date when populating the board.
- [Fix] Issue #62 Fix display issues with the settings menu sidebar on smaller screens/windows.

## Version 1.2.2
- [Feature] Supports phpBB 3.2 boards
- [Fix] Issue #55 Improve compatibility with PHP 7
- [Fix] Issue #49 Removed closing php tag from generated config.php file
- [Fix] Fix for UTF-8 characters $error_msg
- [Fix] Issue #45 Fix compatibility for installing phpBB 3.1.5 boards.
- [Change] Sort boards in a natural order (10 > 9).
- [Change] Added a scroll bar to the boards list.

## Version 1.2.1
- [Fix] Bug in the routine to purge the cache.
- [Fix] Ticket #EXTTOOLS-655 The redirect got two slashes in the URL when QI was installed in localhost root.
- [Fix] Write correct phpBB version (3.1 or 3.0) in config.php.
- [Fix] Error messaging in main did not support multibyte chars. (By Skouat)
- [Fix] Issue #37 Style for "Chunk settings" title was messed up.
- [Fix] Issue #38 Subsilver2 was not installed for 3.1.x boards.
- [Fix] The "boards" tab was not hidden when there where errors in submitting first/only config.
- [Fix] QI tried to write to the "settings" folder even if it was not writable.
- [Fix] The explain text for the Boards tab was not up to date.
- [Fix] Issue #42 the install note box got more or less unreadable in some screen resolutions.
- [Fix] "schema.json" was removed in phpBB 3.1.3. QI used it to create the 3.1 DB. Now install of 3.1.x works again.
- [Fix] Ticket #44 QI removed uppercase letters from directory names due to a missing Hyphen "-".
- [Fix] Ticket #43 when run in server root dirname() on Windows returns a backslash QI did not handle that correct. (By Skouat)
- [Change] Moved the "can take a while" warning box and the options/settings boxes.
- [Change] Moved the header text to the sidebar in the PHP info tab.
- [Change] Added some space abowe and below the "Check all" link.
- [Change] Moved template var S_page to their respective file.
- [Change] Changed some wordings and removed unused strings from language files.
- [Change] Changed default DBMS to MySQLi.
- [Change] Use the same name for the directory as the DB. Links to the boards get messed up with spaces.
- [Change] Allow hyphen and dot in directory names.
- [Change] Always try to login if redirect is set.
- [Change] Don't error out when the selected alt environment can't be found.
- [Change] Removed CHUNK_POST, CHUNK_TOPIC and CHUNK_USER constants. They have been in the settings for a while
- [Feature] Added better error reporting and handling to more places.
- [Feature] Don't show the select menu for alternative environments if the phpBB3_alt folder is empty.
- [Feature] Show board version in boards list.
- [Feature] Make sure cache_dir, boards_dir and boards_url ends with a slash (/)
- [Feature] Now all available styles can be installed. In both 3.0.x and 3.1.x.
- [Feature] Any style can be set as default style.
- [Feature] Automagically update settings. With link to update all profiles (only for 1.2.1 and up).
- [Feature] Added a documentation tab, with info currently from readme.txt.
- [Feature] Only display the DBMS whose extensions are loaded on the settings page.

## Version 1.2.0
- [Fix] Ticket #63309 The first release in the changelog was broken.
- [Fix] Ticket #63317 The "Forgot this" error message improved and also works with rtl languages.
- [Fix] Ticket #63318 Fixed language string error. The directory includes/automod don't exist.
- [Fix] Various tickets containing language errors fixed. And removed some more unused language strings.
- [Fix] Various tickets containing HTML and CSS errors fixed.
- [Fix] Ticket #63321 Check that both the config file and settings directory is writable.
- [Fix] Ticket #63311 The error message had the wrong file name when the config file could not be written.
- [Fix] Need to set $profile before trying to use it.
- [Fix] Ticket #63320 all left/right mentions in css need a ".rtl" equivalent that specifies the other direction.
- [Fix] Ticket #63308 Combining language strings don't  work in all languages.
- [Fix] Ticket #EXTTOOLS-649 QI did not delete the DB when deleting phpBB 3.1 boards.
- [Fix] Ticket #EXTTOOLS-650 QI did not populate migrations table.
- [Fix] Ticket #EXTTOOLS-621 Always check that default language is available before installation.
- [Fix] The button to see the configuration if the settings directory was not visible.
- [Change] Removed the cache usage. The cache directory is for now used to store file based databases.
- [Change] Had 'load_tplcompile' in several places, one should be enough.
- [Change] No need to add session and $user to only store language keys. Changed $user->lang[] to qi::lang().
- [Change] Removed "powered by phpBB" from the footer. That would no be true anymore.
- [Change] Suppress E_DEPRECATED from php >=5.5.0 to still work with php 4
- [Change]	Removed CSS hack for Mac IE since the last version was released in 2003
- [Change] Added CSS hack for Opera.
- [Change] Removed the "Manage boards" tab and moved board delete to the main tab, which where renamed to "Manage boards".
- [Change] Made some modifications to the settings tab and changed some wording.
- [Change] Better error reporting and handling. For now only when AutoMOD is missing.
- [Change] Removed the ability to show passwords when the user hovers over the input.
- [Change] Changed background color for the time note on the main page, to distinguish it from a error box.
- [Change] Ticket #EXTTOOLS-654 Replaced phpBB Group with phpBB Limited. (By Skouat)
- [Feature] Ticket #63300 Show back trace on errors.
- [Feature] Added a php info tab.
- [Feature] Ticket #63302 Added MySQLi support.
- [Feature] Supports both phpBB 3.0 and 3.1 (By Nicofuma)
- [Feature] Added a easy way to get the cache purged when working on QI.

## Version 1.1.8
- [Fix] Some language edits and spelling fixes.
- [Fix] Check that the boards directory exists and is writeable.
- [Fix] Create one user per minute and one post per second, or actually change the timestamp.
- [Fix] Set a default value for user_sig, user_occ and user_interests to not error out there.
- [Fix] Take cache directory from settings if possible.
- [Fix] Use boards_dir from configuration if it is set.
- [Fix] Added missing settings globalizations
- [Fix] Fixes to permission granting.
- [Fix] Made quickinstall functional on postgres.
- [Fix] Make sure boards_url ends with a slash.
- [Fix] Bug #62694 Clarify that it is QI that do not support PHP older than 5.2.0 and not phpBB.
- [Fix] Bug #62700 Put the config through htmlspecialchars_decode() instead of remove request_var().
- [Fix] Bug #62704 Mention the upload directory for AutoMOD 1.0.0.
- [Fix] Bug #62739 Check for phpBB3/common.php instead of the phpBB3 directory.
- [Fix] Bug #62757 Copy permissions from the default forum instead of from the default category.
- [Fix] Bug #62765 Need to globalize table prefix.
- [Fix] Bug #62831 Don't try to login when loading a alternative environment.
- [Fix] Bug #63111 When setting table prefix, table data containing "phpbb_" was also changed.
- [Fix] QI_PHPBB_VERSION and AUTOMOD_VERSION are not used so they got deleted.
- [Fix] Use the QI_VERSION constant directly instead of copying it to the config array.
- [Fix] Ticket #63262 SMTP port was not copied from settings to config table.
- [Fix] Ticket #62767 QI should not crash when it can't delete a board but inform about it.
- [Fix] Ticket #63292 Better options for Daylight saving time.
- [Fix] Ticket #63289 Need to check $_REQUEST data and config before returning the default value.
- [Fix] Ticket #63293 Changed forum/forums to board/boards in several places.
- [Fix] Fixed several spelling and wording errors.
- [Fix] Set the profile cookie when saving new profiles too.
- [Fix] Ticket #63293 Admin, board and all test users now gets the correct timezone and DST setting.
- [Fix] Ticket #63290 Make sure QI script path ends with one and only one slash.
- [Fix] Ticket #63297 If a new profile name is entered; prefill the new profile name if settings validation fails.
- [Fix] Ticket #63298 Error text was not shown when there where errors on first config save.
- [Fix] Ticket #63303 The wrong sidebar was shown in settings when installing QI.
- [Fix] Ticket #63301 Removed some globalizations which got some variables to be empty.
- [Fix] Ticket #63305 $language was null when sent as default to request_var.
- [Fix] Ticket #63306 QI needs to correctly handle an empty qi_config.cfg.
- [Change] Added checking for functions_mods.php. It will be moved in AutoMOD in the future.
- [Change] Include functions_admin.php for postgres' benefit.
- [Change] Use get_cache_dir() in sqlite dbal.
- [Change] Delay database connection until it is necessary.
- [Change] Cleaned up the JS in the main html file.
- [Change] Changed the changelog to have the same format as the rest of our tools have. A text file with most recent versions at top.
- [Change] QI pages are now in $page instead of $mode, need $mode for settings and it got kind of crowded.
- [Change] Only try to connect to DB when saving settings or creating/deleting a board.
- [Change] Removed version check from the About page and moved the changelog stuff a bit.
- [Change] Errors are stored as a array and imploded to a string on demand.
- [Change] The global $qi_config array has been removed and all setting/config are now handled by $settings.
- [Change] Deleted qi_config_sample.cfg since its not used anymore.
- [Change] The note about not removing the copyright is removed, as it is in phpBB.
- [Change] Removed some unused language strings.
- [Change] Added in-page links to the about page instead of big brother.
- [Change] Set class="radio" for all radio buttons.
- [Change] Ticket #63299 and #63296 Leave cookie domain empty for new installs and don't require it either.
- [Feature] Made quickinstall not require write access to qi_config.php
- [Feature] Added UI for setting cache directory.
- [Feature] Added a separate notion of boards url.
- [Feature] Added ability to grant specific permissions when creating boards.
- [Feature] Added hover-over on the manage tab.
- [Feature] Added support for PostgreSQL
- [Feature] Added possibility to set phpBB config fields before creating a board.
- [Feature] Ticket #62796 Added config settings for chunk sizes when populating a board.
- [Feature] Ticket #62774 and #62792 Added a settings directory to replace the config file.
- [Feature] Ticket #62774 and #62792 Added profiles with config files in a directory instead of one configuration file.
- [Feature] Added confirm to the delete button. And don't show it if there is no boards to delete.
- [Feature] Added save/reset buttons after each section on the settings page. For convenience.
- [Feature] Added anchors and internal links to the settings tab.
- [Feature] Config settings for "Drop database if it exists" and "Delete files if they exist" checkboxes default state.
- [Feature] A setting for the confirmation alert when deleting boards and profiles. To show that confirm alert or not.
- [Feature] A setting for default "Alternate environment" now that profiles exists.
- [Feature] Added option to not save passwords and/or admin/db-user names. They will be required when creating a board.
- [Feature] Use JS to check that the required fields are filled.

## Version 1.1.6
- [Fix] Make sure phpBB exists before trying to load it.
- [Fix] Allow localized usernames. Bug #62563
- [Fix] Added gitattributes to not export all .gitkeep files.
- [Change] Moved things in the settings and slightly changed the layout.
- [Fix] Rewrote some of the config handling, at least it looks nicer now. :)
- [Fix] Updated the install instructions.
- [Fix] Checks for the config file and gives a proper slap if it's missing.
- [Fix] Tries to correctly handle non valid chars in db names.
- [Change] Removed AutoMOD from the QI package.
- [Change] Moved QI's language file for phpBB.
- [Change] Moved some AutoMOD installation to QI instead of MODding UMIL or AutoMOD.
- [Feature] Added option so set how many users, forums, topics and posts that will be created.

## Version 1.1.5
- [Fix] Set the new boards language directory as custom language path.
- [Change] Moved to github.
- [Change] Deleted phpBB from the sources/ dir. I now needs to be downloaded from http://www.phpbb.com/.

## Version 1.1.4
- [Change] Finally moved to MOD Teams repo.
- [Change] Updated to AutoMOD 1.0.0.
- [Feature] AutoMOD setting "Preview changes" is on as default.

## Version 1.1.3
- [Fix] Restore the admin after making all posts and users to get the logs correct.
- [Fix] Reset the admin in umil_auto.php for the logs.
- [Feature] Added French.
- [Change] Replaced the language text input with a select.

## Version 1.1.2
- [Fix] If installed, Subsilver2 was always made the default style.

## Version 1.1.1
- [Feature] Option to install Subsilver2.

## Version 1.1.0
- [Feature] Option to make files writable.
- [Feature] Option to populate forum with users and posts.
- [Change] Updated to AutoMOD 1.0.0-RC4
- [Change] Updated for phpBB 3.0.7-PL1
- [Change] The Redirect option now works as intended
- [Change] Updated for phpBB 3.0.7-PL1
- [Change] Updated AutoMOD (Blinky) to rev 227
- [Change] Board list is now in alphabetical order
- [Change] Board listing takes the boards dir value from the config
- [Change] Redirect and AutoMOD values are taken from the config
- [Change] Uses tabs instead of links to navigate
- [Change] Replaced qi_config.php with qi_config.cfg to avoid php errors in config strings
- [Change] Added link to settings from the error page.
- [Change] Added checkbox for not using a db password.
- [Change] Added information about successfull or unsuccessfull settings update.
- [Change] Removed set_magic_quotes_runtime()
- [Change] Use the same string timezones as phpBB 3.1.x. To still be compatible with both 3.0.x and 3.1.x.
- [Feature] Added installation/settings page
- [Feature] Validates the settings on page load.

## Version 1.0.10
- [Change] Updating for phpBB 3.0.4
- [Change] Updating for AutoMOD 1.0.0-b2
- [Feature] Allow users to choose an alternate enviroment

## Version 1.0.9
- [Change] Put default site_name and site_desc on main page (thanks exreaction)
- [Change] Updating for phpBB 3.0.2

## Version 1.0.8
- [Change] Updating for phpBB 3.0.1

## Version 1.0.7
- [Fix] Updated version number in footer.
- [Fix] Added some more security.
- [Change] Partial rewrite.
- [Change] Blinky (easymod) support.

## Version 1.0.6
- [Change] Added hook functionality (auto include files).
- [Change] Updated for phpBB 3.0.0 (Gold).

## Version 1.0.5
- [Change] Updated for phpBB 3.0.RC7.

## Version 1.0.4
- [Fix] Fixed bug in about_body.html.
- [Fix] Fixed some other minon stuff.
- [Change] Added SQLite support.
- [Change] Updated for phpBB 3.0.RC6.

## Version 1.0.3
- [Change] Updated for phpBB 3.0.RC5.

## Version 1.0.2
- [Fix] Fixed language string.

## Version 1.0.1
- [Change] Updated for phpBB 3.0.RC4.

## Version 1.0.0
- [Feature] Added a changelog to the about page.
- [Feature] Added possibility of a database prefix. This also keeps the databases more organised.
- [Fix] Adjusted some of the language.
- [Fix] Copying from sources/mods/ works now.

## Version 0.1.0
- Initial release 2007-07-12
