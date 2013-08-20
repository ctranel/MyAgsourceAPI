<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Herd Manage - 
*
* Author: Chris Tranel
*		  ctranel@agsource.com
*
* Location: na
*
* Created:  01.24.2011
*
* Description:  Library for managing herd data
*
* Requirements: PHP5 or above
*
*/

class Herd_manage
{
	/**
	 * CodeIgniter global
	 *
	 * @var string
	 **/
	protected $ci;

	/**
	 * herd identifier
	 *
	 * @var string
	 **/
	protected $herd_code;

	/**
	 * code used to authorize release of herd information
	 *
	 * @var string
	 **/
	protected $herd_release_code;

	/**
	 * 
	 *
	 * @var string
	 **/
	protected $supervisor_num;

	/**
	 * 
	 *
	 * @var string
	 **/
	protected $association_num;

	/**
	 * enum (cow, heifer, all)
	 *
	 * @var string
	 **/
	protected $access_level;

	/**
	 * 
	 *
	 * @var array
	 **/
	public $test_date;

	/**
	 * __construct
	 *
	 * @return void
	 * @author Chris
	 **/
	public function __construct() {
		$this->ci =& get_instance();
		$this->ci->load->model('herd_model');
	}

	/**
	 * __call
	 *
	 * Acts as a simple way to call model methods without loads of stupid alias'
	 *
	 **/
	public function __call($method, $arguments) {
		if (!method_exists( $this->ci->herd_model, $method) )
		{
			throw new Exception('Undefined method Herd::' . $method . '() called');
		}

		return call_user_func_array( array($this->ci->herd_model, $method), $arguments);
	}
	
	/**
	 * @method get_herd_codes_by_tech_num()
	 * @param string tech num
	 * @return array of stdClass with all herds for given tech num
	 * @access public
	 *
	 **/
	public function get_herd_codes_by_tech_num($tech_num){
		$this->ci->herd_model->get_herds_by_tech($tech_num);
	}

	/**
	 * @method get_herds_by_region()
	 * @param string region for which to get herds.  If nothing is passed, it will use the session value
	 * @return array of stdClass with all herds for given region
	 * @access public
	 *
	 **/
	public function get_herds_by_region($region_in = false){
		//$arr_groups = $this->ci->session->userdata('arr_groups');
		$region_id = $region_in ? $region_in : $this->ci->session->userdata('arr_regions');
		
		$arr_manager_groups = array('1'=>'1', '3'=>'3', '4'=>'4', '8'=>'8');
		
		$bool_in_manager_group = in_array($this->ci->session->userdata('active_group_id'), $arr_manager_groups);
		//following 2 lines to be used in place of previous line if all group permissions, not just the active, can be considered
		//$arr_common_groups = array_intersect_key($arr_groups, $arr_manager_groups);
		//$bool_in_manager_group = empty($arr_common_groups)?FALSE:TRUE;
		if($bool_in_manager_group){ //mgr, DSR, admin, RSS
			return $this->ci->herd_model->get_herds_by_region($region_id);
		}
		else if(array_key_exists('5', $arr_groups)){ //"Field Tech"
			return $this->ci->herd_model->get_herds_by_tech_num($this->ci->session->userdata('supervisor_num'));
		}
		else return FALSE;
	}
	
	
	/**
	 * @method get_herds()
	 * @return array of stdClass objects with all herds
	 * @access public
	 *
	 **/
	public function get_herds($limit=NULL, $offset=NULL){
		return $this->ci->herd_model->get_herds($limit, $offset);
	}
	
	/**
	 * @method set_herd_dropdown_array()
	 * @param array of herd data
	 * @return array 1d (key=>value) array of herds for populating options lists
	 * @access public
	 *
	 **/
	public function set_herd_dropdown_array($tmp_array){
		$new_array = array('Select One');
		array_walk($tmp_array, create_function ('$value, $key, $obj', '$obj["arr_in"][$value["herd_code"]] = $value["herd_owner"] . " - " . $value["farm_name"] . " - " . $value["herd_code"];'), array('arr_in' => &$new_array));
		return $new_array;
	}
	
}
