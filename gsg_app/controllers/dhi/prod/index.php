<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Index extends CI_Controller {
	function __construct(){
		parent::__construct();
	}
	
	function index(){
		$redirect_url = set_redirect_url($this->uri->uri_string(), $this->session->flashdata('redirect_url'), $this->as_ion_auth->referrer);
		$this->session->set_flashdata('redirect_url', $redirect_url);
		redirect(site_url('dhi/prod/test_day'));
	}
	
	function land(){
		$redirect_url = set_redirect_url($this->uri->uri_string(), $this->session->flashdata('redirect_url'), $this->as_ion_auth->referrer);
		$this->session->set_flashdata('redirect_url', $redirect_url);
		redirect(site_url('dhi/prod/test_day'));
	}
}