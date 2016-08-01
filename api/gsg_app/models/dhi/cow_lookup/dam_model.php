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
		->select("c.dam_control_num, c.dam_breed_code, CASE WHEN c.dam_id IS NOT NULL THEN CONCAT(c.dam_country_code, c.dam_id) END AS dam_id, c.dam_name, c.mgr_dam_control_num, c.mgr_dam_breed_code, CASE WHEN c.mgr_dam_id IS NOT NULL THEN CONCAT(c.mgr_dam_country_code, c.mgr_dam_id) END AS mgr_dam_id, c.mgr_dam_name, c.mgr_sire_breed_code, CASE WHEN c.mgr_sire_id IS NOT NULL THEN CONCAT(c.mgr_sire_country_code, c.mgr_sire_id) END AS mgr_sire_id, c.mgr_sire_name, s.mgr_sire_primary_naab, c.mgrtgr_sire_breed_code, CASE WHEN c.mgrtgr_sire_id IS NOT NULL THEN CONCAT(c.mgrtgr_sire_country_code, c.mgrtgr_sire_id) END AS mgrtgr_sire_id, c.mgrtgr_sire_naab, c.mgrtgr_sire_name, c.dam_serial_num")
		//->join('animal.dbo.view_ped_dam_life_pta d', 'c.herd_code = d.herd_code AND c.serial_num = d.serial_num', 'left')
		->join('animal.dbo.view_pedigree_mgr_sire_pta s', 'c.herd_code = s.herd_code AND c.serial_num = s.serial_num', 'left')
		->where('c.herd_code', $herd_code)
		->where('c.serial_num', $serial_num)
		->get('animal.dbo.view_pedigree_life_pta c')
		->result_array();
	
		if(is_array($arr_ret) && !empty($arr_ret)){
			return $arr_ret[0];
		}
		return false;
	}
}

?>