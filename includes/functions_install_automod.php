<?php
/**
*
* @package quickinstall
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
* Some parts are from AutoMODs install file and some from UMIL
* since UMIL seems to require that somebody wants to see the result.
*
*/

/**
* @ignore
*/
if (!defined('IN_QUICKINSTALL'))
{
	exit;
}

class automod_installer
{
	public static function install_automod($board_dir, $make_writable)
	{
		global $quickinstall_path, $phpbb_root_path, $phpEx;
		global $settings, $user, $db;

		// The name of the mod to be displayed during installation.
		$mod_name = 'AUTOMOD';
		$version_config_name = 'automod_version';

		// Since AutoMOD is no longer shipped with QI we need to do some checking...
		$automod_path = '';
		if (file_exists($quickinstall_path . 'sources/automod/includes'))
		{
			// Let's assume they copied the contents.
			$automod_path = $quickinstall_path . 'sources/automod/';
		}
		else if (file_exists($quickinstall_path . 'sources/automod/root/includes'))
		{
			// They copied to complete root to automod instead of its contents.
			$automod_path = $quickinstall_path . 'sources/automod/root/';
		}
		else if (file_exists($quickinstall_path . 'sources/automod/upload/includes'))
		{
			// They copied the complete upload directory to automod instead of its contents.
			$automod_path = $quickinstall_path . 'sources/automod/upload/';
		}
		else
		{
			trigger_error($user->lang['NO_AUTOMOD']);
		}

		file_functions::copy_dir($automod_path, $board_dir);

		// include AutoMOD lanugage files.
		if (file_exists($phpbb_root_path . 'language/' . $user->lang . '/mods/info_acp_modman.' . $phpEx))
		{
			include($phpbb_root_path . 'language/' . $user->lang . '/mods/info_acp_modman.' . $phpEx);
		}
		else
		{
			include("{$phpbb_root_path}language/en/mods/info_acp_modman.$phpEx");
		}

		unset($GLOBALS['lang']);
		$GLOBALS['lang'] = &$user->lang;
		global $lang;

		if (file_exists($phpbb_root_path . 'includes/functions_mods.' . $phpEx))
		{
			include($phpbb_root_path . 'includes/functions_mods.' . $phpEx);
		}
		else
		{
			trigger_error($user->lang['FUNCTIONS_MODS_MISSING']);
		}

		require($phpbb_root_path . 'install/install_versions.' . $phpEx);
		require($phpbb_root_path . 'includes/functions_convert.' . $phpEx);
		require($phpbb_root_path . 'includes/functions_transfer.' . $phpEx);
		require($phpbb_root_path . 'umil/umil_frontend.' . $phpEx);

		// add some language entries to prevent notices
		$user->lang += array(
			'FILE_EDITS'	=> '',
			'NEXT_STEP'		=> '',
		);

		$umil = new umil_frontend($mod_name);

		// We will sort the actions to prevent issues from mod authors incorrectly listing the version numbers
		uksort($versions, 'version_compare');

		// Find the current version to install.
		// The last key has the latest version number.
		global $current_version;
		end($versions);
		$current_version = key($versions);

		$action = 'install';
		$version_select = $current_version;

		global $dbms;
		$dbms = $settings->get_config('dbms');

		$umil->run_actions($action, $versions, $version_config_name, $version_select);

		if ($make_writable)
		{
			// Tell AutoMOD to make files world writable if that is selected
			set_config('am_file_perms', '0666');
			set_config('am_dir_perms', '0777');
		}
	}
}
