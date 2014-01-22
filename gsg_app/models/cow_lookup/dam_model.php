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
		->select("c.dam_control_num, c.dam_breed_code, CONCAT(c.dam_country_code, c.dam_id) AS dam_id, c.dam_name, d.visible_id, FORMAT(d.birth_date, 'd') AS birth_date, c.mgr_dam_control_num, c.mgr_dam_breed_code, CONCAT(c.mgr_dam_country_code, c.mgr_dam_id) AS mgr_dam_id, c.mgr_dam_name, c.mgr_sire_breed_code, CONCAT(c.mgr_sire_country_code, c.mgr_sire_id) AS mgr_sire_id, c.mgr_sire_name, s.mgr_sire_primary_naab, c.mgrtgr_sire_breed_code, CONCAT(c.mgrtgr_sire_country_code, c.mgrtgr_sire_id) AS mgrtgr_sire_id, c.mgrtgr_sire_naab, c.mgrtgr_sire_name, d.me_avg_lbs_dev_milk, d.net_merit_\$amt AS net_merit_amt")
		->join('animal.dbo.view_ped_dam_life_pta d', 'c.herd_code = d.herd_code AND c.serial_num = d.serial_num')
		->join('animal.dbo.view_pedigree_mgr_sire_pta s', 'c.herd_code = s.herd_code AND c.serial_num = s.serial_num')
		->where('c.herd_code', $herd_code)
		->where('c.serial_num', $serial_num)
		->get('animal.dbo.view_pedigree_life_pta c')
		->result_array();
	
		if(is_array($arr_ret)){
			return $arr_ret[0];
		}
		return false;
	}
}

?>