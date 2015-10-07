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
}
