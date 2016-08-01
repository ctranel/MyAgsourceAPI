<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Chart File
*
* Author: ctranel
*		  ctranel@agsource.com
*

*
* Created:  3.18.2011
*
* Description:  Library for working with charts
*
* Requirements: PHP5 or above
*
*/

class Chart{
	//protected $arr_filters;
	public $ci;
	
	public function __construct(){
		$this->ci =& get_instance();
	}


	/**
	 * formatDataSet
	 *
	 * @param array dataset
	 * @param string name of xaxis field
	 * @return array of data formatted into {axis_field, series_value} pairs (or false on error)
	 * @author ctranel
	 **/
	function formatDataSet($arr_dataset, $xaxis_field){
		if(!isset($arr_dataset) || !is_array($arr_dataset) || !isset($xaxis_field) || empty($xaxis_field)){
			return false;
		}
		$arr_ret = array();
		foreach($arr_dataset as $ds){
			if(!isset($ds[$xaxis_field])){ //no value in the array for the xaxis field
				return false;
			}
			$xaxis_value = $ds[$xaxis_field];
			foreach($ds as $k => $v){
				if($k != $xaxis_field) $arr_ret[$k][] = array($xaxis_value, (float)$v);
			}
		}
		return $arr_ret;
	}
}