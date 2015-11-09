<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Page_model extends CI_Model {
	protected $user_id;
	
	
	public function __construct($user_id){
		parent::__construct();
		$this->user_id = $user_id;
		$this->db_group_name = 'default';
//		$this->db = $this->load->database($this->db_group_name, TRUE);
		$this->tables = $this->config->item('tables', 'ion_auth');
	}

    /**
	 * @method getSections
	 * @return array of section data
	 * @author ctranel
	 **/
	public function getPages() {
		return $this->db
			->select('p.*, s.name AS scope')
			->where('p.active', 1)
			->where("(p.user_id IS NULL OR p.user_id = " . $this->user_id . ")")
			->join('users.dbo.lookup_scopes s', 'p.scope_id = s.id', 'inner')
			->order_by('p.list_order')
			->get($this->tables['pages'] . ' p')
			->result_array();
	}
	

	/**
	 * getPagesByCriteria
	 * @param associative array of criteria
	 * @param 2d array of joins ('table', 'condition')
	 * @return array of section data
	 * @author ctranel
	 **/
	public function getByCriteria($where, $join = null) {
		if(isset($where) && !empty($where)){
			$this->db->where($where);
		}
		if(isset($join) && !empty($join)){
			foreach($join as $j){
				$this->db->join($j['table'], $j['condition']);
			}
		}
		return $this->getPages();
	}
	
	/**
	 * getReports
	 * @param int page id
	 * @param string herd code
	 * @return array of report product ids that are linked to given page
	 * @author ctranel
	 **/
	public function getReport($page_id, $herd_code) {
		$results = $this->db
			->select('si.report_code, si.herd_is_active_trial, si.herd_is_paying')
			->where('si.herd_is_on_test', 1)
			->where('si.herd_code', $herd_code)
			->where('pr.page_id', $page_id)
			->join('users.dbo.pages_reports pr', 'si.report_code = pr.report_code','inner')
			//sorting and taking top row could be considered business logic, but this seems like
			//a clean way to handle it
			->order_by('si.herd_is_paying', 'desc')
			->order_by('si.herd_is_active_trial', 'desc')
			->get('users.dbo.v_user_status_info si')
			->result_array();
		
		if(isset($results) && is_array($results) && count($results) > 0){
			return $results[0];
		}
		return null;
	}
}
