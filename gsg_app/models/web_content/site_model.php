<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Site_model extends CI_Model {
	public function __construct(){
		parent::__construct();
		$this->db_group_name = 'default';
		$this->{$this->db_group_name} = $this->load->database($this->db_group_name, TRUE);
		$this->tables = $this->config->item('tables', 'ion_auth');
	}

	/**
	 * @method get_blocks
	 * @return array of block data
	 * @author ctranel
	 **/
	public function getNavigationData($root_section = null) {
		$sql = "WITH cteAnchor AS (
					 SELECT TOP 1000 *
					 FROM users.dbo.content_navigation 
					 WHERE section_path IS NOT NULL AND section_parent_id = 73
					 ORDER BY section_order
				), cteRecursive AS (
					SELECT *
					  FROM cteAnchor
					 UNION all 
					 SELECT t.*
					 FROM users.dbo.content_navigation t
					 join cteRecursive r ON r.section_id = t.section_parent_id
				)
				SELECT *
				  FROM cteRecursive";
		return $this->db->query($sql)->result_array();
	}
}
