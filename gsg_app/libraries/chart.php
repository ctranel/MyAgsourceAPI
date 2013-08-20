<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Chart File
*
* Author: Chris Tranel
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
	 * get_sample_graph_data
	 *
	 * @param string herd code
	 * @param char cow/heifer code
	 * @return array
	 * @author Chris Tranel
	 **/
	function get_sample_graph_data($herd_code, $cow_heifer){
		$this->ci->load->model('gsg/animal_model');
		return $this->ci->animal_model->get_sample_graph_data($herd_code, $cow_heifer);
	}
}