<?php
//namespace myagsource;

require_once(APPPATH . 'libraries/dhi/Herd.php');
require_once(APPPATH.'libraries/AccessLog.php');
require_once(APPPATH . 'libraries/Benchmarks/Benchmarks.php');
require_once(APPPATH.'libraries/dhi/HerdAccess.php');
require_once APPPATH . 'libraries/Settings/SessionSettings.php';

use \myagsource\AccessLog;
use \myagsource\dhi\Herd;
use \myagsource\Benchmarks\Benchmarks;
use \myagsource\dhi\HerdAccess;
use myagsource\Settings\SessionSettings;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Change_herd extends CI_Controller {
	/* 
	 * @var Herd object
	 */
	protected $herd;
	/* 
	 * @var HerdAccess object
	 */
	protected $herd_access;
	/* 
	 * @var AccessLog object
	 */
	protected $access_log;

	function __construct(){
		parent::__construct();
		$this->load->model('herd_model');
		$this->herd_access = new HerdAccess($this->herd_model);
		$this->session->keep_flashdata('redirect_url');
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

		$this->load->model('access_log_model');
		$this->access_log = new AccessLog($this->access_log_model);
				
		$this->page_header_data['num_herds'] = $this->herd_access->getNumAccessibleHerds($this->session->userdata('user_id'), $this->as_ion_auth->arr_task_permissions(), $this->session->userdata('arr_regions'));
		$this->page_header_data['navigation'] = $this->load->view('navigation', [], TRUE);
		/* Load the profile.php config file if it exists */
		if ((ENVIRONMENT == 'development' || ENVIRONMENT == 'localhost') && strpos($this->router->method, 'ajax') === false) {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			} 
		}
	}

	function index(){
		if($this->as_ion_auth->has_permission("Select Herd")) {
			$this->session->keep_flashdata('redirect_url');
			$this->session->keep_flashdata('message');
			redirect(site_url('dhi/change_herd/select'));
		}
		elseif($this->as_ion_auth->has_permission("Request Herd")) {
			$this->session->keep_flashdata('redirect_url');
			$this->session->keep_flashdata('message');
			redirect(site_url('dhi/change_herd/request'));
		}
		else {
			$this->session->set_flashdata('message', 'You do not have permissions to request herds.');
			redirect(site_url($redirect_url));
		}
	}

/**
 * @method select() - option list and input field to select a herd (text field auto-selects options list value).
 * 			sets session herd code on successfull submissions.
 *
 * @access	public
 * @return	void
 */
	function select(){
		$tmp_uri = $this->uri->uri_string();
		$redirect_url = set_redirect_url($tmp_uri, $this->session->flashdata('redirect_url'), $this->as_ion_auth->referrer);
		$this->session->set_flashdata('redirect_url', $redirect_url);
		if(empty($redirect_url) && $this->as_ion_auth->referrer !== $tmp_uri) $redirect_url = $tmp_uri;
		if(!$this->as_ion_auth->has_permission("Select Herd") && $this->as_ion_auth->has_permission("Request Herd")){
			redirect(site_url('dhi/change_herd/request'));
			exit();
		}
		if(!$this->as_ion_auth->has_permission("Select Herd")){
			$this->session->set_flashdata('message', 'You do not have permissions to select herds.');
			redirect(site_url($redirect_url));
			exit();
		}
		//validate form input
		$this->load->library('form_validation');
		$this->form_validation->set_rules('herd_code', 'Herd', 'required|max_length[8]');
		//$this->form_validation->set_rules('herd_code_fill', 'Type Herd Code');

		if ($this->form_validation->run() == TRUE) { //successful submission
			$this->herd = new Herd($this->herd_model, $this->input->post('herd_code'));
			$herd_enroll_status_id = $this->herd->getHerdEnrollStatus($this->config->item('product_report_code'));
			if($this->session->userdata('active_group_id') == 2){ //user is a producer
				if($herd_enroll_status_id == 1){ //herd is signed up at all
					$this->session->set_flashdata('message', 'Herd ' . $this->input->post('herd_code') . ' is not signed up for ' . $this->config->item('product_name') . '. Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' to enroll on ' . $this->config->item('product_name') . '.');
					//redirect back to select herd page again
					redirect(site_url('dhi/change_herd/select'), 'refresh');
					exit();
				}
				if($herd_enroll_status_id == 2){ //herd is not paying
					$trial_days = $this->herd->getTrialDays($this->access_log, $this->session->userdata('user_id'), $this->input->post('herd_code'), $this->config->item('product_report_code'));
					if($trial_days >= $this->config->item('trial_length')){
						$this->session->set_flashdata('message', 'The trial period for herd ' . $this->input->post('herd_code') . ' has expired. Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' to enroll on ' . $this->config->item('product_name') . '.');
						//redirect back to select herd page again
						redirect(site_url('dhi/change_herd/select'), 'refresh');
						exit();
					}
					elseif($trial_days >= $this->config->item('trial_warning')){
						$this->session->set_flashdata('message', 'You have ' . ($this->config->item('trial_length') - $trial_days) . ' days remaining on your free trial.  To ensure uninterrupted access, please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' to enroll on ' . $this->config->item('product_name') . '.');
					}
				}
			}
			$this->set_herd_session_data($herd_enroll_status_id);
			
			$this->_record_access(2); //2 is the page code for herd change
			redirect(site_url($redirect_url));
			exit();
		}
		else
		{
			$err = '';
			//$tmp_arr = $this->as_ion_auth->get_viewable_herds($this->session->userdata('user_id'), $this->session->userdata('arr_regions'));
			$tmp_arr = $this->herd_access->getAccessibleHerdsData($this->session->userdata('user_id'), $this->as_ion_auth->arr_task_permissions(), $this->session->userdata('arr_regions'));
			if(is_array($tmp_arr) && !empty($tmp_arr)){
				if(count($tmp_arr) == 1){
					$this->herd = new Herd($this->herd_model, $tmp_arr[0]['herd_code']);
					$herd_enroll_status_id = $this->herd->getHerdEnrollStatus($this->config->item('product_report_code'));
					if($this->session->userdata('active_group_id') == 2){ //user is a producer
						if($herd_enroll_status_id == 1){ //herd is not enrolled
							//logout user
							$this->as_ion_auth->logout();
							$this->session->set_flashdata('message', 'Herd ' . $this->input->post('herd_code') . ' is not signed up for ' . $this->config->item('product_name') . '. Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' to enroll on ' . $this->config->item('product_name') . '.');
							//redirect to login
							redirect('auth/login', 'refresh');
							exit();
						}
						if($herd_enroll_status_id == 2){ //herd is not paying
							$trial_days = $this->herd->getTrialDays($this->access_log, $this->session->userdata('user_id'), $tmp_arr[0]['herd_code'], $this->config->item('product_report_code'));
							if($trial_days >= $this->config->item('trial_length')){
								//logout user
								$this->as_ion_auth->logout();
								$this->session->set_flashdata('message', 'Your free trial period has expired. Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' to enroll on ' . $this->config->item('product_name') . '.');
								//redirect to login
								redirect('auth/login', 'refresh');
								exit();
							}
							elseif($trial_days >= $this->config->item('trial_warning')){
								$this->session->set_flashdata('message', 'You have ' . ($this->config->item('trial_length') - $trial_days) . ' days remaining on your free trial.  To ensure uninterrupted access, please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' to enroll on ' . $this->config->item('product_name') . '.');
							}
						}
					}
					$this->set_herd_session_data($herd_enroll_status_id);
					redirect(site_url($redirect_url));
					exit();
				}
				$this->load->library('herds');
				$this->data['arr_herd_data'] = $this->herds->set_herd_dropdown_array($tmp_arr);
				unset($tmp_arr);
			}
			else{
				$err = 'A list of herds could not be generated for your account.  If you believe this is an error, please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . '.';
			}


			$this->data['herd_code_fill'] = array('name' => 'herd_code_fill',
				'id' => 'herd_code_fill',
				'type' => 'text',
				'size' => '8',
				'maxlength' => '8',
				'value' => $this->form_validation->set_value('herd_code_fill'),
			);


			if(is_array($this->page_header_data)){
				$this->page_header_data = array_merge($this->page_header_data,
					array(
						'title'=>'Select Herd - ' . $this->config->item('product_name'),
						'description'=>'Herd Selection Form for ' . $this->config->item('product_name'),
						'arr_headjs_line'=>array(
							'{datatable: "' . $this->config->item("base_url_assets") . 'js/dhi/herd_selection_helper.js"}'
						)
					)
				);
			}
			$this->page_footer_data = array();
			$this->page_header_data['message'] = compose_error($err, validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages());
			$arr_redirect_url = explode('/', $redirect_url);
			$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
			$this->data['page_heading'] = 'Select Herd - ' . $this->config->item('product_name');
			$this->data['page_footer'] = $this->load->view('page_footer', $this->page_footer_data, TRUE);

			$this->load->view('dhi/herd_selection', $this->data);
		} // end ELSE -- form validation failed.
	}
	
/**
 * @method request() - input field to select a herd.
 * 			sets session herd code on successfull submissions.
 * 			Currently used only by Genex .
 *
 * @access	public
 * @return	void
 */
	function request(){
		$tmp_uri = $this->uri->uri_string();
		$redirect_url = set_redirect_url($tmp_uri, $this->session->flashdata('redirect_url'), $this->as_ion_auth->referrer);
		$this->session->set_flashdata('redirect_url', $redirect_url);
		if(empty($redirect_url) && $this->as_ion_auth->referrer !== $tmp_uri) $redirect_url = $tmp_uri;
		if($this->as_ion_auth->has_permission("Select Herd") && !$this->as_ion_auth->has_permission("Request Herd")){
			redirect(site_url('dhi/change_herd/select'));
		}
		if(!$this->as_ion_auth->has_permission("Request Herd")){
			$this->session->set_flashdata('message', 'You do not have permissions to request herds.');
			redirect(site_url($redirect_url));
		}

		//validate form input
		$this->load->library('form_validation');
		$this->form_validation->set_rules('herd_code', 'Herd', 'required|exact_length[8]');
		$this->form_validation->set_rules('herd_release_code', 'Herd Release Code', 'required|exact_length[10]');

		$herd_code = $this->input->post('herd_code');
		if(!empty($herd_code)){//if form is submitted
			$herd_release_code = $this->input->post('herd_release_code');
			$error = $this->herd_model->herd_authorization_error($herd_code, $herd_release_code);
			if($error){
				$this->session->set_flashdata('message', 'Invalid data submitted: ' . $error);
				redirect(site_url('dhi/change_herd/request'));
			}
		}

		if ($this->form_validation->run() == TRUE) { //if validation is successful
			$this->herd = new Herd($this->herd_model, $this->input->post('herd_code'));
			$herd_enroll_status_id = $this->herd->getHerdEnrollStatus($this->config->item('product_report_code'));
			if($this->session->userdata('active_group_id') == 2){ //user is a producer
				if($herd_enroll_status_id == 1){ //herd is not enrolled
					$this->session->set_flashdata('message', 'Herd ' . $this->input->post('herd_code') . ' is not signed up for ' . $this->config->item('product_name') . '. Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' to enroll on ' . $this->config->item('product_name') . '.');
					//redirect back to select herd page again
					redirect(site_url('dhi/change_herd/request'), 'refresh');
					exit();
				}
				if($herd_enroll_status_id == 2){ //herd is not paying
					$trial_days = $this->herd->getTrialDays($this->access_log, $this->session->userdata('user_id'), $this->input->post('herd_code'), $this->config->item('product_report_code'));
					if($trial_days >= $this->config->item('trial_length')){
						$this->session->set_flashdata('message', 'The trial period for herd ' . $this->input->post('herd_code') . ' has expired. Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' to enroll on ' . $this->config->item('product_name') . '.');
						//redirect back to select herd page again
						redirect(site_url('dhi/change_herd/request'), 'refresh');
						exit();
					}
					elseif($trial_days >= $this->config->item('trial_warning')){
						$this->session->set_flashdata('message', 'You have ' . ($this->config->item('trial_length') - $trial_days) . ' days remaining on your free trial.  To ensure uninterrupted access, please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' to enroll on ' . $this->config->item('product_name') . '.');
					}
				}
			}
			$this->set_herd_session_data($herd_enroll_status_id);
			
			$this->_record_access(2); //2 is the page code for herd change
			redirect(site_url($redirect_url));
		}
		else {  //the user is not logging in so display the login page
			$this->page_header_data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'));
			$this->data['herd_code'] = array('name' => 'herd_code',
				'id' => 'herd_code',
				'type' => 'text',
				'value' => $this->form_validation->set_value('herd_code'),
				'class' => 'require'
			);
			$this->data['herd_release_code'] = array('name' => 'herd_release_code',
				'id' => 'herd_release_code',
				'type' => 'text',
				'value' => $this->form_validation->set_value('herd_release_code'),
				'class' => 'require'
			);
			$this->data['report_path'] = '';
			if(is_array($this->page_header_data)){
				$this->page_header_data = array_merge($this->page_header_data,
					array(
						'title'=>'Request Herd - ' . $this->config->item('product_name'),
						'description'=>'Herd Selection Form for ' . $this->config->item('product_name')
					)
				);
			}
			$arr_redirect_url = explode('/', $redirect_url);

			$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
			$this->data['page_heading'] = 'Request Herd - ' . $this->config->item('product_name');
			$this->data['page_footer'] = $this->load->view('page_footer', NULL, TRUE);

			$this->load->view('dhi/herd_request', $this->data);
		}
	}

	public function ajax_herd_enrolled($herd_code){
		header('Content-type: application/json');
		$group = $this->session->userdata('active_group_id');
		if($this->as_ion_auth->has_permission('View Assign w permission') === FALSE) {
			//return a 0 for non-service groups
			$this->load->view('echo.php', ['text' => json_encode(['enroll_status' => 0, 'new_test' => false])]);
		}
		$this->herd = new Herd($this->herd_model, $herd_code);
		$enroll_status = $this->herd->getHerdEnrollStatus($this->config->item('product_report_code'));
		$recent_test = $this->herd->getRecentTest();
		$has_accessed = $this->access_log->sgHasAccessedTest($this->session->userdata('sg_acct_num'), $herd_code, $recent_test);
		header('Content-type: application/json');
		header("Cache-Control: no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		$this->load->view('echo.php', ['text' => json_encode(['enroll_status' => $enroll_status, 'new_test' => !$has_accessed])]);
	}
	
	protected function set_herd_session_data($herd_enroll_status_id){
		$this->session->set_userdata('herd_code', $this->herd->herdCode());
//		$this->session->set_userdata('pstring', 0);
//		$this->session->set_userdata('arr_pstring', $this->herd_model->get_pstring_array($this->herd->herdCode()));
//		$arr_breeds = $this->herd_model->get_breed_array($this->herd->herdCode());
//		$this->session->set_userdata('arr_breeds', $arr_breeds);
//		$this->session->set_userdata('breed_code', $arr_breeds[0]['breed_code']);
		$this->session->set_userdata('herd_enroll_status_id', $herd_enroll_status_id);
		$this->session->set_userdata('recent_test_date', $this->herd->getRecentTest());
		//load new benchmarks
		$this->load->model('setting_model');
		$this->load->model('benchmark_model');
		$benchmarks = new Benchmarks($this->session->userdata('user_id'), $this->input->post('herd_code'), $this->herd->header_info($this->input->post('herd_code')), $this->setting_model, $this->benchmark_model, []);
		$this->session->set_userdata('benchmarks', $benchmarks->getSettingKeyValues());

		$general_dhi = new SessionSettings($this->session->userdata('user_id'), $this->input->post('herd_code'), $this->setting_model, 'general_dhi', []);
		$this->session->set_userdata('general_dhi', $general_dhi->getSettingKeyValues());
	}

	protected function _record_access($event_id){
		if($this->session->userdata('user_id') === FALSE){
			return FALSE;
		}
		$herd_code = $this->session->userdata('herd_code');
		$herd_enroll_status_id = empty($herd_code) ? NULL : $this->session->userdata('herd_enroll_status_id');
		$recent_test = $this->session->userdata('recent_test_date');
		$recent_test = empty($recent_test) ? NULL : $recent_test;
		
		$this->access_log->write_entry(
			$this->as_ion_auth->is_admin(),
			$event_id,
			$herd_code,
			$recent_test,
			$herd_enroll_status_id,
			$this->session->userdata('user_id'),
			$this->session->userdata('active_group_id')
		);
	}
}
