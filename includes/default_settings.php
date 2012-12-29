<?php
/**
*
* @package quickinstall
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
 * This file contains only one function.
 * Sets and returns default settings if none is found.
 *
 * The settings are mostly dumped from my config file and should be fine as default settings.
 */
function get_default_settings()
{
	$config = array(
		'qi_lang'			=> 'en',
		'dbms'				=> 'mysql',
		'dbhost'			=> 'localhost',
		'dbport'			=> '',
		'dbuser'			=> '',
		'dbpasswd'			=> '',
		'db_prefix'			=> 'qi_',
		'table_prefix'		=> 'phpbb_',
		'cache_dir'			=> 'cache/',
		'boards_dir'		=> 'boards/',
		'boards_url'		=> 'boards/',
		'make_writable'		=> 0,
		'grant_permissions'	=> '',
		'qi_tz'				=> 0,
		'qi_dst'			=> 0,
		'admin_name'		=> 'admin',
		'admin_pass'		=> 'password',
		'admin_email'		=> 'qi_admin@phpbb-quickinstall.tld',
		'server_name'		=> 'localhost',
		'server_port'		=> '80',
		'cookie_domain'		=> '',
		'cookie_secure'		=> 0,
		'board_email'		=> 'qi_board@phpbb-quickinstall.tld',
		'email_enable'		=> 0,
		'smtp_delivery'		=> 0,
		'smtp_host'			=> '',
		'smtp_port'			=> '25',
		'smtp_auth'			=> 'PLAIN',
		'smtp_user'			=> '',
		'smtp_pass'			=> '',
		'site_name'			=> 'Testing Board',
		'site_desc'			=> 'eviLs testing hood',
		'default_lang'		=> 'en',
		'other_config'		=> 'a:2:{i:0;s:21:"session_length;999999";i:1;s:13:"#A comment...";}',
		'chunk_post'		=> '1000',
		'chunk_topic'		=> '2000',
		'chunk_user'		=> '5000',
		'redirect'			=> 1,
		'automod'			=> 1,
		'subsilver'			=> 0,
		'populate'			=> 0,
		'email_domain'		=> 'phpbb-quickinstall.tld',
		'num_users'			=> 100,
		'num_new_group'		=> 10,
		'create_admin'		=> 1,
		'create_mod'		=> 1,
		'num_cats'			=> 2,
		'num_forums'		=> 10,
		'num_topics_min'	=> 5,
		'num_topics_max'	=> 25,
		'num_replies_min'	=> 0,
		'num_replies_max'	=> 50,
		'no_dbpasswd'		=> 0,
	);

	return($config);
}
