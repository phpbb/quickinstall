<?php
/**
*
* phpbb [English]
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

$lang = array_merge($lang, array(
	'TRANSLATION_INFO'	=> '',
	'DIRECTION'			=> 'ltr',
	'DATE_FORMAT'		=> '|d M Y|',	// 01 Jan 2007
	'USER_LANG'			=> 'en',
	'USER_LANG_LONG'	=> 'English',

	'datetime'			=> array(
		'TODAY'		=> 'Today',
		'TOMORROW'	=> 'Tomorrow',
		'YESTERDAY'	=> 'Yesterday',

		'Sunday'	=> 'Sunday',
		'Monday'	=> 'Monday',
		'Tuesday'	=> 'Tuesday',
		'Wednesday'	=> 'Wednesday',
		'Thursday'	=> 'Thursday',
		'Friday'	=> 'Friday',
		'Saturday'	=> 'Saturday',

		'Sun'		=> 'Sun',
		'Mon'		=> 'Mon',
		'Tue'		=> 'Tue',
		'Wed'		=> 'Wed',
		'Thu'		=> 'Thu',
		'Fri'		=> 'Fri',
		'Sat'		=> 'Sat',

		'January'	=> 'January',
		'February'	=> 'February',
		'March'		=> 'March',
		'April'		=> 'April',
		'May'		=> 'May',
		'June'		=> 'June',
		'July'		=> 'July',
		'August'	=> 'August',
		'September' => 'September',
		'October'	=> 'October',
		'November'	=> 'November',
		'December'	=> 'December',

		'Jan'		=> 'Jan',
		'Feb'		=> 'Feb',
		'Mar'		=> 'Mar',
		'Apr'		=> 'Apr',
		'May_short'	=> 'May',	// Short representation of "May". May_short used because in English the short and long date are the same for May.
		'Jun'		=> 'Jun',
		'Jul'		=> 'Jul',
		'Aug'		=> 'Aug',
		'Sep'		=> 'Sep',
		'Oct'		=> 'Oct',
		'Nov'		=> 'Nov',
		'Dec'		=> 'Dec',
	),

	// The default dateformat which will be used on new installs in this language
	// Translators should change this if a the usual date format is different
	'default_dateformat'	=> 'D M d, Y g:i a', // Mon Jan 01, 2007 1:37 pm

));
