<?php
/**
*
* phpbb [Turkish]
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
	'USER_LANG'			=> 'tr',
	'USER_LANG_LONG'	=> 'Turkish',

	'datetime'			=> array(
		'TODAY'		=> 'Bugün',
		'TOMORROW'	=> 'Yarın',
		'YESTERDAY'	=> 'Dün',

		'Sunday'	=> 'Pazar',
		'Monday'	=> 'Pazartesi',
		'Tuesday'	=> 'Salı',
		'Wednesday'	=> 'Çarşamba',
		'Thursday'	=> 'Perşembe',
		'Friday'	=> 'Cuma',
		'Saturday'	=> 'Cumartesi',

		'Sun'		=> 'Paz',
		'Mon'		=> 'Pzt',
		'Tue'		=> 'Sal',
		'Wed'		=> 'Çar',
		'Thu'		=> 'Per',
		'Fri'		=> 'Cum',
		'Sat'		=> 'Cmt',

		'January'	=> 'Ocak',
		'February'	=> 'Şubat',
		'March'		=> 'Mart',
		'April'		=> 'Nisan',
		'May'		=> 'Mayıs',
		'June'		=> 'Haziran',
		'July'		=> 'Temmuz',
		'August'	=> 'Ağustos',
		'September' => 'Eylül',
		'October'	=> 'Ekim',
		'November'	=> 'Kasım',
		'December'	=> 'Aralık',

		'Jan'		=> 'Oca',
		'Feb'		=> 'Şub',
		'Mar'		=> 'Mar',
		'Apr'		=> 'Nis',
		'May_short'	=> 'May',	// Short representation of "May". May_short used because in English the short and long date are the same for May.
		'Jun'		=> 'Haz',
		'Jul'		=> 'Tem',
		'Aug'		=> 'Ağu',
		'Sep'		=> 'Eyl',
		'Oct'		=> 'Eki',
		'Nov'		=> 'Kas',
		'Dec'		=> 'Ara',
	),

	// The default dateformat which will be used on new installs in this language
	// Translators should change this if a the usual date format is different
	'default_dateformat'	=> 'D M d, Y g:i a', // Mon Jan 01, 2007 1:37 pm

));

?>