<?php defined('BASEPATH') OR exit('No direct script access allowed');

class HTTP_Error extends CI_Controller {

	function __construct() {
		parent::__construct();
	}
	
	function index() {
		
		$this->page_header_data['user_sections'] = $this->as_ion_auth->arr_user_super_sections;
		$this->page_header_data['num_herds'] = $this->as_ion_auth->get_num_viewable_herds($this->session->userdata('user_id'), $this->session->userdata('arr_regions'));
		$this->page_header_data = array(
			'title'=>'Page Not Found',
			'user_sections'=>$this->as_ion_auth->arr_user_super_sections,
			'num_herds'=>$this->as_ion_auth->get_num_viewable_herds($this->session->userdata('user_id'), $this->session->userdata('arr_regions')),
		);
		
		$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
		$this->data['page_footer'] = $this->load->view('page_footer', NULL, TRUE);
		
		
		$this->load->view('http_error', $this->data);
	}
	
}