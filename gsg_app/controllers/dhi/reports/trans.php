<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
class Trans extends report_parent {
    function __construct(){
        parent::__construct();
        
        /* Load the profile.php config file if it exists
         $this->config->load('profiler', false, true);
        if ($this->config->config['enable_profiler']) {
        $this->output->enable_profiler(TRUE);
        } */
        
    }

    function keto_summary($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'KetoMonitor&trade; Summary';
        parent::display($block_in, $display_format);
    }

    function keto_list($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'KetoMonitor&trade; Cow List';
        parent::display($block_in, $display_format);
    }
    
    function keto_cows_due($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'KetoMonitor&trade; Cows Due';
        parent::display($block_in, $display_format);
    }

    function fc_tci($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Transition Cow Index';
        parent::display($block_in, $display_format);
    }

    function fc_health($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Udder Health';
        parent::display($block_in, $display_format);
    }

    function fc_cull($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Early Culling';
        parent::display($block_in, $display_format);
    }
    
    function fcl_cows($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Fresh Cow List TCI Cows';
        parent::display($block_in, $display_format);
    }
    
    function fcl_heifers($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Fresh Cow List Fresh Heifers';
        parent::display($block_in, $display_format);
    }
    
    function fcl_spec($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Fresh Cow List Special';
        parent::display($block_in, $display_format);
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