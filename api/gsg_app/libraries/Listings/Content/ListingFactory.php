<?php

namespace myagsource\Listings\Content;

require_once(APPPATH . 'libraries/Listings/Content/Listing.php');
require_once(APPPATH . 'libraries/Listings/iListingFactory.php');
require_once(APPPATH . 'libraries/Listings/Content/Columns/ListingColumn.php');
require_once(APPPATH . 'libraries/Listings/Content/Actions/ListingActions.php');
require_once(APPPATH . 'libraries/Listings/Content/Actions/ListingAction.php');
require_once(APPPATH . 'libraries/Listings/Content/Conditions/WhereGroup.php');
require_once(APPPATH . 'libraries/Listings/Content/Conditions/WhereCriteria.php');
require_once(APPPATH . 'models/Listings/herd_options_model.php');
require_once APPPATH . 'libraries/Datasource/DbObjects/DbField.php';

use \myagsource\Listings\iListingFactory;
use \myagsource\Listings\Content\Columns\ListingColumn;
use \myagsource\Listings\Content\Actions\ListingActions;
use \myagsource\Listings\Content\Actions\ListingAction;
use \myagsource\Listings\Content\Conditions\WhereGroup;
use \myagsource\Listings\Content\Conditions\WhereCriteria;
use \myagsource\Datasource\DbObjects\DbField;

/**
 * A factory for listing objects
 * 
 * 
 * @name ListingFactory
 * @author ctranel
 * 
 *        
 */
class ListingFactory implements iListingFactory {
	/**
	 * datasource_blocks
	 * @var listing_model
	 **/
	protected $datasource;

	function __construct($datasource) {//, \db_field_model $datasource_dbfield
		$this->datasource = $datasource;
	}
	
	/*
	 * getListing
	 * 
	 * @param int listing id
	 * @param string herd code
	 * @author ctranel
	 * @returns \myagsource\Listing
	 */
	public function getListing($listing_id, $criteria){
		$results = $this->datasource->getListingById($listing_id);
		if(empty($results)){
			return false;
		}

		return $this->createListing($results[0], $criteria);
	}

    /*
     * getByBlock
     *
     * @param int block_id
     * @author ctranel
     * @returns array of Listing objects
     */
    public function getByBlock($block_id, $criteria){
        $listings = [];

        $results = $this->datasource->getListingByBlock($block_id);
        if(empty($results)){
            return [];
        }

        foreach($results as $r){
            $listings[$r['list_order']] = $this->createListing($r, $criteria);
        }
        return $listings;
    }

    /*
     * getByPage
     *
     * @param int page_id
         * @param string herd_code
     * @author ctranel
     * @returns array of Listing objects
     */
    public function getByPage($page_id, $criteria){//$herd_code = null, $serial_num = null){
        $listings = [];
        //unset herd code and ser num?
        $results = $this->datasource->getListingsByPage($page_id);
        if(empty($results)){
            return [];
        }

        foreach($results as $r){
            $listings[$r['list_order']] = $this->createListing($r, $criteria);
        }
        return $listings;
    }

	/*
    * createListing
    *
    * @param array of listing data
    * @param string herd code
    * @author ctranel
    * @returns Array of Listings
    */
    protected function createListing($listing_data, $criteria){
        $column_data = $this->datasource->getListingColumnMeta($listing_data['listing_id']);
        $display_cols = array_diff(array_column($column_data, 'name'), array_keys($criteria));
        $preset_cols = array_filter($column_data, function($v, $k) {
            return $v['is_preset'] == true;
        }, ARRAY_FILTER_USE_BOTH);
        $preset_cols = array_column($preset_cols, 'name');
        $action_data = $this->datasource->getActionData($listing_data['listing_id']);
        $where_group = $this->getWhereGroups($listing_data['listing_id'], $criteria);

        $dataset = $this->datasource->getListingData($listing_data['listing_id'], $where_group->criteria(), $listing_data['order_by'], $listing_data['sort_order'], $display_cols);//, implode(', ', array_column($column_data, 'name')));

        $add_presets = [];
        if(!empty($preset_cols)){
            $add_presets = $this->datasource->getAddPresets($listing_data['listing_id'], $criteria, $preset_cols);
        }

        //listing columns
        $lc = [];
        if(is_array($column_data) && !empty($column_data) && is_array($column_data[0])){
            foreach($column_data as $d){
                $lc[$d['name']] = new ListingColumn($d);
            }
        }

        //listing actions
        $actions = null;
        if(is_array($action_data) && !empty($action_data) && is_array($action_data[0])){
            $la = [];
            foreach($action_data as $d){
                $la[] = new ListingAction($d);
            }
            $actions = new ListingActions($la);
        }

        return new Listing($listing_data['listing_id'], $listing_data['form_id'], $listing_data['delete_path'], $listing_data['activate_path'], $lc, $actions, $dataset, $listing_data['isactive'], array_merge($add_presets, $criteria), $listing_data['order_by'], $listing_data['sort_order']);
    }

    /**
     * getWhereGroups()
     *
     * @return void
     * @author ctranel
     **/
    protected function getWhereGroups($listing_id, $request_criteria){
        $data = $this->datasource->getWhereData($listing_id);

        if(isset($request_criteria) && is_array($request_criteria) && (!is_array($data) || empty($data))){
            $data[] = [
                'name' => null,
                'description' => null,
                'unit_of_measure' => null,
                'db_field_id' => null,
                'table_name' => null,
                'db_field_name' => null,
                'pdf_width' => null,
                'default_sort_order' => null,
                'datatype' => null,
                'is_timespan' => 0,
                'is_natural_sort' => 0,
                'is_foreign_key' => 0,
                'is_nullable' => 0,
                'decimal_scale' => null,
                'max_length' => null,
                'id' => 1,
                'parent_id' => 0,
                'condition_id' => null,
                'group_operator' => 'and',
                'operator' => 'null',
                'operand' => null,
            ];
        }
        if(isset($request_criteria) && is_array($request_criteria)){
            foreach($request_criteria as $k => $c){
                $data[] = [
                    'name' => null,
                    'description' => null,
                    'unit_of_measure' => null,
                    'db_field_id' => null,
                    'table_name' => null,
                    'db_field_name' => $k,
                    'pdf_width' => null,
                    'default_sort_order' => null,
                    'datatype' => null,
                    'is_timespan' => 0,
                    'is_natural_sort' => 0,
                    'is_foreign_key' => 0,
                    'is_nullable' => 0,
                    'decimal_scale' => null,
                    'max_length' => null,
                    'id' => null,
                    'parent_id' => $data[0]['id'],
                    'condition_id' => 99,
                    'group_operator' => 'and',
                    'operator' => '=',
                    'operand' => $c,
                ];
            }
        }

        if(!is_array($data) || empty($data)){
            return;
        }

        return $this->buildWhereTree($data);
    }

    /**
     * buildWhereTree()
     *
     * Recursive function that returns children (where conditions and group (groups are recursive))
     *
     * @param array of data
     * @param int parent_id
     * @param string parent_operator
     * @return array of tree branch
     * @author ctranel
     **/
    public function buildWhereTree(array $data, $parent_id = 0, $parent_operator = 'and'){
        if(!isset($data) || !is_array($data)){
            return;
        }

        $criteria = [];
        $children = [];
        foreach ($data as $k=>$s) {
            if ($s['parent_id'] == $parent_id) {
                $newdata = $data;
                unset($newdata[$k]);

                $data_conversion = null;

                if(isset($s['condition_id'])) {
                    /*if (isset($s['conversion_name'])) {
                        $data_conversion = new DataConversion($s['conversion_name'], $s['metric_label'],
                            $s['metric_abbrev'],
                            $s['to_metric_factor'], $s['metric_rounding_precision'], $s['imperial_label'],
                            $s['imperial_abbrev'], $s['to_imperial_factor'], $s['imperial_rounding_precision']);
                    }*/
                    $criteria_datafield = new DbField($s['db_field_id'], $s['table_name'], $s['db_field_name'],
                        $s['name'], $s['description'], $s['pdf_width'], $s['default_sort_order'],
                        $s['datatype'], $s['max_length'], $s['decimal_scale'], $s['unit_of_measure'], $s['is_timespan'],
                        $s['is_foreign_key'], $s['is_nullable'], $s['is_natural_sort'], $data_conversion);
                    $criteria[] = new WhereCriteria($criteria_datafield, $s['operator'], $s['operand']);
                }
                else{
                    $children[] = $this->buildWhereTree($newdata, $s['id'], $s['group_operator']);
                }
            }
        }
        if(count($criteria) > 0 || count($children) > 0){
            return new WhereGroup($parent_operator, $criteria, $children);
        }
    }
}
