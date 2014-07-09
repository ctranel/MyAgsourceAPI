<?php
namespace myagsource;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Access Log
*
* Author: ctranel
*  
* Created:  4-9-2014
*
* Description:  Record and retrieve data related to site access
*
* Requirements: PHP5 or above
*
*/

class Access_log
{
	/**
	 * model object for access logs
	 * @var object
	 **/
	protected $access_log_model;

	/**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct($access_log_model){
		$this->access_log_model = $access_log_model;
	}

	/**
	 * write_entry
	 *
	 * @param int is_admin
	 * @param int event id
	 * @param string herd code
	 * @param string most recent test date for herd
	 * @param int herd enrollment status (id represents none, paid or trial)
	 * @param int user_id
	 * @param int group_id
	 * @param string format (web, pdf or csv) defaults to web
	 * @param string sort order (NULL, ASC or DESC) defaults to NULL
	 * @param string filter text, defaults to NULL
	 * @return boolean
	 * @author ctranel
	 **/
	function write_entry($is_admin, $event_id, $herd_code, $recent_test_date, $herd_enroll_status_id, $user_id, $group_id, $format='web', $report_page_id = NULL, $sort=NULL, $filters=NULL){
		if($is_admin) return 1; //do not record admin action
		$tmp_array = array(
			'event_id' => $event_id,
			'herd_code' => $herd_code,
			'recent_test_date' => $recent_test_date,
			'herd_enroll_status_id' => $herd_enroll_status_id,
			'user_id' => $user_id,
			'group_id' => $group_id,
			'format' => $format,
			'report_page_id' => $report_page_id,
			'access_time' => date('Y-m-d H:i:s')
		);
		if ($report_page_id) $tmp_array['report_page_id'] = $report_page_id;
		if ($sort) $tmp_array['sort_text'] = $sort;
		if ($filters) $tmp_array['filter_text'] = $filters;
		return $this->access_log_model->write_entry_to_db($tmp_array);
	}

	/**
	 * user has accessed herd in test period
	 *
	 * @method sgHasAccessedTest
	 * @param int user id (uses this to look up service group)
	 * @param string herd code
	 * @param string test date (defaults to null)
	 * @return boolean
	 * @author ctranel
	 **/
	function sgHasAccessedTest($user_id, $herd_code, $test_date = NULL){
		$log = $this->access_log_model->getLogBySgHerdTest($user_id, $herd_code, $test_date);
		return count($log) > 0;
	}
	
	/* -----------------------------------------------------------------
	 * returns the first date that the given user accessed the given report/product
	
	*  returns the first date that the given user accessed the given report/product
	
	*  @since: version 1
	*  @author: ctranel
	*  @date: Jul 7, 2014
	*  @param: int user id
	*  @param: string herd code
	*  @param: string report code
	*  @return date
	*  @throws:
	* -----------------------------------------------------------------
	*/
	public function getInitialAccessDate($user_id, $herd_code, $report_code){
		return $this->access_log_model->getInitialAccessDate($user_id, $herd_code, $report_code);
	}
}