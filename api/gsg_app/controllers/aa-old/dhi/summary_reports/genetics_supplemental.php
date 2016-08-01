<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Genetics_supplemental extends MY_Controller {
	
	function __construct(){
		parent::__construct();
		$this->session->keep_all_flashdata();
		
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
		header("Expires: -1");
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
		header("Expires: -1");
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
		header("Expires: -1");
		$this->load->view('dhi/genetics/calves_due', array('arr_avg'=>$arr_avg));
    }
}