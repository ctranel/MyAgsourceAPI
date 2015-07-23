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

class Filter_model extends CI_Model {
	/**
	 * get_page_filters
	 * @return array of filter data for given page
	 * @author ctranel
	 **/
	public function get_page_filters($section_id, $page_path) {
		$ret_array = array();
		$results = $this->db
		->select('pf.name, pf.type, pf.options_source, pf.default_value, pf.db_field_name, pf.user_editable')
		->where('p.section_id', $section_id)
		->where('p.path', $page_path)
		->join('users.dbo.pages p', "pf.page_id = p.id", "inner")
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
		// if herd code is available in the lookup table, create herd code as a where criteria
		list($db, $schema, $table) = explode('.', $source_table);
		$sql = "USE " . $db . "; SELECT column_name FROM information_schema.columns WHERE table_name = '" . trim($table) . "' AND column_name = 'herd_code'";
		$arr_fields = $this->db->query($sql)->result_array();
		if(count($arr_fields) > 0){
			if(isset($herd_code) && !empty($herd_code)){
				$this->db->where('herd_code', $herd_code);
			}
			else {
				return false;
			}
		}
		// run query
		$results = $this->db
		->select('value, label, is_default')
		->order_by('list_order')
		->get($source_table)
		->result_array();
		return $results;
	}
}