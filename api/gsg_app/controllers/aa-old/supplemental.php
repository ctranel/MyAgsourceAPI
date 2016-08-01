<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Supplemental extends MY_Controller {
	
	function __construct(){
		parent::__construct();
		$this->session->keep_all_flashdata();

		if((!isset($this->as_ion_auth) || !$this->as_ion_auth->logged_in()) && $this->session->userdata('herd_code') != $this->config->item('default_herd')){
			$this->load->view('session_expired', array('url'=>$this->session->flashdata('redirect_url')));
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
		echo 'Direct access to this page is not allowed.';
    }

    function ajax_tip($comment_id) {
    	$this->load->model('supplemental_model');
    	$tip = $this->supplemental_model->getComment($comment_id);
		$this->load->view('tip', ['tip' => $tip]);
    }
    
    function ajax_overlay() {
    }
}