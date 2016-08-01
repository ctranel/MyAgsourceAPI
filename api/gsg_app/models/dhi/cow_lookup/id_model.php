<?php

/**
 *
* @author ctranel
*
*/
class Id_model extends CI_Model {
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
		->select("breed_code, country_code, cow_id, control_num, visible_id, barn_name, FORMAT(birth_date, 'd') AS birth_date, lact_num, twin_code, sire_breed_code, sire_id, sire_naab, sire_name, dam_control_num, dam_breed_code, dam_country_code, dam_id, dam_name, mgr_dam_control_num, mgr_dam_breed_code, mgr_dam_country_code, mgr_dam_id, mgr_dam_name, mgr_sire_breed_code, mgr_sire_id, mgr_sire_naab, mgr_sire_name, mgrtgr_sire_breed_code, mgrtgr_sire_id, mgrtgr_sire_naab, mgrtgr_sire_name")
// 		->where('herd_code', $herd_code)
// 		->where('serial_num', $serial_num)
		->get("vma.dbo.ufn_animal_id('".$herd_code."',".$serial_num.")")
		->result_array();
	
		if(is_array($arr_ret)){
			return $arr_ret[0];
		}
		return false;
	}
}

?>