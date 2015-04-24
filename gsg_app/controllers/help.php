<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
require_once(APPPATH . 'libraries' . FS_SEP . 'dhi' . FS_SEP . 'HerdAccess.php');

use myagsource\dhi\HerdAccess;

class Help extends CI_Controller{
	protected $section_id;
	protected $page_header_data;

	function __construct(){
		parent::__construct();
		$this->load->model('herd_model');
		$herd_access = new HerdAccess($this->herd_model);
		if(!isset($this->as_ion_auth)){
			redirect('auth/login', 'refresh');
		}
		$this->page_header_data['user_sections'] = $this->as_ion_auth->top_sections;
		$this->page_header_data['num_herds'] = $herd_access->getNumAccessibleHerds($this->session->userdata('user_id'), $this->as_ion_auth->arr_task_permissions(), $this->session->userdata('arr_regions'));
		/* Load the profile.php config file if it exists
		if (ENVIRONMENT == 'development' || ENVIRONMENT == 'localhost') {
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
		//$this->super_section_id = $this->web_content_model->get_super_section_id_by_path($this->router->fetch_directory());
		//$this->top_sections = $this->as_ion_auth->get_super_sections_array($this->session->userdata('active_group_id'), $this->session->userdata('user_id'), $this->session->userdata('herd_code'), $arr_scope);
		//$this->user_sections = $this->as_ion_auth->get_sections_array($this->session->userdata('active_group_id'), $this->session->userdata('user_id'), $this->session->userdata('herd_code'), array($this->super_section_id), $arr_scope);
		
		if(is_array($this->page_header_data)){
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