<?php
//namespace myagsource\settings;
/* -----------------------------------------------------------------
 *	CLASS comments
 *  @file: setting_model.php
 *  @author: kmarshall
 *  @date: Nov 19, 2013
 *
 *  @description: Model for Settings -
 *  Accesses settings data.
 *
 * -----------------------------------------------------------------
 */

class Setting_model extends CI_Model {
    /**
     * user_id
     * @var int
     **/
    protected $user_id;

    /**
     * herd_code
     * @var string
     **/
    protected $herd_code;


    public function __construct($args){
		parent::__construct();
        $this->user_id = $args['user_id'];
        $this->herd_code = $args['herd_code'];
	}


    /**
     * getSettingsByPage
     * @param mixed int/string page_id or form name
     * @return string category
     * @author ctranel
     **/
    public function getSettingsByPage($page_id) {
        $this->db
            ->select('s.id, t.name AS data_type, b.name AS category, g.name AS [group], s.name, s.label, s.default_value, uhs.value')
            ->join('users.dbo.blocks b', "pb.block_id = b.id AND b.display_type_id = 6 AND (pb.page_id = " . (int)$page_id . ")", 'inner')// . " OR f.dom_id = '" . $page_id
            ->join('users.frm.forms f', "b.id = f.block_id", 'inner')
            ->join('users.setng.forms_settings fs', "f.id = fs.form_id", 'inner')
            ->join('users.setng.settings s', "fs.setting_id = s.id", 'inner');

        if(isset($this->user_id) && $this->user_id !== FALSE){
            $this->db
                ->join('users.setng.user_herd_settings uhs', "s.id = uhs.setting_id AND uhs.user_id = " . $this->user_id . " AND uhs.herd_code = '" . $this->herd_code . "'", 'left')
                ->where("(uhs.user_id = " . $this->user_id . " OR uhs.user_id IS NULL)");
        }
        else{
            $this->db
                ->join('users.setng.user_herd_settings uhs', "s.id = uhs.setting_id AND uhs.user_id IS NULL AND uhs.herd_code = '" . $this->herd_code . "'", 'left')
                ->where("uhs.user_id IS NULL");
        }

        $results = $this->db->join('users.setng.types t', "s.type_id = t.id", 'inner')
            ->join('users.setng.groups g', "s.group_id = g.id", 'inner')
            ->where("(uhs.herd_code = '" . $this->herd_code . "' OR uhs.herd_code IS NULL)")
            ->get('users.dbo.pages_blocks pb')
            ->result_array();
        return $results;
    }

    /**
     * getFormsByPage
     * @return string category
     * @author ctranel
     **/
    public function getFormsByPage($page_id) {
        $results = $this->db
            ->select('pb.page_id, b.id, f.id AS form_id, b.name, b.description, dt.name AS display_type, s.name AS scope, b.active, b.path, f.dom_id, f.action, pb.list_order')
            ->join('users.dbo.blocks b', "pb.block_id = b.id AND b.display_type_id = 6 AND pb.page_id = " . $page_id, 'inner')
            ->join('users.frm.forms f', "b.id = f.block_id", 'inner')
            ->join('users.dbo.lookup_display_types dt', 'b.display_type_id = dt.id', 'inner')
            ->join('users.dbo.lookup_scopes s', 'b.scope_id = s.id', 'inner')
            ->get('users.dbo.pages_blocks pb')
//            ->where('')
            ->result_array();
        return $results;
    }


    /**
     * getFormById
     * @param int form id
     * @return form data
     * @author ctranel
     **/
    public function getFormById($form_id) {
        $results = $this->db
            ->select('b.id, f.id AS form_id, b.name, b.description, dt.name AS display_type, s.name AS scope, b.active, b.path, f.dom_id, f.action')
            ->join('users.frm.forms f', "b.id = f.block_id AND f.id = " . $form_id, 'inner')
            ->join('users.dbo.lookup_display_types dt', 'b.display_type_id = dt.id', 'inner')
            ->join('users.dbo.lookup_scopes s', 'b.scope_id = s.id', 'inner')
            ->get('users.dbo.blocks b')
            ->result_array();
        return $results;
    }


    /**
     * getFormControlData
     *
     * @param int form id
     * @return string category
     * @author ctranel
     **/
    public function getFormControlData($form_id) {
        $this->db
            ->select('s.id, t.name AS data_type, g.name AS [group], s.name, s.label, s.default_value, s.for_user, s.for_herd, uhs.value') //, f.name AS category, s.dom_id
            ->join('users.setng.forms_settings fs', "f.id = fs.form_id AND f.id = " . $form_id, 'inner')
            ->join('users.setng.settings s', "fs.setting_id = s.id", 'inner');

        if(isset($this->user_id) && $this->user_id != FALSE){
            $this->db
                ->join('users.setng.user_herd_settings uhs', "s.id = uhs.setting_id AND (uhs.user_id = " . $this->user_id . " OR uhs.user_id IS NULL) AND (uhs.herd_code = '" . $this->herd_code . "' OR uhs.herd_code IS NULL)", 'left');
                //->where("(uhs.user_id = " . $this->user_id . " OR uhs.user_id IS NULL)");
        }
        else{
            $this->db
                ->join('users.setng.user_herd_settings uhs', "s.id = uhs.setting_id AND uhs.user_id IS NULL AND (uhs.herd_code = '" . $this->herd_code . "' OR uhs.herd_code IS NULL)", 'left');
                //->where("uhs.user_id IS NULL");
        }

        $results = $this->db->join('users.setng.types t', "s.type_id = t.id", 'inner')
            ->join('users.setng.groups g', "s.group_id = g.id", 'inner')
            //->where("(uhs.herd_code = '" . $this->herd_code . "' OR uhs.herd_code IS NULL)")
            ->get('users.frm.forms f')
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
				SELECT @tbl = table_name FROM users.setng.data_lookup WHERE setting_id = " . $setting_id . "
				SELECT @sql = N' SELECT value, description FROM ' + quotename(@tbl) + ' ORDER BY list_order'
				EXEC sp_executesql @sql";
		$results = $this->db->query($sql)->result_array();
		return $results;
	}
	
	/* -----------------------------------------------------------------
	*  upsert Description

	*  upsert Description

	*  @author: ctranel
	*  @param: array of strings
	*  @return void
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function upsert($arr_using_stmnts){
		if(!isset($arr_using_stmnts) || empty($arr_using_stmnts)){
			return false;
		}
		$using_stmnt = implode(' UNION ALL ', $arr_using_stmnts);
		
		$sql = "MERGE INTO users.setng.user_herd_settings uhs
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