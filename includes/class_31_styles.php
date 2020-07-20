<?php
/**
*
* @package phpBB quickinstall
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_QUICKINSTALL'))
{
	exit;
}

class class_31_styles extends acp_styles
{
	private $qi_styles = array();

	private $qi_default_style = '';

	public function __construct()
	{
		global $db, $user, $phpbb_root_path, $phpEx, $template, $request, $cache, $auth, $config, $settings;

		$this->mode = 'install';
		$this->db = $db;
		$this->user = $user;
		$this->template = $template;
		$this->request = $request;
		$this->cache = $cache;
		$this->auth = $auth;
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;
		$this->styles_path = $this->phpbb_root_path . $this->styles_path_absolute . '/';

		$this->qi_default_style	= $settings->get_config('default_style', '');

		// Get a array with installed styles.
		// Should only contain prosilver
		$installed = $this->get_styles();

		// Get a array with all not installed styles.
		$available = $this->find_available(true);

		// And merge them into one array.
		$style_ary = array_merge($installed, $available);

		// Set styles as active and put their name as key.
		foreach ($style_ary as $style)
		{
			$style['style_active'] = 1;
			$this->qi_styles[$style['style_name']] = $style;
		}

		unset($style_ary);

		// We have the needed style data.
		foreach ($this->qi_styles as $key => $style)
		{
			if (empty($this->qi_styles[$key]['style_id']))
			{
				$this->qi_install_style($style);
			}
		}
	}

	private function qi_install_style($style)
	{
		if (!empty($style['_inherit_name']) && empty($this->qi_styles[$style['_inherit_name']]))
		{
			// We don't have the parent skip this.
			return;
		}
		else if (!empty($style['_inherit_name']) && empty($this->qi_styles[$style['_inherit_name']]['style_id']))
		{
			// Need to install the parent first.
			$this->qi_install_style($this->qi_styles[$style['_inherit_name']]);
		}

		if (!empty($style['_inherit_name']) && !empty($this->qi_styles[$style['_inherit_name']]['style_id']))
		{
			// Set parent id.
			$style['style_parent_id']	= $this->qi_styles[$style['style_name']]['style_parent_id']		= $this->qi_styles[$style['_inherit_name']]['style_id'];
			$style['style_parent_tree']	= $this->qi_styles[$style['style_name']]['style_parent_tree']	= $this->qi_styles[$style['_inherit_name']]['style_path'];
		}

		$id = $this->install_style($style);
		$this->qi_styles[$style['style_name']]['style_id'] = $id;

		if ($this->qi_default_style == $style['style_name'])
		{
			$this->qi_set_default($id);
		}
	}

	private function qi_set_default($id)
	{
		set_config('default_style', $id);

		// Set it for guests and the admin too.
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_style = ' . (int) $id . '
			WHERE user_id = 1 or user_id = 2';
		$this->db->sql_query($sql);
	}
}
