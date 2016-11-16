<?php
//namespace myagsource\settings;
/* -----------------------------------------------------------------
 *	CLASS comments
 *  @file: setting_form_model.php
 *  @author: kmarshall
 *  @date: Nov 19, 2013
 *
 *  @description: Model for Settings -
 *  Accesses settings data.
 *
 * -----------------------------------------------------------------
 */

require_once(APPPATH . 'models/Forms/iForm_Model.php');
require_once APPPATH . 'libraries/MssqlUtility.php';

use \myagsource\MssqlUtility;

class Setting_form_model extends CI_Model implements iForm_Model {

    public function __construct(){
		parent::__construct();
	}


    /**
     * getSettingsByPage
     * @param mixed int/string page_id or form name
     * @return string category
     * @author ctranel
    public function getSettingsByPage($page_id, $user_id, $herd_code) {
        if(isset($user_id) && $user_id !== FALSE){
            $uhs_join_cond = "s.id = uhs.setting_id AND uhs.user_id = " . $user_id . " AND uhs.herd_code = '" . $herd_code . "'";
            $uhs_where = "(uhs.user_id = " . $user_id . " OR uhs.user_id IS NULL)";
        }
        else{
            $uhs_join_cond = "s.id = uhs.setting_id AND uhs.user_id IS NULL AND uhs.herd_code = '" . $herd_code . "'";
            $uhs_where = "uhs.user_id IS NULL";
        }

        $sql =
            "WITH parentForms AS (
                SELECT f.id AS form_id, f.block_id, sl.parent_control_id, sl.list_order AS list_order
                FROM users.frm.forms f
                   
                   --INNER JOIN users.setng.forms_settings fs ON f.id = fs.form_id
                   --INNER JOIN users.setng.settings s ON fs.form_id = f.id AND f.active = 1

 
                    LEFT JOIN users.frm.subform_link sl ON f.id = sl.form_id
                WHERE sl.parent_control_id IS NULL
            ), cteForms AS (
                SELECT form_id, block_id, parent_control_id, list_order
                FROM parentForms
                UNION all 
                SELECT sl.form_id, f.block_id AS block_id, sl.parent_control_id, sl.list_order
                FROM users.frm.subform_link sl
                   
                   INNER JOIN users.setng.settings s ON sl.parent_control_id = s.id
                   INNER JOIN users.setng.forms_settings fs ON s.id = fs.setting_id

                    join cteForms f ON f.form_id = fs.form_id
            )
    
            SELECT s.id, t.name AS control_type, b.name AS category, s.name, s.label, s.default_value, uhs.value
            FROM users.dbo.pages_blocks pb
                INNER JOIN users.dbo.blocks b ON pb.block_id = b.id AND b.display_type_id = 6 AND (pb.page_id = " . $page_id . ")
                INNER JOIN cteForms cte ON b.id = cte.block_id-- OR s.id = cte.parent_control_id)
                INNER JOIN users.frm.forms f ON cte.block_id = f.block_id
                INNER JOIN users.setng.forms_settings fs ON cte.form_id = fs.form_id
                INNER JOIN users.setng.settings s ON fs.setting_id = s.id
                LEFT JOIN users.setng.user_herd_settings uhs ON s.id = uhs.setting_id AND uhs.user_id = 1 AND uhs.herd_code = '35371684'
                INNER JOIN users.frm.control_types t ON s.type_id = t.id 
            WHERE " . $uhs_where . "
            AND (uhs.herd_code = '" . $herd_code . "' OR uhs.herd_code IS NULL)
            ORDER BY pb.list_order, fs.list_order";
//die($sql);
        $results = $this->db->query($sql)->result_array();
        return $results;
    }
**/

    /**
     * getFormsByPage
     * @param page_id
     * @return string category
     * @author ctranel
     **/
    public function getFormsByPage($page_id) {
        $page_id = (int)$page_id;

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
     * getSubFormsByParentId
     * @param $parent_form_id
     * @return string category
     * @author ctranel
     **/
    public function getSubFormsByParentId($parent_form_id) {
        $parent_form_id = (int)$parent_form_id;

        $results = $this->db
            ->select('f.id AS form_id, s.name AS scope, f.active, f.dom_id, f.action, sl.list_order, scg.id AS group_id, scg.parent_id AS group_parent_id, scg.operator AS group_operator, sc.id AS condition_id, sc.operator, sc.operand, fld.db_field_name AS form_control_name') //, sc.form_control_name
            ->join('users.frm.subform_condition_groups scg', "sl.id = scg.subform_link_id AND sl.parent_form_id = " . $parent_form_id, 'inner')
            ->join('users.frm.subform_condition sc', "scg.id = sc.condition_group_id", 'inner')

            ->join('users.frm.form_controls fc', 'sl.parent_control_id = fc.id', 'inner')
            ->join('users.dbo.db_fields fld', 'fc.db_field_id = fld.id', 'inner')
            ->join('users.frm.form_control_groups fcg', 'fc.form_control_group_id = fcg.id', 'inner')

            ->join('users.frm.forms f', "sl.form_id = f.id AND f.active = 1", 'inner')

            ->join('users.dbo.lookup_scopes s', 'f.scope_id = s.id', 'inner')
            ->order_by('sc.form_control_name, sl.list_order')
            ->get('users.frm.subform_link sl')
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
        $form_id = (int)$form_id;

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
    public function getFormControlData($form_id, $key_params) {
        $form_id = (int)$form_id;
        $key_params = MssqlUtility::escape($key_params);

        $herd_code = $key_params['herd_code'];
        $user_id = $key_params['user_id'];

        $this->db
            ->select('s.id, t.name AS control_type, s.name, s.label, s.default_value, s.for_user, s.for_herd, uhs.value') //, f.name AS category, s.dom_id
            ->select("(CAST(
                  (SELECT STUFF((
                      SELECT '|', CONCAT(v.name, ':', v.value) AS [data()] 
                      FROM (SELECT sv.setting_id, val.name, sv.value FROM users.setng.settings_validators sv INNER JOIN users.frm.validators val ON sv.validator_id = val.id) AS v
                      WHERE v.setting_id = s.id 
                      FOR xml path('')
                    ), 1, 1, ''))
                 AS VARCHAR(100))) AS validators
            ")
            ->join('users.setng.settings s', "fs.setting_id = s.id AND fs.form_id = " . $form_id, 'inner')
            ->order_by('fs.list_order');

        if(isset($user_id) && $user_id != FALSE){
            $this->db
                ->join('users.setng.user_herd_settings uhs', "s.id = uhs.setting_id AND (uhs.user_id = " . $user_id . " OR uhs.user_id IS NULL) AND (uhs.herd_code = '" . $herd_code . "' OR uhs.herd_code IS NULL)", 'left');
                //->where("(uhs.user_id = " . $user_id . " OR uhs.user_id IS NULL)");
        }
        else{
            $this->db
                ->join('users.setng.user_herd_settings uhs', "s.id = uhs.setting_id AND uhs.user_id IS NULL AND (uhs.herd_code = '" . $herd_code . "' OR uhs.herd_code IS NULL)", 'left');
                //->where("uhs.user_id IS NULL");
        }

        $results = $this->db->join('users.frm.control_types t', "s.type_id = t.id", 'inner')
            ->get('users.setng.forms_settings fs')
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
		$setting_id = (int)$setting_id;

	    $sql = "USE users;
				DECLARE @tbl nvarchar(100), @value_col nvarchar(32), @desc_col nvarchar(32), @code_type nvarchar(15), @sql nvarchar(255)
				SELECT @tbl = table_name, @value_col = value_column, @desc_col = desc_column, @code_type = codetype FROM users.setng.data_lookup WHERE setting_id = " . $setting_id . "
                IF @code_type IS NOT NULL
				    SELECT @sql = N' SELECT ' + @value_col + ', ' + @desc_col + ' FROM ' + @tbl + ' WHERE codetype = ''' + @code_type + ''' ORDER BY list_order'
				ELSE
				    SELECT @sql = N' SELECT ' + @value_col + ', ' + @desc_col + ' FROM ' + @tbl + ' ORDER BY list_order'
				EXEC sp_executesql @sql";
        //die($sql);
		$results = $this->db->query($sql)->result_array();
		return $results;
	}

    /* -----------------------------------------------------------------
     *  returns key-value pairs of options for a given lookup field

     *  returns key-value pairs of options for a given lookup field

     *  @since: version 1
     *  @author: ctranel
     *  @date: Jun 26, 2014
     *  @param: int control id
     *  @return array key-value pairs
     *  @throws:
     * -----------------------------------------------------------------
     */
    public function getHerdLookupOptions($setting_id, $herd_code){
        $setting_id = (int)$setting_id;
        $herd_code = MssqlUtility::escape($herd_code);

        $sql = "USE users;
				DECLARE @tbl nvarchar(100), @value_col nvarchar(32), @desc_col nvarchar(32), @sql nvarchar(255)
				SELECT @tbl = table_name, @value_col = value_column, @desc_col = desc_column FROM users.setng.data_lookup WHERE setting_id = " . $setting_id . "
				SELECT @sql = N' SELECT ' + @value_col + ', ' + @desc_col + ' FROM ' + @tbl + ' WHERE herd_code = ''" . $herd_code . "'' ORDER BY list_order'
				EXEC sp_executesql @sql";

//echo $sql;
        $results = $this->db->query($sql)->result_array();

        return $results;
    }

    /* -----------------------------------------------------------------
     *  returns key-value pairs of options for a given lookup field

     *  returns key-value pairs of options for a given lookup field

     *  @author: ctranel
     *  @date: 2016-09-21
     *  @param: int control id
     *  @return array key-value pairs
     *  @throws:
     * -----------------------------------------------------------------
     */
    public function getAnimalLookupOptions($setting_id, $herd_code, $serial_num){
        $setting_id = (int)$setting_id;
        $herd_code = MssqlUtility::escape($herd_code);
        $serial_num = (int)$serial_num;

        if(!isset($serial_num) || empty($serial_num)){
            return $this->getHerdLookupOptions($setting_id, $herd_code);
            //throw new Exception('Animal serial number not set in datasource');
        }
        $sql = "USE users;
				DECLARE @tbl nvarchar(100), @value_col nvarchar(32), @desc_col nvarchar(32), @sql nvarchar(255)
				SELECT @tbl = table_name, @value_col = value_column, @desc_col = desc_column FROM users.setng.data_lookup WHERE setting_id = " . $setting_id . "
				SELECT @sql = N' SELECT ' + @value_col + ', ' + @desc_col + ' FROM ' + @tbl + ' WHERE herd_code = ''" . $herd_code . "'' AND (serial_num = " . $serial_num . " OR serial_num IS NULL) ORDER BY list_order'
				EXEC sp_executesql @sql";
//echo $sql;

        $results = $this->db->query($sql)->result_array();

        return $results;
    }

    /* -----------------------------------------------------------------
    *  composeSettingSelect

    *  compose sql insert statements to be used in the "using" clause in the upsert function

    *  @author: ctranel
    *  @param: int user id
     * @param: string herd code
     * @param: int setting id
     * @param: string setting value
    *  @return void
    *  @throws:
    * -----------------------------------------------------------------
    */
    public static function composeSettingSelect($user_id, $herd_code, $setting_id, $setting_value){
        $user_id = (int)$user_id;
        $herd_code = MssqlUtility::escape($herd_code);
        $setting_id = (int)$setting_id;
        $setting_value = MssqlUtility::escape($setting_value);


        $ret = "SELECT " . $user_id . " AS user_id, " . $herd_code . " AS herd_code, " . $setting_id . " AS setting_id, '" . $setting_value . "' AS value";
        return $ret;
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
	public function upsert($form_id, $arr_using_stmnts){
        $form_id = (int)$form_id;
        //data in $arr_using_stmnts was cleaned when statements were composed

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
//print($sql);
		$this->db->query($sql);
	}
    
    
}