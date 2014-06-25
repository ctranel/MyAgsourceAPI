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
	 * @var object db_table
	 **/
	protected $db_table;

	/**
	 * table that stores most recent data for all herd/pstring data for deriving benchmark groups
	 * @var string
	 **/
	protected $herd_benchmark_pool_table = 'herd.dbo.bench_criteria_summary'; //'vma.dbo.vma_bench_criteria_summary';

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
	private function set_criteria($metric, $criteria, $arr_herd_size = NULL, $arr_breed_codes = NULL){
		$this->metric = $metric;
		$this->criteria = $criteria;
		$this->arr_breeds = $arr_breed_codes;
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
	 * get_user_herd_settings
	 *
	 * @return array of user-herd settings
	 * @author ctranel
	 **/
	private function getUserHerdBenchmarkSettings($user_id, $herd_info){
		//REMOVE IF WE DECIDE TO STORE PREFERENCES
		if(isset($arr_tmp['criteria'])){
			$arr_tmp['metric'] = $arr_sess_benchmarks['metric'];
			$arr_tmp['criteria'] = $arr_sess_benchmarks['criteria'];
			$arr_tmp['arr_breeds'] = $arr_sess_benchmarks['arr_breeds'];
			$arr_tmp['arr_herd_size'] = $arr_sess_benchmarks['arr_herd_size'];
			return $arr_tmp;
		}
		else return FALSE;
	}

	/**
	 * @method get_bench_settings()
	 * @return array of data for the graph
	 * @access protected
	 **/
	public function get_bench_settings($user_id, $herd_info){
		//arr_criteria (field_name, sort_order, table_name, join_field) and arr_herd_size (herd_size_floor, herd_size_ceiling) are arrays
		$arr_user_herd_settings = $this->getUserHerdBenchmarkSettings($user_id, $herd_info);
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
	 * gets default criteria for benchmarks
	 *
	 * @param string breed
	 * @param int herd size
	 * @return array of default settings
	 * @author ctranel
	 **/
	private function get_default_settings($breed, $herd_size = FALSE){
		$arr_ret = array(
				'metric' => 'AVG',
				'criteria' => 'PROD',
				'arr_herd_size' => NULL,
				//system built with the capability to include multiple breeds in benchmark group, so we make breed an array
				'arr_breeds' => array($breed)
		);
		if($breed === 'HO' || $breed === 'JE'){
			$arr_ret['metric'] = 'TOP20_PCT';
		}
		if($breed === 'HO'){
			$arr_ret['arr_herd_size'] = $this->get_default_herd_size_range($herd_size);
		}
		return $arr_ret;
	}
	
	/**
	 * gets default get_default_herd_size_range for benchmarks based on herd size parameter
	 * 
	 * Currently used only for Holstein herds
	 *
	 * @param int herd size
	 * @return array of default settings (2 elements: (floor, ceiling))
	 * @author ctranel
	 **/
	private function get_default_herd_size_range($herd_size){
		//We can set this, but it will currently only be used for Holstein herds
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
		if(isset($sess_benchmarks['arr_herd_size']) && in_array('HO', $sess_benchmarks['arr_breeds'])){
			$bench_text .= ' herds between ' . $sess_benchmarks['arr_herd_size'][0] . ' and ' . $sess_benchmarks['arr_herd_size'][1] . ' animals';
		}
		return $bench_text;
	}
	
	/**
	 * @method addBenchmarkRow
	 * @description retrieves row(s) of benchmark data into an array
	 * @param object database table
	 * @param array session benchmarks
	 * @param object benchmark model
	 * @param int user id
	 * @param array herd overview info
	 * @param string row_head_field - the db field name of the column into which benchmark header text is inserted
	 * @param array of strings db field names to exclude
	 * @param array of strings arr_group_by (db field names)
	 * @return array
	 * @author ctranel
	 **/
	function addBenchmarkRow($db_table, $sess_benchmarks, &$benchmark_model, $user_id, $herd_info, $row_head_field, $arr_fields_to_exclude = array('herd_code', 'pstring', 'lact_group_code', 'ls_type_code', 'sol_group_code'), $arr_group_by){
		if(isset($db_table)){
			$this->db_table = $db_table;
		}
		
		$bench_settings = $this->get_bench_settings($user_id, $herd_info);
		$this->set_criteria($bench_settings['metric'], $bench_settings['criteria'], $bench_settings['arr_herd_size'], $bench_settings['arr_breeds']);

		$avg_fields = $benchmark_model->get_benchmark_fields($this->db_table->full_table_name(), $arr_fields_to_exclude);
		$bench_sql = $benchmark_model->build_benchmark_query(
			$this->db_table,
			$avg_fields,
			$this->arr_criteria_table[$this->criteria],
			$this->herd_benchmark_pool_table,
			$this->metric,
			$this->herd_size_floor,
			$this->herd_size_ceiling,
			$this->arr_breeds,
			$arr_group_by
		);
		$arr_benchmarks = $benchmark_model->getBenchmarkData($bench_sql);
/*
 *	@todo: benchmark session not being set
		$this->arr_pdf_widths['benchmark'] = $header_field_width;
		$this->arr_field_sort['benchmark'] = 'ASC';
		$this->arr_unsortable_columns[] = 'benchmark';
*/
		//$this->metric in place of $sess_benchmarks['metric']?
		$tmp_metric = $this->get_metric_options();
		$bench_head_text = ucwords(strtolower($tmp_metric[$this->metric])) . ' (n=' . $arr_benchmarks[0]['cnt_herds'] . ')';

		foreach($arr_benchmarks as &$b){
			unset($b['cnt_herds']);
			if(isset($b['pstring'])){
				$b['pstring'] = '';
			}
			$b = array($row_head_field => $bench_head_text) + $b;
		}
		return $arr_benchmarks;
	}
}