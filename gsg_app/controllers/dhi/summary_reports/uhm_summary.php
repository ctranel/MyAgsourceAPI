<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
class Uhm_summary extends report_parent {
	function __construct(){
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
	 	redirect(site_url('dhi/summary_reports/uhm_summary/uhm_risk_grp'));
	 }
	function uhm_risk_grp($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'UHM - Risk Analysis';
		parent::display($block_in, $display_format);
	 }
	function uhm_dist_scc($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'UHM - Distribution by SCC';
		parent::display($block_in, $display_format);
	 }
	 function uhm_dim($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'UHM - DIM at First Test';
	 	parent::display($block_in, $display_format);
	 }
	 function uhm_infect($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'UHM - Infection by Lact Group';
	 	parent::display($block_in, $display_format);
	 }
	 function uhm_wgt_scc($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'UHM - Weighted Avg SCC';
	 	parent::display($block_in, $display_format);
	 }
}