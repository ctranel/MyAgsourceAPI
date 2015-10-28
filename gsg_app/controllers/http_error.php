<?php defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH . 'libraries/dhi/HerdAccess.php');

use myagsource\dhi\HerdAccess;

class HTTP_Error extends CI_Controller {

	function __construct() {
		parent::__construct();
	}
	
	function index($failed_url = null) {
		$failed_url = urldecode($failed_url);
		$this->load->model('herd_model');
		$herd_access = new HerdAccess($this->herd_model);
		
		$this->page_header_data = [
			'title'=>'Page Not Found',
			'num_herds'=>$herd_access->getNumAccessibleHerds($this->session->userdata('user_id'), $this->as_ion_auth->arr_task_permissions(), $this->session->userdata('arr_regions')),
			'navigation' => $this->load->view('navigation', [], TRUE),
		];
		
		$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
		$this->data['page_footer'] = $this->load->view('page_footer', NULL, TRUE);
		$this->data['failed_url'] = $failed_url ? $failed_url : $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		
		$this->load->view('http_error', $this->data);
	}
	
}