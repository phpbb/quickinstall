<?php
/**
*
* This file is part of French QuickInstall translation.
* Copyright (c) 2010 Ma�l Soucaze.
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; version 2 of the License.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along
* with this program; if not, write to the Free Software Foundation, Inc.,
* 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*
* phpbb [French]
*
* @package   quickinstall
* @author    Ma�l Soucaze <maelsoucaze@gmail.com> (Ma�l Soucaze) http://mael.soucaze.com/
* @copyright (c) 2007, 2008 eviL3
* @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
* @version   $Id$
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
	'DATE_FORMAT'		=> '|d F Y|',	// 01 January 2007
	'USER_LANG'			=> 'fr',
	'USER_LANG_LONG'	=> 'Fran�ais',

	'datetime'			=> array(
		'TODAY'		=> 'Aujourd�hui',
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
		'February'	=> 'F�vrier',
		'March'		=> 'Mars',
		'April'		=> 'Avril',
		'May'		=> 'Mai',
		'June'		=> 'Juin',
		'July'		=> 'Juillet',
		'August'	=> 'Ao�t',
		'September' => 'Septembre',
		'October'	=> 'Octobre',
		'November'	=> 'Novembre',
		'December'	=> 'D�cembre',

		'Jan'		=> 'Jan',
		'Feb'		=> 'F�v',
		'Mar'		=> 'Mars',
		'Apr'		=> 'Avr',
		'May_short'	=> 'Mai',	// Short representation of "May". May_short used because in English the short and long date are the same for May.
		'Jun'		=> 'Juin',
		'Jul'		=> 'Juil',
		'Aug'		=> 'Ao�t',
		'Sep'		=> 'Sep',
		'Oct'		=> 'Oct',
		'Nov'		=> 'Nov',
		'Dec'		=> 'D�c',
	),

	// The default dateformat which will be used on new installs in this language
	// Translators should change this if a the usual date format is different
	'default_dateformat'	=> 'd F Y, H:i', // 01 January 2007, 13:37

));

?>