<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH . 'models/Listings/iListing_model.php');
require_once APPPATH . 'libraries/MssqlUtility.php';

use \myagsource\MssqlUtility;


/* -----------------------------------------------------------------
 *	CLASS comments
 *  @file: herd_options_model.php
 *  @author: ctranel
 *  @date: 2016/09/26
 *
 * -----------------------------------------------------------------
 */

class Event_listing_model extends CI_Model implements iListing_model  {

    public function __construct($args){
		parent::__construct();
        if(isset($args) && is_array($args)){
            array_walk($args, function(&$v, $k){return MssqlUtility::escape($v);});
        }
        $this->criteria = $args;
        $this->herd_code = $args['herd_code'];
        $this->serial_num = $args['serial_num'];
	}

    /**
     * getListingsByPage
     * @param page_id
     * @return array listing data
     * @author ctranel
     **/
    public function getListingsByPage($page_id) {
        $page_id = (int)$page_id;

        $results = $this->db
            ->select('pb.page_id, b.id, l.id AS listing_id, b.name, b.description, dt.name AS display_type, s.name AS scope, srt.db_field_name AS order_by, l.sort_order, l.form_id, l.delete_path, b.active, b.path, pb.list_order')
            ->join('users.dbo.blocks b', "pb.block_id = b.id AND b.display_type_id = 8 AND pb.page_id = " . $page_id . ' AND b.active = 1', 'inner')
            ->join('users.options.listings l', "b.id = l.block_id AND l.isactive = 1", 'inner')
            ->join("(
                    SELECT lci.id, f.db_field_name 
                    FROM users.options.listings_columns lci 
                        INNER JOIN users.dbo.db_fields f ON lci.db_field_id = f.id
                ) srt", 'l.order_by = srt.id', 'left'
            )
            ->join('users.dbo.lookup_display_types dt', 'b.display_type_id = dt.id', 'inner')
            ->join('users.dbo.lookup_scopes s', 'b.scope_id = s.id', 'inner')
            ->get('users.dbo.pages_blocks pb')
//            ->where('')
            ->result_array();
        return $results;
    }

    /**
     * getListingByBlock
     * @param block_id
     * @return array listing data
     * @author ctranel
     **/
    public function getListingByBlock($block_id) {
        $block_id = (int)$block_id;

        $results = $this->db
            ->select('b.id, l.id AS listing_id, b.name, b.description, dt.name AS display_type, s.name AS scope, srt.db_field_name AS order_by, l.sort_order, l.form_id, l.delete_path, b.active, b.path, 1 AS list_order')//pb.page_id, pb.list_order
            ->join('users.options.listings l', "b.id = l.block_id AND l.isactive = 1 AND b.active = 1 AND b.display_type_id = 8 AND b.id = " . $block_id, 'inner')
            ->join("(
                    SELECT lci.id, f.db_field_name 
                    FROM users.options.listings_columns lci 
                        INNER JOIN users.dbo.db_fields f ON lci.db_field_id = f.id
                ) srt", 'l.order_by = srt.id', 'left'
            )
            ->join('users.dbo.lookup_display_types dt', 'b.display_type_id = dt.id', 'inner')
            ->join('users.dbo.lookup_scopes s', 'b.scope_id = s.id', 'inner')
            ->get('users.dbo.blocks b')
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
        $listing_id = (int)$listing_id;

        $results = $this->db
            ->select('b.id, l.id AS listing_id, b.name, b.description, dt.name AS display_type, s.name AS scope, srt.db_field_name AS order_by, l.sort_order, l.form_id, l.delete_path, b.active, b.path')
            ->join('users.options.listings l', "b.id = l.block_id AND l.id = " . $listing_id, 'inner')
            ->join("(
                    SELECT lci.id, f.db_field_name 
                    FROM users.options.listings_columns lci 
                        INNER JOIN users.dbo.db_fields f ON lci.db_field_id = f.id
                ) srt", 'l.order_by = srt.id', 'left'
            )
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
        $listing_id = (int)$listing_id;

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
        $listing_id = (int)$listing_id;

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
     **/
    public function getListingData($listing_id, $criteria, $order_by, $sort_order) {
        $listing_id = (int)$listing_id;
        $order_by = MssqlUtility::escape($order_by);
        $sort_order = MssqlUtility::escape($sort_order);

        $keys = array_keys($criteria);
        $key_meta = $this->getListingKeyMeta($listing_id);
        $key_condition_text = '';

        foreach($keys as $k){
            if(strpos($key_meta[$k]['data_type'], 'char') !== false){
                $key_condition_text .= $k . " = ''" . $criteria[$k] . "'' AND ";
            }
            else {
                $key_condition_text .= $k . " = " . $criteria[$k] . " AND ";
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
			    FROM ', @db_table_name, N' WHERE " . substr($key_condition_text, 0, -5);

        if(isset($order_by) && isset($sort_order)) {
            $sql .= " ORDER BY " . $order_by . " " . $sort_order;
        }
        $sql .= "')

            EXEC (@dsql)
       ";
        //die($sql);
        $results = $this->db->query($sql)->result_array();
        return $results;
    }
}