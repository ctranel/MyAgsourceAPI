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

    public function __construct(){
		parent::__construct();
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
            ->select('pb.page_id, b.id, l.id AS listing_id, b.name, b.description, dt.name AS display_type, s.name AS scope, srt.db_field_name AS order_by, l.sort_order, l.form_id, l.delete_path, l.activate_path, b.isactive, b.path, pb.list_order')
            ->join('users.dbo.blocks b', "pb.block_id = b.id AND b.display_type_id = 8 AND pb.page_id = " . $page_id . ' AND b.isactive = 1', 'inner')
            ->join('users.options.listings l', "b.id = l.block_id", 'inner')
            ->join("(
                    SELECT lci.id, f.db_field_name, lci.list_order 
                    FROM users.options.listings_columns lci 
                        INNER JOIN users.dbo.db_fields f ON lci.db_field_id = f.id
                ) srt", 'l.order_by = srt.id', 'left'
            )
            ->join('users.dbo.lookup_display_types dt', 'b.display_type_id = dt.id', 'inner')
            ->join('users.dbo.lookup_scopes s', 'b.scope_id = s.id', 'inner')
            ->order_by('pb.list_order', 'asc')
            ->order_by('srt.list_order', 'asc')
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
            ->select('b.id, l.id AS listing_id, b.name, b.description, dt.name AS display_type, s.name AS scope, srt.db_field_name AS order_by, l.sort_order, l.form_id, l.delete_path, l.activate_path, b.isactive, b.path, 1 AS list_order')//pb.page_id, pb.list_order
            ->join('users.options.listings l', "b.id = l.block_id AND b.isactive = 1 AND b.display_type_id = 8 AND b.id = " . $block_id, 'inner')
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
            ->select('b.id, l.id AS listing_id, b.name, b.description, dt.name AS display_type, s.name AS scope, srt.db_field_name AS order_by, l.sort_order, l.form_id, l.delete_path, l.activate_path, b.isactive, b.path')
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
     * getActionData
     * @param int listing id
     * @return action data
     * @author ctranel
     **/
    public function getActionData($listing_id) {
        $listing_id = (int)$listing_id;

        $results = $this->db
            ->select('label, url, list_order, isactive')
            ->where('isactive', true)
            ->where('listing_id', $listing_id)
            ->order_by('list_order', 'asc')
            ->get('users.options.batch_actions')
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
    protected function getListingKeyMeta($listing_id) {
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
     * @method getWhereData()
     * @param int listing id
     * @return returns multi-dimensional array
     * @author ctranel
     **/
    public function getWhereData($listing_id){
        return $this->db->query(
            "SELECT NULL AS name, NULL AS description,  NULL AS unit_of_measure, null AS db_field_id, null AS table_name, null AS db_field_name, null AS pdf_width, null AS default_sort_order, null AS datatype, null AS is_timespan, null AS is_natural_sort, null AS is_foreign_key, null AS is_nullable, null AS decimal_scale, null AS datatype, null AS max_length
				, wg.operator AS group_operator, wg.id, COALESCE(wg.parent_id, 0) AS parent_id, null AS condition_id, null as operator, null AS operand
				--, null AS conversion_name, null AS metric_label, null AS metric_abbrev, null AS to_metric_factor, null AS metric_rounding_precision, null AS imperial_label, null AS imperial_abbrev, null AS to_imperial_factor, null AS imperial_rounding_precision
			FROM users.options.listing_condition_groups wg
			WHERE wg.listing_id = " . $listing_id . "
			
			UNION
			
			SELECT f.name, f.description, f.unit_of_measure, f.id AS db_field_id, t.name AS table_name, f.db_field_name, f.pdf_width, f.default_sort_order, f.data_type as datatype, f.is_timespan_field as is_timespan, f.is_natural_sort, is_fk_field AS is_foreign_key, f.is_nullable, f.decimal_points AS decimal_scale, f.data_type as datatype, f.max_length
				, wg.operator AS group_operator, wc.id, wc.condition_group_id AS parent_id, wc.id AS condition_id, wc.operator, wc.operand
				--, mc.name AS conversion_name, mc.metric_label, mc.metric_abbrev, mc.to_metric_factor, mc.metric_rounding_precision, mc.imperial_label, mc.imperial_abbrev, mc.to_imperial_factor, mc.imperial_rounding_precision
			FROM users.options.listing_condition_groups wg
                INNER JOIN users.options.listing_condition wc ON wg.listing_id = " . $listing_id . " AND wg.id = wc.condition_group_id
                INNER JOIN users.dbo.db_fields f ON wc.db_field_id = f.id
                INNER JOIN users.dbo.db_tables t ON f.db_table_id = t.id
                --LEFT JOIN users.dbo.metric_conversion mc ON f.conversion_id = mc.id
			WHERE wg.listing_id = " . $listing_id)
            ->result_array();
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
            throw new Exception('Could not find source table.');
        }
        $err = $this->db->_error_message();
        if(!empty($err)){
            throw new \Exception($err);
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

        $sql = " select lc.id, ct.name AS control_type, lc.label, lc.is_displayed, lc.display_format, lc.is_preset, fld.is_fk_field AS is_key, lc.db_field_id , fld.db_field_name AS name, '' AS default_value
                from users.options.listings_columns lc
                inner join users.dbo.db_fields fld ON lc.db_field_id = fld.id AND lc.listing_id = " . $listing_id . "
                inner join users.frm.control_types ct ON lc.control_type_id = ct.id";

        $results = $this->db->query($sql)->result_array();

        if(!$results){
            throw new Exception('No column data found.');
        }
        $err = $this->db->_error_message();
        if(!empty($err)){
            throw new \Exception($err);
        }

        return $results;
    }

    /**
     * getListingData
     *
     * @param int listing id
     * @param array criteria
     * @param string sort column
     * @param string sort order
     * @param array display column names
     * @return array column data
     * @author ctranel
     **/
    public function getListingData($listing_id, $criteria, $order_by, $sort_order, $display_cols) {
        $listing_id = (int)$listing_id;
        $order_by = MssqlUtility::escape($order_by);
        $sort_order = MssqlUtility::escape($sort_order);
        $criteria = MssqlUtility::escape(array_filter($criteria));
        $display_cols = MssqlUtility::escape(array_filter($display_cols));

/*        $keys = array_keys($criteria);
        $key_meta = $this->getListingKeyMeta($listing_id);
        $key_condition_text = '';

        //all keys from parent entities are passed, only use the ones that have data in this listing
        foreach($keys as $k){
            if(!isset($key_meta[$k])){
                continue;
            }
            $v = $criteria[$k];
            if(strpos($key_meta[$k]['data_type'], 'char') !== false){
                if(is_array($v)){
                    $v = implode("'',''", $v);
                }
                $key_condition_text .= $k . " IN(''" . $v . "'') AND ";
            }
            else {
                if(is_array($v)){
                    $v = implode(",", $v);
                }
                $key_condition_text .= $k . " IN(" . $v . ") AND ";
            }
        } */
        $key_condition_text = $this->getWhereSql($criteria);

        $sql = "
            DECLARE
                @dsql NVARCHAR(MAX)
                ,@db_table_name VARCHAR(100)

            SELECT TOP 1 @db_table_name = CONCAT(db.name, '.',  tbl.db_schema, '.', tbl.name)
                from users.options.listings_columns lc
                inner join users.dbo.db_fields fld ON lc.db_field_id = fld.id AND lc.listing_id = " . $listing_id . "
                inner join users.dbo.db_tables tbl ON fld.db_table_id = tbl.id AND allow_update = 1
                inner join users.dbo.db_databases db ON tbl.database_id = db.id

            SET @dsql = CONCAT(N'SELECT DISTINCT " . implode(',', $display_cols) . "
			    FROM ', @db_table_name, N' WHERE " . $key_condition_text;//substr($key_condition_text, 0, -5);

        if(isset($order_by) && !empty($order_by) && isset($sort_order) && !empty($sort_order)) {
            $sql .= " ORDER BY " . $order_by . " " . $sort_order;
        }
        $sql .= "')

            EXEC (@dsql)
       ";
//die($sql);

        $res = $this->db->query($sql)->result_array();
        if($res === false){
            throw new \Exception('Listing data not found.');
        }
        $err = $this->db->_error_message();

        if(!empty($err)){
            throw new \Exception($err);
        }

        if(isset($res[0]) && is_array($res[0])) {
            foreach($res as &$r){
                $r = $r + $criteria;
            }
            return $res;
        }

        return [];
    }

    /** function getWhereSql
     *
     * translates filter criteria into sql format
     * @param $where_array
     * @return void
     *
     */

    protected function getWhereSql($where_array){
        if(!isset($where_array) || !is_array($where_array)) {
            return;
        }
        $sql = '';
        $is_firstc = true;

        foreach($where_array['criteria'] as $k => $v){
            //add operator if this is not the first criteria or group
            if(!$is_firstc){
                $sql .= ' ' . $where_array['operator'] . ' ';
            }
            $is_firstc = false;

            if(is_array($v)){
                $sql .= '('. $this->getWhereSql($v). ')';
            }
            else{
                $sql .= $v;
            }
        }
        return $sql;
    }

    /**
     * getAddPresets
     *
     * @param int listing id
     * @param array criteria
     * @return array key=>value preset data
     * @author ctranel
     **/
    public function getAddPresets($listing_id, $criteria, $preset_cols) {
        $listing_id = (int)$listing_id;
 //       $order_by = MssqlUtility::escape($order_by);
  //      $sort_order = MssqlUtility::escape($sort_order);
        $criteria = MssqlUtility::escape(array_filter($criteria));

        $keys = array_keys($criteria);
        $key_meta = $this->getListingKeyMeta($listing_id);
        $key_condition_text = '';

        //all keys from parent entities are passed, only use the ones that have data in this listing
        foreach($keys as $k){
            if(!isset($key_meta[$k])){
                continue;
            }
            $v = $criteria[$k];
            if(strpos($key_meta[$k]['data_type'], 'char') !== false){
                if(is_array($v)){
                    $v = implode("'',''", $v);
                }
                $key_condition_text .= $k . " IN(''" . $v . "'') AND ";
            }
            else {
                if(is_array($v)){
                    $v = implode(",", $v);
                }
                $key_condition_text .= $k . " IN(" . $v . ") AND ";
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

            SET @dsql = CONCAT(N'SELECT DISTINCT " . implode(',', $preset_cols) . "
			    FROM ', @db_table_name, N' WHERE " . substr($key_condition_text, 0, -5);

        if(isset($order_by) && !empty($order_by) && isset($sort_order) && !empty($sort_order)) {
            $sql .= " ORDER BY " . $order_by . " " . $sort_order;
        }
        $sql .= "')

            EXEC (@dsql)
       ";
//die($sql);

        $results = $this->db->query($sql)->result_array();

        $err = $this->db->_error_message();

        if(!empty($err)){
            throw new \Exception($err);
        }

        if(isset($results[0]) && is_array($results[0])) {
            foreach($results as &$r){
                $r = $r + $criteria;
            }
            return $results;
        }

        return [];
    }
}