<?php

/**
 *
* @author ctranel
*
*/
class Graphs_model extends CI_Model {
	protected $db_group_name; //name of database group
	
	/**
	 */
	function __construct() {
		parent::__construct();
		$this->db_group_name = 'default';
		$this->{$this->db_group_name} = $this->load->database($this->db_group_name, TRUE);
	}

	public function getGraphData($herd_code, $serial_num, $lact_num){
		if(!$lact_num) return FALSE;	
		
		$this->{$this->db_group_name}
			->select('lact_dim, td_milk_lbs, fcm_lbs, ecm_lbs, mlm_lbs, linear_score, pro_pct, fat_pct, snf_pct, mun, scc_cnt')
			->where('herd_code', $herd_code)
			->where('serial_num', $serial_num)
			->where('lact_num', $lact_num)
			->order_by('test_date', 'ASC');

		$arr_ret = $this->{$this->db_group_name}
			->get('vma.dbo.vma_Cow_Lookup_Tests')
			->result_array();
		if(is_array($arr_ret)){
			return $arr_ret;
		}
		return false;
	}
	
	
}
?>