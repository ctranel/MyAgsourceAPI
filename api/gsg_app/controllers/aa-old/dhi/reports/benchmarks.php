<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
class Benchmarks extends report_parent {
    function __construct(){
        parent::__construct();
        
        /* Load the profile.php config file if it exists
         $this->config->load('profiler', false, true);
        if ($this->config->config['enable_profiler']) {
        $this->output->enable_profiler(TRUE);
        } */
        
    }

    function benchrep($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL, $json_filter_data = NULL){
        $this->product_name = 'Benchmark Report';
        parent::display($block_in, $display_format, isset($sort_by) ? urldecode($sort_by) : NULL, isset($sort_order) ? urldecode($sort_order) : NULL, isset($json_filter_data) ? urldecode($json_filter_data) : NULL);
    }
    
    function rc_curr($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Report Card - Current Test';
        parent::display($block_in, $display_format);
    }
    
    function rc_long($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Report Card - Long View';
        parent::display($block_in, $display_format);
    }
        
}