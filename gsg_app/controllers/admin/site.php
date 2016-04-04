<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Site extends MY_Controller {
	function __construct(){
		parent::__construct();
		$redirect_url = set_redirect_url($this->uri->uri_string(), $this->session->flashdata('redirect_url'));
		$this->session->set_userdata('redirect_url', $redirect_url);
	}
	
	function index(){
		$this->session->keep_all_flashdata();
		redirect(site_url('site/usage'));
	}

	function usage($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'Usage Statistics';
	 	parent::display($block_in, $display_format);
	}
	
	function info(){
		phpinfo();
	}
}