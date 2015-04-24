<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Section_model extends CI_Model {
	public function __construct(){
		parent::__construct();
		$this->db_group_name = 'default';
		$this->{$this->db_group_name} = $this->load->database($this->db_group_name, TRUE);
		$this->tables = $this->config->item('tables', 'ion_auth');
	}

    /**
	 * @method getSections
	 * @return array of section data
	 * @author ctranel
	 **/
	public function getSections() {
		$this->db
			->select('s.id, s.parent_id, s.name, s.description, ls.name AS scope, s.path, s.active, s.default_page_path')
			->where('s.active', 1)
			->order_by('s.parent_id', 'asc')
			->order_by('s.list_order', 'asc')
			->from($this->tables['sections'] . ' s')
			->join('users.dbo.lookup_scopes ls', 's.scope_id = ls.id', 'inner');
		return $this->db->get()->result_array();
	}

	/**
	 * getByCriteria
	 *
	 * @return array of data for given user
	 * @author ctranel
	 **/
	public function getByCriteria($where, $join = null){
		if(isset($where) && !empty($where)){
			$this->db->where($where);
		}
		if(isset($join) && !empty($join)){
			foreach($join as $j){
				$this->db->join($j['table'], $j['condition']);
			}
		}
		return $this->getSections();
	}

	/**
	 * return array of sections to which herd is subscribed (child sections if a parent section is specified)
	 * 
	 * subscription is different in that it fetches content by herd data (i.e. herd output) for users that 
	 * have permission only for subscribed content.  All other scopes are strictly users-based
	 * @method getSubscribedSections
	 * @param int $parent_section_id
	 * @param string $herd_code
	 * @return array of section data for given user
	 * @author ctranel
	 **/
	public function getSubscribedSections($parent_section_id, $herd_code) {
		$tmp_arr_sections = [];
		$this->db
		->join('users.dbo.sections_reports sr', 's.id = sr.section_id', 'inner')
		->join('herd.dbo.herd_output ho', "sr.report_code = ho.report_code AND ho.end_date IS NOT NULL AND ho.medium_type_code = 'w'", 'inner')
		->where('s.scope_id', 2) // 2 = subscription
		->where('ho.herd_code', $herd_code)
		->where('ho.end_date IS NULL')
		->where_in('s.parent_id', $parent_section_id);
	
		
		$tmp_arr_sections = $this->getSections($herd_code);

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
		//join to sections _reports then to herd_output
		$res = $this->db
		->join('users.dbo.sections_reports sr', 's.id = sr.section_id', 'inner')
		->join('herd.dbo.herd_output ho', 'sr.report_code = ho.report_code', 'inner')
		->where('s.scope_id', 2) // 2 = subscription
		->where('ho.herd_code', $herd_code)
		->where('sr.section_id', $section_id)
		->where('ho.end_date IS NULL')
		->getSections();
		
		return (count($res) > 0);
	}
}
