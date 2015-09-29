<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Navigation_model extends CI_Model {
	public function __construct(){
		parent::__construct();
		$this->tables = $this->config->item('tables', 'ion_auth');
	}

	/**
	 * return array of sections to which herd is subscribed (child sections if a parent section is specified)
	 * 
	 * subscription is different in that it fetches content by herd data (i.e. herd output) for users that 
	 * have permission only for subscribed content.  All other scopes are strictly users-based
	 * 
	 * @return array of section and page data
	 * @author ctranel
	 **/
	public function getAllContent() {
//order by parent_id, list_order
//don't need scope name here
		$sql = "
			SELECT * FROM (
				SELECT 999999 AS id, p.section_id AS parent_id, p.name, p.description, ls.name AS scope, p.path, p.active, p.path AS default_page_path, p.list_order
				FROM users.dbo.pages p
					INNER JOIN users.dbo.lookup_scopes ls ON p.scope_id = ls.id
				WHERE p.active = 1
			
				UNION ALL
						
				SELECT s.id, s.parent_id, s.name, s.description, ls.name AS scope, s.path, s.active, s.default_page_path, s.list_order
				FROM users.dbo.sections s
					INNER JOIN users.dbo.lookup_scopes ls ON s.scope_id = ls.id
				WHERE s.active = 1
			) a
			ORDER BY parent_id, list_order
		";
		
		$tmp_arr_sections = $this->db
		->query($sql)
		->result_array();

		return $tmp_arr_sections;
	}

	/**
	 * getContentByScope
	 * 
	 * @param array $scope names
	 * @return array of section and page data for given user
	 * @author ctranel
	 **/
	public function getContentByScope($scopes) {
		if(empty($scopes)){
			return false;
		}
		if(!is_array($scopes)){
			$scopes = [$scopes];
		}
		$scope_text = "'" . implode("','", $scopes) . "'";
		$sql = "
			WITH section_tree AS
			(
				SELECT id, parent_id, name, description, scope_id, path, active, default_page_path, list_order
				FROM users.dbo.sections
				WHERE id IN(
					SELECT DISTINCT p.section_id
						FROM users.dbo.pages p
						INNER JOIN users.dbo.lookup_scopes ls ON p.scope_id = ls.id
						WHERE p.active = 1 AND ls.name IN(" . $scope_text . ")
				)
						
				UNION ALL
						
				SELECT s.id, s.parent_id, s.name, s.description, s.scope_id, s.path, s.active, s.default_page_path, s.list_order
				FROM users.dbo.sections s
					JOIN section_tree st ON st.parent_id = s.id   
			)
						
			SELECT DISTINCT a.*, ls.name AS scope FROM (
				SELECT id, parent_id, name, description, scope_id, path, active, default_page_path, list_order
				FROM section_tree
						
				UNION
						 
				SELECT 999999 AS id, p.section_id AS parent_id, p.name, p.description, p.scope_id, p.path, p.active, p.path, p.list_order
				FROM users.dbo.pages p
					INNER JOIN users.dbo.lookup_scopes ls ON p.scope_id = ls.id
				WHERE p.active = 1 AND ls.name IN(" . $scope_text . ")
			) a
						
			INNER JOIN users.dbo.lookup_scopes ls ON a.scope_id = ls.id
			ORDER BY list_order
		";
		
		$tmp_arr_sections = $this->db
		->query($sql)
		->result_array();

		return $tmp_arr_sections;
	}

	/**
	 * getSubscribedContent
	 * 
	 * subscription is different in that it fetches content by herd data (i.e. herd output) for users that 
	 * have permission only for subscribed content.  All other scopes are strictly users-based
	 * 
	 * @param string $herd_code
	 * @return array of section and page data for given herd
	 * @author ctranel
	 **/
	public function getSubscribedContent($herd_code) {
		$sql = "
			WITH section_tree AS (
				SELECT id, parent_id, name, description, scope_id, path, active, default_page_path, list_order
				FROM users.dbo.sections
				WHERE id IN(
					SELECT DISTINCT p.section_id
						FROM users.dbo.pages p
						INNER JOIN users.dbo.pages_reports pr ON p.id = pr.page_id AND p.active = 1 AND p.scope_id = 2
						INNER JOIN herd.dbo.herd_output ho ON pr.report_code = ho.report_code AND ho.herd_code = '" . $herd_code . "' AND ho.end_date IS NULL AND ho.medium_type_code = 'W' 
				)
		
				UNION ALL
		
				SELECT s.id, s.parent_id, s.name, s.description, s.scope_id, s.path, s.active, s.default_page_path, s.list_order
				FROM users.dbo.sections s
					JOIN section_tree st ON st.parent_id = s.id   
			)
			
			SELECT a.*, ls.name AS scope FROM (
				SELECT DISTINCT id, parent_id, name, description, scope_id, path, active, default_page_path, list_order
				FROM section_tree
			
				UNION
			 
				SELECT 999999 AS id, section_id AS parent_id, name, description, scope_id, path, active, path, list_order
				FROM users.dbo.pages p
					INNER JOIN users.dbo.pages_reports pr ON p.id = pr.page_id AND p.active = 1 AND p.scope_id = 2
					INNER JOIN herd.dbo.herd_output ho ON pr.report_code = ho.report_code AND ho.herd_code = '35031038' AND ho.end_date IS NULL AND ho.medium_type_code = 'W' 
			) a
			
			INNER JOIN users.dbo.lookup_scopes ls ON a.scope_id = ls.id
			ORDER BY list_order";
		
		$tmp_arr_sections = $this->db
		->query($sql)
		->result_array();

		return $tmp_arr_sections;
	}

	/**
	 * getPublicContent
	 * 
	 * @return array of section and page data
	 * @author ctranel
	public function getPublicContent() {
		$sql = "
			WITH section_tree AS (
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
			
			SELECT DISTINCT a.*, ls.name AS scope FROM (
				SELECT id, parent_id, name, description, scope_id, path, active, default_page_path, list_order
				FROM section_tree
			
				UNION
			
				SELECT id, section_id AS parent_id, name, description, scope_id, path, active, path, list_order
					FROM users.dbo.pages
					WHERE active = 1 AND scope_id = 1
			) a
			
			INNER JOIN users.dbo.lookup_scopes ls ON a.scope_id = ls.id
			ORDER BY list_order";
		
		$tmp_arr_sections = $this->db
		->query($sql)
		->result_array();

		return $tmp_arr_sections;
	}
	 **/
}
