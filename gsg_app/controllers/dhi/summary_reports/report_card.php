<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
class Report_card extends report_parent {
	function __construct(){
//		$this->section_path = 'herd_summary'; //this should match the name of this file (minus ".php".  Also used as base for css and js file names and model directory name
//		$this->primary_model = 'herd_summary_model';
		parent::__construct();
		/* Load the profile.php config file if it exists
		$this->config->load('profiler', false, true);
		if ($this->config->config['enable_profiler']) {
			$this->output->enable_profiler(TRUE);
		} */
	}

	 function index($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
		$this->session->keep_all_flashdata();
	 	redirect(site_url('dhi/summary_reports/report_card/rc_curr'));
	 }
	function rc_curr($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'Report Card - Current Test';
		parent::display($block_in, $display_format);
	 }
	function rc_long($block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
	 	$this->product_name = 'Report Card - Long View';
		parent::display($block_in, $display_format);
	 }
	 
	 /*
	  * ajax_report: Called via AJAX to populate graphs
	 * @param string block: name of the block for which to retreive data
	 * @param string output: method of output (chart, table, etc)
	 * @param boolean/string file_format: return the value of function (TRUE), or echo it (FALSE).  Defaults to FALSE
	 * @param string cache_buster: text to make page appear as a different page so that new data is retrieved
	 */
	 public function ajax_report($page, $block, $sort_by = 'null', $sort_order = 'null', $report_count=0, $json_filter_data = NULL, $cache_buster = NULL) {//, $herd_size_code = FALSE, $all_breeds_code = FALSE
		if(strpos($page, '_long') === FALSE){
		 	$this->{$this->primary_model_name}->historical(false);
		}
		else{
		 	$this->{$this->primary_model_name}->historical(true);
		}
		parent::ajax_report($page, $block, $sort_by, $sort_order, $report_count, $json_filter_data);
	 }
}