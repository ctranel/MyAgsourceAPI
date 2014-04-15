<?php

/**
 *
* @author ctranel
*
*/
class Sire_model extends CI_Model {
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
		$arr_ret = $this->{$this->db_group_name}
		->select("sire_naab, sire_name, sire_country_code, sire_id, sire_breed_code, sire_registered_name, FORMAT(sire_load_date, 'd') AS sire_load_date, sire_pta_milk_lbs, sire_pta_fat_lbs, sire_pta_fat_pct, sire_pta_pro_lbs, sire_pta_pro_pct, sire_fluid_merit_amt, sire_cheese_merit_amt, sire_pta_prod_life_reliab, sire_pta_prod_life, sire_pta_scs_reliab, sire_pta_scs, sire_net_merit_pctile, sire_net_merit_reliab, sire_net_merit_amt, sire_inbreeding_coeff_pct, pgr_sire_primary_naab, pgr_sire_short_ai_name, pgr_sire_registered_name")
		->where('herd_code', $herd_code)
		->where('serial_num', $serial_num)
		->get('vma.dbo.vma_Cow_Lookup_Sire')
		->result_array();
	
		if(is_array($arr_ret)){
			return $arr_ret[0];
		}
		return false;
	}
}

?>