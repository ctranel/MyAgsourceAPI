<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
class Fresh_Cow_List extends parent_report {
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
		$redirect_url = set_redirect_url($this->uri->uri_string(), $this->session->flashdata('redirect_url'), $this->as_ion_auth->referrer);
		$this->session->set_flashdata('redirect_url', $redirect_url);
	 	redirect(site_url('dhi/summary_reports/fresh_cow_list/fcl_cows'));
	 }
	function fcl_cows($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
		$this->product_name = 'Fresh Cow List TCI Cows';
		parent::display($block_in, $display_format);
	 }
	function fcl_heifers($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'Fresh Cow List Fresh Heifers';
		parent::display($block_in, $display_format);
	 }
	function fcl_spec($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'Fresh Cow List Special';
		parent::display($block_in, $display_format);
	 }
}