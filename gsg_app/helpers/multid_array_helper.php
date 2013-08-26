<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * multid_array_sort
 *
 * Lets you determine whether an array index is set and whether it has a value.
 * If the element is empty it returns FALSE (or whatever you specify as the default value.)
 *
 * @access	public
 * @param	array data
 * @param	string key on which to sort
 * @param	boolean maintain empty rows
 * @param	boolean maintain key
 * @param	constant sort order
 * @return	array
 */
if ( ! function_exists('multid_array_sort')) {
	function multid_array_sort($array, $on, $keep_blank = TRUE, $maintain_key=FALSE, $order=SORT_ASC) {
		$new_array = array();
		$sortable_array = array();

		if (is_array($array) && count($array) > 0) {
			foreach ($array as $k => $v) {
				if (is_array($v)) {
					foreach ($v as $k2 => $v2) {
						if ($k2 == $on) {
							$sortable_array[$k] = $v2;
						}
					}
				} else {
					$sortable_array[$k] = $v;
				}
			}

			switch ($order) {
				case SORT_ASC:
					asort($sortable_array);
					break;
				case SORT_DESC:
					arsort($sortable_array);
					break;
			}

			foreach ($sortable_array as $k => $v) {
				if($v != '' || $keep_blank){
					if($maintain_key) $new_array[$k] = $array[$k];
					else $new_array[] = $array[$k];
				} 
			}
		}

		return $new_array;
	}
}

if ( ! function_exists('multid_array_filter')) {
	/**
	 * @method multid_array_filter()
	 * @param array input
	 * @param string callback for filter
	 * @return array
	 * @author Chris Tranel
	 **/
	function multid_array_filter($input, $callback = null)
	{
		foreach ($input as &$value)
		{
			if (is_array($value))
			{
				$value = multid_array_filter($value, $callback);
			}
		}

		return array_filter($input, $callback);
	}
}

if(! function_exists('multid_remove_element')){
	function multid_remove_element( $arr_in, $key_in ) {
 		$newArr = array();
	    foreach( $arr_in as $k => $v ) {
	        if(is_array($v) && $k != $key_in) $newArr[ $k ] = multid_remove_element( $v, $key_in );
	    	elseif($k != $key_in) $newArr[ $k ] = $v;
	    }
	         
	    return $newArr;
	 }
}


if ( ! function_exists('array_flatten')) {
	/**
	 * Flattening a multi-dimensional array into a
	 * single-dimensional one. The resulting keys are a
	 * string-separated list of the original keys:
	 *
	 * @param array array to flatten
	 * @return flattened array
	 **/
	function array_flatten($arr_in){
		$arr_return = array(); // initialize so that it can be passed by reference
		array_walk_recursive(
			$arr_in,
			create_function('$val, $key, $obj', 'array_push($obj["output"], $val);'),
			array('output' => &$arr_return)
		);
		return $arr_return;
	}
}
/**
if ( ! function_exists('array_flatten_sep')) {

	 * Flattening a multi-dimensional array into a
	 * single-dimensional one. The resulting keys are a
	 * string-separated list of the original keys:
	 *
	 * a[x][y][z] becomes a[implode(sep, array(x,y,z))]
	 * @param string separator
	 * @param array array to flatten
	 * @return flattened array

	function array_flatten_sep($sep, $array) {
		$result = array();
		$stack = array();
		array_push($stack, array("", $array));

		while (count($stack) > 0) {
			list($prefix, $array) = array_pop($stack);

			foreach ($array as $key => $value) {
				$new_key = $prefix . strval($key);

				if (is_array($value))
				array_push($stack, array($new_key . $sep, $value));
				else
				$result[$new_key] = $value;
			}
		}
		return $result;
	}
}
**/

if ( ! function_exists('array_depth')) {
	/**
	 * Find maximum depth of an array
	 * Usage: int ArrayDepth( array $array, [int $DepthCount] )
	 * @return integer with max depth, if Array is a string or an empty array it will return 0
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

if ( ! function_exists('array_extract_value_recursive')) {
	/**
	 * Get all values from specific key (or '*' for all keys) in a multidimensional array and return them in a numerically indexed 1-D array
	 *
	 * @param $key string ("*" to extract all data into a )
	 * @param $arr array
	 * @return null|string|array
	 */
	function array_extract_value_recursive($key_in, array $arr_in){
		$new_val = array();
	    array_walk_recursive($arr_in, create_function('$val, $key, $obj', 'if($key == key($obj) || key($obj) == "*") array_push($obj[key($obj)], $val);'), array($key_in => &$new_val));
	    return $new_val;
	}
}

if ( ! function_exists('array_map_recursive')) {
	/**
	 * Get all values from specific key (or '*' for all keys) in a multidimensional array and return them in a numerically indexed 1-D array
	 *
	 * @param callback function
	 * @param $arr array
	 * @return array of values after callback has been applied to each
	 */
	function array_map_recursive($func, $arr) {
	     $newArr = array();
	     foreach( $arr as $key => $value ) {
	         $newArr[ $key ] = ( is_array( $value ) ? array_map_recursive( $func, $value ) : $func( $value ) );
	     }
	     return $newArr;
	}
}

if ( ! function_exists('array_merge_distinct')) {
	/**
	 * @abstract merges 2 arrays and removes duplicates
	 *
	 * @param callback function
	 * @param $arr_1 array
	 * @param $arr_2 array
	 * @return array
	 */
 
	function array_merge_distinct($arr_1, $arr_2, $sort_field = FALSE){
		if(!is_array($arr_1) && !is_array($arr_2)) return FALSE;
		elseif(!is_array($arr_1) || empty($arr_1)) return $arr_2;
		elseif(!is_array($arr_2) || empty($arr_2)) return $arr_1;
		
		$arr_dups = array_uintersect($arr_1, $arr_2, 'compare_arrays');
		if(isset($arr_dups) && is_array($arr_dups)){
			foreach($arr_dups as $k=>$v){
				unset($arr_1[$k]);
			}
		}
		$tmp = array_merge($arr_1, $arr_2);
		if($sort_field) $tmp = multid_array_sort($tmp, $sort_field);
		return $tmp;
	}
	
}

if ( ! function_exists('get_element_by_key')) {
	/**
	 * @abstract Returns first match if $key in mulitdimensional array
	 *
	 * @param key
	 * @param array
	 * @return mixed
	 */
 
	function get_element_by_key($key, $array){
		foreach($array as $k => $v){
			if($k == $key){
				return $v;
			} 
			if(is_array($v)) {
				$arr_ret = get_element_by_key($key, $v);
				if(isset($arr_ret)) return $arr_ret;
			}
		}
	}	
}

if ( ! function_exists('set_element_by_key')) {
	/**
	 * @abstract Sets value for first match if $key in mulitdimensional array
	 *
	 * @param array into which value will be inserted
	 * @param key
	 * @param mixed value to be inserted
	 * @return void
	 */
    function set_element_by_key(&$input, $key_in, $new_val_in, $arr_order = NULL){
        if (!is_array($input)){
            return false;
        }
        $cnt = 0;
        $arr_len = count($input) - 1;
//var_dump($input);
		$arr_copy = $input;
        foreach ($arr_copy AS $key => $value){
           if (is_array($input[$key])){
           	if (!empty($new_val_in) && !empty($key_in)){
                    if($key == $key_in){
                    	if(is_array($input[$key])){
                    		if(isset($arr_order) && is_array($arr_order) && $arr_order[key($input[$key])] > key($new_val_in)){
								$input[$key] = array_merge($new_val_in, $input[$key]);
                    		}
                    		else $input[$key] = array_merge($input[$key], $new_val_in);
                    	}
                    	else {
	                    	$value = $new_val_in;
                    	}
                    }
                }
                set_element_by_key($input[$key], $key_in, $new_val_in, $arr_order);
            }
            else{
//echo $key . ' -- ';
//var_dump($new_val_in);
            	$saved_value = $value;
            	if (!empty($new_val_in)){
	                if (!empty($key_in)){
	                    if($key == $key_in) $value = $new_val_in;
	                }
	                elseif (is_array($input)){
				    //root level $input does not have a key, and cannot have list order.  if key_in is empty, traverse array and insert in appropriate slot
	//echo "\n\n\n\n" . key($new_val_in) . ' - ' . $arr_order[key($new_val_in)] . ' == ' . ($arr_order[$key] - 1) . " - $cnt \n\n";
	                	if(isset($arr_order) && is_array($arr_order) && $arr_order[key($new_val_in)] == ($arr_order[$key] - 1)){
	                		array_insert($input, $cnt, $new_val_in);
	                    }
	                    elseif($arr_order[key($new_val_in)] == count($arr_order) && $arr_order[key($new_val_in)] == $arr_order[$key]){
//echo "miling times" . count($arr_order);
//var_dump($new_val_in);
	                    	$input[$key] = $new_val_in[$key];
//var_dump($input);
	                    }
	                }
	                if ($value != $saved_value){
	                    $input[$key] = $value;
	                }
            	}
            }
            $cnt++;
        }
        return true;
    }
}

function array_insert (&$array, $position, $insert_array) { 
  $first_array = array_splice ($array, 0, $position); 
//var_dump($first_array);
//var_dump($insert_array);
//var_dump($array);
  
  $array = array_merge ($first_array, $insert_array, $array); 
//var_dump($array);
} 

function compare_arrays($a1, $a2){
	if(!is_array($a1) || !is_array($a2)) return -1;
	return strcasecmp(serialize($a1), serialize($a2));
}
