<?php
/*
 * Form-processing controller.  Does not load a new page, only gives JSON response to the form submission
 */
require_once APPPATH . 'libraries/Settings/SessionSettings.php';

use myagsource\Settings\SessionSettings;


if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class General extends MY_Controller {	
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
		//form validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('cow_id_field', 'Cow ID Field', 'trim|alpha_dash');
		
		if($this->form_validation->run()){
			$fields = [
				'cow_id_field' => $this->input->post('cow_id_field'),
			];
			
			$make_default = $this->input->post('make_default');
			
			$formatted_form_data = SessionSettings::parseFormData($fields);
	
				//set session benchmarks
			$this->session->set_userdata('general_dhi', $formatted_form_data);
			
			//if set default, write to database
			if($make_default){
				$this->load->model('setting_model', null, false, ['user_id'=>$this->session->userdata('user_id'), 'herd_code'=>$this->session->userdata('herd_code')]);
				$settings = new SessionSettings($this->session->userdata('user_id'), $this->session->userdata('herd_code'), $this->setting_model, 'general_dhi_settings', $this->session->userdata('general_dhi'));
				$settings->save_as_default($formatted_form_data);
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
