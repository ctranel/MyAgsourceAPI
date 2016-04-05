<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Product_model extends CI_Model {
	public function __construct(){
		parent::__construct();
	}

	/**
	 * return array all internal products available on web
	 * 
	 * @return array of product data
	 * @author ctranel
	 **/
	public function getAllProducts() {
		$sql = "
			SELECT DISTINCT pr.report_code AS product_code, r.report_name AS name, r.report_description AS [description]
			FROM users.dbo.pages p
				INNER JOIN users.dbo.pages_reports pr ON p.id = pr.page_id
				INNER JOIN dhi_tables.dbo.report_catalog r ON pr.report_code = r.report_code
			WHERE p.active = 1
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
	 * @return array of product data for given user
	 * @author ctranel
	 *
	 * @todo: escape scopes text before putting in query
	 **/
	public function getProductsByScope($scopes, $herd_code) {
		if(empty($scopes)){
			return false;
		}
		if(!is_array($scopes)){
			$scopes = [$scopes];
		}
		$scope_text = "'" . implode("','", $scopes) . "'";
		$sql = "
			SELECT DISTINCT pr.report_code AS product_code, r.report_name AS name, r.report_description AS [description]
			FROM users.dbo.pages p
				INNER JOIN users.dbo.lookup_scopes ls ON p.scope_id = ls.id
				INNER JOIN users.dbo.pages_reports pr ON p.id = pr.page_id
				INNER JOIN herd.dbo.herd_output ho ON pr.report_code = ho.report_code AND ho.herd_code = '" . $herd_code . "' AND ho.activity_code IN('A', 'E')
				INNER JOIN dhi_tables.dbo.report_catalog r ON pr.report_code = r.report_code
			WHERE p.active = 1 AND ls.name IN(" . $scope_text . ")
		";

        $tmp_arr_sections = $this->db
		->query($sql)
		->result_array();
		return $tmp_arr_sections;
	}

	/**
	 * getSubscribedProducts
	 * 
	 * subscription is different in that it fetches content by herd data (i.e. herd output) for users that 
	 * have permission only for subscribed content.  All other scopes are strictly users-based
	 * 
	 * @param string $herd_code
	 * @return array of product data for given herd
	 * @author ctranel
	 **/
	public function getSubscribedProducts($herd_code) {
		$sql = "
			SELECT DISTINCT pr.report_code AS product_code, r.report_name AS name, r.report_description AS [description]
			FROM users.dbo.pages p
				INNER JOIN users.dbo.lookup_scopes ls ON p.scope_id = ls.id
				INNER JOIN users.dbo.pages_reports pr ON p.id = pr.page_id AND p.active = 1 AND p.scope_id = 2
				INNER JOIN users.dbo.v_user_status_info si ON pr.report_code = si.report_code AND si.herd_code = '" . $herd_code . "' AND (si.herd_is_paying = 1 OR si.herd_is_active_trial = 1)
				INNER JOIN dhi_tables.dbo.report_catalog r ON pr.report_code = r.report_code
		";
		
		$tmp_arr_sections = $this->db
		->query($sql)
		->result_array();

		return $tmp_arr_sections;
	}

	/**
	 * getUpsellProductCodes
	 *
	 * subscription is different in that it fetches content by herd data (i.e. herd output) for users that
	 * have permission only for subscribed content.  All other scopes are strictly users-based
	 *
	 * @param array accessible report_codes
	 * @return array of product data for given herd
	 * @author ctranel
	 **/
	public function getUpsellProducts($accessible_report_codes) {
		$sql = "
			SELECT DISTINCT pr.report_code AS product_code, r.report_name AS name, r.report_description AS [description]
			FROM users.dbo.pages_reports pr
			INNER JOIN dhi_tables.dbo.report_catalog r ON pr.report_code = r.report_code
		";
		if(isset($accessible_report_codes) && is_array($accessible_report_codes) && !empty($accessible_report_codes)){
			$code_string = "'" . implode("','", $accessible_report_codes) . "'";
			$sql .= "
			WHERE pr.page_id NOT IN (
				SELECT page_id
				FROM users.dbo.pages_reports
                WHERE report_code IN($code_string)
            )
            ";
		}

		$tmp_arr = $this->db
		->query($sql)
		->result_array();
		return $tmp_arr;
	}
}
