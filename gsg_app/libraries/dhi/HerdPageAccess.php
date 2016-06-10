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
* Description:  Provides information about a herd's access to pages (AKA reports).
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
	 * report_code
	 * @var string
	 **/
	protected $report_code;

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
		
		$report_data = $this->datasource_pages->getReport($this->page->id(), $this->herd->herdCode());
		$this->report_code = $report_data['report_code'];
		$this->herd_is_paying = $report_data['herd_is_paying'];
		$this->herd_is_active_trial = $report_data['herd_is_active_trial'];
	}

	/**
	 * @method reportCode()
	 * @return boolean
	 * @access public
	 **/
	public function reportCode(){
		return $this->report_code;
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
