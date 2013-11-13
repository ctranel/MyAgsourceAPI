<?php
class Herd_model extends CI_Model {

	protected $tables;
	
	public function __construct(){
		parent::__construct();
		//initialize db tables data
		$this->tables  = $this->config->item('tables', 'ion_auth');
	}

	/**
	 * @method herd_authorization_error()
	 * @param string herd code
	 * @param string release code
	 * @return mixed false if there is no error, "herd_release_code" or "herd_code"
	 * 			if there is an error.
	 * @access public
	 *
	 **/
	function herd_authorization_error($herd_code, $herd_release_code){
		//check herd_release_code against herd_code
		$db_tmp_obj = $this->db->select('account_password')
		->where('herd_code', $herd_code)
		->get($this->tables['herds'])
		->row();
		$db_herd_release_code = '';
		if(is_a($db_tmp_obj, 'stdClass')) {
			$db_herd_release_code = $db_tmp_obj->account_password;
			if ($db_herd_release_code != $herd_release_code || $db_herd_release_code == ''){
				return 'account_creation_invalid_herd_release_code';
			}
		}
		else{
			return 'account_creation_invalid_herd_code';
		}
		return false;
	}


	/**
	 * @method get_herds_by_tech_num()
	 * @param string tech num
	 * @return array of stdClass with all herds for given tech num
	 * @access public
	 *
	 **/
	function get_herds_by_tech_num($tech_num){
		$this->db->where('supervisor_num', $tech_num);
		return $this->get_herds();
	}

	/**
	 * get_herds_by_region
	 *
	 * @param string region number
	 * @return array of object herd
	 * @author Chris Tranel
	 **/
	function get_herds_by_region($region_id){
		if(!isset($region_id)) return FALSE;
		if(!is_array($region_id)) $region_id = array($region_id);
		$this->db
		//->join('herds_regions', 'region.id = herds_regions.region_id')
		->where_in($this->tables['herds_regions'] . '.region_id', $region_id);
		return $this->get_herds();
	}

	/**
	 * @method get_herds_by_consultant()
	 * @param int consultant's user id
	 * @return array of stdClass with all herds for given tech num
	 * @access public
	 *
	 **/
	function get_herds_by_consultant($consultant_user_id = FALSE){
		if(!$consultant_user_id) $consultant_user_id = $this->session->userdata('user_id');
		$this->db->join('consultants_herds', 'herd.herd_code = consultants_herds.herd_code')
		->where('consultants_herds.consultant_user_id', $consultant_user_id)
		->where('(consultants_herds.exp_date > CURDATE() OR consultants_herds.exp_date IS NULL)')
		->where('request_status_id', 1);
		return $this->get_herds();
	}


	/**
	 * get_herds_by_criteria
	 *
	 * @param array criteria (field=>value pairs)
	 * @param int limit
	 * @param int offset
	 * @param string order_by
	 * @return object herd
	 * @author Chris Tranel
	 **/
	public function get_herds_by_criteria($criteria=NULL, $limit=NULL, $offset=NULL, $order_by=NULL)
	{
		$this->db->where($criteria);
		//if(is_array($criteria)){
		//	foreach($criteria as $k=>$v){
		//		$this->db->where($k, $v);
		//	}
		//}
		return $this->get_herds($limit, $offset, $order_by);
	}

	/**
	 * update_herds_by_criteria
	 *
	 * @param array field=>value combinations for update data
	 * @param array field=>value combinations for criteria
	 * @return mixed
	 * @author Chris Tranel
	 **/
	public function update_herds_by_criteria($data, $criteria=NULL)
	{
		$this->db->where($criteria);
		return $this->db->update($this->tables['herds'], $data);
	}

	/**
	 * insert_herd
	 *
	 * @param array field=>value combinations
	 * @return mixed
	 * @author Chris Tranel
	 **/
	public function insert_herd($data)
	{
		return $this->db->insert($this->tables['herds'], $data);
	}

	/**
	 * @method get_herds()
	 * @param int limit
	 * @param int offset
	 * @param string sort by field
	 * @return array of stdClass objects with all herds
	 * @access public
	 * @author Chris Tranel
	 **/
	public function get_herds($limit=NULL, $offset=NULL, $order_by='herd_owner')
	{
		$this->db
		->select('h.[herd_code],h.[farm_name],h.[herd_owner],h.[contact_fn],h.[contact_ln],h.[address_1],h.[address_2]
				,h.[city],h.[state],h.[zip_5],h.[zip_4],h.[primary_area_code],h.[primary_phone_num],h.[association_num]
				,h.[dhi_affiliate_num],h.[supervisor_num],h.[owner_privacy],h.[records_release_code]')
		->join($this->tables['herds_regions'], 'h.herd_code = ' . $this->tables['herds_regions'] . '.herd_code', 'LEFT')
		->join($this->tables['regions'], $this->tables['herds_regions'] . '.region_id = ' . $this->tables['regions'] . '.id', 'LEFT')
		->where("h.member_status_code = 'A'");
		//->join($this->tables['companies'], $this->tables['regions'] . '.company_id = ' . $this->tables['companies'] . '.id', 'LEFT');
		
		if(isset($order_by))$this->db->order_by($order_by);
		if (isset($limit) && isset($offset)) $this->db->limit($limit, $offset);
		elseif(isset($limit)) $this->db->limit($limit);
		//if(!$this->as_ion_auth->has_permission('View Other Companies')) $this->db->where($this->tables['regions'] . '.company_id', $this->session->userdata('company_id'));
		$results = $this->db->get($this->tables['herds'] . ' h');
		return $results;
	}

	/**
	 * @method get_herd()
	 * @param string herd code
	 * @return array of stdClass objects with all herds
	 * @access public
	 * @author Chris Tranel
	 **/
	public function get_herd($herd_code)
	{
		$this->db->where($this->tables['herds'] . '.herd_code', $herd_code);
		return $this->get_herds(1,0);
	}

	/**
	 * @method header_info()
	 * @param string herd code
	 * @return array of data for the herd header record
	 * @access public
	 *
	 **/
	public function header_info($herd_code){
		// results query
		$q = $this->db->select("h.herd_code, h.farm_name, h.herd_owner, " . $this->tables['regions'] . ".region_name, supervisor_num, FORMAT(ct.test_date,'MM-dd-yyyy') AS test_date", FALSE)
		->from($this->tables['herds'] . ' h')
		->join('[herd].[dbo].[view_herd_id_curr_test] ct', 'h.herd_code = ' . 'ct.herd_code', 'left')
		->join($this->tables['herds_regions'], 'h.herd_code = ' . $this->tables['herds_regions'] . '.herd_code', 'left')
		->join($this->tables['regions'], $this->tables['herds_regions'] . '.region_id = ' . $this->tables['regions'] . '.id', 'left')
		->where('h.herd_code',$herd_code);
		$ret = $q->get()->result_array();
		if(!empty($ret) && is_array($ret)) return $ret[0];
		else return FALSE;
	} //end function

	/**
	 * @method get_field()
	 * @abstract gets the given field name for the field that is currently loaded
	 * @param string field name
	 * @param string herd code
	 * @return mixed value for given field
	 * @access public
	 *
	 **/
	public function get_field($field_name, $herd_code = FALSE){
		// results query
		if(!$herd_code) $herd_code = $this->session->userdata('herd_code');
		if(strlen($herd_code) != 8) return NULL;
		$q = $this->db
		->select($field_name)
		->from($this->tables['herds'])
		->where($this->tables['herds'] . '.herd_code',$herd_code)
		->limit(1);
		$ret = $q->get()->result_array();
		if(!empty($ret) && is_array($ret)) return $ret[0][$field_name];
		else return FALSE;
	} //end function
	
	/**
	 * @method get_lookup_values()
	 * @param string field to be populated
	 * @return array
	 * @access public
	 * @author Chris Tranel
	 **/
	public function get_lookup_values($data_field)
	{
		$arr_return = array(0=>'Select One');
		$results = $this->db
		->select('id, value, description')
		->get('users.dbo.lookup_' . $data_field)->result_array();
		foreach($results as $r){
			$arr_return[$r['id']] = $r['description'];
		}
		return $arr_return;
	}
	/**
	 * get_pstring_array
	 * @param string herd code
	 * @return 2d array (pstring & publication name)
	 * @author Chris Tranel
	 **/
	public function get_pstring_array($herd_code, $include_all = TRUE) {
		//$this->pstring_group_name = 'pstring';
		$pstring_db = $this->load->database('herd', TRUE);
		$arr_results = $pstring_db->select('pstring, publication_name')
		//->distinct()
		//->where('pstring IS NOT NULL', NULL, FALSE)
		//->where('pstring > 0', NULL, FALSE)
		->where('herd_code', $herd_code)
		->order_by('pstring', 'asc')
		->get('herd.dbo.pstring_definition')
		->result_array();
		
		if($include_all) array_unshift($arr_results, array('pstring'=>0, 'publication_name'=>'All PStrings'));
		return $arr_results;
	}

	/**
	 * herd_is_registered
	 * @param string herd code
	 * @return bool
	 * @author Chris Tranel
	 **/
	public function herd_is_registered($herd_code){
		$arr_results = $this->db->select('herd_code')
		->from('users')
		->join('users_herd_meta', 'users.id = users_herd_meta.user_id')
		->join('users_groups', 'users.id = users_groups.user_id')
		->where('users_herd_meta.herd_code', $herd_code)
		->where('users.active', 1)
		->where('users_groups.group_id', 2)
		->get()
		->result_array();
		if(is_array($arr_results) && !empty($arr_results)) return TRUE;
		else return FALSE;
	}

	/**
	 * get_herd_emails
	 * @param string herd code
	 * @return array of e-mail addresses
	 * @author Chris Tranel
	 **/
	public function get_herd_emails($herd_code){
		$arr_results = $this->db->select('email')
		->from('users')
		->join('users_herd_meta', 'users.id = users_herd_meta.user_id')
		->join('users_groups', 'users.id = users_groups.user_id')
		->where('users_herd_meta.herd_code', $herd_code)
		->where('users.active', 1)
		->where('users_groups.group_id', 2)
		->get()
		->result_array();
		if(is_array($arr_results) && !empty($arr_results)) return $arr_results;
		else return FALSE;
	}

	/**
	 * get_herd_test_dates_7
	 * @param string herd code
	 * @return array of test_dates from rpm.dbo.t13_herd_info
	 * @author Kevin Marshall
	 **/
	
	public function get_herd_test_dates_7($herd_code){
		$arr_results = $this->db->select('test_date_1,test_date_2,test_date_3,test_date_4,test_date_5,test_date_6,test_date_7')
		->from('t13_herd_info')
		->where('herd_code', $herd_code)
		->get()
		->result_array();
		if(is_array($arr_results) && !empty($arr_results)) return $arr_results;
		else return FALSE;
	}

	public function get_test_dates_7_short($herd_code){
		$rpmdb = $this->load->database('developmentRPM', TRUE);
		$arr_results = $rpmdb->select('short_date_1,short_date_2,short_date_3,short_date_4,short_date_5,short_date_6,short_date_7')
		->from('vma_Dates_Last_7_Tests')
		->where('herd_code', $herd_code)
		->get()
		->result_array();
		if(is_array($arr_results) && !empty($arr_results)) return $arr_results;
		else return FALSE;
	}
	
	
	
}
