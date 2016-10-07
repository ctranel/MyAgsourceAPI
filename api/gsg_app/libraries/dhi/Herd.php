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
	public function __construct(\herd_model $herd_model, $herd_code) {
		if(empty($herd_code) || strlen($herd_code) != 8){
			throw new \Exception('Herd could not be loaded.  No herd code passed to constructor.');
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
	public function getHerdEnrollStatus($report_code){
		$ret = 1;

        if(!isset($report_code)){
			throw new \Exception('No report code given.');
		}
        if(!is_array($report_code)){
            $report_code = [$report_code];
        }

        $herd_output = $this->herd_model->getHerdEnrollmentData($this->herd_code, $report_code);
        if(!$herd_output || count($herd_output) == 0){
            return 1;
        }
		foreach($herd_output as $ho){
			if($ho['herd_is_paying'] === 1){
				if($ret < 3){
                    $ret = 3;
                }
			}
			if($ho['herd_is_active_trial'] === 1){
                if($ret < 2){
                    $ret = 2;
                }
			}
		}
        return $ret;
	}
**/
	
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
	public function getTrialDays($access_log, $herd_code, $report_code){
		if(isset($report_code)){
			if(!is_array($report_code)){
				$report_code = [$report_code];
			}
		}
		$initial_access = $access_log->getInitialAccessDate($herd_code, $report_code);
		if(empty($initial_access)){
			return 0;
		}
		$d_start = new DateTime($initial_access);
		$d_end  = new DateTime();
		$d_diff = $d_start->diff($d_end)->days;
		return $d_diff;
	}
	*/

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
	* -----------------------------------------------------------------*/
	public function getTrialData($report_code = null){
		$trials = $this->herd_model->getTrialData($this->herd_code, $report_code);
		return $trials;
	}
	
	
	/* -----------------------------------------------------------------
	 *  header_info

	 *  Returns array of general herd information used in header and other locations

	 *  @author: ctranel
	 *  @date: May 20, 2014
	 *  @return: array
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	public function header_info() {
		return $this->herd_model->header_info($this->herd_code);
	}

	/* -----------------------------------------------------------------
	 *  getCowOptions

	 *  Returns array of general herd information used in header and other locations

	 *  @author: ctranel
	 *  @date: Sept 15, 2015
	 *  @param string: name of the field to be displayed
	 *  @return: array
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	public function getCowOptions($value_field) {
		$cows = $this->herd_model->getCowList($this->herd_code, $value_field);
		if(!$cows || empty($cows)){
			return false;
		}
		$return = [];
		foreach($cows as $c){
			$return[] = (Object)[$c['serial_num'] => $c[$value_field]];
		}
//		var_dump($return);
		return $return;
	}

    /* -----------------------------------------------------------------
     *  getCowOptions

     *  Returns array of general herd information used in header and other locations

     *  @author: ctranel
     *  @date: Sept 15, 2015
     *  @return: array
     *  @throws:
     * -----------------------------------------------------------------*/
    public function getEventMap() {
        $events = $this->herd_model->getEventMap($this->herd_code);
        $return = [];
        foreach($events as $e){
            $return[] = [(int)$e['event_cd'] => $e['event_cat']];
        }

        return $return;
    }
}
