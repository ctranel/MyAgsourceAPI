<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
class Milk_Quality extends report_parent {
    function __construct(){
        parent::__construct();
        
        /* Load the profile.php config file if it exists
         $this->config->load('profiler', false, true);
        if ($this->config->config['enable_profiler']) {
        $this->output->enable_profiler(TRUE);
        } */
        
    }
    
    function uhm_risk_grp($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Risk Group Analysis';
        parent::display($block_in, $display_format);
    }
    
    function uhm_dist_scc($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Distribution by SCC';
        parent::display($block_in, $display_format);
    }
    
    function uhl_chronic($block_in = NULL, $display_format = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Chronic Cows';
        parent::display($block_in, $display_format);
    }

    function uhl_fail($block_in = NULL, $display_format = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Dry Period Failure to Cure';
        parent::display($block_in, $display_format);
    }

    function uhl_dry($block_in = NULL, $display_format = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Dry Cow List';
        parent::display($block_in, $display_format);
    }
    
    function uhl_fr_inf($block_in = NULL, $display_format = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Fresh Cow Infection List';
        parent::display($block_in, $display_format);
    }
    
    function uhl_lact_inf($block_in = NULL, $display_format = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Milking Cow New Infection List';
        parent::display($block_in, $display_format);
    }

    function uhl_response($block_in = NULL, $display_format = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Response to New Infection List';
        parent::display($block_in, $display_format);
    }

    function uhl_car_code($block_in = NULL, $display_format = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Exception List';
        parent::display($block_in, $display_format);
    }

    function uhl_high_scc($block_in = NULL, $display_format = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'High SCC Cows';
        parent::display($block_in, $display_format);
    }
        
}