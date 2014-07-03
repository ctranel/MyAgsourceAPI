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
	public function getSettingsByCategory($category, $user_id, $herd_code) {
		$results = $this->db
		->select('s.id, t.name AS type, c.name AS category, g.name AS [group], s.name, s.description, s.default_value, uhs.value')
		->join('users.dbo.set_user_herd_settings uhs', "s.id = uhs.setting_id AND uhs.user_id = " . $user_id . " AND uhs.herd_code = '" . $herd_code . "'", 'left')
		->join('users.dbo.set_lookup_types t', "s.type_id = t.id", 'inner')
		->join('users.dbo.set_lookup_categories c', "s.category_id = c.id", 'inner')
		->join('users.dbo.set_lookup_groups g', "s.group_id = g.id", 'inner')
		->where('c.name', $category)
		->where("(uhs.user_id = " . $user_id . " OR uhs.user_id IS NULL)")
		->where("(uhs.herd_code = '" . $herd_code . "' OR uhs.herd_code IS NULL)")
		->get('users.dbo.set_settings s')
		->result_array();
		
		return $results;
	}
	
	/* -----------------------------------------------------------------
	*  returns key-value pairs of options for a given lookup field

	*  returns key-value pairs of options for a given lookup field

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 26, 2014
	*  @param: int setting id
	*  @return array key-value pairs
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
	
	/* -----------------------------------------------------------------
	*  Short Description

	*  Long Description

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jul 1, 2014
	*  @param: int: user id
	*  @param: string: herd_code
	*  @param: array using statements (composed in library for access to setting object)
	*  @param: array of new settings key-value pairs
	*  @return datatype
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function mergeUserHerdSettings($arr_using_stmnts){
		if(!isset($arr_using_stmnts) || empty($arr_using_stmnts)){
			return false;
		}
		$using_stmnt = implode(' UNION ALL ', $arr_using_stmnts);
		
		$sql = "MERGE INTO users.dbo.set_user_herd_settings uhs
				USING ($using_stmnt) nd 
					ON uhs.user_id = nd.user_id AND uhs.herd_code = nd.herd_code AND uhs.setting_id = nd.setting_id
				WHEN MATCHED THEN
					UPDATE
					SET uhs.setting_id = nd.setting_id, uhs.value = nd.value
				WHEN NOT MATCHED BY TARGET THEN
					INSERT (user_id, herd_code, setting_id, value)
					VALUES (nd.user_id, nd.herd_code, nd.setting_id, nd.value);";
		$this->db->query($sql);
	}
}