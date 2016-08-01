<?php

/**
 *
* @author ctranel
*
*/
class Progeny_qtile_model extends CI_Model {
	protected $db_group_name; //name of database group
	protected $tables; //array of tables configured in ion_auth config file
	
	/**
	 */
	function __construct() {
		parent::__construct();
		$this->db_group_name = 'default';
		$this->{$this->db_group_name} = $this->load->database($this->db_group_name, TRUE);
	
		$this->tables  = $this->config->item('tables', 'ion_auth');
	}

	public function getProgenyAverages($herd_code, $test_date){
//		$this->output->enable_profiler(TRUE);
		$arr_ret = $this->{$this->db_group_name}
		->select("month_name,calf_due_cnt,avg_net_merit")
		->where('herd_code', $herd_code)
		->where('test_date', $test_date)
		->order_by('month_period_code','asc')
		->get('vma.dbo.vma_GSG_Progeny_Monthly_Avg_NM')
		->result_array();

		if(is_array($arr_ret)){
			return $arr_ret;
		}
		return false;
	}
	
}

?>