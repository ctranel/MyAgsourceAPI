<?php
namespace myagsource\dhi;

use \DateTime;

/**
* Name:  Herd 
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

class Herd
{
	/**
	 * herd model
	 *
	 * @var herd_model
	 **/
	protected $herd_model;

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
	 * 
	 *
	 * @var date string
	 **/
	protected $recent_test_date;

	/**
	 * __construct
	 *
	 * @return void
	 * @author Chris
	 **/
	public function __construct($herd_model, $herd_code) {
		if(empty($herd_code) || strlen($herd_code) != 8){
			return false;
		}
		$this->herd_code = $herd_code;
		$this->herd_model = $herd_model;
	}

	/**
	 * __call
	 *
	 * Acts as a simple way to call model methods without loads of stupid alias'
	 *
	public function __call($method, $arguments) {
		if (!method_exists( $this->ci->herd_model, $method) )
		{
			throw new Exception('Undefined method Herd::' . $method . '() called');
		}

		return call_user_func_array( array($this->ci->herd_model, $method), $arguments);
	}
	 **/
	
	/**
	 * @method herdCode()
	 * @return string herd_code
	 * @access public
	 *
	 **/
	public function herdCode(){
		return $this->herd_code;
	}

	/**
	 * @method getRecentTest()
	 * @return string recent test date
	 * @access public
	 *
	 **/
	public function getRecentTest(){
		if(!isset($this->recent_test_date)){
			$this->recent_test_date = $this->herd_model->get_recent_test($this->herd_code);
		}
		return $this->recent_test_date;
	}

	/**
	 * Returns enrollment status id of the herd for the given report code
	 * 
	 * 	Possible options are:
	 *  (1) no output record = none
	 *  (2) billing account of AS035099 = unpaid
	 *  (3) billing account of 00000001 = paid
	 * 
	 * @method getHerdEnrollStatus()
	 * @return string recent test date
	 * @access public
	 *
	 **/
	public function getHerdEnrollStatus($report_code = NULL){
		if(isset($report_code)){
			if(!is_array($report_code)){
				$report_code = array($report_code);
			}
		}
		$herd_output = $this->herd_model->get_herd_output($this->herd_code, $report_code);
		if(!$herd_output || count($herd_output) == 0){
			$return_val = 1;
		}
		elseif($herd_output[0]['bill_account_num'] == 'AS035099'){
			$return_val = 2;
		}
		else{
			$return_val = 3;
		}
		return $return_val;
	}
	
	/* -----------------------------------------------------------------
	*  Returns number of days since the initial access for herds that
	*  are not paying for the specified product.

	*  Returns number of days since the initial access for herds that
	*  are not paying for the specified product.

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jul 7, 2014
	*  @param: access log object
	*  @param: int user id
	*  @param: string herd code
	*  @param: string report code
	*  @return int 
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function getTrialDays($access_log, $user_id, $herd_code, $report_code){
		if(isset($report_code)){
			if(!is_array($report_code)){
				$report_code = array($report_code);
			}
		}
		$initial_access = $access_log->getInitialAccessDate($user_id, $herd_code, $report_code);
		if(empty($initial_access)){
			return 0;
		}
		$d_start = new DateTime($initial_access);
		$d_end  = new DateTime();
		$d_diff = $d_start->diff($d_end)->days;
		return $d_diff;
	}

	/* -----------------------------------------------------------------
	 *  Returns array of general herd information used in header and other locations

	 *  Returns array of general herd information used in header and other locations

	 *  @since: 1.0
	 *  @author: ctranel
	 *  @date: May 20, 2014
	 *  @param: string herd code
	 *  @return: array
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	public function header_info() {
		return $this->herd_model->header_info($this->herd_code);
	}
}
