<?php
/**
 *
 * @author Nathan Guse (EXreaction) http://lithiumstudios.org
 * @author David Lewis (Highway of Life) highwayoflife@gmail.com
 * @package umil
 * @version $Id$
 * @copyright (c) 2008 phpBB Group
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

/**
 * Parameters which should be setup before calling this file:
 * @param string $mod_name The name of the mod to be displayed during installation.
 * @param string $language_file The language file which will be included when installing (should contain the $mod_name)
 * @param string $version_config_name The name of the config variable which will hold the currently installed version
 * @param array $versions The array of versions and actions within each.
 */

/**
 * Language entries that should exist in the $language_file that will be included:
 * $mod_name
 * 'INSTALL_' . $mod_name
 * 'INSTALL_' . $mod_name . '_CONFIRM'
 * 'UPDATE_' . $mod_name
 * 'UPDATE_' . $mod_name . '_CONFIRM'
 * 'UNINSTALL_' . $mod_name
 * 'UNINSTALL_' . $mod_name . '_CONFIRM'
 */

// You must run define('UMIL_AUTO', true) before calling this file.
/**
 * @ignore
 */
if (!defined('UMIL_AUTO'))
{
	exit;
}

/*
* If IN_PHPBB is already defined, lets assume they already included the common.php file and are done with setup
*
* NOTE: If you do not setup the common.php file YOU WILL NOT BE ABLE TO USE ANY CONSTANTS!
*/
if (!defined('IN_PHPBB'))
{
	define('IN_PHPBB', true);
	include($phpbb_root_path . 'common.' . $phpEx);
	$user->session_begin();
	$auth->acl($user->data);
	$user->setup();
}

// Add the language file if one was specified
if (isset($language_file))
{
	$user->add_lang($language_file);
}
if (!isset($user->lang[$mod_name]))
{
	// Prevent errors if the language key doesn't exist.
	$user->lang[$mod_name] = $mod_name;
}

// Reset our basic founder for the logs.
$user->data['user_id'] = 2; //
$user->data['username'] = $qi_config['admin_name'];
$user->data['user_colour'] = 'AA0000';

//var_dump($user->lang);

if (!class_exists('umil_frontend'))
{
    if (!file_exists($phpbb_root_path . 'umil/umil_frontend.' . $phpEx))
	{
		trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
	}

	include($phpbb_root_path . 'umil/umil_frontend.' . $phpEx);
}

$force_display_results = false;
$umil = new umil_frontend($mod_name);

// We will sort the actions to prevent issues from mod authors incorrectly listing the version numbers
uksort($versions, 'version_compare');

// Find the current version to install
$current_version = '0.0.0';
foreach ($versions as $version => $actions)
{
	$current_version = $version;
}

//$template->assign_var('L_TITLE_EXPLAIN', ((isset($user->lang[$mod_name . '_EXPLAIN'])) ? $user->lang[$mod_name . '_EXPLAIN'] . '<br /><br />' : '') . sprintf($user->lang['VERSIONS'], $current_version, ((isset($config[$version_config_name])) ? $config[$version_config_name] : $user->lang['NONE'])));

$submit = true;
$action = 'install';
$version_select = '1.0.0';

$umil->run_actions($action, $versions, $version_config_name, $version_select);

function umil_install_update_uninstall_select($value, $key)
{
	global $config, $current_version, $user, $version_config_name;

	$db_version = (isset($config[$version_config_name])) ? $config[$version_config_name] : false;

	if ($db_version === false)
	{
		return '<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="install" checked="checked" /> ' . $user->lang['INSTALL'] . '&nbsp;&nbsp;
		<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="update" disabled="disabled" /> ' . $user->lang['UPDATE'] . '&nbsp;&nbsp;
		<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="uninstall" disabled="disabled" /> ' . $user->lang['UNINSTALL'];
	}
	else if ($current_version == $db_version)
	{
		return '<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="install" disabled="disabled" /> ' . $user->lang['INSTALL'] . '&nbsp;&nbsp;
		<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="update" disabled="disabled" /> ' . $user->lang['UPDATE'] . '&nbsp;&nbsp;
		<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="uninstall" checked="checked" /> ' . $user->lang['UNINSTALL'];
	}
	else if (version_compare($current_version, $db_version, '>'))
	{
		return '<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="install" disabled="disabled" /> ' . $user->lang['INSTALL'] . '&nbsp;&nbsp;
		<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="update" checked="checked" /> ' . $user->lang['UPDATE'] . '&nbsp;&nbsp;
		<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="uninstall" /> ' . $user->lang['UNINSTALL'];
	}
	else
	{
		// Shouldn't ever get here...but just in case.
		return '<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="install" /> ' . $user->lang['INSTALL'] . '&nbsp;&nbsp;
		<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="update" /> ' . $user->lang['UPDATE'] . '&nbsp;&nbsp;
		<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="uninstall" /> ' . $user->lang['UNINSTALL'];
	}
}

function umil_version_select($value, $key)
{
	global $user, $versions;

	$output = '<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="" checked="checked" /> ' . $user->lang['IGNORE'] . ' &nbsp; ';
	$output .='<a href="#" onclick="if (document.getElementById(\'version_select_advanced\').style.display == \'none\') {document.getElementById(\'version_select_advanced\').style.display=\'block\'} else {document.getElementById(\'version_select_advanced\').style.display=\'none\'}">' . $user->lang['ADVANCED'] . '</a><br /><br />';

	$cnt = 0;
	$output .= '<table id="version_select_advanced" style="display: none;" cellspacing="0" cellpadding="0"><tr>';

	foreach ($versions as $version => $actions)
	{
		$cnt++;

		$output .= '<td><input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="' . $version . '" /> ' . $version . '</td>';

		if ($cnt % 4 == 0)
		{
			$output .= '</tr><tr>';
		}
	}
	$output .= '</tr></table>';

	return $output;
}
?>