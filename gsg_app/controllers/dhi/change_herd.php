<?php
//namespace myagsource;

require_once(APPPATH . 'libraries/dhi/Herd.php');
require_once(APPPATH.'libraries/AccessLog.php');
require_once(APPPATH . 'libraries/Benchmarks/Benchmarks.php');
require_once(APPPATH.'libraries/dhi/HerdAccess.php');
require_once APPPATH . 'libraries/Settings/SessionSettings.php';
require_once(APPPATH . 'libraries/Notifications/Notifications.php');

use \myagsource\AccessLog;
use \myagsource\dhi\Herd;
use \myagsource\Benchmarks\Benchmarks;
use \myagsource\dhi\HerdAccess;
use myagsource\Settings\SessionSettings;
use \myagsource\notices\Notifications;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Change_herd extends MY_Controller {
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

	protected $notifications;
	protected $notices;

	function __construct(){
		parent::__construct();

		$this->load->model('herd_model');
		$this->herd_access = new HerdAccess($this->herd_model);
		if(!isset($this->as_ion_auth)){
			$this->session->keep_all_flashdata();
			$this->redirect('auth/login', 'Please log in');
		}
		if((!$this->as_ion_auth->logged_in())){
			$this->session->keep_all_flashdata();
			$msg = '';
			if(array_search('Please log in.', $this->session->flashdata('message')) === FALSE){
				$msg = 'Please log in.';
			}
			$this->redirect(site_url('auth/login'), $msg);
		}
		$this->load->model('access_log_model');
		$this->access_log = new AccessLog($this->access_log_model);
		$this->load->model('notice_model');

		$this->page_header_data['num_herds'] = $this->herd_access->getNumAccessibleHerds($this->session->userdata('user_id'), $this->permissions->permissionsList(), $this->session->userdata('arr_regions'));
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
		if($this->permissions->hasPermission("Select Herd")) {
			$this->session->keep_all_flashdata();
			$this->redirect(site_url('dhi/change_herd/select'));
		}
		elseif($this->permissions->hasPermission("Request Herd")) {
			$this->session->keep_all_flashdata();
			$this->redirect(site_url('dhi/change_herd/request'));
		}
		else {
			$this->session->keep_all_flashdata();
			$this->redirect(site_url($this->session->userdata('redirect_url')), ['You do not have permissions to request herds.']);
		}
	}

	/**
	 * @method select() - option list and input field to select a herd (text field auto-selects options list value).
	 * 			sets session herd code on successful submissions.
	 *
	 * @access	public
	 * @return	void
	 */
	function select(){
		if(!$this->permissions->hasPermission("Select Herd") && $this->permissions->hasPermission("Request Herd")){
			$this->session->keep_all_flashdata();
			$this->redirect(site_url('dhi/change_herd/request'));
			exit();
		}

		if(!$this->permissions->hasPermission("Select Herd")){
			$this->session->keep_all_flashdata();
			$this->redirect(site_url($this->session->userdata('redirect_url')), ['You do not have permissions to select herds.']);
			exit();
		}
		//validate form input
		$this->load->library('form_validation');
		$this->form_validation->set_rules('herd_code', 'Herd', 'required|max_length[8]');
		//$this->form_validation->set_rules('herd_code_fill', 'Type Herd Code');
		$msg = [];

		if ($this->form_validation->run() == TRUE) { //successful submission
			$this->herd = new Herd($this->herd_model, $this->input->post('herd_code'));
			$herd_enroll_status_id = $this->herd->getHerdEnrollStatus();
			if($this->session->userdata('active_group_id') == 2){ //user is a producer
				$trials = $this->herd->getTrialData();
				if(isset($trials) && is_array($trials)){
					$today  = new DateTime();
					foreach($trials as $t){
						if($t['herd_trial_warning'] === null || $t['herd_trial_expires'] === null){
							//$msg[] = '<p>The trial period on ' . $t['value_abbrev'] . ' for herd ' . $this->input->post('herd_code') . ' will begin when you view a report.';
							continue;
						}
						$warn_date = new DateTime($t['herd_trial_warning']);
						$expire_date = new DateTime($t['herd_trial_expires']);
						$days_remain = $expire_date->diff($today)->days;
						if($t['herd_trial_is_expired'] === 1){
							$msg[] = '<p>The trial period on ' . $t['value_abbrev'] . ' for herd ' . $this->input->post('herd_code') . ' has expired. Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' to enroll on ' . $t['value_abbrev'] . ' and get the full benefit of the MyAgSource web site.';
						}
						elseif($warn_date <= $today){
							$msg[] = 'Herd ' . $this->input->post('herd_code') . ' has ' . $days_remain . ' days remaining on its free trial of ' . $t['value_abbrev'] . '.  To ensure uninterrupted access, please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' to enroll on ' . $t['value_abbrev'] . ' and get the full benefit of the MyAgSource web site.';
						}
					}
				}
				if($herd_enroll_status_id === 1){ //herd is not signed up at all
					$msg[] = 'Herd ' . $this->input->post('herd_code') . ' is not signed up for any eligible MyAgSource report products. Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' to enroll.';
				}
			}
			$this->set_herd_session_data($herd_enroll_status_id);

			$this->_record_access(2); //2 is the page code for herd change

			//NOTICES
			//Get any system notices
			$this->notifications = new Notifications($this->notice_model);
			$this->notifications->populateNotices();
			$notices = $this->notifications->getNoticesTexts();
			$msg = array_merge($msg,$notices);
			$this->redirect(site_url($this->session->userdata('redirect_url')), $msg);
			exit();
		}
		else {
			$err = '';
			//$tmp_arr = $this->as_ion_auth->get_viewable_herds($this->session->userdata('user_id'), $this->session->userdata('arr_regions'));
			$tmp_arr = $this->herd_access->getAccessibleHerdsData($this->session->userdata('user_id'), $this->permissions->permissionsList(), $this->session->userdata('arr_regions'));
			if(is_array($tmp_arr) && !empty($tmp_arr)){
				if(count($tmp_arr) === 1){
					$this->herd = new Herd($this->herd_model, $tmp_arr[0]['herd_code']);
					$herd_enroll_status_id = $this->herd->getHerdEnrollStatus();
					if($this->session->userdata('active_group_id') == 2){ //user is a producer
						$trials = $this->herd->getTrialData();
						if(isset($trials) && is_array($trials)){
							$today  = new DateTime();
							foreach($trials as $t){
                                if($t['herd_trial_warning'] === null || $t['herd_trial_expires'] === null){
                                    continue;
                                }
								$warn_date = new DateTime($t['herd_trial_warning']);
								$expire_date = new DateTime($t['herd_trial_expires']);
								$days_remain = $expire_date->diff($today)->days;
								if($t['herd_trial_is_expired'] === 1){
									$msg[] = '<p>The trial period on ' . $t['value_abbrev'] . ' for herd ' . $this->herd->herdCode() . ' has expired. Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' to enroll on ' . $t['value_abbrev'] . ' and get the full benefit of the MyAgSource web site.';
								}
								elseif($warn_date <= $today){
									$msg[] = 'Herd ' . $this->herd->herdCode() . ' has ' . $days_remain . ' days remaining on its free trial of ' . $t['value_abbrev'] . '.  To ensure uninterrupted access, please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' to enroll on ' . $t['value_abbrev'] . ' and get the full benefit of the MyAgSource web site.';
								}
							}
						}
						if($herd_enroll_status_id === 1){ //herd is not signed up at all
							$msg[] = 'Herd ' . $this->herd->herdCode() . ' is not signed up for any eligible MyAgSource report products. Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' to enroll.';
						}
					}
					$this->set_herd_session_data($herd_enroll_status_id);
//die;
					$this->redirect(site_url($this->session->userdata('redirect_url')), $msg);
					exit();
				}
				$this->load->library('herds');
				$this->data['arr_herd_data'] = $this->herds->set_herd_dropdown_array($tmp_arr);
				unset($tmp_arr);
			}
			else{
				$err = 'A list of herds could not be generated for your account.  If you believe this is an error, please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . '.';
			}


			$this->data['herd_code_fill'] = ['name' => 'herd_code_fill',
					'id' => 'herd_code_fill',
					'type' => 'text',
					'size' => '8',
					'maxlength' => '8',
					'value' => $this->form_validation->set_value('herd_code_fill'),
			];


			if(is_array($this->page_header_data)){
				$this->page_header_data = array_merge($this->page_header_data,
						[
								'title'=>'Select Herd - ' . $this->config->item('product_name'),
								'description'=>'Herd Selection Form for ' . $this->config->item('product_name'),
								'arr_headjs_line'=>['{datatable: "' . $this->config->item("base_url_assets") . 'js/dhi/herd_selection_helper.js"}']
						]
				);
			}
			$this->page_footer_data = [];
			$this->page_header_data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $err);
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
		if($this->permissions->hasPermission("Select Herd") && !$this->permissions->hasPermission("Request Herd")){
			$this->session->keep_all_flashdata();
			$this->redirect(site_url('dhi/change_herd/select'));
		}
		if(!$this->permissions->hasPermission("Request Herd")){
			$this->session->keep_all_flashdata();
			$this->redirect(site_url($this->session->userdata('redirect_url')), ['You do not have permissions to request herds.']);
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
				$this->session->keep_all_flashdata();
				$this->redirect(site_url('dhi/change_herd/request'), ['Invalid data submitted: ' . $error]);
			}
		}

		if ($this->form_validation->run() == TRUE) { //if validation is successful
			$this->herd = new Herd($this->herd_model, $this->input->post('herd_code'));
			$herd_enroll_status_id = $this->herd->getHerdEnrollStatus();
			if($this->session->userdata('active_group_id') == 2){ //user is a producer
				$msg = [];
				$trials = $this->herd->getTrialData();
				if(isset($trials) && is_array($trials)){
					$today  = new DateTime();
					foreach($trials as $t){
						$warn_date = new DateTime($t['herd_trial_warning']);
						$expire_date = new DateTime($t['herd_trial_expires']);
						$days_remain = $expire_date->diff($today)->days;
						if($t['herd_trial_is_expired'] === 1){
							$msg[] = '<p>The trial period on ' . $t['value_abbrev'] . ' for herd ' . $this->input->post('herd_code') . ' has expired. Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' to enroll on ' . $t['value_abbrev'] . ' and get the full benefit of the MyAgSource web site.';
						}
						elseif($warn_date <= $today){
							$msg[] = 'Herd ' . $this->input->post('herd_code') . ' has ' . $days_remain . ' days remaining on its free trial of ' . $t['value_abbrev'] . '.  To ensure uninterrupted access, please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' to enroll on ' . $t['value_abbrev'] . ' and get the full benefit of the MyAgSource web site.';
						}
					}
				}
				if($herd_enroll_status_id === 1){ //herd is not signed up at all
					$msg[] = 'Herd ' . $this->input->post('herd_code') . ' is not signed up for any eligible MyAgSource report products. Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' to enroll.';
				}
			}
			$this->set_herd_session_data($herd_enroll_status_id);

			$this->_record_access(2); //2 is the page code for herd change
			$this->redirect(site_url($this->session->userdata('redirect_url')));
		}
		else {  //the user is not logging in so display the login page
			$this->page_header_data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'));
			$this->data['herd_code'] = ['name' => 'herd_code',
					'id' => 'herd_code',
					'type' => 'text',
					'value' => $this->form_validation->set_value('herd_code'),
					'class' => 'require'
			];
			$this->data['herd_release_code'] = ['name' => 'herd_release_code',
					'id' => 'herd_release_code',
					'type' => 'text',
					'value' => $this->form_validation->set_value('herd_release_code'),
					'class' => 'require'
			];
			$this->data['report_path'] = '';
			if(is_array($this->page_header_data)){
				$this->page_header_data = array_merge($this->page_header_data,
						[
								'title'=>'Request Herd - ' . $this->config->item('product_name'),
								'description'=>'Herd Selection Form for ' . $this->config->item('product_name')
						]
				);
			}

			$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
			$this->data['page_heading'] = 'Request Herd - ' . $this->config->item('product_name');
			$this->data['page_footer'] = $this->load->view('page_footer', NULL, TRUE);

			$this->load->view('dhi/herd_request', $this->data);
		}
	}

	public function ajax_herd_enrolled($herd_code){
		//determines type of access for service groups
		if($this->permissions->hasPermission('View Assign w permission') === FALSE) {
			$enroll_status = 0;
			$has_accessed = false;
		}
		else{
			$this->herd = new Herd($this->herd_model, $herd_code);
			//for now, we want to warn if herd is not enrolled on full product
			$enroll_status = $this->herd->getHerdEnrollStatus(['AMYA-550', 'AMYA-500', 'APAG-505']);
			$recent_test = $this->herd->getRecentTest();
			$has_accessed = $this->access_log->sgHasAccessedTest($this->session->userdata('sg_acct_num'), $herd_code, null, $recent_test);
		}
		header('Content-type: application/json');
		header("Cache-Control: no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header("Expires: -1");
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
		$benchmarks = new Benchmarks($this->session->userdata('user_id'), $this->herd->herdCode(), $this->herd->header_info($this->herd->herdCode()), $this->setting_model, $this->benchmark_model, []);
		$this->session->set_userdata('benchmarks', $benchmarks->getSettingKeyValues());

		$general_dhi = new SessionSettings($this->session->userdata('user_id'), $this->herd->herdCode(), $this->setting_model, 'general_dhi', []);
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
