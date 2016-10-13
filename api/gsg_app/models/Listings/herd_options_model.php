<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//shouldn't need this line, but CI_Model is not found without it
require_once(BASEPATH . 'core/Model.php');


/* -----------------------------------------------------------------
 *	CLASS comments
 *  @file: herd_options_model.php
 *  @author: ctranel
 *  @date: 2016/09/26
 *
 * -----------------------------------------------------------------
 */

class Herd_options_model extends CI_Model {
    /**
     * herd_code
     * @var string
     **/
    protected $herd_code;

    /**
     * table_name
     * @var string
     **/
    protected $table_name;


    public function __construct($args){
		parent::__construct();
        $this->criteria = $args;
        $this->herd_code = $args['herd_code'];
	}

    /**
     * getListingsByPage
     * @param page_id
     * @return string category
     * @author ctranel
     **/
    public function getListingsByPage($page_id) {
        $results = $this->db
            ->select('pb.page_id, b.id, l.id AS listing_id, b.name, b.description, dt.name AS display_type, s.name AS scope, l.form_id, b.active, b.path, pb.list_order')
            ->join('users.dbo.blocks b', "pb.block_id = b.id AND b.display_type_id = 8 AND pb.page_id = " . $page_id . ' AND b.active = 1', 'inner')
            ->join('users.options.listings l', "b.id = l.block_id AND l.isactive = 1", 'inner')
            ->join('users.dbo.lookup_display_types dt', 'b.display_type_id = dt.id', 'inner')
            ->join('users.dbo.lookup_scopes s', 'b.scope_id = s.id', 'inner')
            ->get('users.dbo.pages_blocks pb')
//            ->where('')
            ->result_array();
        return $results;
    }


    /**
     * getListingById
     * @param int listing id
     * @return listing data
     * @author ctranel
     **/
    public function getListingById($listing_id) {
        $results = $this->db
            ->select('b.id, l.id AS listing_id, b.name, b.description, dt.name AS display_type, s.name AS scope, l.form_id, b.active, b.path')
            ->join('users.options.listings l', "b.id = l.block_id AND l.id = " . $listing_id, 'inner')
            ->join('users.dbo.lookup_display_types dt', 'b.display_type_id = dt.id', 'inner')
            ->join('users.dbo.lookup_scopes s', 'b.scope_id = s.id', 'inner')
            ->get('users.dbo.blocks b')
            ->result_array();
        return $results;
    }

    /**
     * getListingKeyMeta
     *
     * @param int listing id
     * @return string category
     * @author ctranel
     **/
    protected function getListingKeyMeta($listing_id)
    {
        $ret = [];

        $results = $this->db
            ->select('fld.db_field_name AS name, fld.data_type, fld.max_length')
            ->join('users.dbo.db_fields fld', 'lc.db_field_id = fld.id AND fld.is_fk_field = 1 AND lc.listing_id = ' . $listing_id)
            ->get('users.options.listings_columns lc')
            ->result_array();
        if(is_array($results)){
            foreach($results as $r){
                $ret[$r['name']] = $r;
            }
        }
        return $ret;
    }

    /**
     * getSourceTable
     *
     * @param int $listing_id
     * @return string table name
     * @author ctranel
     **/
    protected function getSourceTable($listing_id)
    {
        $sql = " select DISTINCT CONCAT(db.name, '.',  tbl.db_schema, '.', tbl.name) AS db_table_name
                from users.options.listings_columns lc
                inner join users.dbo.db_fields fld ON lc.db_field_id = fld.id AND lc.listing_id = " . $listing_id . "
                inner join users.dbo.db_tables tbl ON fld.db_table_id = tbl.id AND allow_update = 1
                inner join users.dbo.db_databases db ON tbl.database_id = db.id";

        $results = $this->db->query($sql)->result_array();

        if(!$results){
            throw new Exception('No data found.');
        }

        if(count($results) > 1){
            throw new Exception('There must be only one source for listing data.');
        }
        return $results[0]['db_table_name'];
    }

    /**
     * getListingColumnMeta
     *
     * @param int listing id
     * @return array column data
     * @author ctranel
     **/
    public function getListingColumnMeta($listing_id) {
        $sql = " select lc.id, ct.name AS control_type, lc.label, lc.is_displayed, fld.is_fk_field AS is_key, lc.db_field_id , fld.db_field_name AS name, '' AS default_value
                from users.options.listings_columns lc
                inner join users.dbo.db_fields fld ON lc.db_field_id = fld.id AND lc.listing_id = " . $listing_id . "
                inner join users.frm.control_types ct ON lc.control_type_id = ct.id";

        $results = $this->db->query($sql)->result_array();

        if(!$results){
            throw new Exception('No meta data found.');
        }
        return $results;
    }

    /**
     * getListingData
     *
     * @param int listing id
     * @return array column data
     * @author ctranel
    public function getListingData($listing_id, $field_list) {
        $keys = array_keys($this->criteria);
        $key_meta = $this->getListingKeyMeta($listing_id);

        $key_condition_text = '';
        $declare_key_var_text = '';
        $set_key_var_text = '';

//var_dump($keys, $key_meta);
        foreach($keys as $k){
            $declare_key_var_text .= ", @" . $k . " " . $key_meta[$k]['data_type'];
            if(strpos($key_meta[$k]['data_type'], 'char') !== false){
                $declare_key_var_text .= '(' .  $key_meta[$k]['max_length'] . ')';
                $key_condition_text .= "'" . $k . " = ''" . $this->criteria[$k] . "''' AND ',";
            }
            else {
                $key_condition_text .= "'" . $k . " = " . $this->criteria[$k] . " AND ', ";
            }
            $set_key_var_text .= "SET @" . $k . " = '" . $this->criteria[$k] . "';";
        }

        $sql = "
            DECLARE 
                @dsql NVARCHAR(MAX)
                ,@db_table_name VARCHAR(100)
            
            IF OBJECT_ID('tempdb..##valueTable', 'U') IS NOT NULL
              DROP TABLE ##valueTable;
            
            SELECT TOP 1 @db_table_name = CONCAT(db.name, '.',  tbl.db_schema, '.', tbl.name)
                from users.options.listings_columns lc
                inner join users.dbo.db_fields fld ON lc.db_field_id = fld.id AND lc.listing_id = 1
                inner join users.dbo.db_tables tbl ON fld.db_table_id = tbl.id AND allow_update = 1
                inner join users.dbo.db_databases db ON tbl.database_id = db.id
            
            SET @dsql = CONCAT(N'SELECT *
			    INTO ##valueTable
			    FROM ', @db_table_name, ' WHERE ', " . substr($key_condition_text, 0, -7) . ")

            EXEC sp_executesql @dsql
            
            SELECT " . $field_list . " FROM ##valueTable;
       ";
//print($sql);
        $results = $this->db->query($sql)->result_array();
//var_dump($results);
        return $results;
    }
**/

    /**
     * getListingData
     *
     * @param int listing id
     * @return array column data
     * @author ctranel
     **/
    public function getListingData($listing_id) {
        $keys = array_keys($this->criteria);
        $key_meta = $this->getListingKeyMeta($listing_id);
        $key_condition_text = '';

        foreach($keys as $k){
            if(strpos($key_meta[$k]['data_type'], 'char') !== false){
                $key_condition_text .= "N'" . $k . " = ''" . $this->criteria[$k] . "''' AND ',";
            }
            else {
                $key_condition_text .= "N'" . $k . " = " . $this->criteria[$k] . " AND ', ";
            }
        }

        $sql = "
            DECLARE
                @dsql NVARCHAR(MAX)
                ,@db_table_name VARCHAR(100)

            SELECT TOP 1 @db_table_name = CONCAT(db.name, '.',  tbl.db_schema, '.', tbl.name)
                from users.options.listings_columns lc
                inner join users.dbo.db_fields fld ON lc.db_field_id = fld.id AND lc.listing_id = " . $listing_id . "
                inner join users.dbo.db_tables tbl ON fld.db_table_id = tbl.id AND allow_update = 1
                inner join users.dbo.db_databases db ON tbl.database_id = db.id

            SET @dsql = CONCAT(N'SELECT *
			    FROM ', @db_table_name, N' WHERE ', " . substr($key_condition_text, 0, -7) . ")

            EXEC (@dsql)
       ";
        //print($sql);
        $results = $this->db->query($sql)->result_array();
        //var_dump($results);
        return $results;
    }


}