<?php
//namespace myagsource;
require_once(APPPATH . 'libraries/filters/Filters.php');
require_once(APPPATH . 'libraries/Benchmarks/Benchmarks.php');
require_once(APPPATH . 'libraries/access_log.php');
require_once(APPPATH . 'libraries/Supplemental/Content/SupplementalFactory.php');
require_once(APPPATH . 'libraries/dhi/HerdAccess.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');
require_once(APPPATH . 'libraries/Site/WebContent/Sections.php');
require_once(APPPATH . 'libraries/Site/WebContent/Pages.php');
require_once(APPPATH . 'libraries/Site/WebContent/Blocks.php');
require_once(APPPATH . 'libraries/Report/Content/Csv.php');
require_once(APPPATH . 'libraries/Report/Content/Pdf.php');
require_once(APPPATH . 'libraries/Notifications/Notifications.php');

use \myagsource\Benchmarks\Benchmarks;
use \myagsource\Access_log;
use \myagsource\report_filters\Filters;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\dhi\HerdAccess;
use \myagsource\dhi\Herd;
use \myagsource\Site\WebContent\Sections;
use \myagsource\Site\WebContent\Pages;
use \myagsource\Site\WebContent\Blocks;
use \myagsource\Site\WebContent\Block as PageBlock;
use \myagsource\Report\Content\Csv;
use \myagsource\Report\Content\Pdf;
use \myagsource\Report\iBlock;
use \myagsource\notices\Notifications;

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

abstract class report_parent extends CI_Controller {
	/**
	 * herd_access
	 * @var HerdAccess
	 **/
	protected $herd_access;
	
	/**
	 * section
	 * @var Section
	 **/
	protected $section;
	
	/**
	 * section_path
	 * @var string
	 **/
	protected $section_path;
	
	/**
	 * pages
	 * 
	 * page repository
	 * @var Pages
	 **/
	protected $pages;
	
	/**
	 * page
	 * @var Page
	 **/
	protected $page;
	
	/**
	 * blocks
	 * 
	 * Block repository
	 * @var blocks
	 **/
	protected $blocks;
	
	/**
	 * herd
	 * 
	 * Herd object
	 * @var Herd
	 **/
	protected $herd;

	protected $report_form_id;
	protected $arr_sort_by = array();
	protected $arr_sort_order = array();
	protected $product_name;
	protected $report_path;
	//protected $primary_model_name;
	protected $page_header_data;
	protected $filters; //filters object
	protected $print_all = FALSE;
	protected $bool_is_summary;
	protected $supplemental;
	
	protected $notifications;
	protected $notices;

	/**
	 * Benchmark settings
	 * 
	 * @var Session_settings object
	protected $bench_setting;
	 */
	
	function __construct(){
		parent::__construct();
		$this->load->model('herd_model');
		$this->load->model('web_content/section_model');
		$this->load->model('web_content/page_model', null, false, $this->session->userdata('user_id'));
		$this->load->model('web_content/block_model');
		$this->load->model('notice_model');
		$this->blocks = new Blocks($this->block_model);
		$this->pages = new Pages($this->page_model, $this->blocks);
		$sections = new Sections($this->section_model, $this->pages);
		$this->herd_access = new HerdAccess($this->herd_model);
		$this->herd = new Herd($this->herd_model, $this->session->userdata('herd_code'));
		
		$class_dir = $this->router->fetch_directory(); //this should match the name of this file (minus ".php".  Also used as base for css and js file names and model directory name
		$class = $this->router->fetch_class();
		$method = $this->router->fetch_method();
		$this->section_path = $class_dir . $class;

		//load sections
		$this->section = $sections->getByPath($this->section_path . '/');
		$this->session->set_userdata('section_id', $this->section->id());
		$sections->loadChildren($this->section, $this->pages, $this->session->userdata('user_id'), $this->herd, $this->ion_auth_model->getTaskPermissions());
		$path = uri_string();

		if(strpos($path, $method) === false){
			$method = $this->section->defaultPagePath();
		}

		if(!$this->authorize($method)) {
			if($this->session->flashdata('message')){
				$this->session->keep_flashdata('message');
			}
			if(strpos($method, 'ajax') === false){
				$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
			}
			redirect(site_url('auth/login'));
		}

		if($this->herd->herdCode() == ''){
			$this->session->keep_flashdata('redirect_url');
			redirect(site_url('dhi/change_herd/select'));			
		}

		$arr_path = explode('/',$path);
		$page_name = $method;
		$block_name = '';
		$this->page = $this->pages->getByPath($page_name, $this->section->id());
		$this->report_path = $this->section_path . '/' . $this->page->path();
		//$this->primary_model_name = $this->page->path() . '_model';
		$this->report_form_id = 'report_criteria';//filter-form';
		$this->page_header_data['top_sections'] = $this->as_ion_auth->top_sections;
		$this->page_header_data['user_sections'] = $this->as_ion_auth->user_sections;
		$this->page_header_data['num_herds'] = $this->herd_access->getNumAccessibleHerds($this->session->userdata('user_id'), $this->as_ion_auth->arr_task_permissions(), $this->session->userdata('arr_regions'));
		
		//NOTICES
		//Get any system notices
		$this->notifications = new Notifications($this->notice_model);
	    $this->notifications->populateNotices();
	    $this->notices = $this->notifications->getNoticesTexts();
		
		/*
		 * 
		load most specific model available.  Must load model before setting section
		//Load the most specific model that exists
		if(file_exists(APPPATH . 'models/' . $this->section_path . $block_name . '_model.php')){
			$this->primary_model_name = $block_name. '_model';
			$this->load->model($this->section_path . $this->primary_model_name, '', FALSE, $this->section_path);
		}
		elseif(file_exists(APPPATH . 'models/' . $this->section_path . $class . '_model.php')){
			$this->primary_model_name = $class . '_model';
			$this->load->model($this->section_path . $this->primary_model_name, '', FALSE, $this->section_path);
		}
		else{
			$this->primary_model_name = 'report_model';
			$this->load->model('report_model', '', FALSE, $this->section_path);
		}
		 */
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
	       	return FALSE;
		}
		if(!$this->as_ion_auth->logged_in() && $this->herd->herdCode() != $this->config->item('default_herd')) {
	       	$this->session->set_flashdata('message', "Please log in.");
			return FALSE;
		}
		if(!$this->herd->herdCode()){
			$this->session->set_flashdata('message',  $this->session->flashdata('message') . "Please select a herd and try again.");
			if($this->session->flashdata('message')) $this->session->keep_flashdata('message');
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
			redirect(site_url('dhi/change_herd/select'));
  			exit;
		}
		//if section scope is public, pass unsubscribed test
		//@todo: build display_hierarchy/report_organization, etc interface with get_scope function (with classes for super_sections, sections, etc)
		$pass_unsubscribed_test = true; //$this->as_ion_auth->get_scope('sections', $this->section->id()) == 'pubic';
		//@todo: redo access tests
//		$pass_unsubscribed_test = $this->as_ion_auth->has_permission("View All Content") || $this->web_content_model->herd_is_subscribed($this->section->id(), $this->herd->herdCode());
		$pass_view_nonowned_test = $this->as_ion_auth->has_permission("View All Herds");
		if(!$pass_view_nonowned_test) $pass_view_nonowned_test = in_array($this->herd->herdCode(), $this->herd_access->getAccessibleHerdCodes($this->session->userdata('user_id'), $this->as_ion_auth->arr_task_permissions(), $this->session->userdata('arr_regions')));
		if($pass_unsubscribed_test && $pass_view_nonowned_test) return TRUE;
		elseif(!$pass_unsubscribed_test && !$pass_view_nonowned_test) {
			$this->session->set_flashdata('message', 'Herd ' . $this->herd->herdCode() . ' is not subscribed to the ' . $this->product_name . ', nor do you have permission to view this report for herd ' . $this->herd->herdCode() . '.  Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' if you have questions or concerns.');
 			if($this->session->flashdata('message')) $this->session->keep_flashdata('message');
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
			redirect(site_url('dhi/change_herd/select'));
			exit;
		}
		elseif(!$pass_unsubscribed_test) {
			$this->session->set_flashdata('message', 'Herd ' . $this->herd->herdCode() . ' is not subscribed to the ' . $this->product_name . '.  Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' if you have questions or concerns.');
 			if($this->session->flashdata('message')) $this->session->keep_flashdata('message');
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
			redirect(site_url());
			exit;
		}
		elseif(!$pass_view_nonowned_test) {
			$this->session->set_flashdata('message', 'You do not have permission to view the ' . $this->product_name . ' for herd ' . $this->herd->herdCode() . '.  Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' if you have questions or concerns.');
 			if($this->session->flashdata('message')) $this->session->keep_flashdata('message');
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
			redirect(site_url('dhi/change_herd/select'));
			exit;
		}
		return FALSE;
	}
	
	function index(){
		redirect(site_url($this->report_path));
	}

	function display($arr_block_in, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
		//Check for valid herd_code
		if(!$this->herd){
			$this->session->set_flashdata('message', 'Please select a valid herd.');
			redirect(site_url($this->report_path));
		}
		
		$arr_blocks = $this->blocks->getByPage($this->page->id());
		$this->page->loadChildren($arr_blocks);
				
		//FILTERS
		//Determine if any report blocks have is_summary flag - will determine if pstring needs to be loaded and filters shown
		//@todo make pstring 0 work on both cow and summary reports simultaneously
		$this->load->model('filter_model');
		$this->filters = new Filters($this->filter_model);
		//only use default criteria on initial page loads, when filter form is submitted, it reloads each individual block
		$this->filters->setCriteria($this->section->id(), $this->page->path(), ['herd_code' =>	$this->herd->herdCode()]); //filter form submissions never trigger a new page load (i.e., this function is never fired by a form submission)
		//END FILTERS
/*
		if ($display_format == 'csv'){
			$csv = new Csv();
			$data = array();
			if(isset($arr_blocks) && is_array($arr_blocks)){
				foreach($arr_blocks as $pb){
					if($pb['display_type'] !== 'table'){
						continue;
					}
					if(($arr_block_in !== NULL && in_array($pb->path(), $arr_block_in)) || $arr_block_in == NULL){
						if(isset($sort_by) && isset($sort_order)){
							$this->arr_sort_by = array_values(explode('|', $sort_by));
							$this->arr_sort_order = array_values(explode('|', $sort_order));
						}
						else {
							$tmp = $pb->get_default_sort($pb->path());
							$this->arr_sort_by = $tmp['arr_sort_by'];
							$this->arr_sort_order = $tmp['arr_sort_order'];
							$sort_by = implode('|', $this->arr_sort_by);
							$sort_order = implode('|', $this->arr_sort_order);
						}
						$this->reports->sort_text($this->arr_sort_by, $this->arr_sort_order);//this function sets text, and could return it if needed
						$tmp_data = $this->ajax_report(urlencode($this->page->path()), urlencode($pb->path()), urlencode($sort_by), $sort_order, 'csv', NULL);
						$data[] = array('test_date' => $pb['description']);
						$data = array_merge($data, $tmp_data);
					}
				}
			}
			if(is_array($data) && !empty($data)){
				$this->config->set_item('compress_output', FALSE);
				
				$filename = $this->herd->herdCode() . '-' . date('mdy-His') . '.csv';
				header('Content-type: application/excel');
				header('Content-disposition: attachment; filename=' . $filename);
				$csv->create_csv($data);
				$this->_record_access(90, 'csv', $this->config->item('product_report_code'));
			}
			else {
				$this->{$this->primary_model_name}->arr_messages[] = 'There is no data to export into an Excel file.';
			}
			exit;
		}
		elseif ($display_format == 'pdf' && !is_null($arr_block_in)) {
			$ci_pdf = new Ci_pdf();
			$pdf = new Pdf($ci_pdf);
			//@todo: parameters
			$table_header = new TableHeader();
			$data = array();
			$herd_data = $this->herd_model->header_info($this->herd->herdCode());
			$i = 0;

			if(isset($arr_blocks) && is_array($arr_blocks)){
				foreach($arr_blocks as $pb){
					if($pb['display_type'] == 'table'){
						continue;
					}
					if(($arr_block_in !== NULL && in_array($pb->path(), $arr_block_in)) || $arr_block_in == NULL){
					//SORT
						if(isset($sort_by) && isset($sort_order)){
							$this->arr_sort_by = array_values(explode('|', $sort_by));
							$this->arr_sort_order = array_values(explode('|', $sort_order));
						}
						else {
							$tmp = $pb->get_default_sort($pb->path());
							$this->arr_sort_by = $tmp['arr_sort_by'];
							$this->arr_sort_order = $tmp['arr_sort_order'];
							$sort_by = implode('|', $this->arr_sort_by);
							$sort_order = implode('|', $this->arr_sort_order);
						}

						$this->{$this->primary_model_name}->populate_field_meta_arrays($pb['id']);
						$block[$i]['data'] = $this->ajax_report(urlencode($this->page->path()), urlencode($pb->path()), urlencode($sort_by), $sort_order, 'pdf', NULL);
						$tmp_pdf_width = $this->{$this->primary_model_name}->get_pdf_widths(); 
						$block[$i]['arr_pdf_widths'] = $tmp_pdf_width;
						$arr_header_data = $this->{$this->primary_model_name}->get_fields(); // was $model
						$block[$i]['header_structure'] = $this->table_header->get_table_header_array($arr_header_data, $tmp_pdf_width);
						$block[$i]['title'] = $pb['description'];
						$i++;
					}
				}
			}
			$this->_record_access(90, 'pdf', $this->config->item('product_report_code'));
			$this->reports->create_pdf($block, $this->product_name, NULL, $herd_data, 'P');
			exit;
		}
*/
		// render page
		//get_herd_data
		$herd_data = $this->herd_model->header_info($this->herd->herdCode());

		//set js lines and load views for each block to be displayed on page
		$tmp_js = '';
		$arr_view_blocks = NULL;
		if(isset($arr_blocks) && !empty($arr_blocks)){
			$x = 0;
			$consec_charts = 0;
			$cnt = $arr_blocks->count();
			$curr = null;
			$next = null;

			$arr_blocks->rewind();
			$curr = $arr_blocks->current();
			$arr_blocks->next();
			if($arr_blocks->valid()){
				$next = $arr_blocks->current();
			}

			while($curr instanceof PageBlock){
				if($arr_block_in == NULL || in_array($pb->path(), $arr_block_in)){
					//set up next iteration
					$display_type = $curr->displayType();
					$next_display_type = (isset($next) && $next instanceof PageBlock) ? $next->displayType() : null;
					if(strpos($display_type, 'chart') !== false && strpos($next_display_type, 'chart') === false && $consec_charts === 0){
						$odd_even = 'chart-only';
					}
					else{
						if($consec_charts % 2 == 1) $odd_even = 'chart-even';
						elseif($x == ($cnt - 1)) $odd_even = 'chart-last-odd';
						else $odd_even = 'chart-odd';
					}
					//set up next iteration
					if($display_type === 'table'){
						$consec_charts = 0;
					}
					if(strpos($display_type, 'chart') !== false){
						$consec_charts++;
					}
					
					$arr_blk_data = array(
							'block_num' => $x,
							'link_url' => site_url($this->section_path) . '/' . $this->page->path() . '/' . $curr->path(),
							'form_id' => $this->report_form_id,
							'block' => $curr->path(),
							'odd_even' => $odd_even,
					);
					$arr_view_blocks[] = $this->load->view($curr->displayType(), $arr_blk_data, TRUE);
					//add js line to populate the block after the page loads
					$tmp_js .= "updateBlock(\"block-canvas$x\", \"" . $curr->path() . "\", \"$x\", \"null\", \"null\",\"false\");\n";
					$tmp_js .= "if ($( '#datepickfrom' ).length > 0) $( '#datepickfrom' ).datepick({dateFormat: 'mm-dd-yyyy'});";
					$tmp_js .= "if ($( '#datepickto' ).length > 0) $( '#datepickto' ).datepick({dateFormat: 'mm-dd-yyyy'});";
					$tmp_block = $curr->path();
					$x++;
						
				}
				$curr = $next;
				$arr_blocks->next();
				$next = $arr_blocks->current();
			}
		}
		
		//set up page header
		$this->carabiner->css('chart.css');
		$this->carabiner->css('boxes.css');
		$this->carabiner->css('https://cdn.jsdelivr.net/qtip2/2.2.0/jquery.qtip.min.css', 'screen');
		$this->carabiner->css('tooltip.css');
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
		if(!$this->page->hasBenchmark()){
			$this->carabiner->css('hide_benchmarks.css', 'screen');
		}
		
		if(is_array($this->page_header_data)){
			$arr_sec_nav_data = array(
				'subsections' => $this->as_ion_auth->user_sections,
				'section_id' => $this->section->id(),
				'section_path' => $this->section_path,
			);
			$arr_blocks->rewind();

			$this->page_header_data = array_merge($this->page_header_data,
				[
					'title'=>$this->product_name . ' - ' . $this->config->item('site_title'),
					'description'=>$this->product_name . ' - ' . $this->config->item('site_title'),
					'message' => [$this->session->flashdata('message')],// + $this->{$this->primary_model_name}->arr_messages,
					'section_nav' => $this->load->view('section_nav', $arr_sec_nav_data, TRUE),
					'page_heading' => $this->product_name . " for Herd " . $this->herd->herdCode(),
					'arr_head_line' => array(
						'<script type="text/javascript">',
						'	var page = "' . $this->page->path() . '";',
						'	var page_url = "' . $this->report_path . '";',
						'	var site_url = "' . site_url() . '";',
						'	var herd_code = "' . $this->herd->herdCode() . '";',
						'	var block = "' . $arr_blocks->current()->name()	. '"',
						'</script>'
					),
					'arr_headjs_line'=>array(
						'{highcharts: "https://code.highcharts.com/4.0.4/highcharts.js"}',
						'{highcharts_more: "https://code.highcharts.com/4.0.4/highcharts-more.js"}',
						'{exporting: "https://code.highcharts.com/4.0.4/modules/exporting.js"}',
						'{regression: "' . $this->config->item("base_url_assets") . 'js/charts/high_regression.js"}',
						'{popup: "' . $this->config->item("base_url_assets") . 'js/jquery/popup.min.js"}',
						'{chart_options: "' . $this->config->item("base_url_assets") . 'js/charts/chart_options.js"}',
						'{graph_helper: "' . $this->config->item("base_url_assets") . 'js/charts/graph_helper.js"}',
						'{report_helper: "' . $this->config->item("base_url_assets") . 'js/report_helper.js"}',
						'{table_sort: "' . $this->config->item("base_url_assets") . 'js/jquery/stupidtable.min.js"}',
						'{tooltip: "https://cdn.jsdelivr.net/qtip2/2.2.0/jquery.qtip.min.js"}',
						'{datepick: "' . $this->config->item("base_url_assets") . 'js/jquery/jquery.datepick.min.js"}'
					),
			        'notices'=>$this->notices
				]
			);
			//load the report-specific js file if it exists
			if(file_exists(PROJ_DIR . '/' . 'js' . '/' . $this->section_path . '_helper.js')){
				$this->page_header_data['arr_headjs_line'][] = '{inv_helper: "' . $this->config->item("base_url_assets") . 'js/' . $this->section_path . '_helper.js"}';
			}
			$this->page_header_data['arr_headjs_line'][] = 'function(){' . $tmp_js . ';}';
		}
		//unset($this->{$this->primary_model_name}->arr_messages); //clear message var once it is displayed
		$this->load->model('setting_model');
		$this->load->model('benchmark_model');
		
		$this->benchmarks = new Benchmarks($this->session->userdata('user_id'), $this->input->post('herd_code'), $this->herd_model->header_info($this->herd->herdCode()), $this->setting_model, $this->benchmark_model, $this->session->userdata('benchmarks'));
		$arr_benchmark_data = $this->benchmarks->getFormData($this->session->userdata('benchmarks')); 
		$arr_nav_data = [
			'section_path' => $this->section_path,
			'curr_page' => $this->page->path(),
			'obj_pages' => $this->section->pages(),
		];

		$this->page_footer_data = [];
		$report_nav_path = 'report_nav';
		if(file_exists(APPPATH . 'views/' . $this->section_path . '/report_nav.php')){
			$report_nav_path =  $this->section_path . '/' . $report_nav_path;
		}
		//if(count($arr_nav_data['obj_pages']) < 2) {
		//	$this->carabiner->css('hide_report_nav.css', 'screen');
		//}
		$report_filter_path = 'filters';
		if(file_exists(APPPATH . 'views/' . $this->section_path . '/filters.php')){
			$report_filter_path =  $this->section_path . '/filters' . $report_filter_path;
		}

		$data = [
			'page_header' => $this->load->view('page_header', $this->page_header_data, TRUE),
			'herd_code' => $this->herd->herdCode(),
			'herd_data' => $this->load->view('dhi/herd_info', $herd_data, TRUE),
			'page_footer' => $this->load->view('page_footer', $this->page_footer_data, TRUE),
			'blocks' => $arr_view_blocks,
			'print_all' => $this->print_all,
			'report_path' => $this->report_path
		];
		
		$arr_filter_data = [
			//'arr_filters' => $this->filters->filter_list(),
			'arr_filters' => $this->filters->toArray(),
		];

		if(isset($arr_filter_data)){
			$collapse_data['content'] = $this->load->view($report_filter_path, $arr_filter_data, TRUE);
			$collapse_data['title'] = 'Set Filters';
			$collapse_data['id'] = 'filters';
			$data['filters'] = $this->load->view('collapsible', $collapse_data, TRUE);
		}
		
		$this->load->model('supplemental_model');
		$supp_factory = new SupplementalFactory($this->supplemental_model, site_url());
		$page_supp = $supp_factory->getPageSupplemental($this->page->id(), $this->supplemental_model, site_url());
		$data['page_supplemental'] = $page_supp->getContent();

		if(isset($arr_benchmark_data)){
			$collapse_data['content'] = $this->load->view('set_benchmarks', $arr_benchmark_data, TRUE);
			$collapse_data['title'] = 'Set Benchmarks';
			$collapse_data['id'] = 'bench-div';
			$data['benchmarks'] = $this->load->view('collapsible', $collapse_data, TRUE);
		}

		if((is_a($arr_nav_data['obj_pages'], 'SplObjectStorage') && $arr_nav_data['obj_pages']->count() > 1) || 
			(isset($arr_nav_data['arr_links']) && is_array($arr_nav_data['arr_links']) && count($arr_nav_data['arr_links']) > 1)) {
			$data['report_nav'] = $this->load->view($report_nav_path, $arr_nav_data, TRUE);
		}
		
		$this->_record_access(90, 'web', $this->config->item('product_report_code'));
		$this->load->view('report', $data);
	}

	protected function _record_access($event_id, $format, $product_code = null){
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
			$this->page->id(),
			'',//$this->reports->sort_text_brief($this->arr_sort_by, $this->arr_sort_order),
			$filter_text
		);
	}
}
