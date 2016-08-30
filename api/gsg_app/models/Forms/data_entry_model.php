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
    //protected $user_id;

    /**
     * herd_code
     * @var string
     **/
    protected $herd_code;


    public function __construct($args){
		parent::__construct();
        //$this->user_id = $args['user_id'];
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
     * getFormControlData
     *
     * @param int form id
     * @return string category
     * @author ctranel
     **/
    public function getFormControlData($form_id) {
        return false;
//need a $k=>$v array for key data (herd_code, serial_num, test_date, pstring, etc)
       $sql = "
            DECLARE @dsql NVARCHAR(MAX)
                ,@psql NVARCHAR(500)
                
            
                ,@id INT
                ,@db_table_name VARCHAR(100)
                ,@db_field_name VARCHAR(50)
            
            
            IF OBJECT_ID('tempdb..#valueTable', 'U') IS NOT NULL
              DROP TABLE #valueTable;
            
            CREATE TABLE #valueTable (
                db_field_id INT,
                value VARCHAR(MAX),
                herd_code CHAR(8),
                test_date SMALLDATETIME,
                pstring INT
            )
            
            DECLARE Table_Cursor CURSOR FOR
                select fld.id, CONCAT(db.name, '.',  tbl.db_schema, '.', tbl.name) AS db_table_name,fld.db_field_name--, uhs.value
                from users.dbo.forms_dbfields fs
                inner join users.dbo.db_fields fld ON fs.db_field_id = fld.id AND fs.form_id = 4 --THIS IS THE VARIABLE FROM PHP
                inner join users.dbo.db_tables tbl ON fld.db_table_id = tbl.id --AND allow_update = 1
                inner join users.dbo.db_databases db ON tbl.database_id = db.id
            
            
            OPEN Table_Cursor;
            
            FETCH NEXT FROM Table_Cursor INTO @id, @db_table_name, @db_field_name;
            
            WHILE @@FETCH_STATUS = 0
            BEGIN
               SET @dsql = CONCAT(N'INSERT INTO #valueTable (db_field_id, herd_code, test_date, pstring, value) SELECT @p_id AS db_field_id, herd_code, test_date, pstring, ' , @db_field_name, N' FROM ', @db_table_name, N' WHERE herd_code = ''21110099'' AND test_date = ''2014-08-22'' AND pstring = 0')
               SET @psql = N'@p_id INT'
               EXEC sp_executesql @dsql, @psql, @p_id = @id
            
               FETCH NEXT FROM Table_Cursor INTO @id, @db_table_name, @db_field_name;
            END;
            
            CLOSE Table_Cursor;
            
            DEALLOCATE Table_Cursor;
            
               select fld.id, ct.name AS control_type, fld.name, fld.name AS label, fs.default_value, v.herd_code, v.test_date, v.pstring, v.value
                from users.dbo.forms_dbfields fs
                inner join users.dbo.db_fields fld ON fs.db_field_id = fld.id AND fs.form_id = 4
                inner join users.frm.control_types ct ON fs.control_type_id = ct.id
                inner join #valueTable v ON fld.id = v.db_field_id;
        ";
        $results = $this->db->query($sql)->result_array();
        return $results;

/*        $results = $this->db
            //get the meta data
            ->select('fld.id, ct.name AS control_type, fld.name, fld.label, fs.default_value, uhs.value') //, f.name AS category, s.dom_id
            ->join('users.dbo.db_field fld', "fs.db_field_id = fld.id AND fs.form_id = " . $form_id, 'inner')
            ->join('users.dbo.db_table tbl', "fld.db_table_id = tbl.id", 'inner')
            //now get value
            ->join('users.setng.user_herd_settings uhs', "s.id = uhs.setting_id AND uhs.user_id IS NULL AND (uhs.herd_code = '" . $this->herd_code . "' OR uhs.herd_code IS NULL)", 'left');
            ->join('users.frm.control_types ct', "s.control_type_id = ct.id", 'inner')
            //->where("(uhs.herd_code = '" . $this->herd_code . "' OR uhs.herd_code IS NULL)")
            ->get('users.dbo.forms_dbfields fs')
            ->result_array();
        return $results; */
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