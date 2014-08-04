<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// ------------------------------------------------------------------------

/**
 * Form Label Tag
 *
 * @access	public
 * @param	string	The text to appear onscreen
 * @param	string	The id the label applies to
 * @param	string	Additional attributes
 * @param	array	Input attributes - added by ctranel, 2/28/2011
 * @return	string
 */
if ( ! function_exists('form_label'))
{
	function form_label($label_text = '', $id = '', $attributes = array(), $arr_input_attributes = array())
	{
// if block added by ctranel, 2/28/2011
		if(!is_array($arr_input_attributes) && $arr_input_attributes != ''){
			list($k, $v) = explode('=', $arr_input_attributes);
			$arr_input_attributes = array(trim($k)=>trim($v));
		}
		
		if (isset($arr_input_attributes['type']) && $arr_input_attributes['type'] == 'hidden') return FALSE;
		
		$label = '<label';
		
		if ($id != '')
		{
			$label .= " for=\"$id\"";
// if block added by ctranel, 2/28/2011
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

/**
 * Drop-down Menu
 *
 * @access	public
 * @param	string
 * @param	array
 * @param	string
 * @param	string
 * @return	string
 * @version 1.1 ctranel added ' && count($options) > 1' as a condition for including 'multiple' attribute
 */
if ( ! function_exists('form_dropdown'))
{
	function form_dropdown($name = '', $options = array(), $selected = array(), $extra = '')
	{
		if(!is_array($options)){
			return false;
		}
		
		if ( ! is_array($selected))
		{
			$selected = array($selected);
		}

		// If no selected state was submitted we will attempt to set it automatically
		if (count($selected) === 0)
		{
			// If the form name appears in the $_POST array we have a winner!
			if (isset($_POST[$name]))
			{
				$selected = array($_POST[$name]);
			}
		}

		if ($extra != '') $extra = ' '.$extra;

		$multiple = (count($selected) > 1 && count($options) > 1 && strpos($extra, 'multiple') === FALSE) ? ' multiple="multiple"' : '';

		$form = '<select name="'.$name.'"'.$extra.$multiple.">\n";

		foreach ($options as $key => $val)
		{
			$key = (string) $key;

			if (is_array($val) && ! empty($val))
			{
				$form .= '<optgroup label="'.$key.'">'."\n";

				foreach ($val as $optgroup_key => $optgroup_val)
				{
					$sel = (in_array($optgroup_key, $selected)) ? ' selected="selected"' : '';

					$form .= '<option value="'.$optgroup_key.'"'.$sel.'>'.(string) $optgroup_val."</option>\n";
				}

				$form .= '</optgroup>'."\n";
			}
			else
			{
				$sel = (in_array($key, $selected)) ? ' selected="selected"' : '';

				$form .= '<option value="'.$key.'"'.$sel.'>'.(string) $val."</option>\n";
			}
		}

		$form .= '</select>';

		return $form;
	}
}



