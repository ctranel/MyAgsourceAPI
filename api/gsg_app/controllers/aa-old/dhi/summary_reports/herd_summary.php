<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
class Herd_summary extends report_parent {
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
	 	redirect(site_url('dhi/summary_reports/herd_summary/hs_prod'));
	 }
	function hs_prod($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'Herd Summary Production';
		parent::display($block_in, $display_format);
	 }
	function hs_prod_charts($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'Herd Summary Production Charts';
		parent::display($block_in, $display_format);
	 }
	function hs_repro($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'Herd Summary Reproduction';
		parent::display($block_in, $display_format);
	 }
	function hs_gen($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'Herd Summary Genetics';
		parent::display($block_in, $display_format);
	 }

	function hs_inv($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'Herd Summary Inventory';
		parent::display($block_in, $display_format);
	 }
}