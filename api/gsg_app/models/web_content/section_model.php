<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Section_model extends CI_Model {
	public function __construct(){
		parent::__construct();
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
			->from('users.dbo.sections s')
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
        $parent_section_id = (int)$parent_section_id;
        $herd_code = (int)$herd_code;

		$sql = "
			WITH section_tree AS
				(
					SELECT id, parent_id, name, description, scope_id, path, active, default_page_path, list_order
					FROM users.dbo.sections
					WHERE id IN(
						SELECT DISTINCT p.section_id
						FROM users.dbo.pages p
							INNER JOIN users.dbo.pages_dhi_products pr ON p.id = pr.page_id AND p.active = 1 AND p.scope_id = 2
							INNER JOIN users.dbo.v_user_status_info si ON pr.report_code = si.report_code AND si.herd_code = '" . $herd_code . "' AND (si.herd_is_paying = 1 OR si.herd_is_active_trial = 1)
					)
			
					UNION ALL
			
					SELECT s.id, s.parent_id, s.name, s.description, s.scope_id, s.path, s.active, s.default_page_path, s.list_order
					FROM users.dbo.sections s
						JOIN section_tree st ON st.parent_id = s.id   
				)
			
			SELECT a.*, ls.name AS scope FROM (
				SELECT id, parent_id, name, description, scope_id, path, active, default_page_path, list_order
				FROM section_tree
			
				INTERSECT
			
				SELECT id, parent_id, name, description, scope_id, path, active, default_page_path, list_order
				FROM users.dbo.sections
				WHERE parent_id = " . $parent_section_id . " AND active = 1
			) a
			
			INNER JOIN users.dbo.lookup_scopes ls ON a.scope_id = ls.id
			ORDER BY list_order";
		
		$tmp_arr_sections = $this->db
		->query($sql)
		->result_array();

		return $tmp_arr_sections;
	}

	/**
	 * return array of sections to which herd is subscribed (child sections if a parent section is specified)
	 * 
	 * subscription is different in that it fetches content by herd data (i.e. herd output) for users that 
	 * have permission only for subscribed content.  All other scopes are strictly users-based
	 * @method getPublicSections
	 * @param int $parent_section_id
	 * @param string $herd_code
	 * @return array of section data for given user
	 * @author ctranel
	 **/
	public function getPublicSections($parent_section_id) {
        $parent_section_id = (int)$parent_section_id;

		$sql = "
			WITH section_tree AS
				(
					SELECT id, parent_id, name, description, scope_id, path, active, default_page_path, list_order
					FROM users.dbo.sections
					WHERE id IN(
						SELECT DISTINCT p.section_id
						FROM users.dbo.pages p
						WHERE p.active = 1 AND p.scope_id = 1
					)
			
					UNION ALL
			
					SELECT s.id, s.parent_id, s.name, s.description, s.scope_id, s.path, s.active, s.default_page_path, s.list_order
					FROM users.dbo.sections s
						JOIN section_tree st ON st.parent_id = s.id   
				)
			
			SELECT a.*, ls.name AS scope FROM (
				SELECT id, parent_id, name, description, scope_id, path, active, default_page_path, list_order
				FROM section_tree
			
				INTERSECT
			
				SELECT id, parent_id, name, description, scope_id, path, active, default_page_path, list_order
				FROM users.dbo.sections
				WHERE parent_id = " . $parent_section_id . " AND active = 1
			) a
			
			INNER JOIN users.dbo.lookup_scopes ls ON a.scope_id = ls.id
			ORDER BY list_order";
		
		$tmp_arr_sections = $this->db
		->query($sql)
		->result_array();

		return $tmp_arr_sections;
	}

    /**
     * return array of sections to which herd is subscribed (child sections if a parent section is specified)
     *
     * subscription is different in that it fetches content by herd data (i.e. herd output) for users that
     * have permission only for subscribed content.  All other scopes are strictly users-based
     * @method getPublicSections
     * @param int $parent_section_id
     * @param string $herd_code
     * @return array of section data for given user
     * @author ctranel
     **/
    public function getSectionsByUser($parent_section_id, $user_id) {
        $parent_section_id = (int)$parent_section_id;

        $sql = "
			WITH section_tree AS
				(
					SELECT id, parent_id, name, description, scope_id, path, active, default_page_path, list_order
					FROM users.dbo.sections
	                WHERE active = 1 AND (user_id IS NULL OR user_id = " . $user_id . ")
			
					UNION ALL
			
					SELECT s.id, s.parent_id, s.name, s.description, s.scope_id, s.path, s.active, s.default_page_path, s.list_order
					FROM users.dbo.sections s
						JOIN section_tree st ON st.parent_id = s.id AND s.active = 1 AND (s.user_id IS NULL OR s.user_id = " . $user_id . ")
				)
			
			SELECT a.id, a.name AS scope FROM (
				SELECT id, parent_id, name, description, scope_id, path, active, default_page_path, list_order
				FROM section_tree
			
				UNION
			
				SELECT id, parent_id, name, description, scope_id, path, active, default_page_path, list_order
				FROM users.dbo.sections
				WHERE parent_id = " . $parent_section_id . " AND active = 1 AND (user_id IS NULL OR user_id = " . $user_id . ")
			) a
			
			INNER JOIN users.dbo.lookup_scopes ls ON a.scope_id = ls.id
			ORDER BY a.path";
//die($sql);
        $tmp_arr_sections = $this->db
            ->query($sql)
            ->result_array();

        return $tmp_arr_sections;
    }
}
