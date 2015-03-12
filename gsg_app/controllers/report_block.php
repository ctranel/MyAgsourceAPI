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
require_once(APPPATH . 'libraries/Site/WebContent/Sections.php');
require_once(APPPATH . 'libraries/Datasource/DbObjects/DbTable.php');
require_once(APPPATH . 'libraries/Report/Content/BlockData.php');
require_once(APPPATH . 'libraries/Report/Content/Table/Header/TableHeader.php');

use \myagsource\Benchmarks\Benchmarks;
use \myagsource\report_filters\Filters;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\dhi\HerdAccess;
use \myagsource\dhi\Herd;
use \myagsource\Site\WebContent\Sections;
use \myagsource\Site\WebContent\Pages;
use \myagsource\Site\WebContent\Blocks as WebBlocks;
use \myagsource\Report\Content\Blocks;
use \myagsource\Report\Content\BlockData;
use \myagsource\Report\Content\Table\Header\TableHeader;
use \myagsource\Datasource\DbObjects\DbTable;
use myagsource\Report\Content\Sort;

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
	
	/**
	 * herd
	 * 
	 * Herd object
	 * @var Herd
	 **/
	protected $herd;
	
//	protected $arr_sort_by = [];
//	protected $arr_sort_order = [];
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
		
		// report content
		$this->load->model('supplemental_model');
		$this->load->model('ReportContent/report_block_model');
		$this->blocks = new Blocks($this->report_block_model, new SupplementalFactory($this->supplemental_model, site_url()));

		//web content
		$this->load->model('web_content/page_model', null, false, $this->session->userdata('user_id'));
		$this->load->model('web_content/block_model');
		$this->web_blocks = new WebBlocks($this->block_model);
		$this->pages = new Pages($this->page_model, $this->web_blocks);
//		$sections = new Sections($this->section_model, $this->pages);

		
		$this->herd_access = new HerdAccess($this->herd_model);
		$this->herd = new Herd($this->herd_model, $this->session->userdata('herd_code'));
//		$class_dir = $this->router->fetch_directory(); //this should match the name of this file (minus ".php".  Also used as base for css and js file names and model directory name
//		$class = $this->router->fetch_class();
		$method = $this->router->fetch_method();
//		$this->section_path = $class_dir . $class;

		// info used for links within datasets (table header sorts, etc)
		$path = uri_string();
		$arr_path = explode('/',$path);
		$method_index = array_search($method, $arr_path);
		$page_path = $arr_path[$method_index + 1];

		$page_path = str_replace('|', '/', urldecode($page_path));
		$arr_path = array_filter(explode('/', $page_path));
		$path_page_segment = $arr_path[count($arr_path) - 1];
		
		$this->page = $this->pages->getByPath($path_page_segment);
		$this->report_path = $this->section_path . $this->page->path();
		$this->report_form_id = 'report_criteria';//filter-form';

		
		//load most specific model available.  Must load model before setting section
//		$path = uri_string();
//		$arr_path = explode('/',$path);

		
//@todo: fix line below, why is path coming in as 'land' and not 'dhi/'?  redirect?
/*		$this->section_path = str_replace('land', 'dhi/', $this->section_path);
		$this->section = $sections->getByPath($this->section_path);
		$sections->loadChildren($this->section, $this->pages, $this->session->userdata('user_id'), $herd, $this->ion_auth_model->getTaskPermissions());
*/
		if(!$this->authorize($method)) {
			if($this->session->flashdata('message')) $this->session->keep_flashdata('message');
			if($method != 'ajax_report') $this->session->set_flashdata('redirect_url', $this->uri->uri_string());
			redirect(site_url('auth/login'));
		}
		
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
	 * @todo: use post rather than get
	 */
	public function ajax_report($page_path, $block_name, $output, $sort_by = 'null', $sort_order = 'null', $file_format = 'web', $test_date = FALSE, $report_count=0, $json_filter_data = NULL, $first=FALSE, $cache_buster = NULL) {//, $herd_size_code = FALSE, $all_breeds_code = FALSE
		//verify user has permission to view content for given herd
		if(!$this->authorize()) {
			die('not authorized');
		}
		
		$page_path = str_replace('|', '/', urldecode($page_path));
		$arr_path = array_filter(explode('/', $page_path));
		$path_page_segment = $arr_path[count($arr_path) - 1];
		$tmp_path = $page_path . $block_name;
		
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
		//End load model

		
		
		//SUPPLEMENTAL DATA
		$this->load->model('supplemental_model');
		$supp_factory = new SupplementalFactory($this->supplemental_model, site_url());
//set keyed array with all supplemental data for the block (bsf_id as key supplemental, with df_field id for params)?
//All supp data is also included in block field data view.
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
				
		//$this->page = $this->pages->getByPath(urldecode($page_name));

		$block = $this->blocks->getByPath(urldecode($block_name));
		$block->setReportFields($supp_factory);
		$sort_by = urldecode($sort_by);
		//$this->objPage = $this->{$this->primary_model_name}->arr_blocks[$this->page->path()];
				
		//set sort order
		$this->load->helper('report_chart_helper');
		if($sort_by != 'null' && $sort_order != 'null' && !empty($sort_by) && !empty($sort_order)) {
			$arr_sort_by = explode('|', $sort_by);
			$arr_sort_order = explode('|', $sort_order);
			if(isset($arr_sort_order) && is_array($arr_sort_order)){
				$block->resetSort();
				foreach($arr_sort_order as $k=>$s){
					$s = $this->datasource->getField($block->id(), $arr_sort_by[$k]);
					$datafield = new DbField($s['db_field_id'], $s['table_name'], $s['db_field_name'], $s['name'], $s['description'], $s['pdf_width'], $s['default_sort_order'],
						 $s['datatype'], $s['max_length'], $s['decimal_scale'], $s['unit_of_measure'], $s['is_timespan'], $s['is_foreign_key'], $s['is_nullable'], $s['is_natural_sort']);
					$block->addSort($datafield, $arr_sort_order[$k]);
				}
			}
		}
		
		if(isset($json_filter_data)){
			$section = $this->getSection($this->section_path);
			$arr_params = (array)json_decode(urldecode($json_filter_data));
			if(isset($arr_params['csrf_test_name']) && $arr_params['csrf_test_name'] != $this->security->get_csrf_hash()){
				die("I don't recognize your browser session, your session may have expired, or you may have cookies turned off.");
			}
			unset($arr_params['csrf_test_name']);

			/*
			 * MANUALLY ADJUST FILTERS FOR PSTRINGS
			 * @todo: 	find another way to acheive this--without naming specific blocks.  This handles pstring filters for
			* 			cow-level blocks that are on summary pages
			*/
			foreach($arr_params as $k => $v){
				if(($block->path() == 'peak_milk_trends' || $block->path() == 'dim_at_1st_breeding' || $block->path() == 'bulk_tank_contribution') && substr($k,-7)=='pstring'){
					if(is_array($v)){
						$tmp = array_filter($v);
						if(empty($tmp)){
							unset($arr_params[$k]);
						}
					}
					elseif($v == 0){
						unset($arr_params[$k]);
					}
				}
			}
		
		
			//prep data for filter library
			$this->load->model('filter_model');
			//load required libraries
			$this->filters = new Filters($this->filter_model);
//			$primary_table = $this->{$this->primary_model_name}->get_primary_table_name();
			$this->load->helper('multid_array_helper');
			$this->filters->set_filters(
					$section->id(),
					$path_page_segment,
					['herd_code' => $this->session->userdata('herd_code')] + $arr_params
			);
		}
		// block-level supplemental data
		$this->load->model('supplemental_model');
		$block_supp = $supp_factory->getBlockSupplemental($block->id(), $this->supplemental_model, site_url());
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
		$db_table = new DbTable($block->primaryTableName(), $this->db_table_model);
		$block_data = new BlockData($block, $this->report_data_model, $this->benchmarks, $db_table);
		$results = $block_data->loadData($report_count, ['herd_code' => '35690638']);
		// end report data
		
		
		//$this->json['supplemental'] = $this->supplemental;
		//set parameters for given block
		
		//common functionality
		//$first = ($first === 'true');
		if($file_format == 'csv'){
		//	if($first){
		//		$this->_record_access(90, 'csv', $this->config->item('product_report_code'));
		//	}
			return $results;
		}
		elseif($file_format == 'pdf'){
		//	if($first){
		//		$this->_record_access(90, 'pdf', $this->config->item('product_report_code'));
		//	}
			if($output == 'html'){
				return $this->html;
			}
			else {
				return $results;
			}
		}

		if(isset($this->benchmarks)){
			$bench_text = $this->benchmarks->get_bench_text();
		}

		if($output == 'table'){
			$header_groups = $this->report_block_model->getHeaderGroups($block->id());
			//@todo: pull this only when needed?
			$arr_dates = $this->herd_model->get_test_dates_7_short($this->session->userdata('herd_code'));
			$header_groups = $this->adjustHeaderGroups($header_groups, $arr_dates);

			$table_header = new TableHeader($block, $header_groups);
			
			$table_header_data = [
				'structure' => $table_header->getTableHeaderStructure(),
				'form_id' => $this->report_form_id,
				'report_path' => $block->path(),
				'sorts' => $block->sorts(),
				'block' => $block->path(),
				'report_count' => $report_count
			];
			
			$this->json['html'] = $this->html;
			$this->report_data['table_header'] = $this->load->view('table_header', $table_header_data, TRUE);
			$this->report_data['num_columns'] = $table_header->columnCount();
		}

		$this->report_data['block'] = $block;
		$this->report_data['report_data'] = $results;
		
		if(isset($this->supplemental) && !empty($this->supplemental)){
			$this->report_data['supplemental'] = $this->supplemental;
		}
		
		if(isset($bench_text)){
			$this->report_data['table_benchmark_text'] = $bench_text;
		}

		if(isset($this->report_data) && is_array($this->report_data)) {
			$this->json['html'] = $this->load->view('report_table.php', $this->report_data, TRUE);
		}
		else {
			$this->json['html'] = '<p class="message">No data found.</p>';
		}
print($this->json['html']);
//		if($first){
//			$this->_record_access(90, 'web', $this->config->item('product_report_code'));
//		}
		$this->json['section_data'] = [
			'block' => $block_name,
			'sort_by' => $sort_by,
			'sort_order' => $sort_order,
			'graph_order' => $report_count
		];
		$return_val = prep_output($output, $this->json, $report_count, $file_format);
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
	
	protected function adjustHeaderGroups($header_groups, $dates){
		//@todo: KLM block should not be in this class--controller?
		//KLM - Added logic to convert header text to date text from herd_model function get_test_dates_7_short
		foreach($header_groups as $hk => $hv){
			$c = 0;
			if(isset($dates) && is_array($dates)){
				foreach($dates[0] as $key => $value){
//var_dump($header_groups);
					if ($key == $hv['text']) {
						if ($value == '0-0') {
							$value='No Test (-'.$c.')';
						}
//var_dump($header_groups[$hk]);
						$header_groups[$hk]['text'] = $value;
						break;
					}
					$c++;
				}
			}
		}
//var_dump($header_groups);
		return $header_groups;
		//end KLM
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
