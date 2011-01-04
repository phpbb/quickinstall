<?php
/**
*
* info_acp_qi [正體中文]
*
* @package language
* @version $Id$
* @copyright (c)  2010 phpBB-TW 心靈捕手 (wang5555) http://phpbb-tw.net/phpbb/
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
	'LOG_INSTALL_INSTALLED_QI'	=> '<strong>已安裝 phpBB Quickinstall %s 版</strong>',
));

?>