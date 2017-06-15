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
     *
     *
     * @var boolean
     **/
    protected $is_metric;

    /**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct(\herd_model $herd_model, $herd_code) {
		if(empty($herd_code) || strlen($herd_code) != 8){
			throw new \Exception('Herd could not be loaded.  No herd code passed to constructor.');
		}
		$this->herd_code = $herd_code;
		$this->herd_model = $herd_model;
	}

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
     * @method getRecentTest()
     * @return string recent test date
     * @access public
     *
     **/
    public function isMetric(){
        if(!isset($this->is_metric)){
            $this->is_metric = $this->herd_model->isMetric($this->herd_code);
        }
        return $this->is_metric;
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
	public function getCowOptions($value_field, $show_heifers, $show_bulls, $show_sold) {
		$cows = $this->herd_model->getCowList($this->herd_code, $value_field, $show_heifers, $show_bulls, $show_sold);
		if(!$cows || empty($cows)){
			return false;
		}
		$return = [];
		foreach($cows as $c){
			$return[] = (Object)[$c['serial_num'] => $c[$value_field]];
		}
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
