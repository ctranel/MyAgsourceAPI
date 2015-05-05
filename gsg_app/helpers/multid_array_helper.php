<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('array_search_recursive')) {
	/**
	 * @method array_search_recursive()
	 * @param string needle
	 * @param array haystack
	 * @return mixed key
	 * @author ctranel
	 **/

	function array_search_recursive($needle,$haystack) {
		foreach($haystack as $key=>$val) {
			$current_key=$key;
			if($needle === $val || (is_array($val) && array_search_recursive($needle,$val) !== false)) {
				return $current_key;
			}
		}
		return false;
	}
}


if ( ! function_exists('array_flatten')) {
	/**
	 * Flattening a multi-dimensional array into a
	 * single-dimensional one. The resulting keys are a
	 * string-separated list of the original keys:  <- found to be false... see array_flatten_wkeys
	 *
	 * @param array array to flatten
	 * @return flattened array
	 * @author ctranel
	 **/
	function array_flatten($arr_in){
		$arr_return = []; // initialize so that it can be passed by reference
		array_walk_recursive(
			$arr_in,
			create_function('$val, $key, $obj', 'array_push($obj["output"], $val);'),
			['output' => &$arr_return]
		);
		return $arr_return;
	}
}

if ( ! function_exists('array_depth')) {
	/**
	 * Find maximum depth of an array
	 * Usage: int ArrayDepth( array $array, [int $DepthCount] )
	 * @return integer with max depth, if Array is a string or an empty array it will return 0
	 * @author ctranel
	 * 
	 **/

	function array_depth($Array,$DepthCount=-1) {
		$DepthArray=array(0);
		$DepthCount++;
		$Depth = 0;
		if (is_array($Array))
		foreach ($Array as $Key => $Value) {
			$DepthArray[]=array_depth($Value,$DepthCount);
		}
		else
		return $DepthCount;
		return max($DepthCount,max($DepthArray));
	}
}

if ( ! function_exists('get_elements_by_key')) {
	/**
	 * @description Recursively returns all matches of $key in mulitdimensional $array, returns array with all values for that key
	 *
	 * @param key
	 * @param array
	 * @return array
	 * @author ctranel
	 */
 
	function get_elements_by_key($key, $array, &$ret_val = array()){
		foreach($array as $k => $v){
			if($k === $key){
				$ret_val[] = $v;
			} 
			if(is_array($v)) {
				get_elements_by_key($key, $v, $ret_val);
			}
		}
		return $ret_val;
	}	
}

if ( ! function_exists('array_extract_value_recursive')) {
	/**
	 * Get all values from specific key (or '*' for all keys) in a multidimensional array and return them in a numerically indexed 1-D array
	 *
	 * @param $key string ("*" to extract all data into a )
	 * @param $arr array
	 * @return null|string|array
	 * @author ctranel
	 */
	function array_extract_value_recursive($key_in, array $arr_in){
		$new_val = array();
	    array_walk_recursive($arr_in, create_function('$val, $key, $obj', 'if($key == key($obj) || key($obj) == "*") array_push($obj[key($obj)], $val);'), array($key_in => &$new_val));
	    return $new_val;
	}
}