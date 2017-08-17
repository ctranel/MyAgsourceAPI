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
	 * herd datasource
	 *
	 * @var datasource
	 **/
	protected $datasource;

	/**
	 * herd identifier
	 *
	 * @var string
	 **/
	protected $herd_code;

    /**
     * herd owner
     *
     * @var string
     **/
    protected $herd_owner;

    /**
	 * code used to authorize release of herd information
	 *
	 * @var string
	 **/
	protected $herd_release_code;

    /**
     * farm_name
     *
     * @var string
     **/
    protected $farm_name;

    /**
     * breed code
     *
     * @var string
     **/
    protected $breed_code;

    /**
     * contact first name
     *
     * @var string
     **/
    protected $contact_first_name;

    /**
     * contact last name
     *
     * @var string
     **/
    protected $contact_last_name;

    /**
     * address line 1
     *
     * @var string
     **/
    protected $address_1;

    /**
     * address line 2
     *
     * @var string
     **/
    protected $address_2;

    /**
     * city
     *
     * @var string
     **/
    protected $city;

    /**
     * state
     *
     * @var string
     **/
    protected $state;

    /**
     * zip
     *
     * @var string
     **/
    protected $zip;

    /**
     * email
     *
     * @var string
     **/
    protected $email;

    /**
	 * supervisor_num
	 *
	 * @var string
	 **/
	protected $supervisor_num;

    /**
     * supervisor_name
     *
     * @var string
     **/
    protected $supervisor_name;

	/**
	 * association_num
	 *
	 * @var string
	 **/
	protected $association_num;

    /**
     * association_name
     *
     * @var string
     **/
    protected $association_name;

    /**
	 * recent_test_date
	 *
	 * @var date string
	 **/
	protected $recent_test_date;

    /**
     *cow_cnt
     *
     * @var int
     **/
    protected $cow_cnt;

    /**
     *milk_cow_cnt
     *
     * @var int
     **/
    protected $milk_cow_cnt;

    /**
     *is_metric
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
	public function __construct(\Herd_model $datasource, $herd_code) {
		if(empty($herd_code) || strlen($herd_code) != 8){
			throw new \Exception('Herd could not be loaded.  No herd code passed to constructor.');
		}
		$this->datasource = $datasource;

		$data = $this->datasource->get_herd($herd_code);
        $this->herd_code = $herd_code;
        $this->farm_name = $data['farm_name'];
        $this->breed_code = $data['breed_code'];
        $this->contact_first_name = $data['contact_fn'];
        $this->contact_last_name = $data['contact_ln'];
        $this->address_1 = $data['address_1'];
        $this->address_2 = $data['address_2'];
        $this->city = $data['city'];
        $this->state = $data['state'];
        $this->zip = $data['zip_5'];
        if(isset($data['zip_4']) && !empty($data['zip_4'])){
            $this->zip .= '-' . $data['zip_4'];
        }
        $this->association_num = $data['association_num'];
        $this->association_name = $data['assoc_name'];
        $this->supervisor_num = $data['supervisor_num'];
        $this->supervisor_name = $data['supervisor_name'];
        $this->herd_release_code = $data['records_release_code'];
        $this->email = $data['email'];
        $this->recent_test_date = $data['recent_test_date'];
        $this->cow_cnt = $data['herd_size'];
        $this->milk_cow_cnt = $data['milk_cow_cnt'];
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
     * @method herdCode()
     * @return string herd_code
     * @access public
     *
     **/
    public function herdOwner(){
        if(!isset($this->herd_owner)){
            $this->herd_owner = $this->datasource->isMetric($this->herd_code);
        }
        return $this->herd_owner;
    }

    /**
	 * @method getRecentTest()
	 * @return string recent test date
	 * @access public
	 *
	 **/
	public function getRecentTest(){
		return $this->recent_test_date;
	}

    /**
     * @method isMetric()
     * @return boolean
     * @access public
     *
     **/
    public function isMetric(){
        if(!isset($this->is_metric)){
            $this->is_metric = $this->datasource->isMetric($this->herd_code);
        }
        return (boolean)$this->is_metric;
    }

    /**
     * getDateRangeStart
     *
     * @param string db_table_name - database string for formatting date
     * @param string date_field - db name of the date field used for this trend
     * @param int num_dates - number of test dates to include in report
     * @return string recent test date
     * @access public
     *
     **/
    public function getDateRangeStart($db_table_name, $date_field, $num_dates = 12){
        if(isset($this->datasource)){
            return null;
        }
        return $this->datasource->getStartDate($this->herd_code, $db_table_name, $date_field, $num_dates);
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
		$trials = $this->datasource->getTrialData($this->herd_code, $report_code);
		return $trials;
	}
	
	
	/* -----------------------------------------------------------------
	 *  toArray

	 *  Returns array of general herd information

	 *  @author: ctranel
	 *  @date: May 20, 2014
	 *  @return: array
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	public function toArray() {
		return [
            'herd_code' => $this->herd_code,
            'farm_name' => $this->farm_name,
            'herd_owner' => $this->herd_owner,
            'contact' => $this->contact_first_name . ' ' . $this->contact_last_name,
            'breed_code' => $this->breed_code,
            'state' => $this->state,
            'assoc_name' => $this->association_name,
            'supervisor_name' => $this->supervisor_name,
            'recent_test_date' => $this->recent_test_date,
            'cow_cnt' => $this->cow_cnt,
            'milk_cow_cnt' => $this->milk_cow_cnt,
            'email' => $this->email,
        ];
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
		$cows = $this->datasource->getCowList($this->herd_code, $value_field, $show_heifers, $show_bulls, $show_sold);
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
        $events = $this->datasource->getEventMap($this->herd_code);
        $return = [];
        foreach($events as $e){
            $return[] = [(int)$e['event_cd'] => $e['event_cat']];
        }

        return $return;
    }
}
