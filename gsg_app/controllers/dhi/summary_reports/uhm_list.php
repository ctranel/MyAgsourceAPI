<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
class UHM_List extends report_parent {
	function __construct(){
//		$this->section_path = 'herd_summary'; //this should match the name of this file (minus ".php".  Also used as base for css and js file names and model directory name
//		$this->primary_model = 'herd_summary_model';
		parent::__construct();
		/* Load the profile.php config file if it exists
		$this->config->load('profiler', false, true);
		if ($this->config->config['enable_profiler']) {
			$this->output->enable_profiler(TRUE);
		} */
	}

	 function index($block_in = NULL, $display_format = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
		$this->session->keep_all_flashdata();
	 	redirect(site_url('dhi/summary_reports/uhm_list/uhl_chronic'));
	 }
	function uhl_chronic($block_in = NULL, $display_format = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'UHM Chronic Cow List';
		parent::display($block_in, $display_format);
	 }
	function uhl_fail($block_in = NULL, $display_format = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'UHM Dry Period Failure to Cure List';
		parent::display($block_in, $display_format);
	 }
	 	function uhl_dry($block_in = NULL, $display_format = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'UHM Dry Cow List';
		parent::display($block_in, $display_format);
	 }
	function uhl_fr_inf($block_in = NULL, $display_format = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'UHM Fresh Cow Infection List';
		parent::display($block_in, $display_format);
	 }
	function uhl_lact_inf($block_in = NULL, $display_format = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'UHM Lactating Cow New Infection List';
		parent::display($block_in, $display_format);
	 }
	function uhl_response($block_in = NULL, $display_format = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'UHM Response to New Infection List';
		parent::display($block_in, $display_format);
	 }
	function uhl_car_code($block_in = NULL, $display_format = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'UHM CAR Codes & Milk Withheld Indicator';
		parent::display($block_in, $display_format);
	 }
	function uhl_high_scc($block_in = NULL, $display_format = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'UHM High SCC Cows';
		parent::display($block_in, $display_format);
	 }
}