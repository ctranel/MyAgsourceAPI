<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
class Culling extends report_parent {
    function __construct(){
        parent::__construct();
        
        /* Load the profile.php config file if it exists
         $this->config->load('profiler', false, true);
        if ($this->config->config['enable_profiler']) {
        $this->output->enable_profiler(TRUE);
        } */
        
    }
    
    function hs_inv($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Culling Summary';
        parent::display($block_in, $display_format);
    }
    
    function culled_cows($block_in = NULL, $display_format = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Culled Cow List';
        parent::display($block_in, $display_format);
    }
    
    function culled_heifers($block_in = NULL, $display_format = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
        $this->product_name = 'Culled Heifer List';
        parent::display($block_in, $display_format);
    }    
    
}