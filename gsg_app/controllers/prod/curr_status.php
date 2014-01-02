<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
class Curr_Status extends parent_report {
	function __construct(){
		parent::__construct();
		/* Load the profile.php config file if it exists
		$this->config->load('profiler', false, true);
		if ($this->config->config['enable_profiler']) {
			$this->output->enable_profiler(TRUE);
		} */
	}

	function index($block_in = NULL, $sort_by = NULL, $sort_order = NULL, $display_format = NULL, $json_filter_data = NULL){
		$this->product_name = 'Current Status';
		parent::display($block_in, $display_format, isset($sort_by) ? urldecode($sort_by) : NULL, isset($sort_order) ? urldecode($sort_order) : NULL, isset($json_filter_data) ? urldecode($json_filter_data) : NULL);
	}

	/*
	 * ajax_report: Called via AJAX to populate graphs
	 * to add flexibility (any graph/table can be called from any page),
	 * all block generation code has been moved to the report parent ajax_report function
	 */
}