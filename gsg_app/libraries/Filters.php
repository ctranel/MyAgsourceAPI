<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Filters Library File
*
* Author: Chris Tranel
*		  Compiled and Expanded by Kevin Marshall
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

	private $sect_id;
	private $arr_pg_filters;
	
	public function __construct(){
		$ci =& get_instance();
		$ci->load->model('filter_model');
	}
	

	public function get_filter_array($json_array){
		//always have filters for pstring (and page?)
		$arr_params = (array)json_decode(urldecode($json_array));
		if(isset($arr_params['csrf_test_name']) && $arr_params['csrf_test_name'] != $ci->security->get_csrf_hash()) die("I don't recognize your browser session, your session may have expired, or you may have cookies turned off.");
		unset($arr_params['csrf_test_name']);
		return $arr_params;
	}

	
	public function set_filters($page_in, $arr_params){	//FILTERS
		$ci =& get_instance();
		$ci->arr_page_filters = $ci->filter_model->get_page_filters($sect_id, $page_in);
		//always have filters for herd & pstring (and page?)
		if(array_key_exists('pstring', $ci->arr_page_filters) === FALSE){ //all queries need to specify pstring
			$ci->arr_page_filters['pstring'] = array('db_field_name' => 'pstring', 'name' => 'PString', 'type' => 'select multiple', 'default_value' => array(0));
			if(isset($arr_params['pstring']) === FALSE) $arr_params['pstring'] = array(0);
			//$ci->arr_filter_criteria['pstring'] = array(0);
		}
		//herd code is not a part of the filter form (yet), so we hard-code it
		$ci->arr_filter_criteria['herd_code'] = $ci->session->userdata('herd_code');
	
		//iterate through page filter options
		foreach($ci->arr_page_filters as $k=>$f){ //key is the db field name
			//if range, create 2 fields, to and from.  Default value stored in DB as pipe-delimited
			if($f['type'] == 'range' || $f['type'] == 'date range'){
				if(!isset($f['default_value'])) $f['default_value'] = '|';
				list($ci->arr_filter_criteria[$k . '_dbfrom'], $ci->arr_filter_criteria[$k . '_dbto']) = explode('|', $f['default_value']);
			}
			elseif(!isset($ci->arr_filter_criteria[$k])) $ci->arr_filter_criteria[$k] = $f['default_value'];
			$arr_filters_list[] = $f['db_field_name'];
		}
		$arr_params = array_filter($arr_params, function($val){
			return ($val !== FALSE && $val !== NULL && $val !== '');
		});
		if (is_array($arr_params) && !empty($arr_params)) {
			foreach($ci->arr_page_filters as $k=>$f){ //key is the db field name
	
				if($k == 'page') $ci->arr_filter_criteria['page'] = $ci->arr_pages[$arr_params['page']]['name'];
				elseif($f['type'] == 'range' || $f['type'] == 'date range'){
					if(!isset($arr_params[$k . '_dbfrom']) || !isset($arr_params[$k . '_dbto'])) continue;
					$ci->arr_filter_criteria[$k . '_dbfrom'] = $arr_params[$k . '_dbfrom'];
					$ci->arr_filter_criteria[$k . '_dbto'] = $arr_params[$k . '_dbto'];
				}
				elseif($f['type'] == 'select multiple'){
					if(isset($arr_params[$k]) && is_array($arr_params[$k])){
						foreach($arr_params[$k] as $k1=>$v1){
							$arr_params[$k][$k1] = explode('|', $v1);
						}
						$arr_params[$k] = array_flatten($arr_params[$k]);
						$ci->arr_filter_criteria[$k] = $arr_params[$k];
					}
					if(!$ci->arr_filter_criteria[$k] && $k != 'pstring') {
						$ci->arr_filter_criteria[$k] = array();
					}
					elseif(isset($arr_params[$k])) $ci->arr_filter_criteria[$k] = $arr_params[$k];
				}
				else {
					if(!isset($arr_params[$k])) continue;
					$ci->arr_filter_criteria[$k] = $arr_params[$k];
				}
			}
		}
		else { //if no form has been successfully submitted, set to defaults
			foreach($ci->arr_page_filters as $f){
				if($f['db_field_name'] == 'pstring' && (!isset($f['default_value']) || empty($f['default_value']))){
					//$tmp = current($ci->{$ci->primary_model}->arr_pstring);
					$ci->arr_filter_criteria['pstring'] = $ci->pstring;//isset($ci->{$ci->primary_model}->arr_pstring) && is_array($tmp)?array($tmp['pstring']):array(0);
				}
				elseif($f['db_field_name'] == 'test_date' && (!isset($f['default_value']) || empty($f['default_value']))){
					$ci->arr_filter_criteria['test_date'] = $ci->{$ci->primary_model}->get_recent_dates();
				}
				else $ci->arr_filter_criteria[$f['db_field_name']] = $f['default_value'];
			}
		}
		if(validation_errors()) $ci->{$ci->primary_model}->arr_messages[] = validation_errors();
		$arr_filter_text = $ci->reports->filters_to_text($ci->arr_filter_criteria, $ci->{$ci->primary_model}->arr_pstring);
		$ci->log_filter_text = is_array($arr_filter_text) && !empty($arr_filter_text)?implode('; ', $arr_filter_text):'';
		$filter_data = array(
				'arr_filters'=>isset($arr_filters_list) && is_array($arr_filters_list)?$arr_filters_list:array(),
				'filter_selected'=>$ci->arr_filter_criteria,
				'report_path'=>$ci->report_path,
				'arr_pstring'=>$ci->{$ci->primary_model}->arr_pstring,
				//'arr_pages' => $ci->access_log_model->get_pages_by_criteria(array('section_id' => $ci->section_id))->result_array()
		//'page' => $ci->arr_filter_criteria['page']
		);
		return $filter_data;
		//END FILTERS
	}
	
}