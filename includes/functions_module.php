<?php
/**
*
* @package quickinstall
* @version $Id$
* @copyright (c) 2007 eviL3
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
* Module handler from wiedler.ch, optimized for phpbb
* @package quickinstall
*/

class module_handler
{
	var $modules_path;
	
	function module_handler($modules_path)
	{
		$this->modules_path = $modules_path;
	}
	
	function load($module, $default, $sub = false)
	{
		global $phpEx, $user;
		
		// just some security (thanks lordlebrand)
		//$module = str_replace(array('.', '/', '\\'), '', $module);
		$module = basename($module);
		
		if (!file_exists($this->modules_path . (($sub !== false) ? $sub . '/' : '') . $module . '.' . $phpEx))
		{
			$module = $default;
		}
		
		if (!@include($this->modules_path . (($sub !== false) ? $sub . '/' : '') . $module . '.' . $phpEx))
		{
			trigger_error(sprintf($user->lang['NO_MODULE'], $module), E_USER_ERROR);
		}
		
		return new $module;
	}
}

?>