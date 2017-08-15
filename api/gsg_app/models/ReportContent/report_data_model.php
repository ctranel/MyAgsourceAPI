<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . 'helpers/multid_array_helper.php');

use \myagsource\Report\iReport;

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
	 * @param iReport
	 * @param array select fields
	 * @param array filter criteria
	 * @return array results of search
	 * @author ctranel
	 **/
	function search(iReport $report, $select_fields, $arr_filter_criteria) {//, $arr_sort_by = array(''), $arr_sort_order = array(''), $limit = NULL) {
        $this->composeSearch($report, $select_fields, $arr_filter_criteria);

        $ret = $this->db->get()->result_array();
        //$this->num_results = count($ret);
        //$ret['arr_unsortable_columns'] = $this->arr_unsortable_columns;
        return $ret;
    }

    /**
     * @method composeSearch()
     * @param iReport
     * @param array select fields
     * @param array filter criteria
     * @return void
     * @author ctranel
     **/
    protected function composeSearch(iReport $report, $select_fields, $arr_filter_criteria) {
        //load data used to build query
		$where_array = $report->getWhereGroupArray();
		$group_by_array = $report->getGroupBy();
		
		//Start building query
		$this->db->from($report->primaryTableName());
		/*
		 * @todo: add joins
		$joins = $report->joins();
		if(is_array($joins) && !empty($joins)) {
			foreach($joins as $j){
				$this->db->join($j['table'], $j['join_text']);
			}
		}		
		*/

        $where_sql = $this->getWhereSql($where_array);
		if(isset($where_sql)){
		    $this->db->where($where_sql);
        }
		if(is_array($arr_filter_criteria) && !empty($arr_filter_criteria)){
			$this->setFilters($arr_filter_criteria);
		}

		$this->db->group_by($group_by_array);
		$this->prep_sort($report); // the prep_sort function adds the sort field to the active record object

		//add select fields to query
		//set variable to be used in the query
		if(!is_array($select_fields) || empty($select_fields)){
			$select_fields = ['*'];
		}

		$this->db->select($select_fields, FALSE);
		if($report->maxRows() > 0){
			$this->db->limit($report->maxRows());
		}

        //uncomment to dump search query to screen
            //$this->db->select('d');

//echo $this->db->get_compiled_select(); //die;
	}
	
	/** function getWhereSql
	 * 
	 * translates filter criteria into sql format
	 * @param $arr_filter_criteria
	 * @return void
	 * 
	 */
	
	protected function getWhereSql($where_groups){
		if(!isset($where_groups) || !is_array($where_groups)) {
            return;
        }
        $sql = '';
        $is_firstc = true;

        foreach($where_groups['criteria'] as $k => $v){
            //add operator if this is not the first criteria or group
            if(!$is_firstc){
                $sql .= ' ' . $where_groups['operator'] . ' ';
            }
            $is_firstc = false;

            if(is_array($v)){
                $sql .= '('. $this->getWhereSql($v). ')';
            }
            else{
                $sql .= $v;
            }
        }
        return $sql;
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
                $val = $v['value'];
				if(empty($val) === FALSE || $val === '0'){
					if(is_array($val)){
						//if filter is a range
						if(key($val) === 'dbfrom' || key($val) === 'dbto'){
							if(isset($where_criteria[$k]['value']['dbfrom']) && !empty($where_criteria[$k]['value']['dbfrom']) && isset($where_criteria[$k]['value']['dbto']) && !empty($where_criteria[$k]['value']['dbto'])){
								$from = is_date_format($where_criteria[$k]['value']['dbfrom']) ? date_to_mysqldatetime($where_criteria[$k]['value']['dbfrom']) : $where_criteria[$k]['value']['dbfrom'];
								$to = is_date_format($where_criteria[$k]['value']['dbto']) ? date_to_mysqldatetime($where_criteria[$k]['value']['dbto']) : $where_criteria[$k]['value']['dbto'];
								$this->db->where($k . " BETWEEN '" . $from . "' AND '" . $to . "'");
							}
						}
						else {
							$v = array_filter($val, create_function('$a', 'return (!empty($a) || $a === "0" || $a === 0);'));
							if(empty($v)) continue;
							$this->db->where_in($k, $v);
						}
					}
					else { //is not an array
                        if(strtoupper($v['operator']) === 'IN'){
                            $this->db->where_in($k, [$val]);
                        }
                        elseif(strtoupper($v['operator']) === 'LIKE'){
                            $this->db->like($k, $val);
                        }
						else{
                            $this->db->where($k . $v['operator'] . ' ', $val);
                        }
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
	protected function prep_sort(iReport $report){
		$sort_array = $report->getSortArray();
		foreach($sort_array as $f => $o) {
			$sort_order = (strtoupper($o) === 'DESC') ? 'DESC' : 'ASC';
			$table = $report->getFieldTable($f);
			if($report->isSortable($f)){
				//put the select in an array in case the field includes a function with commas between parameters 
				if($report->isNaturalSort($f)){
					$this->db->order_by('users.dbo.naturalize(' . $table . '.' . $f . ')', $sort_order);
				}
				else {
					$this->db->order_by($table . '.' . $f, $sort_order);
				}
			}
		}
	}
	
	/**
	 * @method getGraphDataset()
	 * @param string herd code
	 * @param int number of tests to include on report
	 * @param string date field used on graph (test_date)
	 * @return array of database results
	 * @access public
	 *
	function getGraphDataset($arr_filters, iReport $report, $num_dates, $date_field, $block_url){
		$data = $this->search($arr_filters['herd_code'], $block_url, $arr_filters, array($date_field), array('ASC'), $num_dates);
		return $data;
	}
	 **/
}
