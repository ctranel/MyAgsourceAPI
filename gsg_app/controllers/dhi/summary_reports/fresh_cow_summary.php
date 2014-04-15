<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
class Fresh_cow_summary extends parent_report {
	function __construct(){
		parent::__construct();
		/* Load the profile.php config file if it exists
		$this->config->load('profiler', false, true);
		if ($this->config->config['enable_profiler']) {
			$this->output->enable_profiler(TRUE);
		} */
	}

	 function index($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	redirect(site_url('dhi/summary_reports/fresh_cow_summary/fc_tci'));
	 }
	function fc_tci($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'Fresh Cow Summary - TCI';
		parent::display($block_in, $display_format);
	 }
	function fc_cull($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'Fresh Cow Summary - Culling';
		parent::display($block_in, $display_format);
	 }
	function fc_health($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'Fresh Cow Summary - Health';
		parent::display($block_in, $display_format);
	 }
	 
	 protected function derive_series($arr_fields){
	 	if($this->graph['config']['chart']['type'] !== 'boxplot'){
	 		return parent::derive_series($arr_fields);
	 	}
		$return_val = array();
	 	$c = 0;
	 	$arr_chart_type = $this->{$this->primary_model}->get_chart_type_array();
	 	$arr_axis_index = $this->{$this->primary_model}->get_axis_index_array();
	 		
	 	foreach($arr_fields as $k=>$f){
	 		//for the median field, create 1 series for the boxplot and 1 for the trend line ("$c + 1" is the trend line)
	 		if(strpos($f, 'median') !== FALSE){
	 			$return_val[$c]['name'] = trim(str_replace('50th Pct.', '', $k));
	 			$return_val[$c + 1]['name'] = $k;
//	 			if(isset($this->{$this->primary_model}->arr_unit_of_measure[$f]) && !empty($this->{$this->primary_model}->arr_unit_of_measure[$f])) $um = $this->{$this->primary_model}->arr_unit_of_measure[$f];
		 		if(isset($arr_axis_index[$f]) && !empty($arr_axis_index[$f])) {
		 			$return_val[$c]['yAxis'] = $arr_axis_index[$f];
		 			$return_val[$c + 1]['yAxis'] = $arr_axis_index[$f];
		 		}
		 		if(isset($arr_chart_type[$f]) && !empty($arr_chart_type[$f])) {
		 			$return_val[$c + 1]['type'] = $arr_chart_type[$f];
		 		}
		 		$return_val[$c + 1]['linkedTo'] = ':previous';
		 		$return_val[$c + 1]['enableMouseTracking'] = false;
		 		$return_val[$c + 1]['marker']['enabled'] = false;
		 		$c += 2;
	 		}
	 	}
	 	return $return_val;
	 }
}