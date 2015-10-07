<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Index extends CI_Controller {
	function __construct(){
		parent::__construct();
	}
	
	function index(){
		$this->session->keep_all_flashdata();
		redirect(site_url('dhi/summary_reports/herd_summary/hs_prod'));
	}
}