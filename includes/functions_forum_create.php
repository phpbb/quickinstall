<?php
/**
*
* @package quickinstall
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
 * Need it here since we don't want to include adm/index.php
 */

/**
* @ignore
*/
if (!defined('IN_QUICKINSTALL'))
{
	exit;
}

/**
 * Checks whatever or not a variable is OK for use in the Database
 *
 * Copied from adm/index.php, since we do not want to include that file.
 *
 * param mixed $value_ary An array of the form array(array('lang' => ..., 'value' => ..., 'column_type' =>))'
 * param mixed $error The error array
 */
function validate_range($value_ary, &$error)
{
	global $user;

	$column_types = array(
		'BOOL'	=> array('php_type' => 'int', 		'min' => 0, 				'max' => 1),
		'USINT'	=> array('php_type' => 'int',		'min' => 0, 				'max' => 65535),
		'UINT'	=> array('php_type' => 'int', 		'min' => 0, 				'max' => (int) 0x7fffffff),
		'INT'	=> array('php_type' => 'int', 		'min' => (int) 0x80000000, 	'max' => (int) 0x7fffffff),
		'TINT'	=> array('php_type' => 'int',		'min' => -128,				'max' => 127),

		'VCHAR'	=> array('php_type' => 'string', 	'min' => 0, 				'max' => 255),
	);
	foreach ($value_ary as $value)
	{
		$column = explode(':', $value['column_type']);
		$max = $min = 0;
		$type = 0;
		if (!isset($column_types[$column[0]]))
		{
			continue;
		}
		else
		{
			$type = $column_types[$column[0]];
		}

		switch ($type['php_type'])
		{
			case 'string' :
				$max = (isset($column[1])) ? min($column[1],$type['max']) : $type['max'];
				if (strlen($value['value']) > $max)
				{
					$error[] = sprintf($user->lang['SETTING_TOO_LONG'], $user->lang[$value['lang']], $max);
				}
			break;

			case 'int':
				$min = (isset($column[1])) ? max($column[1],$type['min']) : $type['min'];
				$max = (isset($column[2])) ? min($column[2],$type['max']) : $type['max'];
				if ($value['value'] < $min)
				{
					$error[] = sprintf($user->lang['SETTING_TOO_LOW'], $user->lang[$value['lang']], $min);
				}
				else if ($value['value'] > $max)
				{
					$error[] = sprintf($user->lang['SETTING_TOO_BIG'], $user->lang[$value['lang']], $max);
				}
			break;
		}
	}
}
