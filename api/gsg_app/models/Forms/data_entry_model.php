<?php
require_once APPPATH . 'libraries/MssqlUtility.php';
require_once(APPPATH . 'models/Forms/iForm_Model.php');

use \myagsource\MssqlUtility;

/* -----------------------------------------------------------------
 *	CLASS comments
 *  @file: Data_entry_model.php
 *  @author: ctranel
 *  @date: 2016/08/29
 *
 *  @description: Model for Settings -
 *  Accesses data entry data.
 *
 * -----------------------------------------------------------------
 */



class Data_entry_model extends CI_Model implements iForm_Model {

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
            ->select('pb.page_id, b.id, b.name, f.id AS form_id, f.name AS form_name, b.description, dt.name AS display_type, s.name AS scope, b.active, b.path, f.dom_id, f.action, pb.list_order')
            ->join('users.dbo.blocks b', "pb.block_id = b.id AND b.display_type_id = 7 AND pb.page_id = " . $page_id . ' AND b.active = 1', 'inner')
            ->join('users.frm.forms f', "b.id = f.block_id AND f.active = 1", 'inner')
            //the following gets only data-entry form data
            //->join('users.frm.form_control_groups cg', "f.id = cg.form_id", 'inner')
            ->join('users.dbo.lookup_display_types dt', 'b.display_type_id = dt.id', 'inner')
            ->join('users.dbo.lookup_scopes s', 'b.scope_id = s.id', 'inner')
            ->get('users.dbo.pages_blocks pb')
//            ->where('')
            ->result_array();
        return $results;
    }

    /**
     * getFormByBlock
     * @param block_id
     * @return string category
     * @author ctranel
     **/
    public function getFormByBlock($block_id) {
        $block_id = (int)$block_id;
        $results = $this->db
            ->select('b.id, b.name, f.id AS form_id, f.name AS form_name, b.description, dt.name AS display_type, s.name AS scope, b.active, b.path, f.dom_id, f.action, 1 AS list_order') //, pb.list_order, pb.page_id,
            ->join('users.frm.forms f', "b.id = f.block_id AND f.active = 1 AND b.active = 1 AND b.display_type_id = 7 AND b.id = " . $block_id, 'inner')
            //the following gets only data-entry form data
            //->join('users.frm.form_control_groups cg', "f.id = cg.form_id", 'inner')
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
            ->select('sub.form_id, sub.form_name, sub.action, sub.dom_id, sub.condition_group_id, sub.condition_group_parent_id, sub.condition_group_operator, sub.condition_id, sub.form_control_name, sub.form_control_name, sub.operator, sub.operand, sub.active, s.name AS scope, sub.list_order') //, sub.form_control_name
            ->join('users.dbo.lookup_scopes s', 'sub.scope_id = s.id', 'inner')
            ->where('sub.active', 1)
            ->where('sub.parent_form_id', $parent_form_id)
            ->order_by('sub.form_control_name, sub.list_order')
            ->get('users.frm.vma_entry_subforms sub')
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
            ->select('sub.block_id, sub.block_name, sub.condition_group_id, sub.condition_group_parent_id, sub.condition_group_operator, sub.condition_id, sub.form_control_name, sub.operator, sub.operand, sub.active, s.name AS scope, sub.list_order') //
            ->join('users.dbo.lookup_scopes s', 'sub.scope_id = s.id', 'inner')
            ->where('sub.active', 1)
            ->where('sub.parent_form_id', $parent_form_id)
            ->order_by('sub.form_control_name, sub.list_order')
            ->get('users.frm.v_subblocks sub')
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
            ->select('b.id, b.name, f.id AS form_id, f.name AS form_name, b.description, dt.name AS display_type, s.name AS scope, b.active, b.path, f.dom_id, f.action')
            ->join('users.frm.forms f', "b.id = f.block_id AND f.id = " . $form_id, 'inner')
            ->join('users.dbo.lookup_display_types dt', 'b.display_type_id = dt.id', 'inner')
            ->join('users.dbo.lookup_scopes s', 'b.scope_id = s.id', 'inner')
            ->get('users.dbo.blocks b')
            ->result_array();
        return $results;
    }

    /**
     * getSubformById
     * @param int form id
     * @return form data
     * @author ctranel
     **/
    public function getSubformById($form_id) {
        $form_id = (int)$form_id;

        $results = $this->db
            ->select('f.id AS form_id, f.name AS form_name, f.description, s.name AS scope, f.active, f.dom_id, f.action')
            //->join('users.frm.forms f', "b.id = f.block_id AND f.id = " . $form_id, 'inner')
            ->join('users.dbo.lookup_scopes s', "f.scope_id = s.id AND f.id = " . $form_id, 'inner')
            ->get('users.frm.forms f')
            ->result_array();
        return $results;
    }

    /**
     * getFormKeyMeta
     *
     * @param int form id
     * @param array of ints $ancestor_form_ids
     * @return string category
     * @author ctranel
    protected function getFormKeyMeta($form_id, $ancestor_form_ids = null) {
        $ret = [];

        if(is_array($ancestor_form_ids)){
            $form_ids = $ancestor_form_ids + [$form_id];
        }
        else{
            $form_ids = [$form_id];
        }

        $results = $this->db
            ->select('fld.db_field_name, fld.data_type, fld.max_length')
            ->join('users.frm.form_controls fc', 'cg.id = fc.form_control_group_id AND cg.form_id IN(' . implode(',', $form_ids) . ')')
            ->join('users.dbo.db_fields fld', 'fc.db_field_id = fld.id AND fld.is_fk_field = 1')
            ->get('users.frm.form_control_groups cg')
            ->result_array();
        if(is_array($results)){
            foreach($results as $r){
                $ret[$r['db_field_name']] = $r;
            }
        }
        return $ret;
    }
**/

    /**
     * getSourceTable
     *
     * @param int form id
     * @return string category
     * @author ctranel
     **/
    protected function getSourceTable($form_id)
    {
        $form_id = (int)$form_id;

        $sql = " select DISTINCT CONCAT(db.name, '.',  tbl.db_schema, '.', tbl.name) AS db_table_name
                from users.frm.form_control_groups cg
                inner join users.frm.form_controls fc ON cg.id = fc.form_control_group_id AND cg.form_id = " . $form_id . "
                inner join users.dbo.db_fields fld ON fc.db_field_id = fld.id
                inner join users.dbo.db_tables tbl ON fld.db_table_id = tbl.id AND allow_update = 1
                inner join users.dbo.db_databases db ON tbl.database_id = db.id";
//print($sql);
        $results = $this->db->query($sql)->result_array();

        if(!$results){
            throw new Exception('No data found.');
        }

        if(count($results) > 1){
            throw new Exception('There must be only one source for form data.');
        }
        return $results[0]['db_table_name'];
    }

    /**
     * getFormControlMeta
     *
     * @param int form id
     * @param array of ints $ancestor_form_ids
     * @return string category
     * @author ctranel
     **/
    public function getFormControlMeta($form_id, $ancestor_form_ids = null) {
        $form_id = (int)$form_id;
        if(isset($ancestor_form_ids) && is_array($ancestor_form_ids)){
            //have not yet tested with multiple level nesting
            array_walk_recursive($ancestor_form_ids, function(&$v, $k){return (int)$v;});
        }
        $result = $this->db->select('fc.id, ct.name AS control_type, fld.db_field_name AS name, fc.label, fld.is_editable, fld.is_generated, fld.is_fk_field AS is_key, fld.data_type, fc.biz_validation_url, fc.form_defaults_url, fc.add_option_form_id, fc.default_value, fc.batch_variable_type')
            ->select("(CAST(
                  (SELECT STUFF((
                      SELECT '|', CONCAT(v.name, ':', v.value) AS [data()] 
                      FROM (SELECT cv.form_control_id, val.name, cv.value FROM users.frm.controls_validators cv INNER JOIN users.frm.validators val ON cv.validator_id = val.id) AS v
                      WHERE v.form_control_id = fc.id 
                      FOR xml path('')
                    ), 1, 1, ''))
                 AS VARCHAR(100))) AS validators
            ")
            ->from('users.frm.form_control_groups cg')
            ->join('users.frm.form_controls fc', "cg.id = fc.form_control_group_id AND cg.form_id = " . $form_id, 'inner')
            ->join('users.dbo.db_fields fld', 'fc.db_field_id = fld.id', 'inner')
            ->join('users.frm.control_types ct', 'fc.control_type_id = ct.id', 'inner')
            ->order_by('fc.list_order')

            ->get()
            ->result_array();
        return $result;
    }

    /**
     * getControlMetaById
     *
     * @param int control id
     * @return array
     * @author ctranel
     **/
    public function getControlMetaById($control_id) {
        $control_id = (int)$control_id;

        $result = $this->db->select('fc.id, ct.name AS control_type, fld.db_field_name AS name, fc.label, fld.is_editable, fld.is_generated, fld.is_fk_field AS is_key, fc.biz_validation_url, fc.form_defaults_url, fc.add_option_form_id, fc.default_value, fc.batch_variable_type')
            ->select("(CAST(
                  (SELECT STUFF((
                      SELECT '|', CONCAT(v.name, ':', v.value) AS [data()] 
                      FROM (
                        SELECT cv.form_control_id, val.name, cv.value 
                        FROM users.frm.controls_validators cv 
                            INNER JOIN users.frm.validators val ON cv.validator_id = val.id
                      ) AS v
                      WHERE v.form_control_id = fc.id 
                      FOR xml path('')
                    ), 1, 1, ''))
                 AS VARCHAR(100))) AS validators
            ")
            ->from('users.frm.form_controls fc')
//            ->join('TD.ref.lookup_codes l', "l.codetype = 'BATCH_VAR_TYPE' AND fc.batch_variable_type = l.code", 'inner')
            ->join('users.dbo.db_fields fld', "fc.db_field_id = fld.id AND fc.id = '" . $control_id . "'", 'inner')
            ->join('users.frm.control_types ct', 'fc.control_type_id = ct.id', 'inner')
            ->order_by('fc.list_order')

            ->get()
            ->result_array();

        if(isset($result) && is_array($result) && isset($result[0])){
            return $result[0];
        }
    }

    /**
     * getFormControlData
     *
     * @param int form id
     * @param int form id
     * @param array of ints $ancestor_form_ids
     * @return string category
     * @author ctranel
     **/
    public function getFormControlData($form_id, $params, $ancestor_form_ids = null) {
        $form_id = (int)$form_id;
        if(isset($params) && is_array($params)){
            array_walk_recursive($params, function(&$v, $k){return MssqlUtility::escape($v);});
        }
        if(isset($ancestor_form_ids) && is_array($ancestor_form_ids)){
            array_walk_recursive($ancestor_form_ids, function(&$v, $k){return (int)$v;});
        }

        $keys = array_keys($params);
        $common = array_intersect(['herd_code', 'serial_num'], $keys);

        if(count($common) === count($keys) || true){ //no key fields besides herd_code and serial num--testing this method with all listings
            $results = $this->getFormControlMeta($form_id, $ancestor_form_ids);
            //add passed param values back in to data
            foreach($results as &$r){
                if(in_array($r['name'], $keys)){
                    $r['value'] = $params[$r['name']];
                }
            }
            return $results;
        }
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
	public function getLookupOptions($control_id){
        $control_id = (int)$control_id;

	    $sql = "USE users;
				DECLARE @tbl nvarchar(100), @value_col nvarchar(32), @desc_col nvarchar(32), @code_type nvarchar(15), @sql nvarchar(255)
				SELECT @tbl = table_name, @value_col = value_column, @desc_col = desc_column, @code_type = codetype FROM users.frm.data_lookup WHERE control_id = " . $control_id . "
                IF @code_type IS NOT NULL
				    SELECT @sql = N' SELECT ' + @value_col + ', ' + @desc_col + ' FROM ' + @tbl + ' WHERE codetype = ''' + @code_type + ''' AND isactive = 1 ORDER BY list_order'
				ELSE
				    SELECT @sql = N' SELECT ' + @value_col + ', ' + @desc_col + ' FROM ' + @tbl + ' WHERE isactive = 1 ORDER BY list_order'
				EXEC sp_executesql @sql";

        $results = $this->db->query($sql)->result_array();

//print($sql);
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
    public function getHerdLookupOptions($control_id, $herd_code){
        $control_id = (int)$control_id;
        $herd_code = MssqlUtility::escape($herd_code);

        $sql = "USE users;
				DECLARE @tbl nvarchar(100), @value_col nvarchar(32), @desc_col nvarchar(32), @sql nvarchar(255)
				SELECT @tbl = table_name, @value_col = value_column, @desc_col = desc_column FROM users.frm.data_lookup WHERE control_id = " . $control_id . "
				SELECT @sql = N' SELECT ' + @value_col + ', ' + @desc_col + ' FROM ' + @tbl + ' WHERE herd_code = ''" . $herd_code . "'' AND isactive = 1 ORDER BY list_order'
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
    public function getAnimalLookupOptions($control_id, $herd_code, $serial_num){
        $control_id = (int)$control_id;
        $herd_code = MssqlUtility::escape($herd_code);
        $serial_num = (int)$serial_num;

        if(!isset($serial_num) || empty($serial_num)){
            return $this->getHerdLookupOptions($control_id, $herd_code);
            //throw new Exception('Animal serial number not set in datasource');
        }
        $sql = "USE users;
				DECLARE @tbl nvarchar(100), @value_col nvarchar(32), @desc_col nvarchar(32), @sql nvarchar(255)
				SELECT @tbl = table_name, @value_col = value_column, @desc_col = desc_column FROM users.frm.data_lookup WHERE control_id = " . $control_id . "
				SELECT @sql = N' SELECT ' + @value_col + ', ' + @desc_col + ' FROM ' + @tbl + ' WHERE herd_code = ''" . $herd_code . "'' AND (serial_num = " . $serial_num . " OR serial_num IS NULL) AND isactive = 1 ORDER BY list_order'
				EXEC sp_executesql @sql";
//echo $sql;
        $results = $this->db->query($sql)->result_array();

        return $results;
    }

    /* -----------------------------------------------------------------
    *  upsert

    *  upsert form submitted data

    *  @author: ctranel
    *  @param: array of strings
    *  @return key->value array of keys for the record
    *  @throws:
    * -----------------------------------------------------------------
    */
    public function upsert($form_id, $form_data, $control_meta = null, $key_meta = null){
        $form_id = (int)$form_id;
        $form_data = MssqlUtility::escape($form_data);
        $is_update = true;

            throw new \Exception('Upsert function not defined.');
    }

    /* -----------------------------------------------------------------
    *  insert

    *  insert form submitted data

    *  @author: ctranel
    *  @param: array of strings
    *  @return key->value array of keys for the record
    *  @throws:
    * -----------------------------------------------------------------
    */
    public function insert($form_id, $form_data, $control_meta = null){
        $form_id = (int)$form_id;
        $form_data = MssqlUtility::escape($form_data);

        $table_name = $this->getSourceTable($form_id);

        //string vs numeric, or can we use quotes for both?
        $no_quotes = ['decimal', 'numeric', 'tinyint', 'int', 'smallint', 'bit'];

        $tmp_table_schema = [];

        foreach($form_data as $k=>$v){
            if(is_array($v)){
                $v = implode('|', $v);
            }
            //only update non-generated columns
            if($control_meta[$k]['is_generated'] === false){
                if(in_array($control_meta[$k]['data_type'], $no_quotes) === true){
                    $v = (isset($v) && (!empty($v) || $v === 0)) ? $v : 'null';
                    $insert_vals[$k] = $v;
                }
                else {
                    $insert_vals[$k] = "'" . $v . "'";
                }
            }
            //use identity columns to build temp table schema
            else {
                $tmp_table_schema[] = $k . ' ' . $control_meta[$k]['data_type'];
            }
        }

        //need the commented select statement to trigger a return value.  temp table is used in updatable views to return key data
        if(!empty($tmp_table_schema)){
            $sql = "--SELECT;
                CREATE TABLE #output(" . implode(", ", $tmp_table_schema) . ");
                INSERT " . $table_name . " (" . implode(', ', array_keys($insert_vals)) . ")
                VALUES (" . implode(", ", $insert_vals) . ");
                SELECT * FROM #output;
                DROP TABLE #output";
        }
        else {
            $sql = "--SELECT;
                INSERT " . $table_name . " (" . implode(', ', array_keys($insert_vals)) . ")
                VALUES (" . implode(", ", $insert_vals) . ");";
        }
//die($sql);
        $res = $this->db->query($sql);
//die(var_dump($res));

        if(!$res){
            throw new \Exception('Submission Failed.');
        }
        $err = $this->db->_error_message();
        if(!empty($err)){
            throw new \Exception($err);
        }

        $dataset = $res->result_array();
        if(count($dataset) > 0){
            return $dataset[0];
        }

        return [];
    }

    /* -----------------------------------------------------------------
    *  batchInsert

    *  batchInsert form submitted data

    *  @author: ctranel
     * @param: int form id
     * @param: string variable field name
    *  @param: array of form data
     * @param: array of generated columns
    *  @return key->value array of keys for the record
    *  @throws:
    * -----------------------------------------------------------------
    */
    public function batchInsert($form_id, $variable_field, $form_data, $control_meta = null){
        $form_id = (int)$form_id;
        $form_data = MssqlUtility::escape($form_data);

        $table_name = $this->getSourceTable($form_id);

        //string vs numeric, or can we use quotes for both?
        $no_quotes = ['decimal', 'numeric', 'tinyint', 'int', 'smallint', 'bit'];

        $tmp_table_schema = [];
//var_dump($control_meta); die;
        foreach($form_data as $k=>$v){
            if(is_array($v)){
                $v = implode('|', $v);
            }
            //only update non-generated columns
            if($control_meta[$k]['is_generated'] === false && $k != $variable_field){
                if(in_array($control_meta[$k]['data_type'], $no_quotes) === true){
                    $v = (isset($v) && (!empty($v) || $v === 0)) ? $v : 'null';
                    $insert_vals[$k] = $v;
                }
                else {
                    $insert_vals[$k] = "'" . $v . "'";
                }
            }
            //use identity columns to build temp table schema
            elseif($control_meta[$k]['is_generated'] === true) {
                $tmp_table_schema[] = $k . ' ' . $control_meta[$k]['data_type'];
            }
        }

        $sql = "--SELECT;\n";
        if(!empty($tmp_table_schema)){
            //need the commented select statement to trigger a return value
            $sql .= "CREATE TABLE #output(" . implode(", ", $tmp_table_schema) . ");\n";
        }
        $sql .= "INSERT " . $table_name . " (" . $variable_field . ", " . implode(', ', array_keys($insert_vals)) . ")
                VALUES ";

        if(isset($form_data[$variable_field]) && !empty($form_data[$variable_field])){
            $batch_values = explode('|', $form_data[$variable_field]);
            foreach($batch_values as $v){
                $sql .= "(" . $v . ", " . implode(", ", $insert_vals) . "),";
            }
        }

        $sql = substr($sql, 0, -1);

        if(!empty($tmp_table_schema)){
            $sql .= "\nSELECT * FROM #output;
                DROP TABLE #output";
        }
//die(substr($sql, 0, -1));
        $res = $this->db->query($sql);

        if(!$res){
            throw new \Exception('Batch Submission Failed.');
        }
        $err = $this->db->_error_message();
        if(!empty($err)){
            throw new \Exception($err);
        }

        $dataset = $res->result_array();
        if(count($dataset) > 0){
            return $dataset[0];
        }

        return [];
    }

    /* -----------------------------------------------------------------
    *  update

    *  update form submitted data

    *  @author: ctranel
    *  @param: array of strings
    *  @return key->value array of keys for the record
    *  @throws:
    * -----------------------------------------------------------------
    */
    public function update($form_id, $form_data, $control_meta = null, $key_meta = null){
        $form_id = (int)$form_id;
        $form_data = MssqlUtility::escape($form_data);

        $table_name = $this->getSourceTable($form_id);
        //id key fields
        //$key_meta = $this->getFormKeyMeta($form_id);
        $key_field_names = array_keys($key_meta);

        $upd_where = [];
        if(isset($key_field_names) && is_array($key_field_names)){
            foreach($key_field_names as $k){
                $key_values[$k] = $form_data[$k];
                if(!isset($form_data[$k]) || empty($form_data[$k])){
                    throw new \Exception('Missing key data, submission failed.');
                }
                $upd_where[] = $k . "='" . $form_data[$k] . "'";
            }
        }

        $update_set = [];
        //string vs numeric, or can we use quotes for both?
        $no_quotes = ['decimal', 'numeric', 'tinyint', 'int', 'smallint', 'bit'];
        foreach($form_data as $k=>$v){
            if(is_array($v)){
                $v = implode('|', $v);
            }
            //only update non-generated columns
            if($control_meta[$k]['is_editable'] === true){
                if(in_array($control_meta[$k]['data_type'], $no_quotes) === true){
                    $v = (isset($v) && (!empty($v) || $v === 0)) ? $v : 'null';
                    $update_set[] = $k . "=" . $v;
                }
                else {
                    $update_set[] = $k . "='" . $v . "'";
                }
            }
        }

        $sql = "UPDATE " . $table_name .
            " SET " . implode(',' , $update_set) .
            " WHERE " . implode(" AND ", $upd_where);
//die($sql);
        $res = $this->db->query($sql);
        if(!$res){
            throw new \Exception('Update failed.');
        }

        return $key_values;
    }

    /* -----------------------------------------------------------------
*  delete

*  delete submitted data

*  @author: ctranel
*  @param: array of strings
*  @return void
*  @throws:
* -----------------------------------------------------------------
*/
    public function delete($form_id, $key_data){
        $form_id = (int)$form_id;
        $key_data = MssqlUtility::escape($key_data);

        //get table name
        $table_name = $this->getSourceTable($form_id);
        //id key fields
        //$key_meta = $this->getFormKeyMeta($form_id);
        $key_field_names = array_keys($key_data);

        $delete_cond = '';
        foreach($key_data as $k=>$v){
            if(is_array($v)){
                $v = implode('|', $v);
            }
            //string vs numeric, or can we use quotes for both?
            if(in_array($k, $key_field_names)){
                $delete_cond .= $k . "='" . $v . "' AND ";
            }
        }

        $sql = "DELETE FROM " . $table_name . " WHERE " . substr($delete_cond, 0, -5);
//die($sql);
        $res = $this->db->query($sql);

        if(!$res){
            throw new \Exception('Submission Failed.');
        }

        return $res;
    }
}