<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
class Genetic_summary extends report_parent {
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
		$this->session->keep_all_flashdata();
	 	redirect(site_url('dhi/summary_reports/genetic_summary/gen_over'));
	 }

	function gen_over($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'Genetic Overview';
		parent::display($block_in, $display_format);
	 }

	function ann_trends($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'Inbreeding Trend';
		parent::display($block_in, $display_format);
	 }
	 
	function cow_trends($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'Cow Trend Graphs';
		parent::display($block_in, $display_format);
	 }
	 
	function inbreeding($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'Inbreeding Analysis';
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

	 function young_anl($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'Youngstock Analysis';
	 	parent::display($block_in, $display_format);
	 }
	 
}