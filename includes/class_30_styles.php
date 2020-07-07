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

class class_30_styles
{
	private $qi_default_style = '';

	private $qi_styles = array();

	private $styles_path = 'styles/';

	private $acp_styles;

	public function __construct()
	{
		global $settings, $phpbb_root_path;

		$this->styles_path = $phpbb_root_path . $this->styles_path;

		$this->acp_styles = new acp_styles();
		$this->acp_styles->main(0, '');

		$this->qi_default_style	= $settings->get_config('default_style', '');

		// Get available styles
		$this->qi_get_styles();

		// Install all styles.
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
		global $phpbb_root_path;

		if (!empty($style['inherit_from']) && empty($this->qi_styles[$style['inherit_from']]))
		{
			// We don't have the parent skip this.
			return;
		}
		else if (!empty($style['inherit_from']) && empty($this->qi_styles[$style['inherit_from']]['style_id']))
		{
			// Need to install the parent first.
			$this->qi_install_style($this->qi_styles[$style['inherit_from']]);
		}

		$error = array();

		$install_path = $style['install_path'];
		$root_path = "{$phpbb_root_path}styles/$install_path/";

		$this->acp_styles->install_style($error, 'install', $root_path, $style['style_id'], $style['style_name'], $install_path, $style['style_copyright'], $style['style_active'], $style['style_default'], $style);
		unset($error);

		$this->qi_styles[$style['style_name']] = $style;
	}

	private function qi_get_styles()
	{
		$dh = dir($this->styles_path);
		while (($dir = $dh->read()) !== false)
		{
			// Ignore everything that starts with a dot, is a file or prosilver.
			if ($dir[0] === '.' || is_file($this->styles_path . $dir))
			{
				continue;
			}

			$style_ary	= array(
				'style_id'		=> ($dir === 'prosilver') ? 1 : 0,
				'template_id'	=> 0,
				'theme_id'		=> 0,
				'imageset_id'	=> 0,
				'store_db'		=> 0,
				'style_active'	=> 1,
			);

			// Read cfg files and fill $style_ary.
			// style.cfg
			$install_path = $this->styles_path . $dir;

			$cfg_file	= $install_path . '/style.cfg';
			$rows		= parse_cfg_file($cfg_file);
			$style_name	= (!empty($rows['name'])) ? $rows['name'] : '';

			$style_ary['style_name']		= (!empty($rows['name'])) ? $rows['name'] : '';
			$style_ary['style_copyright']	= (!empty($rows['copyright'])) ? $rows['copyright'] : '';

			// imageset.cfg
			$cfg_file	= $install_path . '/imageset/imageset.cfg';
			$rows		= parse_cfg_file($cfg_file);

			$style_ary['imageset_name']			= (!empty($rows['name'])) ? $rows['name'] : '';
			$style_ary['imageset_copyright']	= (!empty($rows['copyright'])) ? $rows['copyright'] : '';

			// template.cfg
			$cfg_file	= $install_path . '/template/template.cfg';
			$rows		= parse_cfg_file($cfg_file);

			$style_ary['template_name']			= (!empty($rows['name'])) ? $rows['name'] : '';
			$style_ary['template_copyright']	= (!empty($rows['copyright'])) ? $rows['copyright'] : '';
			$style_ary['bbcode_bitfield']		= (!empty($rows['template_bitfield'])) ? $rows['template_bitfield'] : '';
			$style_ary['inherit_from']			= (!empty($rows['inherit_from'])) ? $rows['inherit_from'] : '';

			// theme.cfg
			$cfg_file	= $install_path . '/theme/theme.cfg';
			$rows		= parse_cfg_file($cfg_file);

			$style_ary['theme_name']		= (!empty($rows['name'])) ? $rows['name'] : '';
			$style_ary['theme_copyright']	= (!empty($rows['copyright'])) ? $rows['copyright'] : '';

			// Other stuff
			$style_ary['style_default']	= ($this->qi_default_style == $style_name) ? 1 : 0;
			$style_ary['install_path']	= $dir;

			$this->qi_styles[$style_name] = $style_ary;
		}
		$dh->close();
	}
}
