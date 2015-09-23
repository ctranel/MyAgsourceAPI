<?php
require_once(APPPATH . 'libraries/Benchmarks/Benchmarks.php');
require_once APPPATH . 'controllers/report_parent.php';
require_once(APPPATH . 'libraries/filters/Filters.php');

use \myagsource\Benchmarks\Benchmarks;
use \myagsource\report_filters\Filters;

defined('BASEPATH') OR exit('No direct script access allowed');

class Index extends report_parent {
	protected $page_header_data;
	protected $footer_data;
	protected $data;
	
	function __construct(){
		parent::__construct();
		if(!isset($this->as_ion_auth) || $this->session->userdata('herd_code') === FALSE){
			$this->session->keep_flashdata('redirect_url');
			redirect(site_url('auth/login'));
		}
		$this->page_header_data['num_herds'] = $this->herd_access->getNumAccessibleHerds($this->session->userdata('user_id'), $this->as_ion_auth->arr_task_permissions(), $this->session->userdata('arr_regions'));
		$this->page_header_data['navigation'] = $this->load->view('navigation', [], TRUE);
		
		$this->load->library('form_validation');
		/* Load the profile.php config file if it exists */
		if (ENVIRONMENT == 'development' || ENVIRONMENT == 'localhost') {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			} 
		}
	}

	//dashboard
	function index(){
		//FILTERS
		//load required libraries
		$this->load->model('filter_model');
		$this->filters = new Filters($this->filter_model);
		$recent_test_date = isset($primary_table) ? $this->{$this->primary_model_name}->getRecentDates() : NULL;
		$this->filters->setCriteria(
				$this->section->id(),
				$this->page->path(),
				['herd_code' =>	$this->session->userdata('herd_code')], //filter form submissions never trigger a new page load (i.e., this function is never fired by a form submission)
				'herd_code'
		);
		//END FILTERS
		
		$this->page_header_data['message'] = $this->session->flashdata('message');

		//get web content generated reports
		//$this->objPage = $this->{$this->primary_model_name}->arr_blocks[$this->page->path()];
		//$arr_blocks = $this->objPage['blocks'];
		$arr_blocks = $this->blocks->getByPage($this->page->id());
		
		//set js lines and load views for each block to be displayed on page
		$arr_block_in = NULL;
		$tmp_js = '';
		$arr_view_blocks = NULL;
		$has_benchmarks = false;
		if(isset($arr_blocks) && !empty($arr_blocks)){
			$x = 0;
			$cnt = count($arr_blocks);
			foreach($arr_blocks as $c => $pb){
				//if($pb['bench_row'] === 1){
//					$has_benchmarks = $pb->displayBenchRow();
				//}
		
				$display = 'table';//$pb->display_type();
				//load view for placeholder for block display
/*				if(isset($sort_by) && isset($sort_order)){
					$this->arr_sort_by = array_values(explode('|', $sort_by));
					$this->arr_sort_order = array_values(explode('|', $sort_order));
				}
				else {
					$tmp = $this->{$this->primary_model_name}->get_default_sort($pb['path']);
					$this->arr_sort_by = $tmp['arr_sort_by'];
					$this->arr_sort_order = $tmp['arr_sort_order'];
					$sort_by = implode('|', $this->arr_sort_by);
					$sort_order = implode('|', $this->arr_sort_order);
				}
*/				if($arr_block_in == NULL || in_array($pb['path'], $arr_block_in)){
//					$this->{$this->primary_model_name}->populate_field_meta_arrays($pb['id']);
					//if($cnt == 1) $odd_even = 'chart-only';
					//elseif($x % 2 == 1) $odd_even = 'chart-even';
					//elseif($x == ($cnt - 1)) $odd_even = 'chart-last-odd';
					//else $odd_even = 'chart-odd';
					if($display == 'table') $cnt = 0;
					$arr_blk_data = array(
							'block_num' => $x,
							'link_url' => site_url($this->full_section_path) . '/' . $this->page->path() . '/' . $pb->path(),
//							'form_id' => $this->report_form_id,
							//'odd_even' => $odd_even,
							'block' => $pb->path(),
							//'sort_by' => urlencode($sort_by),
							//'sort_order' => urlencode($sort_order),
							'skip_heading' => true,
					);
					$arr_view_blocks[$pb->name()] = $this->load->view($display, $arr_blk_data, TRUE);
					//add js line to populate the block after the page loads
					$tmp_js .= "updateBlock(\"block-canvas$x\", \"" . $pb->path() . "\", \"$x\", \"null\", \"null\",\"false\");\n";//, \"" . $this->{$this->primary_model_name}->arr_blocks[$this->page]['display'][$display][$block]['description'] . "\", \"" . $bench_text . "\");\n";
					$tmp_js .= "if ($( '#datepickfrom' ).length > 0) $( '#datepickfrom' ).datepick({dateFormat: 'mm-dd-yyyy'});";
					$tmp_js .= "if ($( '#datepickto' ).length > 0) $( '#datepickto' ).datepick({dateFormat: 'mm-dd-yyyy'});";
					$tmp_block = $pb->path();
					$x++;
				}
			}
		}
		//end web content generated reports

		
		// Select modules for logged in user
		$this->carabiner->css('dashboard.css', 'screen');
		$this->carabiner->css('benchmarks.css');
		$this->carabiner->css('chart.css');
		$this->carabiner->css('boxes.css');
		$this->carabiner->css('popup.css');
		$this->carabiner->css('tabs.css');
		$this->carabiner->css('report.css');
		$this->carabiner->css('expandable.css');
		$this->carabiner->css('chart.css', 'print');
		$this->carabiner->css('report.css', 'print');
		if($this->filters->displayFilters()){
			//$this->carabiner->css('filters.css', 'screen');
			$this->carabiner->css('agsource.datepick.css', 'screen');
			$this->carabiner->css('jquery.datetimeentry.css', 'screen');
		}
		else{
			$this->carabiner->css('hide_filters.css', 'screen');
		}
		
		if(is_array($this->page_header_data)){
			$this->page_header_data = array_merge($this->page_header_data,
				array(
					'title'=>'Dashboard - ' . $this->config->item("product_name"),
					'description'=>'Dashboard for ' . $this->config->item("product_name"),
					'arr_head_line' => array(
							'<script type="text/javascript">',
							'	var page = "' . $this->page->path() . '";',
							'	var page_url = "' . $this->report_path . '";',
							'	var site_url = "' . site_url() . '";',
							'	var herd_code = "' . $this->session->userdata('herd_code') . '";',
							'	var block = "' . $tmp_block	. '"',
							'</script>'
					),
					'arr_headjs_line'=>array(
						'{highcharts: "https://cdnjs.cloudflare.com/ajax/libs/highcharts/3.0.7/highcharts.js"}',
						'{highcharts_more: "https://cdnjs.cloudflare.com/ajax/libs/highcharts/3.0.7/highcharts-more.js"}',
						'{exporting: "https://cdnjs.cloudflare.com/ajax/libs/highcharts/3.0.7/modules/exporting.js"}',
						'{popup: "' . $this->config->item("base_url_assets") . 'js/jquery/popup.min.js"}',
						'{chart_options: "' . $this->config->item("base_url_assets") . 'js/charts/chart_options.js"}',
						'{graph_helper: "' . $this->config->item("base_url_assets") . 'js/charts/graph_helper.js"}',
						'{report_helper: "' . $this->config->item("base_url_assets") . 'js/report_helper.js"}',
						'{table_sort: "' . $this->config->item("base_url_assets") . 'js/jquery/stupidtable.min.js"}',
						'{tooltip: "https://cdn.jsdelivr.net/qtip2/2.2.0/jquery.qtip.min.js"}',
						'{datepick: "' . $this->config->item("base_url_assets") . 'js/jquery/jquery.datepick.min.js"}',
						'{helper: "' . $this->config->item("base_url_assets") . 'js/as_dashboard_helper.js"}'
					)
				)
			);
			$this->page_header_data['arr_headjs_line'][] = 'function(){' . $tmp_js . ';}';
		}

		//header and footer
		$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
		$this->data['page_footer'] = $this->load->view('page_footer', $this->footer_data, TRUE);
//		$this->load->_ci_cached_vars = array();
		//widgets (pull from DB?)
		//get_herd_data
		$herd_data = $this->herd_model->header_info($this->session->userdata('herd_code'));
		$this->data['widget']['herd'][] = array(
			'content' => $this->load->view('auth/dashboard/herd_data', $herd_data, TRUE),
			'title' => 'Herd Data'
		);

		$this->data['widget']['herd'][] = array(
			'content' => $this->load->view('auth/dashboard/message', null, true),
			'title' => 'Message'
		);

		$this->load->model('setting_model');
		$this->load->model('benchmark_model');
		$this->benchmarks = new Benchmarks($this->session->userdata('user_id'), $this->input->post('herd_code'), $this->herd_model->header_info($this->herd->herdCode()), $this->setting_model, $this->benchmark_model, $this->session->userdata('benchmarks'));
		$arr_benchmark_data = $this->benchmarks->getFormData($this->session->userdata('benchmarks')); 
		if(isset($arr_benchmark_data)){
			$this->data['widget']['herd'][] = array(
				'content' => $this->load->view('dhi/settings/benchmarks', $arr_benchmark_data, TRUE),
				'title' => 'Benchmarks',
				'id' => 'benhmarks',
			);
		}
		
		//filters	
		$report_filter_path = 'filters';
		if(file_exists(APPPATH . 'views/' . $this->full_section_path . '/filters.php')){
			$report_filter_path =  $this->full_section_path . '/filters';
		}

		$arr_filter_data = array(
				'arr_filters' => $this->filters->toArray(),
		);

		if($this->filters->displayFilters()){
			$this->data['widget']['herd'][] = array(
				'content' => $this->load->view($report_filter_path, $arr_filter_data, TRUE),
				'title' => 'Filters',
				'id' => 'filters',
			);
		}
		
		foreach($arr_view_blocks as $k => $b){
			$col = $k === 'Herd Performance Overview' ? 'feature' : 'info';
			$this->data['widget'][$col][] = array(
				'content' => $b,
				'title' => $k,
			);
		}
		

		if($this->as_ion_auth->has_permission('Update SG Access')){
			$consultants_by_status = $this->as_ion_auth->getConsultantsByHerd($this->session->userdata('herd_code'));
			if(isset($consultants_by_status['open']) && is_array($consultants_by_status['open'])){
				$this->data['widget']['herd'][] = array(
					'content' => $this->_set_consult_section($consultants_by_status['open'], 'open', array('Grant Access', 'Deny Access')),
					'title' => 'Open Consultant Requests'
				);
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
			$this->section_data = array(
				'arr_submit_options' => $arr_submit_options,
				'attributes' => array('class' => $key . ' consult-form'),
			);
			foreach($data as $h) {
				$h['is_editable'] = TRUE;
				$this->section_data['arr_records'][] = $this->load->view('auth/service_grp/service_grp_line', $h, TRUE);
			}
			//add disclaimer field for when producer can grant access
			if($key === 'open') {
				$this->section_data['disclaimer'] = array(
					'name' => 'disclaimer',
					'id' => 'disclaimer',
					'type' => 'checkbox',
					'value' => '1',
					'checked' => FALSE,
					'class' => 'required',
				);
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
