<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* -----------------------------------------------------------------
 *	CLASS comments
 *  @file: report_parent.php
 *  @author: kmarshall
 *  @date: Nov 14, 2013
 *
 *  @description: First created by Chris Tranel based upon CI_controller.
 *
 * -----------------------------------------------------------------
 */

/* -----------------------------------------------------------------
 *  UPDATE comment
 *  @author: kmarshall
 *  @date: Nov 14, 2013
 *
 *  @description: Cleaned up file/class as a whole, modularized long sections of code into functions
 *  and finished filters coding.
 *  
 *  -----------------------------------------------------------------
 */


abstract class parent_report extends CI_Controller {
	protected $section_id;
	protected $report_form_id;
	protected $arr_page_filters = array(); //two dimensional of filters on the page, includes 3 keys, e.g.: array('db_field_name' => 'pstring', 'name' => 'PString', 'default_value' => array(0));
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
	
	function __construct(){
		
		// construct CI_Controller
		parent::__construct();

/* ----  BEGIN debugging code - for testing only --------DEBUG_SEARCH_TAG
 *  Remove before deploying
 *  @author: kmarshall
 *  @date: Nov 14, 2013
 *
 */
 		$this->load->library('session');
//		[data] = stuff you want to see in profiler session data	
/* 
 *  ----  END debugging code - for testing only------------------------------------
 */
		
 		$this->load->model('filter_model');
 		
		//Set up several class variables based upon uri - See http://ellislab.com/codeigniter%20/user-guide/libraries/uri.html

		$this->section_path = $this->router->fetch_class(); //this should match the name of this file (minus ".php".  Also used as base for css and js file names and model directory name
		//NOTE: fetch_class grabs the controller class regardless of rerouted URI

		if($this->uri->segment(1) != $this->section_path){
			//Likely called with all reports as the section is almost always superceded by the parent_section name
			$this->section_path = $this->uri->segment(1) . '/' . $this->section_path;
			//Example: Above results in section_path = summary_reports/herd_summary for herd_summary section
		} 
		$this->page = $this->router->fetch_method();
		$this->block = $this->uri->segment(3);  //grabs block name in most cases: hs_prod in summary_reports/herd_summary/hs_prod
		$this->report_path = $this->section_path . '/' . $this->page;
//DELETE		$this->primary_model = $this->block . '_model';
//DELETE		$this->report_form_id = 'report_criteria';//filter-form';

		//Get the herd_code from the session if it is 8 chars long, otherwise set to NULL
		$this->herd_code = strlen($this->session->userdata('herd_code')) == 8?$this->session->userdata('herd_code'):NULL;

		//Set 
		$this->page_header_data['user_sections'] = $this->as_ion_auth->arr_user_super_sections;
		if($this->authorize()) {  //If logged in, session not expired, etc
			$this->load->library('reports');
			$this->reports->herd_code = $this->herd_code;
//DELETE	$tmp_dir = explode('/', $this->section_path);
//DELETE	$tmp_dir = $tmp_dir[(count($tmp_dir) - 1)] . '_model';
	
			$class = $this->router->fetch_class();  			//get name of model for most animal reports
					
			//load model for parent_section/section/page reports (most summary reports)
			if(file_exists(APPPATH . 'models/' . $this->section_path . '/' . $this->primary_model . '.php')){
				$this->load->model($this->section_path . '/' . $this->primary_model, '', FALSE, $this->section_path);
			}
			//load model for parent/section reports (most animal reports)
			elseif(file_exists(APPPATH . 'models/' . $this->section_path . '/' . $class . '.php')){
				$this->load->model($this->section_path . '/' . $class, '', FALSE, $this->section_path);
				$this->primary_model = $class;
			}
			//else default to catch-all report model class
			else{
				$this->load->model('report_model', '', FALSE, $this->section_path);
				$this->primary_model = 'report_model';
			}
		}
		else {  //redirect to login if not logged in or session is expired
			if($this->session->flashdata('message')) $this->session->keep_flashdata('message');
			if($this->uri->segment(3) != 'ajax_report') $this->session->set_flashdata('redirect_url', $this->uri->uri_string());
			redirect(site_url('auth/login'));
		}
		
//DELETE  || $this->session->userdata('herd_code') == '35990571')  <- was part of if below... relic from gsg
		if($this->session->userdata('herd_code') == ''){  		// send user to herd select screen if no herd_code selected
			$this->session->keep_flashdata('redirect_url');
			redirect(site_url('change_herd/select'));			
		}

		
		$this->section_id = $this->{$this->primary_model}->get_section_id();
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
	       	if($this->uri->segment(3) == 'ajax_report'){
				echo "Your session has expired, please log in and try again.";
			}
			else return FALSE;
		}
		if(!$this->as_ion_auth->logged_in()) {
	       	if($this->uri->segment(3) == 'ajax_report'){
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

		return true;
		
/*DELETE
		$pass_unsubscribed_test = $this->as_ion_auth->has_permission("View Unsubscribed Herds") || $this->ion_auth_model->herd_is_subscribed($this->section_id, $this->herd_code);
		$pass_view_nonowned_test = $this->as_ion_auth->has_permission("View Non-owned Herds") || $this->ion_auth_model->user_owns_herd($this->herd_code);
		if(!$pass_view_nonowned_test) $pass_view_nonowned_test = $this->as_ion_auth->has_permission("View non-own w permission") && $this->ion_auth_model->consultant_has_access($this->session->userdata('user_id'), $this->herd_code, $this->section_id);
		if($pass_unsubscribed_test && $pass_view_nonowned_test) return TRUE;
		elseif(!$pass_unsubscribed_test && !$pass_view_nonowned_test) {
			if($this->uri->segment(3) == 'ajax_report') {
				echo 'Herd ' . $this->herd_code . ' is not subscribed to the ' . $this->product_name . ', nor do you have permission to view this report for herd ' . $this->herd_code . '.  Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' if you have questions or concerns.';
			}
			else {
				$this->session->set_flashdata('message', 'Herd ' . $this->herd_code . ' is not subscribed to the ' . $this->product_name . ', nor do you have permission to view this report for herd ' . $this->herd_code . '.  Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' if you have questions or concerns.');
 				if($this->session->flashdata('message')) $this->session->keep_flashdata('message');
				$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
				redirect(site_url());
      		}
			exit;
		}
		elseif(!$pass_unsubscribed_test) {
			if($this->uri->segment(3) == 'ajax_report') {
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
			if($this->uri->segment(3) == 'ajax_report') {
				echo 'You do not have permission to view the ' . $this->product_name . ' for herd ' . $this->herd_code . '.  Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' if you have questions or concerns.';
			}
			else {
				$this->session->set_flashdata('message', 'You do not have permission to view the ' . $this->product_name . ' for herd ' . $this->herd_code . '.  Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' if you have questions or concerns.');
 				if($this->session->flashdata('message')) $this->session->keep_flashdata('message');
				$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
				redirect(site_url());
			}
			exit;
		}
		return FALSE;

	END DELETE */
	}
	
	function index(){
		redirect(site_url($this->report_path));
	}

	function display($arr_block_in = NULL, $display_format = NULL, $sort_by = NULL, $sort_order = NULL, $json_filter_data = NULL){
		//Get Pstrings from DB
		$this->arr_pstring = $this->herd_model->get_pstring_array($this->session->userdata('herd_code'));
		$tmp = current($this->{$this->primary_model}->arr_pstring);
		//Set Pstring in session data
		$this->pstring = $this->session->userdata('pstring');

		//If no Pstring, set to default of 0
		if(!isset($this->pstring) || empty($this->pstring)){
			$this->pstring = isset($this->{$this->primary_model}->arr_pstring) && is_array($tmp)?$tmp['pstring'] . '':'0';
			$this->session->set_userdata('pstring', $this->pstring);
		}

		//Create block info as array in arr_block_in if not an array
		if(isset($arr_block_in) && !empty($arr_block_in) && !is_array($arr_block_in)) $arr_block_in = array($arr_block_in);

/* DELETE
 * 
		if($this->data_dump){
			$arr_tables = array();
			$arr_charts = array();
			if(isset($this->{$this->primary_model}->arr_blocks) && is_array($this->{$this->primary_model}->arr_blocks)){
				foreach($this->{$this->primary_model}->arr_blocks as $arr){
					if(isset($arr['display']['table'])) $arr_tables = array_merge($arr_tables, $arr['display']['table']);
					if(isset($arr['display']['chart'])) $arr_charts = array_merge($arr_charts, $arr['display']['chart']);
				}
			}
			$arr_blocks = array('table' => $arr_tables, 'chart' => $arr_charts);
		}
		else  */
		$arr_blocks = $this->{$this->primary_model}->arr_blocks[$this->page]['display'];
		//Check for valid herd_code
		if(empty($this->herd_code) || strlen($this->herd_code) != 8){
			$this->session->set_flashdata('message', 'Please select a valid herd.');
			redirect(site_url($this->report_path));
		}

//FILTERS

		$this->load->library('filters');

		//set arr_params to filter data from json		
		$arr_params = $this->filters->get_filter_array($json_filter_data);

		
		$this->_set_filters($this->page, $arr_params);
		//if params were passed to the function
		if(isset($arr_params) && is_array($arr_params)){
			foreach($arr_params as $k => $v){
				$this->arr_filter_criteria[$k] = $v;
			}
			$this->load->library('form_validation');
			foreach($this->arr_page_filters as $k=>$f){ //key is the db field name
			//if range, create 2 fields, to and from.  Default value stored in DB as pipe-delimited
				if($f['type'] == 'range' || $f['type'] == 'date range'){
					if(!isset($f['default_value'])) $f['default_value'] = '|';
					//list($this->arr_filter_criteria[$k . '_dbfrom'], $this->arr_filter_criteria[$k . '_dbto']) = explode('|', $f['default_value']);
				}
				//elseif(!isset($this->arr_filter_criteria[$k])) $this->arr_filter_criteria[$k] = $f['default_value'];
				$arr_filters_list[] = $f['db_field_name'];
				$this->form_validation->set_rules($f['db_field_name'], $f['name']);
			}
		}
		else{
			$this->load->library('form_validation');
			//validate form input for filters
			foreach($this->arr_page_filters as $k=>$f){ //key is the db field name
			//if range, create 2 fields, to and from.  Default value stored in DB as pipe-delimited
				if($f['type'] == 'range' || $f['type'] == 'date range'){
					if(!isset($f['default_value'])) $f['default_value'] = '|';
					list($this->arr_filter_criteria[$k . '_dbfrom'], $this->arr_filter_criteria[$k . '_dbto']) = explode('|', $f['default_value']);
				}
				elseif(!isset($this->arr_filter_criteria[$k])) $this->arr_filter_criteria[$k] = $f['default_value'];
				$arr_filters_list[] = $f['db_field_name'];
				$this->form_validation->set_rules($f['db_field_name'], $f['name']);
			}
			if ($this->form_validation->run() == TRUE) { //successful submission
				foreach($this->arr_page_filters as $k=>$f){ //key is the db field name
					if($k == 'page') $this->arr_filter_criteria['page'] = $this->arr_pages[$this->input->post('page', TRUE)]['name'];
					elseif($f['type'] == 'range' || $f['type'] == 'date range'){
						$this->arr_filter_criteria[$k . '_dbfrom'] = $this->input->post($k . '_dbfrom', TRUE);
						$this->arr_filter_criteria[$k . '_dbto'] = $this->input->post($k . '_dbto', TRUE);
					}
					else $this->arr_filter_criteria[$k] = $this->input->post($k, TRUE);
					if($f['type'] == 'select multiple' && !$this->arr_filter_criteria[$k] && $k != 'pstring') {
						$this->arr_filter_criteria[$k] = array();
					}
				}
			}
			else { //if no form has been successfully submitted, set to defaults
				foreach($this->arr_page_filters as $f){
					if($f['db_field_name'] == 'pstring' && (!isset($f['default_value']) || empty($f['default_value']))){
						$this->arr_filter_criteria['pstring'] = $this->pstring;//isset($this->{$this->primary_model}->arr_pstring) && is_array($tmp)?array($tmp['pstring']):array(0);
					}
					elseif($f['db_field_name'] == 'test_date' && (!isset($f['default_value']) || empty($f['default_value']))){
						$this->arr_filter_criteria['test_date'] = $this->{$this->primary_model}->get_recent_dates();
					}
					else $this->arr_filter_criteria[$f['db_field_name']] = $f['default_value'];
				}
			}
		}
		if(validation_errors()) $this->{$this->primary_model}->arr_messages[] = validation_errors();
		$arr_filter_text = $this->reports->filters_to_text($this->arr_filter_criteria, $this->{$this->primary_model}->arr_pstring);
		$log_filter_text = is_array($arr_filter_text) && !empty($arr_filter_text)?implode('; ', $arr_filter_text):'';
		$filter_data = array(
			'arr_filters'=>isset($arr_filters_list) && is_array($arr_filters_list)?$arr_filters_list:array(),
			'filter_selected'=>$this->arr_filter_criteria,
			'report_path'=>$this->report_path,
			'arr_pstring'=>$this->{$this->primary_model}->arr_pstring,
			//'arr_pages' => $this->access_log_model->get_pages_by_criteria(array('section_id' => $this->section_id))->result_array()
			//'page' => $this->arr_filter_criteria['page']
		);
//END FILTERS
		if ($display_format == 'csv'){
			$data = array();
			if(isset($arr_blocks['table']) && is_array($arr_blocks['table'])){
				foreach($arr_blocks['table'] as $k=>$pb){
					if(($arr_block_in !== NULL && in_array($k, $arr_block_in)) || $arr_block_in == NULL){
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
						$this->reports->sort_text($this->arr_sort_by, $this->arr_sort_order);//this function sets text, and could return it if needed
//						$this->{$this->primary_model}->populate_field_meta_arrays($pb['id']);
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
			//foreach($arr_block_in as $block_in){
			if(isset($arr_blocks['table']) && is_array($arr_blocks['table'])){
				foreach($arr_blocks['table'] as $k=>$pb){
					if(($arr_block_in !== NULL && in_array($k, $arr_block_in)) || $arr_block_in == NULL){
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
						$tmp_pdf_width = $this->{$this->primary_model}->get_pdf_widths(); // was $model in place of $this->primary_model
						$block[$i]['arr_pdf_widths'] = $tmp_pdf_width;
						$arr_header_data = $this->{$this->primary_model}->get_fields(); // was $model
						$block[$i]['header_structure'] = get_table_header_array($arr_header_data, $tmp_pdf_width);
						$block[$i]['title'] = $pb['description'];
						$i++;
					}
				}
			}
			//}
			$this->access_log_model->write_entry($this->{$this->primary_model}->arr_blocks[$this->page]['page_id'], 'pdf', $this->reports->sort_text_brief($this->arr_sort_by, $this->arr_sort_order), $this->log_filter_text);
			$this->reports->create_pdf($block, $this->product_name, NULL, $herd_data, 'P');
			exit;
		}

		// render page
		$this->carabiner->css('chart.css');
		$this->carabiner->css('report.css');
		$this->carabiner->css('chart.css', 'print');
		$this->carabiner->css('report.css', 'print');
		$this->carabiner->css($this->section_path . '.css', 'screen');
		$bool_has_summary = array_search(1, get_elements_by_key('is_summary', $arr_blocks)) === FALSE ? FALSE : TRUE;
		if($bool_has_summary) $this->carabiner->css('hide_filters.css', 'screen');
		else $this->carabiner->css('filters.css', 'screen');
		//$this->carabiner->css('tooltip.css', 'screen');
		//get_herd_data
		$herd_data = $this->herd_model->header_info($this->session->userdata('herd_code'));
		
		//set js lines and load views for each block to be displayed on page
		$tmp_js = '';
		$arr_chart = NULL;
		if(isset($arr_blocks) && !empty($arr_blocks)){
			foreach($arr_blocks as $display=>$arr_v){
				$x = 0;
				$cnt = count($arr_blocks[$display]);
				foreach($arr_v as $k=>$pb){ //($x = 0; $x < $cnt; $x++){
					//load view for placeholder for block display
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
					if($arr_block_in == NULL || in_array($k, $arr_block_in)){
						$this->{$this->primary_model}->populate_field_meta_arrays($pb['id']);
						if($cnt == 1) $odd_even = 'chart-only';
						elseif($x % 2 == 1) $odd_even = 'chart-even';
						elseif($x == ($cnt - 1)) $odd_even = 'chart-last-odd';
						else $odd_even = 'chart-odd';
						$arr_blk_data = array(
							$display . '_num' => $x, 
							'link_url' => site_url($this->section_path . '/' . $this->page . '/' . $k . '/' . $sort_by . '/' . $sort_order), 
							'form_id' => $this->report_form_id,
							'odd_even' => $odd_even,
							'block' => $k
						);
						$arr_chart[$display][] = $this->load->view($display, $arr_blk_data, TRUE);
						//add js line to populate the block after the page loads
						$tmp_container_div = $display == 'chart' ? 'graph-canvas' . $x : 'table-canvas' . $x;
						$tmp_js .= "updateBlock(\"$tmp_container_div\", \"$k\", \"$x\", \"null\", \"null\", \"$display\")\n";//, \"" . $this->{$this->primary_model}->arr_blocks[$this->page]['display'][$display][$block]['description'] . "\", \"" . $bench_text . "\");\n";
						$tmp_block = $k;
						$x++;
					}
				}
			}
		}
		//set up page header
		if(is_array($this->page_header_data)){
			$arr_sec_nav_data = array(
				'arr_pages' => $this->as_ion_auth->arr_user_sections,//$this->access_log_model->get_pages_by_criteria(array('section_id' => $this->section_id))->result_array(),
				'section_id' => $this->section_id,
//				'section_path' => $this->section_path
			);
			
			$this->page_header_data = array_merge($this->page_header_data,
				array(
					'title'=>$this->product_name . ' - ' . $this->config->item('site_title'),
					'description'=>$this->product_name . ' - ' . $this->config->item('site_title'),
					'messages' => $this->{$this->primary_model}->arr_messages,
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
						'{tooltips: "' . $this->config->item("base_url_assets") . 'js/jquery/jquery.qtip-1.0.0.min.js"}',
						//'{tips_helper: "' . $this->config->item("base_url_assets") . 'js/rc_tooltip.js"}',
						//'{highcharts: "' . $this->config->item("base_url_assets") . 'js/charts/highcharts.js"}',
						//'{exporting: "' . $this->config->item("base_url_assets") . 'js/charts/exporting.js"}',
						'{highcharts: "https://cdnjs.cloudflare.com/ajax/libs/highcharts/3.0.2/highcharts.js"}',
						'{exporting: "https://cdnjs.cloudflare.com/ajax/libs/highcharts/3.0.2/modules/exporting.js"}',
						'{graph_helper: "' . $this->config->item("base_url_assets") . 'js/charts/graph_helper.js"}',
						'{report_helper: "' . $this->config->item("base_url_assets") . 'js/report_helper.js"}',
//						'function(){' . $tmp_js . ';}'
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
			'arr_pstring' => $this->{$this->primary_model}->arr_pstring,
			'pstring_selected' => $this->arr_filter_criteria['pstring'][0],
			'section_path' => $this->section_path,
//			'benchmarks_id' => $this->arr_filter_criteria['benchmarks_id'],
			'curr_pstring' => $this->pstring,
			'curr_page' => $this->page,
			'arr_pages' => $this->access_log_model->get_pages_by_criteria(array('section_id' => $this->section_id))->result_array()
		);
		$this->page_footer_data = array();
		$report_nav_path = 'report_nav';
		if(file_exists(APPPATH . 'views/' . $this->section_path . '/report_nav.php')) $report_nav_path =  $this->section_path . '/' . $report_nav_path;
		$report_filter_path = 'filters';
		if(file_exists(APPPATH . 'views/' . $this->section_path . '/filters.php')) $report_filter_path =  $this->section_path . '/' . $report_filter_path;
		$data = array(
			'page_header' => $this->load->view('page_header', $this->page_header_data, TRUE),
			'herd_code' => $this->session->userdata('herd_code'),
			'herd_data' => $this->load->view('herd_info', $herd_data, TRUE),
			'page_footer' => $this->load->view('page_footer', $this->page_footer_data, TRUE),
			'charts' => $arr_chart,
			'print_all' => $this->print_all,
			'report_path' => $this->report_path
		);
		if(isset($filter_data)) $data['filters'] = $this->load->view($report_filter_path, $filter_data, TRUE);
		if((is_array($arr_nav_data['arr_pages']) && count($arr_nav_data['arr_pages']) > 1) || (is_array($arr_nav_data['arr_pstring']) && count($arr_nav_data['arr_pstring']) > 1)) $data['report_nav'] = $this->load->view($report_nav_path, $arr_nav_data, TRUE);
		
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
			$this->_set_filters($page, $arr_params);
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

		//$pstring = (empty($pstring) && $pstring !== 0)?'0':$pstring;
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
			//'test_date' => $test_date[0],
			'pstring' => $pstring,
			'sort_by' => $sort_by,
			'sort_order' => $sort_order,
			'graph_order' => $report_count
		);
	}
	
	protected function load_block($block, $report_count, $file_format){
		//$ajax_url = site_url($this->section_path . '/ajax_report/' . $this->page . '/' . $block . '/' . $this->pstring . '/' . $this->display . '/null/null/' . $file_format . '/null/' . $report_count);
		//table header setup
		//$this->arr_filter_criteria = array('herd_code' => $this->session->userdata('herd_code'));
		$arr_this_block = get_element_by_key($block, $this->{$this->primary_model}->arr_blocks);
		$this->max_rows = isset($arr_this_block['max_rows']) ? $arr_this_block['max_rows'] : NULL;
		$this->cnt_row = $arr_this_block['cnt_row'];
		$this->sum_row = $arr_this_block['sum_row'];
		$this->avg_row = $arr_this_block['avg_row'];
		$this->bench_row = $arr_this_block['bench_row'];
		$this->pivot_db_field = isset($arr_this_block['pivot_db_field']) ? $arr_this_block['pivot_db_field'] : NULL;
		if($this->display == 'table' || $this->display == 'array') $this->load_table($arr_this_block, $report_count);
		elseif($this->display == 'chart'){$this->load_chart($arr_this_block, $report_count);}
	}
	
	protected function load_chart(&$arr_this_block, $report_count){
		$um = '';//unit of measure
		$arr_axes = $this->{$this->primary_model}->get_chart_axes($arr_this_block['id']); // was $model in place of $this->primary_model
		$x_axis_date_field = NULL;
		$this->graph['config'] = get_chart_options($arr_this_block['chart_type']);
		$this->graph['config']['subtitle']['text'] = "Herd " . $this->session->userdata('herd_code');
		$this->graph['config']['title']['text'] = $arr_this_block['description'];
		$this->graph['config']['exporting']['filename'] = $arr_this_block['name'];
		$this->graph['config']['title']['text'] = $arr_this_block['description'];
		$this->{$this->primary_model}->set_chart_fields($arr_this_block['id']);
		$arr_fields = $this->{$this->primary_model}->get_fields();
		if(is_array($arr_fields) && !empty($arr_fields)){
			$c = 0;
			$arr_chart_type = $this->{$this->primary_model}->get_chart_type_array();
			$arr_axis_index = $this->{$this->primary_model}->get_axis_index_array();
			
			foreach($arr_fields as $k=>$f){
				//these 2 arrays need to have the same numeric index so that the yaxis# can be correctly assigned to series
				$this->graph['config']['series'][$c]['name'] = $k;
				$arr_fieldnames[$c] = $f;
				if(isset($this->{$this->primary_model}->arr_unit_of_measure[$f]) && !empty($this->{$this->primary_model}->arr_unit_of_measure[$f])) $um = $this->{$this->primary_model}->arr_unit_of_measure[$f]; // was $model in place of $this->primary_model
				if(isset($arr_axis_index[$f]) && !empty($arr_axis_index[$f])) $this->graph['config']['series'][$c]['yAxis'] = $arr_axis_index[$f];
				if(isset($arr_chart_type[$f]) && !empty($arr_chart_type[$f])) $this->graph['config']['series'][$c]['type'] = $arr_chart_type[$f];
				$c++;
			}
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
				if(isset($a['db_field_name']) && !empty($a['db_field_name'])) $this->{$this->primary_model}->add_field(array('Date' => $a['db_field_name'])); // was $model in place of $this->primary_model
			}
		}
		if(is_array($arr_axes['y'])){
			$cnt = 0;
			foreach($arr_axes['y'] as $a){
/*				switch($a['data_type']) {
					case 'datetime':
						$label_format = "function(){return '<b>' + this.series.name + ':</b><br> - ' + this.x + Highcharts.dateFormat('%B %e, %Y', this.y);}";
						$tooltip_format = "function(){return '<b>' + this.series.name + ':</b><br> - ' + this.x + Highcharts.dateFormat('%B %e, %Y', this.y);}";
						break;
					default: */
						$label_format = 'function(){return this.value}';
						if(isset($x_axis_date_field)) $tooltip_format = "function(){return '<b>' + this.series.name + ':</b><br>' + Highcharts.dateFormat('%B %e, %Y', this.x) + ' - ' + this.y + ' " . $um . "';}";
						else $tooltip_format = "function(){return '<b>' + this.series.name + ':</b>' + this.y + ' " . $um . "';}";
/*						break;
				} */
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
		$this->graph['data'] = $this->{$this->primary_model}->get_graph_data($arr_fieldnames, $this->session->userdata('herd_code'), $this->max_rows, $x_axis_date_field, $this->graph['config']['xAxis']['categories']); // was $model in place of $this->primary_model
/*				examples of output:
				$this->graph['config']['tooltip']['formatter'] = "function(){return '<b>' + this.series.name + ':</b><br>' + Highcharts.dateFormat('%B %e, %Y', this.x) + ' - ' + this.y + ' lbs';}";
				$this->graph['config']['tooltip']['formatter'] = "function(){return '<b>'+ Highcharts.dateFormat('%B %e, %Y', this.x) +'</b><br/>'+this.series.name +': '+ this.y +'<br/>'+'Combined Total: '+ this.point.stackTotal +'<br/>'+'Click on graph line to view Cow Report for that group';}";
				$this->graph['config']['tooltip']['formatter'] = "function(){return '<b>' + this.series.name + ':</b><br>' + Math.floor(this.x / 12) + ' yrs, ' + (this.x % 12) + ' mos' + ' - ' + '$' + this.y;}";
*//*
			case 'inventory':
				$graph['config'] = $this->get_snapshot_options();
				$graph['config']['title']['text'] = 'Inventory';
				$graph['config']['xAxis']['categories'] = array('Turnover Less<br>Dairy Sales %', 'All Cows Died %', 'Live Heifer Calves %');
				$graph['config']['exporting']['filename'] = 'RC-Inventory';
				$arr_fieldname_base = array('l0_left_herd_percent', 'l0_exit_died_percent', 'females_born_alive_percent');
				break;

				
				$this->graph['config']['plotOptions']['series']['point']['events']['click'] = 'function(){
					if(this.series.name.indexOf("1st") == 0) location.href = "../uhm_cow/display/chronic/null/null";
					if(this.series.name.indexOf("Peak") == 0) location.href = "../uhm_cow/display/new_infect/null/null";
					if(this.series.name.indexOf("ME Milk") == 0) location.href = "../uhm_cow/display/fresh_infect/null/null";
					if(this.series.name.indexOf("305M") == 0) location.href = "../uhm_cow/display/fresh_infect/null/null";
				}';
*/
				
	}
		
	protected function load_table(&$arr_this_block, $report_count){
		$title = $arr_this_block['description'];
		$subtitle = 'Herd ' + $this->session->userdata('herd_code');
		$this->{$this->primary_model}->populate_field_meta_arrays($arr_this_block['id']);// was $model in place of $this->primary_model
		$results = $this->{$this->primary_model}->search($this->session->userdata('herd_code'), $this->arr_filter_criteria, $this->arr_sort_by, $this->arr_sort_order, $this->max_rows);// was $model in place of $this->primary_model
		if(!empty($this->pivot_db_field)) $results = $this->{$this->primary_model}->pivot($results, $this->pivot_db_field, 10, 10, $this->avg_row, $this->sum_row, $this->bench_row);// was $model in place of $this->primary_model
		
		$tmp = array(
			'form_id' => $this->report_form_id,
			'report_path' => $this->report_path,
			'arr_sort_by' => $this->arr_sort_by,
			'arr_sort_order' => $this->arr_sort_order,
			'block' => $arr_this_block['url_segment'],
//			'ajax_url' => $ajax_url,
			'report_count' => $report_count,
//			'num_months_displayed' => $this->max_rows
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
			'fields' => $this->{$this->primary_model}->get_fieldlist_array(),// was $model in place of $this->primary_model
			'report_data' => $results,
			'table_heading' => $title,
			'table_sub_heading' => $subtitle,
	//		'table_benchmark_text' => $bench_text
		);
		if(isset($this->report_data) && is_array($this->report_data)) {
			$this->html = $this->load->view('report_table.php', $this->report_data, TRUE);
		}
		else {
			$this->html = '<p class="message">No data found.</p>';
		}
		$this->display = 'table';
	}
	
	protected function _set_filters($page_in, $arr_params){	//FILTERS
		$this->arr_page_filters = $this->filter_model->get_page_filters($this->section_id, $page_in);
		//always have filters for herd & pstring (and page?)
		if(array_key_exists('pstring', $this->arr_page_filters) === FALSE){ //all queries need to specify pstring
			$this->arr_page_filters['pstring'] = array('db_field_name' => 'pstring', 'name' => 'PString', 'type' => 'select multiple', 'default_value' => array(0));
			if(isset($arr_params['pstring']) === FALSE) $arr_params['pstring'] = array(0);
			//$this->arr_filter_criteria['pstring'] = array(0);
		}
		//herd code is not a part of the filter form (yet), so we hard-code it
		$this->arr_filter_criteria['herd_code'] = $this->session->userdata('herd_code');
		
		//iterate through page filter options
		foreach($this->arr_page_filters as $k=>$f){ //key is the db field name
			//if range, create 2 fields, to and from.  Default value stored in DB as pipe-delimited
			if($f['type'] == 'range' || $f['type'] == 'date range'){
				if(!isset($f['default_value'])) $f['default_value'] = '|';
				list($this->arr_filter_criteria[$k . '_dbfrom'], $this->arr_filter_criteria[$k . '_dbto']) = explode('|', $f['default_value']);
			}
			elseif(!isset($this->arr_filter_criteria[$k])) $this->arr_filter_criteria[$k] = $f['default_value'];
			$arr_filters_list[] = $f['db_field_name'];
		}
		$arr_params = array_filter($arr_params, function($val){
			return ($val !== FALSE && $val !== NULL && $val !== '');
		});
		if (is_array($arr_params) && !empty($arr_params)) {
			foreach($this->arr_page_filters as $k=>$f){ //key is the db field name
				
				if($k == 'page') $this->arr_filter_criteria['page'] = $this->arr_pages[$arr_params['page']]['name'];
				elseif($f['type'] == 'range' || $f['type'] == 'date range'){
					if(!isset($arr_params[$k . '_dbfrom']) || !isset($arr_params[$k . '_dbto'])) continue;
					$this->arr_filter_criteria[$k . '_dbfrom'] = $arr_params[$k . '_dbfrom'];
					$this->arr_filter_criteria[$k . '_dbto'] = $arr_params[$k . '_dbto'];
				}
				elseif($f['type'] == 'select multiple'){
					if(isset($arr_params[$k]) && is_array($arr_params[$k])){
						 foreach($arr_params[$k] as $k1=>$v1){
							$arr_params[$k][$k1] = explode('|', $v1);
						 } 
						$arr_params[$k] = array_flatten($arr_params[$k]);
						$this->arr_filter_criteria[$k] = $arr_params[$k];
					}
					if(!$this->arr_filter_criteria[$k] && $k != 'pstring') {
						$this->arr_filter_criteria[$k] = array();
					}
					elseif(isset($arr_params[$k])) $this->arr_filter_criteria[$k] = $arr_params[$k];
				}
				else {
					if(!isset($arr_params[$k])) continue;
					$this->arr_filter_criteria[$k] = $arr_params[$k];
				}
			}
		}
		else { //if no form has been successfully submitted, set to defaults
			foreach($this->arr_page_filters as $f){
				if($f['db_field_name'] == 'pstring' && (!isset($f['default_value']) || empty($f['default_value']))){
					//$tmp = current($this->{$this->primary_model}->arr_pstring);
					$this->arr_filter_criteria['pstring'] = $this->pstring;//isset($this->{$this->primary_model}->arr_pstring) && is_array($tmp)?array($tmp['pstring']):array(0);
				}
				elseif($f['db_field_name'] == 'test_date' && (!isset($f['default_value']) || empty($f['default_value']))){
					$this->arr_filter_criteria['test_date'] = $this->{$this->primary_model}->get_recent_dates();
				}
				else $this->arr_filter_criteria[$f['db_field_name']] = $f['default_value'];
			}
		}
		if(validation_errors()) $this->{$this->primary_model}->arr_messages[] = validation_errors();
		$arr_filter_text = $this->reports->filters_to_text($this->arr_filter_criteria, $this->{$this->primary_model}->arr_pstring);
		$this->log_filter_text = is_array($arr_filter_text) && !empty($arr_filter_text)?implode('; ', $arr_filter_text):'';
		$filter_data = array(
				'arr_filters'=>isset($arr_filters_list) && is_array($arr_filters_list)?$arr_filters_list:array(),
				'filter_selected'=>$this->arr_filter_criteria,
				'report_path'=>$this->report_path,
				'arr_pstring'=>$this->{$this->primary_model}->arr_pstring,
				//'arr_pages' => $this->access_log_model->get_pages_by_criteria(array('section_id' => $this->section_id))->result_array()
		//'page' => $this->arr_filter_criteria['page']
		);
		return $filter_data;
		//END FILTERS
	}
}

