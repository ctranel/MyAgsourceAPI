<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
class Fresh_cow_summary extends parent_report {
	function __construct(){
		parent::__construct();
		/* Load the profile.php config file if it exists
		$this->config->load('profiler', false, true);
		if ($this->config->config['enable_profiler']) {
			$this->output->enable_profiler(TRUE);
		} */
	}

	 function index($block_in = NULL, $sort_by = NULL, $sort_order = NULL, $display_format = NULL){
	 	redirect(site_url('summary_reports/fresh_cow_summary/fc_tci'));
	 }
	function fc_tci($block_in = NULL, $sort_by = NULL, $sort_order = NULL, $display_format = NULL){
	 	$this->product_name = 'Fresh Cow Summary - TCI';
		parent::display($block_in, $display_format);
	 }
	function fc_cull($block_in = NULL, $sort_by = NULL, $sort_order = NULL, $display_format = NULL){
	 	$this->product_name = 'Fresh Cow Summary - Culling';
		parent::display($block_in, $display_format);
	 }
	function fc_health($block_in = NULL, $sort_by = NULL, $sort_order = NULL, $display_format = NULL){
	 	$this->product_name = 'Fresh Cow Summary - Health';
		parent::display($block_in, $display_format);
	 }
	 
	 /*
	 * ajax_report: Called via AJAX to populate graphs
	 * to add flexibility (any graph/table can be called from any page),
	 * all block generation code has been moved to the report parent ajax_report function
	 */
}