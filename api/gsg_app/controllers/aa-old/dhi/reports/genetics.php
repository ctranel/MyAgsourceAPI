<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
class Genetics extends report_parent {
    function __construct(){
        parent::__construct();
        
        /* Load the profile.php config file if it exists
         $this->config->load('profiler', false, true);
        if ($this->config->config['enable_profiler']) {
        $this->output->enable_profiler(TRUE);
        } */
        
    }

    function gsg_cow($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL, $json_filter_data = NULL){
        $this->product_name = 'Cow Selection Guide';
        parent::display($block_in, $display_format, isset($sort_by) ? urldecode($sort_by) : NULL, isset($sort_order) ? urldecode($sort_order) : NULL, isset($json_filter_data) ? urldecode($json_filter_data) : NULL);
    }
    
    function gsg_heifer($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL, $json_filter_data = NULL){
        $this->product_name = 'Heifer Selection Guide';
        parent::display($block_in, $display_format, isset($sort_by) ? urldecode($sort_by) : NULL, isset($sort_order) ? urldecode($sort_order) : NULL, isset($json_filter_data) ? urldecode($json_filter_data) : NULL);
    }

    function gsg_progeny($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL, $json_filter_data = NULL){
        $this->product_name = 'Progeny Selection Guide';
        parent::display($block_in, $display_format, isset($sort_by) ? urldecode($sort_by) : NULL, isset($sort_order) ? urldecode($sort_order) : NULL, isset($json_filter_data) ? urldecode($json_filter_data) : NULL);
    }
    
    function pta_cow($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL, $json_filter_data = NULL){
        $this->product_name = 'PTA Cows';
        parent::display($block_in, $display_format, isset($sort_by) ? urldecode($sort_by) : NULL, isset($sort_order) ? urldecode($sort_order) : NULL, isset($json_filter_data) ? urldecode($json_filter_data) : NULL);
    }
    
    function pta_heifer($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL, $json_filter_data = NULL){
        $this->product_name = 'PTA Heifers';
        parent::display($block_in, $display_format, isset($sort_by) ? urldecode($sort_by) : NULL, isset($sort_order) ? urldecode($sort_order) : NULL, isset($json_filter_data) ? urldecode($json_filter_data) : NULL);
    }
    
    function missing_ptas($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL, $json_filter_data = NULL){
        $this->product_name = 'Missing PTAs';
        parent::display($block_in, $display_format, isset($sort_by) ? urldecode($sort_by) : NULL, isset($sort_order) ? urldecode($sort_order) : NULL, isset($json_filter_data) ? urldecode($json_filter_data) : NULL);
    }
    
    function gen_over($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Genetic Summary';
        parent::display($block_in, $display_format);
    }
    
    function ann_trends($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Annual Trends';
        parent::display($block_in, $display_format);
    }
    
    function cow_trends($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Cow Trend Graphs';
        parent::display($block_in, $display_format);
    }
    
    function sire_anl($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Sire Analysis';
        parent::display($block_in, $display_format);
    }
    
    function serv_sire_anl($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Service Sire Analysis';
        parent::display($block_in, $display_format);
    }

    function inbreeding($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Inbreeding Analysis';
        parent::display($block_in, $display_format);
    }
    
    function young_anl($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Youngstock Analysis';
        parent::display($block_in, $display_format);
    }

    function sire_values($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Sire Values by Lactation';
        parent::display($block_in, $display_format);
    }
     
    
}