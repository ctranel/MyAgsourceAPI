<?php
namespace myagsource\reports\settings;
/* -----------------------------------------------------------------
 *	CLASS comments
 *  @file: setting_model.php
 *  @author: kmarshall
 *  @date: Nov 19, 2013
 *
 *  @description: Model for Filters - 
 *  Accesses page_filters table and appends additions to the where criteria when filters are involved.
 *
 * -----------------------------------------------------------------
 */

class Setting_model extends CI_Model {
	public function __construct(){
		parent::__construct();
//		$this->db_group_name = 'default';
//		$this->{$this->db_group_name} = $this->load->database($this->db_group_name, TRUE);
	}

	
	/**
	 * getSettingsByCategory
	 * @return string category
	 * @author ctranel
	 **/
	public function getSettingsByCategory($category) {
		$results = $this->db
		->select('t.name AS type, c.name AS category, g.name AS group, s.name, s.description, s.default_value, uhs.value')
		->join('users.dbo.set_user_herd_settings uhs', "s.id = uhs.setting_id", 'left')
		->join('users.dbo.set_lookup_types t', "s.type_id = t.id", 'inner')
		->join('users.dbo.set_lookup_categories c', "s.type_id = c.id", 'inner')
		->join('users.dbo.set_lookup_groups g', "s.type_id = g.id", 'inner')
		->where('c.name', $category)
		->get('users.dbo.set_settings s')
		->result_array();
		
		return $results;
	}
	
}