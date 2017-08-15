<?php
/*
require_once(APPPATH . 'libraries/Benchmarks/Benchmarks.php');
require_once APPPATH . 'controllers/report_parent.php';
require_once(APPPATH . 'libraries/Filters/ReportFilters.php');
require_once(APPPATH . 'libraries/Products/Products/Products.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');
*/
require_once(APPPATH . 'libraries/dhi/PdfArchives.php');
require_once(APPPATH . 'controllers/dpage.php');

use myagsource\Benchmarks\Benchmarks;
use myagsource\Filters\ReportFilters;
use myagsource\Products\Products\Products;
use myagsource\dhi\Herd;
use myagsource\dhi\PdfArchives;
use \myagsource\Site\WebContent\WebBlockFactory;
use \myagsource\Site\WebContent\Page;
use \myagsource\dhi\HerdPageAccess;
use \myagsource\Site\WebContent\PageAccess;

defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends dpage {

	function __construct(){
		parent::__construct();
	}

	protected function _archivePDFs(){
        if($this->permissions->hasPermission('View All Content') || $this->permissions->hasPermission('View Archived Reports')){

            $this->load->model('dhi/pdf_archive_model');
            try{
                $PdfArchives = new PdfArchives($this->pdf_archive_model, $this->herd->herdCode());
                if($this->permissions->hasPermission('View All Content')){
                    $PdfArchives->setAllHerdArchives();
                }
                else{
                    $PdfArchives->setSubscribedHerdArchives();
                }
                return $PdfArchives;
            }
            catch(\Exception $e){
                $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
            }
        }
        return null;
    }

	//dashboard
	function index(){
        $page_id = 74; //page_id for dashboard page

        $params = [];
        if(isset($json_filter_data)) {
            $params = (array)json_decode(urldecode($json_filter_data));
        }

        $supplemental_factory = $this->_supplementalFactory();
        $this->filters = $this->_filters($page_id, $params);
        $benchmarks = $this->_benchmarks();
        $this->load->model('ReportContent/report_data_model');
        $block_content = $this->_blockContent($page_id, $supplemental_factory, $params, $benchmarks, $this->report_data_model);
        $tmp = $this->_archivePDFs();
        if(isset($tmp)){
            $block_content[] = $tmp;
            unset($tmp);
        }

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


        //other products
        $this->load->model('product_model');
        $products_factory = new Products($this->product_model, $this->herd, $this->permissions->permissionsList());
        $missing_products = $products_factory->inaccessibleProducts();
        if(isset($missing_products) && is_array($missing_products) && count($missing_products) > 0){
            $this->data['widget']['herd'][] = [
                'content' => $this->load->view('auth/dashboard/other_products', ['products' => $missing_products], true),
                'title' => 'Maximize Your Profitability'
            ];
        }

		if($this->permissions->hasPermission('Update SG Access')){
			$consultants_by_status = $this->as_ion_auth->getConsultantsByHerd($this->herd->herdCode());
			if(isset($consultants_by_status['open']) && is_array($consultants_by_status['open'])){
				$this->data['widget']['herd'][] = [
					'content' => $this->_set_consult_section($consultants_by_status['open'], 'open', array('Grant Access', 'Deny Access')),
					'title' => 'Open Consultant Requests'
				];
			}
		}
		
/*		$product_data = Array('sections' => $this->as_ion_auth->get_promo_sections());
		if(isset($product_data['sections']) && !empty($product_data['sections'])){
			$this->data['widget']['herd'][] = array(
				'content' => $this->load->view('auth/dashboard/other_products', $product_data, TRUE),
				'title' => 'Other Products'
			);
		}
*/
		$this->load->view('auth/dashboard/main', $this->data);
	}
	
	function _set_consult_section($data, $key, $arr_submit_options){
	//this code is also used in auth/_set_consult_section
			if(isset($data) && is_array($data)){
			$this->section_data = [
				'arr_submit_options' => $arr_submit_options,
				'attributes' => array('class' => $key . ' consult-form'),
			];
			foreach($data as $h) {
				$h['is_editable'] = TRUE;
				$this->section_data['arr_records'][] = $this->load->view('auth/service_grp/service_grp_line', $h, TRUE);
			}
			//add disclaimer field for when producer can grant access
			if($key === 'open') {
				$this->section_data['disclaimer'] = [
					'name' => 'disclaimer',
					'id' => 'disclaimer',
					'type' => 'checkbox',
					'value' => '1',
					'checked' => FALSE,
					'class' => 'required',
				];
				$this->section_data['disclaimer_text'] = ' I understand that if I grant a consultant access to my herd&apos;s information, that consultant will be able to use any animal and herd summary data through their own ' . $this->config->item('product_name') . ' account. This consultant will not have access to my account information. An email will be sent to the consultant to inform them whether access has been granted or denied, and include any expiration date that is specified above.</p><p>Because relationships with consultants change over time, it is highly recommended that you do not share your login information with any consultant.';
			}
			//vars are cached between view loads, so we need to include the disclaimer var even when it shouldn't be set
			else {
				$this->section_data['disclaimer'] = NULL;
			}
			return $this->load->view('auth/service_grp/service_grp_section', $this->section_data, TRUE);
		}
	}
}
