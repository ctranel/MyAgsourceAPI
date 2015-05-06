<?php
require_once APPPATH . 'controllers/report_parent.php';

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Land extends report_parent {
	
	function __construct(){
		parent::__construct();
	}


	function index($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL, $json_filter_data = NULL){
		$this->product_name = 'Benchmark Overview';
		parent::display($block_in, $display_format, isset($sort_by) ? urldecode($sort_by) : NULL, isset($sort_order) ? urldecode($sort_order) : NULL, isset($json_filter_data) ? urldecode($json_filter_data) : NULL);
	}
}
