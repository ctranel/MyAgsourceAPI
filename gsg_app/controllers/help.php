<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
class Help extends CI_Controller{
	protected $section_id;
	protected $page_header_data;

	function __construct(){
		parent::__construct();
			if(!isset($this->as_ion_auth)){
			redirect('auth/login', 'refresh');
		}
		if((!$this->as_ion_auth->logged_in())){
			$redirect_url = set_redirect_url($this->uri->uri_string(), $this->session->flashdata('redirect_url'), $this->as_ion_auth->referrer);
			$this->session->set_flashdata('redirect_url', $redirect_url);
			if(strpos($this->session->flashdata('message'), 'Please log in.') === FALSE){
				$this->session->set_flashdata('message',  $this->session->flashdata('message') . 'Please log in.');
			}
			else{
				$this->session->keep_flashdata('message');
			}
			redirect(site_url('auth/login'));
		}
		$this->page_header_data['user_sections'] = $this->as_ion_auth->arr_user_super_sections;
		$this->page_header_data['num_herds'] = $this->as_ion_auth->get_num_viewable_herds($this->session->userdata('user_id'), $this->session->userdata('arr_regions'));
		/* Load the profile.php config file if it exists
		if (ENVIRONMENT == 'development') {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			} 
		} */
	}
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
		$this->super_section_id = $this->web_content_model->get_super_section_id_by_path($this->router->fetch_directory());
		$this->arr_user_super_sections = $this->as_ion_auth->get_super_sections_array($this->session->userdata('active_group_id'), $this->session->userdata('user_id'), $this->session->userdata('herd_code'), $arr_scope);
		$this->arr_user_sections = $this->as_ion_auth->get_sections_array($this->session->userdata('active_group_id'), $this->session->userdata('user_id'), $this->session->userdata('herd_code'), array($this->super_section_id), $arr_scope);
		
		if(is_array($this->page_header_data)){
/*			$arr_sec_nav_data = array(
					'arr_pages' => $this->arr_user_sections,
					'section_id' => $this->section_id
			);*/
			$this->page_header_data = array_merge($this->page_header_data,
					array(
							'title'=>$this->product_name . ' - ' . $this->config->item('site_title'),
							'description'=>$this->product_name . ' - ' . $this->config->item('site_title'),
							'messages' => compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors()),
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