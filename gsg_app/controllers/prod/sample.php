<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
class Sample extends parent_report {
	function __construct(){
		parent::__construct();
		/* Load the profile.php config file if it exists
		$this->config->load('profiler', false, true);
		if ($this->config->config['enable_profiler']) {
			$this->output->enable_profiler(TRUE);
		} */
	}

	function index($block_in = NULL, $sort_by = NULL, $sort_order = NULL, $display_format = NULL){
	 	$this->product_name = 'Production - Sample Day';
		parent::display($block_in, $display_format);
	}
	 
	function prod_fi($block_in = NULL, $sort_by = NULL, $sort_order = NULL, $display_format = NULL){
		$this->product_name = 'Sample Day Flex Items';
		parent::display($block_in, $display_format);
	}
	 /*
	 * ajax_report: Called via AJAX to populate graphs
	 * to add flexibility (any graph/table can be called from any page),
	 * all block generation code has been moved to the report parent ajax_report function
	 */
}