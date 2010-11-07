<?php
/** 
*
* @package automod
* @version $Id$
* @copyright (c) 2008 phpBB Group 
* @license http://opensource.org/licenses/gpl-2.0.php GNU Public License 
*
*/

/**
* @package automod
*/
class acp_mods_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_mods',
			'title'		=> 'ACP_CAT_MODS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'frontend'		=> array('title' => 'ACP_AUTOMOD', 'auth' => 'acl_a_mods', 'cat' => array('ACP_MODS_GENERAL')),
				'config'		=> array('title' => 'ACP_AUTOMOD_CONFIG', 'auth' => 'acl_a_mods', 'cat' => array('ACP_MODS_GENERAL')),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}

?>