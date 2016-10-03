<?php
//namespace myagsource;
require_once(APPPATH . 'core/MY_Api_Controller.php');
require_once(APPPATH . 'libraries/Filters/ReportFilters.php');
require_once(APPPATH . 'libraries/Benchmarks/Benchmarks.php');
require_once(APPPATH . 'libraries/AccessLog.php');
require_once(APPPATH . 'libraries/Supplemental/Content/SupplementalFactory.php');
require_once(APPPATH . 'libraries/dhi/HerdAccess.php');
require_once(APPPATH . 'libraries/dhi/HerdPageAccess.php');
require_once(APPPATH . 'libraries/Site/WebContent/WebBlockFactory.php');
require_once(APPPATH . 'libraries/Site/WebContent/PageAccess.php');
require_once(APPPATH . 'libraries/Site/WebContent/Page.php');
require_once(APPPATH . 'libraries/Datasource/DbObjects/DbTableFactory.php');
require_once(APPPATH . 'libraries/DataHandler.php');

require_once(APPPATH . 'libraries/Report/Content/ReportFactory.php');
require_once(APPPATH . 'libraries/Form/Content/FormFactory.php');


use \myagsource\Benchmarks\Benchmarks;
use \myagsource\AccessLog;
use \myagsource\Filters\ReportFilters;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\dhi\HerdAccess;
use \myagsource\dhi\HerdPageAccess;
use \myagsource\Site\WebContent\Page;
use \myagsource\Site\WebContent\WebBlockFactory;
use \myagsource\Site\WebContent\PageAccess;
use \myagsource\Report\Content\ReportFactory;
use \myagsource\DataHandler;
use \myagsource\Datasource\DbObjects\DbTableFactory;
use \myagsource\Api\Response\ResponseMessage;
use \myagsource\Form\Content\FormFactory;
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
	 * page
	 * @var Page
	 **/
	protected $page;
	
    /**
     * filters
     *
     * Filters object
     * @var Filters
     **/
    protected $filters;

    /**
	 * message
	 * 
	 * @var Array (Strings)
	 **/
	protected $message = [];

	function __construct(){
		parent::__construct();

        if(!$this->as_ion_auth->logged_in()) {
            $this->sendResponse(401);
        }

        if(!isset($this->herd)){
            $this->sendResponse(400,  new ResponseMessage('Please select a herd and try again.', 'error'));
        }

        //is someone logged in?
		if($this->herd->herdCode() != $this->config->item('default_herd')){
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

		/* Load the profile.php config file if it exists*/
		if (ENVIRONMENT == 'development' || ENVIRONMENT == 'localhost') {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			}
		}
	}
	
	function index($page_id, $json_filter_data = null){
        //supplemental factory
        $this->load->model('supplemental_model');
        $supplemental_factory = new SupplementalFactory($this->supplemental_model, site_url());

        //Set up site content objects
        $this->load->model('web_content/page_model', null, false, $this->session->userdata('user_id'));
        $this->load->model('web_content/block_model');
        $web_block_factory = new WebBlockFactory($this->block_model);

        //filters
        $params = [];
        if(isset($json_filter_data)) {
            $params = (array)json_decode(urldecode($json_filter_data));
        }
        $this->load->model('filter_model');
        $this->filters = new ReportFilters($this->filter_model, $page_id, ['herd_code' => $this->session->userdata('herd_code')] + $params);
        $this->load->model('Forms/setting_model', null, false, ['user_id'=>$this->session->userdata('user_id'), 'herd_code'=>$this->session->userdata('herd_code')]);
        //end filters

        //benchmarks
        if($this->permissions->hasPermission("Set Benchmarks")){
            $this->load->model('benchmark_model');
            $benchmarks = new Benchmarks($this->session->userdata('user_id'), $this->herd->herdCode(), $this->herd_model->header_info($this->herd->herdCode()), $this->setting_model, $this->benchmark_model, $this->session->userdata('benchmarks'));
        }

        //page content
        $this->load->model('ReportContent/report_block_model');
        $this->load->model('Datasource/db_field_model');
        $this->load->model('ReportContent/report_data_model');
        $this->load->model('Datasource/db_table_model');
        $data_handler = new DataHandler($this->report_data_model, $benchmarks);
        $db_table_factory = new DbTableFactory($this->db_table_model);

		//load factories for block content
		$report_factory = new ReportFactory($this->report_block_model, $this->db_field_model, $this->filters, $supplemental_factory, $data_handler, $db_table_factory);
		$setting_form_factory = new FormFactory($this->setting_model, $supplemental_factory);

//this will actually be passed from client
//$params = ['key_value' => 1];
$params = ['ID' => 2911100, 'serial_num' => '366']; //for events

        $this->load->model('Forms/Data_entry_model', null, false, $params + ['herd_code'=>$this->session->userdata('herd_code')]);
		$entry_form_factory = new FormFactory($this->Data_entry_model, $supplemental_factory);

        //create block content
        $reports = $report_factory->getByPage($page_id);
        $setting_forms = $setting_form_factory->getByPage($page_id);
        $entry_forms = $entry_form_factory->getByPage($page_id);

        //combine and sort
        $block_content = $reports + $setting_forms + $entry_forms;
        ksort($block_content);
        unset($report_factory, $setting_form_factory, $entry_form_factory, $reports, $setting_forms, $entry_forms);

        //create blocks for content
        $blocks = $web_block_factory->getBlocksFromContent($page_id, $block_content);

        $this->load->model('web_content/page_model');
        $page_data = $this->page_model->getPage($page_id);
		$this->page = new Page($page_data, $blocks, $supplemental_factory, $this->filters, $benchmarks);

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