<?php
//namespace myagsource;
require_once APPPATH . 'libraries' . FS_SEP . 'db_objects' . FS_SEP . 'db_table.php';
require_once(APPPATH . 'libraries' . FS_SEP . 'filters' . FS_SEP . 'Filters.php');
require_once(APPPATH . 'libraries' . FS_SEP . 'benchmarks_lib.php');
require_once(APPPATH . 'libraries' . FS_SEP . 'access_log.php');
require_once(APPPATH . 'libraries' . FS_SEP . 'supplemental' . FS_SEP . 'Supplemental.php');

use \myagsource\db_objects\db_table;
use \myagsource\settings\Benchmarks_lib;
use \myagsource\Access_log;
use \myagsource\report_filters\Filters;
use \myagsource\supplemental\Supplemental;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* -----------------------------------------------------------------
 *	CLASS comments
 *  @file: report_parent.php
 *  @author: ctranel
 *
 *  @description: Parent abstract class that drives report page generation.  All database driven report pages 
 *  	extend this class.
 *
 * -----------------------------------------------------------------
 */

abstract class parent_report extends CI_Controller {
	protected $section_id;
	protected $report_form_id;
	protected $log_filter_text;
	protected $arr_sort_by = array();
	protected $arr_sort_order = array();
	protected $pstring;
	protected $breed_code;
	protected $herd_code;
	protected $product_name;
	protected $report_path;
	protected $primary_model;
	protected $section_path; //The path to the site section; set in constructor to point to the controller name
	protected $page_header_data;
	protected $report_data;
	protected $display;
	protected $html;
	protected $graph;
	protected $page; //url segment of current page
	protected $filters; //filters object
	protected $objPage; //object of current page
	protected $block;
	protected $report_count;
	protected $print_all = FALSE;
	protected $data_dump = FALSE;
	protected $max_rows;
	protected $max_row;
	protected $cnt_row;
	protected $sum_row;
	protected $avg_row;
	protected $pivot_db_field;
	protected $bool_is_summary;
	protected $supplemental;

	/**
	 * Benchmark settings
	 * 
	 * @var Session_settings object
	protected $bench_setting;
	 */
	
	function __construct(){
		parent::__construct();
		$class_dir = $this->router->fetch_directory(); //this should match the name of this file (minus ".php".  Also used as base for css and js file names and model directory name
		$class = $this->router->fetch_class();
		$method = $this->router->fetch_method();
		$this->section_path = $class_dir . $class;
		
		$this->page = $this->router->fetch_method();
		$this->report_path = $this->section_path . '/' . $this->page;
		$this->primary_model = $this->page . '_model';
		$this->report_form_id = 'report_criteria';//filter-form';
		$this->herd_code = strlen($this->session->userdata('herd_code')) == 8?$this->session->userdata('herd_code'):NULL;
		$this->page_header_data['user_sections'] = $this->as_ion_auth->arr_user_super_sections;
		$this->page_header_data['num_herds'] = $this->as_ion_auth->get_num_viewable_herds($this->session->userdata('user_id'), $this->session->userdata('arr_regions'));
		
		//load most specific model available.  Must load model before setting section_id
		$path = uri_string();
		$arr_path = explode('/',$path);
		if($method == 'ajax_report') {
			$tmp_index = array_search($method,$arr_path);
			$block = $arr_path[($tmp_index +2)];
		}
		else {
			$block = '';
		}
		//Load the most specific model that exists
		if(file_exists(APPPATH . 'models' . FS_SEP . $this->section_path . FS_SEP . $block . '_model.php')){
			$this->primary_model = $block. '_model';
			$this->load->model($this->section_path . '/' . $this->primary_model, '', FALSE, $this->section_path);
			
		}
		elseif(file_exists(APPPATH . 'models' . FS_SEP . $this->section_path . FS_SEP . $class . '_model.php')){
			$this->primary_model = $class . '_model';
			$this->load->model($this->section_path . '/' . $this->primary_model, '', FALSE, $this->section_path);
		}
		else{
			$this->primary_model = 'report_model';
			$this->load->model('report_model', '', FALSE, $this->section_path);
		}
		
		$this->section_id = $this->{$this->primary_model}->get_section_id();

		if($this->authorize($method)) {
			$this->load->library('reports');
			$this->reports->herd_code = $this->herd_code;
		}
//		else {  //redirect to login if not logged in or session is expired
//			if($this->session->flashdata('message')) $this->session->keep_flashdata('message');
//			if($method != 'ajax_report') $this->session->set_flashdata('redirect_url', $this->uri->uri_string());
//			redirect(site_url('auth/login'));
//		}
		
		if($this->session->userdata('herd_code') == ''){ // || $this->session->userdata('herd_code') == '35990571'
			$this->session->keep_flashdata('redirect_url');
			redirect(site_url('dhi/change_herd/select'));			
		}
		/* Load the profile.php config file if it exists*/
		if (ENVIRONMENT == 'development' || ENVIRONMENT == 'localhost') {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			} 
		}
	}

	protected function authorize($method){
		if(!isset($this->as_ion_auth)){
	       	if($method == 'ajax_report' && $this->herd_code != $this->config->item('default_herd')){
				echo "Your session has expired, please log in and try again..";
				exit;
			}
			else return FALSE;
		}
		if(!$this->as_ion_auth->logged_in() && $this->herd_code != $this->config->item('default_herd')) {
	       	if($method == 'ajax_report'){
				echo "Your session has expired, please log in and try again...";
				exit;
			}
			else {
	       		$this->session->set_flashdata('message', "Please log in.");
				return FALSE;
			}
		}
		if(!isset($this->herd_code)){
	       	if($method == 'ajax_report'){
				echo 'Either your session expired, or you have not yet chosen a herd.  Please select a herd and try again.';
			}
			else {
				$this->session->set_flashdata('message',  $this->session->flashdata('message') . "Please select a herd and try again.");
				if($this->session->flashdata('message')) $this->session->keep_flashdata('message');
				$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
				redirect(site_url('dhi/change_herd/select'));
			}
  			exit;
		}
		//if section scope is public, pass unsubscribed test
		//@todo: build display_hierarchy/report_organization, etc interface with get_scope function (with classes for super_sections, sections, etc)
		$pass_unsubscribed_test = true; //$this->as_ion_auth->get_scope('sections', $this->section_id) == 'pubic';
		$pass_unsubscribed_test = $this->as_ion_auth->has_permission("View Unsubscribed Herds") || $this->web_content_model->herd_is_subscribed($this->section_id, $this->herd_code);
		$pass_view_nonowned_test = $this->as_ion_auth->has_permission("View All Herds") || $this->session->userdata('herd_code') == $this->config->item('default_herd');
		if(!$pass_view_nonowned_test) $pass_view_nonowned_test = in_array($this->herd_code, $this->as_ion_auth->get_viewable_herd_codes($this->session->userdata('user_id'), $this->session->userdata('arr_regions')));//$this->as_ion_auth->has_permission("View Non-owned Herds") || $this->ion_auth_model->user_owns_herd($this->herd_code);
		if($pass_unsubscribed_test && $pass_view_nonowned_test) return TRUE;
		elseif(!$pass_unsubscribed_test && !$pass_view_nonowned_test) {
			if($method == 'ajax_report') {
				echo 'Herd ' . $this->herd_code . ' is not subscribed to the ' . $this->product_name . ', nor do you have permission to view this report for herd ' . $this->herd_code . '.  Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' if you have questions or concerns.';
			}
			else {
				$this->session->set_flashdata('message', 'Herd ' . $this->herd_code . ' is not subscribed to the ' . $this->product_name . ', nor do you have permission to view this report for herd ' . $this->herd_code . '.  Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' if you have questions or concerns.');
 				if($this->session->flashdata('message')) $this->session->keep_flashdata('message');
				$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
				redirect(site_url('dhi/change_herd/select'));
      		}
			exit;
		}
		elseif(!$pass_unsubscribed_test) {
			if($method == 'ajax_report') {
				echo 'Herd ' . $this->herd_code . ' is not subscribed to the ' . $this->product_name . '.  Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' if you have questions or concerns.';
			}
			else {
				$this->session->set_flashdata('message', 'Herd ' . $this->herd_code . ' is not subscribed to the ' . $this->product_name . '.  Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' if you have questions or concerns.');
 				if($this->session->flashdata('message')) $this->session->keep_flashdata('message');
				$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
				redirect(site_url());
			}
			exit;
		}
		elseif(!$pass_view_nonowned_test) {
			if($method == 'ajax_report') {
				echo 'You do not have permission to view the ' . $this->product_name . ' for herd ' . $this->herd_code . '.  Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' if you have questions or concerns.';
			}
			else {
				$this->session->set_flashdata('message', 'You do not have permission to view the ' . $this->product_name . ' for herd ' . $this->herd_code . '.  Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' if you have questions or concerns.');
 				if($this->session->flashdata('message')) $this->session->keep_flashdata('message');
				$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
				redirect(site_url('dhi/change_herd/select'));
			}
			exit;
		}
		return FALSE;
	}
	
	function index(){
		redirect(site_url($this->report_path));
	}

	function display($arr_block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
		//Create block info as array in arr_block_in if not an array
		if(isset($arr_block_in) && !empty($arr_block_in) && !is_array($arr_block_in)) $arr_block_in = array($arr_block_in);
		$this->objPage = $this->{$this->primary_model}->arr_blocks[$this->page];
		$arr_blocks = $this->objPage['blocks'];

		//Determine if any report blocks have is_summary flag - will determine if tstring needs to be loaded and filters shown
		$this->load->helper('multid_array_helper');
		$this->bool_is_summary = array_search(1, get_elements_by_key('is_summary', $arr_blocks)) === FALSE ? FALSE : TRUE;
		//Check for valid herd_code
		if(empty($this->herd_code) || strlen($this->herd_code) != 8){
			$this->session->set_flashdata('message', 'Please select a valid herd.');
			redirect(site_url($this->report_path));
		}

		//FILTERS
		//load required libraries
		$this->load->model('filter_model');
		$this->filters = new Filters($this->filter_model);
		$recent_test_date = isset($primary_table) ? $this->{$this->primary_model}->get_recent_dates() : NULL;
		$this->filters->set_filters(
				$this->section_id,
				$this->page,
				array(
					'herd_code' =>	$this->session->userdata('herd_code'),
				) //filter form submissions never trigger a new page load (i.e., this function is never fired by a form submission)
		);
		//END FILTERS
		if ($display_format == 'csv'){
			$data = array();
			if(isset($arr_blocks) && is_array($arr_blocks)){
				foreach($arr_blocks as $pb){
					if($pb['display_type'] !== 'table'){
						continue;
					}
					if(($arr_block_in !== NULL && in_array($pb['url_segment'], $arr_block_in)) || $arr_block_in == NULL){
						if(isset($sort_by) && isset($sort_order)){
							$this->arr_sort_by = array_values(explode('|', $sort_by));
							$this->arr_sort_order = array_values(explode('|', $sort_order));
						}
						else {
							$tmp = $this->{$this->primary_model}->get_default_sort($pb['url_segment']);
							$this->arr_sort_by = $tmp['arr_sort_by'];
							$this->arr_sort_order = $tmp['arr_sort_order'];
							$sort_by = implode('|', $this->arr_sort_by);
							$sort_order = implode('|', $this->arr_sort_order);
						}
						$this->reports->sort_text($this->arr_sort_by, $this->arr_sort_order);//this function sets text, and could return it if needed
						$tmp_data = $this->ajax_report(urlencode($this->page), urlencode($pb['url_segment']), 'array', urlencode($sort_by), $sort_order, 'csv', NULL);
						$data[] = array('test_date' => $pb['description']);
						$data = array_merge($data, $tmp_data);
					}
				}
			}
			if(is_array($data) && !empty($data)){
				$this->reports->create_csv($data);
				$this->_record_access(90, $this->objPage['page_id'], 'csv', $this->config->item('product_report_code'));
			}
			else {
				$this->{$this->primary_model}->arr_messages[] = 'There is no data to export into an Excel file.';
			}
			exit;
		}
		elseif ($display_format == 'pdf' && !is_null($arr_block_in)) {
			$this->load->library('table_header');
			$data = array();
			$herd_data = $this->herd_model->header_info($this->session->userdata('herd_code'));
			$i = 0;

			if(isset($arr_blocks) && is_array($arr_blocks)){
				foreach($arr_blocks as $pb){
					if($pb['display_type'] == 'table'){
						continue;
					}
					if(($arr_block_in !== NULL && in_array($pb['url_segment'], $arr_block_in)) || $arr_block_in == NULL){
					//SORT
						if(isset($sort_by) && isset($sort_order)){
							$this->arr_sort_by = array_values(explode('|', $sort_by));
							$this->arr_sort_order = array_values(explode('|', $sort_order));
						}
						else {
							$tmp = $this->{$this->primary_model}->get_default_sort($pb['url_segment']);
							$this->arr_sort_by = $tmp['arr_sort_by'];
							$this->arr_sort_order = $tmp['arr_sort_order'];
							$sort_by = implode('|', $this->arr_sort_by);
							$sort_order = implode('|', $this->arr_sort_order);
						}

						$this->{$this->primary_model}->populate_field_meta_arrays($pb['id']);
						$block[$i]['data'] = $this->ajax_report(urlencode($this->page), urlencode($pb['url_segment']), 'array', urlencode($sort_by), $sort_order, 'pdf', NULL);
						$tmp_pdf_width = $this->{$this->primary_model}->get_pdf_widths(); 
						$block[$i]['arr_pdf_widths'] = $tmp_pdf_width;
						$arr_header_data = $this->{$this->primary_model}->get_fields(); // was $model
						$block[$i]['header_structure'] = $this->table_header->get_table_header_array($arr_header_data, $tmp_pdf_width);
						$block[$i]['title'] = $pb['description'];
						$i++;
					}
				}
			}
			$this->_record_access(90, $this->objPage['page_id'], 'pdf', $this->config->item('product_report_code'));
			$this->reports->create_pdf($block, $this->product_name, NULL, $herd_data, 'P');
			exit;
		}

		// render page
		//get_herd_data
		$herd_data = $this->herd_model->header_info($this->session->userdata('herd_code'));
		
		//set js lines and load views for each block to be displayed on page
		$tmp_js = '';
		$arr_view_blocks = NULL;
		$has_benchmarks = false;
		if(isset($arr_blocks) && !empty($arr_blocks)){
			$x = 0;
			$consec_charts = 0;
			$prev_display_type = '';
			$cnt = count($arr_blocks);
			foreach($arr_blocks as $c => $pb){
				if($pb['bench_row'] === 1){
					$has_benchmarks = true;
				}
				
				$display = $pb['display_type'];
				//load view for placeholder for block display
				if(isset($sort_by) && isset($sort_order)){
					$this->arr_sort_by = array_values(explode('|', $sort_by));
					$this->arr_sort_order = array_values(explode('|', $sort_order));
				}
				else {
					$tmp = $this->{$this->primary_model}->get_default_sort($pb['url_segment']);
					$this->arr_sort_by = $tmp['arr_sort_by'];
					$this->arr_sort_order = $tmp['arr_sort_order'];
					$sort_by = implode('|', $this->arr_sort_by);
					$sort_order = implode('|', $this->arr_sort_order);
				}
				if($arr_block_in == NULL || in_array($pb['url_segment'], $arr_block_in)){
					$this->{$this->primary_model}->populate_field_meta_arrays($pb['id']);
					//manage display details
					$next_pb = next($arr_blocks);
					$next_display_type = $next_pb['display_type'];
					if($display === 'chart' && $next_pb['display_type'] !== 'chart' && $prev_display_type !== 'chart'){
						$odd_even = 'chart-only';
					}
					else{
						if($consec_charts % 2 == 1) $odd_even = 'chart-even';
						elseif($consec_charts == ($cnt - 1)) $odd_even = 'chart-last-odd';
						else $odd_even = 'chart-odd';
					}
					//set up next iteration
					$prev_display_type = $pb['display_type'];
					if($display === 'table'){
						$consec_charts = 0;
					}
					if($display === 'chart'){
						$consec_charts++;
					}
					
					$arr_blk_data = array(
						'block_num' => $x, 
						'link_url' => site_url($this->section_path) . '/' . $this->page . '/' . $pb['url_segment'], 
						'form_id' => $this->report_form_id,
						'odd_even' => $odd_even,
						'block' => $pb['url_segment'],
						'sort_by' => urlencode($sort_by),
						'sort_order' => urlencode($sort_order),
					);
					$arr_view_blocks[] = $this->load->view($display, $arr_blk_data, TRUE);
					//add js line to populate the block after the page loads
					$tmp_container_div = $display == 'chart' ? 'graph-canvas' . $x : 'table-canvas' . $x;
					$tmp_js .= "updateBlock(\"$tmp_container_div\", \"" . $pb['url_segment'] . "\", \"$x\", \"null\", \"null\", \"$display\",\"false\");\n";//, \"" . $this->{$this->primary_model}->arr_blocks[$this->page]['display'][$display][$block]['description'] . "\", \"" . $bench_text . "\");\n";
					$tmp_js .= "if ($( '#datepickfrom' ).length > 0) $( '#datepickfrom' ).datepick({dateFormat: 'mm-dd-yyyy'});";
					$tmp_js .= "if ($( '#datepickto' ).length > 0) $( '#datepickto' ).datepick({dateFormat: 'mm-dd-yyyy'});";
					$tmp_block = $pb['url_segment'];
					$x++;
				}
			}
		}
		//set up page header
		$this->carabiner->css('chart.css');
		$this->carabiner->css('boxes.css');
		$this->carabiner->css('https://cdn.jsdelivr.net/qtip2/2.2.0/jquery.qtip.min.css', 'screen');
		$this->carabiner->css('popup.css');
		$this->carabiner->css('tabs.css');
		$this->carabiner->css('report.css');
		$this->carabiner->css('expandable.css');
		$this->carabiner->css('chart.css', 'print');
		$this->carabiner->css('report.css', 'print');
		$this->carabiner->css($this->section_path . '.css', 'screen');
		if($this->filters->displayFilters()){
			//$this->carabiner->css('filters.css', 'screen');
			$this->carabiner->css('agsource.datepick.css', 'screen');
			$this->carabiner->css('jquery.datetimeentry.css', 'screen');
		}
		else{
			$this->carabiner->css('hide_filters.css', 'screen');
		}
		if(!$has_benchmarks){
			$this->carabiner->css('hide_benchmarks.css', 'screen');
		}
		
		if(is_array($this->page_header_data)){
			$arr_sec_nav_data = array(
				'arr_pages' => $this->as_ion_auth->arr_user_sections,//$this->web_content_model->get_pages_by_criteria(array('section_id' => $this->section_id))->result_array(),
				'section_id' => $this->section_id,
				'section_path' => $this->section_path,
			);
			
			$this->page_header_data = array_merge($this->page_header_data,
				array(
					'title'=>$this->product_name . ' - ' . $this->config->item('site_title'),
					'description'=>$this->product_name . ' - ' . $this->config->item('site_title'),
					'message' => array($this->session->flashdata('message')) + $this->{$this->primary_model}->arr_messages,
					'section_nav' => $this->load->view('section_nav', $arr_sec_nav_data, TRUE),
					'page_heading' => $this->product_name . " for Herd " . $this->herd_code,
					'arr_head_line' => array(
						'<script type="text/javascript">',
						'	var page = "' . $this->page . '";',
						'	var base_url = "' . site_url($this->section_path) . '";',
						'	var site_url = "' . site_url() . '";',
						'	var herd_code = "' . $this->session->userdata('herd_code') . '";',
						'	var block = "' . $tmp_block	. '"',
						'</script>'
					),
					'arr_headjs_line'=>array(
						'{highcharts: "https://code.highcharts.com/4.0.4/highcharts.js"}',
						'{highcharts_more: "https://code.highcharts.com/4.0.4/highcharts-more.js"}',
						'{exporting: "https://code.highcharts.com/4.0.4/modules/exporting.js"}',
						'{popup: "' . $this->config->item("base_url_assets") . 'js/jquery/popup.min.js"}',
						'{chart_options: "' . $this->config->item("base_url_assets") . 'js/charts/chart_options.js"}',
						'{graph_helper: "' . $this->config->item("base_url_assets") . 'js/charts/graph_helper.js"}',
						'{report_helper: "' . $this->config->item("base_url_assets") . 'js/report_helper.js"}',
						'{table_sort: "' . $this->config->item("base_url_assets") . 'js/jquery/stupidtable.min.js"}',
						'{tooltip: "https://cdn.jsdelivr.net/qtip2/2.2.0/jquery.qtip.min.js"}',
						'{datepick: "' . $this->config->item("base_url_assets") . 'js/jquery/jquery.datepick.min.js"}'
					)
				)
			);
			//load the report-specific js file if it exists
			if(file_exists(PROJ_DIR . FS_SEP . 'js' . FS_SEP . str_replace('/', FS_SEP, $this->section_path) . '_helper.js')){
				$this->page_header_data['arr_headjs_line'][] = '{inv_helper: "' . $this->config->item("base_url_assets") . 'js/' . $this->section_path . '_helper.js"}';
			}
			$this->page_header_data['arr_headjs_line'][] = 'function(){' . $tmp_js . ';}';
		}
		unset($this->{$this->primary_model}->arr_messages); //clear message var once it is displayed
		$this->load->model('setting_model');
		$this->benchmarks_lib = new Benchmarks_lib($this->session->userdata('user_id'), $this->input->post('herd_code'), $this->herd_model->header_info($this->herd_code), $this->setting_model);
		$arr_benchmark_data = $this->benchmarks_lib->getFormData($this->session->userdata('benchmarks')); 
		$arr_nav_data = array(
			'section_path' => $this->section_path,
			'curr_page' => $this->page,
			'arr_pages' => $this->web_content_model->get_pages_by_criteria(array('section_id' => $this->section_id))->result_array(),
		);
		$this->page_footer_data = array();
		$report_nav_path = 'report_nav';
		if(file_exists(APPPATH . 'views' . FS_SEP . $this->section_path . FS_SEP . 'report_nav.php')){
			$report_nav_path =  $this->section_path . '/' . $report_nav_path;
		}
		if(count($arr_nav_data['arr_pages']) < 2) {
			$this->carabiner->css('hide_report_nav.css', 'screen');
		}
		$report_filter_path = 'filters';
		if(file_exists(APPPATH . 'views' . FS_SEP . $this->section_path . FS_SEP . 'filters.php')){
			$report_filter_path =  $this->section_path . '/filters' . $report_filter_path;
		}
		$data = array(
			'page_header' => $this->load->view('page_header', $this->page_header_data, TRUE),
			'herd_code' => $this->session->userdata('herd_code'),
			'herd_data' => $this->load->view('dhi/herd_info', $herd_data, TRUE),
			'page_footer' => $this->load->view('page_footer', $this->page_footer_data, TRUE),
			'blocks' => $arr_view_blocks,
			'print_all' => $this->print_all,
			'report_path' => $this->report_path
		);
		
		$arr_filter_data = array(
			//'arr_filters' => $this->filters->filter_list(),
			'arr_filters' => $this->filters->toArray(),
		);
		if(isset($arr_filter_data)){
			$collapse_data['content'] = $this->load->view($report_filter_path, $arr_filter_data, TRUE);
			$collapse_data['title'] = 'Set Filters';
			$collapse_data['id'] = 'filters';
			$data['filters'] = $this->load->view('collapsible', $collapse_data, TRUE);
		}

		$this->load->model('supplemental_model');
		$page_supp = Supplemental::getPageSupplemental($this->objPage['page_id'], $this->supplemental_model, site_url());
		$data['page_supplemental'] = $page_supp->getContent();
		if(isset($arr_benchmark_data)){
			$collapse_data['content'] = $this->load->view('set_benchmarks', $arr_benchmark_data, TRUE);
			$collapse_data['title'] = 'Set Benchmarks';
			$collapse_data['id'] = 'bench-div';
			$data['benchmarks'] = $this->load->view('collapsible', $collapse_data, TRUE);
		}
		if((is_array($arr_nav_data['arr_pages']) && count($arr_nav_data['arr_pages']) > 1) || 
				(isset($arr_nav_data['arr_links']) && is_array($arr_nav_data['arr_links']) && count($arr_nav_data['arr_links']) > 1)) {
			$data['report_nav'] = $this->load->view($report_nav_path, $arr_nav_data, TRUE);
		}
		
		$this->_record_access(90, $this->objPage['page_id'], 'web', $this->config->item('product_report_code'));
		$this->load->view('report', $data);
	}
	
	/*
	 * ajax_report: Called via AJAX to populate graphs
	 * @param string block: name of the block for which to retreive data
	 * @param string output: method of output (chart, table, etc)
	 * @param boolean/string file_format: return the value of function (TRUE), or echo it (FALSE).  Defaults to FALSE
	 * @param string cache_buster: text to make page appear as a different page so that new data is retrieved
	 */
	public function ajax_report($page, $block, $output, $sort_by = 'null', $sort_order = 'null', $file_format = 'web', $test_date = FALSE, $report_count=0, $json_filter_data = NULL, $first=FALSE, $cache_buster = NULL) {//, $herd_size_code = FALSE, $all_breeds_code = FALSE
		$first = ($first === 'true');
		$page = urldecode($page);
		$block = urldecode($block);
		$sort_by = urldecode($sort_by);
		$this->objPage = $this->{$this->primary_model}->arr_blocks[$page];
				
		if(isset($json_filter_data)){
			$arr_params = (array)json_decode(urldecode($json_filter_data));
			if(isset($arr_params['csrf_test_name']) && $arr_params['csrf_test_name'] != $this->security->get_csrf_hash()) die("I don't recognize your browser session, your session may have expired, or you may have cookies turned off.");
			unset($arr_params['csrf_test_name']);

			//prep data for filter library
			$this->load->model('filter_model');
			//load required libraries
			$this->filters = new Filters($this->filter_model);
			$primary_table = $this->{$this->primary_model}->get_primary_table_name();
			$recent_test_date = isset($primary_table) ? $this->{$this->primary_model}->get_recent_dates() : NULL;
			$this->load->helper('multid_array_helper');
			$this->filters->set_filters(
					$this->section_id,
					$page,
					array('herd_code' => $this->session->userdata('herd_code')) + $arr_params
			);
		}
		//supplemental data
		$this->load->model('supplemental_model');
		$block_supp = Supplemental::getBlockSupplemental($this->objPage['blocks'][$block]['id'], $this->supplemental_model, site_url());
		$this->supplemental = $block_supp->getContent();
		//end supplemental

		$this->load->helper('report_chart_helper');
		if($sort_by != 'null' && $sort_order != 'null') {
			$this->arr_sort_by = explode('|', $sort_by);
			$this->arr_sort_order = explode('|', $sort_order);
		}
		else {
			$tmp = $this->{$this->primary_model}->get_default_sort($block);
			$this->arr_sort_by = $tmp['arr_sort_by'];
			$this->arr_sort_order = $tmp['arr_sort_order'];
		}
		
		$this->page = $page;
		$this->json = NULL;
		
		$this->json['supplemental'] = $this->supplemental;
		$this->display = $output;
		//set parameters for given block
		
		$this->load_block($block, $report_count, $file_format);

		//common functionality
		if($file_format == 'csv'){
			if($first){
				$this->_record_access(90, $this->objPage['page_id'], 'csv', $this->config->item('product_report_code'));
			}
			return $this->report_data['report_data'];
		}
		elseif($file_format == 'pdf'){
			if($first){
				$this->_record_access(90, $this->objPage['page_id'], 'pdf', $this->config->item('product_report_code'));
			}
			if($this->display == 'html') return $this->html;
			else {
				return $this->report_data['report_data'];
			}
		}
		if($this->display == 'table'){
			$this->json['html'] = $this->html;
		}

		if($first){
			$this->_record_access(90, $this->objPage['page_id'], 'web', $this->config->item('product_report_code'));
		}
		$this->json['section_data'] = $this->get_section_data($block, $sort_by, $sort_order, $report_count);
		$return_val = prep_output($this->display, $this->json, $report_count, $file_format);
		if($return_val) {
			return $return_val;
		}
 	   	exit;
	}

	protected function get_section_data($block, $sort_by, $sort_order, $report_count){
		return array(
			'block' => $block,
			'sort_by' => $sort_by,
			'sort_order' => $sort_order,
			'graph_order' => $report_count
		);
	}
	
	protected function load_block($block, $report_count, $file_format){
		$arr_this_block = get_element_by_key($block, $this->{$this->primary_model}->arr_blocks);
		$this->max_rows = $arr_this_block['max_rows'];
		$this->cnt_row = $arr_this_block['cnt_row'];
		$this->sum_row = $arr_this_block['sum_row'];
		$this->avg_row = $arr_this_block['avg_row'];
		$this->bench_row = $arr_this_block['bench_row'];
		$this->pivot_db_field = isset($arr_this_block['pivot_db_field']) ? $arr_this_block['pivot_db_field'] : NULL;
		if($this->display == 'table' || $this->display == 'array'){
			$this->load_table($arr_this_block, $report_count);
		}
		elseif($this->display == 'chart'){
			$this->load_chart($arr_this_block, $report_count);
		}
	}
	
	protected function derive_series($arr_fields, $chart_type, $arr_categories, $cnt_arr_datapoints){
//as of 9/11/2014, in order to get labels correct, we need to change the header text in blocks_select_fields for the first {number of series'} fields
//in order for this function to work correctly, the DB view must have all fields in one row, or have series' as columns and categories as row keys.
		$return_val = array();
		$c = 0;
		$arr_chart_type = $this->{$this->primary_model}->get_chart_type_array();
		$arr_axis_index = $this->{$this->primary_model}->get_axis_index_array();

		//allow for normalized or non-normalized data
		if((int)($cnt_arr_datapoints / count($arr_fields)) === 1){
			$num_series = count($arr_fields) / count($arr_categories);
		}
		else{
			$num_series = count($arr_fields);
		}
		
		foreach($arr_fields as $k=>$f){
			//these 2 arrays need to have the same numeric index so that the yaxis# can be correctly assigned to series
			$return_val[$c]['name'] = $k;
			if(isset($this->{$this->primary_model}->arr_unit_of_measure[$f]) && !empty($this->{$this->primary_model}->arr_unit_of_measure[$f])){
				$return_val[$c]['um'] = $this->{$this->primary_model}->arr_unit_of_measure[$f]; 
			}
			if(isset($arr_axis_index[$f]) && !empty($arr_axis_index[$f])){
				$return_val[$c]['yAxis'] = $arr_axis_index[$f];
			}
			if(isset($arr_chart_type[$f]) && !empty($arr_chart_type[$f])){
				$return_val[$c]['type'] = $arr_chart_type[$f];
			}
			$c++;
			if($c >= $num_series){
				break;
			}
		}
		return $return_val;
	}
	
	protected function derive_field_array($arr_fields){
		$return_val = array();
		$c = 0;
			
		foreach($arr_fields as $k=>$f){
			$return_val[$c] = $f;
			$c++;
		}
		return $return_val;
	}
	
	protected function load_chart(&$arr_this_block, $report_count){
		$arr_axes = $this->{$this->primary_model}->get_chart_axes($arr_this_block['id']); 
		$x_axis_date_field = NULL;
		
		$this->json['name'] = $arr_this_block['name'];
		$this->json['description'] = $arr_this_block['description'];
		$this->json['chart_type'] = $arr_this_block['chart_type'];

		$this->json['arr_axes'] = $arr_axes;
		$tmp_x_axis = current($this->json['arr_axes']['x']);
		$tmp_categories = isset($tmp_x_axis['categories']) ? $tmp_x_axis['categories'] : null;
		
		$this->json['herd_code'] = $this->session->userdata('herd_code');

		$this->{$this->primary_model}->set_chart_fields($arr_this_block['id']);
		$arr_fields = $this->{$this->primary_model}->get_fields();
		if(!is_array($arr_fields) || empty($arr_fields)){
			return false;
		}
		$arr_fieldnames = $this->derive_field_array($arr_fields);

		if(is_array($arr_axes['x'])){
			foreach($arr_axes['x'] as $a){
				$tmp_cat = isset($a['categories']) && !empty($a['categories']) ? $a['categories'] : NULL;
				if($a['data_type'] === 'datetime' || $a['data_type'] === 'date'){
					$x_axis_date_field = $a['db_field_name'];
				}
				if(isset($a['db_field_name']) && !empty($a['db_field_name'])){
					$this->{$this->primary_model}->add_field(array('Date' => $a['db_field_name'])); 
				}
			}
		}
		$this->json['data'] = $this->{$this->primary_model}->get_graph_data($arr_fieldnames, $this->filters->criteriaKeyValue(), $this->max_rows, $x_axis_date_field, $arr_this_block['url_segment'], $tmp_categories);
		$this->json['series'] = $this->derive_series($arr_fields, $this->json['chart_type'], $tmp_categories, count($this->json['data'], COUNT_RECURSIVE));
		$this->json['filter_text'] = $this->filters->get_filter_text();
	}
		
	protected function load_table(&$arr_this_block, $report_count){
		$title = $arr_this_block['description'];
		$subtitle = $this->filters->get_filter_text();
		$this->{$this->primary_model}->populate_field_meta_arrays($arr_this_block['id']);
		$arr_field_list = $this->{$this->primary_model}->get_fieldlist_array();
		$results = $this->{$this->primary_model}->search($this->session->userdata('herd_code'), $arr_this_block['url_segment'], $this->filters->criteriaKeyValue(), $this->arr_sort_by, $this->arr_sort_order, $this->max_rows);
		if($this->bench_row){
		//if the data is pivoted, set the pivoted field as the row header, else use the first non-pstring column
			$row_head_field = NULL;
			if(!empty($this->pivot_db_field)){
				$row_head_field = $this->pivot_db_field;
			}
			else{
				foreach($arr_field_list as $fl){
					if($fl != 'pstring'){
						$row_head_field = $fl;
						break;
					}
				}
			}
			$this->load->model('benchmark_model');
			$this->load->model('db_table_model');
			$this->load->model('setting_model');
			$herd_info = $this->herd_model->header_info($this->herd_code);
			$this->benchmarks_lib = new Benchmarks_lib($this->session->userdata('user_id'), $this->input->post('herd_code'), $herd_info, $this->setting_model, $this->session->userdata('benchmarks'));
			$this->db_table = new db_table($this->{$this->primary_model}->get_primary_table_name(), $this->db_table_model);
			//$sess_benchmarks = $this->session->userdata('benchmarks');
			$arr_group_by = $this->{$this->primary_model}->get_group_by_fields($arr_this_block['id']);
//			$arr_group_by = array_filter($arr_group_by);
			$arr_bench_data = $this->benchmarks_lib->addBenchmarkRow(
					$this->db_table,
					$this->session->userdata('benchmarks'),
					$this->benchmark_model,
					$row_head_field,
					$arr_field_list,
					$this->{$this->primary_model}->get_group_by_fields($arr_this_block['id'])
				);
			if(count($arr_bench_data) > 1){
			/*
			 * @todo: if block_group_by isset (i.e., there are multiple rows of benchmarks), need to iterate through result set and place benchmark rows in correct spots.
			 * 	(i.e., when the value of the group_by field changes, insert the benchmark row that matches the previous value in the group by field)
			 */
			}
			else{
				$results[] = $arr_bench_data[0];
			}
		}
		if(!empty($this->pivot_db_field)){
			$results = $this->{$this->primary_model}->pivot($results, $this->pivot_db_field, 10, 10, $this->avg_row, $this->sum_row);
		}
		
		$tmp = array(
			'form_id' => $this->report_form_id,
			'report_path' => $this->report_path,
			'arr_sort_by' => $this->arr_sort_by,
			'arr_sort_order' => $this->arr_sort_order,
			'block' => $arr_this_block['url_segment'],
			'report_count' => $report_count
		);
		$tmp2 = $this->{$this->primary_model}->get_table_header_data();
		$table_header_data = array_merge($tmp, $tmp2);
		if(isset($this->benchmarks_lib)){
			$bench_text = $this->benchmarks_lib->get_bench_text($this->session->userdata('benchmarks'));
		}
		$this->report_data = array(
			'table_header' => $this->load->view('table_header', $table_header_data, TRUE),
			'num_columns' => $table_header_data['num_columns'],
			'table_id' => $arr_this_block['url_segment'],
			'fields' => $this->{$this->primary_model}->get_fieldlist_array(),
			'report_data' => $results,
			'table_heading' => $title,
			'table_sub_heading' => $subtitle,
			'arr_numeric_fields' => $this->{$this->primary_model}->get_numeric_fields(),
			'arr_decimal_places' => $this->{$this->primary_model}->get_decimal_places(),
			'arr_field_links' => $this->{$this->primary_model}->get_field_links(),
		);
		
		if(isset($this->supplemental) && !empty($this->supplemental)){
			$this->report_data['supplemental'] = $this->supplemental;
		}
		
		if(isset($bench_text)){
			$this->report_data['table_benchmark_text'] = $bench_text;
		}

		if(isset($this->report_data) && is_array($this->report_data)) {
			$this->html = $this->load->view('report_table.php', $this->report_data, TRUE);
		}
		else {
			$this->html = '<p class="message">No data found.</p>';
		}
		$this->display = 'table';
	}
	
	protected function _record_access($event_id, $page_id, $format, $product_code = null){
		if($this->session->userdata('user_id') === FALSE){
			return FALSE;
		}
		$herd_code = $this->session->userdata('herd_code');
		$herd_enroll_status_id = empty($herd_code) ? NULL : $this->session->userdata('herd_enroll_status_id');
		$recent_test = $this->session->userdata('recent_test_date');
		$recent_test = empty($recent_test) ? NULL : $recent_test;
		
		$filter_text = isset($this->filters) ? $this->filters->get_filter_text() : NULL;

		$this->load->model('access_log_model');
		$access_log = new Access_log($this->access_log_model);
		
		$access_log->write_entry(
			$this->as_ion_auth->is_admin(),
			$event_id,
			$herd_code,
			$recent_test,
			$herd_enroll_status_id,
			$this->session->userdata('user_id'),
			$this->session->userdata('active_group_id'),
			$product_code,
			$format,
			$page_id,
			$this->reports->sort_text_brief($this->arr_sort_by, $this->arr_sort_order),
			$filter_text
		);
	}
}
