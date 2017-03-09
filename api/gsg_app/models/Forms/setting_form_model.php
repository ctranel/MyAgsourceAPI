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
     * getFormsByPage
     * @param page_id
     * @return string category
     * @author ctranel
     **/
    public function getFormsByPage($page_id) {
        $page_id = (int)$page_id;

        $results = $this->db
            ->select('pb.page_id, b.id, b.name, f.id AS form_id, f.name AS form_name, b.name AS block_name, b.description, dt.name AS display_type, s.name AS scope, b.isactive, b.path, f.dom_id, f.action, pb.list_order')
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
     * getFormsByPage
     * @param int $block_id
     * @return string category
     * @author ctranel
     **/
    public function getFormByBlock($block_id) {
        $block_id = (int)$block_id;

        $results = $this->db
            ->select('b.id, b.name, f.id AS form_id, f.name AS form_name, b.name AS block_name, b.description, dt.name AS display_type, s.name AS scope, b.isactive, b.path, f.dom_id, f.action, 1 AS list_order') //pb.page_id, pb.list_order
            ->join('users.frm.forms f', "b.id = f.block_id AND b.display_type_id = 6 AND b.id = " . $block_id, 'inner')
            ->join('users.dbo.lookup_display_types dt', 'b.display_type_id = dt.id', 'inner')
            ->join('users.dbo.lookup_scopes s', 'b.scope_id = s.id', 'inner')
            ->get('users.dbo.blocks b')
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
            ->select('sub.parent_control_name, sub.form_id, sub.form_name, sub.action, sub.dom_id, sub.condition_group_id, sub.condition_group_parent_id, sub.condition_group_operator, sub.condition_id, sub.form_control_name, sub.form_control_name, sub.operator, sub.operand, sub.isactive, s.name AS scope, sub.list_order') //, sub.form_control_name
            ->join('users.dbo.lookup_scopes s', 'sub.scope_id = s.id', 'inner')
            ->where('sub.isactive', 1)
            ->where('sub.parent_form_id', $parent_form_id)
            ->order_by('sub.form_control_name, sub.list_order')
            ->get('users.frm.vma_setting_subforms sub')
            ->result_array();

        return $results;
    }

    /**
     * getSubBlocksByParentId
     * @param $parent_form_id
     * @return string category
     * @author ctranel
     **/
    public function getSubBlocksByParentId($parent_form_id) {
        $parent_form_id = (int)$parent_form_id;

        $results = $this->db
            ->select('sub.parent_control_name, sub.parent_control_id, sub.block_id, sub.name, sub.display_type, sub.subblock_content_id, sub.datalink_form_id, sub.condition_group_id, sub.condition_group_parent_id, sub.condition_group_operator, sub.condition_id, sub.form_control_name, sub.operator, sub.operand, sub.isactive, s.name AS scope, sub.list_order')
            ->join('users.dbo.lookup_scopes s', 'sub.scope_id = s.id', 'inner')
            ->where('sub.isactive', 1)
            ->where('sub.parent_form_id', $parent_form_id)
            ->order_by('sub.form_control_name, sub.list_order')
            ->get('users.frm.vma_subblocks sub')
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
            ->select('b.id, b.name, f.id AS form_id, f.name AS form_name, b.name AS block_name, b.description, dt.name AS display_type, s.name AS scope, b.isactive, b.path, f.dom_id, f.action')
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
            ->select("s.id, t.name AS control_type, s.name, s.label, s.default_value, 'string' AS data_type, NULL AS batch_variable_type, s.for_user, s.for_herd, uhs.value") //, f.name AS category, s.dom_id
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
    *  @param: int setting id
    *  @return array key-value pairs
    *  @throws:
    * -----------------------------------------------------------------
    */
    public function getLookupKeys($control_id){
        $control_id = (int)$control_id;

        $sql = "SELECT value_column, desc_column FROM users.frm.data_lookup WHERE control_id = " . $control_id;

        $results = $this->db->query($sql)->result_array();

        if(!$results){
            throw new Exception('No lookup key data found.');
        }
        $err = $this->db->_error_message();
        if(!empty($err)){
            throw new \Exception($err);
        }
//print($sql);
        if(isset($results[0]) && is_array($results[0])) {
            return $results[0];
        }

        return [];
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
				SELECT @sql = N' SELECT ' + @value_col + ', ' + @desc_col + ' FROM ' + @tbl + ' WHERE herd_code = '" . $herd_code . "' AND (serial_num = " . $serial_num . " OR serial_num IS NULL) ORDER BY list_order'
				EXEC sp_executesql @sql";
//echo $sql;

        $results = $this->db->query($sql)->result_array();

        return $results;
    }

    /* -----------------------------------------------------------------
     *  returns key-value pairs of options for a given lookup field

     *  returns key-value pairs of options for a given lookup field

     *  @author: ctranel
     *  @date: 2017-03-02
     *  @param: int control id
     *  @return array key-value pairs
     *  @throws:
     * -----------------------------------------------------------------
     */
    public function getUserLookupOptions($control_id, $user_id){
        $control_id = (int)$control_id;
        $user_id = (int)$user_id;

        if(!isset($user_id) || empty($user_id)){
            throw new Exception('User not specified when getting options.');
        }
        $sql = "USE users;
				DECLARE @tbl nvarchar(100), @value_col nvarchar(32), @desc_col nvarchar(32), @sql nvarchar(255)
				SELECT @tbl = table_name, @value_col = value_column, @desc_col = desc_column FROM users.frm.data_lookup WHERE control_id = " . $control_id . "
				SELECT @sql = N' SELECT ' + @value_col + ', ' + @desc_col + ' FROM ' + @tbl + ' WHERE (user_id = " . $user_id . " OR user_id IS NULL) AND isactive = 1 ORDER BY list_order'
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
     * @param: datetime log datetime
     * @param: int log id
    *  @return void
    *  @throws:
    * -----------------------------------------------------------------
    */
    public static function composeSettingSelect($user_id, $herd_code, $setting_id, $setting_value, $log_dttm, $log_id){
//die($user_id);
        if($user_id != "NULL"){
            $user_id = (int)$user_id;
        }
        $herd_code = MssqlUtility::escape($herd_code);
        $setting_id = (int)$setting_id;
        $setting_value = MssqlUtility::escape($setting_value);

        $ret = "SELECT " . $user_id . " AS user_id, '" . $herd_code . "' AS herd_code, " . $setting_id . " AS setting_id, '" . $setting_value . "' AS value, '" . $log_dttm . "' AS logdttm, '" . $log_id . "' AS logid";
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
	public function upsert($form_id, $using_stmnt){
        $form_id = (int)$form_id;
        //data in $arr_using_stmnts was cleaned when statements were composed

		if(!isset($using_stmnt) || empty($using_stmnt)){
			throw new Exception("No data provided");
		}
		//$using_stmnt = implode(' UNION ALL ', $arr_using_stmnts);
		
		$sql = "MERGE INTO users.setng.user_herd_settings uhs
				USING ($using_stmnt) nd 
					ON (uhs.user_id = nd.user_id OR (uhs.user_id IS NULL AND nd.user_id IS NULL)) 
					    AND (uhs.herd_code = nd.herd_code  OR (uhs.herd_code IS NULL AND nd.herd_code IS NULL))
					    AND uhs.setting_id = nd.setting_id
				WHEN MATCHED THEN
					UPDATE
					SET uhs.setting_id = nd.setting_id, uhs.value = nd.value, uhs.logdttm = nd.logdttm, uhs.logid = nd.logid
				WHEN NOT MATCHED BY TARGET THEN
					INSERT (user_id, herd_code, setting_id, value, logdttm, logid)
					VALUES (nd.user_id, nd.herd_code, nd.setting_id, nd.value, nd.logdttm, nd.logid);";
//print($sql);
		$this->db->query($sql);
	}
    
    public function insert($form_id, $using_stmnt){
        $this->upsert($form_id, $using_stmnt);
    }
    public function update($form_id, $using_stmnt){
        $this->upsert($form_id, $using_stmnt);
    }
}