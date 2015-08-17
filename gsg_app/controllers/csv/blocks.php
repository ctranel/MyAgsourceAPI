<?php
//namespace myagsource;
require_once(APPPATH . 'libraries/filters/Filters.php');
require_once(APPPATH . 'libraries/Benchmarks/Benchmarks.php');
require_once(APPPATH . 'libraries/dhi/HerdAccess.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');
require_once(APPPATH . 'libraries/Report/Content/Blocks.php');
require_once(APPPATH . 'libraries/Site/WebContent/Blocks.php');
require_once(APPPATH . 'libraries/Site/WebContent/Pages.php');
require_once(APPPATH . 'libraries/Site/WebContent/Sections.php');
require_once(APPPATH . 'libraries/Datasource/DbObjects/DbTable.php');
require_once(APPPATH . 'libraries/Report/Content/SortBuilder.php');
require_once(APPPATH . 'libraries/DataHandler.php');
require_once(APPPATH . 'libraries/Report/Content/Table/TableData.php');
require_once(APPPATH . 'libraries/Report/Content/Table/Header/TableHeader.php');
require_once(APPPATH . 'libraries/Report/Content/Csv.php');
require_once(APPPATH . 'libraries/AccessLog.php');

use \myagsource\Benchmarks\Benchmarks;
use \myagsource\report_filters\Filters;
use \myagsource\dhi\HerdAccess;
use \myagsource\dhi\Herd;
use \myagsource\Site\WebContent\Sections;
use \myagsource\Site\WebContent\Pages;
use \myagsource\Site\WebContent\Blocks as WebBlocks;
use \myagsource\Report\Content\Blocks as ReportBlocks;
use \myagsource\Report\Content\Table\Header\TableHeader;
use \myagsource\Datasource\DbObjects\DbTable;
use \myagsource\Report\Content\SortBuilder;
use \myagsource\DataHandler;
use \myagsource\Report\Content\Table\TableData;
use \myagsource\Report\Content\Csv;
use \myagsource\AccessLog;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* -----------------------------------------------------------------
 *	blocks csv controller
 *  @file: dhi/csv/blocks.php
 *  @author: ctranel
 *
 *  @description: CSV equivelant of report block.  Works only with tables
 *
 * -----------------------------------------------------------------
 */

class Blocks extends CI_Controller {
	/**
	 * herd_access
	 * @var HerdAccess
	 **/
	protected $herd_access;
	
	/**
	 * section
	 * @var Sections
	 **/
	protected $sections;
	
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
	 * block
	 * 
	 * Block
	 * @var Block
	 **/
	protected $block;
	
	/**
	 * supp_factory
	 * 
	 * Supplemental factory
	 * @var \myagsource\Supplemental\Content\SupplementalFactory
	protected $supp_factory;
	 **/
	
	/**
	 * herd
	 * 
	 * Herd object
	 * @var Herd
	 **/
	protected $herd;
	
	/**
	 * product_name
	 * 
	 * @var String
	 **/
	protected $product_name;

	/**
	 * section_path
	 * 
	 * The path to the site section; set in constructor to point to the controller name
	 * 
	 * @var String
	 **/
	protected $section_path;

	/**
	 * filters
	 * 
	 * Filters object
	 * @var Filters
	 **/
	protected $filters;

	/**
	 * supplemental
	 * 
	 * Supplemental
	 * @var Supplemental
	 **/
	protected $supplemental;

	function __construct(){
		parent::__construct();
		$this->load->model('herd_model');
		
		// report content
		$this->load->model('supplemental_model');
		$this->load->model('ReportContent/report_block_model');
		$this->load->model('Datasource/db_field_model');
//		$this->supp_factory = new SupplementalFactory($this->supplemental_model, site_url());
		$this->blocks = new ReportBlocks($this->report_block_model, $this->db_field_model, null);

		$this->herd_access = new HerdAccess($this->herd_model);
		$this->herd = new Herd($this->herd_model, $this->session->userdata('herd_code'));
		$method = $this->router->fetch_method();

		$this->load->model('web_content/section_model');
		$this->load->model('web_content/page_model', null, false, $this->session->userdata('user_id'));
		$this->load->model('web_content/block_model', 'WebBlockModel');
		$web_blocks = new WebBlocks($this->WebBlockModel);
		$this->pages = new Pages($this->page_model, $web_blocks);
		$this->sections = new Sections($this->section_model, $this->pages);
		
		
		if(!$this->authorize($method)) {
			if($this->session->flashdata('message')) $this->session->keep_flashdata('message');
			if($method != 'ajax_report') $this->session->set_flashdata('redirect_url', $this->uri->uri_string());
			redirect(site_url('auth/login'));
		}
		
		if($this->session->userdata('herd_code') == ''){ // || $this->session->userdata('herd_code') == '35990571'
			$this->session->keep_flashdata('redirect_url');
			redirect(site_url('dhi/change_herd/select'));			
		}
		/* Load the profile.php config file if it exists
		if (ENVIRONMENT == 'development' || ENVIRONMENT == 'localhost') {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			} 
		}*/
	}

	protected function authorize(){
		if(!isset($this->as_ion_auth)){
			echo "Your session has expired, please log in and try again..";
			exit;
		}
		if(!$this->as_ion_auth->logged_in()) {
			echo "Your session has expired, please log in and try again...";
			exit;
		}
		if(!isset($this->herd)){
			echo 'Either your session expired, or you have not yet chosen a herd.  Please select a herd and try again.';
  			exit;
		}
		//if section scope is public, pass unsubscribed test
		//@todo: build display_hierarchy/report_organization, etc interface with get_scope function (with classes for super_sections, sections, etc)
		$pass_unsubscribed_test = true; //$this->as_ion_auth->get_scope('sections', $this->section->id()) == 'pubic';
		//@todo: redo access tests
//		$pass_unsubscribed_test = $this->as_ion_auth->has_permission("View All Content") || $this->web_content_model->herd_is_subscribed($this->section->id(), $this->herd->herdCode());
		$pass_view_nonowned_test = $this->as_ion_auth->has_permission("View All Herds") || $this->session->userdata('herd_code') == $this->config->item('default_herd');
		if(!$pass_view_nonowned_test){
			$pass_view_nonowned_test = in_array($this->herd->herdCode(), $this->herd_access->getAccessibleHerdCodes($this->session->userdata('user_id'), $this->as_ion_auth->arr_task_permissions(), $this->session->userdata('arr_regions')));
		}
		if($pass_unsubscribed_test && $pass_view_nonowned_test){
			return TRUE;
		}
		elseif(!$pass_unsubscribed_test && !$pass_view_nonowned_test) {
			echo 'Herd ' . $this->herd->herdCode() . ' is not subscribed to the ' . $this->product_name . ', nor do you have permission to view this report for herd ' . $this->herd->herdCode() . '.  Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' if you have questions or concerns.';
			exit;
		}
		elseif(!$pass_unsubscribed_test) {
			echo 'Herd ' . $this->herd->herdCode() . ' is not subscribed to the ' . $this->product_name . '.  Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' if you have questions or concerns.';
			exit;
		}
		elseif(!$pass_view_nonowned_test) {
			echo 'You do not have permission to view the ' . $this->product_name . ' for herd ' . $this->herd->herdCode() . '.  Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' if you have questions or concerns.';
			exit;
		}
		return FALSE;
	}
	
	/*
	 * ajax_report: Called via AJAX to populate graphs
	 * @param string page path
	 * @param string block name
	 * @param string output
	 * @param string sort by
	 * @param string sort order
	 * @param boolean/string file_format: return the value of function (TRUE), or echo it (FALSE).  Defaults to FALSE
	 * @param string test date
	 * @param int report count
	 * @param string serialized filter data
	 * @param boolean first
	 * @param string cache_buster: text to make page appear as a different page so that new data is retrieved
	 * @todo: can I delete the last 2 params?
	 */
	public function csv($page_path, $block_name, $sort_by = 'null', $sort_order = 'null', $json_filter_data = NULL) {
		//verify user has permission to view content for given herd
		if(!$this->authorize()) {
			die('not authorized');
		}
		
		$page_path = str_replace('|', '/', urldecode($page_path));
		
		$this->section_path = substr($page_path, 0, (strrpos($page_path, '/') + 1));
		$section = $this->sections->getByPath($this->section_path);
		$path_page_segment = substr($page_path, (strrpos($page_path, '/') + 1));
		$page = $this->pages->getByPath($path_page_segment);
		
		$this->block = $this->blocks->getByPath(urldecode($block_name));
		$output = $this->block->displayType();
		
		//SORT
		$sort_builder = new SortBuilder();
		$sort_builder->build($this->block, $sort_by, $sort_order);
		//END SORT
		
		//FILTERS
		//prep data for filter library
		$this->load->model('filter_model');
		//load required libraries
		$filters = new Filters($this->filter_model);
		if(isset($json_filter_data)){
			$arr_params = (array)json_decode(urldecode($json_filter_data));
			if(isset($arr_params['csrf_test_name']) && $arr_params['csrf_test_name'] != $this->security->get_csrf_hash()){
				die("I don't recognize your browser session, your session may have expired, or you may have cookies turned off.");
			}
			unset($arr_params['csrf_test_name']);
		
			$this->load->helper('multid_array_helper');
			$filters->setCriteria(
					$section->id(),
					$path_page_segment,
					['herd_code' => $this->session->userdata('herd_code')] + $arr_params
			);

			/*
			 * If this is a cow level block, and the filter is set to 0 (pstring), remove filter
			 * Needed for reports that contain both cow level and summary reports.
			*/
			foreach($arr_params as $k => $v){
				if(!$this->block->isSummary()){
					if(is_array($v)){
						$tmp = array_filter($v);
						if(empty($tmp)){
							$filters->removeCriteria($k);
						}
					}
					elseif($v == 0){
						$filters->removeCriteria($k);
					}
				}
			}
		}
		$this->block->setFilters($filters);
		//END FILTERS
		
		// benchmarks
		$this->load->model('setting_model');
		$herd_info = $this->herd_model->header_info($this->herd->herdCode());
		$this->load->model('benchmark_model');
		$this->benchmarks = new Benchmarks($this->session->userdata('user_id'), $this->input->post('herd_code'), $herd_info, $this->setting_model, $this->benchmark_model, $this->session->userdata('benchmarks'));
		// end benchmarks
			
		// report data
		$this->load->model('ReportContent/report_data_model');
		$this->load->model('Datasource/db_table_model');
		$db_table = new DbTable($this->block->primaryTableName (), $this->db_table_model);

		// Load the most specific data-handling library that exists
		$tmp_path = $page_path . '/' . $block_name;
		$data_handler = new DataHandler();
		$block_data_handler = $data_handler->load($this->block, $tmp_path, $this->report_data_model, $db_table, $this->benchmarks);
		//End load data-handling library

		$results = $block_data_handler->getData($filters->criteriaKeyValue());//$report_count, 
		if(!is_array($results) || empty($results)){
			
		}
		// end report data
		
		//Handle table headers for table blocks
		$header_groups = $this->report_block_model->getHeaderGroups($this->block->id());
		
		//@todo: pull this only when needed? move adjustHeaderGroups to TableBlock or TableHeader class
		$arr_dates = $this->herd_model->get_test_dates_7_short($this->session->userdata('herd_code'));
		$header_groups = TableHeader::mergeDateIntoHeader($header_groups, $arr_dates);

		$this->block->setTableHeader($results, null, $header_groups);
		$header = $this->block->getTableHeaderLeafs();
		$benchmark_text = '';
		if($this->block->hasBenchmark()){
			$benchmark_text = $this->benchmarks->get_bench_text();
		}

		
		$data = array_merge([$header], [[$benchmark_text]], $results);
		
		//@todo: base header on accept property of request header.  Could then merge with report_block and use $this->report_data['data'] for data
		if(is_array($results) && !empty($results)){
			$csv = new Csv();
			$this->config->set_item('compress_output', FALSE);
			
			$filename = $this->herd->herdCode() . '-' . date('mdy-His') . '.csv';
			$csv_text = $csv->create_csv($data);
			
			header('Content-type: application/excel');
			header('Content-disposition: attachment; filename=' . $filename);
			echo $csv_text;
			$this->_record_access(90, 'csv', $page->id(), $this->config->item('product_report_code'));
		}
		else {
			//send error response code
			//$this->{$this->primary_model_name}->arr_messages[] = 'There is no data to export into an Excel file.';
		}
	}

	protected function _record_access($event_id, $format, $page_id, $product_code = null){
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
			'csv',
			$page_id,
			$this->block->sortText(),
			$filter_text
		);
	}
}