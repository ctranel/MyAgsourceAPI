<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/report_parent.php';
class Land extends parent_report {
	function __construct()
	{
		parent::__construct();
	}
	
	function index($pstring = NULL){
		$this->load->model('herd_model');
		$this->load->model('alert_model');
		$arr_pstring = $this->herd_model->get_pstring_array($this->session->userdata('herd_code'));
		if(isset($pstring)){
			$this->session->set_userdata('pstring', $pstring);
		}
		else {
			$pstring = $this->session->userdata('pstring');
			if(!isset($this->pstring) || empty($this->pstring)){
				$tmp = current($arr_pstring);
				$pstring = isset($tmp) && isset($tmp['pstring']) ? $tmp['pstring'] . '' : '0';
				$this->session->set_userdata('pstring', $pstring);
			}
		}
		if(is_array($this->page_header_data)){
			$this->page_header_data = array_merge($this->page_header_data,
				array(
					'title'=>'Dashboard - ' . $this->config->item("product_name"),
					'description'=>'Dashboard for ' . $this->config->item("product_name"),
				)
			);
		}
		$this->carabiner->css('report.css');
		$this->carabiner->css('benchmarks.css');
		$this->footer_data = Array();
		
		//header and footer
		$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
		//		$this->load->_ci_cached_vars = array();
		//widgets (pull from DB?)
		//get_herd_data
		$herd_data = $this->herd_model->header_info($this->session->userdata('herd_code'));
		
		$nav_data = array(
			'arr_pstring' => $arr_pstring
			,'curr_pstring' => $pstring
		);
	
		$this->data = array(
			'page_header' => $this->load->view('page_header', $this->page_header_data, TRUE)
			,'page_heading' => 'My Account'
			,'herd_code' => $this->session->userdata('herd_code')
			,'herd_data' => $this->load->view('herd_info', $herd_data, TRUE)
			,'table_heading' => 'Herd Overview'
			,'page_footer' => $this->load->view('page_footer', $this->footer_data, TRUE)
			,'bench_data' => $this->alert_model->get_benchmarks($this->session->userdata('herd_code'), $pstring)
			,'report_nav' => $this->load->view('auth/dashboard/report_nav', $nav_data, TRUE)
		);
		
//		if((is_array($arr_nav_data['arr_pages']) && count($arr_nav_data['arr_pages']) > 1) || (is_array($arr_nav_data['arr_pstring']) && count($arr_nav_data['arr_pstring']) > 1)) $data['report_nav'] = $this->load->view($report_nav_path, $arr_nav_data, TRUE);
		
		//$this->access_log_model->write_entry($this->{$this->primary_model}->arr_blocks[$this->page]['page_id'], 'web');
		$this->load->view('auth/dashboard/main-bench', $this->data);
		//$this->load->view('report', $data);
	}

	
	
	//dashboard
	function index_dashboard(){
		if(!isset($this->as_ion_auth) || !$this->as_ion_auth->logged_in()){
			$this->session->keep_flashdata('redirect_url');
			redirect(site_url('auth/login'));
		}
		$tmp = $this->session->userdata('herd_code');
		if(!isset($tmp) || empty($tmp)){
			$this->session->keep_flashdata('redirect_url');
			redirect(site_url('change_herd/select'));
		}
		$this->data['message'] = $this->session->flashdata('message');

		//set variables for sample report card
//		$this->load->model('report_card_model');
//		$arr_pstring = $this->herd_model->get_pstring_array($this->session->userdata('herd_code'));
//		$tmp = current($arr_pstring);
//		$pstring = isset($arr_pstring) && is_array($tmp)?$tmp['pstring']:0;
//		$benchmarks_id = $this->report_card_model->get_herd_size_code($this->session->userdata('herd_code'), $pstring);
//		$all_breeds_code = $this->report_card_model->get_all_breeds_code($this->session->userdata('herd_code'), $pstring);
//		$ajax_url = 'ajax_graph/production/' . urlencode($pstring) . '/chart/' . urlencode($benchmarks_id);

		// Select modules for logged in user

		//$this->carabiner->css('jquery.tweet.css', 'screen');
		$this->carabiner->css('jquery.jtweetsanywhere-1.2.0.css', 'screen');
		$this->carabiner->css('dashboard.css', 'screen');
		$this->carabiner->css('accordion.css', 'screen');
		if(is_array($this->page_header_data)){
			$this->page_header_data = array_merge($this->page_header_data,
				array(
					'title'=>'Dashboard - ' . $this->config->item("product_name"),
					'description'=>'Dashboard for ' . $this->config->item("product_name"),
					'arr_headjs_line'=>array(
						'{twitter: "' . $this->config->item("base_url_assets") . 'js/jquery/jquery.jtweetsanywhere-1.2.1.min.js"}',
						'{highcharts: "https://cdnjs.cloudflare.com/ajax/libs/highcharts/3.0.2/highcharts.js"}',
						'{exporting: "https://cdnjs.cloudflare.com/ajax/libs/highcharts/3.0.2/modules/exporting.js"}',
						'{graph_helper: "' . $this->config->item("base_url_assets") . 'js/charts/graph_helper.js"}',
						'{card_helper: "' . $this->config->item("base_url_assets") . 'js/summary_reports/report_card_helper.js"}',
						'{helper: "' . $this->config->item("base_url_assets") . 'js/as_dashboard_helper.js"}'
					)
				)
			);
		}
		$this->footer_data = Array(
/*			'arr_foot_line'=>array(
				'<script type="text/javascript"> //for chart
					head.ready("card_helper", function(){//load_chart("' . $ajax_url . '", false);
						load_chart("' . site_url("/report_card/ajax_graph/production/" . $pstring . "/chart/" . $benchmarks_id . "/" . $all_breeds_code) . '", "snapshot-chart");
						load_chart("' . site_url("/report_card/ajax_graph/inventory/" . $pstring . "/chart/" . $benchmarks_id . "/" . $all_breeds_code) . '");
					});
				</script>'
			)
*/		);

		//header and footer
		$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
		$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
		$this->data['page_footer'] = $this->load->view('page_footer', $this->footer_data, TRUE);
//		$this->load->_ci_cached_vars = array();
		//widgets (pull from DB?)
		//get_herd_data
		$this->load->model('herd_model');
		$herd_data = $this->herd_model->header_info($this->session->userdata('herd_code'));
		$this->data['widget']['sections'][] = array(
			'content' => $this->load->view('auth/dashboard/herd_data', $herd_data, TRUE),
			'title' => 'Herd Data'
		);
		$this->data['widget']['sections'][] = array(
			'content' => $this->load->view('auth/dashboard/untreated_scc', NULL, TRUE),
			'title' => 'High SCC Cows with no Recent Treatment'
		);
/*		$snapshot['inner_html'] = $this->load->view('chart', array('div_id'=>'snapshot-chart', 'chart_height'=>250, 'chart_width'=>400), TRUE);
		$this->data['widget']['sections'][] = array(
			'content' => $this->load->view('auth/dashboard/snapshot', $snapshot, TRUE),
			'title' => 'Report Snapshot'
		);
*/
//		$this->load->_ci_cached_vars = array();
		unset($snapshot);
		$this->data['widget']['sections'][] = array(
			'content' => $this->load->view('auth/dashboard/past_results', NULL, TRUE),
			'title' => 'Past Reports'
		);
		$resource_data = Array('email' => $this->session->userdata('email'), 'name' => $this->session->userdata('first_name') . ' ' . $this->session->userdata('last_name'));
		$this->data['widget']['info'][] = array(
			'content' => $this->load->view('auth/dashboard/resources', $resource_data, TRUE),
			'title' => 'Resources'
		);
		/*$this->data['widget']['sections'][] = array(
			'content' => $this->load->view('auth/dashboard/weather', array('zip_code'=>'53593'), TRUE),
			'title' => 'Weather'
		);*/
		$this->load->library('RSSParser', array('url' => 'http://www.agweb.com/rss/blogs.aspx?bf=%2f&bid=281', 'life' => 0));
		//Get six items from the feed
		$data = $this->rssparser->getFeed(6);
		$this->data['widget']['agsource'][] = array(
			'content' => $this->load->view('auth/dashboard/dairy_blog', array('data'=>$data), TRUE),
			'title' => 'Dairy Blog'
		);
		$this->data['widget']['info'][] = array(
			'content' => $this->load->view('auth/dashboard/profit_tips', NULL, TRUE),
			'title' => 'Profit Tips'
		);
		if($this->session->userdata('active_group_id') == 2){
			$consultants_by_status = $this->ion_auth_model->get_consultants_by_herd($this->session->userdata('herd_code'));
			if(isset($consultants_by_status['open']) && is_array($consultants_by_status['open'])){
				$section_data['content'] = $this->_set_consult_section($consultants_by_status['open'], 'open', 'Open Requests', array('Grant Access', 'Deny Access'));
				$this->data['widget']['info'][] = array(
					'content' => $this->load->view('auth/dashboard/open_consult_requests', $section_data, TRUE),
					'title' => 'Open Consultant Requests'
				);
			}
		}
		
		$product_data = Array('sections' => $this->as_ion_auth->get_promo_sections());
		if(isset($product_data['sections']) && !empty($product_data['sections'])) $this->data['widget']['info'][] = array(
			'content' => $this->load->view('auth/dashboard/other_products', $product_data, TRUE),
			'title' => 'Other Products'
		);
		$this->data['widget']['info'][] = array(
			'content' => $this->load->view('auth/dashboard/survey', NULL, TRUE),
			'title' => 'Survey'
		);
		$this->data['widget']['agsource'][] = array(
			'content' => $this->load->view('auth/dashboard/twitter', NULL, TRUE),
			'title' => 'Twitter'
		);
		$this->data['widget']['agsource'][] = array(
			'content' => $this->load->view('auth/dashboard/youtube', NULL, TRUE),
			'title' => $this->config->item("cust_serv_company","ion_auth") . ' Video'
		);
		$full_width['inner_html'] = $this->load->view('chart', NULL, TRUE);
		$this->data['widget']['full_width'][] = array(
			'content' => $this->load->view('auth/dashboard/full_width', $full_width, TRUE),
			'title' => 'Too Wide for Normal Widget'
		);
//		$this->load->_ci_cached_vars = array();api.twitter
		unset($full_width);
		//page
		$this->load->view('auth/dashboard/main', $this->data);
	}
}
