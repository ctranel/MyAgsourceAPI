<?php

/**
 *
* @author ctranel
*
*/
class Heifer_qtile_model extends CI_Model {
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

	public function getHeiferAverages($herd_code, $pstring, $test_date){
		$arr_ret = $this->{$this->db_group_name}
		->select("quartile1,quartile2,quartile3,quartile4")
		->where('herd_code', $herd_code)
		->where('pstring', $pstring)
		->where('test_date', $test_date)
		->get('vma.dbo.vma_GSG_Heifer_Quartile_Averages')
		->result_array();
		if(is_array($arr_ret)){
			return $arr_ret[0];
		}
		return false;
	}
	
	
}

?>