<?php

namespace myagsource\Listings\Content;

require_once(APPPATH . 'libraries/Listings/Content/Listing.php');
require_once(APPPATH . 'libraries/Listings/iListingFactory.php');
require_once(APPPATH . 'libraries/Listings/Content/Columns/ListingColumn.php');
require_once(APPPATH . 'models/Listings/herd_options_model.php');

use \myagsource\Listings\iListingFactory;
use \myagsource\Listings\Content\Columns\ListingColumn;

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
	public function getListing($listing_id, $herd_code){
		$results = $this->datasource->getListingById($listing_id);
		if(empty($results)){
			return false;
		}

		return $this->createListing($results[0], $herd_code);
	}

	/*
    * createListing
    *
    * @param array of listing data
    * @param string herd code
    * @author ctranel
    * @returns Array of Listings
    */
    protected function createListing($listing_data, $herd_code){
        $column_data = $this->datasource->getListingColumnMeta($listing_data['listing_id']);
        $dataset = $this->datasource->getListingData($listing_data['listing_id'], implode(', ', array_column($column_data, 'field_name')));

        $lc = [];
//var_dump($column_data);
        if(is_array($column_data) && !empty($column_data) && is_array($column_data[0])){
//var_dump('column_data', $column_data); //die;
            foreach($column_data as $d){
                $lc[$d['field_name']] = new ListingColumn($d);
            }
        }
        return new Listing($listing_data['listing_id'], $listing_data['form_id'], $lc, $dataset, $listing_data['active']);
    }

    /*
     * getByPage
     *
     * @param int page_id
         * @param string herd_code
     * @author ctranel
     * @returns array of Listing objects
     */
    public function getByPage($page_id, $herd_code = null){
        $listings = [];
        $results = $this->datasource->getListingsByPage($page_id);
        if(empty($results)){
            return [];
        }

        foreach($results as $r){
            $listings[$r['list_order']] = $this->dataToObject($r, $herd_code);
        }
        return $listings;
    }

    /*
    * dataToObject
    *
    * @param array of listing data
     * @param string herd_code
    * @author ctranel
    * @returns Array of Listings
    */
    protected function dataToObject($listing_data, $herd_code = null){
        $f = $this->getListing($listing_data['listing_id'], $herd_code);
        return $f;
    }
}
