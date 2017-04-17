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
	}
	
	public function getCowArray($herd_code, $serial_num){
		$arr_ret = $this->{$this->db_group_name}->select ('barn_name, visible_id, control_num, curr_lact_num, curr_lact_start_dt AS curr_calving_date')//, list_order_num', net_merit_$amt AS net_merit_amt, tstring, curr_lact_num, curr_milk_lbs, curr_pct_last_milk, curr_scc_cnt, curr_ltd_dim, curr_305_milk_lbs, curr_305_fat_lbs, curr_305_pro_lbs, FORMAT(curr_calving_date, \'d\') AS curr_calving_date')
			->where ('herd_code', $herd_code)
			->where ('serial_num', $serial_num)
			//->join ('rpm.dbo.report_cow_prod_1 p1', 'i.herd_code = p1.herd_code AND i.serial_num = p1.serial_num')
			//->join ('rpm.dbo.report_cow_prod_2 p2', 'i.herd_code = p2.herd_code AND i.serial_num = p2.serial_num')
			->get ('TD.animal.id')
			->result_array();

		if(is_array($arr_ret) && !empty($arr_ret)){
			return $arr_ret[0];
		}
		return false;
	}
	
	public function getEventsArray($herd_code, $serial_num, $curr_calving_date, $show_all_events){
		if(!$show_all_events && isset($curr_calving_date)){
			$this->{$this->db_group_name}->where('event_date >=', $curr_calving_date);
		}
		
		$arr_ret = $this->{$this->db_group_name}
			->select("event_date AS ev_date, FORMAT(event_date, 'd') AS event_date, short_desc, (CASE WHEN srv_sire_name IS NULL THEN srv_sire_naab ELSE CONCAT([srv_sire_naab],' - ',[srv_sire_name]) END) as event_data, srv_sire_naab")
			->where('herd_code', $herd_code)
			->where('serial_num', $serial_num)
			->order_by('ev_date', 'desc')
			->order_by('seq_num', 'desc')
			->get('vma.dbo.vma_Cow_Lookup_Events e')
			->result_array();
		
		if(is_array($arr_ret)){
			return $arr_ret;
		}
		return false;
	}
}

?>