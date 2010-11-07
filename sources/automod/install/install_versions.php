<?php
/**
*
* @package automod
* @version $Id$
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
*
*/

/*
* The array of versions and actions within each.
* You do not need to order it a specific way (it will be sorted automatically), however, you must enter every version, even if no actions are done for it.
*
* You must use correct version numbering.  Unless you know exactly what you can use, only use X.X.X (replacing X with an integer).
* The version numbering must otherwise be compatible with the version_compare function - http://php.net/manual/en/function.version-compare.php
*/

$cat_module_data = array(
	'module_enabled'	=> 1,
	'module_display'	=> 1,
	'module_basename'	=> '',
	'module_langname'	=> '',
	'module_mode'		=> '',
	//'module_auth'		=> 'a_mods',
	'module_langname'	=> 'ACP_CAT_MODS',
	'module_auth'		=> 'acl_a_mods',
);

// Insert Parent Module
$parent_module_data = array(
	'module_enabled'	=> 1,
	'module_display'	=> 1,
	'module_langname'	=> 'ACP_MODS',
	'module_auth'		=> 'acl_a_mods',
);

// Frontend Module
$front_module_data = array(
	'module_enabled'	=> 1,
	'module_display'	=> 1,
	'module_langname'	=> 'ACP_AUTOMOD',

	'module_basename'	=> 'mods',
	'module_mode'		=> 'frontend',
	'module_auth'		=> 'acl_a_mods',
);

// Config Module
$config_module_data = array(
	'module_enabled'	=> 1,
	'module_display'	=> 1,
	'module_langname'	=> 'ACP_AUTOMOD_CONFIG',

	'module_basename'	=> 'mods',
	'module_mode'		=> 'config',
	'module_auth'		=> 'acl_a_mods',
);

$schema_data = array(
	'COLUMNS'		=> array(
		'mod_id'				=> array('UINT', NULL, 'auto_increment'),
		'mod_active'			=> array('BOOL', 0),
		'mod_time'				=> array('TIMESTAMP', 0),
		'mod_dependencies'		=> array('MTEXT_UNI', ''),
		'mod_name'				=> array('XSTEXT_UNI', ''),
		'mod_description'		=> array('TEXT_UNI', ''),
		'mod_version'			=> array('VCHAR:25', ''),
		'mod_author_notes'		=> array('TEXT_UNI', ''),
		'mod_author_name'		=> array('XSTEXT_UNI', ''),
		'mod_author_email'		=> array('XSTEXT_UNI', ''),
		'mod_author_url'		=> array('XSTEXT_UNI', ''),
		'mod_actions'			=> array('MTEXT_UNI', ''),
		'mod_languages'			=> array('STEXT_UNI', ''),
		'mod_template'			=> array('STEXT_UNI', ''),
		'mod_path'				=> array('STEXT_UNI', ''),
	),
	'PRIMARY_KEY'	=> 'mod_id',
);

$versions = array(

	// Version 1.0.0
	'1.0.0-b1'	=> array(
		// add permission settings
		'permission_add' => array(
			array('a_mods', true),
		),

		'module_add'	=> array(
			array('acp', '', $cat_module_data), // root "AutoMOD" module
			array('acp', 'ACP_CAT_MODS', $parent_module_data), // child "AutoMOD" module
			array('acp', 'ACP_MODS', $front_module_data),
			array('acp', 'ACP_MODS', $config_module_data),
		),

		'table_add'		=> array(
			array('phpbb_mods', $schema_data),
		),

		'config_add'	=> array(
	         array('ftp_method', 0),
	         array('ftp_host', ''),
	         array('ftp_username', ''),
	         array('ftp_root_path', ''),
	         array('ftp_port', '21'),
	         array('ftp_timeout', '60'),
	         array('write_method', WRITE_DIRECT),
	         array('compress_method', '.zip'),
		),
	),

	'1.0.0-b2'	=> array(
		'config_add'	=> array(
			array('preview_changes', true),
			array('am_file_perms', '0644'),
			array('am_dir_perms', '0755'),
		),
	),
	'1.0.0-RC1'	=> array(),
	'1.0.0-RC2' => array(),
	'1.0.0-RC3'	=> array(),
	'1.0.0-RC4' => array(),
	'1.0.0'     => array(),
);

return $versions;

?>
