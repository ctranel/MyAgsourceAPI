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
	 * getPageFilters
	 * 
	 * @param int page id
	 * @return array of filter data for given page
	 * @author ctranel
	 **/
	public function getPageFilters($page_id) {
		$ret_array = array();
		$results = $this->db
            ->select('pf.name, fct.name AS type, pf.options_source, pf.options_filter_form_field_name, pf.default_value, df.db_field_name, pf.user_editable')
            //->where('p.section_id', $section_id)
            ->where('p.id', $page_id)
            ->join('users.dbo.pages p', "pf.page_id = p.id", "inner")
            ->join('users.frm.control_types fct', "pf.control_type_id = fct.id", "inner")
            ->join('users.dbo.db_fields df', "pf.field_id = df.id", 'left')
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
	public function getCriteriaOptions($source_table, $options_conditions) {
		// if herd code is available in the lookup table, create herd code as a where criteria
/*		list($db, $schema, $table) = explode('.', $source_table);
		$sql = "USE " . $db . "; SELECT column_name FROM information_schema.columns WHERE table_name = '" . trim($table) . "' AND column_name = 'herd_code'";
		$arr_fields = $this->db->query($sql)->result_array();
		if(count($arr_fields) === 0){
			return false;
		}
*/		
		if(isset($options_conditions) && is_array($options_conditions)){
			foreach($options_conditions as $c){
                $this->db->where($c['db_field_name'] . ' ' . $c['options_operator'], $c['value']);
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