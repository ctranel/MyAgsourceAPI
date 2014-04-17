<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/report_parent.php';
class Herd_overview extends parent_report {

	function index($pstring){
		$this->load->model('dhi/alert_model');
		$this->session->set_userdata('pstring', $pstring);
		
		$this->data = array(
			'herd_code' => $this->session->userdata('herd_code')
//			,'table_heading' => 'Herd Overview'
			,'bench_data' => $this->alert_model->get_benchmarks($this->session->userdata('herd_code'), $pstring)
		);
		return $this->load->view('dhi/herd_overview', $this->data, true);
	}
}
