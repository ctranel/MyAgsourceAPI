<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Land extends CI_Controller {
	function __construct(){
		parent::__construct();
	}
	
	function index(){
		redirect(site_url('dhi/prod/test_day'));
	}
	
	function land(){
		redirect(site_url('dhi/prod/test_day'));
	}
}