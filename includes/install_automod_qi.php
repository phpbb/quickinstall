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

?>
