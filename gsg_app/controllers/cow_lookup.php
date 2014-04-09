<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Cow_lookup extends CI_Controller {
	var $barn_name;
	var $curr_lact_num;
	var $curr_calving_date;
	
	function __construct(){
		parent::__construct();
		$this->session->keep_flashdata('message');
		//make sure previous page remains as the redirect url 
		$redirect_url = set_redirect_url($this->uri->uri_string(), $this->session->flashdata('redirect_url'), $this->as_ion_auth->referrer);
		$this->session->set_flashdata('redirect_url', $redirect_url);
		
		if((!isset($this->as_ion_auth) || !$this->as_ion_auth->logged_in()) && $this->session->userdata('herd_code') != $this->config->item('default_herd')){
			$msg = $this->load->view('session_expired', array('url'=>$this->session->flashdata('redirect_url')), true);
			echo $msg;
			exit;
		}
		
		/* Load the profile.php config file if it exists
		if (ENVIRONMENT == 'development') {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			} 
		} */
	}
	
    function index($serial_num, $tab = 'events'){
		$this->_loadObjVars($serial_num);
    	$this->load->model('cow_lookup/events_model');
    	$events_data = $this->events_model->getCowArray($this->session->userdata('herd_code'), $serial_num);
    	$events_data['serial_num'] = $serial_num;
    	$events_data['show_all_events'] = false;
    	$events_data['arr_events'] = $this->events_model->getEventsArray($this->session->userdata('herd_code'), $serial_num, $this->curr_calving_date, false);
    	$data = array(
			'serial_num'=>$serial_num
    		,'barn_name'=>$events_data['barn_name']
			,'events_content' => $this->load->view('cow_lookup/events', $events_data, true)
    		,'tab' => $tab
    	);
    	$this->_record_access(93);
    	$this->load->view('cow_lookup/land', $data);
	}
	
	function events($serial_num, $show_all_events = 0){
		$this->_loadObjVars($serial_num);
		$this->load->model('cow_lookup/events_model');
    	$data = $this->events_model->getCowArray($this->session->userdata('herd_code'), $serial_num);
    	$data['serial_num'] = $serial_num;
     	$data['show_all_events'] = (bool)$show_all_events;
   		$data['arr_events'] = $this->events_model->getEventsArray($this->session->userdata('herd_code'), $serial_num, $this->curr_calving_date, (bool)$show_all_events);
       	$this->load->view('cow_lookup/events', $data);
	}
	
	function id($serial_num){
		$this->load->model('cow_lookup/id_model');
    	$data = $this->id_model->getCowArray($this->session->userdata('herd_code'), $serial_num);
    	$this->load->view('cow_lookup/id', $data);
	}

	function dam($serial_num){
		$this->load->model('cow_lookup/dam_model');

    	$data['dam'] = $this->dam_model->getCowArray($this->session->userdata('herd_code'), $serial_num);
		//build lactation tables
		$this->load->model('cow_lookup/lactations_model');
		$tab = array();
		if(isset($data['dam']['dam_serial_num']) && !empty($data['dam']['dam_serial_num'])){
			$subdata['arr_lacts'] = $this->lactations_model->getLactationsArray($this->session->userdata('herd_code'), $data['dam']['dam_serial_num']);
			$tab['lact_table'] = $this->load->view('cow_lookup/lactations_table', $subdata, true);
			$subdata['arr_offspring'] = $this->lactations_model->getOffspringArray($this->session->userdata('herd_code'), $data['dam']['dam_serial_num']);
			$tab['offspring_table'] = $this->load->view('cow_lookup/offspring_table', $subdata, true);
			unset($subdata);
		}
		$data['lact_tables'] = $this->load->view('cow_lookup/lactations', $tab, true);
		unset($tab);
		
		$this->load->view('cow_lookup/dam', $data);
	}
	
	function sire($serial_num){
		$this->load->model('cow_lookup/sire_model');
    	$data = $this->sire_model->getCowArray($this->session->userdata('herd_code'), $serial_num);
		$this->load->view('cow_lookup/sire', $data);
	}
	
	function tests($serial_num, $lact_num=NULL){
		if(!isset($this->curr_lact_num) || !isset($this->barn_name)) {
			$this->_loadObjVars($serial_num);
		}
		if(!isset($lact_num)){
			$lact_num = $this->curr_lact_num;
		}
		$this->load->model('cow_lookup/tests_model');
		$data = array(
			'arr_tests' => $this->tests_model->getTests($this->session->userdata('herd_code'), $serial_num, $lact_num)
			,'barn_name' => $this->barn_name
			,'serial_num' => $serial_num
			,'lact_num' => $lact_num
			,'curr_lact_num' => $this->curr_lact_num
		);
		$this->load->view('cow_lookup/tests', $data);
	}
	
	function lactations($serial_num){
		$this->load->model('cow_lookup/lactations_model');
    	$data['arr_lacts'] = $this->lactations_model->getLactationsArray($this->session->userdata('herd_code'), $serial_num);
		$tab['lact_table'] = $this->load->view('cow_lookup/lactations_table', $data, true);
    	$data['arr_offspring'] = $this->lactations_model->getOffspringArray($this->session->userdata('herd_code'), $serial_num);
		$tab['offspring_table'] = $this->load->view('cow_lookup/offspring_table', $data, true);
		$this->load->view('cow_lookup/lactations', $tab);
	}
	
	function graphs($serial_num, $lact_num=NULL){
		if(!isset($this->curr_lact_num) || !isset($this->barn_name)) {
			$this->_loadObjVars($serial_num);
		}
		if(!isset($lact_num)){
			$lact_num = $this->curr_lact_num;
		}
		$this->load->model('cow_lookup/graphs_model');
		$this->load->library('chart');
		$data = array(
			'arr_tests' => $this->chart->formatDataSet($this->graphs_model->getGraphData($this->session->userdata('herd_code'), $serial_num, $lact_num), 'lact_dim')
			,'barn_name' => $this->barn_name
			,'serial_num' => $serial_num
			,'lact_num' => $lact_num
			,'curr_lact_num' => $this->curr_lact_num
		);
		$this->load->view('cow_lookup/graphs', $data);
	}

	protected function _loadObjVars($serial_num){
		$this->load->model('cow_lookup/events_model');
		$events_data = $this->events_model->getCowArray($this->session->userdata('herd_code'), $serial_num);
		$this->barn_name = $events_data['barn_name'];
		$this->curr_lact_num = $events_data['curr_lact_num'];
		$this->curr_calving_date = $events_data['curr_calving_date'];
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