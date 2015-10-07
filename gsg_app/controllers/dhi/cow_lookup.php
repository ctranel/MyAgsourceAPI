<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . 'libraries/dhi/HerdAccess.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');
require_once(APPPATH . 'libraries/AccessLog.php');
require_once(APPPATH . 'libraries/Notifications/Notifications.php');

use \myagsource\dhi\HerdAccess;
use \myagsource\dhi\Herd;
use \myagsource\AccessLog;
use \myagsource\notices\Notifications;

class Cow_lookup extends CI_Controller {
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
	
	/**
	 * cow_id_field
	 * @var String
	 **/
	protected $cow_id_field;
	
	var $cow_id;
	var $curr_lact_num;
	var $curr_calving_date;

	function __construct(){
		parent::__construct();
		//set redirect, this handles keeping flashdata when appropriate
		$redirect_url = set_redirect_url($this->uri->uri_string(), $this->session->userdata('redirect_url'));
		$this->session->set_userdata('redirect_url', $redirect_url);
		
		$this->load->model('herd_model');
		$this->herd_access = new HerdAccess($this->herd_model);
		$this->herd = new Herd($this->herd_model, $this->session->userdata('herd_code'));
		$this->cow_id_field = $this->session->userdata('general_dhi')['cow_id_field'];
		
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
		$this->session->keep_all_flashdata();
		redirect($url);
	}

	function index(){
		//validate form input
		$this->load->library('form_validation');
		$this->form_validation->set_rules('cow_ref', 'Cow', 'required|max_length[8]');
		$this->form_validation->set_rules('tab', 'Tab', '');
		//$this->form_validation->set_rules('herd_code_fill', 'Type Herd Code');
		$cow_options = $this->herd->getCowOptions($this->cow_id_field);
		if ($this->form_validation->run() == TRUE) { //successful submission
			$serial_num = $this->form_validation->set_value('cow_ref', key($cow_options));
			$tab = $this->form_validation->set_value('tab', 'events');
		}
		else {
			$serial_num = key($cow_options);
			$tab = 'events';
		}

		//load cow data for tabs
		$this->_loadObjVars($serial_num);
    	$this->load->model('dhi/cow_lookup/events_model');
    	$events_data = $this->events_model->getCowArray($this->session->userdata('herd_code'), $serial_num);
    	$tab_data = [];
    	if($events_data){
    		$events_data['serial_num'] = $serial_num;
	    	$events_data['show_all_events'] = false;
	    	$events_data['arr_events'] = $this->events_model->getEventsArray($this->session->userdata('herd_code'), $serial_num, $this->curr_calving_date, false);
	    	$tab_data = [
				'serial_num'=>$serial_num
	    		,'cow_id'=>$events_data[$this->cow_id_field]
				,'events_content' => $this->load->view('dhi/cow_lookup/events', $events_data, true)
	    		,'tab' => $tab
	    	];
    	}
    	else{
    		$tab_data = [
				'serial_num'=>$serial_num
	    		,'cow_id'=>'unknown'
				,'events_content' => 'No Data Found for Selected Animal.'
	    		,'tab' => $tab
	    	];
    	}
    	
		$err = '';
		$form_data['cow_selected'] = $serial_num;
		if(is_array($cow_options) && !empty($cow_options)){
			$form_data['cow_options'] = ['' => 'Select'] + $cow_options;
			unset($cow_options);
		}
		else{
			$err = 'A list of cows could not be generated for your herd.  If you believe this is an error, please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . '.';
		}

		$form_data['cow_ref'] = 'id = "cow_ref"';
		$form_data['tab'] = $tab;
		$form_data['cow_fill'] = [
			'name' => 'cow_fill',
			'id' => 'cow_fill',
			'type' => 'text',
			'size' => '15',
			'maxlength' => '15',
			'value' => $this->form_validation->set_value('cow_fill'),
		];

		$this->page_header_data = array_merge($this->page_header_data,
			[
				'title'=>'Cow Lookup - ' . $this->config->item('product_name'),
				'description'=>'Cow Lookup Form for ' . $this->config->item('product_name'),
				'arr_headjs_line'=>[
					'{datatable: "' . $this->config->item("base_url_assets") . 'js/dhi/cow_lookup_helper.js"}',
					'{table_sort: "' . $this->config->item("base_url_assets") . 'js/jquery/stupidtable.min.js"}',
					'{highcharts: "https://code.highcharts.com/4.1.7/highcharts.js"}',
					'{highcharts_more: "https://code.highcharts.com/4.1.7/highcharts-more.js"}',
					'{exporting: "https://code.highcharts.com/4.1.7/modules/exporting.js"}',
					'{chart_options: "' . $this->config->item("base_url_assets") . 'js/charts/chart_options.js"}',
					'{graph_helper: "' . $this->config->item("base_url_assets") . 'js/charts/graph_helper.js"}',
					'{report_helper: "' . $this->config->item("base_url_assets") . 'js/report_helper.js"}',
				]
			]
		);
		$this->carabiner->css('tabs.css');
		$this->carabiner->css('report.css');
		$this->carabiner->css('cow_page.css');
		$page_footer_data = array();
		$this->page_header_data['message'] = compose_error($err, validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages());
		$this->page_header_data['page_heading'] = 'Cow Lookup';
		$page_data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
		$page_data['page_footer'] = $this->load->view('page_footer', $page_footer_data, TRUE);
		$page_data['form'] = $this->load->view('dhi/cow_lookup/cow_lookup_form', $form_data, true);
		
		$page_data['tabs'] = $this->load->view('dhi/cow_lookup/land', $tab_data, true);
			
		$this->load->view('dhi/cow_lookup/cow_lookup_page', $page_data);
   	}
	
   	//@todo: create a cowpage library files and move this there
   	protected function _loadObjVars($serial_num){
   		$this->load->model('dhi/cow_lookup/events_model');
   		$events_data = $this->events_model->getCowArray($this->session->userdata('herd_code'), $serial_num);
   		$this->cow_id = $events_data[$this->cow_id_field];
   		$this->curr_lact_num = $events_data['curr_lact_num'];
   		$this->curr_calving_date = $events_data['curr_calving_date'];
   	}
   	
	function log_page(){
		echo $this->access_log_model->write_entry();
		exit;
	}
}