<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Dm extends CI_Controller {
// ONLY NEEDED WHILE LINKING TO EXTERNAL AGSOURCE DM	
	function __construct(){
		parent::__construct();
		if(!isset($this->as_ion_auth)) {
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
			redirect('auth/login', 'refresh');
		}
				
		if((!$this->as_ion_auth->logged_in())){ //redirect when live
					if(strpos($this->session->flashdata('message'), 'Please log in.') === FALSE){
				$this->session->set_flashdata('message',  $this->session->flashdata('message') . 'Please log in.');
			}
			else{
				$this->session->keep_flashdata('message');
			}
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
       		redirect(site_url('auth/login'));
		}

//		$this->as_ion_auth->is_admin = $this->as_ion_auth->is_admin();
//		$this->as_ion_auth->is_manager = $this->as_ion_auth->is_manager();

		$this->load->model('dm_model');
		
		$this->herd_code = strlen($this->session->userdata('herd_code')) == 8?$this->session->userdata('herd_code'):NULL;
	}
	
	function index(){
		redirect(site_url());
	}
	
	function log_page(){
		echo $this->access_log_model->write_entry(19); //19 is the page code for DM Login
		exit;
	}
}