<?php
/**
*
* info_acp_qi [Românã]
*
* @package language
* @version $Id$
* @copyright (c) 2008 evil3
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'LOG_INSTALL_INSTALLED_QI'	=> '<strong>Instalat de phpBB Quickinstall versiunea %s</strong>',
));

?>