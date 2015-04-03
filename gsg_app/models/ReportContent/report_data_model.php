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
		$arr_primary_table_fields = $this->{$this->db_group_name}
			->select('db_field_name')
			->from('users.dbo.db_fields')
			->join('users.dbo.db_tables', 'users.dbo.db_fields.db_table_id = users.dbo.db_tables.id')
			->where(array('users.dbo.db_fields.is_fk_field'=>1, 'users.dbo.db_tables.name'=>$tmp_tbl_only))
			->get()
			->result_array();
		list($a, $b, $tmp_tbl_only) = explode('.', $join_table);
		$arr_join_table_fields = $this->{$this->db_group_name}
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
	function search(Block $block, $select_fields, $arr_filter_criteria){//, $arr_sort_by = array(''), $arr_sort_order = array(''), $limit = NULL) {
		$this->load->helper('multid_array_helper');
//		$this->herd_code = $arr_filter_criteria['herd_code'];
		$this->db->start_cache();
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
		if(is_array($arr_filter_criteria) && !empty($arr_filter_criteria)){
			$this->prep_where_criteria($arr_filter_criteria);
		}

		/*now that the where clauses are set, let's see how many rows would be returned with that criteria.
		 *If over 1000 and a filter has not yet been set for quartiles, add the 1st quartile as a filter.
		 *Then we can add the select and sort data to the query.
		 **/
		$this->db->stop_cache();
		if(isset($limit) === FALSE){
			$this->db->select('COUNT(*) AS c');
			$count_result = $this->db->get()->result_array();
			$this->num_results = $count_result[0]['c'];
			
			if($this->num_results > 1000) {// && empty($arr_filter_criteria[$this->arr_auto_filter_field[0]])) {
				$this->_set_autofilter($arr_filter_criteria);
			}
		}
		else $this->db->limit($limit);

		$this->db->group_by($block->getGroupBy());
//		$this->prep_sort($arr_sort_by, $arr_sort_order); // the prep_sort function adds the sort field to the active record object

		//add select fields to query
		//set variable to be used in the query
		if(!is_array($select_fields) || empty($select_fields)){
			$select_fields = ['*'];
		}

//$this->db->select('d'); //uncomment to dump search query to screen
		$this->db->select($select_fields, FALSE);
		$ret = $this->db->get()->result_array();
		$this->num_results = count($ret);
		$this->db->flush_cache();
		//$ret['arr_unsortable_columns'] = $this->arr_unsortable_columns;
		return $ret;
	}
	
	/**
	 * @method prep_select_fields()
	 * @param arr_fields: copy of fields array to be formatted into SQL
	 * @return array of sql-prepped select fields
	 * @author ctranel
	protected function prep_select_fields(){
	}
	 **/
	

	/** function prep_where_criteria
	 * 
	 * translates filter criteria into sql format
	 * @param $arr_filter_criteria
	 * @return void
	 */
	
	protected function prep_where_criteria($where_criteria){
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
	
	/*  
	 * @method prep_group_by()
	 * @author ctranel
	 */
/*	protected function prep_group_by(){
		$arr_len = is_array($this->arr_group_by_field)?count($this->arr_group_by_field):0;
		for($c=0; $c<$arr_len; $c++) {
			$table = isset($this->arr_field_table[$this->arr_group_by_field[$c]]) && !empty($this->arr_field_table[$this->arr_group_by_field[$c]])?$this->arr_field_table[$this->arr_group_by_field[$c]] . '.':$this->primary_table_name . '.';
			if(!empty($this->arr_group_by_field[$c])){
				$this->{$this->db_group_name}->group_by($table . $this->arr_group_by_field[$c]);
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
	protected function prep_sort($arr_sort_by, $arr_sort_order){
		$arr_len = is_array($arr_sort_by)?count($arr_sort_by):0;
		for($c=0; $c<$arr_len; $c++) {
			$sort_order = (strtoupper($arr_sort_order[$c]) == 'DESC') ? 'DESC' : 'ASC';
			$table = isset($this->arr_field_table[$arr_sort_by[$c]]) && !empty($this->arr_field_table[$arr_sort_by[$c]])?$this->arr_field_table[$arr_sort_by[$c]] . '.':$this->primary_table_name . '.';
			if((!is_array($this->arr_unsortable_columns) || in_array($arr_sort_by[$c], $this->arr_unsortable_columns) === FALSE) && !empty($arr_sort_by[$c])){
				//put the select in an array in case the field includes a function with commas between parameters 
				if(is_array($this->arr_natural_sort_fields) && in_array($arr_sort_by[$c], $this->arr_natural_sort_fields) !== FALSE){
					$this->{$this->db_group_name}->order_by('users.dbo.naturalize(' . $table . $arr_sort_by[$c] . ')', $sort_order);
				}
				else {
					$this->{$this->db_group_name}->order_by($table . $arr_sort_by[$c], $sort_order);
				}
			}
		}
	}
	
	
	protected function _set_autofilter($arr_filter_criteria){
		$this->arr_messages['filter_alert'] = '';
		$num_fields = count($this->arr_auto_filter_field);
		for($c = 0; $c < $num_fields; $c++){
			if(empty($arr_filter_criteria[$this->arr_auto_filter_field[$c]])){
				//handle range fields
				//$dbfield = str_replace('_dbfrom', '', $this->arr_auto_filter_field[$c]);
				//$dbfield = str_replace('_dbto', '', $dbfield);
				//end handle range fields
				
				$criteria = $this->arr_auto_filter_criteria[$c];
				if(in_array($dbfield, $this->arr_date_fields) || in_array($dbfield, $this->arr_datetime_fields)) $criteria = date_to_mysqldatetime($criteria);
				if(in_array($dbfield, $this->arr_numeric_fields) === FALSE) $criteria = "'" . $criteria . "'";
				
				$this->{$this->db_group_name}->where($dbfield . $this->arr_auto_filter_operator[$c] . $criteria);
				$this->arr_messages['filter_alert'] .= $this->arr_auto_filter_alert[$c];
			}
		}
	}

	public function get_auto_filter_criteria(){
		$arr_return = array();
		$num_fields = count($this->arr_auto_filter_field);
		for($c = 0; $c < $num_fields; $c++){
			$arr_return[] = array('key' => $this->arr_auto_filter_field[$c], 'value' => $this->arr_auto_filter_criteria[$c]);
		}
		return $arr_return;
	}
	
	/**
	 * get_recent_dates
	 * @return date string
	 * @author ctranel
	 **/
	public function get_recent_dates($date_field = 'test_date', $num_dates = 1, $date_format = 'MMM-yy') {
		if($date_format){
			$this->db->select("FORMAT(" . $date_field . ", '" . $date_format . "', 'en-US') AS " . $date_field, FALSE);
		}
		else{
			$this->db->select($date_field);
		}
		$this->db
			->where($this->primary_table_name . '.herd_code', $this->session->userdata('herd_code'))
			->where($date_field . ' IS NOT NULL')
			->order_by($this->primary_table_name . '.' . $date_field, 'desc');
		if(isset($num_dates) && !empty($num_dates)){
			$this->db->limit($num_dates);		
		}
		$result = $this->db->get($this->primary_table_name)->result_array();
		if(is_array($result) && !empty($result)){
			return array_flatten($result);
		} 
		else return FALSE;
	}

    /**
    * @function get_start_test_date
    * @param string date_field - db name of the date field used for this trend
    * @param int num_dates - number of test dates to include in report
    * @param string date_format - database string for formatting date
    * @param int num_dates_to_shift - number of dates to shift the results back
    * @return string date
    * @author ctranel
    **/
    public function get_start_date($date_field = 'test_date', $num_dates = 12, $date_format = 'MMM-yy', $num_dates_to_shift = 0) {
		$sql = "SELECT FORMAT(a." . $date_field . ", 'MM-dd-yyyy', 'en-US') AS " . $date_field . "
    		FROM (SELECT DISTINCT TOP " . ($num_dates + $num_dates_to_shift) . " " . $date_field . "
                FROM " . $this->primary_table_name . " 
                WHERE herd_code = '" . $this->session->userdata('herd_code') . "' AND " . $date_field . " IS NOT NULL
                ORDER BY " . $date_field . " DESC) a";
        $result = $this->{$this->db_group_name}->query($sql)->result_array();
        if(is_array($result) && !empty($result)) return $result[(count($result) - 1)][$date_field];
		else return FALSE;
	}	
	
/******* CHART FUNCTIONS ****************/
	public function set_chart_fields($block_in){
		$arr_numeric_types = array('bigint','decimal','int','money','smallmoney','numeric','smallint','tinyint','float','real');
		$arr_field_child = array();
		$arr_table_ref_cnt = array();

		$arr_field_data = $this->{$this->db_group_name}
			->where('block_id', $block_in)
			->order_by('list_order')
			->get('users.dbo.v_block_field_data')
			->result_array();
		if(is_array($arr_field_data) && !empty($arr_field_data)){
			foreach($arr_field_data as $fd){
				$fn = $fd['db_field_name'];
				$this->arr_fields[$fd['name']] = $fn;
				$this->arr_db_field_list[] = $fn;
				$arr_table_ref_cnt[$fd['table_name']] = isset($arr_table_ref_cnt[$fd['table_name']]) ? ($arr_table_ref_cnt[$fd['table_name']] + 1) : 1;
				$this->arr_field_sort[$fn] = $fd['default_sort_order'];
				$this->arr_decimal_points[$fn] = $fd['decimal_points'];
				$this->arr_aggregates[$fn] = $fd['aggregate'];
				$this->arr_axis_index[$fn] = $fd['axis_index'];
				$this->arr_bool_display[$fn] = $fd['display'];
				$this->arr_chart_type[$fn] = $fd['chart_type'];
				$this->arr_unit_of_measure[$fn] = $fd['unit_of_measure'];
				$this->arr_field_table[$fn] = $fd['table_name'];
				if(strpos($fd['data_type'], 'date') !== FALSE && strpos($fn, 'time') !== FALSE) $this->arr_datetime_fields[] = $fn;
				elseif(strpos($fd['data_type'], 'date') !== FALSE) $this->arr_date_fields[] = $fn;
				if($fd['is_nullable'] === FALSE) $arr_notnull_fields[] = $fn;
				if(in_array($fd['data_type'], $arr_numeric_types)) $this->arr_numeric_fields[] = $fn;
				if($fd['is_natural_sort']) $this->arr_natural_sort_fields[] = $fn;
			}
		}
		$this->primary_table_name = array_search(max($arr_table_ref_cnt), $arr_table_ref_cnt);
		//set up arr_fields hierarchy
		if(is_array($arr_table_ref_cnt) && count($arr_table_ref_cnt) >  1){
			foreach($arr_table_ref_cnt as $t => $cnt){
				if($t != $this->primary_table_name){
					$this->joins[] = array('table'=>$t, 'join_text'=>$this->get_join_text($this->primary_table_name, $t));
				}
			}
		}
	}
	
	/**
	 * @method get_chart_axes - retrieve data for categories, axes, etc.
	 * @param int block id
	 * @return array of meta data for the block
	 * @access public
	 *
	 **/
	public function get_chart_axes($block_id){
		$arr_return = array();
		$this->{$this->db_group_name}
			->select("a.id, a.x_or_y, a.min, a.max, a.opposite, a.data_type, f.db_field_name, f.name AS field_name, f.unit_of_measure, text,c.name AS category")
			->from('users.dbo.block_axes AS a')
			->join('users.dbo.chart_categories AS c', 'a.id = c.block_axis_id', 'left')
			->join('users.dbo.db_fields AS f', 'a.db_field_id = f.id', 'left')
			->where('a.block_id', $block_id)
			->order_by('a.list_order', 'asc')
			->order_by('c.list_order', 'asc');
		$result = $this->{$this->db_group_name}->get()->result_array();
		
		if(count($result) < 1){
			return false;
		}
		
		$arr_keep_keys = array('min' => '', 'max' => '', 'opposite' => '', 'data_type' => '', 'db_field_name' => '', 'field_name' => '', 'text' => '');
		if(is_array($result) && !empty($result)){
			foreach($result as $a){
				if(!isset($arr_return[$a['x_or_y']][$a['id']])){
					$arr_return[$a['x_or_y']][$a['id']] = array_intersect_key($a, $arr_keep_keys);
				}
				if(isset($a['category'])){
					$arr_return[$a['x_or_y']][$a['id']]['categories'][] = $a['category'];
				}
			}
			return $arr_return;
		}
		else return FALSE;
	}
	
	/**
	 * @method set_row_to_series - used when data for multiple series' are returned in one row.  
	 * Breaks data down so that there is one row per category, each row having one entry for each series.
	 * 
	 * @param array of field name base text (for percentages, add '_pct')
	 * @return array of data for the graph
	 * @access protected
	 *
	 **/
	protected function set_row_to_series($data, $arr_fieldname_base, $arr_categories){
		$mod_base = count($arr_categories);
		if(is_array($data) && !empty($data)){
			$key = 0;
			foreach($data as $k=>$row){
				$count = 1;
				$key++;
				//must account for multiple series being returned in a single row
				foreach($arr_fieldname_base as $kk => $f){
					if($count > $mod_base && $count % $mod_base == 1) $key++;
					if(!isset($key)) $key = $k;
					$arr_return[$key][] = (float)$row[$f];
					$count++;
				}
			}
			return $arr_return;
		}
		else return FALSE;
	}
	
	
	/**
	 * @method get_graph_data()
	 * @param array database field names included on graph
	 * @param string herd code
	 * @param int number of tests to include on report
	 * @param string date field used on graph (test_date)
	 * @param string url segment of block
	 * @param array of categories
	 * @return array of data for the chart
	 * @access public
	 *
	 **/
	function get_graph_data($arr_fieldname, $arr_filters, $num_dates, $date_field, $block_url, $arr_categories = NULL){
		$data = $this->get_graph_dataset($arr_filters, $num_dates, $date_field, $block_url);
		if(isset($arr_categories) && is_array($arr_categories)){
			return $this->set_row_to_series($data, $arr_fieldname, $arr_categories);
		}
		if(!isset($date_field)){//not a category or trend chart (e.g., pie chart)
			return array_values($data);
		}
		else{
			$return_val = $this->set_longitudinal_data($data, $date_field);
		}
		return $return_val;
	}
	
	/**
	 * @method get_graph_dataset()
	 * @param string herd code
	 * @param int number of tests to include on report
	 * @param string date field used on graph (test_date)
	 * @return array of database results
	 * @access public
	 *
	 **/
	function get_graph_dataset($arr_filters, $num_dates, $date_field, $block_url){
		if(isset($date_field) && isset($num_dates)){
			$arr_filters[$date_field]['dbfrom'] = $this->get_start_date($date_field, $num_dates, 'MM-dd-yyyy');
			$arr_filters[$date_field]['dbto'] = $this->get_recent_dates($date_field, 1, 'MM-dd-yyyy')[0];
		}
		$data = $this->search($arr_filters['herd_code'], $block_url, $arr_filters, array($date_field), array('ASC'), $num_dates);
		return $data;
	}
	
	/**
	 * @method get_longitudinal_data()
	 * @param array of field name base text (for percentages, add '_pct')
	 * @param string date field used on graph (test_date)
	 * @return array of data for the graph
	 * @access protected
	 *
	 **/
	protected function set_longitudinal_data($data, $date_field = 'test_date'){
		$count = count($data);
		for($x = 0; $x < $count; $x++){
			$arr_y_values = $data[$x];

			$arr_fields = array_keys($arr_y_values);
			$date_key = array_search($date_field, $arr_fields);
			unset($arr_fields[$date_key]);
			if($date_field == 'age_months'){
				foreach($arr_fields as $k=>$f){
					$tmp_data = is_numeric($data[$x][$f]) ? (float)$data[$x][$f] : $data[$x][$f];
					$arr_return[$k][] = array($data[$x][$date_field], $tmp_data);
				}
			}
			elseif(isset($data[$x][$date_field]) && !empty($data[$x][$date_field])){
				$arr_d = explode('-', $data[$x][$date_field]);
				foreach($arr_fields as $k=>$f){
					$tmp_data = is_numeric($data[$x][$f]) ? (float)$data[$x][$f] : $data[$x][$f];
					$arr_return[$k][] = [(mktime(0, 0, 0, $arr_d[0], $arr_d[1],$arr_d[2]) * 1000), $tmp_data];
				}
			}
		}
		if(isset($arr_return) && is_array($arr_return)) return $arr_return;
		else return FALSE;
	}

	/**
	 * @method set_boxplot_data()
	 * @param array of data from active record result_array() function
	 * @param int number of boxplot series (BOXPLOT SERIES FIELDS MUST ALL BE IMMEDIATELY AFTER THE TEST DATE)
	 * @return array of data for the graph
	 * @access protected
	 *
	 **/
	protected function set_boxplot_data($data, $date_field = 'test_date', $num_boxplot_series = 1, $adjustment = 200000000){
		$row_count = 0;
		$arr_series = [];
		foreach ($data as $d){ //foreach row
			//set a variable so we can pair date with each data point
			if(!isset($d[$date_field])) continue;
			$arr_d = explode('-', $d[$date_field]);
			unset($d[$date_field]); //remove date so we can loop through the remaining data points
			//the date is formated in the database search ('Mon-yr'), so we need to accommodate that
			if(count($arr_d) == 2){
				$arr_month = [];
				$this_date =strtotime($arr_d[0] . ' 15, 20' . $arr_d[1]) * 1000;
			}
			//the date is formated in the database search ('m-d-y'), so we need to accommodate that in the mktime function
			elseif(count($arr_d) == 3){
				$this_date = mktime(0, 0, 0, $arr_d[0], $arr_d[1],'20' . $arr_d[2]) * 1000;
			}
			$num_series = count($d)/3;
			$field_count = 1;
			$series_count = 0;
			$offset = $this->_get_series_offset($num_series, $series_count, $adjustment);
			$arr_series[$series_count][$row_count] = array($this_date + $offset);
			$arr_series[$series_count + 1][$row_count] = array($this_date + $offset);
			foreach ($d as $f){ //for each field in row
				$tmp_data = is_numeric($f) ? (float)$f : $f;
				if($field_count <= ($num_boxplot_series * 3)){// using boxplot chart requires 4 datapoints
					$modulus = $field_count%3;
					$arr_series[$series_count][$row_count][] = $tmp_data;
					//boxplots require 5 datapoints, need to replicate each end of the box (i.e., blend whiskers into box)
					if($modulus === 1 || $modulus === 0){
						$arr_series[$series_count][$row_count][] = $tmp_data;
					}
					if($modulus === 2){ //for median, add a datapoint in the trendline series
						$arr_series[$series_count + 1][$row_count][] = $tmp_data;
					}
					if($modulus == 0 && $field_count > 1){
						$series_count += 2;
						if(($field_count + 1) <= ($num_boxplot_series * 3)){
							$offset = $this->_get_series_offset($num_series, $series_count, $adjustment);
							$arr_series[$series_count][$row_count] = array(($this_date + $offset)); //adjust date so that multiple boxplots are not on top of each other
							$arr_series[$series_count +1][$row_count] = array(($this_date + $offset)); //adjust date so that multiple boxplots are not on top of each other
						}
					}
				}
/*				else { //assumes that non-box series correspond to box series
					$offset = $this->_get_series_offset($num_series, $series_count, $adjustment);
					$arr_series[$series_count][$row_count] = array(($this_date + $offset), $tmp_data);
					$arr_series[$series_count + 1][$row_count] = array(($this_date + $offset), $tmp_data);
					$series_count += 2;
				}
*/				$field_count++;
			}
			$row_count++;
		}
		return $arr_series;
	}
	
	/**
	 * @method _get_series_offset()
	 * @param int number of series' in the dataset for which the offset is being calculated
	 * @param int numeric position of series for which offset is currently being calculated
	 * @param int standardized unit on which adjustment calculation is based
	 * @return int amount to offset date in series
	 * @access protected
	 *
	 **/
		protected function _get_series_offset($num_series, $series_count, $adjustment){
		$offset = 0;;
		if($num_series == 2){
			if($series_count == 0) {
				$offset -= $adjustment;
			}
			if($series_count == 2) {
				$offset += $adjustment;
			}
		}
		if($num_series == 3){
			if($series_count == 0) {
				$offset -= ($adjustment * 2);
			}
			if($series_count == 4) {
				$offset += ($adjustment * 2);
			}
		}
		return $offset;
	}
}
