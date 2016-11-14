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
            ->select('pb.page_id, b.id, f.id AS form_id, b.name, b.description, dt.name AS display_type, s.name AS scope, b.active, b.path, f.dom_id, f.action, pb.list_order')
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
     * getFormKeyMeta
     *
     * @param int form id
     * @param array of ints $ancestor_form_ids
     * @return string category
     * @author ctranel
     **/
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
            //var_dump($ancestor_form_ids);
            array_walk_recursive($ancestor_form_ids, function(&$v, $k){return (int)$v;});
            //var_dump($ancestor_form_ids);
        }
        $result = $this->db->select('fc.id, ct.name AS control_type, fld.db_field_name AS name, fld.name AS label, fld.is_editable, fld.is_generated, fld.is_fk_field AS is_key, fc.biz_validation_url, fc.default_value')
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

        $result = $this->db->select('fc.id, ct.name AS control_type, fld.db_field_name AS name, fld.name AS label, fld.is_editable, fld.is_generated, fld.is_fk_field AS is_key, fc.biz_validation_url, fc.default_value')
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
            ->join('users.dbo.db_fields fld', "fc.db_field_id = fld.id AND fc.id = '" . $control_id . "'", 'inner')
            ->join('users.frm.control_types ct', 'fc.control_type_id = ct.id', 'inner')

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

        if(count($common) === count($keys)){ //no key fields besides herd_code and serial num
            $results = $this->getFormControlMeta($form_id, $ancestor_form_ids);
            //add passed param values back in to data
            foreach($results as &$r){
                if(in_array($r['name'], $keys)){
                    $r['value'] = $params[$r['name']];
                }
            }
            return $results;
        }

        $key_meta = $this->getFormKeyMeta($form_id, $ancestor_form_ids);

        $key_field_list_text = '';
        $key_field_def_text = '';
        $key_condition_text = '';
        $key_val_field_list_text = '';
        $declare_key_var_text = '';
        $set_key_var_text = '';

        foreach($keys as $k){
            $key_field_def_text .= $k . " " . $key_meta[$k]['data_type'];
            $declare_key_var_text .= ", @" . $k . " " . $key_meta[$k]['data_type'];
            if(strpos($key_meta[$k]['data_type'], 'char') !== false){
                $key_field_def_text .= '(' .  $key_meta[$k]['max_length'] . ')';
                $declare_key_var_text .= '(' .  $key_meta[$k]['max_length'] . ')';
                $key_condition_text .= "N'" . $k . " = ''', @" . $k . ", N''' AND ',";
            }
            else {
                $key_condition_text .= "N'" . $k . " = ', @" . $k . ", N' AND ', ";
            }
            $key_field_def_text .= ', ';

            $key_field_list_text .= $k . ', ';

            $key_val_field_list_text .= 'v.' . $k . ', ';


            $set_key_var_text .= "SET @" . $k . " = '" . $params[$k] . "';";
        }

       $sql = "
            DECLARE 
                @dsql NVARCHAR(MAX)
                ,@psql NVARCHAR(500)
                ,@db_field_id INT
                ,@db_table_name VARCHAR(100)
                ,@db_field_name VARCHAR(50)
                " . $declare_key_var_text . "
            
            " . $set_key_var_text . "
            
            IF OBJECT_ID('tempdb..#valueTable', 'U') IS NOT NULL
              DROP TABLE #valueTable;
            
            CREATE TABLE #valueTable (
                db_field_id INT,
                " . $key_field_def_text . "
                value VARCHAR(MAX) NULL,
            )

            DECLARE Table_Cursor CURSOR FOR
                select fld.id, CONCAT(db.name, '.',  tbl.db_schema, '.', tbl.name) AS db_table_name,fld.db_field_name
                from users.frm.form_control_groups cg
                inner join users.frm.form_controls fc ON cg.id = fc.form_control_group_id AND cg.form_id = " . $form_id . "
                inner join users.dbo.db_fields fld ON fc.db_field_id = fld.id
                inner join users.dbo.db_tables tbl ON fld.db_table_id = tbl.id AND allow_update = 1
                inner join users.dbo.db_databases db ON tbl.database_id = db.id

            
            OPEN Table_Cursor;
            
            FETCH NEXT FROM Table_Cursor INTO @db_field_id, @db_table_name, @db_field_name;
            
            WHILE @@FETCH_STATUS = 0
            BEGIN
               SET @dsql = CONCAT(N'INSERT INTO #valueTable (db_field_id, " . $key_field_list_text . " value) SELECT DISTINCT @p_id AS db_field_id, " . $key_field_list_text . " ' , @db_field_name, N' FROM ', @db_table_name, N' WHERE ', " . substr($key_condition_text, 0, -7) . "')
               SET @psql = N'@p_id INT'
               EXEC sp_executesql @dsql, @psql, @p_id = @db_field_id
               FETCH NEXT FROM Table_Cursor INTO @db_field_id, @db_table_name, @db_field_name;
            END;
            
            CLOSE Table_Cursor;
            
            DEALLOCATE Table_Cursor;

                select fc.id, ct.name AS control_type, fld.db_field_name AS name, fld.name AS label, fld.is_editable, fld.is_generated, fld.is_fk_field AS is_key, fc.biz_validation_url, fc.default_value, " . $key_val_field_list_text . " v.value AS value,
                    (CAST(
                        (SELECT STUFF((
                          SELECT '|', CONCAT(v.name, ':', v.value) AS [data()] 
                          FROM (SELECT cv.form_control_id, val.name, cv.value FROM users.frm.controls_validators cv INNER JOIN users.frm.validators val ON cv.validator_id = val.id) AS v
                          WHERE v.form_control_id = fc.id 
                          FOR xml path('')
                        ), 1, 1, ''))
                     AS VARCHAR(100))) AS validators
                from users.frm.form_control_groups cg
                inner join users.frm.form_controls fc ON cg.id = fc.form_control_group_id AND cg.form_id = " . $form_id . "
                inner join users.dbo.db_fields fld ON fc.db_field_id = fld.id
                inner join users.frm.control_types ct ON fc.control_type_id = ct.id
                inner join #valueTable v ON fld.id = v.db_field_id;
       ";
//print($sql); die;
        $time_start = microtime(true);

        $results = $this->db->query($sql)->result_array();

        $time_end = microtime(true);
/*        echo "
        
        
        TIME: " . ($time_end - $time_start);
        echo $sql;
*/        return $results;
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
        $time_start = microtime(true);

        $results = $this->db->query($sql)->result_array();

        $time_end = microtime(true);
/*        echo "


        TIME: " . ($time_end - $time_start);
        echo $sql;
*/
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
        $time_start = microtime(true);

        $results = $this->db->query($sql)->result_array();

        $time_end = microtime(true);
/*        echo "


        TIME: " . ($time_end - $time_start);
        echo $sql;
*/        return $results;
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
        $time_start = microtime(true);

        $results = $this->db->query($sql)->result_array();

        $time_end = microtime(true);
/*        echo "


        TIME: " . ($time_end - $time_start);
        echo $sql;
*///var_dump($results);
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
	public function upsert($form_id, $form_data, $generated_cols = null){
        $form_id = (int)$form_id;
        $form_data = MssqlUtility::escape($form_data);

        //get table name
        $table_name = $this->getSourceTable($form_id);
        //id key fields
        $key_meta = $this->getFormKeyMeta($form_id);
        $key_field_names = array_keys($key_meta);
        $form_field_names = array_keys($form_data);

        $join_cond = '';
        if(isset($key_field_names) && is_array($key_field_names)){
            foreach($key_field_names as $k){
                $join_cond .= " t." . $k . "=nd." . $k . " AND";
            }
        }

        $value_as_key = '';
        foreach($generated_cols as $k => $v){
            $value_as_key .= "'" .  $v  . "' AS " . $k . ",";
        }

        $update_set = '';
        foreach($form_data as $k=>$v){
            if(is_array($v)){
                $v = implode('|', $v);
            }
            //string vs numeric, or can we use quotes for both?
            //only update non-key fields
            if(in_array($k, $key_field_names) === false){
                $update_set .= " t." . $k . "=nd." . $k . ",";
            }
            if($v === null) {
                $value_as_key .= "NULL AS " . $k . ",";
            }
            else{
                $value_as_key .= "'" .  $v  . "' AS " . $k . ",";
            }
        }

		$sql = "MERGE INTO " . $table_name . " t
				USING (SELECT " . substr($value_as_key, 0, -1) . ") nd 
					ON" . substr($join_cond, 0, -4) . "
				WHEN MATCHED THEN
					UPDATE
					SET " . substr($update_set, 0, -1) . "
				WHEN NOT MATCHED BY TARGET THEN
					INSERT (" . implode(', ', $form_field_names) . ")
					VALUES (nd." . implode(', nd.', $form_field_names) . ");";
//die($sql);
		return $this->db->query($sql);
	}
}