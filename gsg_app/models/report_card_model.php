<?php
require_once APPPATH . 'models/report_model.php';
class Report_card_model extends Report_model {
	protected $arr_bench_data;
	public $arr_herd_size_text;
	public $arr_breed_text;
	public $herd_code;
	public $herd_size_code;
	public $pstring;
	public $all_breeds_code;
	
	public function __construct() {
		parent::__construct();
		$this->db_group_name = 'rep_card';
		$this->arr_herd_size_text = array('any number of', 'less than 100', '100 to 250', '251 to 500', '501 to 1000', 'more than 1000');
		$this->arr_breed_text = array('HO' => 'Holstein', 'JE' => 'Jersey', '' => 'All Breeds');
/* ----  BEGIN debugging code - for testing only --------DEBUG_SEARCH_TAG
 *  Remove before deploying
 *  @author: carolmd
 *  @date: Nov 15, 2013
 *
 */
		$this->{$this->db_group_name} = 'default';
		
/* 
 *  ----  END debugging code - for testing only------------------------------------
 */
	//	$this->{$this->db_group_name} = $this->load->database($this->db_group_name, TRUE);
		$this->arr_pages = $this->access_log_model->get_page_links('4');
		$this->tables = array('herd_size_percentile_xref' => 'herd_size_percentile_xref', 'benchmarks'=>'rc_percentile', 'herd_history'=>'rc_graph', 'herd_snap'=>'rc ');
	}
	
	/**
	 * @method get_graph_data()
	 * @param array of field name base text (for percentages, add '_pct')
	 * @param string herd code
	 * @param string pstring
	 * @param string herd size code
	 * @param boolean longitudinal (is the graph longitudinal or a snapshot)
	 * @param string summary date - defaults to most recent summary date
	 * @param boolean use non-breed-specific data
	 * @return array of data for the graph
	 * @access public
	 *
	 **/
	function get_graph_data($arr_fieldname_base, $herd_code, $pstring, $herd_size_code, $longitudinal = FALSE, $summary_date = FALSE, $all_breeds_code = 1) {
		if(is_array($arr_fieldname_base)){
			//set object vars
			if(!$summary_date) $summary_date = $this->get_recent_summary_date();
			$this->summary_date = $summary_date;
			$this->herd_code = $herd_code;
			$this->pstring = $pstring;
			$this->herd_size_code = $herd_size_code;
			$this->all_breeds_code = $all_breeds_code;

			//compose field list and items that are the same for snapshot and historical charts
			//if($this->session->userdata('herd_size_code') == $herd_size_code) //if the requested herd size code is the code for this herd
			foreach($arr_fieldname_base as $f){
				//$this->{$this->db_group_name}->select(str_replace('_percent', '', $f) . '_pct');
				$this->{$this->db_group_name}->select($f);
			}
			$this->{$this->db_group_name}
				//->where('rc_type_code', $this->all_breeds_code) we are getting actual values, not percentiles, so type code is irrelevent
				->where('herd_code',$this->herd_code)
				->where('pstring', $this->pstring);
		
			if($longitudinal) return $this->get_longitudinal_data($arr_fieldname_base);
			else {
				return $this->get_snapshot_data($arr_fieldname_base);
			} 
		}
		else return FALSE;
	}

	/**
	 * @method get_snapshot_data()
	 * @param array of field name base text (for percentages, add '_pct')
	 * @return array of data for the graph
	 * @access private
	 *
	 **/
	private function get_snapshot_data($arr_fieldname_base){
		$this->{$this->db_group_name}->order_by('summary_date', 'desc')
		->distinct()
		->limit(1);
		$data = $this->{$this->db_group_name}->get($this->tables['herd_snap'])->result_array();
		if(is_array($data) && !empty($data)){
			$data = $data[0];
			$percentiles = $this->get_percentiles($data, $this->tables['herd_snap'], $this->summary_date);
			$percentiles = $percentiles[0];
			
			foreach($data as $k=>$v){
				$arr_return[] = array('y'=>(int)$percentiles[$k], 'value'=>(int)$v);
			}
			
			$arr_bench = $this->get_bench_graph_data($arr_fieldname_base);
			return array($arr_return, $arr_bench);
		}
		else return FALSE;
	}
	
	/**
	 * @method get_longitudinal_data()
	 * @param array of field name base text (for percentages, add '_pct')
	 * @return array of data for the graph
	 * @access private
	 *
	 **/
	private function get_longitudinal_data($arr_fieldname_base){
		$this->{$this->db_group_name}->select('summary_date')
			->order_by('summary_date', 'asc');
		$data = $this->{$this->db_group_name}->get($this->tables['herd_history'])->result_array();

		$count = count($data);
		for($x = 0; $x < $count; $x++){
			$arr_y_values = $data[$x];
			unset($arr_y_values['summary_date']);
			$percentiles = $this->get_percentiles($arr_y_values, $this->tables['herd_history'], $data[$x]['summary_date']);
			foreach($arr_fieldname_base as $k=>$f){
				//$this->{$this->db_group_name}->select(str_replace('_percent', '', $f) . '_pct');
				//$this->{$this->db_group_name}->select($f);
				$arr_d = explode('-', $data[$x]['summary_date']);
				$arr_return[$k][] = array((mktime(0, 0, 0, $arr_d[1], $arr_d[2],$arr_d[0]) * 1000), (int)$percentiles[0][$f]);
			}
		}
		if(isset($arr_return) && is_array($arr_return)) return $arr_return;
		else return FALSE;
	}
	
	/**
	 * @method get_bench_graph_data()
	 * @param array of base field names
	 * @param string breed code
	 * @param string herd size code
	 * @return array of benchmark data for the production graph
	 * @access private
	 *
	 **/
	private function get_bench_graph_data($arr_fieldname_base) {
		if(is_array($arr_fieldname_base)){
			foreach($arr_fieldname_base as $f){
				$f = str_replace('_percent', '', $f);
				$this->{$this->db_group_name}->select($f . '_10_pct');
				$this->{$this->db_group_name}->select($f . '_50_pct');
				$this->{$this->db_group_name}->select($f . '_90_pct');
			}
			$data = $this->{$this->db_group_name}
			->where('herd_size_code',$this->herd_size_code)
			->where('rc_type_code',$this->all_breeds_code)
			->get($this->tables['benchmarks'])->result_array();
			$data = $data[0]; //get the first (and only) row of results
			if(is_array($data) && is_array($arr_fieldname_base)){
				$c = 0;
				foreach($arr_fieldname_base as $f){
					$f = str_replace('_percent', '', $f);
					$arr_return[] = array('x'=>$c , 'y'=>'10', 'val'=>$data[$f . '_10_pct']);
					$arr_return[] = array('x'=>$c , 'y'=>'50', 'val'=>$data[$f . '_50_pct']);
					$arr_return[] = array('x'=>$c , 'y'=>'90', 'val'=>$data[$f . '_90_pct']);
					$c++;
				}
				$this->arr_bench_data = $arr_return;
				return $this->arr_bench_data;
			}
			else return FALSE;
		}
		else return FALSE;
	}	

	/**
	 * @method get_herd_size_code()
	 * @param string herd code
	 * @param string pstring
	 * @param string summary date
	 * @return array of data for the alert graph
	 * @access public
	 *
	 **/
	function get_herd_size_code($herd_code, $pstring = 0, $summary_date = FALSE) {
		if($summary_date) $this->{$this->db_group_name}->where('summary_date', $summary_date);
		else $this->{$this->db_group_name}->order_by('summary_date', 'desc');
		$results = $this->{$this->db_group_name}->select('herd_size_code')
		->where('pstring', $pstring)
		->where('herd_code', $herd_code)
		->where('herd_code', $herd_code)
		->order_by('rc_type_code', 'asc')
		->get($this->tables['herd_snap'])->result_array();
		if(is_array($results) && !empty($results)) return $results[0]['herd_size_code'];
		else return false;
	}

	/**
	 * @method get_all_breeds_code()
	 * @param string herd code
	 * @param string pstring
	 * @param string summary date
	 * @return array of data for the alert graph
	 * @access public
	 *
	 **/
	function get_all_breeds_code($herd_code, $pstring=0, $summary_date = FALSE) {
		if($summary_date) $this->{$this->db_group_name}->where('summary_date', $summary_date);
		else $this->{$this->db_group_name}->order_by('summary_date', 'desc');
		$results = $this->{$this->db_group_name}->select('rc_type_code')
		->where('pstring', $pstring)
		->where('herd_code', $herd_code)
		->order_by('rc_type_code', 'asc')
		->get($this->tables['herd_snap'])->result_array();
		if(is_array($results) && !empty($results)) return($results[0]['rc_type_code']);
		else return false;
	}

	/**
	 * @method get_percentiles()
	 * @param array of values with the base field name as the key
	 * @param string herd code
	 * @param string pstring
	 * @param string herd size code
	 * @return array of benchmark data for all report card graph
	 * @access private
	 *
	 **/
	private function get_percentiles($arr_values, $table = NULL, $summary_date) {
		if($table == NULL) $table = $this->tables['herd_snap'];
		//check to see if there are any pre-calculated values for this herd/pstring/rc_type
		foreach($arr_values as $k=>$v){
			if($k != 'summary_date') $this->{$this->db_group_name}->select(str_replace('_percent', '', $k) . '_pct AS ' . $k);
		}
		if($summary_date) $this->{$this->db_group_name}->where('summary_date', $summary_date);
		$results = $this->{$this->db_group_name}->where('herd_code',$this->herd_code)
		->where('pstring', $this->pstring)
		->where('rc_type_code', $this->all_breeds_code)
		->order_by('summary_date', 'desc')
		->get($table)->result_array();
		if(is_array($results) && !empty($results)) return $results;
		else {//if there are no pre-calculated values for this herd/pstring/rc_type, get value from cross-ref table
			$arr_return = array();
			foreach($arr_values as $k=>$v){
			//get value for sequence column
				$sequence = $this->get_sequence($k);
				$gt_lt = strtoupper($sequence) == 'DESC' ? '>' : '<';
				if(empty($v) && $v !== '0') $v = strtoupper($sequence) == 'DESC' ? '9999999' : '0';
				if($summary_date) $this->{$this->db_group_name}->where('summary_date', $summary_date);
				$results = $this->{$this->db_group_name}
					->select('percentile')
					->where('rc_type_code',$this->all_breeds_code)
					->where('herd_size_code', $this->herd_size_code)
					->where('benchmark_field_name', $k)
					->where('benchmark_value ' . $gt_lt . '=', $v)
					->order_by('summary_date', 'desc')
					->order_by('percentile', 'desc')
					->limit(1)
					->get($this->tables['herd_size_percentile_xref'])->result_array();
				if(isset($results[0]) && is_array($results[0])) $arr_return[0][$k] = $results[0]['percentile'];
				else $arr_return[0][$k] = null;
			}
			return $arr_return;
		}
		
		return FALSE;
	}
	
	/**
	 * get_sequence
	 * @param string field name
	 * @return string of sort order for that field
	 * @author Chris Tranel
	 **/
	public function get_sequence($field) {
		$this->{$this->db_group_name}->select('sequence');//, publication_name')
		$result = $this->{$this->db_group_name}->get_where($this->tables['herd_size_percentile_xref'], array('benchmark_field_name'=>$field), 1)->result_array();
		if(is_array($result) && !empty($result)) return $result[0]['sequence'];
		else return FALSE;
	}
	
	/**
	 * get_comp_data
	 * @param string herd size code
	 * @param boolean use non-breed-specific data
	 * @return array of fields used to compose comparison text
	 * @author Chris Tranel
	 **/
	public function get_comp_data($herd_size_code, $all_breeds_code = FALSE) {
		$this->{$this->db_group_name}->select('herd_cnt, breed_code') //, publication_name')
		->where('herd_size_code', $herd_size_code)
		->where('rc_type_code',$all_breeds_code);
		if(isset($summary_date)) $this->{$this->db_group_name}->where('summary_date', $summary_date);
		
		$result = $this->{$this->db_group_name}->get($this->tables['benchmarks'])->result_array();
		if(is_array($result) && !empty($result)) return $result[0];
		else return FALSE;
	}

	/**
	 * get_recent_summary_date
	 * @return date string
	 * @author Chris Tranel
	 **/
	public function get_recent_summary_date() {
		$this->{$this->db_group_name}->select("FORMAT(MAX(summary_date), 'yyyy-MM-dd') AS summary_date", FALSE);		
		$result = $this->{$this->db_group_name}->get($this->tables['herd_snap'])->result_array();
		if(is_array($result) && !empty($result)) return $result[0]['summary_date'];
		else return FALSE;
	}
}
