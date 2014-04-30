<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* -----------------------------------------------------------------
*  @description: Benchmark data access
*  @author: ctranel
*  @date: Nov 15, 2013
*
*
*  -----------------------------------------------------------------
*/
class Benchmark_model extends CI_Model {
	//public function __construct(){
	//	parent::construct();
	//}

	/**
	 * @method get_benchmark_fields()
	 * @param array fields to exclude from the returned value
	 * @return array of data fields for the current primary table, excluding those fields in the param
	 * @access protected
	 *
	 **/
	public function get_benchmark_fields($db_table, $arr_excluded_fields = NULL){
		$sql = "SELECT CAST ((select ',AVG(CAST('+quotename(C.name)+' AS DECIMAL(12,0))) AS '+quotename(C.name)
         from sys.columns as C
         where C.object_id = object_id('" . $db_table . "')";
        if(is_array($arr_excluded_fields) && !empty($arr_excluded_fields)) $sql .= " and C.name NOT IN('" . implode("','", $arr_excluded_fields) . "')";// AND C.name NOT LIKE 'cnt%'";
        $sql .= " AND TYPE_NAME(C.user_type_id) NOT IN('char','smalldatetime','varchar','date')";
		$sql .= "for xml path('')) AS text) AS fields";

        $results = $this->{$this->db_group_name}
		->query($sql)
		->result_array();
		return substr($results[0]['fields'], 1);
	}
	
	/**
	 * get_user_herd_settings
	 *
	 * @return array of user-herd settings
	 * @author ctranel
	 **/
	public function getHerdBenchmarkSettings(){
		//REMOVE IF WE DECIDE TO STORE PREFERENCES
		$arr_sess_benchmarks = $this->session->userdata('benchmarks');
		if(isset($arr_sess_benchmarks) && !empty($arr_sess_benchmarks)){
			$arr_tmp['metric'] = $arr_sess_benchmarks['metric'];
			$arr_tmp['criteria'] = $arr_sess_benchmarks['criteria'];
			$arr_tmp['arr_states'] = $arr_sess_benchmarks['arr_states'];
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
			
	public function getBenchmarkData($bench_sql){
		$arr_benchmarks = $this->db->query($bench_sql)->result_array();
		$arr_benchmarks = $arr_benchmarks[0];
	}
}