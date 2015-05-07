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
		$arr_ret = $this->{$this->db_group_name}->select ("i.barn_name, i.visible_id, i.net_merit_\$amt AS net_merit_amt, i.tstring, p2.curr_lact_num, p1.curr_milk_lbs, p1.curr_pct_last_milk, p1.curr_scc_cnt, p2.curr_ltd_dim, p2.curr_305_milk_lbs, p2.curr_305_fat_lbs, p2.curr_305_pro_lbs, FORMAT(p2.curr_calving_date, 'd') AS curr_calving_date")
			->where ('i.herd_code', $herd_code)
			->where ('i.serial_num', $serial_num)
			->join ('rpm.dbo.report_cow_prod_1 p1', 'i.herd_code = p1.herd_code AND i.serial_num = p1.serial_num')
			->join ('rpm.dbo.report_cow_prod_2 p2', 'i.herd_code = p2.herd_code AND i.serial_num = p2.serial_num')
			->get ('rpm.dbo.report_cow_id_1 i')
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