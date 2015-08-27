<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Keto_supplemental extends CI_Controller {
	
	function __construct(){
		parent::__construct();
		$this->session->keep_flashdata('message');
		$this->session->keep_flashdata('redirect_url');
		//make sure previous page remains as the redirect url 
		$redirect_url = set_redirect_url($this->uri->uri_string(), $this->session->flashdata('redirect_url'), $this->as_ion_auth->referrer);
		$this->session->set_flashdata('redirect_url', $redirect_url);
		
		if((!isset($this->as_ion_auth) || !$this->as_ion_auth->logged_in()) && $this->session->userdata('herd_code') != $this->config->item('default_herd')){
			$msg = $this->load->view('session_expired', array('url'=>$this->session->flashdata('redirect_url')), true);
			echo $msg;
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
		$redirect_url = set_redirect_url($this->uri->uri_string(), $this->session->flashdata('redirect_url'), $this->as_ion_auth->referrer);
		$this->session->set_flashdata('redirect_url', $redirect_url);
    	$msg = 'Direct access to this page is not allowed.';
		$this->load->view('dhi/genetics/quartiles', array('msg'=>$msg));
    }

    function ajax_summary() {
		$this->load->model('dhi/summary_reports/keto/keto_model');
    	$tip = $this->keto_model->getKetoPageTip($this->session->userdata('herd_code'));
		$this->load->view('dhi/summary_reports/ketomonitor/pagesupp', $tip);
    }
}