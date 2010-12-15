<?php
/**
*
* phpbb [Bahasa Indonesia]
* Translated by zourbuth, 2010
* Email: zourbuth@gmail.com
* Site: http://www.phpbb-id.com
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
	'TRANSLATION_INFO'	=> '',
	'DIRECTION'			=> 'ltr',
	'DATE_FORMAT'		=> '|d M Y|',	// 01 Jan 2007
	'USER_LANG'			=> 'id',
	'USER_LANG_LONG'	=> 'Bahasa Indonesia',

	'datetime'			=> array(
		'TODAY'		=> 'Hari ini',
		'TOMORROW'	=> 'Besok',
		'YESTERDAY'	=> 'Kemarin',

		'Sunday'	=> 'Minggu',
		'Monday'	=> 'Senin',
		'Tuesday'	=> 'Selasa',
		'Wednesday'	=> 'Rabu',
		'Thursday'	=> 'Kamis',
		'Friday'	=> 'Jumat',
		'Saturday'	=> 'Sabtu',

		'Sun'		=> 'Min',
		'Mon'		=> 'Sen',
		'Tue'		=> 'Sel',
		'Wed'		=> 'Rab',
		'Thu'		=> 'Kam',
		'Fri'		=> 'Jum',
		'Sat'		=> 'Sab',

		'January'	=> 'Januari',
		'February'	=> 'Februari',
		'March'		=> 'Maret',
		'April'		=> 'April',
		'May'		=> 'Mei',
		'June'		=> 'Juni',
		'July'		=> 'Juli',
		'August'	=> 'Agustus',
		'September' => 'September',
		'October'	=> 'Oktober',
		'November'	=> 'November',
		'December'	=> 'Desember',

		'Jan'		=> 'Jan',
		'Feb'		=> 'Feb',
		'Mar'		=> 'Mar',
		'Apr'		=> 'Apr',
		'May_short'	=> 'Mei',	// Short representation of "May". May_short used because in English the short and long date are the same for May.
		'Jun'		=> 'Jun',
		'Jul'		=> 'Jul',
		'Aug'		=> 'Agu',
		'Sep'		=> 'Sep',
		'Oct'		=> 'Okt',
		'Nov'		=> 'Nov',
		'Dec'		=> 'Ded',
	),

	// The default dateformat which will be used on new installs in this language
	// Translators should change this if a the usual date format is different
	'default_dateformat'	=> 'D M d, Y g:i a', // Mon Jan 01, 2007 1:37 pm

));

?>