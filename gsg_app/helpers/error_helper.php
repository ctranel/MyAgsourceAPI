<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// ------------------------------------------------------------------------

/**
 * Combine various session vars and functions into one error message
 *
 * compose_error
 *
 * @access public
 * @param string
 * @param string
 * @param string
 * @param string
 * @return string
 */
if ( ! function_exists('compose_error')) {
	function compose_error($str1, $arr2 = [], $str3 = NULL, $str4 = NULL){
		$str2 = '';
        if(is_array($arr2)){
            $str2 = implode('</div><div>', $arr2);
        }
        
		$return_val = '';
		//if(!empty($str1) && !empty($tmp_err2)) $return_val = $str1 . $str2;
		if(!empty($str1)) $return_val .= '<div>' . $str1 . '</div>';
		if(!empty($str2)) $return_val .= '<div>' . $str2 . '</div>';
		if(!empty($str3)) $return_val .= '<div>' . $str3 . '</div>';
		if(!empty($str4)) $return_val .= '<div>' . $str4 . '</div>';
		return $return_val;
	}
}
