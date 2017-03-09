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

interface iListing_model {
    public function getListingsByPage($page_id);
    public function getListingByBlock($block_id);
    public function getListingById($listing_id);
    public function getListingColumnMeta($listing_id);
    public function getListingData($listing_id, $criteria, $order_by, $sort_order, $display_cols);
}