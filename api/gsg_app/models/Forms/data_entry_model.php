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


    public function __construct($args){
		parent::__construct();
        $this->criteria = $args;
        $this->herd_code = $args['herd_code'];
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
            ->join('users.dbo.blocks b', "pb.block_id = b.id AND b.display_type_id = 7 AND pb.page_id = " . $page_id, 'inner')
            ->join('users.frm.forms f', "b.id = f.block_id", 'inner')
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
            ->join('users.frm.subform_condition_groups scg', "sl.id = scg.subform_link_id AND sl.parent_form_id = " . $parent_form_id, 'inner')
            ->join('users.frm.subform_condition sc', "scg.id = sc.condition_group_id", 'inner')
            ->join('users.frm.forms f', "sl.form_id = f.id", 'inner')
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
     * @return string category
     * @author ctranel
     **/
    protected function getFormKeyMeta($form_id)
    {
        $ret = [];

        $results = $this->db
            ->select('fld.db_field_name, fld.data_type, fld.max_length')
            ->join('users.frm.form_controls fc', 'cg.id = fc.form_control_group_id AND cg.form_id = ' . $form_id)
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
     * getFormControlData
     *
     * @param int form id
     * @return string category
     * @author ctranel
     **/
    public function getFormControlData($form_id) {
        $keys = array_keys($this->criteria);
        $key_meta = $this->getFormKeyMeta($form_id);

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
                select fld.id, CONCAT(db.name, '.',  tbl.db_schema, '.', tbl.name) AS db_table_name,fld.db_field_name--, uhs.value
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

                select fc.id, ct.name AS control_type, fld.name, fld.name AS label, fc.default_value, " . $key_val_field_list_text . " v.value
                from users.frm.form_control_groups cg
                inner join users.frm.form_controls fc ON cg.id = fc.form_control_group_id AND cg.form_id = " . $form_id . "
                inner join users.dbo.db_fields fld ON fc.db_field_id = fld.id
                inner join users.frm.control_types ct ON fc.control_type_id = ct.id
                inner join #valueTable v ON fld.id = v.db_field_id;
       ";

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
		$sql = "USE users;
				DECLARE @tbl nvarchar(100), @sql nvarchar(255)
				SELECT @tbl = table_name FROM users.frm.data_lookup WHERE control_id = " . $control_id . "
				SELECT @sql = N' SELECT value, description FROM ' + @tbl + ' ORDER BY list_order'
				EXEC sp_executesql @sql";
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
    public function getHerdLookupOptions($control_id){
        $sql = "USE users;
				DECLARE @tbl nvarchar(100), @sql nvarchar(255)
				SELECT @tbl = table_name FROM users.frm.data_lookup WHERE control_id = " . $control_id . "
				SELECT @sql = N' SELECT value, description FROM ' + quotename(@tbl) + ' WHERE herd_code = " . $this->herd_code . " ORDER BY list_order'
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
		die('control upsert');
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