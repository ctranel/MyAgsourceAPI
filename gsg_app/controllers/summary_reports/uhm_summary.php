<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
class Uhm_summary extends parent_report {
	function __construct(){
		parent::__construct();
		/* Load the profile.php config file if it exists
		$this->config->load('profiler', false, true);
		if ($this->config->config['enable_profiler']) {
			$this->output->enable_profiler(TRUE);
		} */
	}

	 function index($block_in = NULL, $sort_by = NULL, $sort_order = NULL, $display_format = NULL){
	 	redirect(site_url('summary_reports/uhm_summary/uhm_risk_grp'));
	 }
	function uhm_risk_grp($block_in = NULL, $sort_by = NULL, $sort_order = NULL, $display_format = NULL){
	 	$this->product_name = 'UHM - Risk Analysis';
		parent::display($block_in, $display_format);
	 }
	function uhm_dist_scc($block_in = NULL, $sort_by = NULL, $sort_order = NULL, $display_format = NULL){
	 	$this->product_name = 'UHM - Distribution by SCC';
		parent::display($block_in, $display_format);
	 }
	 function uhm_dim($block_in = NULL, $sort_by = NULL, $sort_order = NULL, $display_format = NULL){
	 	$this->product_name = 'UHM - DIM at First Test';
	 	parent::display($block_in, $display_format);
	 }
	 function uhm_infect($block_in = NULL, $sort_by = NULL, $sort_order = NULL, $display_format = NULL){
	 	$this->product_name = 'UHM - Infection by Lact Group';
	 	parent::display($block_in, $display_format);
	 }
	 function uhm_wgt_scc($block_in = NULL, $sort_by = NULL, $sort_order = NULL, $display_format = NULL){
	 	$this->product_name = 'UHM - Weighted Avg SCC';
	 	parent::display($block_in, $display_format);
	 }
	 
	 protected function get_section_data($block, $pstring, $sort_by, $sort_order, $report_count){
	 	$arr_return = array(
	 			'block' => $block,
	 			//'test_date' => $test_date[0],
	 			'pstring' => $pstring,
	 			'sort_by' => $sort_by,
	 			'sort_order' => $sort_order,
	 			'graph_order' => $report_count
	 	);
	 	$arr_return['avg_weighted_avg'] = 200;//if block == 'weighted_average_scc_-_la', get average scc
	 	return $arr_return;
	 }
}