<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH . 'libraries/Supplemental/Content/SupplementalFactory.php');
require_once(APPPATH . 'libraries/Site/WebContent/Sections.php');
require_once(APPPATH . 'libraries/Site/WebContent/Pages.php');
require_once(APPPATH . 'libraries/Site/WebContent/Blocks.php');

use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\Site\WebContent\Sections;
use \myagsource\Site\WebContent\Pages;
use \myagsource\Site\WebContent\Blocks as WebBlocks;
use \myagsource\Report\Content\Block;
use \myagsource\Report\iBlock;

/* -----------------------------------------------------------------
*  @description: Base data access for database-driven report generation
*  @author: ctranel
*  -----------------------------------------------------------------
*/
class Report_data_model extends CI_Model {
	public $arr_messages = array();
	
	public function __construct(){
		parent::__construct();
	}

	protected function get_join_text($primary_table, $join_table){
		$join_text = '';
		list($a, $b, $tmp_tbl_only) = explode('.', $primary_table);
		$arr_primary_table_fields = $this->db
			->select('db_field_name')
			->from('users.dbo.db_fields')
			->join('users.dbo.db_tables', 'users.dbo.db_fields.db_table_id = users.dbo.db_tables.id')
			->where(array('users.dbo.db_fields.is_fk_field'=>1, 'users.dbo.db_tables.name'=>$tmp_tbl_only))
			->get()
			->result_array();
		list($a, $b, $tmp_tbl_only) = explode('.', $join_table);
		$arr_join_table_fields = $this->db
			->select('db_field_name')
			->from('users.dbo.db_fields')
			->join('users.dbo.db_tables', 'users.dbo.db_fields.db_table_id = users.dbo.db_tables.id')
			->where(array('users.dbo.db_fields.is_fk_field'=>1, 'users.dbo.db_tables.name'=>$tmp_tbl_only))
			->get()
			->result_array();
		if(is_array($arr_primary_table_fields) && is_array($arr_join_table_fields)){
			$arr_intersect = array_intersect(array_flatten($arr_primary_table_fields), array_flatten($arr_join_table_fields));
			foreach($arr_intersect as $j){
				if(!empty($join_text)) $join_text .= ' AND ';
				$join_text .= $primary_table . '.' . $j . '=' . $join_table . '.' . $j;
			}
			return $join_text;
		}
		else return FALSE;
	}
	
	/**
	 * @method search()
	 * @param string herd code
	 * @param array filter criteria
	 * @param array sort by
	 * @param array sort order
	 * @return array results of search
	 * @author ctranel
	 **/
	function search(iBlock $block, $select_fields, $arr_filter_criteria){//, $arr_sort_by = array(''), $arr_sort_order = array(''), $limit = NULL) {
//		$this->load->helper('multid_array_helper');
		//load data used to build query
		$where_array = $block->getWhereGroupArray();
		$group_by_array = $block->getGroupBy();
		
		//Start building query
		$this->db->from($block->primaryTableName());
		/*
		 * @todo: add joins
		$joins = $block->joins();
		if(is_array($joins) && !empty($joins)) {
			foreach($joins as $j){
				$this->db->join($j['table'], $j['join_text']);
			}
		}		
		*/
		$this->prepWhereCriteria($where_array);
		if(is_array($arr_filter_criteria) && !empty($arr_filter_criteria)){
			$this->setFilters($arr_filter_criteria);
		}

		$this->db->group_by($group_by_array);
		$this->prep_sort($block); // the prep_sort function adds the sort field to the active record object

		//add select fields to query
		//set variable to be used in the query
		if(!is_array($select_fields) || empty($select_fields)){
			$select_fields = ['*'];
		}

		$this->db->select($select_fields, FALSE);
		if($block->maxRows() > 0){
			$this->db->limit($block->maxRows());
		}
//$this->db->select('d'); //uncomment to dump search query to screen
		$ret = $this->db->get()->result_array();
		$this->num_results = count($ret);
		//$ret['arr_unsortable_columns'] = $this->arr_unsortable_columns;
		return $ret;
	}
	
	/** function prepWhereCriteria
	 * 
	 * translates filter criteria into sql format
	 * @param $arr_filter_criteria
	 * @return void
	 * 
	 * @todo: too much business logic?
	 * @todo: implement child/nested groups
	 */
	
	protected function prepWhereCriteria($where_groups){
		if(isset($where_groups) && is_array($where_groups)){
//			$is_firstg = true;
			foreach($where_groups as $k => $v){
				$sql = '(';
				$is_firstc = true;
//				if(!$is_firstg){
//					$sql .= ') AND (';
//				}
				foreach($v['criteria'] as $c){
					if(!$is_firstc){
						$sql .= ' ' . $v['operator'] . ' ';
					}
					$sql .= $c;
					$is_firstc = false;
				}
				//if this criteria has children
/*				if()){
					//concatenate all conditions with the operator
					foreach($v as $vv){
						//if it is another group
						if(isset($vv['operator'])){
							//recursive call, use this as a wrapper that calls a function that returns only SQL snippets (i.e., the code at the top of the foreach above)
						}
						//$this->db->where(' ' . $v['operator'] . ' ', $v['criteria']);
					}
				}
*/
//				$is_firstg = false;
				$sql .= ')';
				$this->db->where($sql);
			}
		}
	}
		
		
		
	/** setFilters
	 * 
	 * translates filter criteria into sql format
	 * @param $arr_filter_criteria
	 * @return void
	 */
	
	protected function setFilters($where_criteria){
		if(isset($where_criteria) && is_array($where_criteria)){
			foreach($where_criteria as $k => $v){
				if(empty($v) === FALSE || $v === '0'){
					if(is_array($v)){
						//if filter is a range
						if(key($v) === 'dbfrom' || key($v) === 'dbto'){
							if(isset($where_criteria[$k]['dbfrom']) && !empty($where_criteria[$k]['dbfrom']) && isset($where_criteria[$k]['dbto']) && !empty($where_criteria[$k]['dbto'])){
								$from = is_date_format($where_criteria[$k]['dbfrom']) ? date_to_mysqldatetime($where_criteria[$k]['dbfrom']) : $where_criteria[$k]['dbfrom'];
								$to = is_date_format($where_criteria[$k]['dbto']) ? date_to_mysqldatetime($where_criteria[$k]['dbto']) : $where_criteria[$k]['dbto'];
								$this->db->where($k . " BETWEEN '" . $from . "' AND '" . $to . "'");
							}
						}
						else {
							$v = array_filter($v, create_function('$a', 'return (!empty($a) || $a === "0" || $a === 0);'));
							if(empty($v)) continue;
							$this->db->where_in($k, $v);
						}
					}
					else { //is not an array
						$this->db->where($k, $v);
					} 
				}
			}
		}
	}
	
	/*  
	 * @method prep_group_by()
	 * @author ctranel
	 */
/*	protected function prep_group_by(){
		$arr_len = is_array($this->arr_group_by_field)?count($this->arr_group_by_field):0;
		for($c=0; $c<$arr_len; $c++) {
			$table = isset($this->arr_field_table[$this->arr_group_by_field[$c]]) && !empty($this->arr_field_table[$this->arr_group_by_field[$c]])?$this->arr_field_table[$this->arr_group_by_field[$c]] . '.':$this->primary_table_name . '.';
			if(!empty($this->arr_group_by_field[$c])){
				$this->db->group_by($table . $this->arr_group_by_field[$c]);
			}
		}
	}
	*/
	/*  
	 * @method prep_sort()
	 * @param array fields to sort by
	 * @param array sort order--corresponds to first parameter
	 * @author ctranel
	 */
	protected function prep_sort(Block $block){
		$sort_array = $block->getSortArray();
		foreach($sort_array as $f => $o) {
			$sort_order = (strtoupper($o) === 'DESC') ? 'DESC' : 'ASC';
			$table = $block->getFieldTable($f);
			if($block->isSortable($f)){
				//put the select in an array in case the field includes a function with commas between parameters 
				if($block->isNaturalSort($f)){
					$this->db->order_by('users.dbo.naturalize(' . $table . '.' . $f . ')', $sort_order);
				}
				else {
					$this->db->order_by($table . '.' . $f, $sort_order);
				}
			}
		}
	}
	
	/**
	 * getRecentDates
	 * @return date string
	 * @author ctranel
	 **/
	public function getRecentDates($primary_table_name, $date_field, $num_dates = 1, $date_format = 'MMM-yy') {
		if($date_format){
			$this->db->select("FORMAT(" . $date_field . ", '" . $date_format . "', 'en-US') AS " . $date_field, FALSE);
		}
		else{
			$this->db->select($date_field);
		}
		$this->db
			->where($primary_table_name . '.herd_code', $this->session->userdata('herd_code'))
			->where($date_field . ' IS NOT NULL')
			->order_by($primary_table_name . '.' . $date_field, 'desc');
		if(isset($num_dates) && !empty($num_dates)){
			$this->db->limit($num_dates);		
		}
		$result = $this->db->get($primary_table_name)->result_array();
		if(is_array($result) && !empty($result)){
			return array_flatten($result);
		} 
		else return FALSE;
	}

    /**
    * get_start_test_date
    * 
    * @param string date_field - db name of the date field used for this trend
    * @param int num_dates - number of test dates to include in report
    * @param string date_format - database string for formatting date
    * @param int num_dates_to_shift - number of dates to shift the results back
    * @return string date
    * @author ctranel
    **/
    public function getStartDate($primary_table_name, $date_field, $num_dates = 12, $date_format = 'MMM-yy', $num_dates_to_shift = 0) {
		$sql = "SELECT TOP 1 FORMAT(a." . $date_field . ", 'MM-dd-yyyy', 'en-US') AS " . $date_field . "
    		FROM (SELECT DISTINCT TOP " . ($num_dates + $num_dates_to_shift) . " " . $date_field . "
                FROM " . $primary_table_name . " 
                WHERE herd_code = '" . $this->session->userdata('herd_code') . "' AND " . $date_field . " IS NOT NULL
                ORDER BY " . $date_field . " DESC) a
            ORDER BY a." . $date_field . " ASC";
		$result = $this->db->query($sql)->result_array();
        if(is_array($result) && !empty($result)) return $result[(count($result) - 1)][$date_field];
		else return FALSE;
	}	
	
	/**
	 * @method getGraphDataset()
	 * @param string herd code
	 * @param int number of tests to include on report
	 * @param string date field used on graph (test_date)
	 * @return array of database results
	 * @access public
	 *
	function getGraphDataset($arr_filters, iBlock $block, $num_dates, $date_field, $block_url){
		$data = $this->search($arr_filters['herd_code'], $block_url, $arr_filters, array($date_field), array('ASC'), $num_dates);
		return $data;
	}
	 **/
}
