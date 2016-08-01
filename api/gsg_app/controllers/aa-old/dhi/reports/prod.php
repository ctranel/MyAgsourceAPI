<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
class Prod extends report_parent {
    function __construct(){
        parent::__construct();
        
        /* Load the profile.php config file if it exists
         $this->config->load('profiler', false, true);
        if ($this->config->config['enable_profiler']) {
        $this->output->enable_profiler(TRUE);
        } */
        
    }

    function hs_prod($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Production Summaries';
        parent::display($block_in, $display_format);
    }

    function hs_prod_charts($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Production Charts';
        parent::display($block_in, $display_format);
    }

    function test_day($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL, $json_filter_data = NULL){
        $this->product_name = 'Test Day Results';
        parent::display($block_in, $display_format, isset($sort_by) ? urldecode($sort_by) : NULL, isset($sort_order) ? urldecode($sort_order) : NULL, isset($json_filter_data) ? urldecode($json_filter_data) : NULL);
    }

    function curr_test($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL, $json_filter_data = NULL){
        $this->product_name = 'Current Test Results';
        parent::display($block_in, $display_format, isset($sort_by) ? urldecode($sort_by) : NULL, isset($sort_order) ? urldecode($sort_order) : NULL, isset($json_filter_data) ? urldecode($json_filter_data) : NULL);
    }
    
    function current($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL, $json_filter_data = NULL){
        $this->product_name = 'Current Status';
        parent::display($block_in, $display_format, isset($sort_by) ? urldecode($sort_by) : NULL, isset($sort_order) ? urldecode($sort_order) : NULL, isset($json_filter_data) ? urldecode($json_filter_data) : NULL);
    }

    function ltd($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL, $json_filter_data = NULL){
        $this->product_name = 'Lactations to Date';
        parent::display($block_in, $display_format, isset($sort_by) ? urldecode($sort_by) : NULL, isset($sort_order) ? urldecode($sort_order) : NULL, isset($json_filter_data) ? urldecode($json_filter_data) : NULL);
    }
    
    function lact_305($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL, $json_filter_data = NULL){
        $this->product_name = '305-365 Day Lactations';
        parent::display($block_in, $display_format, isset($sort_by) ? urldecode($sort_by) : NULL, isset($sort_order) ? urldecode($sort_order) : NULL, isset($json_filter_data) ? urldecode($json_filter_data) : NULL);
    }
    
    function me_rank($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL, $json_filter_data = NULL){
        $this->product_name = 'ME Lactation Rankings';
        parent::display($block_in, $display_format, isset($sort_by) ? urldecode($sort_by) : NULL, isset($sort_order) ? urldecode($sort_order) : NULL, isset($json_filter_data) ? urldecode($json_filter_data) : NULL);
    }

    function mun_curr($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Management MUN - Current';
        parent::display($block_in, $display_format);
    }

    function mun_recent($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Management MUN - Recent';
        parent::display($block_in, $display_format);
    }
}