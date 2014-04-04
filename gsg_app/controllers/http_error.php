<?php defined('BASEPATH') OR exit('No direct script access allowed');

class HTTP_Error extends CI_Controller {

	function __construct() {
		parent::__construct();
	}
	
	function index() {
		
		$this->page_header_data = array('title'=>'Page Not Found');
		
		$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
		$this->data['page_footer'] = $this->load->view('page_footer', NULL, TRUE);
		
		
		$this->load->view('http_error', $this->data);
	}
	
}