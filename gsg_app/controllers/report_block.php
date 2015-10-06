<?php
//namespace myagsource;
require_once(APPPATH . 'libraries/filters/Filters.php');
require_once(APPPATH . 'libraries/Benchmarks/Benchmarks.php');
require_once(APPPATH . 'libraries/Supplemental/Content/SupplementalFactory.php');
require_once(APPPATH . 'libraries/dhi/HerdAccess.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');
require_once(APPPATH . 'libraries/Report/Content/Blocks.php');
require_once(APPPATH . 'libraries/Site/WebContent/Blocks.php');
require_once(APPPATH . 'libraries/Site/WebContent/Pages.php');
require_once(APPPATH . 'libraries/Site/WebContent/PageAccess.php');
require_once(APPPATH . 'libraries/dhi/HerdPageAccess.php');
require_once(APPPATH . 'libraries/Site/WebContent/Sections.php');
require_once(APPPATH . 'libraries/Datasource/DbObjects/DbTable.php');
require_once(APPPATH . 'libraries/Report/Content/Chart/ChartData.php');
require_once(APPPATH . 'libraries/Report/Content/SortBuilder.php');
require_once(APPPATH . 'libraries/DataHandler.php');
require_once(APPPATH . 'libraries/Report/Content/Table/TableData.php');
require_once(APPPATH . 'libraries/Report/Content/Table/Header/TableHeader.php');

use \myagsource\Benchmarks\Benchmarks;
use \myagsource\report_filters\Filters;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\dhi\HerdAccess;
use \myagsource\dhi\HerdPageAccess;
use \myagsource\Site\WebContent\PageAccess;
use \myagsource\dhi\Herd;
use \myagsource\Site\WebContent\Sections;
use \myagsource\Site\WebContent\Pages;
use \myagsource\Site\WebContent\Blocks as WebBlocks;
use \myagsource\Report\Content\Blocks;
use \myagsource\Report\Content\Chart\ChartData;
use \myagsource\Report\Content\Table\Header\TableHeader;
use \myagsource\Datasource\DbObjects\DbTable;
use \myagsource\Report\Content\SortBuilder;
use \myagsource\DataHandler;
use \myagsource\Report\Content\Table\TableData;

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
	 * herd_page_access
	 * @var HerdPageAccess
	 **/
	protected $herd_page_access;
	
	/**
	 * sections
	 * @var Sections
	 **/
	protected $sections;
	
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
	
	/**
	 * supp_factory
	 * 
	 * Supplemental factory
	 * @var \myagsource\Supplemental\Content\SupplementalFactory
	 **/
	protected $supp_factory;
	
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
	 * report_data
	 * 
	 * @var Array
	 **/
	protected $report_data;

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
		//set up herd
		$this->load->model('herd_model');
		$this->herd_access = new HerdAccess($this->herd_model);
		$this->herd = new Herd($this->herd_model, $this->session->userdata('herd_code'));

		//is someone logged in?
		if(!$this->as_ion_auth->logged_in() && $this->herd->herdCode() != $this->config->item('default_herd')) {
			$this->post_message("Please log in.  ");
		}
		
		//is a herd selected?
		if(!$this->herd->herdCode() || $this->herd->herdCode() == ''){
			$this->post_message("Please select a herd and try again.  ");
		}
		
		//does logged in user have access to selected herd?
		$has_herd_access = $this->herd_access->hasAccess($this->session->userdata('user_id'), $this->herd->herdCode(), $this->session->userdata('arr_regions'), $this->ion_auth_model->getTaskPermissions());
		if(!$has_herd_access){
			$this->post_message("You do not have permission to access this herd.  Please select another herd and try again.  ");
		}
		
				
		// report content
		$this->load->model('supplemental_model');
		$this->load->model('ReportContent/report_block_model');
		$this->load->model('Datasource/db_field_model');
		$this->supp_factory = new SupplementalFactory($this->supplemental_model, site_url());
		$this->blocks = new Blocks($this->report_block_model, $this->db_field_model, $this->supp_factory);

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

	//redirects while retaining message and conditionally setting redirect url
	//@todo: needs to be a part of some kind of authorization class
	protected function post_message($message = ''){
		$this->session->keep_flashdata('redirect_url');
		$this->load->view('echo.php', ['text' => $message]);
//		exit;
	}

	/*
	 * ajax_report: Called via AJAX to populate graphs
	 * @param string page path
	 * @param string block name
	 * @param string sort by
	 * @param string sort order
	 * @param int report count
	 * @param string serialized filter data
	 * @param string cache_buster: text to make page appear as a different page so that new data is retrieved
	 * @todo: can I delete the last param?
	 */
	public function ajax_report($page_path, $block_name, $sort_by = 'null', $sort_order = 'null', $report_count=0, $json_filter_data = NULL, $cache_buster = NULL) {//, $herd_size_code = FALSE, $all_breeds_code = FALSE
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
		$this->herd_page_access = new HerdPageAccess($this->herd_model, $this->herd, $this->page);
		$this->page_access = new PageAccess($this->page, $this->as_ion_auth->has_permission("View All Content"));
		if(!$this->page_access->hasAccess($this->herd_page_access->hasAccess())) {
			$this->post_message('You do not have permission to view the requested report for herd ' . $this->herd->herdCode() . '.  Please select a report from the navigation or contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' if you have questions or concerns.');
			return;
		}
		
		$block = $this->blocks->getByPath(urldecode($block_name));
		$output = $block->displayType();
		
		//SORT
		$sort_builder = new SortBuilder($this->report_block_model);
		$sort_builder->build($block, $sort_by, $sort_order);
		//END SORT

		//FILTERS
		if(isset($json_filter_data)){
			$section = $this->section;
			$arr_params = (array)json_decode(urldecode($json_filter_data));
			if(isset($arr_params['csrf_test_name']) && $arr_params['csrf_test_name'] != $this->security->get_csrf_hash()){
				die("I don't recognize your browser session, your session may have expired, or you may have cookies turned off.");
			}
			unset($arr_params['csrf_test_name']);
		
			//prep data for filter library
			$this->load->model('filter_model');
			//load required libraries
			$filters = new Filters($this->filter_model);
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
				if(!$block->isSummary()){
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
		$block->setFilters($filters);
		//END FILTERS
		
		// block-level supplemental data
		$block_supp = $this->supp_factory->getBlockSupplemental($block->id(), $this->supplemental_model, site_url());
		$this->supplemental = $block_supp->getContent();
		//end supplemental

		// benchmarks
		$this->load->model('setting_model');
		$herd_info = $this->herd_model->header_info($this->herd->herdCode());
		$this->load->model('benchmark_model');
		$this->benchmarks = new Benchmarks($this->session->userdata('user_id'), $this->input->post('herd_code'), $herd_info, $this->setting_model, $this->benchmark_model, $this->session->userdata('benchmarks'));
		// end benchmarks
			
		// report data
		$this->load->model('ReportContent/report_data_model');
		$this->load->model('Datasource/db_table_model');
		$db_table = new DbTable($block->primaryTableName (), $this->db_table_model);

		// Load the most specific data-handling library that exists
		$tmp_path = 'libraries/' . $page_path . '/' . $block_name;
		$data_handler = new DataHandler();
		$block_data_handler = $data_handler->load($block, $tmp_path, $this->report_data_model, $db_table, $this->benchmarks);
		//End load data-handling library

		$results = $block_data_handler->getData($filters->criteriaKeyValue());//$report_count, 
		// end report data
		
		/*
		$first = ($first === 'true');
		if($file_format == 'csv' || $file_format == 'pdf'){
			if($first){
				$this->_record_access(90, $file_format, $this->config->item('product_report_code'));
			}
			return $results;
		}
		*/

		//Handle table headers for table blocks
		if($block->displayType() == 'table'){
			//table header
			$header_groups = $this->report_block_model->getHeaderGroups($block->id());
			
			//@todo: pull this only when needed? move adjustHeaderGroups to TableBlock or TableHeader class
			$arr_dates = $this->herd_model->get_test_dates_7_short($this->session->userdata('herd_code'));
			$header_groups = TableHeader::mergeDateIntoHeader($header_groups, $arr_dates);
			
			$block->setTableHeader($results, $this->supp_factory, $header_groups);
			unset($supp_factory);
		}
		$this->report_data = $block->getOutputData();
		$this->report_data['herd_code'] = $this->session->userdata('herd_code');
		if($block->hasBenchmark()){
			$this->report_data['benchmark_text'] = $this->benchmarks->get_bench_text();
		}

		$this->report_data['data'] = $results;
		
		if(isset($this->supplemental) && !empty($this->supplemental)){
			$this->report_data['supplemental'] = $this->supplemental;
		}

		if($block->displayType() == 'table'){
			$this->report_data['table_header'] = $this->load->view('table_header', $block->getTableHeaderData($report_count), TRUE);
			//finish table
			/*
			 * @todo: when we have a javascript framework in place, we will send table data via json too.
			 * for now, we need to send the html for the table instead of the data
			 */
				$this->report_data['html'] = $this->load->view('report_table.php', $this->report_data, TRUE);
				unset($this->report_data['data'],$this->report_data['table_header']);
		}

		//@todo: base header on accept property of request header 
		$return_val = json_encode($this->report_data);//, JSON_HEX_QUOT | JSON_HEX_TAG); //json_encode_jsfunc
		header("Content-type: application/json"); //being sent as json
		header("Cache-Control: no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		$this->load->view('echo.php', ['text' => $return_val]);
	}
}
