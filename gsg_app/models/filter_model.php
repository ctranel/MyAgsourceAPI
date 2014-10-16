<?php

/* -----------------------------------------------------------------
 *	CLASS comments
 *  @file: filter_model.php
 *  @author: kmarshall
 *  @date: Nov 19, 2013
 *
 *  @description: Model for Filters - 
 *  Accesses page_filters table and appends additions to the where criteria when filters are involved.
 *
 * -----------------------------------------------------------------
 */

class Filter_model extends Report_Model {
	public function __construct(){
		parent::__construct();
		$this->db_group_name = 'default';
		$this->{$this->db_group_name} = $this->load->database($this->db_group_name, TRUE);
	}

	
	/** function prep_where_criteria -- overrode parent function to set where criteria to end of the date given (on form, the user enters only the date).
	 *
	 * translates filter criteria into sql format
	 * @param $arr_filter_criteria
	 * @return void
	 * @author ctranel
	
	 public function prep_where_criteria($arr_filter_criteria){
		foreach($arr_filter_criteria as $k => $v){
			if(empty($v) === FALSE){
				if(is_array($v)){
					if(($tmp_key = array_search('NULL', $v)) !== FALSE){
						unset($v[$tmp_key]);
						$text = implode(',', $v);
						if(!empty($v)) $this->{$this->db_group_name}->where("($k IS NULL OR $k IN ( $text ))");
						else $this->{$this->db_group_name}->where("$k IS NULL");
					}
					elseif(key($v) === 'dbfrom' || key($v) === 'dbto'){
						$from = is_date_format($arr_filter_criteria[$v['dbfrom']]) ? date_to_mysqldatetime($arr_filter_criteria[$v['dbfrom']]) : $arr_filter_criteria[$v['dbfrom']];
						$to = is_date_format($arr_filter_criteria[$v['dbto']]) ? date_to_mysqldatetime($arr_filter_criteria[$v['dbto']]) : $arr_filter_criteria[$v['dbto']];
						$this->{$this->db_group_name}->where($k . " BETWEEN '" . $from . "' AND '" . $to . "'");
					}
					else $this->{$this->db_group_name}->where_in($k, $v);
				}
				else { //is not an array
					$this->{$this->db_group_name}->where($k, $v);
				}
			}
		}
	}
	 */
	
	
	/**
	 * get_page_filters
	 * @return array of filter data for given page
	 * @author ctranel
	 **/
	public function get_page_filters($section_id, $page_url_segment) {
		$ret_array = array();
		$results = $this->{$this->db_group_name}
		->select('pf.name, pf.type, pf.options_source, pf.default_value, pf.db_field_name')
		->where('p.section_id', $section_id)
		->where('p.url_segment', $page_url_segment)
		->join($this->tables['pages'] . ' p', "pf.page_id = p.id")
		->order_by('pf.list_order')
		->get('users.dbo.page_filters pf')
		->result_array();
		if(isset($results) && is_array($results)){
			foreach($results as $r){
				foreach($r as $k => $v){
					$ret_array[$r['db_field_name']][$k] = $v;
				}
				$ret_array[$r['db_field_name']]['default_value'] = unserialize($r['default_value']);
			}
		}
		return $ret_array;
	}
	
	/**
	 * getCriteriaOptions
	 * @return array of criteria options for given filter
	 * @author ctranel
	 **/
	public function getCriteriaOptions($source_table, $herd_code = null) {
		list($db, $schema, $table) = explode('.', $source_table);
		$sql = "USE " . $db . "; SELECT column_name FROM information_schema.columns WHERE table_name = '" . trim($table) . "' AND column_name = 'herd_code'";
		$arr_fields = $this->{$this->db_group_name}->query($sql)->result_array();
		if(count($arr_fields) > 0){
			if(isset($herd_code) && !empty($herd_code)){
				$this->{$this->db_group_name}->where('herd_code', $herd_code);
			}
			else {
				return false;
			}
		}
		
		$results = $this->{$this->db_group_name}
		->select('value, label, is_default')
		->order_by('list_order')
		->get($source_table)
		->result_array();
		return $results;
	}
}