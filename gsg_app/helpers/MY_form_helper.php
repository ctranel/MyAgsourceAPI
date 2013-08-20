<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// ------------------------------------------------------------------------

/**
 * Form Label Tag
 *
 * @access	public
 * @param	string	The text to appear onscreen
 * @param	string	The id the label applies to
 * @param	string	Additional attributes
 * @param	array	Input attributes - added by Chris Tranel, 2/28/2011
 * @return	string
 */
if ( ! function_exists('form_label'))
{
	function form_label($label_text = '', $id = '', $attributes = array(), $arr_input_attributes = array())
	{
// if block added by Chris Tranel, 2/28/2011
		if(!is_array($arr_input_attributes) && $arr_input_attributes != ''){
			list($k, $v) = explode('=', $arr_input_attributes);
			$arr_input_attributes = array(trim($k)=>trim($v));
		}
		
		if (isset($arr_input_attributes['type']) && $arr_input_attributes['type'] == 'hidden') return FALSE;
		
		$label = '<label';
		
		if ($id != '')
		{
			$label .= " for=\"$id\"";
// if block added by Chris Tranel, 2/28/2011
			if (isset($arr_input_attributes['class']) && strpos($arr_input_attributes['class'], 'require') !== FALSE){
				if(isset($attributes['class'])){
					$attributes['class'] .= ' require';
				}
				else {
					$attributes['class'] = 'require';
				}
				$label_text = '<span class="require">*</span>' . $label_text;
			}
		}

		if (is_array($attributes) AND count($attributes) > 0)
		{
			foreach ($attributes as $key => $val)
			{
				$label .= ' '.$key.'="'.$val.'"';
			}
		}

		$label .= ">$label_text</label>";

		return $label;
	}
}



