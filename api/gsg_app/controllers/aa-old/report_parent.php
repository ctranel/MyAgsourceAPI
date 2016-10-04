<?php
//namespace myagsource;
require_once(APPPATH . 'libraries/Filters/ReportFilters.php');
require_once(APPPATH . 'libraries/Benchmarks/Benchmarks.php');
require_once(APPPATH . 'libraries/AccessLog.php');
require_once(APPPATH . 'libraries/Supplemental/Content/SupplementalFactory.php');
require_once(APPPATH . 'libraries/dhi/HerdAccess.php');
require_once(APPPATH . 'libraries/dhi/HerdPageAccess.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');
require_once(APPPATH . 'libraries/Site/WebContent/SectionFactory.php');
require_once(APPPATH . 'libraries/Site/WebContent/PageFactory.php');
require_once(APPPATH . 'libraries/Site/WebContent/WebBlockFactory.php');
require_once(APPPATH . 'libraries/Page/Content/FormBlock/FormBlockactory.php');
require_once(APPPATH . 'libraries/Site/WebContent/PageAccess.php');
require_once(APPPATH . 'libraries/Page/Content/Csv.php');
//require_once(APPPATH . 'libraries/Page/Content/Pdf.php');
require_once(APPPATH . 'libraries/ErrorPage.php');

use \myagsource\Benchmarks\Benchmarks;
use \myagsource\AccessLog;
use \myagsource\Filters\ReportFilters;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\dhi\HerdAccess;
use \myagsource\dhi\HerdPageAccess;
use \myagsource\dhi\Herd;
use \myagsource\Site\WebContent\SectionFactory;
use \myagsource\Site\WebContent\PageFactory;
use \myagsource\Site\WebContent\WebBlockFactory;
use \myagsource\Page\Content\FormBlock\FormBlockFactory;
use \myagsource\Site\WebContent\Block as PageBlock;
use \myagsource\Site\WebContent\PageAccess;
use \myagsource\Page\Content\Csv;
use \myagsource\ErrorPage;

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

abstract class report_parent extends MY_Controller {
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
	 * blocks
	 * 
	 * Block repository
	 * @var blocks
	 **/
	protected $blocks;

	/**
	 * form_factory
	 *
	 * Form Factory
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
	protected $supplemental;
    protected $supplemental_factory;

	function __construct(){
		parent::__construct();
		
		//set redirect, this handles keeping flashdata when appropriate
		$redirect_url = set_redirect_url($this->uri->uri_string(), $this->session->userdata('redirect_url'));
		$this->session->set_userdata('redirect_url', $redirect_url);
		
		//set up herd
		$this->load->model('herd_model');
		$this->herd_access = new HerdAccess($this->herd_model);
		$this->herd = new Herd($this->herd_model, $this->session->userdata('herd_code'));

		//is someone logged in?
		if($this->herd->herdCode() != $this->config->item('default_herd')){
			if(!$this->as_ion_auth->logged_in()) {
				$this->redirect(site_url('auth/login'), "Please log in.");
			}
			
			//is a herd selected?
			if(!$this->herd->herdCode() || $this->herd->herdCode() == ''){
				$this->redirect(site_url('dhi/change_herd/select'), "Please select a herd and try again.");
			}
			
			//does logged in user have access to selected herd?
			$has_herd_access = $this->herd_access->hasAccess($this->session->userdata('user_id'), $this->herd->herdCode(), $this->session->userdata('arr_regions'), $this->permissions->permissionsList());
			if(!$has_herd_access){
				$this->redirect(site_url('dhi/change_herd/select'),"You do not have permission to access this herd.  Please select another herd and try again.");
			}
		}
		$this->load->model('web_content/section_model');
		$this->load->model('web_content/page_model', null, false, $this->session->userdata('user_id'));
		$this->load->model('web_content/block_model');
        $this->load->model('Forms/setting_form_model', null, false, ['user_id'=>$this->session->userdata('user_id'), 'herd_code'=>$this->session->userdata('herd_code')]);
		$this->blocks = new WebBlockFactory($this->block_model);
        //supplemental factory
        $this->load->model('supplemental_model');
        $this->supplemental_factory = new SupplementalFactory($this->supplemental_model, site_url());

		$this->form_factory = new FormFactory($this->setting_form_model, $this->blocks, $this->supplemental_factory);
		$this->page_factory = new PageFactory($this->page_model, $this->blocks, $this->form_factory);
		$section_factory = new SectionFactory($this->section_model, $this->page_factory);
		
		$class_dir = $this->router->fetch_directory(); //this should match the name of this file (minus ".php".  Also used as base for css and js file names and model directory name
		$class = $this->router->fetch_class();
		if($class === 'index'){
			$class = '';
		}
		$method = $this->router->fetch_method();
		$this->full_section_path = $class_dir . $class;
		if(substr($this->full_section_path, -1) === '/'){
			$this->full_section_path = substr($this->full_section_path, 0, -1);
		}
		//load sections
		$this->section = $section_factory->getByPath($class . '/');
		$this->session->set_userdata('section_id', $this->section->id());
		$section_factory->loadChildren($this->section, $this->page_factory, $this->session->userdata('user_id'), $this->herd, $this->permissions->permissionsList());

		$path = uri_string();

		if(strpos($path, $method) === false){
			$method = $this->section->defaultPagePath();
		}

		$arr_path = explode('/',$path);
		$page_name = $method;
		$block_name = '';

		$this->page = $this->page_factory->getByPath($page_name, $this->section->id());
		//if page is not found, display 404
		if(!$this->page){
			$page_header_data = [
				'title'=>'Page Not Found',
				'page_heading'=>'Page Not Found',
				'navigation' => $this->load->view('navigation', [], TRUE),
			];
		
			$page_header = $this->load->view('page_header', $page_header_data, TRUE);
			$page_footer = $this->load->view('page_footer', NULL, TRUE);
			$error_page = new ErrorPage(APPPATH, $page_header, $page_footer, 'error_404');
			$error_page ->show_404($this->uri->uri_string());
			exit;
		}
		$this->report_path = $this->full_section_path . '/' . $this->page->path();

		//does user have access to current page for selected herd?
		$this->herd_page_access = new HerdPageAccess($this->page_model, $this->herd, $this->page);
		$this->page_access = new PageAccess($this->page, ($this->permissions->hasPermission("View All Content") || $this->permissions->hasPermission("View All Content-Billed")));
		if(!$this->page_access->hasAccess($this->herd_page_access->hasAccess())) {
            $this->redirect(site_url(), 'You do not have permission to view the requested report for herd ' . $this->herd->herdCode() . '.  Please select a report from the navigation or contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' if you have questions or concerns.');
		}
        //the user can access this page for this herd, but do they have to pay?
        if($this->permissions->hasPermission("View All Content-Billed")){
            $this->message[] = 'Herd ' . $this->herd->herdCode() . ' is not paying for this product.  You will be billed a monthly fee for any month in which you view content for which the herd is not paying.';
        }

		$this->page_header_data['num_herds'] = $this->herd_access->getNumAccessibleHerds($this->session->userdata('user_id'), $this->permissions->permissionsList(), $this->session->userdata('arr_regions'));
				
		/* Load the profile.php config file if it exists*/
		if (ENVIRONMENT == 'development' || ENVIRONMENT == 'localhost') {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			} 
		}
	}
	
	function index(){
		$this->redirect(site_url($this->report_path));
	}

	function display($arr_block_in, $display_format = NULL, $sort_by = NULL, $sort_order = NULL){
		//Check for valid herd_code
		if(!$this->herd){
			$this->redirect(site_url($this->report_path), 'Please select a valid herd.');
		}

        if(is_array($this->session->flashdata('message'))){
            $this->message = $this->message + $this->session->flashdata('message');
        }

		$arr_blocks = $this->blocks->getByPage($this->page->id());
		$this->page->loadChildren($arr_blocks);
				
		//FILTERS
		$this->load->model('filter_model');
		$this->filters = new ReportFilters($this->filter_model, $this->page->id(), ['herd_code' =>	$this->herd->herdCode()]);
		//END FILTERS
/*
		if ($display_format == 'pdf' && !is_null($arr_block_in)) {
			$ci_pdf = new Ci_pdf();
			$pdf = new Pdf($ci_pdf);
			//@todo: parameters
			$table_header = new TableHeader();
			$data = array();
			$herd_data = $this->herd_model->header_info($this->herd->herdCode());
			$i = 0;

			if(isset($arr_blocks) && is_array($arr_blocks)){
				foreach($arr_blocks as $pb){
					if($pb['display_type'] == 'table'){
						continue;
					}
					if(($arr_block_in !== NULL && in_array($pb->path(), $arr_block_in)) || $arr_block_in == NULL){
					//SORT
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

						$this->{$this->primary_model_name}->populate_field_meta_arrays($pb['id']);
						$block[$i]['data'] = $this->ajax_report(urlencode($this->page->path()), urlencode($pb->path()), urlencode($sort_by), $sort_order, 'pdf', NULL);
						$tmp_pdf_width = $this->{$this->primary_model_name}->get_pdf_widths(); 
						$block[$i]['arr_pdf_widths'] = $tmp_pdf_width;
						$arr_header_data = $this->{$this->primary_model_name}->get_fields(); // was $model
						$block[$i]['header_structure'] = $this->table_header->get_table_header_array($arr_header_data, $tmp_pdf_width);
						$block[$i]['title'] = $pb['description'];
						$i++;
					}
				}
			}
			$this->_record_access(90, 'pdf', $this->herd_page_access->reportCodes());
			$this->reports->create_pdf($block, $this->product_name, NULL, $herd_data, 'P');
			exit;
		}
*/
		// render page
		//get_herd_data
		$herd_data = $this->herd_model->header_info($this->herd->herdCode());

		//set js lines and load views for each block to be displayed on page
		$tmp_js = '';
		$arr_view_blocks = NULL;
		if(isset($arr_blocks) && !empty($arr_blocks)){
			$x = 0;
			$consec_charts = 0;
			$cnt = $arr_blocks->count();
			$curr = null;
			$next = null;

			$arr_blocks->rewind();
			$curr = $arr_blocks->current();
			$arr_blocks->next();
			if($arr_blocks->valid()){
				$next = $arr_blocks->current();
			}

			while($curr instanceof PageBlock){
				if($arr_block_in == NULL || in_array($curr->path(), $arr_block_in)){
					//set up next iteration
					$display_type = $curr->displayType();
					$next_display_type = (isset($next) && $next instanceof PageBlock) ? $next->displayType() : null;
					if(strpos($display_type, 'chart') !== false && strpos($next_display_type, 'chart') === false && $consec_charts === 0){
						$odd_even = 'chart-only';
					}
					else{
						if($consec_charts % 2 == 1) $odd_even = 'chart-even';
						elseif($x == ($cnt - 1)) $odd_even = 'chart-last-odd';
						else $odd_even = 'chart-odd';
					}
					//set up next iteration
					if($display_type === 'table'){
						$consec_charts = 0;
					}
					if(strpos($display_type, 'chart') !== false){
						$consec_charts++;
					}
					$arr_blk_data = array(
							'block_num' => $x,
							'block_csv_url_encoded' => site_url('csv/blocks/csv') . '/' . urlencode(str_replace('/', '|', $this->full_section_path) . '|' . $this->page->path()) . '/' . $curr->path() . '/null/null/',
//							'form_id' => $this->report_form_id,
							'block' => $curr->path(),
							'odd_even' => $odd_even,
					);
					$arr_view_blocks[] = $this->load->view($curr->displayType(), $arr_blk_data, TRUE);
					//add js line to populate the block after the page loads
					$tmp_js .= "head.ready('graph_helper.js', updateBlock('block-canvas$x', '" . $curr->path() . "', '$x', 'null', 'null','false'));\n";
					$tmp_js .= "if ($( '#datepickfrom' ).length > 0) $( '#datepickfrom' ).datepick({dateFormat: 'mm-dd-yyyy'});";
					$tmp_js .= "if ($( '#datepickto' ).length > 0) $( '#datepickto' ).datepick({dateFormat: 'mm-dd-yyyy'});";
					$tmp_block = $curr->path();
					$x++;
						
				}
				$curr = $next;
				$arr_blocks->next();
				$next = $arr_blocks->current();
			}
		}
		
		//set up page header
		$this->carabiner->css('chart.css');
		$this->carabiner->css('boxes.css');
		$this->carabiner->css('https://cdn.jsdelivr.net/qtip2/2.2.0/jquery.qtip.min.css', 'screen');
		$this->carabiner->css('tooltip.css');
		$this->carabiner->css('popup.css');
		$this->carabiner->css('tabs.css');
		$this->carabiner->css('report.css');
		$this->carabiner->css('expandable.css');
		$this->carabiner->css('chart.css', 'print');
		$this->carabiner->css('report.css', 'print');
		$this->carabiner->css($this->full_section_path . '.css', 'screen');
		if($this->filters->displayFilters()){
			//$this->carabiner->css('filters.css', 'screen');
			$this->carabiner->css('agsource.datepick.css', 'screen');
			$this->carabiner->css('jquery.datetimeentry.css', 'screen');
		}
		else{
			$this->carabiner->css('hide_filters.css', 'screen');
		}
		if(!$this->page->hasBenchmark()){
			$this->carabiner->css('hide_benchmarks.css', 'screen');
		}
		
		if(is_array($this->page_header_data)){
			$arr_blocks->rewind();

			$this->page_header_data = array_merge($this->page_header_data,
				[
					'title'=>$this->product_name . ' - ' . $this->config->item('site_title'),
					'description'=>$this->product_name . ' - ' . $this->config->item('site_title'),
					'message' => $this->message,// + $this->{$this->primary_model_name}->arr_messages,
					'navigation' => $this->load->view('navigation', [], TRUE),
					'page_heading' => $this->product_name . " for Herd " . $this->herd->herdCode(),
					'arr_head_line' => array(
						'<script type="text/javascript">',
						'	var page = "' . $this->page->path() . '";',
						'	var page_url = "' . $this->report_path . '";',
						'	var site_url = "' . site_url() . '";',
						'	var herd_code = "' . $this->herd->herdCode() . '";',
						'	var block = "' . $arr_blocks->current()->name()	. '"',
						'</script>'
					),
					'arr_headjs_line'=>array(
						'{highcharts: "https://code.highcharts.com/4.1.7/highcharts.js"}',
						'{highcharts_more: "https://code.highcharts.com/4.1.7/highcharts-more.js"}',
						'{exporting: "https://code.highcharts.com/4.1.7/modules/exporting.js"}',
						'{regression: "' . $this->config->item("base_url_assets") . 'js/charts/high_regression.js"}',
						'{popup: "' . $this->config->item("base_url_assets") . 'js/jquery/popup.min.js"}',
						'{chart_options: "' . $this->config->item("base_url_assets") . 'js/charts/chart_options.js"}',
						'{graph_helper: "' . $this->config->item("base_url_assets") . 'js/charts/graph_helper.js"}',
						'{report_helper: "' . $this->config->item("base_url_assets") . 'js/report_helper.js"}',
						'{table_sort: "' . $this->config->item("base_url_assets") . 'js/jquery/stupidtable.min.js"}',
						'{tooltip: "https://cdn.jsdelivr.net/qtip2/2.2.0/jquery.qtip.min.js"}',
						'{datepick: "' . $this->config->item("base_url_assets") . 'js/jquery/jquery.datepick.min.js"}'
					)
				]
			);
			//load the report-specific js file if it exists
			if(file_exists(PROJ_DIR . '/' . 'js' . '/' . $this->full_section_path . '_helper.js')){
				$this->page_header_data['arr_headjs_line'][] = '{inv_helper: "' . $this->config->item("base_url_assets") . 'js/' . $this->full_section_path . '_helper.js"}';
			}
			$this->page_header_data['arr_headjs_line'][] = 'function(){' . $tmp_js . ';}';
		}
		//unset($this->{$this->primary_model_name}->arr_messages); //clear message var once it is displayed

		$arr_nav_data = [
			'section_path' => $this->full_section_path,
			'curr_page' => $this->page->path(),
			'obj_pages' => $this->section->pages(),
		];

		$this->page_footer_data = [];
		$report_filter_path = 'filters';
		if(file_exists(APPPATH . 'views/' . $this->full_section_path . '/filters.php')){
			$report_filter_path =  $this->full_section_path . '/filters' . $report_filter_path;
		}

		$data = [
			'page_header' => $this->load->view('page_header', $this->page_header_data, TRUE),
			'herd_code' => $this->herd->herdCode(),
			'herd_data' => $this->load->view('dhi/herd_info', $herd_data, TRUE),
			'page_footer' => $this->load->view('page_footer', $this->page_footer_data, TRUE),
			'blocks' => $arr_view_blocks,
			'print_all' => $this->print_all,
			'report_path' => $this->report_path
		];
		
		$arr_filter_data = [
			//'arr_filters' => $this->filters->filter_list(),
			'arr_filters' => $this->filters->toArray(),
		];
		if(isset($arr_filter_data)){
			$collapse_data['content'] = $this->load->view($report_filter_path, $arr_filter_data, TRUE);
			$collapse_data['title'] = 'Set Filters';
			$collapse_data['id'] = 'filters';
			$data['filters'] = $this->load->view('collapsible', $collapse_data, TRUE);
		}
		
		$this->load->model('supplemental_model');
		$supp_factory = new SupplementalFactory($this->supplemental_model, site_url());
		$page_supp = $supp_factory->getPageSupplemental($this->page->id());
		$data['page_supplemental'] = $page_supp->getContent();

        if($this->permissions->hasPermission("Set Benchmarks")){
            $this->load->model('Forms/setting_form_model', null, false, ['user_id'=>$this->session->userdata('user_id'), 'herd_code'=>$this->session->userdata('herd_code')]);
            $this->load->model('Settings/benchmark_model');

            $this->benchmarks = new Benchmarks($this->session->userdata('user_id'), $this->input->post('herd_code'), $this->herd_model->header_info($this->herd->herdCode()), $this->setting_form_model, $this->benchmark_model, $this->session->userdata('benchmarks'));
            $arr_benchmark_data = $this->benchmarks->getFormData($this->session->userdata('benchmarks'));
            if(isset($arr_benchmark_data)){
                $collapse_data['content'] = $this->load->view('dhi/settings/benchmarks', $arr_benchmark_data, TRUE);
                $collapse_data['title'] = 'Set Benchmarks';
                $collapse_data['id'] = 'bench-div';
                $data['benchmarks'] = $this->load->view('collapsible', $collapse_data, TRUE);
            }
        }

		$this->_record_access(90, 'web', $this->herd_page_access->reports());
		$this->load->view('report', $data);
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