<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
class Help extends parent_report{

	function index(){
		$this->support();
	}

	function support() {
		$this->product_name = 'Support';
		$this->generatePageHeader();
	}

	function faq() {
		$this->product_name = 'FAQ';
		$this->generatePageHeader();
	}
	
	function about() {
		$this->product_name = 'About';
		$this->generatePageHeader();
	}
	
	function generatePageHeader() {
		$this->load->helper('multid_array_helper');
		$arr_scope = array('subscription','public','unmanaged');
		$this->super_section_id = $this->ion_auth_model->get_super_section_id_by_path($this->uri->segment(1));
		$this->arr_user_super_sections = $this->as_ion_auth->get_super_sections_array($this->session->userdata('active_group_id'), $this->session->userdata('user_id'), $this->session->userdata('herd_code'), $arr_scope);
		$this->arr_user_sections = $this->as_ion_auth->get_sections_array($this->session->userdata('active_group_id'), $this->session->userdata('user_id'), $this->session->userdata('herd_code'), array($this->super_section_id), $arr_scope);
		
		if(is_array($this->page_header_data)){
			$arr_sec_nav_data = array(
					'arr_pages' => $this->arr_user_sections,
					'section_id' => $this->section_id
			);
			$this->page_header_data = array_merge($this->page_header_data,
					array(
							'title'=>$this->product_name . ' - ' . $this->config->item('site_title'),
							'description'=>$this->product_name . ' - ' . $this->config->item('site_title'),
							'messages' => $this->{$this->primary_model}->arr_messages,
							'section_nav' => $this->load->view('section_nav', $arr_sec_nav_data, TRUE)
			)
			);
			$data = array(
					'page_header' => $this->load->view('page_header', $this->page_header_data, TRUE),
					'product_name' => $this->product_name
			);
		
		}
		$this->load->view('help', $data);
		
	}
}