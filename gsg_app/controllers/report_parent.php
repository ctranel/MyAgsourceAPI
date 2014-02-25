<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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
	protected $arr_filter_criteria = array(); //data for filtering results, the db_field_name key value of $this->arr_page_filters is a key for this array
	protected $log_filter_text;
	protected $arr_sort_by = array();
	protected $arr_sort_order = array();
	protected $pstring;
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
	protected $page;
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
	
	function __construct(){
		parent::__construct();
		$class = $this->router->fetch_class(); //this should match the name of this file (minus ".php".  Also used as base for css and js file names and model directory name
		$this->section_path = $class;
		if($this->uri->segment(1) != $this->section_path){
			$this->section_path = $this->uri->segment(1) . '/' . $this->section_path;
		} 
		$this->page = $this->router->fetch_method();
		$this->report_path = $this->section_path . '/' . $this->page;
		$this->primary_model = $this->page . '_model';
		$this->report_form_id = 'report_criteria';//filter-form';
		$this->herd_code = strlen($this->session->userdata('herd_code')) == 8?$this->session->userdata('herd_code'):NULL;
		$this->page_header_data['user_sections'] = $this->as_ion_auth->arr_user_super_sections;

		//load most specific model available.  Must load model before setting section_id
		if(file_exists(APPPATH . 'models' . FS_SEP . $this->section_path . FS_SEP . $this->primary_model . '_model.php')){
			$this->load->model($this->section_path . '/' . $this->primary_model, '', FALSE, $this->section_path);
		}
		elseif(file_exists(APPPATH . 'models' . FS_SEP . $this->section_path . FS_SEP . $class . '_model.php')){
			$this->load->model($this->section_path . '/' . $class . '_model', '', FALSE, $this->section_path);
			$this->primary_model = $class . '_model';
		}
		else{
			$this->load->model('report_model', '', FALSE, $this->section_path);
			$this->primary_model = 'report_model';
		}
		
		$this->section_id = $this->{$this->primary_model}->get_section_id();

		if($this->authorize()) {
			$this->load->library('reports');
			$this->reports->herd_code = $this->herd_code;
		}
//		else {  //redirect to login if not logged in or session is expired
//			if($this->session->flashdata('message')) $this->session->keep_flashdata('message');
//			if($this->uri->segment(3) != 'ajax_report') $this->session->set_flashdata('redirect_url', $this->uri->uri_string());
//			redirect(site_url('auth/login'));
//		}
		
		if($this->session->userdata('herd_code') == ''){ // || $this->session->userdata('herd_code') == '35990571'
			$this->session->keep_flashdata('redirect_url');
			redirect(site_url('change_herd/select'));			
		}
		/* Load the profile.php config file if it exists*/
		if (ENVIRONMENT == 'development') {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			} 
		}
	}

	protected function authorize(){
		if(!isset($this->as_ion_auth)){
	       	if($this->uri->segment(3) == 'ajax_report' && $this->session->userdata('herd_code') != $this->config->item('default_herd', 'ion_auth')){
				echo "Your session has expired, please log in and try again.";
			}
			else return FALSE;
		}
		if(!$this->as_ion_auth->logged_in()) {
	       	if($this->uri->segment(3) == 'ajax_report' && $this->session->userdata('herd_code') != $this->config->item('default_herd', 'ion_auth')){
				echo "Your session has expired, please log in and try again.";
			}
			else {
	       		$this->session->set_flashdata('message',  $this->session->flashdata('message') . " Please log in.");
				return FALSE;
			}
		}
		if(!isset($this->herd_code)){
	       	if($this->uri->segment(3) == 'ajax_report'){
				echo 'Please select a herd and try again.';
			}
			else {
      			$this->session->set_flashdata('message',  $this->session->flashdata('message') . "Please select a herd and try again.");
				if($this->session->flashdata('message')) $this->session->keep_flashdata('message');
				$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
				redirect(site_url('change_herd/select'));
			}
  			exit;
		}
		//if section scope is public, pass unsubscribed test
		//@todo: build display_hierarchy/report_organization, etc interface with get_scope function (with classes for super_sections, sections, etc)
		$pass_unsubscribed_test = true; //$this->as_ion_auth->get_scope('sections', $this->section_id) == 'pubic';
		$pass_unsubscribed_test = $this->as_ion_auth->has_permission("View Unsubscribed Herds") || $this->ion_auth_model->herd_is_subscribed($this->section_id, $this->herd_code);
		$pass_view_nonowned_test = $this->as_ion_auth->has_permission("View All Herds");
		if(!$pass_view_nonowned_test) $pass_view_nonowned_test = in_array($this->herd_code, $this->as_ion_auth->get_viewable_herd_codes($this->session->userdata('user_id'), $this->session->userdata('arr_regions')));//$this->as_ion_auth->has_permission("View Non-owned Herds") || $this->ion_auth_model->user_owns_herd($this->herd_code);
		if($pass_unsubscribed_test && $pass_view_nonowned_test) return TRUE;
		elseif(!$pass_unsubscribed_test && !$pass_view_nonowned_test) {
			if($this->uri->segment(3) == 'ajax_report') {
				echo 'Herd ' . $this->herd_code . ' is not subscribed to the ' . $this->product_name . ', nor do you have permission to view this report for herd ' . $this->herd_code . '.  Please contact ' . $this->config->item('cust_serv_company', 'ion_auth') . ' at ' . $this->config->item('cust_serv_email', 'ion_auth') . ' or ' . $this->config->item('cust_serv_phone', 'ion_auth') . ' if you have questions or concerns.';
			}
			else {
				$this->session->set_flashdata('message', 'Herd ' . $this->herd_code . ' is not subscribed to the ' . $this->product_name . ', nor do you have permission to view this report for herd ' . $this->herd_code . '.  Please contact ' . $this->config->item('cust_serv_company', 'ion_auth') . ' at ' . $this->config->item('cust_serv_email', 'ion_auth') . ' or ' . $this->config->item('cust_serv_phone', 'ion_auth') . ' if you have questions or concerns.');
 				if($this->session->flashdata('message')) $this->session->keep_flashdata('message');
				$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
				redirect(site_url('change_herd/select'));
      		}
			exit;
		}
		elseif(!$pass_unsubscribed_test) {
			if($this->uri->segment(3) == 'ajax_report') {
				echo 'Herd ' . $this->herd_code . ' is not subscribed to the ' . $this->product_name . '.  Please contact ' . $this->config->item('cust_serv_company', 'ion_auth') . ' at ' . $this->config->item('cust_serv_email', 'ion_auth') . ' or ' . $this->config->item('cust_serv_phone', 'ion_auth') . ' if you have questions or concerns.';
			}
			else {
				$this->session->set_flashdata('message', 'Herd ' . $this->herd_code . ' is not subscribed to the ' . $this->product_name . '.  Please contact ' . $this->config->item('cust_serv_company', 'ion_auth') . ' at ' . $this->config->item('cust_serv_email', 'ion_auth') . ' or ' . $this->config->item('cust_serv_phone', 'ion_auth') . ' if you have questions or concerns.');
 				if($this->session->flashdata('message')) $this->session->keep_flashdata('message');
				$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
				redirect(site_url());
			}
			exit;
		}
		elseif(!$pass_view_nonowned_test) {
			if($this->uri->segment(3) == 'ajax_report') {
				echo 'You do not have permission to view the ' . $this->product_name . ' for herd ' . $this->herd_code . '.  Please contact ' . $this->config->item('cust_serv_company', 'ion_auth') . ' at ' . $this->config->item('cust_serv_email', 'ion_auth') . ' or ' . $this->config->item('cust_serv_phone', 'ion_auth') . ' if you have questions or concerns.';
			}
			else {
				$this->session->set_flashdata('message', 'You do not have permission to view the ' . $this->product_name . ' for herd ' . $this->herd_code . '.  Please contact ' . $this->config->item('cust_serv_company', 'ion_auth') . ' at ' . $this->config->item('cust_serv_email', 'ion_auth') . ' or ' . $this->config->item('cust_serv_phone', 'ion_auth') . ' if you have questions or concerns.');
 				if($this->session->flashdata('message')) $this->session->keep_flashdata('message');
				$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
				redirect(site_url('change_herd/select'));
			}
			exit;
		}
		return FALSE;
	}
	
	function index(){
		redirect(site_url($this->report_path));
	}

	function display($arr_block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL, $json_filter_data = NULL){
		$this->pstring = $this->session->userdata('pstring');
		if(!isset($this->pstring) || empty($this->pstring)){
			$tmp = $this->{$this->primary_model}->get_current_pstring();
			$this->pstring = isset($tmp) && isset($tmp['pstring']) ? $tmp['pstring'] . '' : '0';
			$this->session->set_userdata('pstring', $this->pstring);
		}
		
		//Get Tstrings from DB
		$this->arr_tstring = $this->herd_model->get_tstring_array($this->session->userdata('herd_code'));
		//get current element from array
		$this->session->set_userdata('arr_tstring', $this->arr_tstring);
		//If no Tstring, set to default of 0
		if(!isset($this->tstring) || empty($this->tstring)){
			$this->tstring = array(0);
		}
		$this->session->set_userdata('tstring', $this->tstring);
				
		//Create block info as array in arr_block_in if not an array
		if(isset($arr_block_in) && !empty($arr_block_in) && !is_array($arr_block_in)) $arr_block_in = array($arr_block_in);

		$arr_blocks = $this->{$this->primary_model}->arr_blocks[$this->page]['blocks'];
		//Determine if any report blocks have is_summary flag - will determine if tstring needs to be loaded and filters shown
		$this->load->helper('multid_array_helper');
		$this->bool_is_summary = array_search(1, get_elements_by_key('is_summary', $arr_blocks)) === FALSE ? FALSE : TRUE;
		//Check for valid herd_code
		if(empty($this->herd_code) || strlen($this->herd_code) != 8){
			$this->session->set_flashdata('message', 'Please select a valid herd.');
			redirect(site_url($this->report_path));
		}

		//FILTERS
		include(APPPATH.'libraries/Filters.php');
		//set arr_params to filter data from json
		$arr_params = Filters::get_filter_array($json_filter_data);

		//prep data for filter library
		$filter_lib_data = array(
			'page'=>$this->page,
			'params'=>$arr_params,
			'section'=>$this->section_id,
			'criteria'=>$this->arr_filter_criteria,
			'primary_model'=>$this->{$this->primary_model},
			'log_filter_text'=>$this->log_filter_text,
			'report_path'=>$this->report_path
		);
		
		//load required libraries
		$this->load->library('filters',$filter_lib_data);
		$this->load->library('form_validation');

		$arr_filter_data = $this->filters->set_filters($this->bool_is_summary);
		
		$this->arr_filter_criteria = $arr_filter_data['filter_selected'];

		if ($display_format == 'csv'){
			$data = array();
			if(isset($arr_blocks) && is_array($arr_blocks)){
				foreach($arr_blocks as $pb){
					if($pb['display_type'] == 'table'){
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
						$tmp_data = $this->ajax_report(urlencode($this->page), urlencode($pb['url_segment']), $this->session->userdata('pstring'), 'array', urlencode($sort_by), $sort_order, 'csv', NULL);
						$data[] = array('test_date' => $pb['description']);
						$data = array_merge($data, $tmp_data);
					}
				}
			}
			if(is_array($data) && !empty($data)){
				$this->reports->create_csv($data);
				$this->access_log_model->write_entry($this->{$this->primary_model}->arr_blocks[$this->page]['page_id'], 'csv');
			}
			else {
				$this->{$this->primary_model}->arr_messages[] = 'There is no data to export into an Excel file.';
			}
			exit;
		}
		elseif ($display_format == 'pdf' && !is_null($arr_block_in)) {
			$this->load->helper('table_header');
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
						$block[$i]['data'] = $this->ajax_report(urlencode($this->page), urlencode($pb['url_segment']), $this->session->userdata('pstring'), 'array', urlencode($sort_by), $sort_order, 'pdf', NULL);
						$tmp_pdf_width = $this->{$this->primary_model}->get_pdf_widths(); 
						$block[$i]['arr_pdf_widths'] = $tmp_pdf_width;
						$arr_header_data = $this->{$this->primary_model}->get_fields(); // was $model
						$block[$i]['header_structure'] = get_table_header_array($arr_header_data, $tmp_pdf_width);
						$block[$i]['title'] = $pb['description'];
						$i++;
					}
				}
			}
			$this->access_log_model->write_entry($this->{$this->primary_model}->arr_blocks[$this->page]['page_id'], 'pdf', $this->reports->sort_text_brief($this->arr_sort_by, $this->arr_sort_order), $this->log_filter_text);
			$this->reports->create_pdf($block, $this->product_name, NULL, $herd_data, 'P');
			exit;
		}

		// render page
		$this->carabiner->css('chart.css');
		$this->carabiner->css('popup.css');
		$this->carabiner->css('tabs.css');
		$this->carabiner->css('report.css');
		$this->carabiner->css('chart.css', 'print');
		$this->carabiner->css('report.css', 'print');
		$this->carabiner->css($this->section_path . '.css', 'screen');
		if($this->bool_is_summary) $this->carabiner->css('hide_filters.css', 'screen');
		else $this->carabiner->css('filters.css', 'screen');

		//get_herd_data
		$herd_data = $this->herd_model->header_info($this->session->userdata('herd_code'));
		
		//set js lines and load views for each block to be displayed on page
		$tmp_js = '';
		$arr_view_blocks = NULL;
		if(isset($arr_blocks) && !empty($arr_blocks)){
			$x = 0;
			$cnt = count($arr_blocks);
			foreach($arr_blocks as $c => $pb){
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
					if($cnt == 1) $odd_even = 'chart-only';
					elseif($x % 2 == 1) $odd_even = 'chart-even';
					elseif($x == ($cnt - 1)) $odd_even = 'chart-last-odd';
					else $odd_even = 'chart-odd';
					if($display == 'table') $cnt = 0;

					$arr_blk_data = array(
						'block_num' => $x, 
						'link_url' => site_url($this->section_path . '/' . $this->page . '/' . $pb['url_segment'] . '/' . $sort_by . '/' . $sort_order), 
						'form_id' => $this->report_form_id,
						'odd_even' => $odd_even,
						'block' => $pb['url_segment'],
					);
					$arr_view_blocks[] = $this->load->view($display, $arr_blk_data, TRUE);
					//add js line to populate the block after the page loads
					$tmp_container_div = $display == 'chart' ? 'graph-canvas' . $x : 'table-canvas' . $x;
					$tmp_js .= "updateBlock(\"$tmp_container_div\", \"" . $pb['url_segment'] . "\", \"$x\", \"null\", \"null\", \"$display\")\n";//, \"" . $this->{$this->primary_model}->arr_blocks[$this->page]['display'][$display][$block]['description'] . "\", \"" . $bench_text . "\");\n";
					$tmp_block = $pb['url_segment'];
					$x++;
				}
			}
		}
		//set up page header
		if(is_array($this->page_header_data)){
			$arr_sec_nav_data = array(
				'arr_pages' => $this->as_ion_auth->arr_user_sections,//$this->access_log_model->get_pages_by_criteria(array('section_id' => $this->section_id))->result_array(),
				'section_id' => $this->section_id,
				'section_path' => $this->section_path,
			);
			
			$this->page_header_data = array_merge($this->page_header_data,
				array(
					'title'=>$this->product_name . ' - ' . $this->config->item('site_title'),
					'description'=>$this->product_name . ' - ' . $this->config->item('site_title'),
					'message' => $this->{$this->primary_model}->arr_messages,
					'section_nav' => $this->load->view('section_nav', $arr_sec_nav_data, TRUE),
					'page_heading' => $this->product_name . " for Herd " . $this->herd_code,
					'arr_head_line' => array(
						'<script type="text/javascript">',
						'	var page = "' . $this->page . '";',
						'	var base_url = "' . site_url($this->section_path) . '";',
						'	var herd_code = "' . $this->session->userdata('herd_code') . '";',
						'	var block = "' . $tmp_block	. '"',
						'</script>'
					),
					'arr_headjs_line'=>array(
						'{highcharts: "https://cdnjs.cloudflare.com/ajax/libs/highcharts/3.0.2/highcharts.js"}',
						'{highcharts_more: "https://cdnjs.cloudflare.com/ajax/libs/highcharts/3.0.2/highcharts-more.js"}',
						'{exporting: "https://cdnjs.cloudflare.com/ajax/libs/highcharts/3.0.2/modules/exporting.js"}',
						'{popup_helper: "' . $this->config->item("base_url_assets") . 'js/jquery/popup.min.js"}',
						'{graph_helper: "' . $this->config->item("base_url_assets") . 'js/charts/graph_helper.js"}',
						'{report_helper: "' . $this->config->item("base_url_assets") . 'js/report_helper.js"}',
						'{table_sort: "' . $this->config->item("base_url_assets") . 'js/jquery/stupidtable.min.js"}',
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
		$arr_nav_data = array(
			//if I do not add this empty array, the array in the view somehow populated (should only be populated if code in bool_is_summary block below is executed)
			'arr_pstring' => array(),
			'section_path' => $this->section_path,
//			'benchmarks_id' => $this->arr_filter_criteria['benchmarks_id'],
			'curr_page' => $this->page,
			'arr_pages' => $this->access_log_model->get_pages_by_criteria(array('section_id' => $this->section_id))->result_array()
		);
		if($this->bool_is_summary && (substr($this->page,0,3)!= 'mun')){
			$arr_nav_data['arr_pstring'] = $this->{$this->primary_model}->arr_pstring;
			$arr_nav_data['pstring_selected'] = $this->arr_filter_criteria['pstring'][0];
			$arr_nav_data['curr_pstring'] = $this->pstring;
		}
		$this->page_footer_data = array();
		$report_nav_path = 'report_nav';
		if(file_exists(APPPATH . 'views' . FS_SEP . $this->section_path . FS_SEP . 'report_nav.php')) $report_nav_path =  $this->section_path . '/' . $report_nav_path;
		if(count($arr_nav_data['arr_pages']) < 2) {
			$this->carabiner->css('hide_report_nav.css', 'screen');
		}
		$report_filter_path = 'filters';
		if(file_exists(APPPATH . 'views' . FS_SEP . $this->section_path . FS_SEP . 'filters.php')) $report_filter_path =  $this->section_path . '/' . $report_filter_path;
		$data = array(
			'page_header' => $this->load->view('page_header', $this->page_header_data, TRUE),
			'herd_code' => $this->session->userdata('herd_code'),
			'herd_data' => $this->load->view('herd_info', $herd_data, TRUE),
			'page_footer' => $this->load->view('page_footer', $this->page_footer_data, TRUE),
			'blocks' => $arr_view_blocks,
			'print_all' => $this->print_all,
			'report_path' => $this->report_path
		);
		if(isset($arr_filter_data)) $data['filters'] = $this->load->view($report_filter_path, $arr_filter_data, TRUE);
		if((is_array($arr_nav_data['arr_pages']) && count($arr_nav_data['arr_pages']) > 1) || (isset($arr_nav_data['arr_pstring']) && is_array($arr_nav_data['arr_pstring']) && count($arr_nav_data['arr_pstring']) > 1)){
			$data['report_nav'] = $this->load->view($report_nav_path, $arr_nav_data, TRUE);
		}
		
		//$this->access_log_model->write_entry($this->{$this->primary_model}->arr_blocks[$this->page]['page_id'], 'web');
		$this->load->view('report', $data);
	}
	
	/*
	 * ajax_report: Called via AJAX to populate graphs
	 * @param string block: name of the block for which to retreive data
	 * @param string output: method of output (chart, table, etc)
	 * @param boolean/string file_format: return the value of function (TRUE), or echo it (FALSE).  Defaults to FALSE
	 * @param string cache_buster: text to make page appear as a different page so that new data is retrieved
	 * @todo phasing out passing the pstring in the url, using the filter form instead (passed as $json_filter_data) --- the pstring parameter is no longer being used, but I have not removed it from the URLs that call ajax_report
	 */
	public function ajax_report($page, $block, $pstring, $output, $sort_by = 'null', $sort_order = 'null', $file_format = 'web', $test_date = FALSE, $report_count=0, $json_filter_data = NULL, $cache_buster = NULL) {//, $herd_size_code = FALSE, $all_breeds_code = FALSE
		$page = urldecode($page);
		$block = urldecode($block);
		$sort_by = urldecode($sort_by);
		
		if(isset($json_filter_data)){
			$arr_params = (array)json_decode(urldecode($json_filter_data));
			if(isset($arr_params['csrf_test_name']) && $arr_params['csrf_test_name'] != $this->security->get_csrf_hash()) die("I don't recognize your browser session, your session may have expired, or you may have cookies turned off.");
			unset($arr_params['csrf_test_name']);
			//ASSUMING ONLY ONE PSTRING WILL BE SELECTED FOR NOW
			$pstring = (!isset($arr_params['pstring']) || empty($arr_params['pstring'][0]))?'0':$arr_params['pstring'][0];
			$this->session->set_userdata('pstring', $pstring);
			$this->pstring = $pstring;

			//prep data for filter library
			$filter_lib_data = array(
					'page'=>$page,
					'params'=>$arr_params,
					'section'=>$this->section_id,
					'criteria'=>$this->arr_filter_criteria,
					'primary_model'=>$this->{$this->primary_model},
					'log_filter_text'=>$this->log_filter_text,
					'report_path'=>$this->report_path
			);
				
			//load required libraries
			$this->load->library('filters',$filter_lib_data);

			$arr_filter_data = $this->filters->set_filters($this->bool_is_summary);

			$this->arr_filter_criteria = $arr_filter_data['filter_selected'];
			
		}
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
		$this->graph = NULL;
		$this->display = $output;
		//set parameters for given block
		
		//can change functionality depending on block.  Suggest making chart changes in the JS file that corresponds with chart(one js file per section)
		switch ($block) {
			default:
				$this->load_block($block, $report_count, $file_format);
				break;
		}
		//common functionality
		if($file_format == 'csv') return $this->report_data['report_data'];
		elseif($file_format == 'pdf'){
			if($this->display == 'html') return $this->html;
			else {
				return $this->report_data['report_data'];
			}
		}
		if($this->display == 'table'){
			$this->graph['html'] = $this->html;
		}
		$this->graph['section_data'] = $this->get_section_data($block, $this->pstring, $sort_by, $sort_order, $report_count);
		$return_val = prep_output($this->display, $this->graph, $report_count, $file_format);
		if($return_val) {
			return $return_val;
		}
 	   	exit;
	}
	
	protected function get_section_data($block, $pstring, $sort_by, $sort_order, $report_count){
		return array(
			'block' => $block,
			'pstring' => $pstring,
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
		if($this->display == 'table' || $this->display == 'array') $this->load_table($arr_this_block, $report_count);
		elseif($this->display == 'chart'){$this->load_chart($arr_this_block, $report_count);}
	}
	
	protected function derive_series($arr_fields){
		$return_val = array();
		$c = 0;
		$arr_chart_type = $this->{$this->primary_model}->get_chart_type_array();
		$arr_axis_index = $this->{$this->primary_model}->get_axis_index_array();
			
		foreach($arr_fields as $k=>$f){
			//these 2 arrays need to have the same numeric index so that the yaxis# can be correctly assigned to series
			$return_val[$c]['name'] = $k;
			if(isset($this->{$this->primary_model}->arr_unit_of_measure[$f]) && !empty($this->{$this->primary_model}->arr_unit_of_measure[$f])) $um = $this->{$this->primary_model}->arr_unit_of_measure[$f]; 
			if(isset($arr_axis_index[$f]) && !empty($arr_axis_index[$f])) $return_val[$c]['yAxis'] = $arr_axis_index[$f];
			if(isset($arr_chart_type[$f]) && !empty($arr_chart_type[$f])) $return_val[$c]['type'] = $arr_chart_type[$f];
			$c++;
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
		$um = '';//unit of measure
		$arr_axes = $this->{$this->primary_model}->get_chart_axes($arr_this_block['id']); 
		$x_axis_date_field = NULL;
		$this->graph['config'] = get_chart_options($arr_this_block['chart_type']);
		$this->graph['config']['subtitle']['text'] = "Herd " . $this->session->userdata('herd_code');
		$this->graph['config']['title']['text'] = $arr_this_block['description'];
		$this->graph['config']['exporting']['filename'] = $arr_this_block['name'];
		$this->graph['config']['title']['text'] = $arr_this_block['description'];
		$this->{$this->primary_model}->set_chart_fields($arr_this_block['id']);
		$arr_fields = $this->{$this->primary_model}->get_fields();
		if(is_array($arr_fields) && !empty($arr_fields)){
			$this->graph['config']['series'] = $this->derive_series($arr_fields);
			$arr_fieldnames = $this->derive_field_array($arr_fields);
		}		
		if(is_array($arr_axes['x'])){
			foreach($arr_axes['x'] as $a){
				$tmp_cat = isset($a['categories']) && !empty($a['categories']) ? $a['categories'] : NULL;
				switch($a['data_type']) {
					case 'datetime':
						$label_format = "function(){return Highcharts.dateFormat('%b %e, %Y', this.value);}";
						$x_axis_date_field = $a['db_field_name'];
						break;
					default:
						$label_format = 'function(){return this.value}';
						break;
				}
				$tmp_array = array(
					'type' => $a['data_type'], 
					'categories' => $tmp_cat
				);
				if($arr_this_block['chart_type'] != 'bar'){
					$tmp_array['title'] = array('text' => $a['text']);
					if($a['data_type'] == 'datetime') $tmp_array['labels'] = array('formatter' => $label_format, 'rotation' => -35, 'align' => 'left', 'x' => -50, 'y' => 55);
					else $tmp_array['labels'] = array('formatter' => $label_format, 'rotation' => -35, 'y' => 25);
				}
				if(count($arr_axes['x']) > 1) $this->graph['config']['xAxis'][] = $tmp_array;
				else $this->graph['config']['xAxis'] = $tmp_array;
				unset($tmp_array);
				if(isset($a['db_field_name']) && !empty($a['db_field_name'])) $this->{$this->primary_model}->add_field(array('Date' => $a['db_field_name'])); 
			}
		}
		if(is_array($arr_axes['y'])){
			$cnt = 0;
			foreach($arr_axes['y'] as $a){
				$label_format = 'function(){return this.value}';
				if(isset($x_axis_date_field)){
					if($arr_this_block['chart_type'] == 'boxplot'){
						$tooltip_format = 'function(){
							var p = this.point;
							if(this.series.options.type == "boxplot" || typeof(this.series.options.type) == "undefined"){
								return "<b>" + Highcharts.dateFormat("%B %Y", this.x) +"</b><br/>" + this.series.name +"<br/>75th Percentile: "+ p.q1 + "<br/>50th Percentile: "+ p.median + "<br/>25th Percentile: "+ p.q3;
							}
							else {
								return false;
								//return "<b>"+ Highcharts.dateFormat("%B %Y", this.x) +"</b><br/>"+this.series.name +": "+ this.y;
							}
						}';
					}
					else{
						$tooltip_format = "function(){return '<b>' + this.series.name + ':</b><br>' + Highcharts.dateFormat('%B %e, %Y', this.x) + ' - ' + this.y + ' " . $um . "';}";
					}
				}
				else {
					$tooltip_format = "function(){return '<b>' + this.series.name + ':</b>' + this.y + ' " . $um . "';}";
				}
				if($arr_this_block['chart_type'] != 'bar') $tmp_array['opposite'] = $a['opposite'];
				if(isset($a['text'])) $tmp_array['title'] = array('text' => $a['text'], 'style'=>array('color'=>''));
				if(isset($label_format) && $arr_this_block['chart_type'] != 'bar') $tmp_array['labels'] = array('formatter' => $label_format);
				if(isset($a['data_type'])) $tmp_array['type'] = $a['data_type'];
				if(isset($a['max'])) $tmp_array['max'] = $a['max'];
				if(isset($a['min'])) $tmp_array['min'] = $a['min'];
				//check for opposite yAxes
				if(isset($a['db_field_name']) && !empty($a['db_field_name']) && $a['opposite']){
					$tmp_key = array_search($a['db_field_name'], $arr_fieldnames);
					$this->graph['config']['series'][$tmp_key]['yAxis'] = 1;
				}

				if(count($arr_axes['y']) > 1) {
					if(isset($this->graph['config']['yAxis'][$cnt])) $this->graph['config']['yAxis'][$cnt] = array_merge($this->graph['config']['yAxis'][$cnt], $tmp_array);
					else $this->graph['config']['yAxis'][$cnt] = $tmp_array;
				}
				else {
					if(isset($this->graph['config']['yAxis'])) $this->graph['config']['yAxis'] = array_merge($this->graph['config']['yAxis'][$cnt], $tmp_array);
					else $this->graph['config']['yAxis'] = $tmp_array;
				}
			}
			$this->graph['config']['tooltip']['formatter'] = $tooltip_format;
		}
		$this->graph['data'] = $this->{$this->primary_model}->get_graph_data($arr_fieldnames, $this->session->userdata('herd_code'), $this->max_rows, $x_axis_date_field, $arr_this_block['url_segment'], $this->graph['config']['xAxis']['categories']);
	}
		
	protected function load_table(&$arr_this_block, $report_count){
		$title = $arr_this_block['description'];
		$subtitle = 'Herd ' + $this->session->userdata('herd_code');
		$this->{$this->primary_model}->populate_field_meta_arrays($arr_this_block['id']);// was $model in place of $this->primary_model
		$results = $this->{$this->primary_model}->search($this->session->userdata('herd_code'), $arr_this_block['url_segment'], $this->arr_filter_criteria, $this->arr_sort_by, $this->arr_sort_order, $this->max_rows);
		if(!empty($this->pivot_db_field)) $results = $this->{$this->primary_model}->pivot($results, $this->pivot_db_field, 10, 10, $this->avg_row, $this->sum_row, $this->bench_row);
		
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

/*		$sess_benchmarks = $this->session->userdata('benchmarks');
		$criteria_options = $this->benchmarks_lib->get_criteria_options();
		$bench_text = 'Benchmark herds determined by ' . $criteria_options[$sess_benchmarks['criteria']];
		if(isset($sess_benchmarks['arr_herd_size'])) $bench_text .= ' for Herds between ' . $sess_benchmarks['arr_herd_size'][0] . ' and ' . $sess_benchmarks['arr_herd_size'][1] . ' animals.';
		if(isset($sess_benchmarks['arr_states'])) $bench_text .= ' for Herds in ' . implode(',', $sess_benchmarks['arr_states']) . '.';
*/
	//	$bench_text = $this->benchmarks_lib->get_bench_text();
		$this->report_data = array(
			'table_header' => $this->load->view('table_header', $table_header_data, TRUE),
			'table_id' => $arr_this_block['url_segment'],
			'fields' => $this->{$this->primary_model}->get_fieldlist_array(),
			'report_data' => $results,
			'table_heading' => $title,
			'table_sub_heading' => $subtitle,
			'arr_numeric_fields' => $this->{$this->primary_model}->get_numeric_fields(),
			'arr_decimal_places' => $this->{$this->primary_model}->get_decimal_places(),
			'arr_field_links' => $this->{$this->primary_model}->get_field_links(),
		);
		if(isset($this->report_data) && is_array($this->report_data)) {
			$this->html = $this->load->view('report_table.php', $this->report_data, TRUE);
		}
		else {
			$this->html = '<p class="message">No data found.</p>';
		}
		$this->display = 'table';
	}
}

