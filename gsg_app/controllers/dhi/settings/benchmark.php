<?php
/*
 * Form-processing controller.  Does not load a new page, only gives JSON response to the form submission
 */
require_once(APPPATH . 'libraries/Benchmarks/Benchmarks.php');

use \myagsource\Benchmarks\Benchmarks;


if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Benchmark extends CI_Controller {	
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
	function ajax_set($ser_form_data = null){
		//do we have any data?
		if(!isset($ser_form_data)){
			http_response_code(400);
			echo json_encode(['error'=>['message'=>'No form data received.']]);
			exit;
		}
		
		//HANDLE DATA
		$arr_params = json_decode(urldecode($ser_form_data), true);
		//verify csrf
		if(isset($arr_params['csrf_test_name']) && $arr_params['csrf_test_name'] != $this->security->get_csrf_hash()){
			http_response_code(403);
			echo json_encode(['error'=>['message'=>"I don't recognize your browser session, your session may have expired, or you may have cookies turned off."]]);
			exit;
		}
		unset($arr_params['csrf_test_name']);

		$this->load->model('setting_model');
		$make_default = $arr_params['make_default'];
		unset($arr_params['make_default']);
		
		$formatted_form_data = Benchmarks::parseFormData($arr_params);

		//set session benchmarks
		//$benchmarks->setSessionValues($formatted_form_data);
		$this->session->set_userdata('benchmarks', $formatted_form_data);
		
		//if set default, write to database
		if($make_default){
			$this->load->model('benchmark_model');
			$benchmarks = new Benchmarks($this->session->userdata('user_id'), $this->session->userdata('herd_code'), $this->herd_model->header_info($this->session->userdata('herd_code')), $this->setting_model, $this->benchmark_model, $this->session->userdata('benchmarks'));
			$benchmarks->save_as_default($formatted_form_data);
		}
			
		exit;
	}
}
