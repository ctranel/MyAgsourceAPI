<?php
namespace myagsource;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Filters Library File
*
* Author: ctranel
*		  Compiled and Expanded by Kevin Marshall, refactored by ctranel
*

*
* Created:  20131118
*
* Description:  Library for filter handling
*
* Requirements: PHP5 or above
*
*/

class Filters{
	private $recent_test_date;
	private $arr_filters_list;
	private $criteria;
	private $primary_model;
	private $log_filter_text;
	
	public function __construct(){
	}
	
	/* -----------------------------------------------------------------
	*  Returns filter text

	*  Returns filter text

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 17, 2014
	*  @return: string filter text
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function get_filter_text(){
		if(empty($this->log_filter_text)){
			$this->set_filter_text();
		}
		return $this->log_filter_text;
	}

	/* -----------------------------------------------------------------
	*  displayFilters() returns boolean of whether filters should be displayed

	*  Returns boolean of whether filters should be displayed

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 17, 2014
	*  @param: boolean is page a summary page
	*  @return boolean
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function displayFilters($is_summary){
		$arr_display_filters = array_diff($this->arr_filters_list, array('pstring', 'breed_code'));
		$ret_val = (count($arr_display_filters) > 0 || (count($this->arr_pstring) > 1) && !$is_summary);
		return $ret_val;
	}
	
	/* -----------------------------------------------------------------
	*  set_filters() sets default filter criteria based on the field-specific values (herd code, pstring, test date)
	*   and page filters pulled from the database

	*  sets default filter criteria based on the field-specific values (herd code, pstring, test date)
	*   and page filters pulled from the database

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 17, 2014
	*  @param: array page-level filters
	*  @param: $arr_params
	*  @return void
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function set_filters($sess_herd_code, $sess_pstring, $sess_breed_code, $recent_test_date, $filter_model, $sect_id, $page, $arr_params){
		//get filters from DB for the current page, set other vars
		$arr_page_filters = $filter_model->get_page_filters($sect_id, $page);
		$this->arr_pstring = $arr_pstring;
		$this->arr_breeds = $arr_breeds;
		$this->arr_filters_list = array();
		//always have filters for herd & pstring (and page?) - Answer: no; Currently only exception is genetic summary
		if($sect_id == 60){
			if(array_key_exists('breed_code', $arr_page_filters) === FALSE) {
				$arr_page_filters['breed_code'] = array('db_field_name' => 'breed_code', 'name' => 'Breed', 'type' => 'select multiple', 'default_value' => array('HO'));
				if(isset($arr_params['breed_code']) === FALSE) {
					$arr_params['breed_code'] = array($sess_breed_code);
				}
			}
		} else {
		
			if(array_key_exists('pstring', $arr_page_filters) === FALSE){ //all queries need to specify pstring
				$arr_page_filters['pstring'] = array('db_field_name' => 'pstring', 'name' => 'PString', 'type' => 'select multiple', 'default_value' => array(0));
				//if pstring isn't set in filter form, set it to the session value
				if(isset($arr_params['pstring']) === FALSE){
					$arr_params['pstring'] = array($sess_pstring);
				}
			}
		}		
		//set default criteria as base
		$this->setDefaultCriteria($sess_herd_code, $sess_pstring, $sess_breed_code, $recent_test_date, $arr_page_filters);
		
		// if form was submitted, add/overwrite with form criteria
		if (is_array($arr_params) && !empty($arr_params)) {
			$this->setFilterFormCriteria($arr_page_filters, $arr_params);
		}
	}

	/* -----------------------------------------------------------------
	*  setFilterFormCriteria() sets filter criteria based on filter form submission

	*  sets filter criteria based on filter form submission

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 17, 2014
	*  @param: array page-level filters
	*  @param: $arr_params
	*  @return void
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	protected function setFilterFormCriteria($arr_page_filters, $arr_params){
		if(!isset($arr_page_filters)){
			return false;
		}
		if(!isset($arr_params)){
			return false;
		}
		
		//if there are no values submitted with form, don't include field in query (remove from array)
		$arr_params = array_filter($arr_params, function($val){
			return ($val !== FALSE && $val !== NULL && $val !== '');
		});
		
		foreach($arr_page_filters as $k=>$f){ //key is the db field name
			if($k == 'page') $this->criteria['page'] = $this->arr_pages[$this->$arr_params['page']]['name'];
			elseif($f['type'] == 'range' || $f['type'] == 'date range'){
				if(!isset($arr_params[$k . '_dbfrom']) || !isset($arr_params[$k . '_dbto'])){
					continue;
				}
				$this->criteria[$k . '_dbfrom'] = $arr_params[$k . '_dbfrom'];
				$this->criteria[$k . '_dbto'] = $arr_params[$k . '_dbto'];
			}
			elseif($f['type'] == 'select multiple'){
				if(isset($arr_params[$k]) && is_array($arr_params[$k])){
					foreach($arr_params[$k] as $k1=>$v1){
						$arr_params[$k][$k1] = explode('|', $v1);
					}
					$arr_params[$k] = array_flatten($arr_params[$k]);
					$this->criteria[$k] = $arr_params[$k];
				}
				if(!$this->criteria[$k] && $k != 'pstring' && $k != 'breed_code') {
					$this->criteria[$k] = array();
				}
				elseif(isset($arr_params[$k])) $this->criteria[$k] = $arr_params[$k];
			}
			else {
				if(!isset($arr_params[$k])) continue;
				$this->criteria[$k] = $arr_params[$k];
			}
		}
	}

	/* -----------------------------------------------------------------
	*  set_default_criteria() sets default filter criteria based on the field-specific values (herd code, pstring, test date)
	*   and page filters pulled from the database

	*  sets default filter criteria based on the field-specific values (herd code, pstring, test date)
	*   and page filters pulled from the database

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 17, 2014
	*  @param: string current herd code
	*  @param: int current pstring
	*  @param: string recent test date
	*  @param: array page-level filters
	*  @return void
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	protected function setDefaultCriteria($sess_herd_code, $sess_pstring, $sess_breed_code, $recent_test_date, $arr_page_filters){
		if(!isset($arr_page_filters)){
			return false;
		}

		$this->criteria['herd_code'] = $sess_herd_code;
		foreach($arr_page_filters as $k=>$f){
			if($f['db_field_name'] == 'pstring' && (!isset($f['default_value']) || empty($f['default_value']))){
				$this->criteria['pstring'] = $sess_pstring;
			}
			elseif($f['db_field_name'] == 'breed_code' && (!isset($f['default_value']) || empty($f['default_value']))){
				$this->criteria['breed_code'] = $sess_breed_code;
			}
			elseif($f['db_field_name'] == 'test_date' && $f['type'] != 'date range' && (!isset($f['default_value']) || empty($f['default_value']))){
				$this->criteria['test_date'] = $recent_test_date;
			}

			//if range, create 2 fields, to and from.  Default value stored in DB as pipe-delimited
			elseif($f['type'] == 'range' || $f['type'] == 'date range'){
				if(!isset($f['default_value'])){
					$f['default_value'] = '|';
				}
				if(strpos($f['default_value'], '|') !== FALSE){
					list($this->criteria[$k . '_dbfrom'], $this->criteria[$k . '_dbto']) = explode('|', $f['default_value']);
				}
			}
			else $this->criteria[$f['db_field_name']] = $f['default_value'];
			
			$this->arr_filters_list[] = $f['db_field_name'];
		}
	}

	/* -----------------------------------------------------------------
	*  criteria() Returns an key=>value array of field_name=>selected_value

	*  Returns an key=>value array of field_name=>selected_value.  These values are populated with the set filter function

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 17, 2014
	*  @return array field_name=>selected_value
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function criteria(){
		if(!isset($this->criteria)){
			return false;
		}
		/* create array of all filter data		
		$filter_data = array(
				'arr_filters'=>$this->arr_filters_list,
				'filter_selected'=>$this->criteria,
				'report_path'=>$this->report_path,
				'arr_pstring'=>$this->primary_model->arr_pstring);	*/			
		return $this->criteria;
	}

	/* -----------------------------------------------------------------
	*  filter_list() Returns a numerically indexed array of filter fields

	*  Returns a numerically indexed array of filter fields

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 17, 2014
	*  @return array of strings
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function filter_list(){
		if(!isset($this->arr_filters_list)){
			return false;
		}
		return $this->arr_filters_list;
	}

	/**
	 * set_filter_text
	 * @description sets arr_filter_text variable.  Composes filter text property for use in the GSG Library file
	 * @author ctranel
	 * @return void
	 * 
	 **/
	protected function set_filter_text(){
		if(!is_array($this->criteria) || empty($this->criteria)){
			return FALSE;
		}

		$arr_filter_text = array();
		foreach($this->criteria as $k=>$v){
			if($k == 'block'); //don't show block filter info because it is specified in heading
			elseif($k == 'pstring') {
				if(is_array($v)) {
					$pstring_text = '';
					if(!empty($v)) {
						foreach($v as $k1=>$v1){
							$pstring_text .= $this->arr_pstring[$k1]['publication_name'] . ', ';
						}
						$pstring_text = substr($pstring_text, 0, -2);
					}
				}
				else $pstring_text = $this->arr_pstring[$v]['publication_name'];
				$arr_filter_text[] = 'PString: ' . $pstring_text;
			}
			elseif($k == 'breed_code') {
				if(is_array($v)) {
					$breed_text = '';
					if(!empty($v)) {
						foreach($v as $k1=>$v1){
							if(isset($this->arr_breeds[$k1]) && !empty($this->arr_breeds[$k1])){
								$breed_text .= $this->arr_breeds[$k1]['breed_name'] . ', ';
							}
						}
						$breed_text = substr($breed_text, 0, -2);
					}
				}
				else $breed_text = $this->arr_breeds[$v]['breed_name'];
				$arr_filter_text[] = 'Breed: ' . $breed_text;
			}
			elseif(is_array($v) && !empty($v)){
				if(($tmp_key = array_search('NULL', $v)) !== FALSE) unset($v[$tmp_key]);
				else $arr_filter_text[] = ucwords(str_replace('_', ' ', $k)) . ': ' . implode(', ', $v);
			}
			else{
				if(substr($k, -5) == "_dbto" && !empty($v)){ //ranges
					$db_field = substr($k, 0, -5);
					$arr_filter_text[] = ucwords(str_replace('_', ' ', $db_field)) . ': Between ' . $this->criteria[$db_field . '_dbfrom'] . ' and ' . $this->criteria[$db_field . '_dbto'];
				}
				elseif(substr($k, -7) != "_dbfrom" && $k != 'herd_code' && !empty($v)){ //default--it skips the opposite end of the range as _dbto
					$arr_filter_text[] = ucwords(str_replace('_', ' ', $k)) . ': ' . $v;
				}
			}
		}
		$this->log_filter_text = is_array($arr_filter_text) && !empty($arr_filter_text)?implode('; ', $arr_filter_text):'';
	}
}