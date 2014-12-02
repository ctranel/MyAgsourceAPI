<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Section_model extends CI_Model {
	public function __construct(){
		parent::__construct();
		$this->db_group_name = 'default';
		$this->{$this->db_group_name} = $this->load->database($this->db_group_name, TRUE);
		$this->tables = $this->config->item('tables', 'ion_auth');
	}

    /**
	 * @method get_sections
	 * @return array of section data
	 * @author ctranel
	 **/
	public function get_sections() {
		$this->db
			->where('s.active', 1)
			->order_by('s.super_section_id', 'asc')
			->order_by('s.list_order', 'asc')
			->from($this->tables['sections'] . ' s');
		return $this->db->get()->result_array();
	}
	

	/**
	 * getByCriteria
	 *
	 * @return array of section data for given user
	 * @author ctranel
	 **/
	public function getByCriteria($where, $join = null){
		$this->db
		->select('s.id, s.parent_id')
		->where($where);
		return $this->get_sections();
	}
	
	
	
	/**
	 * get_sections_by_user
	 *
	 * @return array of section data for given user
	 * @author ctranel
	 **/
	public function get_sections_by_user($user_id){
		$this->db
		->select('s.id, s.name, s.path')
		->where("(s.user_id IS NULL OR s.user_id = '" . $user_id . ")");
		return $this->get_sections();
	}

	/**
	 * get_sections_by_herd
	 *
	 * @return array of section data for given herd
	 * @author ctranel
	 **/
	public function get_sections_by_herd($herd_code){
		$this->db
		->join('users.dbo.sections_reports sr', 's.id = sr.section_id')
		->join('herd.dbo.herd_output ho', "sr.report_code = ho.report_code AND ho.end_date IS NOT NULL AND ho.medium_type_code = 'w'")
		->where($this->tables['sections'] . '.scope_id', 2); // 2 = subscription
		return $this->get_sections();
	}

	/**
	 * @method get_sections_by_scope array
	 * @param string scope
	 * @return array of section data for given user
	 * @author ctranel
	public function get_sections_by_scope($scope) {
		$this->db
		->select($this->tables['sections'] . '.*')
		->join($this->tables['lookup_scopes'], $this->tables['sections'] . '.scope_id = ' . $this->tables['lookup_scopes'] . '.id', 'left')
		->where($this->tables['lookup_scopes'] . '.name', $scope);
		return $this->get_sections();
	}
	 **/
	
	/**
	 * @method get_section_id_by_path()
	 * @param string section path
	 * @return int id of section
	 * @access public
	 *
	public function get_section_id_by_path($section_path){
		$arr_res = $this->db
			->select('id')
			->where('path', $section_path)
			->get($this->tables['sections'])
			->result_array();
		if(is_array($arr_res) && !empty($arr_res)){
			return $arr_res[0]['id'];
		}
		return FALSE;
	}
	 **/

	/**
	 * @method get_sections_select_data()
	 * @return int id of super section
	 * @access public
	 *
	public function get_select_data($parent_section_id){
		$arr_return = array();
		$this->{$this->db_group_name}
		->select('id, name')
		->where($this->tables['sections'] . '.super_section_id', $parent_section_id);
		$tmp = $this->get_sections();
		if(is_array($tmp)){
			$arr_return[0] = 'Select one';
			foreach($tmp as $t){
				$arr_return[$t['id']] = $t['name'];
			}
		}
		return $arr_return;
	}
	 **/
	
	/**
	 * @method get_subscribed_sections_array
	 * @param int $user_id
	 * @param int $parent_section_id
	 * @param string $herd_code
	 * @param boolean has permission to view non-owned herds
	 * @return array of section data for given user
	 * @author ctranel
	 **/
	public function get_subscribed_sections_array($user_id, $parent_section_id, $herd_code, $view_non_own) {
		$tmp_arr_sections = array();
		$this->db
		->where($this->tables['sections'] . '.active', 1)
		->where($this->tables['sections'] . '.scope_id', 2) // 2 = subscription
		->where_in($this->tables['sections'] . '.super_section_id', $parent_section_id);
		//if($this->has_permission("View Unsubscribed Herds")){ //if the logged in user has permission to view reports to which the herd is not subscribed
		if(!$view_non_own){
			if(isset($herd_code) && !empty($herd_code)){
				$tmp_arr_sections = $this->get_sections_by_herd($herd_code);
			}
		}
		else{
			if(!isset($user_id) || !$user_id){
				$user_id = $this->session->userdata('user_id');
			}
			if(isset($user_id) && !empty($user_id)){
				$tmp_arr_sections = $this->get_sections_by_user($user_id);
			}
		}
		return $tmp_arr_sections;
	}
	
	/**
	 * @method herd_is_subscribed
	 * @param int section_id
	 * @param string herd_code
	 * @return array of section data for given user
	 * @author ctranel
	 **/
	public function herd_is_subscribed($section_id, $herd_code) {
		//$this->db->where($this->tables['sections'] . '.id', $section_id);
		//return $this->get_sections();
		return TRUE;
	}
	
	/**
	 * @method get_keyed_section_array
	 *
	 * @param array scope options to include
	 * @return 1d array (id=>name)
	 * @author ctranel
	public function get_keyed_section_array($arr_scope = NULL) {
		$this->db
		->select($this->tables['sections'] . '.id, ' . $this->tables['sections'] . '.name AS name')
		->join($this->tables['lookup_scopes'], $this->tables['sections'] . '.scope_id = ' . $this->tables['lookup_scopes'] . '.id', 'left')
		->order_by('name', 'asc');
		if(isset($arr_scope) && is_array($arr_scope)){
			$this->db->where_in($this->tables['lookup_scopes'] . '.name', $arr_scope);
		}
		$arr_section_obj = $this->get_sections()->result();
		if(is_array($arr_section_obj)) {
			foreach($arr_section_obj as $e){
				$ret_array[$e->id] = $e->name;
			}
			return $ret_array;
		}
		else return false;
	}
	 **/

/***  PAGES *****************************************************/
	
	/**
	 * get_pages
	 * @return array of page data
	 * @author ctranel
	 **/
	private function get_pages() {
		//need to check/adjust user_id to allow showing of default herd when no users are logged in
		$uid = $this->session->userdata('user_id');
		if(!isset($uid) || empty($uid)) $uid = 0;
		return $this->{$this->db_group_name}
			->where($this->tables['pages'] . '.active', 1)
			->where("(" . $this->tables['pages'] . ".user_id IS NULL OR " . $this->tables['pages'] . ".user_id = " . $uid . ")")
			->order_by($this->tables['pages'] . '.list_order')
			->get($this->tables['pages']);
	}

	/**
	 * get_pages_by_criteria
	 * @param associative array of criteria
	 * @return array of section data
	 * @author ctranel
	 **/
	public function get_pages_by_criteria($arr_criteria) {
		$this->{$this->db_group_name}
		->where($arr_criteria);
		return $this->get_pages();
	}

	/**
	 * @method get_pages_select_data()
	 * @return int id of section
	 * @access public
	 *
	 **/
	function get_pages_select_data($section_id){
		$arr_return = array();
		$this->{$this->db_group_name}
		->select('id, name')
		->where($this->tables['pages'] . '.section_id', $section_id);
		$tmp = $this->get_pages()->result_array();
		if(is_array($tmp)){
			$arr_return[0] = 'Select one';
			foreach($tmp as $t){
				$arr_return[$t['id']] = $t['name'];
			}
		}
		return $arr_return;
	}
	
	/**USED IN ORIGINAL ACCESS LOG
	 * get_keyed_page_array
	 *
	 * @return 2d array ([section_id][id]=name)
	 * @author ctranel
	public function get_keyed_page_array() {
		$this->{$this->db_group_name}->select($this->tables['pages'] . '.id, '. $this->tables['pages'] . '.section_id, ' . $this->tables['pages'] . '.name')
		->join($this->tables['sections'], $this->tables['pages'] . '.section_id = ' . $this->tables['sections'] . '.id')
		->order_by($this->tables['sections'] . '.name', 'asc')
		->order_by($this->tables['pages'] . '.name', 'asc');
		$arr_page_obj = $this->get_pages()->result();
		if(is_array($arr_page_obj)) {
			foreach($arr_page_obj as $e){
				$ret_array[$e->section_id][$e->id] = $e->name;
			}
			return $ret_array;
		}
		else return false;
	}
	 **/
	
}
