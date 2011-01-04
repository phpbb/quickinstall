<?php
/**
*
* phpbb [Dutch]
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
	'TRANSLATION_INFO'	=> '<a href="http://www.phpbb.nl">phpBB.nl Vertaling</a>', // Copyright mag verwijderd worden
	'DIRECTION'			=> 'ltr',
	'DATE_FORMAT'		=> '|d M Y|',	// 01 jan 2007
	'USER_LANG'			=> 'nl-nl',
	'USER_LANG_LONG'	=> 'Nederlands (Informeel)',

	'datetime'			=> array(
		'TODAY'		=> 'vandaag',
		'TOMORROW'	=> 'morgen',
		'YESTERDAY'	=> 'gisteren',

		'Sunday'	=> 'zondag',
		'Monday'	=> 'maandag',
		'Tuesday'	=> 'dinsdag',
		'Wednesday'	=> 'woensdag',
		'Thursday'	=> 'donderdag',
		'Friday'	=> 'vrijdag',
		'Saturday'	=> 'zaterdag',

		'Sun'		=> 'zo',
		'Mon'		=> 'ma',
		'Tue'		=> 'di',
		'Wed'		=> 'wo',
		'Thu'		=> 'do',
		'Fri'		=> 'vr',
		'Sat'		=> 'za',

		'January'	=> 'januari',
		'February'	=> 'februari',
		'March'		=> 'maart',
		'April'		=> 'april',
		'May'		=> 'mei',
		'June'		=> 'juni',
		'July'		=> 'juli',
		'August'	=> 'augustus',
		'September'	=> 'september',
		'October'	=> 'oktober',
		'November'	=> 'november',
		'December'	=> 'december',

		'Jan'		=> 'jan',
		'Feb'		=> 'feb',
		'Mar'		=> 'maart',
		'Apr'		=> 'apr',
		'May_short'	=> 'mei',	// Short representation of "May". May_short used because in english the short and long date are the same for May.
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
	'default_dateformat'	=> 'D d M Y, H:i', // Ma 01 jan 2007 13:37

));

?>