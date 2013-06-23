<?php
/**
*
* phpbb [正體中文]
*
* @package quickinstall
* @version $Id$
* @copyright (c)  2010 phpBB-TW 心靈捕手 (wang5555) http://phpbb-tw.net/phpbb/
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
	'TRANSLATION_INFO'	=> '正體中文語系由 <a href="http://phpbb-tw.net/" onclick="window.open(this.href);return false;"><span style="color:#ff6633"><strong>竹貓星球</strong></span></a> 維護製作',	'DIRECTION'			=> 'ltr',
	'DATE_FORMAT'		=> '|Y-m-d|',// 2007-01-01
	'USER_LANG'			=> 'zh-tw',
	'USER_LANG_LONG'	=> '正體中文',

	'datetime'			=> array(
		'TODAY'		=> '今天',
		'TOMORROW'	=> '明天',
		'YESTERDAY'	=> '昨天',

		'Sunday'	=> '星期天',
		'Monday'	=> '星期一',
		'Tuesday'	=> '星期二',
		'Wednesday'	=> '星期三',
		'Thursday'	=> '星期四',
		'Friday'	=> '星期五',
		'Saturday'	=> '星期六',

		'Sun'		=> '週日',
		'Mon'		=> '週一',
		'Tue'		=> '週二',
		'Wed'		=> '週三',
		'Thu'		=> '週四',
		'Fri'		=> '週五',
		'Sat'		=> '週六',

		'January'	=> '一月',
		'February'	=> '二月',
		'March'		=> '三月',
		'April'		=> '四月',
		'May'		=> '五月',
		'June'		=> '六月',
		'July'		=> '七月',
		'August'	=> '八月',
		'September' => '九月',
		'October'	=> '十月',
		'November'	=> '十一月',
		'December'	=> '十二月',

		'Jan'		=> '1月',
		'Feb'		=> '2月',
		'Mar'		=> '3月',
		'Apr'		=> '4月',
		'May_short'	=> '5月',	// Short representation of "May". May_short used because in English the short and long date are the same for May.
		'Jun'		=> '6月',
		'Jul'		=> '7月',
		'Aug'		=> '8月',
		'Sep'		=> '9月',
		'Oct'		=> '10月',
		'Nov'		=> '11月',
		'Dec'		=> '12月',
	),

	// The default dateformat which will be used on new installs in this language
	// Translators should change this if a the usual date format is different
	'default_dateformat'	=> 'Y年 M j日, H:i', // 2007年 1月 1日, 13:37

));

?>