<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Fresh_cow_supplemental extends MY_Controller {
	
	function __construct(){
		parent::__construct();
		$this->session->keep_all_flashdata();
				
		if((!isset($this->as_ion_auth) || !$this->as_ion_auth->logged_in()) && $this->session->userdata('herd_code') != $this->config->item('default_herd')){
			$this->load->view('session_expired', ['url'=>$this->session->flashdata('redirect_url')]);
			exit;
		}
		
		/* Load the profile.php config file if it exists
		if (ENVIRONMENT == 'development' || ENVIRONMENT == 'localhost') {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			} 
		} */
	}
	
    function index(){
		$this->ajax_tci_culling();
    }

    function ajax_tci_culling() {
	 	$this->load->model('dhi/summary_reports/fresh_cow_summary_model');
    	$tip = $this->fresh_cow_summary_model->getFCPageTip($this->session->userdata('herd_code'));
		header("Cache-Control: no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header("Expires: -1");
		$this->load->view('dhi/summary_reports/fresh_cow_summary/pagesupp', $tip);
    }
}