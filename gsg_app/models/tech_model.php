<?php
class Tech_model extends CI_Model {

	protected $tables;
	
	public function __construct(){
		parent::__construct();
		//initialize db tables data
		$this->tables  = $this->config->item('tables', 'ion_auth');
	}

	/**
	 * get_techs_by_region
	 *
	 * @param array/string region number
	 * @return array of object tech
	 * @author ctranel
	 **/
	function get_techs_by_region($region_arr_in, $limit = NULL){
		if (!isset($region_arr_in) or empty($region_arr_in)) {
			return FALSE;
		}	
		// incoming array might be a multi-dimentional array. If so, need to flatten it into simple array before it can be used in the where clause.
		$region_arr_in = array_keys($region_arr_in);
		
		if(!is_array($region_arr_in)) $region_arr_in = array($region_arr_in);
		$this->db->where_in('t.association_num', $region_arr_in);
		return $this->get_techs($limit);
	}

*
	 *  get_techs_by_criteria
	 *
	 * @param array criteria (field=>value pairs)
	 * @param int limit
	 * @param int offset
	 * @param string order_by
	 * @return array or tech arrays
	 * @author ctranel
	 **/
	public function get_techs_by_criteria($criteria=NULL, $limit=NULL, $offset=NULL, $order_by=NULL)
	{
		$this->db->where($criteria);
		return $this->get_techs($limit, $offset, $order_by);
	}

*
	 *  get_tech_by_herd
	 *
	 * @param string herd_code
	 * @return array techs
	 * @author ctranel
	 **/
	public function get_tech_by_herd($herd_code)
	{
		$this->db->where('h.herd_code', $herd_code)
			->join('herd.dbo.herd_id h', 'h.supervisor_num = CONCAT(t.[affiliate_num], t.[supervisor_num])');
		$arr_return = $this->get_techs(1);
		if(isset($arr_return[0])) return $arr_return[0];
		else return FALSE;
	}

	/**
	 * update_techs_by_criteria
	 *
	 * @param array field=>value combinations for update data
	 * @param array field=>value combinations for criteria
	 * @return mixed
	 * @author ctranel
	public function update_techs_by_criteria($data, $criteria=NULL)
	{
		$this->db->where($criteria);
		return $this->db->update($this->tables['dhi_supervisors'], $data);
	}
	 **/

	/**
	 * insert_tech
	 *
	 * @param array field=>value combinations
	 * @return mixed
	 * @author ctranel
	 **/
	public function insert_tech($data)
	{
		return $this->db->insert($this->tables['users_dhi_supervisors'], $data);
	}

	/**
	 * @method get_techs()
	 * @param int limit
	 * @param int offset
	 * @param string sort by field
	 * @return array of arrays with all techs
	 * @access public
	 * @author ctranel
	 **/
	public function get_techs($limit=NULL, $offset=NULL, $order_by='t.last_name')
	{
		$this->db
		->select("t.[account_num], t.[association_num], t.[supervisor_num],t.[first_name],t.[last_name],t.[voice_mail_num], CONCAT(hp.area_code, '-', hp.phone_num) AS home_phone, CONCAT(hp.area_code, '-', hp.phone_num) AS cell_phone")
		->where('t.status_code', 'A')
		->join('address.dbo.phone hp', 't.account_num = hp.account_num AND hp.phone_type_code = 1', 'left')
		->join('address.dbo.phone cp', 't.account_num = cp.account_num AND cp.phone_type_code = 4', 'left');
		
		if(isset($order_by))$this->db->order_by($order_by);
		if (isset($limit) && isset($offset)) $this->db->limit($limit, $offset);
		elseif(isset($limit)) $this->db->limit($limit);
		$results = $this->db->get($this->tables['dhi_supervisors'] . ' t')->result_array();
		return $results;
	}

	/**
	 * @method get_tech()
	 * @param string tech account num
	 * @return 1D array of tech data
	 * @access public
	 * @author ctranel
	 **/
	public function get_tech($account_num)
	{
		$this->db->where($this->tables['dhi_supervisors'] . '.account_num', $account_num);
		return $this->get_techs(1,0);
	}


	/**
	 * @method get_field()
	 * @abstract gets the given field name for the field that is currently loaded
	 * @param string field name
	 * @param string account_num
	 * @return mixed value for given field
	 * @access public
	 *
	 **/
	public function get_field($field_name, $account_num){
		if(strlen($account_num) != 8) return NULL;
		$q = $this->db
		->select($field_name)
		->from($this->tables['dhi_supervisors'])
		->where('account_num',$account_num)
		->limit(1);
		$ret = $q->get()->result_array();
		if(!empty($ret) && is_array($ret)) return $ret[0][$field_name];
		else return FALSE;
	} //end function
}
