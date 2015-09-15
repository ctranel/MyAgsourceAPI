<?php defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH . 'libraries' . FS_SEP . 'dhi' . FS_SEP . 'HerdAccess.php');

use myagsource\dhi\HerdAccess;

class HTTP_Error extends CI_Controller {

	function __construct() {
		parent::__construct();
	}
	
	function index() {
		$this->load->model('herd_model');
		$herd_access = new HerdAccess($this->herd_model);
		
		$this->page_header_data = [
			'title'=>'Page Not Found',
			'num_herds'=>$herd_access->getNumAccessibleHerds($this->session->userdata('user_id'), $this->as_ion_auth->arr_task_permissions(), $this->session->userdata('arr_regions')),
			'navigation' => $this->load->view('navigation', [], TRUE),
		];
		
		$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
		$this->data['page_footer'] = $this->load->view('page_footer', NULL, TRUE);
		
		
		$this->load->view('http_error', $this->data);
	}
	
}