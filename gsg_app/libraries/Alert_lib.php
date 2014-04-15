<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Laboratories Library File
*
* Author: ctranel
*		  ctranel@agsource.com
*

*
* Created:  3.18.2011
*
* Description:  Library for rendering reports for Alerts
*
* Requirements: PHP5 or above
*
*/

require_once APPPATH . 'libraries/Reports.php';

class Alert_lib extends Reports{
	
	public function __construct(){
		$this->ci =& get_instance();
		$this->ci->load->model('dhi/alert_model');
	}

	/**
	 * get_herd_info - retrieves herd data (for use in report headers)
	 * @return array of herd data
	 * @author ctranel
	 **/
	public function get_herd_info($herd_code_in = FALSE){
		if(!$herd_code_in) $herd_code_in = $this->ci->session->userdata('herd_code');
		return $this->ci->alert_model->header_info($herd_code_in);
	}	 
}