<?php

/**
 *
 * @author ctranel
 *        
 */
class Events_model extends CI_Model {
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
	
	public function getCowArray($herd_code, $serial_num){
		$arr_ret = $this->{$this->db_group_name}
		->select('i.barn_name, i.visible_id, i.net_merit_$amt AS net_merit_amt, i.tstring, p.curr_lact_num, p.curr_milk_lbs, p.curr_pct_last_milk, p.curr_scc_cnt, p.curr_lact_dim, p.curr_td_proj_305_milk_lbs, p.curr_td_proj_305_fat_lbs, p.curr_td_proj_305_pro_lbs')
		->where('i.herd_code', $herd_code)
		->where('i.serial_num', $serial_num)
		->join('rpm.dbo.report_cow_prod_1 p', 'i.herd_code = p.herd_code AND i.serial_num = p.serial_num')
		->get('rpm.dbo.report_cow_id_1 i')
		->result_array();
		
		if(is_array($arr_ret)){
			return $arr_ret[0];
		}
		return false;
	}
	
	public function getEventArray(){
		
	}
}

?>