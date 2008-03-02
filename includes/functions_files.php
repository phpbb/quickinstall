<?php
/** 
*
* @package quickinstall
* @version $Id$
* @copyright (c) 2007 eviL3
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
 * Useful class for directory and file actions
 * Can't use self because that's php5 only
 */
class file_functions
{
	function delete_file($file)
	{
		return unlink($file);
	}
	
	function copy_file($src_file, $dst_file)
	{
		return copy($src_file, $dst_file);
	}
	
	function move_file($src_file, $dst_file)
	{
		file_functions::copy_file($src_file, $dst_file);
		file_functions::delete_file($src_file);
	}
	
	function copy_dir($src_dir, $dst_dir)
	{
		file_functions::dir_slash($src_dir);
		file_functions::dir_slash($dst_dir);
		
		if (!is_dir($dst_dir))
		{
			mkdir($dst_dir);
		}
		
		$d = dir($src_dir);
		while (false !== ($file = $d->read()))
		{
			if (in_array($file, array('.', '..')))
			{
				continue;
			}
			
			$src_file = $src_dir . $file;
			$dst_file = $dst_dir . $file;
			
			if (is_file($src_file))
			{
				if (is_file($dst_file))
				{
					$ow = filemtime($src_file) - filemtime($dst_file);
				}
				else
				{
					$ow = 1;
				}
				
				if ($ow > 0)
				{
					if (copy($src_file, $dst_file))
					{
						touch($dst_file, filemtime($src_file));
					}
				}
			}
			else if (is_dir($src_file))
			{
				file_functions::copy_dir($src_file, $dst_file);
			}
		}
		$d->close();
	}
	
	function delete_dir($dir, $empty = false)
	{
		file_functions::dir_slash($dir);
		
		if (!file_exists($dir) || !is_dir($dir) || !is_readable($dir))
		{
			return false;
		}
		
		$d = dir($dir);
		while (false !== ($file = $d->read()))
		{
			if (in_array($file, array('.', '..')))
			{
				continue;
			}
			
			if (is_dir($dir . $file)) 
			{
				file_functions::delete_dir($dir . $file);
			}
			else
			{
				file_functions::delete_file($dir . $file);
			}
		}
		$d->close();
		
		if (!$empty)
		{
			@rmdir($dir);
		}
	}
	
	function delete_files($dir, $files_ary, $recursive = true)
	{
		file_functions::dir_slash($dir);
		
		$d = dir($dir);
		while (false !== ($file = $d->read()))
		{
			if (in_array($file, array('.', '..')))
			{
				continue;
			}
			
			if (is_dir($dir . $file))
			{
				if ($recursive)
				{
					file_functions::delete_files($dir . $file, $files_ary, true);
				}
			}
			
			if (in_array($file, $files_ary))
			{
				if (is_dir($dir . $file))
				{
					file_functions::delete_dir($dir . $file);
				}
				else
				{
					file_functions::delete_file($dir . $file);
				}
			}
		}
		$d->close();
	}
	
	function dir_slash(&$dir)
	{
		if ($dir[strlen($dir) - 1] != '/')
		{
			$dir .= '/';
		}
	}
}

?>