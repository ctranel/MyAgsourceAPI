<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/report_parent.php';
class Herd_overview extends parent_report {
//@todo how to best structure controllers that are consumed by other controllers?
	function index($pstring){
		$this->load->model('dhi/alert_model');
		$this->session->set_userdata('pstring', $pstring);
		$bench_data = $this->alert_model->get_benchmarks($this->session->userdata('herd_code'), $pstring);
		
		$subtitle = '80th percentile is derived from ' .  $bench_data['breed_code'] . ' herds';
		if(isset($bench_data['herd_size']) && !empty($bench_data['herd_size'])){
			$subtitle .= ' with ' . $bench_data['herd_size'] . ' animals';
		}
		
		unset($bench_data['breed_code']);

		$this->data = array(
			'herd_code' => $this->session->userdata('herd_code'),
//			'table_heading' => 'Herd Overview',
			'bench_data' => $bench_data,
		);
		
		return array(
			'content' => $this->load->view('dhi/herd_overview', $this->data, true),
			'subtitle' => $subtitle,
			'title' => 'Herd Overview',
		);
	}
}
