<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Land extends CI_Controller {
	function __construct(){
		parent::__construct();
	}
	
	function index(){
		redirect(site_url('custom_report/custom_report/create'));
	}
	
	function log_page(){
		echo $this->access_log_model->write_entry(19); //19 is the page code for DM Login
		exit;
	}
}