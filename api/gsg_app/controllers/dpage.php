<?php
//namespace myagsource;
require_once(APPPATH . 'core/MY_Api_Controller.php');
require_once(APPPATH . 'libraries/Filters/ReportFilters.php');
require_once(APPPATH . 'libraries/Page/Content/FormBlock/FormBlockFactory.php');
require_once(APPPATH . 'libraries/Benchmarks/Benchmarks.php');
require_once(APPPATH . 'libraries/AccessLog.php');
require_once(APPPATH . 'libraries/Supplemental/Content/SupplementalFactory.php');
require_once(APPPATH . 'libraries/dhi/HerdAccess.php');
require_once(APPPATH . 'libraries/dhi/HerdPageAccess.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');
require_once(APPPATH . 'libraries/Site/WebContent/PageFactory.php');
require_once(APPPATH . 'libraries/Site/WebContent/WebBlockFactory.php');
require_once(APPPATH . 'libraries/Site/WebContent/PageAccess.php');
require_once(APPPATH . 'libraries/Datasource/DbObjects/DbTableFactory.php');
require_once(APPPATH . 'libraries/DataHandler.php');

require_once(APPPATH . 'libraries/Report/Content/ReportFactory.php');
require_once(APPPATH . 'libraries/Page/Content/ReportBlock/ReportBlockFactory.php');
require_once(APPPATH . 'libraries/Page/Content/ReportBlock/ReportBlock.php');


use \myagsource\Benchmarks\Benchmarks;
use \myagsource\AccessLog;
use \myagsource\Filters\ReportFilters;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\dhi\HerdAccess;
use \myagsource\dhi\HerdPageAccess;
use \myagsource\dhi\Herd;
use \myagsource\Page\Content\FormBlock\FormBlockFactory;
use \myagsource\Site\WebContent\Page;
use \myagsource\Site\WebContent\WebBlockFactory;
use \myagsource\Site\WebContent\PageAccess;
use \myagsource\Report\Content\ReportFactory;
use \myagsource\Page\Content\ReportBlock\ReportBlockFactory;
use \myagsource\DataHandler;
use \myagsource\Datasource\DbObjects\DbTableFactory;
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
	 * page_factory
	 * 
	 * page repository
	 * @var PageFactory
	 **/
	protected $page_factory;
	
	/**
	 * page
	 * @var Page
	 **/
	protected $page;
	
	/**
	 * web_block_factory
	 * 
	 * SiteBlock factory
	 * @var WebBlockFactory
	 **/
	protected $web_block_factory;

    /**
     * report_block_factory
     *
     * ReportBlock repository
     * @var ReportBlockFactory
     **/
    protected $report_block_factory;

	/**
	 * report_factory
	 *
	 * Report Factory
	 * @var ReportFactory
	 **/
	protected $report_factory;

	/**
     * FormBlock factory
     *
     * FormBlock factory
     * @var FormBlockFactory
     **/
    protected $form_block_factory;

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

	protected $filters; //filters object
	protected $supplemental_factory;
	
	function __construct(){
		parent::__construct();
        //DO WE HAVE A HERD AND AN ACTIVE USER?
		//set up herd
		$this->load->model('herd_model');
		$this->herd_access = new HerdAccess($this->herd_model);
		$this->herd = new Herd($this->herd_model, $this->session->userdata('herd_code'));

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
	
	function index($page_id, $json_filter_data = null){
        //supplemental factory
        $this->load->model('supplemental_model');
        $this->supplemental_factory = new SupplementalFactory($this->supplemental_model, site_url());

        //Set up site content objects
        $this->load->model('web_content/page_model', null, false, $this->session->userdata('user_id'));
        $this->load->model('web_content/block_model');
        $this->web_block_factory = new WebBlockFactory($this->block_model);

        //filters
        $params = [];
        if(isset($json_filter_data)) {
            $params = (array)json_decode(urldecode($json_filter_data));
        }
        $this->load->model('filter_model');
        $this->filters = new ReportFilters($this->filter_model, $page_id, ['herd_code' => $this->session->userdata('herd_code')] + $params);
        $this->load->model('setting_model', null, false, ['user_id'=>$this->session->userdata('user_id'), 'herd_code'=>$this->session->userdata('herd_code')]);
        //end filters

        //benchmarks
        if($this->permissions->hasPermission("Set Benchmarks")){
            $this->load->model('benchmark_model');
            $this->benchmarks = new Benchmarks($this->session->userdata('user_id'), $this->herd->herdCode(), $this->herd_model->header_info($this->herd->herdCode()), $this->setting_model, $this->benchmark_model, $this->session->userdata('benchmarks'));
        }

        // report content
        $this->load->model('ReportContent/report_block_model');
        $this->load->model('Datasource/db_field_model');
        $this->load->model('ReportContent/report_data_model');
        $this->load->model('Datasource/db_table_model');
        $data_handler = new DataHandler($this->report_data_model, $this->benchmarks);
        $db_table_factory = new DbTableFactory($this->db_table_model);
		$this->report_factory = new ReportFactory($this->report_block_model, $this->db_field_model, $this->filters, $this->supplemental_factory, $data_handler, $db_table_factory);
        $this->report_block_factory = new ReportBlockFactory($this->report_block_model, $this->web_block_factory, $this->report_factory, $this->supplemental_factory);

        $this->form_block_factory = new FormBlockFactory($this->setting_model, $this->web_block_factory, $this->supplemental_factory);

        $this->load->model('web_content/page_model');
        $page_data = $this->page_model->getPage($page_id);
		$this->page = new Page($page_data, $this->report_block_factory, $this->form_block_factory, $this->supplemental_factory, $this->filters, $this->benchmarks);

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