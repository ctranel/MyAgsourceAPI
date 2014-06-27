<?php
//namespace myagsource\settings;
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
		->select('s.id, t.name AS type, c.name AS category, g.name AS [group], s.name, s.description, s.default_value, uhs.value')
		->join('users.dbo.set_user_herd_settings uhs', "s.id = uhs.setting_id", 'left')
		->join('users.dbo.set_lookup_types t', "s.type_id = t.id", 'inner')
		->join('users.dbo.set_lookup_categories c', "s.category_id = c.id", 'inner')
		->join('users.dbo.set_lookup_groups g', "s.group_id = g.id", 'inner')
		->where('c.name', $category)
		->get('users.dbo.set_settings s')
		->result_array();
		
		return $results;
	}
	
	/* -----------------------------------------------------------------
	*  Short Description

	*  Long Description

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 26, 2014
	*  @param: string
	*  @param: int
	*  @param: array
	*  @return datatype
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function getLookupOptions($setting_id){
		$sql = "USE users;
				DECLARE @tbl nvarchar(100), @sql nvarchar(255)
				SELECT @tbl = table_name FROM users.dbo.set_type_data_lookup WHERE setting_id = " . $setting_id . "
				SELECT @sql = N' SELECT value, description FROM ' + quotename(@tbl)
				EXEC sp_executesql @sql";
		$results = $this->db->query($sql)->result_array();
		return $results;
	}
}