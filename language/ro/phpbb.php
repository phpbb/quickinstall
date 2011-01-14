<?php
/**
*
* phpbb [Română]
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
	'TRANSLATION_INFO'	=> 'phpBB România',
	'DIRECTION'			=> 'ltr',
	'DATE_FORMAT'		=> '|d M Y|',	// 01 Jan 2007
	'USER_LANG'			=> 'ro',
	'USER_LANG_LONG'	=> 'Română',

	'datetime'			=> array(
	 'TODAY'        => 'Astăzi',
        'TOMORROW'    => 'Mâine',
        'YESTERDAY'    => 'Ieri',

        'Sunday'    => 'Duminică',
        'Monday'    => 'Luni',
        'Tuesday'    => 'Marţi',
        'Wednesday'    => 'Miercuri',
        'Thursday'    => 'Joi',
        'Friday'    => 'Vineri',
        'Saturday'    => 'Sâmbată',

        'Sun'        => 'Dum',
        'Mon'        => 'Lun',
        'Tue'        => 'Mar',
        'Wed'        => 'Mie',
        'Thu'        => 'Joi',
        'Fri'        => 'Vin',
        'Sat'        => 'Sâm',

        'January'    => 'Ianuarie',
        'February'    => 'Februarie',
        'March'        => 'Martie',
        'April'        => 'Aprilie',
        'May'        => 'Mai',
        'June'        => 'Iunie',
        'July'        => 'Iulie',
        'August'    => 'August',
        'September' => 'Septembrie',
        'October'    => 'Octombrie',
        'November'    => 'Noiembrie',
        'December'    => 'Decembrie',

        'Jan'        => 'Ian',
        'Feb'        => 'Feb',
        'Mar'        => 'Mar',
        'Apr'        => 'Apr',
        'May_short'    => 'Mai',    // Short representation of "May". May_short used because in English the short and long date are the same for May.
        'Jun'        => 'Iun',
        'Jul'        => 'Iul',
        'Aug'        => 'Aug',
        'Sep'        => 'Sep',
        'Oct'        => 'Oct',
        'Nov'        => 'Noi',
        'Dec'        => 'Dec',
	),

	// The default dateformat which will be used on new installs in this language
	// Translators should change this if a the usual date format is different
	'default_dateformat'	=> 'D M d, Y g:i a', // Mon Jan 01, 2007 1:37 pm

));

?>