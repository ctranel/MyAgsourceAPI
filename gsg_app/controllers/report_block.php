<?php
//namespace myagsource;
require_once(APPPATH . 'libraries/filters/Filters.php');
require_once(APPPATH . 'libraries/benchmarks_lib.php');
require_once(APPPATH . 'libraries/Supplemental/Content/SupplementalFactory.php');
require_once(APPPATH . 'libraries/dhi/HerdAccess.php');
require_once(APPPATH . 'libraries/dhi/herd.php');
require_once(APPPATH . 'libraries/Report/Content/Blocks.php');
require_once(APPPATH . 'libraries/Site/WebContent/Blocks.php');
require_once(APPPATH . 'libraries/Site/WebContent/Pages.php');
require_once(APPPATH . 'libraries/Site/WebContent/Sections.php');

use \myagsource\settings\Benchmarks_lib;
use \myagsource\report_filters\Filters;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\dhi\HerdAccess;
use \myagsource\dhi\Herd;
use \myagsource\Site\WebContent\Sections;
use \myagsource\Site\WebContent\Pages;
use \myagsource\Site\WebContent\Blocks as WebBlocks;
use \myagsource\Report\Content\Blocks;

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

class report_block extends CI_Controller {
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
	
	protected $arr_sort_by = [];
	protected $arr_sort_order = [];
	protected $herd_code;
	protected $product_name;
	protected $primary_model_name;
	protected $section_path; //The path to the site section; set in constructor to point to the controller name
//	protected $page_header_data;
	protected $report_data;
	protected $display;
	protected $html;
	protected $filters; //filters object
	protected $supplemental;

	/**
	 * Benchmark settings
	 * 
	 * @var Session_settings object
	protected $bench_setting;
	 */
	
	function __construct(){
		parent::__construct();
		$this->load->model('herd_model');
		$this->load->model('supplemental_model');
		$this->load->model('ReportContent/report_block_model');
		$this->blocks = new Blocks($this->report_block_model, new SupplementalFactory($this->supplemental_model, site_url()));
//		$this->pages = new Pages($this->page_model, $this->blocks);
//		$sections = new Sections($this->section_model, $this->pages);

		
		$this->herd_access = new HerdAccess($this->herd_model);
		$herd = new Herd($this->herd_model, $this->session->userdata('herd_code'));
		
//		$class_dir = $this->router->fetch_directory(); //this should match the name of this file (minus ".php".  Also used as base for css and js file names and model directory name
//		$class = $this->router->fetch_class();
//		$method = $this->router->fetch_method();

//		$this->section_path = $class_dir . $class;
//var_dump($this->section_path);
		
		//load most specific model available.  Must load model before setting section
//		$path = uri_string();
//		$arr_path = explode('/',$path);

		
//@todo: fix line below, why is path coming in as 'land' and not 'dhi/'?  redirect?
/*		$this->section_path = str_replace('land', 'dhi/', $this->section_path);
		$this->section = $sections->getByPath($this->section_path);
		$sections->loadChildren($this->section, $this->pages, $this->session->userdata('user_id'), $herd, $this->ion_auth_model->getTaskPermissions());
		if($this->authorize($method)) {
			$this->load->library('reports');
			$this->reports->herd_code = $this->herd_code;
		}
*/		
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

	protected function authorize(){
		if(!isset($this->as_ion_auth)){
			echo "Your session has expired, please log in and try again..";
			exit;
		}
		if(!$this->as_ion_auth->logged_in() && $this->herd_code != $this->config->item('default_herd')) {
			echo "Your session has expired, please log in and try again...";
			exit;
		}
		if(!isset($this->herd_code)){
			echo 'Either your session expired, or you have not yet chosen a herd.  Please select a herd and try again.';
  			exit;
		}
		//if section scope is public, pass unsubscribed test
		//@todo: build display_hierarchy/report_organization, etc interface with get_scope function (with classes for super_sections, sections, etc)
		$pass_unsubscribed_test = true; //$this->as_ion_auth->get_scope('sections', $this->section->id()) == 'pubic';
		//@todo: redo access tests
//		$pass_unsubscribed_test = $this->as_ion_auth->has_permission("View All Content") || $this->web_content_model->herd_is_subscribed($this->section->id(), $this->herd_code);
		$pass_view_nonowned_test = $this->as_ion_auth->has_permission("View All Herds") || $this->session->userdata('herd_code') == $this->config->item('default_herd');
		if(!$pass_view_nonowned_test){
			$pass_view_nonowned_test = in_array($this->herd_code, $this->herd_access->getAccessibleHerdCodes($this->session->userdata('user_id'), $this->as_ion_auth->arr_task_permissions(), $this->session->userdata('arr_regions')));
		}
		if($pass_unsubscribed_test && $pass_view_nonowned_test){
			return TRUE;
		}
		elseif(!$pass_unsubscribed_test && !$pass_view_nonowned_test) {
			echo 'Herd ' . $this->herd_code . ' is not subscribed to the ' . $this->product_name . ', nor do you have permission to view this report for herd ' . $this->herd_code . '.  Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' if you have questions or concerns.';
			exit;
		}
		elseif(!$pass_unsubscribed_test) {
			echo 'Herd ' . $this->herd_code . ' is not subscribed to the ' . $this->product_name . '.  Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' if you have questions or concerns.';
			exit;
		}
		elseif(!$pass_view_nonowned_test) {
			echo 'You do not have permission to view the ' . $this->product_name . ' for herd ' . $this->herd_code . '.  Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' if you have questions or concerns.';
			exit;
		}
		return FALSE;
	}
	
	/*
	 * ajax_report: Called via AJAX to populate graphs
	 * @param string block: name of the block for which to retreive data
	 * @param string output: method of output (chart, table, etc)
	 * @param boolean/string file_format: return the value of function (TRUE), or echo it (FALSE).  Defaults to FALSE
	 * @param string cache_buster: text to make page appear as a different page so that new data is retrieved
	 */
	public function ajax_report($page_path, $block_name, $output, $sort_by = 'null', $sort_order = 'null', $file_format = 'web', $test_date = FALSE, $report_count=0, $json_filter_data = NULL, $first=FALSE, $cache_buster = NULL) {//, $herd_size_code = FALSE, $all_breeds_code = FALSE
		$page_path = str_replace('|', '/', urldecode($page_path));
		$arr_path = array_filter(explode('/', $page_path));
		$path_page_segment = $arr_path[count($arr_path) - 1];
		$tmp_path = $page_path . $block_name;
		
		$this->herd_code = strlen($this->session->userdata('herd_code')) == 8?$this->session->userdata('herd_code'):NULL;
		
		//Load the most specific model that exists
		while(strpos($tmp_path, '/') !== false){
			if(file_exists(APPPATH . 'models/' . $tmp_path . '_model.php')){
				$this->primary_model_name = substr($tmp_path, strripos($tmp_path, '/')) . '_model';
				$this->load->model($tmp_path, '', FALSE, $this->section_path);
				exit;
			}
			$tmp_path = substr($tmp_path, 0, strripos($tmp_path, '/'));
		}

		//if no specific models found, go with the general report model
		if(!isset($this->primary_model_name)){
			$this->primary_model_name = 'report_model';
			$this->load->model('report_model', '', FALSE, $this->section_path);
		}

		
		
		//SUPPLEMENTAL DATA
//set keyed array with all supplemental data for the block (bsf_id as key supplemental, with df_field id for params)?
//All supp data is also included in block field data view.
		$this->load->model('supplemental_model');
		$supp_factory = new SupplementalFactory($this->supplemental_model, site_url());
		//column data
		//$block_supp = Supplemental::getColDataSupplemental($fd['bsf_id'], $this->supplemental_model, site_url());
		
		
		//$this->arr_field_links[$fn] = $block_supp->getContent();
		//add fields included in the supplemental parameters to the field list used for composing select queries (not displayed)
//		foreach($block_supp->supplementalLinks() as $s){
//			foreach($s->params() as $p){
//				if(!in_array($p->value_db_field_name(), $this->arr_db_field_list)){
//					$this->arr_db_field_list[] = $p->value_db_field_name();
//				}
//			}
//		}
		//column header
		//$block_supp = Supplemental::getColHeaderSupplemental($fd['bsf_id'], $this->supplemental_model, site_url());
		//$this->arr_header_links[$fn] = $block_supp->getContent();
		//END SUPPLEMENTAL DATA
				
		//verify user has permission to view content for given herd
		if($this->authorize()) {
			$this->load->library('reports');
			$this->reports->herd_code = $this->herd_code;
		}
		
		//$this->page = $this->pages->getByPath(urldecode($page_name));

		$block = $this->blocks->getByPath(urldecode($block_name));
		$sort_by = urldecode($sort_by);
		//$this->objPage = $this->{$this->primary_model_name}->arr_blocks[$this->page->path()];
				
		//set sort order
		$this->load->helper('report_chart_helper');
		if($sort_by != 'null' && $sort_order != 'null') {
			$this->arr_sort_by = explode('|', $sort_by);
			$this->arr_sort_order = explode('|', $sort_order);
		}
		else {
			$this->arr_sort_by = $block->sortFieldNames();
			$this->arr_sort_order = $block->sortOrders();
		}
		
		if(isset($json_filter_data)){
			$section = $this->getSection($this->section_path);
			$arr_params = (array)json_decode(urldecode($json_filter_data));
			if(isset($arr_params['csrf_test_name']) && $arr_params['csrf_test_name'] != $this->security->get_csrf_hash()) die("I don't recognize your browser session, your session may have expired, or you may have cookies turned off.");
			unset($arr_params['csrf_test_name']);

			//prep data for filter library
			$this->load->model('filter_model');
			//load required libraries
			$this->filters = new Filters($this->filter_model);
			$primary_table = $this->{$this->primary_model_name}->get_primary_table_name();
			$this->load->helper('multid_array_helper');
			$this->filters->set_filters(
					$section->id(),
					$path_page_segment,
					array('herd_code' => $this->session->userdata('herd_code')) + $arr_params
			);
		}
		// block-level supplemental data
		$this->load->model('supplemental_model');
		$block_supp = $supp_factory->getBlockSupplemental($block->id(), $this->supplemental_model, site_url());
		$this->supplemental = $block_supp->getContent();
		//end supplemental

		$this->json = $block->loadData($report_count);
		
		//$this->json['supplemental'] = $this->supplemental;
		$this->display = $output;
		//set parameters for given block
		
		//common functionality
		$first = ($first === 'true');
		if($file_format == 'csv'){
			if($first){
				$this->_record_access(90, 'csv', $this->config->item('product_report_code'));
			}
			return $this->report_data['report_data'];
		}
		elseif($file_format == 'pdf'){
			if($first){
				$this->_record_access(90, 'pdf', $this->config->item('product_report_code'));
			}
			if($this->display == 'html'){
				return $this->html;
			}
			else {
				return $this->report_data['report_data'];
			}
		}
		if($this->display == 'table'){
			$this->json['html'] = $this->html;
		}

		if($first){
			$this->_record_access(90, 'web', $this->config->item('product_report_code'));
		}
		$this->json['section_data'] = [
			'block' => $block_name,
			'sort_by' => $sort_by,
			'sort_order' => $sort_order,
			'graph_order' => $report_count
		];
		$return_val = prep_output($this->display, $this->json, $report_count, $file_format);
		if($return_val) {
			return $return_val;
		}
 	   	exit;
	}

	protected function getSection($arr_path){
		//get section
		//unset($arr_path[count($arr_path) - 1]);
		$this->load->model('web_content/section_model');
		$this->load->model('web_content/page_model', null, false, $this->session->userdata('user_id'));
		$this->load->model('web_content/block_model', 'WebBlockModel');
		$web_blocks = new WebBlocks($this->WebBlockModel);
		$pages = new Pages($this->page_model, $web_blocks);
		//$section_path = implode('/', $arr_path);
		$sections = new Sections($this->section_model, $pages);
		$section = $sections->getByPath($this->section_path);
		return $section;
	}
/*		
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
			$this->reports->sort_text_brief($this->arr_sort_by, $this->arr_sort_order),
			$filter_text
		);
	}
*/
}
