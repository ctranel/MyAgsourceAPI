<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Herd Manage - 
*
* Author: ctranel
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
