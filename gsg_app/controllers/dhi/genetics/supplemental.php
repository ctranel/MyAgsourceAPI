<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Cow_quartiles extends CI_Controller {
	
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
		if (ENVIRONMENT == 'development') {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			} 
		} */
	}
	
    function index(){
		$msg = 'Direct access to this page is not allowed.';
		$this->load->view('quartiles', $msg);
    }

    function ajax_cow() {
    	$this->load('dhi/genetics/cow');
    	$arr_avg = $this->getCowAverages(
				$this->session->userdata('herd_code'),
    			$this->session->userdata('pstring'),
    			$this->session->userdata('test_date')
    	);
		$this->load->view('quartiles', $arr_avg);
    	
    }
    
}