<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Supplemental extends CI_Controller {
	
	function __construct(){
		parent::__construct();
		$this->session->keep_flashdata('message');
		$this->session->keep_flashdata('redirect_url');
		//make sure previous page remains as the redirect url 
		$redirect_url = set_redirect_url($this->uri->uri_string(), $this->session->flashdata('redirect_url'), $this->as_ion_auth->referrer);
		$this->session->set_flashdata('redirect_url', $redirect_url);
		
		if((!isset($this->as_ion_auth) || !$this->as_ion_auth->logged_in()) && $this->session->userdata('herd_code') != $this->config->item('default_herd')){
			$msg = $this->load->view('session_expired', array('url'=>$this->session->flashdata('redirect_url')), true);
			$this->load->view('echo.php', ['text' => $msg]);
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

    function ajax_cow() {
    	$this->load->model('dhi/genetics/cow_qtile_model');
    	$arr_avg = $this->cow_qtile_model->getCowAverages(
				$this->session->userdata('herd_code'),
    			$this->session->userdata('pstring'),
    			$this->session->userdata('recent_test_date')
    	);
		header("Cache-Control: no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
    	$this->load->view('dhi/genetics/quartiles', $arr_avg);
    }

    function ajax_heifer() {
    	$this->load->model('dhi/genetics/heifer_qtile_model');
    	$arr_avg = $this->heifer_qtile_model->getHeiferAverages(
    			$this->session->userdata('herd_code'),
    			$this->session->userdata('pstring'),
    			$this->session->userdata('recent_test_date')
    	);
		header("Cache-Control: no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
    	$this->load->view('dhi/genetics/quartiles', $arr_avg);
    }
    
    function ajax_progeny() {
    	$this->load->model('dhi/genetics/progeny_qtile_model');
    	$arr_avg = $this->progeny_qtile_model->getProgenyAverages(
				$this->session->userdata('herd_code'),
    			$this->session->userdata('recent_test_date')
    	);
		header("Cache-Control: no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
    	$this->load->view('dhi/genetics/calves_due', array('arr_avg'=>$arr_avg));
    }
    
}