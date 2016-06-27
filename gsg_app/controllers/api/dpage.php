<?php
//namespace myagsource;
require_once(APPPATH . 'core/MY_Api_Controller.php');
require_once(APPPATH . 'libraries/filters/Filters.php');
require_once(APPPATH . 'libraries/Form/Content/FormFactory.php');
require_once(APPPATH . 'libraries/Benchmarks/Benchmarks.php');
require_once(APPPATH . 'libraries/AccessLog.php');
require_once(APPPATH . 'libraries/Supplemental/Content/SupplementalFactory.php');
require_once(APPPATH . 'libraries/dhi/HerdAccess.php');
require_once(APPPATH . 'libraries/dhi/HerdPageAccess.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');
require_once(APPPATH . 'libraries/Site/WebContent/Pages.php');
require_once(APPPATH . 'libraries/Site/WebContent/Blocks.php');
require_once(APPPATH . 'libraries/Site/WebContent/PageAccess.php');

require_once(APPPATH . 'libraries/Report/Content/Blocks.php');
require_once(APPPATH . 'libraries/Report/Content/Chart/ChartBlock.php');
require_once(APPPATH . 'libraries/Report/Content/Table/TableBlock.php');


use \myagsource\Benchmarks\Benchmarks;
use \myagsource\AccessLog;
use \myagsource\report_filters\Filters;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\dhi\HerdAccess;
use \myagsource\dhi\HerdPageAccess;
use \myagsource\dhi\Herd;
use \myagsource\Form\Content\FormFactory;
use \myagsource\Site\WebContent\Page;
use \myagsource\Site\WebContent\Blocks as SiteBlocksFactory;
use \myagsource\Site\WebContent\Block as SiteBlock;
use \myagsource\Site\WebContent\PageAccess;
use \myagsource\Report\Content\Blocks as ReportBlocksFactory;
use \myagsource\Report\iBlock;
use \myagsource\Api\Response\ResponseMessage;

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

class dpage extends MY_Api_Controller {
	/**
	 * herd_access
	 * @var HerdAccess
	 **/
	protected $herd_access;
	
	/**
	 * page_access
	 * @var PageAccess
	 **/
	protected $page_access;
	
	/**
	 * herd_page_access
	 * @var HerdPageAccess
	 **/
	protected $herd_page_access;
	
	/**
	 * section
	 * @var Section
	 **/
	protected $section;
	
	/**
	 * section_path
	 * @var string
	 **/
	protected $full_section_path;
	
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
	 * site_blocks_factory
	 * 
	 * SiteBlock repository
	 * @var SiteBlocksFactory
	 **/
	protected $site_blocks_factory;

    /**
     * report_blocks_factory
     *
     * ReportBlock repository
     * @var ReportBlocksFactory
     **/
    protected $report_blocks_factory;

    /**
     * form factory
     *
     * Form repository
     * @var FormFactory
     **/
    protected $form_factory;

    /**
	 * herd
	 * 
	 * Herd object
	 * @var Herd
	 **/
	protected $herd;

	/**
	 * message
	 * 
	 * @var Array (Strings)
	 **/
	protected $message = [];

    /**
     * benchmarks
     *
     * @var Benchmarks object
     **/
    protected $benchmarks;

	//	protected $report_form_id;
	protected $arr_sort_by = [];
	protected $arr_sort_order = [];
	protected $product_name;
	protected $report_path;
	//protected $primary_model_name;
	protected $page_header_data;
	protected $filters; //filters object
	protected $print_all = FALSE;
	protected $bool_is_summary;
	protected $supplemental_factory;
	
	function __construct(){
		parent::__construct();
        //DO WE HAVE A HERD AND AN ACTIVE USER?
		//set up herd
		$this->load->model('herd_model');
		$this->herd_access = new HerdAccess($this->herd_model);
		$this->herd = new Herd($this->herd_model, '35130320');//$this->session->userdata('herd_code'));

		//is someone logged in?
		if($this->herd->herdCode() != $this->config->item('default_herd')){
			if(!$this->as_ion_auth->logged_in()) {
                $this->sendResponse(401);
			}
			
			//is a herd selected?
			if(!$this->herd->herdCode() || $this->herd->herdCode() == ''){
                $this->sendResponse(400,  new ResponseMessage('Please select a herd and try again.', 'error'));
			}
			
			//does logged in user have access to selected herd?
			$has_herd_access = $this->herd_access->hasAccess($this->session->userdata('user_id'), $this->herd->herdCode(), $this->session->userdata('arr_regions'), $this->permissions->permissionsList());
			if(!$has_herd_access){
                $this->sendResponse(403,  new ResponseMessage('You do not have permission to access this herd.  Please select another herd and try again.', 'error'));
			}
		}

		/* Load the profile.php config file if it exists
		if (ENVIRONMENT == 'development' || ENVIRONMENT == 'localhost') {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			} 
		}*/
	}
	
	function index($page_id){
        //supplemental factory
        $this->load->model('supplemental_model');
        $this->supplemental_factory = new SupplementalFactory($this->supplemental_model, site_url());

        //Set up site content objects
        $this->load->model('web_content/page_model', null, false, $this->session->userdata('user_id'));
        $this->load->model('web_content/block_model');
        $this->site_blocks_factory = new SiteBlocksFactory($this->block_model);

        // report content
        $this->load->model('ReportContent/report_block_model');
        $this->load->model('Datasource/db_field_model');
        $this->report_blocks_factory = new ReportBlocksFactory($this->report_block_model, $this->db_field_model, $this->supplemental_factory, $this->site_blocks_factory);

        //@todo: filter form will sometimes be submitted
        //filters
        $this->load->model('filter_model');
        $this->filters = new Filters($this->filter_model, $page_id, ['herd_code' => $this->herd->herdCode()]);
        $this->load->model('setting_model', null, false, ['user_id'=>$this->session->userdata('user_id'), 'herd_code'=>$this->session->userdata('herd_code')]);
        //benchmarks
        if($this->permissions->hasPermission("Set Benchmarks")){
            $this->load->model('benchmark_model');
            $this->benchmarks = new Benchmarks($this->session->userdata('user_id'), $this->herd->herdCode(), $this->herd_model->header_info($this->herd->herdCode()), $this->setting_model, $this->benchmark_model, $this->session->userdata('benchmarks'));
        }

        $this->form_factory = new FormFactory($this->setting_model, $this->supplemental_factory);

        $this->load->model('web_content/page_model');
        $page_data = $this->page_model->getPage($page_id);
		$this->page = new Page($page_data, $this->site_blocks_factory, $this->form_factory, $this->supplemental_factory, $this->filters, $this->benchmarks);

        //does user have access to current page for selected herd?
        $this->herd_page_access = new HerdPageAccess($this->page_model, $this->herd, $this->page);
        $this->page_access = new PageAccess($this->page, ($this->permissions->hasPermission("View All Content") || $this->permissions->hasPermission("View All Content-Billed")));
        if(!$this->page_access->hasAccess($this->herd_page_access->hasAccess())) {
            $this->sendResponse(403, new ResponseMessage('You do not have permission to view the requested report for herd ' . $this->herd->herdCode() . '.  Please select a report from the navigation', 'error'));
        }
        //the user can access this page for this herd, but do they have to pay?
        if($this->permissions->hasPermission("View All Content-Billed")){
            $this->message[] = new ResponseMessage('Herd ' . $this->herd->herdCode() . ' is not paying for this product.  You will be billed a monthly fee for any month in which you view content for which the herd is not paying.', 'message');
        }

        $this->sendResponse(200, $this->message, $this->page->toArray());
	}

    protected function reportContent($block_id){ //$json_filter_data, $sort_by, $sort_order
        $block = $this->report_blocks->getBlock($block_id);

        //SORT
        $sort_builder = new SortBuilder($this->report_block_model);
        $sort_builder->build($block, $sort_by, $sort_order);
        //END SORT

        //FILTERS
        if(isset($json_filter_data)){
            $section = $this->section;
            $arr_params = (array)json_decode(urldecode($json_filter_data));

            //prep data for filter library
            $this->load->model('filter_model');
            //load required libraries
            $filters = new Filters($this->filter_model, $this->page->id(), ['herd_code' => $this->session->userdata('herd_code')] + $arr_params);
            //$this->load->helper('multid_array_helper');
            $filters->setCriteria();

            /*
             * myagsource special case: if PAGE filters or params contain only a pstring of 0, and the block is not a summary
            * Needed for pages that contain both cow level and summary reports.
			*/
            if($filters->criteriaExists('pstring') && !$block->isSummary()){
                $p_value = $filters->getCriteriaValueByKey('pstring');
                if(count($p_value) === 1 && $p_value[0] === 0){
                    $filters->removeCriteria('pstring');
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
        $this->load->model('setting_model', null, false, ['user_id'=>$this->session->userdata('user_id'), 'herd_code'=>$this->session->userdata('herd_code')]);
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
        $return_val = json_encode($this->report_data);
    }

    protected function _record_access($event_id, $format, $product_code = null){
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
			$product_code,
			$format,
			$this->page->id(),
			'',//$this->reports->sortTextBrief($this->arr_sort_by, $this->arr_sort_order),
			$filter_text
		);
	}
}