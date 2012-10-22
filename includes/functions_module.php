<?php
/**
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
* Module handler from wiedler.ch, optimized for phpbb
* @package quickinstall
*/

class module_handler
{
	protected $modules_path;
	protected $modules_prefix;

	public function __construct($modules_path, $modules_prefix = '')
	{
		$this->modules_path = (string) $modules_path;
		$this->modules_prefix = (string) $modules_prefix;
	}

	public function load($module, $default)
	{
		global $phpEx, $user;

		// just some security (thanks lordlebrand)
		$module = basename($module);

		if (!file_exists($this->modules_path . $this->modules_prefix . $module . '.' . $phpEx))
		{
			$module = $default;
		}

		if (false === @include($this->modules_path . $this->modules_prefix . $module . '.' . $phpEx))
		{
			trigger_error(sprintf($user->lang['NO_MODULE'], $module), E_USER_ERROR);
		}

		$class_name = $this->modules_prefix . $module;
		return new $class_name();
	}
}
