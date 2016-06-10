<?php
namespace myagsource\dhi;

use myagsource\dhi\Herd;
use myagsource\Site\iPage;


/**
* Name:  HerdAccess
*
* Author: ctranel
*  
* Created:  12-12-2014
*
* Description:  Provides information about a herd's accessible pages (AKA reports).
*
* Requirements: PHP5 or above
*/

class HerdPageAccess
{
	/**
	 * datasource_pages
	 * @var page_model
	 **/
	protected $datasource_pages;

	/**
	 * herd
	 * @var Herd
	 **/
	protected $herd;

	/**
	 * page
	 * @var Page
	 **/
	protected $page;

	/**
	 * reports
	 * @var 2-d array of report data
	 **/
	protected $reports;

	/**
	 * herd_is_paying
	 * @var boolean
	 **/
	protected $herd_is_paying;

	/**
	 * herd_is_active_trial
	 * @var boolean
	 **/
	protected $herd_is_active_trial;

	/**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct($datasource_pages, Herd $herd, iPage $page) {
		$this->datasource_pages = $datasource_pages;
		$this->herd = $herd;
		$this->page = $page;

		$report_data = $this->datasource_pages->getAccessibleReports($this->page->id(), $this->herd->herdCode());

		if(is_array($report_data)){
            $this->reports = $report_data;
            //if any qualifying reports
            $this->herd_is_paying = in_array(1, array_column($report_data, 'herd_is_paying'));
            $this->herd_is_active_trial = in_array(1, array_column($report_data, 'herd_is_active_trial'));
        }
	}

	/**
	 * @method reportCodes()
	 * @return array of strings
	 * @access public
	 **/
	public function reportCodes(){
		return array_column($this->reports, 'report_code');
	}

    /**
     * @method reports()
     * @return 2-d array of report data
     * @access public
     **/
    public function reports(){
        return $this->reports;
    }

    /**
	 * @method hasAccess()
	 * @return boolean
	 * @access public
	 **/
	public function hasAccess(){
		if($this->page->scope() === 'base'){
			return true;
		}
		if($this->page->scope() === 'subscription' && ($this->herd_is_paying || $this->herd_is_active_trial)){
			return true;
		}
		return false;
	}
}
