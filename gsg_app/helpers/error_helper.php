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
 * @return string
 */
if ( ! function_exists('compose_error')) {
	function compose_error($str1, $str2 = NULL, $str3 = NULL, $str4 = NULL){
		$return_val = '';
		//if(!empty($str1) && !empty($tmp_err2)) $return_val = $str1 . $str2;
		if(!empty($str1)) $return_val .= '<p>' . $str1 . '</p>';
		if(!empty($str2)) $return_val .= '<p>' . $str2 . '</p>';
		if(!empty($str3)) $return_val .= '<p>' . $str3 . '</p>';
		if(!empty($str4)) $return_val .= '<p>' . $str4 . '</p>';
		return $return_val;
	}
}
