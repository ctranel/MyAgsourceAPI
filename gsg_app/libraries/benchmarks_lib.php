<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Benchmarks
*
* Author: ctranel
*  
* Created:  1-11-2013
*
* Description:  Works with the report model to set benchmark criteria, calculate benchmarks and return benchmark data.
*
* Requirements: PHP5 or above
*
*/

class Benchmarks_lib
{
	/**
	 * table object used in benchmarks
	 * @var string
	 **/
	protected $db_table;

	/**
	 * table that stores most recent data for all herd/pstring data for deriving benchmark groups
	 * @var string
	 **/
	protected $herd_benchmark_pool_table = 'vma.dbo.bench_criteria_summary'; //'vma.dbo.vma_bench_criteria_summary';

	/**
	 * key field used in benchmarks (will always be test date?)
	 * @var string
	 **/
//	protected $arr_key_fields;

	/**
	 * metric used in benchmarks (avg, qtile, top 10%, etc ...)
	 * @var string
	 **/
	protected $metric;

	/**
	 * criteria used in benchmarks (avg_milk, cow_preg_rate, etc ...)
	 * @var string
	 **/
	protected $criteria;
	
	/**
	 * array of breeds
	 * @var array
	 **/
	protected $arr_breeds;
	
	/**
	 * low end of the herd size range used in benchmarks
	 * @var int
	 **/
	protected $herd_size_floor;
	
	/**
	 * high end of the herd size range used in benchmarks
	 * @var int
	 **/
	protected $herd_size_ceiling;
	
	/**
	 * array of herd size groups
	 * @var array
	 **/
	protected $arr_herd_size_groups;
	
	/**
	 * array of data related to criteria (join text, etc)
	 * @var array
	 **/
	protected $arr_criteria_table;
	
	/**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct()
	{
		$this->arr_herd_size_groups = array(
			1 => array('floor' => 1, 'ceiling' => 124),
			2 => array('floor' => 125, 'ceiling' => 500),
			3 => array('floor' => 501, 'ceiling' => 2000),
			4 => array('floor' => 2001, 'ceiling' => 100000),
		);

		$this->arr_criteria_table = array(
			'PROD' => array(
				'field' => 'rha_milk_lbs',
				'sort_order' => 'desc',
			),
			'GEN' => array(
				'field' => 'net_merit_amt',
				'sort_order' => 'desc',
			),
			'REPRO' => array(
				'field' => 'pregnancy_rate_pct',
				'sort_order' => 'desc',
			),
			'UH' => array(
				'field' => 'wtd_avg_scc',
				'sort_order' => 'asc',
			),
		);
//		'p.herd_rha' => 			array('join_text' => " LEFT JOIN herd_summary.dbo.herd_rha p ON td.herd_code = p.herd_code AND td.test_date = p.test_date", 'sort_order' => 'desc', 'date_field' => 'test_date'),
	}

	/**
	 * gets metric options for benchmarks
	 *
	 * @return array of metric options
	 * @author ctranel
	 **/
	public function get_metric_options(){
		return array(
			'TOP20_PCT' => 'Top 20%',
			'TOP10_PCT' => 'Top 10%',
			'QTILE1' => '1st Quartile',
			'QTILE2' => '2nd Quartile',
			'QTILE3' => '3rd Quartile',
			'QTILE4' => '4th Quartile',
			'AVG' => 'Average'
		);
	}

	/**
	 * gets breed options for benchmarks
	 *
	 * @return array of breed options
	 * @author ctranel
	 **/
	public function get_breed_options(){
		return array(
			'HO' => 'Holstein',
			'JE' => 'Jersey',
			'BS' => 'Brown Swiss',
			'AY' => 'Ayrshire',
			'GU' => 'Guernsey',
			'XX' => 'Cross-bred',
			'MS' => 'Milking Shorthorn',
		);
	}

	/**
	 * gets criteria options for benchmarks
	 *
	 * @return array of criteria options
	 * @author ctranel
	 **/
	public function get_criteria_options(){
		return array(
			'PROD' => 'Production',
			'GEN' => 'Genetics',
			'REPRO' => 'Cow Reproduction',
			'UH' => 'Udder Health'
		);
	}

	/**
	 * sets criteria for benchmarks
	 *
	 * @param string
	 * @param string
	 * @param string
	 * @param array
	 * @param array
	
	 * @return void
	 * @author ctranel
	 **/
	private function set_criteria($pivot_field, $metric, $criteria, $arr_herd_size = NULL, $arr_breed_codes = NULL){
		$this->metric = $metric;
		$this->criteria = $criteria;
		if(is_array($arr_herd_size)){
			if(count($arr_herd_size) == 1){ //the size of the current herd was passed
				foreach($this->arr_herd_size_groups as $k=>$v){
					if($v['floor'] <= $arr_herd_size[0] && $v['ceiling'] >= $arr_herd_size[0]){
						$this->herd_size_floor = $v['floor'];
						$this->herd_size_ceiling = $v['ceiling'];
					}
				}
			}
			if(count($arr_herd_size) == 2){ //a range was passed
				if($arr_herd_size[0] < $arr_herd_size[1]){
					$this->herd_size_floor = $arr_herd_size[0];
					$this->herd_size_ceiling = $arr_herd_size[1];
				}
				else{
					$this->herd_size_floor = $arr_herd_size[1];
					$this->herd_size_ceiling = $arr_herd_size[0];
				}
			}
		}
	}

	/**
	 * gets text description of benchmarks being used
	 *
	 * @return string
	 * @author ctranel
	 **/
	public function get_bench_text($sess_benchmarks){
		if(!isset($sess_benchmarks) || $sess_benchmarks === FALSE){
			return "Benchmark session not set";
		}
		$criteria_options = $this->get_criteria_options();
		$bench_text = 'Benchmark herds determined by ' . $criteria_options[$sess_benchmarks['criteria']] . ' for';
		if(isset($sess_benchmarks['arr_breeds'])){
			$bench_text .= ' ' . implode(',', $sess_benchmarks['arr_breeds']);
		}
		if(isset($sess_benchmarks['arr_herd_size'])){
			$bench_text .= ' herds between ' . $sess_benchmarks['arr_herd_size'][0] . ' and ' . $sess_benchmarks['arr_herd_size'][1] . ' animals';
		}
		return $bench_text;
	}
	
	/**
	 * @method get_bench_settings()
	 * @return array of data for the graph
	 * @access protected
	 **/
	public function get_bench_settings($user_id, $herd_info, $arr_sess_benchmarks = null){
		//arr_criteria (field_name, sort_order, table_name, join_field) and arr_herd_size (herd_size_floor, herd_size_ceiling) are arrays
		$arr_user_herd_settings = $this->getUserHerdBenchmarkSettings($user_id, $herd_info, $arr_sess_benchmarks);
		$arr_default = $this->get_default_settings($herd_info['breed_code'], $herd_info['herd_size']);
		if(isset($arr_user_herd_settings) && is_array($arr_user_herd_settings)){
			$arr_common = array_intersect_key($arr_default, $arr_user_herd_settings);
			if(is_array($arr_common) && !empty($arr_common)){
				foreach($arr_common as $k=>$v){
					$arr_default[$k] = $arr_user_herd_settings[$k];
				}
			}
		}
		return $arr_default;
	}
	
	/**
	 * get_user_herd_settings
	 *
	 * @return array of user-herd settings
	 * @author ctranel
	 **/
	private function getUserHerdBenchmarkSettings($user_id, $herd_info, $arr_sess_benchmarks){
		//REMOVE IF WE DECIDE TO STORE PREFERENCES
		if(isset($arr_sess_benchmarks) && !empty($arr_sess_benchmarks)){
			$arr_tmp['metric'] = $arr_sess_benchmarks['metric'];
			$arr_tmp['criteria'] = $arr_sess_benchmarks['criteria'];
			$arr_tmp['arr_breeds'] = $arr_sess_benchmarks['arr_breeds'];
			$arr_tmp['arr_herd_size'] = $arr_sess_benchmarks['arr_herd_size'];
			return $arr_tmp;
		}
		else return FALSE;
		/*		$user_id = $this->session->userdata('user_id');
			$result = $this->db
		->select()
		->get($this->tables['users_herds_settings'])
		->return_array();
	
		if(is_array($result) && !empty($result)) return $result[0];
		else return FALSE;*/
	}

	/**
	 * gets default criteria for benchmarks
	 *
	 * @param string breed
	 * @param int herd size
	 * @return array of default settings
	 * @author ctranel
	 **/
	private function get_default_settings($breed, $herd_size = FALSE){
		return array(
				'metric' => 'TOP20_PCT',
				'criteria' => 'PROD',
				'arr_herd_size' => $this->get_default_herd_size_range($herd_size),
				'arr_breeds' => array($breed)
		);
	}
	
	private function get_default_herd_size_range($herd_size){
		if($herd_size){
			foreach($this->arr_herd_size_groups as $k=>$v){
				if($v['floor'] <= $herd_size && $v['ceiling'] >= $herd_size){
					$herd_size_floor = $v['floor'];
					$herd_size_ceiling = $v['ceiling'];
				}
			}
			return array($herd_size_floor, $herd_size_ceiling);
		}
		else return NULL;
	}
	
	/**
	 * @description builds sql based on object variables
	 * @param object report_model
	 * @param string arr_fields_to_exclude
	 * @param array of strings arr_group_by (db field names)
	 * @return string
	 * @author ctranel
	 **/
	function addBenchmarkRow($db_table, $sess_benchmarks, &$benchmark_model, $user_id, $herd_info, $pivot_field, $arr_fields_to_exclude = array('herd_code', 'pstring', 'lact_group_code', 'ls_type_code', 'sol_group_code')){
		if(isset($db_table)){
			$this->db_table = $db_table;
		}
		$bench_settings = $this->get_bench_settings($user_id, $herd_info);
		$this->set_criteria($pivot_field, $bench_settings['metric'], $bench_settings['criteria'], $bench_settings['arr_herd_size'], $bench_settings['arr_breeds']);

		$avg_fields = $benchmark_model->get_benchmark_fields($this->db_table->table_name(), $arr_fields_to_exclude);
		$bench_sql = $this->build_benchmark_query($db_table, $avg_fields);
		$arr_benchmarks = $benchmark_model->getBenchmarkData($bench_sql);
/*
 * 
 *	@todo: need to fix this, all model vars
 *	@todo: benchmark session not being set, benchmark text not being displayed in tables
		$this->arr_pdf_widths['benchmark'] = $header_field_width;
		$this->arr_field_sort['benchmark'] = 'ASC';
		$this->arr_unsortable_columns[] = 'benchmark';
*/
		//$this->metric in place of $sess_benchmarks['metric']?
		$tmp_metric = $this->get_metric_options();
		$tmp_key = ucwords(strtolower($tmp_metric[$this->metric])) . ' (n=' . $arr_benchmarks['cnt_herds'] . ')';
/*
 * @todo: make this flexible (work for lact groups (and other) and test date based tables), not dhi-specific
 * 
 */
		unset($arr_benchmarks['cnt_herds']);
//'pstring' => 0 ?
		$arr_benchmarks = array($pivot_field => $tmp_key) + $arr_benchmarks;
		return $arr_benchmarks;
	}
	
	/**
	 * @description builds sql based on object variables
	 * @param object report_model
	 * @param string arr_fields_to_exclude
	 * @param array of strings arr_group_by (db field names)
	 * @return string
	 * @author ctranel
	 **/
	public function build_benchmark_query($db_table, $avg_fields, $arr_group_by = NULL){
		$sql = '';
		$cte = '';
		$addl_select_fields = '';
		$from = '';
		$where = '';
		$group_by = '';
		$order_by = '';
//var_dump($this->arr_criteria_table);
//		$criteria_date_field = $this->arr_criteria_table[$this->criteria]['date_field'];
//		$this->primary_table_date_field = $report_model->date_field;
		
		if($this->metric == "AVG") {
			$cte = $this->build_cte();
			$from = " FROM benchmark_herds bh INNER JOIN " . $this->db_table->table_name() . " p ON bh.herd_code = p.herd_code";
		}
		else {
			$cte = $this->build_cte();
			$from = " FROM benchmark_herds bh INNER JOIN " . $this->db_table->table_name() . " p ON bh.herd_code = p.herd_code";
			if($db_table->field_exists('test_date')){
				$from .= " AND bh.test_date = p.test_date";
			}
		}
		if(strpos($this->metric, 'QTILE') !== FALSE){
			$where = " WHERE bh.quartile = " . str_replace('QTILE', '', $this->metric);
		}
		
		if(isset($arr_group_by) && is_array($arr_group_by)){
			$group_by = " GROUP BY " . $arr_group_by[0];
			$order_by = " ORDER BY " . $arr_group_by[0];
			$addl_select_fields = $arr_group_by[0] . ',';
			$high_index = (count($arr_group_by) - 1);
			for($i=1; $i<=$high_index; $i++){
				$addl_select_fields .= $arr_group_by[$i] . ',';
				$group_by .= ", " . $arr_group_by[$i];
				$order_by .= ", " . $arr_group_by[$i];
			}
		}
		$sql = $cte . "SELECT COUNT(1) AS cnt_herds, " . $addl_select_fields. $avg_fields . $from . $where . $group_by . $order_by;
		return $sql;
	}
	
	protected function build_cte(){
		$sql = '';
		$cte_qualifier = '';
		$cte_order_by = '';
		$cte_fields = 'herd_code, test_date';
		$arr_criteria_data = $this->arr_criteria_table[$this->criteria];
		
		if($this->metric == 'AVG') $cte_qualifier = '';
		if($this->metric == 'TOP10_PCT'){
			$cte_qualifier = 'TOP(10)PERCENT ';
			$cte_order_by = ' ORDER BY ' . $arr_criteria_data['field'] . ' ' . $arr_criteria_data['sort_order'];
		}
		if(strpos($this->metric, 'QTILE') !== FALSE){
			$cte_fields = 'quartile, ' . $cte_fields;
			$cte_qualifier = 'NTILE(4) OVER (ORDER BY ' . $arr_criteria_data['field'] . ' ' . $arr_criteria_data['sort_order'] . ') AS quartile, ';
		}
		
		$sql =  'WITH benchmark_herds(' . $cte_fields . ') AS (SELECT ' . $cte_qualifier . 'herd_code, test_date FROM ' . $this->herd_benchmark_pool_table;
		
		$sql .= ' WHERE test_date > DATEADD(MONTH, -4, GETDATE()) AND ' . $arr_criteria_data['field'] . ' IS NOT NULL ';
		if(isset($this->herd_size_floor) && isset($this->herd_size_ceiling)){
			$sql .= ' AND rha_cow_cnt BETWEEN ' . $this->herd_size_floor . ' AND ' . $this->herd_size_ceiling;
		}
		if(isset($this->arr_breeds) && is_array($this->arr_breeds) && !empty($this->arr_breeds)){
			$sql .= ' AND breed_code IN (' . implode(',', $this->arr_breeds) . ')';
		}
		
		$sql .= $cte_order_by;
		$sql .= ')';
		return $sql;
	}
}