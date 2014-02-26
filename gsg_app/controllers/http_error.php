<?php defined('BASEPATH') OR exit('No direct script access allowed');

class HTTP_Error extends CI_Controller {

	function __construct() {
		parent::__construct();
	}
	
	function index() {
		$this->load->view('http_error');
	}
	
}