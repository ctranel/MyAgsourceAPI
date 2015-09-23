<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . 'libraries/dhi/HerdAccess.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');
require_once(APPPATH . 'libraries/AccessLog.php');
require_once(APPPATH . 'libraries/Notifications/Notifications.php');
require_once APPPATH . 'libraries/Settings/SessionSettings.php';
require_once(APPPATH . 'libraries/Benchmarks/Benchmarks.php');

use myagsource\Settings\SessionSettings;
use \myagsource\dhi\HerdAccess;
use \myagsource\dhi\Herd;
use \myagsource\AccessLog;
use \myagsource\notices\Notifications;
use \myagsource\Benchmarks\Benchmarks;

class Forms extends CI_Controller {
	/**
	 * herd_access
	 * @var HerdAccess
	 **/
	protected $herd_access;
	
	/**
	 * herd
	 *
	 * Herd object
	 * @var Herd
	 **/
	protected $herd;
	
	function __construct(){
		parent::__construct();
		$this->load->model('herd_model');
		$this->herd_access = new HerdAccess($this->herd_model);
		$this->herd = new Herd($this->herd_model, $this->session->userdata('herd_code'));
		$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
		
		//is someone logged in?
		if($this->herd->herdCode() != $this->config->item('default_herd')){
			if(!$this->as_ion_auth->logged_in()) {
				$this->redirect(site_url('auth/login'), "Please log in.  ");
			}
				
			//is a herd selected?
			if(!$this->herd->herdCode() || $this->herd->herdCode() == ''){
				$this->redirect(site_url('dhi/change_herd/select'), "Please select a herd and try again.  ");
			}
				
			//does logged in user have access to selected herd?
			$has_herd_access = $this->herd_access->hasAccess($this->session->userdata('user_id'), $this->herd->herdCode(), $this->session->userdata('arr_regions'), $this->ion_auth_model->getTaskPermissions());
			if(!$has_herd_access){
				$this->redirect(site_url('dhi/change_herd/select'),"You do not have permission to access this herd.  Please select another herd and try again.  ");
			}
		}
		
		$this->load->model('access_log_model');
		$this->access_log = new AccessLog($this->access_log_model);
				
		$this->page_header_data['num_herds'] = $this->herd_access->getNumAccessibleHerds($this->session->userdata('user_id'), $this->as_ion_auth->arr_task_permissions(), $this->session->userdata('arr_regions'));
		$this->page_header_data['navigation'] = $this->load->view('navigation', [], TRUE);

		//NOTICES
		//Get any system notices
		$this->load->model('notice_model');
		$this->notifications = new Notifications($this->notice_model);
	    $this->notifications->populateNotices();
	    $this->notices = $this->notifications->getNoticesTexts();
		
		/* Load the profile.php config file if it exists */
		if ((ENVIRONMENT == 'development' || ENVIRONMENT == 'localhost') && strpos($this->router->method, 'ajax') === false) {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			} 
		}
	}
	
	//redirects while retaining message and conditionally setting redirect url
	//@todo: needs to be a part of some kind of authorization class
	protected function redirect($url, $message = ''){
		$this->session->set_flashdata('message',  $this->session->flashdata('message') . $message);
		if(isset($method) && strpos($method, 'ajax') === false && strpos($method, '/demo') === false){
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
		}
		else{
			$this->session->keep_flashdata('redirect_url');
		}
		redirect($url);
	}

	function general(){
		$err = '';
		//form validation is handled by the controller to which the form is submitted
		
		//get setting data and load form
		$this->load->model('setting_model');
		$this->settings = new SessionSettings($this->session->userdata('user_id'), $this->session->userdata('herd_code'), $this->setting_model, 'general_dhi', $this->session->userdata('general_dhi')); //last optional param is session_values
		$settings_data = $this->settings->getFormData($this->session->userdata('dhi_settings')); 
		if(isset($settings_data)){
			$page_data['form'] = $this->load->view('dhi/settings/general', $settings_data, TRUE);
		}
		else{
			$err = 'General DHI Settings form could not be found.  Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' for assistance.';
		}

		//header
		$this->page_header_data = array_merge($this->page_header_data,
			[
				'title'=>'General DHI Settings  - ' . $this->config->item('product_name'),
				'description'=>'General DHI Settings Form for ' . $this->config->item('product_name'),
				'arr_headjs_line'=>[
					'{form_helper: "' . $this->config->item("base_url_assets") . 'js/form_helper.js"}',
				]
			]
		);
		$this->page_header_data['message'] = compose_error($err, validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages());
		$this->page_header_data['page_heading'] = 'General DHI Settings';
		
		//put it all together
		$page_data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
		$page_data['page_footer'] = $this->load->view('page_footer', [], TRUE);
			
		$this->load->view('form_page', $page_data);
   	}
	
	function benchmarks(){
		$err = '';
		//form validation is handled by the controller to which the form is submitted

		//get benchmark data and load form
		$this->load->model('setting_model');
		$this->load->model('benchmark_model');
		$this->benchmarks = new Benchmarks($this->session->userdata('user_id'), $this->input->post('herd_code'), $this->herd_model->header_info($this->herd->herdCode()), $this->setting_model, $this->benchmark_model, $this->session->userdata('benchmarks'));
		$arr_benchmark_data = $this->benchmarks->getFormData($this->session->userdata('benchmarks')); 
		if(isset($arr_benchmark_data)){
			$page_data['form'] = $this->load->view('dhi/settings/benchmarks', $arr_benchmark_data, TRUE);
		}
		else{
			$err = 'Benchmark form could not be found.  Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' for assistance.';
		}

		//header
		$this->page_header_data = array_merge($this->page_header_data,
			[
				'title'=>'Benchmark Settings  - ' . $this->config->item('product_name'),
				'description'=>'Benchmark Settings Form for ' . $this->config->item('product_name'),
				'arr_headjs_line'=>[
					'{form_helper: "' . $this->config->item("base_url_assets") . 'js/form_helper.js"}',
				]
			]
		);
		$this->page_header_data['message'] = compose_error($err, validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages());
		$this->page_header_data['page_heading'] = 'Benchmark Settings';
		
		//put it all together
		$page_data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
		$page_data['page_footer'] = $this->load->view('page_footer', [], TRUE);
			
		$this->load->view('form_page', $page_data);
   	}
	
   	function log_page(){
		echo $this->access_log_model->write_entry();
		exit;
	}
}