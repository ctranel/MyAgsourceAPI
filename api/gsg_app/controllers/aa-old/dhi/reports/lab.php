<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
class Lab extends report_parent {
    function __construct(){
        parent::__construct();
        
        /* Load the profile.php config file if it exists
         $this->config->load('profiler', false, true);
        if ($this->config->config['enable_profiler']) {
        $this->output->enable_profiler(TRUE);
        } */
        
    }
    
	function bulk_scc($block_in = NULL, $display_format = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'Bulk Tank SCC Contribution';
		parent::display($block_in, $display_format);
	 }

	 function johnes($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL, $json_filter_data = NULL){
	     $this->product_name = 'Johnes Results';
	     parent::display($block_in, $display_format, isset($sort_by) ? urldecode($sort_by) : NULL, isset($sort_order) ? urldecode($sort_order) : NULL, isset($json_filter_data) ? urldecode($json_filter_data) : NULL);
	 }
	 
	 function milk_preg($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL, $json_filter_data = NULL){
	     $this->product_name = 'Milk Pregnancy';
	     parent::display($block_in, $display_format, isset($sort_by) ? urldecode($sort_by) : NULL, isset($sort_order) ? urldecode($sort_order) : NULL, isset($json_filter_data) ? urldecode($json_filter_data) : NULL);
	 }
	 
	 function culture($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL, $json_filter_data = NULL){
	     $this->product_name = 'Culture Results';
	     parent::display($block_in, $display_format, isset($sort_by) ? urldecode($sort_by) : NULL, isset($sort_order) ? urldecode($sort_order) : NULL, isset($json_filter_data) ? urldecode($json_filter_data) : NULL);
	 }

	 function pcr_results($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL, $json_filter_data = NULL){
	     $this->product_name = 'PCR Results';
	     parent::display($block_in, $display_format, isset($sort_by) ? urldecode($sort_by) : NULL, isset($sort_order) ? urldecode($sort_order) : NULL, isset($json_filter_data) ? urldecode($json_filter_data) : NULL);
	 }
	 
}