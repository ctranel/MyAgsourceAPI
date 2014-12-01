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
			->where($this->tables['sections'] . '.active', 1)
			->order_by('super_section_id', 'asc')
			->order_by('list_order', 'asc')
			->from($this->tables['sections']);
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
		->select($this->tables['sections'] . '.id, ' . $this->tables['sections'] . '.parent_id')
		->where($where);
		return $this->get_sections();
	}
	
	
	
	/**
	 * get_sections_by_user
	 *
	 * @return array of section data for given user
	 * @author ctranel
	public function get_sections_by_user($user_id){
		$this->db
		->select($this->tables['sections'] . '.id, ' . $this->tables['sections'] . '.name, ' . $this->tables['sections'] . '.path')
		->where("(" . $this->tables['sections'] . '.user_id IS NULL OR ' . $this->tables['sections'] . '.user_id = ' . $user_id . ")");
		return $this->get_sections();
	}
	 **/

	/**
	 * get_sections_by_herd
	 *
	 * @return array of section data for given herd
	 * @author ctranel
	public function get_sections_by_herd(){
		$this->db
		->where($this->tables['sections'] . '.scope_id', 2); // 2 = subscription
		return $this->get_sections();
	}
	 **/

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
	 **/
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
	 **/
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
	
/***  BLOCKS *****************************************************/
	/**
	 * @method get_blocks
	 * @return array of block data
	 * @author ctranel
	 **/
	public function get_blocks() {
		$this->db
			->select('name,[description],url_segment,display_type_id')
			->where($this->tables['blocks'] . '.active', 1)
			->order_by('list_order', 'asc')
			->from($this->tables['blocks']);
		return $this->db->get()->result_array();
	}
	
	/**
	 * get_blocks
	 * @return array of section data
	 * @author ctranel
	private function get_blocks() {
		return $this->{$this->db_group_name}
			->where($this->tables['blocks'] . '.active', 1)
			->get($this->tables['blocks']);
	}
	 **/
	
	/**
	 * @method get_blocks_by_section array
	 * @param int section
	 * @return array of block for given section
	 * @author ctranel
	public function get_blocks_by_section($section, $display = NULL) {
		$this->db
			->join($this->tables['pages_blocks'], $this->tables['blocks'] . '.id = ' . $this->tables['pages_blocks'] . '.block_id', 'left')
			->join($this->tables['pages'], $this->tables['pages_blocks'] . '.page_id = ' . $this->tables['pages'] . '.id', 'left')
			->join($this->tables['lookup_display_types'], $this->tables['blocks'] . '.display_type_id = ' . $this->tables['lookup_display_types'] . '.id', 'left')
			->where($this->tables['pages'] . '.section_id', $section);
		if(isset($display)) $this->db->where($this->tables['lookup_display_types'] . '.name', $display);
		return $this->get_blocks();
	}
	 **/

	/**
	 * get_block_display_types
	 * @return array of section data
	 * @author ctranel
	 **/
	public function get_block_display_types() {
		return $this->{$this->db_group_name}
			//->where($this->tables['lookup_display_types'] . '.active', 1)
			->get($this->tables['lookup_display_types']);
	}
	
	/**
	 * get_block_links
	 * @param int section id
	 * @return array of block info keyed by url_segment
	 * @author ctranel
	 **/
	public function get_block_links($section_id = NULL) {
		$arr_return = array();
		if(isset($section_id)) $this->{$this->db_group_name}->where('p.section_id', $section_id);
		$result = $this->{$this->db_group_name}
		->select("p.id AS page_id, b.id, p.section_id, b.url_segment, b.name, ct.name AS chart_type, b.description, p.url_segment AS page, p.name AS page_name, CASE WHEN dt.name LIKE '%chart' THEN 'chart' ELSE dt.name END AS display_type,s.path AS section_path, b.max_rows, b.cnt_row, b.sum_row, b.avg_row, b.bench_row, pf.db_field_name AS pivot_db_field, b.is_summary")
		->join($this->tables['pages'] . ' AS p', 'p.section_id = s.id', 'left')
		->join($this->tables['pages_blocks'] . ' AS pb', 'p.id = pb.page_id', 'left')
		->join($this->tables['blocks'] . ' AS b', 'pb.block_id = b.id', 'left')
		->join($this->tables['lookup_display_types'] . ' AS dt', 'b.display_type_id = dt.id', 'left')
		->join('users.dbo.lookup_chart_types AS ct', 'b.chart_type_id = ct.id', 'left')
		->join('users.dbo.db_fields AS pf', 'pf.id = b.pivot_db_field', 'left')
		//->where($this->tables['blocks'] . '.display IS NOT NULL')
		->where('b.url_segment IS NOT NULL')
		->order_by('s.list_order', 'asc')
		->order_by('p.list_order', 'asc')
		->order_by('pb.list_order', 'asc')
		->get($this->tables['sections'] . ' AS s')->result_array();
		if(is_array($result) && !empty($result)){
			foreach($result as $r){
				$arr_return[$r['page']]['page_id'] = $r['page_id'];
				$arr_return[$r['page']]['name'] = $r['page_name'];
				if(empty($r['url_segment']) === FALSE){
					$arr_return[$r['page']]['blocks'][$r['url_segment']] = array(
						'id'=>$r['id'],
						'section_id'=>$r['section_id'],
						'name'=>$r['name'],
						'description'=>$r['description'],
						'url_segment'=>$r['url_segment'],
						'section_path'=>$r['section_path'],
						'display_type'=>$r['display_type'],
						'chart_type'=>$r['chart_type'],
						'max_rows'=>$r['max_rows'],
						'cnt_row'=>$r['cnt_row'],
						'sum_row'=>$r['sum_row'],
						'avg_row'=>$r['avg_row'],
						'bench_row'=>$r['bench_row'],
						'is_summary'=>$r['is_summary'],
						'pivot_db_field'=>$r['pivot_db_field']
					);
				} 
				else	{
					$arr_return[$r['page']]['blocks'][$r['url_segment']] = array(
						'id'=>$r['id'],
						'section_id'=>$r['section_id'],
						'name'=>$r['name'],
						'description'=>$r['description'],
						'url_segment'=>$r['url_segment'],
						'is_summary'=>$r['is_summary'],
						'section_path'=>$r['section_path']
					);
				}
 			}
 			return $arr_return;
		}
		else return FALSE;
	}
	
/***  CHART *****************************************************/
	
	/**
	 * get_chart_display_types
	 * @return array of section data
	 * @author ctranel
	 **/
	public function get_chart_display_types() {
		return $this->{$this->db_group_name}
			//->where($this->tables['lookup_chart_types'] . '.active', 1)
			->get($this->tables['lookup_chart_types']);
	}
}
