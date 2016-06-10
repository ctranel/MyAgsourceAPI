<?php
//namespace myagsource;
require_once(APPPATH . 'libraries/filters/Filters.php');
require_once(APPPATH . 'libraries/Benchmarks/Benchmarks.php');
//use supp factory in CSV?
require_once(APPPATH . 'libraries/Supplemental/Content/SupplementalFactory.php');
require_once(APPPATH . 'libraries/dhi/HerdAccess.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');
require_once(APPPATH . 'libraries/Report/Content/Blocks.php');
require_once(APPPATH . 'libraries/Site/WebContent/Blocks.php');
require_once(APPPATH . 'libraries/Site/WebContent/Pages.php');
require_once(APPPATH . 'libraries/Site/WebContent/Sections.php');
require_once(APPPATH . 'libraries/Site/WebContent/PageAccess.php');
require_once(APPPATH . 'libraries/dhi/HerdPageAccess.php');
require_once(APPPATH . 'libraries/Datasource/DbObjects/DbTable.php');
require_once(APPPATH . 'libraries/Report/Content/SortBuilder.php');
require_once(APPPATH . 'libraries/DataHandler.php');
require_once(APPPATH . 'libraries/Report/Content/Table/TableData.php');
require_once(APPPATH . 'libraries/Report/Content/Table/Header/TableHeader.php');
require_once(APPPATH . 'libraries/Report/Content/Csv.php');
require_once(APPPATH . 'libraries/AccessLog.php');

use \myagsource\Benchmarks\Benchmarks;
use \myagsource\report_filters\Filters;
//use supp factory in CSV?
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\dhi\HerdAccess;
use \myagsource\dhi\HerdPageAccess;
use \myagsource\Site\WebContent\PageAccess;
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

class Blocks extends MY_Controller {
	/**
	 * herd_access
	 * @var HerdAccess
	 **/
	protected $herd_access;
	
	/**
	 * herd_page_access
	 * @var HerdPageAccess
	 **/
	protected $herd_page_access;
	
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
		$this->session->keep_all_flashdata();

		//set up herd
		$this->load->model('herd_model');
		$this->herd_access = new HerdAccess($this->herd_model);
		$this->herd = new Herd($this->herd_model, $this->session->userdata('herd_code'));
		
		//is someone logged in?
		if(!$this->as_ion_auth->logged_in() && $this->herd->herdCode() != $this->config->item('default_herd')) {
			$this->redirect(site_url('auth/login'), "Please log in.  ");
		}
		
		//is a herd selected?
		if(!$this->herd->herdCode() || $this->herd->herdCode() == ''){
			$this->redirect(site_url('dhi/change_herd/select'), "Please select a herd and try again.  ");
		}
		
		//does logged in user have access to selected herd?
		$has_herd_access = $this->herd_access->hasAccess($this->session->userdata('user_id'), $this->herd->herdCode(), $this->session->userdata('arr_regions'), $this->permissions->permissionsList());
		if(!$has_herd_access){
			$this->redirect(site_url('dhi/change_herd/select'),"You do not have permission to access this herd.  Please select another herd and try again.  ");
		}
		
		// report content
		$this->load->model('supplemental_model');
		$this->load->model('ReportContent/report_block_model');
		$this->load->model('Datasource/db_field_model');
//use supp factory in CSV?
		$this->supp_factory = null;//new SupplementalFactory($this->supplemental_model, site_url());
		$this->blocks = new ReportBlocks($this->report_block_model, $this->db_field_model, $this->supp_factory);

		//set up web content objects
		$this->load->model('web_content/section_model');
		$this->load->model('web_content/page_model', null, false, $this->session->userdata('user_id'));
		$this->load->model('web_content/block_model', 'WebBlockModel');
		$web_blocks = new WebBlocks($this->WebBlockModel);
		$this->pages = new Pages($this->page_model, $web_blocks);
		$this->sections = new Sections($this->section_model, $this->pages);
		
				/* Load the profile.php config file if it exists
		if (ENVIRONMENT == 'development' || ENVIRONMENT == 'localhost') {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			} 
		}*/
	}

	/*
	 * ajax_report: Called via AJAX to populate graphs
	 * @param string page path
	 * @param string block name
	 * @param string sort by
	 * @param string sort order
	 * @param boolean/string file_format: return the value of function (TRUE), or echo it (FALSE).  Defaults to FALSE
	 */
	public function csv($page_path, $block_name, $sort_by = 'null', $sort_order = 'null', $json_filter_data = NULL) {
		$page_path = str_replace('|', '/', trim(urldecode($page_path), '|'));
		$path_parts = explode('/', $page_path);
		$num_parts = count($path_parts);
		$path_page_segment = $path_parts[$num_parts - 1];

		//load section
		$this->section_path = isset($path_parts[$num_parts - 2]) ? $path_parts[$num_parts - 2] . '/' : '/';
		$this->section = $this->sections->getByPath($this->section_path);
						
		//is container page viewable to this user?
		//does user have access to current page for selected herd?
		$this->page = $this->pages->getByPath($path_page_segment, $this->section->id());
		$this->herd_page_access = new HerdPageAccess($this->page_model, $this->herd, $this->page);
		$this->page_access = new PageAccess($this->page, $this->permissions->hasPermission("View All Content"));
		if(!$this->page_access->hasAccess($this->herd_page_access->hasAccess())) {
			$this->post_message('You do not have permission to view the requested report for herd ' . $this->herd->herdCode() . '.  Please select a report from the navigation or contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' if you have questions or concerns.');
			return;
		}
		
		$this->block = $this->blocks->getByPath(urldecode($block_name));
		$output = $this->block->displayType();
		
		//SORT
		$sort_builder = new SortBuilder($this->report_block_model);
		$sort_builder->build($this->block, $sort_by, $sort_order);
		//END SORT
		
		//FILTERS
		//prep data for filter library
		$this->load->model('filter_model');
		//load required libraries
		$filters = new Filters($this->filter_model);
		if(isset($json_filter_data)){
			$arr_params = (array)json_decode(urldecode($json_filter_data));
			/* @todo: backend csrf was blocking CORS, so we need to turn it off for development
			if(isset($arr_params['csrf_test_name']) && $arr_params['csrf_test_name'] != $this->security->get_csrf_hash()){
				die("I don't recognize your browser session, your session may have expired, or you may have cookies turned off.");
			} */
			unset($arr_params['csrf_test_name']);
		
			$this->load->helper('multid_array_helper');
			$filters->setCriteria(
					$this->section->id(),
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
						$tmp = array_filter($arr_params[$k], function($v){
							return (!empty($v) || $v === 0 || $v === '0');
						});
						if(empty($tmp)){
							$filters->removeCriteria($k);
						}
					}
					elseif(empty($v) && ($v !== 0 || $k === 'pstring')){
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
        $results = $block_data_handler->prepareDisplayData();

		if(!is_array($results) || empty($results)){
			
		}
		// end report data
		
		//Handle table headers for table blocks
		$header_groups = $this->report_block_model->getHeaderGroups($this->block->id());
		
		//@todo: pull this only when needed? move adjustHeaderGroups to TableBlock or TableHeader class
		$arr_dates = $this->herd_model->get_test_dates_7_short($this->session->userdata('herd_code'));
		//no header groups in csv
        //$header_groups = TableHeader::mergeDateIntoHeader($header_groups, $arr_dates);

		//$this->block->setTableHeader($results, null, $header_groups);
		//$header = $this->block->getTableHeaderLeafs();
        //@todo: this will not work for pivots
        $header = $this->block->getDisplayedFieldArray();
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
			$this->_record_access(90, 'csv', $this->herd_page_access->reports(), null);
			$this->load->view('echo.php', ['text' => $csv_text]);
		}
		else {
			//send error response code
			//$this->{$this->primary_model_name}->arr_messages[] = 'There is no data to export into an Excel file.';
		}
	}

	protected function _record_access($event_id, $format, $page_id, $products = null){
		if($this->session->userdata('user_id') === FALSE){
			return FALSE;
		}
		$herd_code = $this->session->userdata('herd_code');
		$recent_test = $this->session->userdata('recent_test_date');
		$recent_test = empty($recent_test) ? NULL : $recent_test;
		
		$filter_text = isset($this->filters) ? $this->filters->get_filter_text() : NULL;

		$this->load->model('access_log_model');
		$access_log = new AccessLog($this->access_log_model);
		
		$access_log->writeEntry(
			$this->as_ion_auth->is_admin(),
			$event_id,
			$herd_code,
			$recent_test,
			$this->session->userdata('user_id'),
			$this->session->userdata('active_group_id'),
			$products,
			'csv',
			$page_id,
			$this->block->sortText(),
			$filter_text
		);
	}
}
