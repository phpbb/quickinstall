<?php
/**
*
* @package quickinstall
* @copyright (c) 2007 phpBB Group
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

/**
 * Our extension to install_install
 */
class install_automod_qi extends install_automod
{
	protected $data = array();

	public function set_data($data)
	{
		$this->data = $data;
	}

	public function get_submitted_data()
	{
		return $this->data;
	}
}
