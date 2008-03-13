<?php
/**
*
* install [English]
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

/**
 * Default database schema entries... for install stuff ^^
 * This is from phpBB and is used for the install page
 */
$lang = array_merge($lang, array(
	'CONFIG_BOARD_EMAIL_SIG'		=> 'Thanks, The Management',
	'CONFIG_SITE_DESC'				=> 'A _little_ text to describe your forum',
	'CONFIG_SITENAME'				=> 'yourdomain.com',

	'DEFAULT_INSTALL_POST'			=> 'This is an example post in your phpBB3 installation. You may delete this post, this topic and even this forum if you like since everything seems to be working!' . "\n\n" . 'This board was installed through phpBB QuickInstall.',

	'EXT_GROUP_ARCHIVES'			=> 'Archives',
	'EXT_GROUP_DOCUMENTS'			=> 'Documents',
	'EXT_GROUP_DOWNLOADABLE_FILES'	=> 'Downloadable Files',
	'EXT_GROUP_FLASH_FILES'			=> 'Flash Files',
	'EXT_GROUP_IMAGES'				=> 'Images',
	'EXT_GROUP_PLAIN_TEXT'			=> 'Plain Text',
	'EXT_GROUP_QUICKTIME_MEDIA'		=> 'Quicktime Media',
	'EXT_GROUP_REAL_MEDIA'			=> 'Real Media',
	'EXT_GROUP_WINDOWS_MEDIA'		=> 'Windows Media',

	'FORUMS_FIRST_CATEGORY'			=> 'My first Category',
	'FORUMS_TEST_FORUM_DESC'		=> 'This is just a test forum.',
	'FORUMS_TEST_FORUM_TITLE'		=> 'Test Forum 1',

	'RANKS_SITE_ADMIN_TITLE'		=> 'Site Admin',
	'REPORT_WAREZ'					=> 'The post contains links to illegal or pirated software.',
	'REPORT_SPAM'					=> 'The reported post has the only purpose to advertise for a website or another product.',
	'REPORT_OFF_TOPIC'				=> 'The reported post is off topic.',
	'REPORT_OTHER'					=> 'The reported post does not fit into any other category, please use the description field.',

	'SMILIES_ARROW'					=> 'Arrow',
	'SMILIES_CONFUSED'				=> 'Confused',
	'SMILIES_COOL'					=> 'Cool',
	'SMILIES_CRYING'				=> 'Crying or Very Sad',
	'SMILIES_EMARRASSED'			=> 'Embarrassed',
	'SMILIES_EVIL'					=> 'Evil or Very Mad',
	'SMILIES_EXCLAMATION'			=> 'Exclamation',
	'SMILIES_GEEK'					=> 'Geek',
	'SMILIES_IDEA'					=> 'Idea',
	'SMILIES_LAUGHING'				=> 'Laughing',
	'SMILIES_MAD'					=> 'Mad',
	'SMILIES_MR_GREEN'				=> 'Mr. Green',
	'SMILIES_NEUTRAL'				=> 'Neutral',
	'SMILIES_QUESTION'				=> 'Question',
	'SMILIES_RAZZ'					=> 'Razz',
	'SMILIES_ROLLING_EYES'			=> 'Rolling Eyes',
	'SMILIES_SAD'					=> 'Sad',
	'SMILIES_SHOCKED'				=> 'Shocked',
	'SMILIES_SMILE'					=> 'Smile',
	'SMILIES_SURPRISED'				=> 'Surprised',
	'SMILIES_TWISTED_EVIL'			=> 'Twisted Evil',
	'SMILIES_UBER_GEEK'				=> 'Uber Geek',
	'SMILIES_VERY_HAPPY'			=> 'Very Happy',
	'SMILIES_WINK'					=> 'Wink',

	'TOPICS_TOPIC_TITLE'			=> 'Welcome to phpBB3',
));

?>