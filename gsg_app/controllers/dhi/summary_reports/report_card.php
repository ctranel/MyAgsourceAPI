<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
class Report_card extends parent_report {
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

	 function index($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	redirect(site_url('dhi/summary_reports/report_card/rc_curr'));
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