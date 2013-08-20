<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
class Herd_summary extends parent_report {
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

	 function index($block_in = NULL, $sort_by = NULL, $sort_order = NULL, $display_format = NULL){
	 	redirect(site_url('summary_reports/herd_summary/hs_prod'));
	 }
	function hs_prod($block_in = NULL, $sort_by = NULL, $sort_order = NULL, $display_format = NULL){
	 	$this->product_name = 'Herd Summary Production';
//		$this->page = 'hs_prod'; //corresponds with DB 'pages' table and function name.
//		$this->report_path = $this->section_path . '/' . $this->page;
		parent::display($block_in, $display_format);
	 }
	function hs_prod_charts($block_in = NULL, $sort_by = NULL, $sort_order = NULL, $display_format = NULL){
	 	$this->product_name = 'Herd Summary Production Charts';
		parent::display($block_in, $display_format);
	 }
	function hs_repro($block_in = NULL, $sort_by = NULL, $sort_order = NULL, $display_format = NULL){
	 	$this->product_name = 'Herd Summary Reproduction';
		parent::display($block_in, $display_format);
	 }
	function hs_gen_inv($block_in = NULL, $sort_by = NULL, $sort_order = NULL, $display_format = NULL){
	 	$this->product_name = 'Herd Summary Genetics & Inventory';
		parent::display($block_in, $display_format);
	 }

	 /*
	 * ajax_report: Called via AJAX to populate graphs
	 * to add flexibility (any graph/table can be called from any page),
	 * all block generation code has been moved to the report parent ajax_report function
	 */
}