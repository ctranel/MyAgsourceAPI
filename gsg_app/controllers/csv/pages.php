<?php
//namespace myagsource;
require_once(APPPATH . 'libraries/filters/Filters.php');
require_once(APPPATH . 'libraries/Benchmarks/Benchmarks.php');
require_once(APPPATH . 'libraries/AccessLog.php');
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
use \myagsource\AccessLog;
use \myagsource\report_filters\Filters;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\dhi\HerdAccess;
use \myagsource\dhi\Herd;
use \myagsource\Site\WebContent\Sections;
use \myagsource\Site\WebContent\Pages;
//use \myagsource\Site\WebContent\Blocks;
use \myagsource\Site\WebContent\Block as PageBlock;
use \myagsource\Report\Content\Csv;
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

class blocks extends CI_Controller {
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

//	protected $report_form_id;
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

	function __construct(){
		parent::__construct();
		$this->load->model('herd_model');
/*		$this->load->model('web_content/section_model');
		$this->load->model('web_content/page_model', null, false, $this->session->userdata('user_id'));
		$this->load->model('notice_model');
*/		$this->load->model('web_content/block_model');
		$this->blocks = new Blocks($this->block_model);
//		$this->pages = new Pages($this->page_model, $this->blocks);
//		$sections = new Sections($this->section_model, $this->pages);
		$this->herd_access = new HerdAccess($this->herd_model);
		$this->herd = new Herd($this->herd_model, $this->session->userdata('herd_code'));
/*		
		$class_dir = $this->router->fetch_directory(); //this should match the name of this file (minus ".php".  Also used as base for css and js file names and model directory name
		$class = $this->router->fetch_class();
		if($class === 'index'){
			$class = '';
		}
		$method = $this->router->fetch_method();
		$this->section_path = $class_dir . $class;
		if(substr($this->section_path, -1) === '/'){
			$this->section_path = substr($this->section_path, 0, -1);
		}
		//load sections
		$this->section = $sections->getByPath($this->section_path . '/');
		$this->session->set_userdata('section_id', $this->section->id());
		$sections->loadChildren($this->section, $this->pages, $this->session->userdata('user_id'), $this->herd, $this->ion_auth_model->getTaskPermissions());
		$path = uri_string();

		if(strpos($path, $method) === false){
			$method = $this->section->defaultPagePath();
		}

		if(!$this->authorize($method)) {
			$this->session->keep_flashdata('message');
			$this->session->keep_flashdata('redirect_url');
			//@todo: redirect, or send error message?
			redirect(site_url('auth/login'));
		}
*/
		if($this->herd->herdCode() == ''){
			$this->session->keep_flashdata('redirect_url');
			//@todo: redirect, or send error message?
			redirect(site_url('dhi/change_herd/select'));			
		}
/*
		$arr_path = explode('/',$path);
		$page_name = $method;
		$block_name = '';

		$this->page = $this->pages->getByPath($page_name, $this->section->id());
		$this->report_path = $this->section_path . '/' . $this->page->path();
		//$this->primary_model_name = $this->page->path() . '_model';
//		$this->report_form_id = 'report_criteria';//filter-form';
		$this->page_header_data['top_sections'] = $this->as_ion_auth->top_sections;
		$this->page_header_data['user_sections'] = $this->as_ion_auth->user_sections;
		$this->page_header_data['num_herds'] = $this->herd_access->getNumAccessibleHerds($this->session->userdata('user_id'), $this->as_ion_auth->arr_task_permissions(), $this->session->userdata('arr_regions'));
		
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

	function get($arr_block_in, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
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
					$this->reports->sortText($this->arr_sort_by, $this->arr_sort_order);//this function sets text, and could return it if needed
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
		$access_log = new AccessLog($this->access_log_model);
		
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
			'',//$this->reports->sortTextBrief($this->arr_sort_by, $this->arr_sort_order),
			$filter_text
		);
	}
}
