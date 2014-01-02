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
	 * @author Chris Tranel
	 */
	
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
					else $this->{$this->db_group_name}->where_in($k, $v);
				}
				else { //is not an array
					if(substr($k, -5) == "_dbto"){ //ranges
						$db_field = substr($k, 0, -5);
						//overrode this line only--if we add time to user form, this function can be removed.
						$this->{$this->db_group_name}->where("$db_field BETWEEN '" . date_to_mysqldatetime($arr_filter_criteria[$db_field . '_dbfrom']) . "' AND '" . date_to_mysqldatetime($arr_filter_criteria[$db_field . '_dbto'] . ' 23:59:59') . "'");
					}
					elseif(substr($k, -7) != "_dbfrom"){ //default--it skips the opposite end of the range as _dbto
						$this->{$this->db_group_name}->where($k, $v);
					}
				}
			}
		}
	}
	
	
	/**
	 * get_page_filters
	 * @return array of filter data for given page
	 * @author Chris Tranel
	 **/
	public function get_page_filters($section_id, $page_url_segment) {
		$ret_array = array();
		$results = $this->{$this->db_group_name}
		->select('pf.*, f.db_field_name')
		->where('p.section_id', $section_id)
		->where('p.url_segment', $page_url_segment)
		->join($this->tables['pages'] . ' p', "pf.page_id = p.id")
		->join('users.dbo.db_fields f', "pf.field_id = f.id")
		->order_by('pf.list_order')
		->get('users.dbo.page_filters pf')
		->result_array();
		if(isset($results) && is_array($results)){
			foreach($results as $r){
				$ret_array[$r['db_field_name']] = array(
						'db_field_name' => $r['db_field_name']
						,'name' => $r['name']
						,'type' => $r['type']
						,'default_value' => unserialize($r['default_value'])
				);
			}
		}
		return $ret_array;
	}
	
}