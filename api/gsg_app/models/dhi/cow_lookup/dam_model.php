<?php

/**
 *
* @author ctranel
*
*/
class Dam_model extends CI_Model {
	protected $db_group_name; //name of database group
	
	/**
	 */
	function __construct() {
		parent::__construct();
		$this->db_group_name = 'default';
		$this->{$this->db_group_name} = $this->load->database($this->db_group_name, TRUE);
	}

	public function getCowArray($herd_code, $serial_num){
		$arr_ret = $this->{$this->db_group_name}
		->select("control_num, list_order AS list_order_num, barn_name, visible_id, dam_control_num, dam_breed_code, dam_id, dam_name, mgr_dam_control_num, mgr_dam_breed_code, mgr_dam_id, mgr_dam_name, mgr_sire_breed_code, mgr_sire_id, mgr_sire_name, mgr_sire_naab, mgrtgr_sire_breed_code, mgrtgr_sire_id, mgrtgr_sire_naab, mgrtgr_sire_name, dam_serial_num")
		//->join('animal.dbo.view_ped_dam_life_pta d', 'c.herd_code = d.herd_code AND c.serial_num = d.serial_num', 'left')
		//->join('animal.dbo.view_pedigree_mgr_sire_pta s', 'c.herd_code = s.herd_code AND c.serial_num = s.serial_num', 'left')
		->where('herd_code', $herd_code)
		->where('serial_num', $serial_num)
		->get('vamTD.dbo.vma_Cow_Lookup_ID')
		->result_array();
	
		if(is_array($arr_ret) && !empty($arr_ret)){
			return $arr_ret[0];
		}
		return false;
	}
}

?>