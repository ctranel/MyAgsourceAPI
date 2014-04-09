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
	 * table used in benchmarks
	 * @var string
	 **/
	protected $db_table;

	/**
	 * date field used in benchmarks (will always be test date?)
	 * @var string
	 **/
	protected $primary_table_date_field;

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
	 * benchmarks regions (not the same as user regions) used in benchmarks
	 * @var array
	 **/
	protected $arr_regions;
	
	/**
	 * array of states
	 * @var array
	 **/
	protected $arr_states;
	
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
	 * array of states in each region
	 * @var array
	 **/
	protected $arr_states_in_region;

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
	
	private $ci;
	
	/**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct()
	{
		$this->ci =& get_instance();
		//$this->load->config('ion_auth', TRUE);
		$this->arr_states_in_region = array(
			'NE' => array('NY','PA','OH','VT','ME','CT','NH','MA','NJ','RI'),
			'SE' => array('FL','VA','GA','MD','KY','NC','TN','SC','LA','MS','WV','AL','AK','DE'),
			'MW' => array('WI','MI','MN','IL','IA','IN','KS','SD','MO','NE','ND'),
			'NW' => array('ID','WA','CO','OR','UT','MT','WY'),
			'SW' => array('CA','TX','NM','AZ','OK','NV'),
			'US' => array('NY','PA','OH','VT','ME','CT','NH','MA','NJ','RI','FL','VA','GA','MD','KY','NC','TN','SC','LA','MS','WV','AL','AK','DE','WI','MI','MN','IL','IA','IN','KS','SD','MO','NE','ND','ID','WA','CO','OR','UT','MT','WY','CA','TX','NM','AZ','OK','NV')
		);
		$this->arr_herd_size_groups = array(
			1 => array('floor' => 1, 'ceiling' => 124),
			2 => array('floor' => 125, 'ceiling' => 500),
			3 => array('floor' => 501, 'ceiling' => 2000),
			4 => array('floor' => 2001, 'ceiling' => 100000),
		);
		$this->arr_criteria_table = array(
			'p.herd_rha' => 			array('join_text' => " LEFT JOIN herd_summary.dbo.herd_rha p ON td.herd_code = p.herd_code AND td.recent_test_date = p.test_date", 'sort_order' => 'desc', 'date_field' => 'test_date'),
//			'r.herd_preg_rate' => 		array('join_text' => " LEFT JOIN dbo.v_repro_dashboard r ON td.herd_code = r.herd_code AND td.recent_test_date = r.test_date", 'sort_order' => 'desc', 'date_field' => 'test_date'),
//			'hr.preg_rate_395_vwp' => 	array('join_text' => " LEFT JOIN dbo.v_heifer_repro_dashboard hr ON td.herd_code = hr.herd_code AND td.recent_test_date = hr.recent_test_date", 'sort_order' => 'desc', 'date_field' => 'test_date'),
//			'q.avg_linear' =>			array('join_text' => " LEFT JOIN dbo.v_quality_dashboard q ON td.herd_code = q.herd_code AND td.recent_test_date= q.test_date", 'sort_order' => 'asc', 'date_field' => 'test_date'),
//			'g.avg_net_merit' => 		array('join_text' => " LEFT JOIN dbo.genetics g ON td.herd_code = g.herd_code AND td.recent_test_date = g.recent_test_date", 'sort_order' => 'desc', 'date_field' => 'test_date')
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
			'TOP10_PCT' => 'Top 10%',
			'AVG' => 'Average',
			'QTILE1' => '1st Quartile',
			'QTILE2' => '2nd Quartile',
			'QTILE3' => '3rd Quartile',
			'QTILE4' => '4th Quartile'
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
			'p.avg_milk' => 'Average Milk',
//			'r.herd_preg_rate' => 'Cow Preg Rate',
//			'hr.preg_rate_395_vwp' => 'Heifer Preg Rate',
//			'q.avg_linear' => 'Average Linear Score',
//			'g.avg_net_merit' => 'Net Merit'
		);
	}

	/**
	 * gets breakdown options for benchmarks
	 *
	 * @return array of breakdown options
	 * @author ctranel
	 **/
	public function get_breakdown_options(){
		return array(
			'' => 'All Herds',
			'herd_size' => 'Herd Size',
			'region' => 'Region'
		);
	}

	/**
	 * gets text description of benchmarks being used
	 *
	 * @return string
	 * @author ctranel
	 **/
	public function get_bench_text(){
		$sess_benchmarks = $this->ci->session->userdata('benchmarks');
		$criteria_options = $this->get_criteria_options();
		$bench_text = 'Benchmark herds determined by ' . $criteria_options[$sess_benchmarks['criteria']];
		if(isset($sess_benchmarks['arr_herd_size'])) $bench_text .= ' for Herds between ' . $sess_benchmarks['arr_herd_size'][0] . ' and ' . $sess_benchmarks['arr_herd_size'][1] . ' animals.';
		if(isset($sess_benchmarks['arr_states'])) $bench_text .= ' for Herds in ' . implode(',', $sess_benchmarks['arr_states']) . '.';
		return $bench_text;
	}
	
	/**
	 * gets criteria options for benchmarks
	 *
	 * @return array of criteria options
	 * @author ctranel
	public function get_criteria_sort_order($criteria){
		switch($criteria){
			case 'p.avg_milk':
				return 'DESC';
			case 'r.herd_preg_rate':
				return 'DESC';
			case 'hr.preg_rate_395_vwp':
				return 'DESC';
			case 'q.avg_linear':
				return 'ASC';
			case 'g.avg_net_merit':
				return 'DESC';
			default:
				return FALSE;
		}
	}
	 **/

	/**
	 * gets default criteria for benchmarks
	 *
	 * @param int herd size
	 * @param string state

	 * @return array of default settings
	 * @author ctranel
	 **/
	public function get_default_settings($herd_size = FALSE, $state = FALSE){
		return array(
			'metric' => 'TOP10_PCT',
			'criteria' => 'p.avg_milk',
			'arr_herd_size' => $this->get_default_herd_range($herd_size),
			'arr_states' => $this->get_default_states($state)
		);
	}

	public function get_default_herd_range($herd_size){
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
	
	public function get_default_states($state){
		if($state){
			$arr_states = array();
			foreach($this->arr_states_in_region as $k=>$v){
				if($k != 'US'){ //all states would be included in US, so we don't consider those
					if(in_array($state, $v)){
						//$this->arr_regions[] = $k;
						$arr_states = array_merge($arr_states, $v);
					}					
				}
			}
//echo "<b>get default states:</b>";
//var_dump($arr_states);
			return $arr_states;
		}
		else return NULL;
	}
	
/*	public function get_default_states($state){
		if($state){
			$arr_states = array();
			foreach($this->arr_states_in_region as $k=>$v){
				if($k != 'US'){ //all states would be included in US, so we don't consider those
					$arr_regions = array_intersect($this->arr_states_in_region, $v);
					if(count($arr_regions) > 0){
						//$this->arr_regions[] = $k;
						$arr_states = array_merge($arr_states, $v);
					}
					
				}
			}
			return $arr_states;
		}
		else return NULL;
	}*/
	/**
	 * sets default criteria for benchmarks
	 *
	 * @param int herd size
	 * @param string state

	 * @return array of default settings
	 * @author ctranel
	 **/
	public function set_herd_defaults($herd_code){
		$herd_info = $this->ci->herd_model->header_info($this->ci->session->userdata('herd_code'));
		$arr_tmp = array(
			'metric' => 'TOP10_PCT',
			'criteria' => 'p.avg_milk',
			'arr_herd_size' => $this->get_default_herd_range($herd_info['herd_size']),
			'arr_states' => NULL // $this->get_default_states($herd_info['state_prov'])
		);
		$this->ci->session->set_userdata('benchmarks', $arr_tmp);
		return $arr_tmp;
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
	public function set_criteria($db_table, $date_field, $metric = 'TOP10_PCT', $criteria = 'p.herd_rha', $arr_herd_size = NULL, array $arr_states = NULL){
		if(isset($db_table)) $this->db_table = $db_table;
		if(isset($date_field)) $this->date_field = $date_field;
		else return FALSE;
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
		if(is_array($arr_states)){
			$this->arr_states = $arr_states;
/*			
			foreach($this->arr_states_in_region as $k=>$v){
				$arr_regions = array_intersect($this->arr_states_in_region, $v);
				if(count($arr_regions) > 0){
					$this->arr_regions[] = $k;
					$this->arr_states = array_merge($this->arr_states, $v);
				}
			} */
		}
		else{ //default to US region so that international herds are not included
			//$this->arr_regions[] = 'US';
			$this->arr_states = $this->arr_states_in_region['US'];
		}
	}
	
	/**
	 * builds sql based on object variables
	 *
	 * @return string
	 * @author ctranel
	 **/
	public function build_benchmark_query(&$report_model, $arr_fields_to_exclude = NULL, $arr_group_by = NULL){
		$sql = '';
		$cte = '';
		$addl_select_fields = '';
		$from = '';
		$where = '';
		$group_by = '';
		$order_by = '';
		$criteria_date_field = $this->arr_criteria_table[$this->criteria]['date_field'];
		$this->primary_table_date_field = $report_model->date_field;
		
		if($this->metric == "AVG") {
			$cte = $this->build_cte();
			$from = " FROM benchmark_herds bh INNER JOIN " . $this->db_table . " p ON bh.herd_code = p.herd_code AND bh.recent_" . $report_model->date_field . " = p." . $report_model->date_field;
		}
		else {
			$cte = $this->build_cte();
			$from = " FROM benchmark_herds bh LEFT JOIN " . $this->db_table . " p ON bh.herd_code = p.herd_code AND bh.recent_" . $report_model->date_field . " = p." . $report_model->date_field;
		}
		if(strpos($this->metric, 'QTILE') !== FALSE){
			$where = " WHERE bh.quartile = " . str_replace('QTILE', '', $this->metric);
		}
		
		$avg_fields = $report_model->get_benchmark_fields($arr_fields_to_exclude);
		
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
		$cte_top = '';
		$cte_order_by = '';
		$criteria_date_field = $this->arr_criteria_table[$this->criteria]['date_field'];
		list($table_pre, $table_name) = explode('.', $this->criteria);
		
		if($this->metric == "AVG") $cte_top = "WITH benchmark_herds(herd_code, recent_test_date) AS (SELECT td.herd_code, td.recent_test_date FROM";
		if($this->metric == "TOP10_PCT"){
			$cte_top = "WITH benchmark_herds(herd_code, recent_test_date) AS (SELECT TOP(10)PERCENT td.herd_code, td.recent_test_date FROM";
			$cte_order_by = "ORDER BY " . $this->criteria . " " . $this->arr_criteria_table[$this->criteria]['sort_order'];
		}
		if(strpos($this->metric, 'QTILE') !== FALSE){
			$cte_top = "WITH benchmark_herds(quartile, herd_code, recent_test_date) AS (SELECT NTILE(4) OVER (ORDER BY " . $this->criteria . " " . $this->arr_criteria_table[$this->criteria]['sort_order'] . ") AS quartile, td.herd_code, td.recent_test_date FROM";
		}
		
		$sql = $cte_top . "(
				SELECT i.herd_code, MAX(p.test_date) AS recent_test_date 
				FROM herd.dbo.herd_id h
				LEFT JOIN herd_summary.dbo.herd_inventory i ON h.herd_code = i.herd_code
				LEFT JOIN herd_summary.dbo.herd_rha p ON i.herd_code = p.herd_code AND i.test_date = p.test_date";
		$sql .= " GROUP BY i.herd_code, i.test_date
			HAVING (SELECT MAX(test_date) FROM inventory WHERE herd_code = i.herd_code";

		if(isset($this->herd_size_floor) && isset($this->herd_size_ceiling)) $sql .= " AND hi.milk_cow_cnt BETWEEN " . $this->herd_size_floor . " AND " . $this->herd_size_ceiling;
		if(isset($this->arr_states) && is_array($this->arr_states) && !empty($this->arr_states)) " AND state_prov IN (" . implode(',', $this->arr_states) . ")";
		
		$sql .= ") = i.test_date
		) td";
		if(isset($this->arr_criteria_table[$this->criteria]['join_text'])) {
			$sql .= $this->arr_criteria_table[$this->criteria]['join_text'];
		}
		$sql .= " WHERE DATEDIFF(MONTH, GETDATE(), " . $table_pre . "." . $criteria_date_field . ") < 4 AND " . $this->criteria . " IS NOT NULL " .
			$cte_order_by .
		")";
		return $sql;
	}
}