<?php
/**
*
* phpbb [french]
* translated by PhpBB-fr.com <http://www.phpbb-fr.com/>
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
	'USER_LANG'			=> 'fr',
	'USER_LANG_LONG'	=> 'Français',

	'datetime'			=> array(
		'TODAY'		=> 'Aujourd’hui',
		'TOMORROW'	=> 'Demain',
		'YESTERDAY'	=> 'Hier',

		'Sunday'	=> 'Dimanche',
		'Monday'	=> 'Lundi',
		'Tuesday'	=> 'Mardi',
		'Wednesday'	=> 'Mercredi',
		'Thursday'	=> 'Jeudi',
		'Friday'	=> 'Vendredi',
		'Saturday'	=> 'Samedi',

		'Sun'		=> 'Dim',
		'Mon'		=> 'Lun',
		'Tue'		=> 'Mar',
		'Wed'		=> 'Mer',
		'Thu'		=> 'Jeu',
		'Fri'		=> 'Ven',
		'Sat'		=> 'Sam',

		'January'	=> 'Janvier',
		'February'	=> 'Février',
		'March'		=> 'Mars',
		'April'		=> 'Avril',
		'May'		=> 'Mai',
		'June'		=> 'Juin',
		'July'		=> 'Juiller',
		'August'	=> 'Août',
		'September' => 'Septembre',
		'October'	=> 'Octobre',
		'November'	=> 'Novembre',
		'December'	=> 'Décembre',

		'Jan'		=> 'Jan',
		'Feb'		=> 'Fév',
		'Mar'		=> 'Mar',
		'Apr'		=> 'Avr',
		'May_short'	=> 'Mai',	// Réprésentation courte de "Mai". May_short utilisé car en français et en anglais la date courte et longue sont les mêmes pour mai.
		'Jun'		=> 'Juin',
		'Jul'		=> 'Juil',
		'Aug'		=> 'Aoû',
		'Sep'		=> 'Sep',
		'Oct'		=> 'Oct',
		'Nov'		=> 'Nov',
		'Dec'		=> 'Déc',
	),

	// The default dateformat which will be used on new installs in this language
	// Translators should change this if a the usual date format is different
	'default_dateformat'	=> 'D j M Y H:i', // Lun 10 Jan 2007 13:37

));

?>