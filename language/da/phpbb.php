<?php
/**
*
* phpbb [Danish]
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

$lang = array_merge($lang, array(
	'TRANSLATION_INFO'	=> 'Danish Translation by Jan Skovsgaard &copy; 2010',
	'DIRECTION'			=> 'ltr',
	'DATE_FORMAT'		=> '|j. M Y|',	// 01 Jan 2007
	'USER_LANG'			=> 'da',
	'USER_LANG_LONG'	=> 'Dansk',

	'datetime'			=> array(
		'TODAY'		=> 'i dag',
		'TOMORROW'	=> 'i morgen',
		'YESTERDAY'	=> 'i går',

		'Sunday'	=> 'søndag',
		'Monday'	=> 'mandag',
		'Tuesday'	=> 'tirsdag',
		'Wednesday'	=> 'onsdag',
		'Thursday'	=> 'torsdag',
		'Friday'	=> 'fredag',
		'Saturday'	=> 'lørdag',

		'Sun'	=> 'søn',
		'Mon'	=> 'man',
		'Tue'	=> 'tirs',
		'Wed'	=> 'ons',
		'Thu'	=> 'tors',
		'Fri'	=> 'fre',
		'Sat'	=> 'lør',

		'January'	=> 'januar',
		'February'	=> 'februar',
		'March'	=> 'marts',
		'April'	=> 'april',
		'May'	=> 'maj',
		'June'	=> 'juni',
		'July'	=> 'juli',
		'August'	=> 'august',
		'September'	=> 'september',
		'October'	=> 'oktober',
		'November'	=> 'november',
		'December'	=> 'december',

		'Jan'		=> 'jan',
		'Feb'		=> 'feb',
		'Mar'		=> 'mar',
		'Apr'		=> 'apr',
		'May_short'	=> 'maj',	// Short representation of "May". May_short used because in English the short and long date are the same for May.
		'Jun'		=> 'jun',
		'Jul'		=> 'jul',
		'Aug'		=> 'aug',
		'Sep'		=> 'sep',
		'Oct'		=> 'okt',
		'Nov'		=> 'nov',
		'Dec'		=> 'dec',
	),

	// The default dateformat which will be used on new installs in this language
	// Translators should change this if a the usual date format is different
	'default_dateformat'	=> 'j. M Y, H:i', // Mon Jan 01, 2007 1:37 pm

));

?>