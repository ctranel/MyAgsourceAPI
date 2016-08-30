<?php
//namespace myagsource;
require_once(APPPATH . 'libraries/Filters/ReportFilters.php');
require_once(APPPATH . 'libraries/Benchmarks/Benchmarks.php');
require_once(APPPATH . 'libraries/AccessLog.php');
require_once(APPPATH . 'libraries/Supplemental/Content/SupplementalFactory.php');
require_once(APPPATH . 'libraries/dhi/HerdAccess.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');
//require_once(APPPATH . 'libraries/Site/WebContent/PageFactory.php');
require_once(APPPATH . 'libraries/Site/WebContent/Blocks.php');
require_once(APPPATH . 'libraries/Page/Content/Csv.php');
require_once(APPPATH . 'libraries/Page/Content/Pdf.php');
require_once(APPPATH . 'libraries/Notifications/Notifications.php');

use \myagsource\Benchmarks\Benchmarks;
use \myagsource\AccessLog;
use \myagsource\Filters\ReportFilters;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\dhi\HerdAccess;
use \myagsource\dhi\Herd;
//use \myagsource\Site\WebContent\PageFactory;
use \myagsource\Page\Content\Csv;

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

class blocks extends MY_Controller {
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
	 * page_factory
	 * 
	 * page repository
	 * @var PageFactory
	protected $page_factory;
**/
	
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
		$this->session->keep_all_flashdata();

		$this->load->model('herd_model');
		$this->load->model('web_content/block_model');
		$this->blocks = new Blocks($this->block_model);
		$this->herd_access = new HerdAccess($this->herd_model);
		$this->herd = new Herd($this->herd_model, $this->session->userdata('herd_code'));
		$this->page_header_data['num_herds'] = $this->herd_access->getNumAccessibleHerds($this->session->userdata('user_id'), $this->permissions->permissionsList(), $this->session->userdata('arr_regions'));
		$this->page_header_data['navigation'] = $this->load->view('navigation', [], TRUE);
		
		
		/* Load the profile.php config file if it exists*/
		if (ENVIRONMENT == 'development' || ENVIRONMENT == 'localhost') {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			} 
		}
	}

	function index(){
		//keep flashdata is in constructor
		redirect(site_url($this->report_path));
	}

	function get($arr_block_in, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
		//Check for valid herd_code
		if(!$this->herd){
			//keep flashdata is in constructor
			$this->session->set_flashdata('message', ['Please select a valid herd.']);
			redirect(site_url($this->report_path));
		}
		
		$arr_blocks = $this->blocks->getByPage($this->page->id());
		$this->page->loadChildren($arr_blocks);

		//FILTERS
		//Determine if any report blocks have is_summary flag - will determine if pstring needs to be loaded and filters shown
		//@todo make pstring 0 work on both cow and summary reports simultaneously
		$this->load->model('filter_model');
		$this->filters = new ReportFilters($this->filter_model, $this->page->id(), ['herd_code' =>	$this->herd->herdCode()]);
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
			$this->_record_access(90, 'csv', null);
		}
		else {
			$this->{$this->primary_model_name}->arr_messages[] = 'There is no data to export into an Excel file.';
		}
		exit;
	}

	protected function _record_access($event_id, $format, $products = null){
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
			$format,
			$this->page->id(),
			'',//$this->reports->sortTextBrief($this->arr_sort_by, $this->arr_sort_order),
			$filter_text
		);
	}
}
