<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'libraries/MssqlUtility.php';

use \myagsource\MssqlUtility;

/* -----------------------------------------------------------------
*  @description: Benchmark data access
*  @author: ctranel
*
*
*  -----------------------------------------------------------------
*/

require_once(APPPATH . 'models/Settings/settings_model.php');


class Benchmark_model extends Settings_model {
	//db object for views
	protected $bench_criteria_database;
	
	public function __construct(){
		parent::__construct();
	}

	/**
	 * @method get_select_sql()
	 * @param string table from which benchmarks are pulled
	 * @param array fields to exclude from the returned value
	 * @return array of data fields for the current primary table, excluding those fields in the param
	 * @access protected
	 *
	 **/
	public function get_select_sql($report_fields, $arr_excluded_fields = NULL){//$db_table){
        $text = '';

        foreach($report_fields as $f){
            if($f->isDisplayed() && $f->isNumeric() && in_array($f->dbFieldName(), $arr_excluded_fields) === false){
                $base_field_name = $f->dbFieldName();
                if($f->isAggregate()){
                    $base_field_name = str_replace(strtolower($f->aggregate()) . '_', '', $base_field_name);
                }

                $text .= ",CAST(ROUND(AVG(CAST([" . $base_field_name . "] AS DECIMAL(12,4))), " . $f->decimalScale() . ") AS DECIMAL(10, " . $f->decimalScale() . ")) AS [";
                $text .= $f->dbFieldName() . ']';
            }
        }

		return substr($text, 1);
	}
	
	public function getBenchmarkData($bench_sql){
        //$bench_sql = MssqlUtility::escape($bench_sql);

		$arr_benchmarks = $this->db->query($bench_sql)->result_array();
		return $arr_benchmarks;
	}
	
	/**
	 * @description builds sql based on object variables
	 * @param db_table object
	 * @param string sql for avg fields
     * @param array of criteria
     * @param string db table name of benchmark pool
     * @param string metric
     * @param int herd size floor
     * @param int herd size ceiling
     * @param array of strings (breeds)
	 * @param array of strings arr_group_by (db field names)
	 * @return string
	 * @author ctranel
	 **/
	public function build_benchmark_query($db_table, $avg_fields, $arr_criteria_data, $herd_benchmark_pool_table, $metric, $herd_size_floor, $herd_size_ceiling, $arr_breeds, $arr_group_by = NULL){
        $avg_fields = MssqlUtility::escape($avg_fields);
        if(isset($arr_criteria_data) && is_array($arr_criteria_data)){
            array_walk_recursive($arr_criteria_data, function(&$v, $k){return MssqlUtility::escape($v);});
        }
        $herd_benchmark_pool_table = MssqlUtility::escape($herd_benchmark_pool_table);
        $metric = MssqlUtility::escape($metric);
        $herd_size_floor = (int)$herd_size_floor;
        $herd_size_ceiling = (int)$herd_size_ceiling;
        if(isset($arr_breeds) && is_array($arr_breeds)){
            array_walk_recursive($arr_breeds, function(&$v, $k){return MssqlUtility::escape($v);});
        }
        if(isset($arr_group_by) && is_array($arr_group_by)){
            array_walk_recursive($arr_group_by, function(&$v, $k){return MssqlUtility::escape($v);});
        }

        $sql = '';
		$cte = '';
		$addl_select_fields = '';
		$from = '';
		$where = '';
		$group_by = '';
		$order_by = '';

		if($metric == "AVG") {
			$cte = $this->build_cte($arr_criteria_data, $herd_benchmark_pool_table, $metric, $herd_size_floor, $herd_size_ceiling, $arr_breeds);
			$from = " FROM benchmark_herds bh INNER JOIN " . $db_table->full_table_name() . " p ON bh.herd_code = p.herd_code";
		}
		else {
			$cte = $this->build_cte($arr_criteria_data, $herd_benchmark_pool_table, $metric, $herd_size_floor, $herd_size_ceiling, $arr_breeds);
			$from = " FROM benchmark_herds bh INNER JOIN " . $db_table->full_table_name() . " p ON bh.herd_code = p.herd_code";
		}
		if($db_table->field_exists('test_date')){
			$from .= " AND bh.test_date = p.test_date";
		}
		if(strpos($metric, 'QTILE') !== FALSE){
			$where = " WHERE bh.quartile = " . str_replace('QTILE', '', $metric);
		}
		if($db_table->field_exists('pstring')){
			if($where != ''){
				$where .= ' AND';
			}
			else{
				$where .= ' WHERE';
			}
			$where .= ' p.pstring = 0';// . $pstring;
		}
		//include additional key fields
		if(isset($arr_group_by) && is_array($arr_group_by) && count($arr_group_by) > 0){
			$group_by = " GROUP BY p." . $arr_group_by[0];
			$order_by = " ORDER BY p." . $arr_group_by[0];
			$addl_select_fields = $arr_group_by[0] . ',';
			$high_index = (count($arr_group_by) - 1);
			for($i=1; $i<=$high_index; $i++){
				$addl_select_fields .= $arr_group_by[$i] . ',';
				$group_by .= ", p." . $arr_group_by[$i];
				$order_by .= ", p." . $arr_group_by[$i];
			}
		}
		$sql = $cte . "SELECT COUNT(1) AS cnt_herds, " . $addl_select_fields. $avg_fields . $from . $where . $group_by . $order_by;
//print($sql);
		return $sql;
	}
	
	protected function build_cte($arr_criteria_data, $herd_benchmark_pool_table, $metric, $herd_size_floor, $herd_size_ceiling, $arr_breeds){
		$sql = '';
		$cte_qualifier = '';
		$cte_order_by = '';
		$cte_fields = 'herd_code, test_date';
	
		if($metric == 'AVG') $cte_qualifier = '';
		if($metric == 'TOP10_PCT'){
			$cte_qualifier = 'TOP(10)PERCENT ';
			$cte_order_by = ' ORDER BY ' . $arr_criteria_data['field'] . ' ' . $arr_criteria_data['sort_order'];
		}
		if($metric == 'TOP20_PCT'){
			$cte_qualifier = 'TOP(20)PERCENT ';
			$cte_order_by = ' ORDER BY ' . $arr_criteria_data['field'] . ' ' . $arr_criteria_data['sort_order'];
		}
		if(strpos($metric, 'QTILE') !== FALSE){
			$cte_fields = 'quartile, ' . $cte_fields;
			$cte_qualifier = 'NTILE(4) OVER (ORDER BY ' . $arr_criteria_data['field'] . ' ' . $arr_criteria_data['sort_order'] . ') AS quartile, ';
		}
	
		$sql =  'WITH benchmark_herds(' . $cte_fields . ') AS (SELECT ' . $cte_qualifier . 'herd_code, test_date FROM ' . $herd_benchmark_pool_table;
	
		$sql .= ' WHERE test_date > DATEADD(MONTH, -4, GETDATE()) AND ' . $arr_criteria_data['field'] . ' IS NOT NULL ';
//the following line should not be necessary if we eliminate records where pstring != 0 from the table
		$sql .= ' AND pstring = 0 ';
		if(isset($arr_breeds) && is_array($arr_breeds) && !empty($arr_breeds)){
			$sql .= " AND breed_code IN ('" . implode("','", $arr_breeds) . "')";
			if(in_array('HO', $arr_breeds)){
				if(isset($herd_size_floor) && isset($herd_size_ceiling)){
					$sql .= ' AND rha_cow_cnt BETWEEN ' . $herd_size_floor . ' AND ' . $herd_size_ceiling;
				}
			}
		}
	
		$sql .= $cte_order_by;
		$sql .= ')';
		return $sql;
	}

    /**
     * @method getSettingsData()
     * @param string herd_code
     * @return array of data fields for the current primary table, excluding those fields in the param
     * @access protected
     *
     **/
    public function getSettingsData($user_id, $herd_code){
        $user_id = (int)$user_id;
        $herd_code = MssqlUtility::escape($herd_code);

        if(isset($user_id) && $user_id != FALSE){
            $this->db
                ->join('users.setng.user_herd_settings uhs', "s.id = uhs.setting_id AND (uhs.user_id = " . $user_id . " OR uhs.user_id IS NULL) AND (uhs.herd_code = '" . $herd_code . "' OR uhs.herd_code IS NULL)", 'left');
        }
        else{
            $this->db
                ->join('users.setng.user_herd_settings uhs', "s.id = uhs.setting_id AND uhs.user_id IS NULL AND (uhs.herd_code = '" . $herd_code . "' OR uhs.herd_code IS NULL)", 'left');
        }
        $ret = $this->db
            ->select('s.id, s.name, s.label, uhs.value, s.default_value, c.name AS control_type')
            ->from('users.setng.settings s')
            ->join('users.frm.control_types c', "s.type_id = c.id AND s.id IN(1,2,3,4)", 'inner')
            ->get()
            ->result_array();

        return $ret;
    }
}
