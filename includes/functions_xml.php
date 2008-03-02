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
 * Code by geoffers@gmail.com
 * From: http://php.net/xml
 */
class simple_parser
{
	var $parser;
	var $error_code;
	var $error_string;
	var $current_line;
	var $current_column;
	var $data	= array();
	var $datas	= array();

	function parse($data)
	{
		$this->parser = xml_parser_create('UTF-8');
		xml_set_object($this->parser, $this);
		xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, 1);
		xml_set_element_handler($this->parser, 'tag_open', 'tag_close');
		xml_set_character_data_handler($this->parser, 'cdata');
		if (!xml_parse($this->parser, $data))
		{
			$this->data = array();
			$this->error_code = xml_get_error_code($this->parser);
			$this->error_string = xml_error_string($this->error_code);
			$this->current_line = xml_get_current_line_number($this->parser);
			$this->current_column = xml_get_current_column_number($this->parser);
		}
		else
		{
			$this->data = $this->data['child'];
		}
		xml_parser_free($this->parser);
	}

	function tag_open($parser, $tag, $attribs)
	{
		// added by eviL3 because big tags suck <3
		$tag = strtolower($tag);
		$this->data['child'][$tag][] = array('data' => '', 'attribs' => $attribs, 'child' => array());
		$this->datas[] =& $this->data;
		$this->data =& $this->data['child'][$tag][count($this->data['child'][$tag])-1];
	}

	function cdata($parser, $cdata)
	{
		$this->data['data'] .= $cdata;
	}

	function tag_close($parser, $tag)
	{
		$this->data =& $this->datas[count($this->datas)-1];
		array_pop($this->datas);
	}
}

?>