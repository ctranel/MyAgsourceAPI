<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Cow_lookup extends CI_Controller {
	function __construct(){
		parent::__construct();
	}
	
    function index($serial_num){
		$this->load->model('cow_lookup/events_model', 'events_model');
    	$events_data = $this->events_model->getCowArray($this->session->userdata('herd_code'), $serial_num);
    	$data = array(
			'serial_num'=>$serial_num
    		,'barn_name'=>$events_data['barn_name']
			,'events_content' => $this->load->view('cow_lookup/events', $events_data, true)
		);
    	$this->load->view('cow_lookup/land', $data);
	}
	
	function events($serial_num){
		$this->load->model('cow_lookup/events_model', 'events_model');
    	$data = $this->events_model->getCowArray($this->session->userdata('herd_code'), $serial_num);
    	$this->load->view('cow_lookup/events', $data);
	}
	
	function id($serial_num){
		$this->load->model('cow_lookup/id_model', 'id_model');
    	$data = $this->id_model->getCowArray($this->session->userdata('herd_code'), $serial_num);
    	$this->load->view('cow_lookup/id', $data);
	}

	function dam($serial_num){
		$data = search(
				$this->userdata->session('herd_code')
				, array('serial_num' => $serial_num)
		);
		$this->load->view('cow_lookup/dam', $data);
	}
	
	function sire($serial_num){
		$data = search(
				$this->userdata->session('herd_code')
				, array('serial_num' => $serial_num)
		);
		$this->load->view('cow_lookup/sire', $data);
	}
	
	function tests($serial_num){
		$data = search(
				$this->userdata->session('herd_code')
				, array('serial_num' => $serial_num)
		);
		$this->load->view('cow_lookup/tests', $data);
	}
	
	function lactations($serial_num){
		$data = search(
				$this->userdata->session('herd_code')
				, array('serial_num' => $serial_num)
		);
		$this->load->view('cow_lookup/lactations', $data);
	}
	
	function graphs($serial_num){
		$data = search(
				$this->userdata->session('herd_code')
				, array('serial_num' => $serial_num)
		);
		$this->load->view('cow_lookup/graphs', $data);
	}

	function log_page(){
		echo $this->access_log_model->write_entry(); //19 is the page code for DM Login
		exit;
	}
}