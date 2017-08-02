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

require_once APPPATH . 'libraries/Listings/Content/ListingFactory.php';
require_once(APPPATH . 'libraries/Report/Content/ReportFactory.php');
require_once(APPPATH . 'libraries/Form/Content/FormDisplayFactory.php');
require_once(APPPATH . 'libraries/Settings/Form/SettingsFormDisplayFactory.php');
require_once(APPPATH . 'libraries/dhi/Animal.php');


use \myagsource\Benchmarks\Benchmarks;
use \myagsource\AccessLog;
use \myagsource\Filters\ReportFilters;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\dhi\HerdAccess;
use \myagsource\dhi\HerdPageAccess;
use \myagsource\Site\WebContent\Page;
use \myagsource\Site\WebContent\WebBlockFactory;
use \myagsource\Site\WebContent\PageAccess;
use \myagsource\DataHandler;
use \myagsource\Datasource\DbObjects\DbTableFactory;
use \myagsource\Api\Response\ResponseMessage;
use \myagsource\Settings\Form\SettingsFormDisplayFactory;
use \myagsource\Listings\Content\ListingFactory;
use \myagsource\Report\Content\ReportFactory;
use \myagsource\Form\Content\FormDisplayFactory;
use \myagsource\dhi\Animal;

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

        if(!$this->session->userdata('user_id')) {
            $this->sendResponse(401);
        }

        if(!$this->session->userdata('herd_code')){
            $this->sendResponse(400,  new ResponseMessage('Please select a herd and try again.', 'error'));
        }

        //is someone logged in?
		/*if($this->herd->herdCode() != $this->config->item('default_herd')){
			//is a herd selected?
			if(!$this->herd->herdCode() || $this->herd->herdCode() == ''){
                $this->sendResponse(400,  new ResponseMessage('Please select a herd and try again.', 'error'));
			}
			*/
			//does logged in user have access to selected herd?
			$has_herd_access = $this->herd_access->hasAccess($this->session->userdata('user_id'), $this->herd->herdCode(), $this->session->userdata('arr_regions'), $this->permissions->permissionsList());
			if(!$has_herd_access){
                $this->sendResponse(403,  new ResponseMessage('You do not have permission to access this herd.  Please select another herd and try again.', 'error'));
			}
		//}

		/* Load the profile.php config file if it exists*/
		if (ENVIRONMENT == 'development' || ENVIRONMENT == 'localhost') {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			}
		}
	}

	protected function _supplementalFactory(){
        //supplemental factory
        $this->load->model('supplemental_model');
        return new SupplementalFactory($this->supplemental_model, site_url());
    }
    protected function _filters($page_id, $params){
        $this->load->model('filter_model');
        return new ReportFilters($this->filter_model, $page_id, ['herd_code' => $this->session->userdata('herd_code')] + $params, $this->settings);
    }
    protected function _benchmarks(){
        if($this->permissions->hasPermission("Set Benchmarks")){
            $this->load->model('Settings/benchmark_model');//, null, false, ['user_id' => $this->session->userdata('user_id'), 'herd_code' => $this->session->userdata('herd_code')]);
            return new Benchmarks($this->benchmark_model, $this->session->userdata('user_id'), $this->herd->herdCode(), $this->herd_model->header_info($this->herd->herdCode()), $this->session->userdata('benchmarks'));
        }

        return null;
    }
    protected function _blockContent($page_id, $supplemental_factory, $params, $benchmarks, $data_handler_model){
        $this->load->model('ReportContent/report_block_model');
        $this->load->model('Datasource/db_field_model');
        $this->load->model('Datasource/db_table_model');
        $data_handler = new DataHandler($data_handler_model, $benchmarks, (bool)$this->herd->isMetric());//true
        $db_table_factory = new DbTableFactory($this->db_table_model);

        //load factories for block content
        $report_factory = new ReportFactory($this->report_block_model, $this->db_field_model, $this->filters, $supplemental_factory, $data_handler, $db_table_factory);

        $this->load->model('Listings/herd_options_model');
        $option_listing_factory = new ListingFactory($this->herd_options_model);//, $params + ['herd_code'=>$this->session->userdata('herd_code')]);

        $this->load->model('Forms/setting_form_model');//, null, false, ['user_id'=>$this->session->userdata('user_id'), 'herd_code'=>$this->session->userdata('herd_code')]);
        $setting_form_factory = new SettingsFormDisplayFactory($this->setting_form_model, $supplemental_factory, $params + ['herd_code'=>$this->session->userdata('herd_code'), 'user_id'=>$this->session->userdata('user_id')]);

        $this->load->model('Forms/Data_entry_model');//, null, false, $params + ['herd_code'=>$this->session->userdata('herd_code')]);
        //Can't edit inactive animals
        $editable = true;
        if(isset($params['serial_num'])){
            $this->load->model('dhi/animal_model');
            $editable = Animal::isActive($this->animal_model, $this->session->userdata('herd_code'), $params['serial_num']);
        }

        $entry_form_factory = new FormDisplayFactory($this->Data_entry_model, $supplemental_factory, $params + ['herd_code'=>$this->session->userdata('herd_code'), 'user_id'=>$this->session->userdata('user_id')]);

        //create block content
        $reports = $report_factory->getByPage($page_id, (bool)$this->herd->isMetric());//true
        $setting_forms = $setting_form_factory->getByPage($page_id);
        $entry_forms = $entry_form_factory->getByPage($page_id, $this->session->userdata('herd_code'), $editable);
        //$serial_num = isset($params['serial_num']) ? $params['serial_num'] : null;
        $listings = $option_listing_factory->getByPage($page_id, $params + ['herd_code'=>$this->session->userdata('herd_code')]);//, 'serial_num'=>$serial_num

        //combine and sort
        $block_content = $reports + $setting_forms + $entry_forms + $listings;
        ksort($block_content);
        return $block_content;
    }
	
	function index($page_id, $json_filter_data = null){
        $params = [];
        if(isset($json_filter_data)) {
            $params = array_filter((array)json_decode(urldecode($json_filter_data)));
        }

        $supplemental_factory = $this->_supplementalFactory();
        $this->filters = $this->_filters($page_id, $params);
        $benchmarks = $this->_benchmarks();
        $this->load->model('ReportContent/report_data_model');
        $block_content = $this->_blockContent($page_id, $supplemental_factory, $params, $benchmarks, $this->report_data_model);

        //Set up site content objects
        $this->load->model('web_content/page_model', null, false, $this->session->userdata('user_id'));
        $this->load->model('web_content/block_model');
        $web_block_factory = new WebBlockFactory($this->block_model, $supplemental_factory);

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

    function todo($page_id, $json_filter_data = null){
        $report_date = date('Y-m-d');

        //supplemental factory
        $this->load->model('supplemental_model');
        $supplemental_factory = new SupplementalFactory($this->supplemental_model, site_url());

        //Set up site content objects
        $this->load->model('web_content/page_model', null, false, $this->session->userdata('user_id'));
        $this->load->model('web_content/block_model');
        $web_block_factory = new WebBlockFactory($this->block_model, $supplemental_factory);

        //filters
        $params = [];
        if(isset($json_filter_data)) {
            $params = (array)json_decode(urldecode($json_filter_data));
        }

        //added look-ahead days to view, but will keep this around to make sure it works
        //convert look-ahead days to date ranges
        //make sure we have a value
        /*if(isset($params['look_ahead_days']) && !empty($params['look_ahead_days'])){
            //$params['look_ahead_days'] = $this->settings->getValue('look_ahead_days');
            $params['expires_date']['dbfrom'] = date('Y-m-d');
            $params['expires_date']['dbto'] = date('Y-m-d');
            $tmp = strtotime("+" . $params['look_ahead_days'] . " day");
            $params['target_date']['dbfrom'] = date('Y-m-d', $tmp);
            $params['target_date']['dbto'] = date('Y-m-d', $tmp);
        }
        unset($params['look_ahead_days']); */
        $params['report_options'] = ($page_id == 103) ? 2 : ($page_id == 106 ? 1 : null);

        $this->filters = $this->_filters($page_id, $params);
        $this->load->model('Forms/setting_form_model');//, null, false, ['user_id'=>$this->session->userdata('user_id'), 'herd_code'=>$this->session->userdata('herd_code')]);
        //end filters

        $benchmarks = $this->_benchmarks();

        //create blocks for content
        $this->load->model('dhi/todo_list_model', null, false, $this->settings);
        $block_content = $this->_blockContent($page_id, $supplemental_factory, $params, $benchmarks, $this->todo_list_model);
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

    function protocol($page_id, $protocol_id, $json_filter_data = null){
        $report_date = date('Y-m-d');

        //supplemental factory
        $this->load->model('supplemental_model');
        $supplemental_factory = new SupplementalFactory($this->supplemental_model, site_url());

        //Set up site content objects
        $this->load->model('web_content/page_model', null, false, $this->session->userdata('user_id'));
        $this->load->model('web_content/block_model');
        $web_block_factory = new WebBlockFactory($this->block_model, $supplemental_factory);

        //filters
        $params = [];
        if(isset($json_filter_data)) {
            $params = (array)json_decode(urldecode($json_filter_data));
        }
        if(isset($protocol_id) && !empty($protocol_id)){
            $params['protocol_id'] = $protocol_id;
        }

        //added look-ahead days to view, but will keep this around to make sure it works
        //convert look-ahead days to date ranges
        //make sure we have a value
        /*if(isset($params['look_ahead_days']) && !empty($params['look_ahead_days'])){
            //$params['look_ahead_days'] = $this->settings->getValue('look_ahead_days');
            $params['expires_date']['dbfrom'] = date('Y-m-d');
            $params['expires_date']['dbto'] = date('Y-m-d');
            $tmp = strtotime("+" . $params['look_ahead_days'] . " day");
            $params['target_date']['dbfrom'] = date('Y-m-d', $tmp);
            $params['target_date']['dbto'] = date('Y-m-d', $tmp);
        }
        unset($params['look_ahead_days']); */

        $this->filters = $this->_filters($page_id, $params);
        $this->load->model('Forms/setting_form_model');//, null, false, ['user_id'=>$this->session->userdata('user_id'), 'herd_code'=>$this->session->userdata('herd_code')]);
        //end filters

        $benchmarks = $this->_benchmarks();

        //create blocks for content
        $this->load->model('dhi/todo_list_model', null, false, $this->settings);
        $block_content = $this->_blockContent($page_id, $supplemental_factory, $params, $benchmarks, $this->todo_list_model);
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