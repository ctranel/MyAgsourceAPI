<?php
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

require_once(APPPATH . 'models/Forms/iForm_Model.php');


class Data_entry_model extends CI_Model implements iForm_Model {
    /**
     * user_id
     * @var int
     **/
    protected $criteria;

    /**
     * herd_code
     * @var string
     **/
    protected $herd_code;

    /**
     * serial_num
     * @var int
     **/
    protected $serial_num;

    /**
     * table_name
     * @var string
     **/
    protected $table_name;


    public function __construct($args){
		parent::__construct();
        $this->criteria = $args;
        $this->herd_code = $args['herd_code'];
        if(isset($args['serial_num'])){
            $this->serial_num = $args['serial_num'];
        }
	}

    /**
     * getFormsByPage
     * @param page_id
     * @return string category
     * @author ctranel
     **/
    public function getFormsByPage($page_id) {
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
        $results = $this->db
            ->select('f.id AS form_id, s.name AS scope, f.active, f.dom_id, f.action, sl.list_order, scg.id, scg.parent_id, scg.operator, sc.form_control_name, sc.operator, sc.operand')
            ->join('users.frm.subform_condition_groups scg', "sl.id = scg.subform_link_id", 'inner')
            ->join('users.frm.subform_condition sc', "scg.id = sc.condition_group_id", 'inner')

            ->join('users.frm.form_controls fc', 'sl.parent_control_id = fc.id', 'inner')
            ->join('users.frm.form_control_groups fcg', 'fc.form_control_group_id = fcg.id AND fcg.form_id = ' . $parent_form_id, 'inner')

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
    protected function getFormKeyMeta($form_id, $ancestor_form_ids = null)
    {
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
     * getFormControlData
     *
     * @param int form id
     * @param array of ints $ancestor_form_ids
     * @return string category
     * @author ctranel
     **/
    public function getFormControlData($form_id, $ancestor_form_ids = null) {
        $keys = array_keys($this->criteria);
        $key_meta = $this->getFormKeyMeta($form_id, $ancestor_form_ids);

        $key_field_list_text = '';
        $key_field_def_text = '';
        $key_condition_text = '';
        $key_val_field_list_text = '';
        $declare_key_var_text = '';
        $set_key_var_text = '';

//var_dump($keys, $key_meta);
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


            $set_key_var_text .= "SET @" . $k . " = '" . $this->criteria[$k] . "';";
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
               SET @dsql = CONCAT(N'INSERT INTO #valueTable (db_field_id, " . $key_field_list_text . " value) SELECT @p_id AS db_field_id, " . $key_field_list_text . " ' , @db_field_name, N' FROM ', @db_table_name, N' WHERE ', " . substr($key_condition_text, 0, -7) . "')
               SET @psql = N'@p_id INT'
               EXEC sp_executesql @dsql, @psql, @p_id = @db_field_id
               FETCH NEXT FROM Table_Cursor INTO @db_field_id, @db_table_name, @db_field_name;
            END;
            
            CLOSE Table_Cursor;
            
            DEALLOCATE Table_Cursor;

                select fc.id, ct.name AS control_type, fld.db_field_name AS name, fld.name AS label, fc.default_value, " . $key_val_field_list_text . " v.value AS value
                from users.frm.form_control_groups cg
                inner join users.frm.form_controls fc ON cg.id = fc.form_control_group_id AND cg.form_id = " . $form_id . "
                inner join users.dbo.db_fields fld ON fc.db_field_id = fld.id
                inner join users.frm.control_types ct ON fc.control_type_id = ct.id
                inner join #valueTable v ON fld.id = v.db_field_id;
       ";
//print($sql);
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
	public function getLookupOptions($control_id){
		$sql = "--USE users;
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
    public function getHerdLookupOptions($control_id){
        $sql = "--USE users;
				DECLARE @tbl nvarchar(100), @value_col nvarchar(32), @desc_col nvarchar(32), @sql nvarchar(255)
				SELECT @tbl = table_name, @value_col = value_column, @desc_col = desc_column FROM users.frm.data_lookup WHERE control_id = " . $control_id . "
				SELECT @sql = N' SELECT ' + @value_col + ', ' + @desc_col + ' FROM ' + @tbl + ' WHERE herd_code = ''" . $this->herd_code . "'' AND isactive = 1 ORDER BY list_order'
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
    public function getAnimalLookupOptions($control_id){
        if(!isset($this->serial_num) || empty($this->serial_num)){
            throw new Exception('Animal serial number not set in datasource');
        }
        $sql = "--USE users;
				DECLARE @tbl nvarchar(100), @value_col nvarchar(32), @desc_col nvarchar(32), @sql nvarchar(255)
				SELECT @tbl = table_name, @value_col = value_column, @desc_col = desc_column FROM users.frm.data_lookup WHERE control_id = " . $control_id . "
				SELECT @sql = N' SELECT ' + @value_col + ', ' + @desc_col + ' FROM ' + @tbl + ' WHERE herd_code = ''" . $this->herd_code . "'' AND (serial_num = " . $this->serial_num . " OR serial_num IS NULL) AND isactive = 1 ORDER BY list_order'
				EXEC sp_executesql @sql";
//echo $sql;
        $results = $this->db->query($sql)->result_array();
//var_dump($results);
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
	public function upsert($form_id, $form_data){
        if(!isset($form_data) || empty($form_data)){
            return false;
        }

        //get table name
        $table_name = $this->getSourceTable($form_id);
        //id key fields
        $key_meta = $this->getFormKeyMeta($form_id);
        $key_field_names = array_keys($key_meta);
        $form_field_names = array_keys($form_data);
        //can't insert values on identity fields
        //if($key = array_search('key_value', $form_field_names) !== false){
        //    unset($form_field_names[$key]);
        //}

        $join_cond = '';
        if(isset($key_field_names) && is_array($key_field_names)){
            foreach($key_field_names as $k){
                $join_cond .= " t." . $k . "=nd." . $k . " AND";
            }
        }

        $update_set = '';
        $value_as_key = '';
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

		return $this->db->query($sql);
	}
}