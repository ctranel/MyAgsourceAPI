<?php
//namespace myagsource;
require_once(APPPATH . 'controllers/dpage.php');
require_once APPPATH . 'libraries/Site/WebContent/WebBlockFactory.php';
require_once APPPATH . 'libraries/Listings/Content/ListingFactory.php';
require_once(APPPATH . 'libraries/Supplemental/Content/SupplementalFactory.php');
require_once(APPPATH . 'libraries/Site/WebContent/Page.php');
require_once(APPPATH . 'libraries/dhi/HerdPageAccess.php');
require_once(APPPATH . 'libraries/Site/WebContent/PageAccess.php');

use \myagsource\Site\WebContent\WebBlockFactory;
use \myagsource\Listings\Content\ListingFactory;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\Site\WebContent\Page;
use \myagsource\dhi\HerdPageAccess;
use \myagsource\Site\WebContent\PageAccess;

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

class listings extends dpage {
	function __construct(){
		parent::__construct();

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

        //page content
        $this->load->model('ReportContent/report_block_model');
        //$this->load->model('Datasource/db_field_model');
        //$this->load->model('ReportContent/report_data_model');
        //$this->load->model('Datasource/db_table_model');
        //$data_handler = new DataHandler($this->report_data_model, null);
        //$db_table_factory = new DbTableFactory($this->db_table_model);

		//load factories for block content
		//$report_factory = new ReportFactory($this->report_block_model, $this->db_field_model, $this->filters, $supplemental_factory, $data_handler, $db_table_factory);
		//$setting_form_factory = new FormFactory($this->setting_model, $supplemental_factory);

        $this->load->model('Listings/herd_options_model', null, false, ['herd_code'=>$this->session->userdata('herd_code')]);
		$option_listing_factory = new ListingFactory($this->herd_options_model);

        //create block content
        //$reports = $report_factory->getByPage($page_id);
        //$setting_forms = $setting_form_factory->getByPage($page_id);
        $listings = $option_listing_factory->getByPage($page_id);

        //combine and sort
        //$block_content = $reports + $setting_forms + $entry_forms;
        //ksort($block_content);
        //unset($report_factory, $setting_form_factory, $entry_form_factory, $reports, $setting_forms, $entry_forms);

        //create blocks for content
        $blocks = $web_block_factory->getBlocksFromContent($page_id, $listings);

        $this->load->model('web_content/page_model');
        $page_data = $this->page_model->getPage($page_id);
        $this->page = new Page($page_data, $blocks, $supplemental_factory, $this->filters, null);

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
}