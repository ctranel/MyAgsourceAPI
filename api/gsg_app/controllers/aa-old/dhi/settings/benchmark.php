<?php
/*
 * Form-processing controller.  Does not load a new page, only gives JSON response to the form submission
 */
require_once(APPPATH . 'libraries/Benchmarks/Benchmarks.php');

use \myagsource\Benchmarks\Benchmarks;


if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Benchmark extends MY_Controller {	
	function __construct(){
		parent::__construct();
		
		$this->session->keep_all_flashdata();
		
		if((!isset($this->as_ion_auth) || !$this->as_ion_auth->logged_in()) && $this->session->userdata('herd_code') != $this->config->item('default_herd')){
			http_response_code(401);
			echo json_encode(['error'=>['message'=>'Your session has expired, please log in and try again.']]);
			exit;
		}
	}

/**
 * @method index()
 * 
 * @description updates benchmark session data and optionally modifies benchmark defaults
 * 
 * @param string serialized form parameters 
 * @access	public
 * @return	void
 * @todo: sending confirmation to client???
 */
	function ajax_set(){
		//form validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('breed', 'Breed', 'trim|alpha_dash');
		$this->form_validation->set_rules('metric', 'Metric', 'trim|alpha_dash');
		$this->form_validation->set_rules('criteria', 'Criteria', 'trim|alpha_dash');
		$this->form_validation->set_rules("herd_size['dbfrom']", 'Herd Size Start', 'trim|integer');
		$this->form_validation->set_rules("herd_size['dbto']", 'Herd Size End', 'trim|integer');
		$this->form_validation->set_rules('make_default', 'Save as Default', 'trim');
		
		if($this->form_validation->run()){
			$fields = [
				'breed' => $this->input->post('breed'),
				'metric' => $this->input->post('metric'),
				'criteria' => $this->input->post('criteria'),
				'herd_size' => $this->input->post('herd_size'),
			];
			
			$make_default = $this->input->post('make_default');
			
			$formatted_form_data = Benchmarks::parseFormData($fields);
	
			//set session benchmarks
			$this->session->set_userdata('benchmarks', $formatted_form_data);
			
			//if set default, write to database
			if($make_default){
				$this->load->model('setting_model', null, false, ['user_id'=>$this->session->userdata('user_id'), 'herd_code'=>$this->session->userdata('herd_code')]);
				$this->load->model('benchmark_model');
				$benchmarks = new Benchmarks($this->session->userdata('user_id'), $this->session->userdata('herd_code'), $this->herd_model->header_info($this->session->userdata('herd_code')), $this->setting_model, $this->benchmark_model, $this->session->userdata('benchmarks'));
				$benchmarks->save_as_default($formatted_form_data);
			}
			exit;
		}
		else {
			http_response_code(400);
			echo json_encode(['error'=>['message'=>validation_errors()]]);
			exit;
		}
	}
}
