<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/ionauth.php';

class Auth extends Ionauth {
	function __construct()
	{
		parent::__construct();
//		if(isset($this->as_ion_auth)){
//			$this->as_ion_auth->is_admin = $this->as_ion_auth->is_admin();
//			$this->as_ion_auth->is_manager = $this->as_ion_auth->is_manager();
//		}

		$this->page_header_data['user_sections'] = $this->as_ion_auth->arr_user_super_sections;
		
		//load necessary files
		$this->load->library('form_validation');
		$this->load->helper('cookie');

		/* Load the profile.php config file if it exists
		$this->config->load('profiler', false, true);
		if ($this->config->config['enable_profiler']) {
			$this->output->enable_profiler(TRUE);
		} */
	}
	
	function index(){
		$this->load->model('herd_model');
		$this->load->model('alert_model');
		if(!isset($this->as_ion_auth) || !$this->as_ion_auth->logged_in()){
			$this->session->keep_flashdata('redirect_url');
			redirect(site_url('auth/login'));
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
		

	
		$this->data = array(
			'page_header' => $this->load->view('page_header', $this->page_header_data, TRUE),
			'page_heading' => 'My Account',
			'herd_code' => $this->session->userdata('herd_code'),
			'herd_data' => $this->load->view('herd_info', $herd_data, TRUE),
			'table_heading' => 'Herd Overview',
			'page_footer' => $this->load->view('page_footer', $this->footer_data, TRUE),
			'bench_data' => $this->alert_model->get_benchmarks($this->session->userdata('herd_code'))
//			'report_path' => $this->report_path
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
			'title' => $this->config->item("cust_serv_company") . ' Video'
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

	function section_info(){
		$arr_section_inquiry = $this->input->post('sections');
		if(isset($arr_section_inquiry) && is_array($arr_section_inquiry)){
			if($this->as_ion_auth->record_section_inquiry($arr_section_inquiry, $this->input->post('comments'))){
				$this->session->set_flashdata('message', 'Thank you for your interest.  Your request for more information has been sent.');
			}
			else{
				$this->session->set_flashdata('message', 'We encountered a problem sending your request.  Please try again or contact us at ' . $this->config->item("cust_serv_email") . ' or ' . $this->config->item("cust_serv_phone") . '.');
			}
		}
		else {
			$this->session->set_flashdata('message', 'Please select one or more web products and resubmit your request.');
		}
		redirect(site_url('auth'));
	}

	function manage_consult(){
		if((!$this->as_ion_auth->logged_in())){
       		$this->session->keep_flashdata('message');
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
       		redirect(site_url('auth/login'));
		}
		if($this->session->userdata('active_group_id') != 2) {
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
			$this->session->set_flashdata('message', 'Only producers can manage consultant access to their herd data.');
			redirect('auth');
		}
		
		$this->form_validation->set_rules('modify', 'Herd Selection');

		if ($this->form_validation->run() == TRUE) {
			$action = $this->input->post('submit');
			$arr_modify_id = $this->input->post('modify');
			if(isset($arr_modify_id) && is_array($arr_modify_id)){
				switch ($action) {
					case 'Remove Access':
						if($this->ion_auth_model->batch_consult_revoke($arr_modify_id)) {
							$this->access_log_model->write_entry(41);
							$this->data['message'] = 'Consultant access adjusted successfully.';
						}
						else $this->data['message'] = 'Consultant access adjustment failed.  Please try again.';
					break;
					case 'Grant Access':
						if($this->ion_auth_model->batch_grant_consult($arr_modify_id)) {
							$this->access_log_model->write_entry(34);
							$this->data['message'] = 'Consultant access adjusted successfully.';
						}
						else $this->data['message'] = 'Consultant access adjustment failed.  Please try again.';
					break;
					case 'Deny Access':
						if($this->ion_auth_model->batch_deny_consult($arr_modify_id)) {
							$this->access_log_model->write_entry(42);
							$this->data['message'] = 'Consultant access adjusted successfully.';
						}
						else $this->data['message'] = 'Consultant access adjustment failed.  Please try again.';
					break;
					case 'Remove Expiration Date':
						if($this->ion_auth_model->batch_remove_consult_expire($arr_modify_id)) {
							$this->access_log_model->write_entry(43);
							$this->data['message'] = 'Consultant access adjusted successfully.';
						}
						else $this->data['message'] = 'Consultant access adjustment failed.  Please try again.';
					break;
					default:
						$this->data['message'] = 'Consultant access adjustment failed.  Please try again.';
					break;
				}
			}
		}
		$this->data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());
		$consultants_by_status = $this->ion_auth_model->get_consultants_by_herd($this->session->userdata('herd_code'));
		if(isset($consultants_by_status['open']) && is_array($consultants_by_status['open'])){
			$section_data['content'] = $this->_set_consult_section($consultants_by_status['open'], 'open', array('Grant Access', 'Deny Access'));
			$section_data['title'] = 'Open Requests';
			$this->data['arr_sections']['open'] = $this->load->view('auth/consult/consultant_section_container', $section_data, TRUE);
		}
		if(isset($consultants_by_status['deny']) && is_array($consultants_by_status['deny'])){
			$section_data['content'] = $this->_set_consult_section($consultants_by_status['deny'], 'deny', array('Grant Access'));
			$section_data['title'] = 'Denied Requests';
			$this->data['arr_sections']['deny'] = $this->load->view('auth/consult/consultant_section_container', $section_data, TRUE);
		}
		if(isset($consultants_by_status['grant']) && is_array($consultants_by_status['grant'])) {
			$section_data['content'] = $this->_set_consult_section($consultants_by_status['grant'], 'grant', array('Remove Access'));
			$section_data['title'] = 'Granted Requests';
			$this->data['arr_sections']['grant'] = $this->load->view('auth/consult/consultant_section_container', $section_data, TRUE);
		}
		if(isset($consultants_by_status['expired']) && is_array($consultants_by_status['expired'])) {
			$section_data['content'] = $this->_set_consult_section($consultants_by_status['expired'], 'expired', array('Remove Expiration Date'));
			$section_data['title'] = 'Expired Requests';
			$this->data['arr_sections']['expired'] = $this->load->view('auth/consult/consultant_section_container', $section_data, TRUE);
		}
		if(isset($consultants_by_status['consult_revoked']) && is_array($consultants_by_status['consult_revoked'])){
			$section_data['content'] = $this->_set_consult_section($consultants_by_status['consult_revoked'], 'consult_revoked', NULL);
			$section_data['title'] = 'Consultant Revoked Access';
			$this->data['arr_sections']['consult_revoked'] = $this->load->view('auth/consult/consultant_section_container', $section_data, TRUE);
		}
		if(isset($consultants_by_status['herd_revoked']) && is_array($consultants_by_status['herd_revoked'])){
			$section_data['content'] = $this->_set_consult_section($consultants_by_status['herd_revoked'], 'herd_revoked', array('Grant Access'));
			$section_data['title'] = 'Herd Revoked Access';
			$this->data['arr_sections']['herd_revoked'] = $this->load->view('auth/consult/consultant_section_container', $section_data, TRUE);
		}
		$this->carabiner->css('accordion.css', 'screen');
		$this->data['title'] = "Manage Consultants";
		if(is_array($this->page_header_data)){
			$this->page_header_data = array_merge($this->page_header_data,
				array(
					'title'=>'Manage Herd Access - ' . $this->config->item('product_name'),
					'description'=>'Manage Herd Access, ' . $this->config->item('product_name'),
				)
			);
		}
		$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
		$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
		$this->data['page_heading'] = "Manage Herd Access";
		$footer_data = array();
		$this->data['page_footer'] = $this->load->view('page_footer', $footer_data, TRUE);
		
		$this->load->view('auth/consult/manage_consult', $this->data);
	}
	
	function consult_manage_herds(){
		if((!$this->as_ion_auth->logged_in())){
       		$this->session->keep_flashdata('message');
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
       		redirect(site_url('auth/login'));
		}
		if($this->session->userdata('active_group_id') != 9) {
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
			$this->session->set_flashdata('message', 'Only consultants can manage their access to herd data.');
			redirect('auth');
		}

		$this->form_validation->set_rules('modify', 'Herd Selection');

		if ($this->form_validation->run() == TRUE) {
			$action = $this->input->post('submit');
			$arr_modify_id = $this->input->post('modify');
			if(isset($arr_modify_id) && is_array($arr_modify_id)){
				switch ($action) {
					case 'Remove Access':
						if($this->ion_auth_model->batch_consult_revoke($arr_modify_id)){
							$this->access_log_model->write_entry(41);
							$this->data['message'] = 'Consultant access adjusted successfully.';
						}
						else $this->data['message'] = 'Consultant access adjustment failed.  Please try again.';
					break;
					case 'Restore Access':
						//if consultant had revoked access, they can restore it (call grant_access)
						foreach($arr_modify_id as $k=>$id){
							if($this->ion_auth_model->get_consult_status_text($id) != 'consult_revoked') unset($arr_modify_id[$k]);
						}
						if(!empty($arr_modify_id) && $this->ion_auth_model->batch_grant_consult($arr_modify_id)) {
							$this->access_log_model->write_entry(34);
							$this->data['message'] = 'Consultant access adjusted successfully.';
						}
						else $this->data['message'] = 'Consultant access adjustment failed.  Please try again.';
					break;
					case 'Resend Request Email':
						foreach($arr_modify_id as $k=>$id){
							$arr_relationship_data = $this->ion_auth_model->get_consult_relationship_by_id($id);
							if ($this->as_ion_auth->send_consultant_request($arr_relationship_data, $id)) {
								$this->access_log_model->write_entry(35);
								$this->data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());
							}
							else { //if the request was un-successful
								//redirect them back to the login page
								$this->data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());
							}
						}
					break;
					default:
						$this->data['message'] = 'Consultant access adjustment failed.  Please try again.';
					break;
				}
			}
		}
		$this->data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());

		$herds_by_status = $this->ion_auth_model->get_herds_by_consult($this->session->userdata('user_id'));
		if(isset($herds_by_status['open']) && is_array($herds_by_status['open'])){
			$section_data['content'] = $this->_set_consult_herd_section($herds_by_status['open'], 'open', array('Resend Request Email'));
			$section_data['title'] = 'Open Requests';
			$this->data['arr_sections']['open'] = $this->load->view('auth/consult/herd_section_container', $section_data, TRUE);
		}
		if(isset($herds_by_status['deny']) && is_array($herds_by_status['deny'])){
			$section_data['content'] = $this->_set_consult_herd_section($herds_by_status['deny'], 'deny', NULL);
			$section_data['title'] = 'Denied Requests';
			$this->data['arr_sections']['deny'] = $this->load->view('auth/consult/herd_section_container', $section_data, TRUE);
		}
		if(isset($herds_by_status['grant']) && is_array($herds_by_status['grant'])) {
			$section_data['content'] = $this->_set_consult_herd_section($herds_by_status['grant'], 'grant', array('Remove Access'));
			$section_data['title'] = 'Granted Requests';
			$this->data['arr_sections']['grant'] = $this->load->view('auth/consult/herd_section_container', $section_data, TRUE);
		}
		if(isset($herds_by_status['expired']) && is_array($herds_by_status['expired'])) {
			$section_data['content'] = $this->_set_consult_herd_section($herds_by_status['expired'], 'expired', array('Resend Request Email'));
			$section_data['title'] = 'Expired Requests';
			$this->data['arr_sections']['expired'] = $this->load->view('auth/consult/herd_section_container', $section_data, TRUE);
		}
		if(isset($herds_by_status['consult_revoked']) && is_array($herds_by_status['consult_revoked'])){
			$section_data['content'] = $this->_set_consult_herd_section($herds_by_status['consult_revoked'], 'consult_revoked', array('Restore Access'));
			$section_data['title'] = 'Consultant Revoked Access';
			$this->data['arr_sections']['consult_revoked'] = $this->load->view('auth/consult/herd_section_container', $section_data, TRUE);
		}
		if(isset($herds_by_status['herd_revoked']) && is_array($herds_by_status['herd_revoked'])){
			$section_data['content'] = $this->_set_consult_herd_section($herds_by_status['herd_revoked'], 'herd_revoked', NULL);
			$section_data['title'] = 'Herd Revoked Access';
			$this->data['arr_sections']['herd_revoked'] = $this->load->view('auth/consult/herd_section_container', $section_data, TRUE);
		}
		$this->carabiner->css('accordion.css', 'screen');
		$this->data['title'] = "Manage Herd Access";
		if(is_array($this->page_header_data)){
			$this->page_header_data = array_merge($this->page_header_data,
				array(
					'title'=>'Manage Herd Access - ' . $this->config->item('product_name'),
					'description'=>'Manage Herd Access, ' . $this->config->item('product_name'),
				)
			);
		}
		$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
		$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
		$this->data['page_heading'] = "Manage Herd Access";
		$footer_data = array();
		$this->data['page_footer'] = $this->load->view('page_footer', $footer_data, TRUE);
		$this->load->view('auth/consult/manage_consult', $this->data);
	}
	
	function _set_consult_section($data, $key, $arr_submit_options){
		if(isset($data) && is_array($data)){
			$this->section_data = array(
				'arr_submit_options' => $arr_submit_options,
				'attributes' => array('class' => $key . ' consult-form'),
			);
			foreach($data as $h) {
				$h['is_editable'] = TRUE;
				$this->section_data['arr_records'][] = $this->load->view('auth/consult/consultant_line', $h, TRUE);
			}
			return $this->load->view('auth/consult/consultant_section', $this->section_data, TRUE);
		}
	}

	function _set_consult_herd_section($data, $key, $arr_submit_options){
		if(isset($data) && is_array($data)){
			$this->section_data = array(
				'arr_submit_options' => $arr_submit_options,
				'attributes' => array('class' => $key . ' consult-form'),
			);
			foreach($data as $h) {
				$h['is_editable'] = FALSE;
				$this->section_data['arr_records'][] = $this->load->view('auth/consult/herd_line', $h, TRUE);
			}
			return $this->load->view('auth/consult/herd_section', $this->section_data, TRUE);
		}
	}
	
	//Producers only, give consultant permission to view herd
	function consult_access($cuid = NULL) {
		if((!$this->as_ion_auth->logged_in())){
       		$this->session->keep_flashdata('message');
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
       		redirect(site_url('auth/login'));
		}
		if($this->session->userdata('active_group_id') != 2) {
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
			$this->session->set_flashdata('message', 'Only producers can manage access to their herd data.');
			redirect('auth');
		}
		
		$this->data['title'] = "Grant Consultant Access to Herd";

		//validate form input
		$this->form_validation->set_rules('consultant_user_id', 'Consultant User Id', 'trim|required');
		$this->form_validation->set_rules('section_id', 'Sections', 'required');
		$this->form_validation->set_rules('exp_date', 'Expiration Date', 'trim');
		$this->form_validation->set_rules('request_status_id', 'Request Status', '');
		$this->form_validation->set_rules('write_data', 'Enter Event Data', '');
		//$this->form_validation->set_rules('consult_request', '', '');
		$this->form_validation->set_rules('disclaimer', 'Confirmation of Understanding', 'required');

		if ($this->form_validation->run() == TRUE) {
			$arr_relationship_data = array(
				'herd_code' => $this->session->userdata('herd_code'),
				'consultant_user_id' => $this->input->post('consultant_user_id'),
				'request_status_id' => $this->input->post('request_status_id'),
				//'consult_request' => $this->input->post('consult_request'),
				'write_data' => $this->input->post('write_data')
			);
			$tmp = human_to_mysql($this->input->post('exp_date'));
			if(isset($tmp) && !empty($tmp)) $arr_relationship_data['exp_date'] = $tmp;
			//if($this->input->post('request_denied') == 1) $arr_relationship_data['request_denied'] = 1;
			$arr_consultant = $this->ion_auth_model->user($this->input->post('consultant_user_id'))->row_array();
			$arr_consult_groups = explode(',', $arr_consultant['groups']);
			if(!in_array('9', $arr_consult_groups)){
				$this->session->set_flashdata('message', 'The user you are attempting to add as a consultant is not a consultant.  Please try again or contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone'));
				redirect(site_url('auth/consult_access'));
			}
			if ($this->as_ion_auth->allow_consult($arr_relationship_data, $this->input->post('section_id'))) { //if permission is granted successfully
				//redirect them back to the home page
				$msg = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());
				$this->access_log_model->write_entry(34);
				$this->session->set_flashdata('message', $msg);
				redirect(site_url($redirect_url)); //to access management page?
			}
			else { //if the request was un-successful
				$msg = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());
				$this->session->set_flashdata('message', $msg);
				redirect(site_url('auth/consult_access'));
			}
		}
		else {
			//set the flash data error message if there is one
			$this->data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());
			//check of an existing record for this relationship
			if(!isset($cuid)) $cuid = $this->input->post('consultant_user_id');
			if(isset($cuid) && !empty($cuid)) $arr_relationship = $this->ion_auth_model->get_consult_relationship($cuid, $this->session->userdata('herd_code'));
			else $arr_relationship = FALSE;

			// get sections for user
			if($arr_relationship['consult_request']){
				$arr_form_section_id = $this->ion_auth_model->get_consult_rel_sections($arr_relationship['id']);
			}
			else{
//				$user_id = $this->input->post('user_id');
				$user_id = $this->session->userdata('user_id');
				$obj_user = $this->ion_auth_model->user($user_id)->row();
				$obj_user->arr_groups = array_keys($this->ion_auth_model->get_users_group_array($obj_user->id));
				//note: active group id should always be 2
				$tmp_array = $this->as_ion_auth->get_sections_array(2, $user_id, $obj_user->herd_code, NULL, array('subscription','public','unmanaged'));
				$obj_user->section_id = $this->as_ion_auth->set_form_array($tmp_array, 'id', 'id'); // populate array of sections for which user is enrolled
				$tmp_array = $this->input->post('section_id');
				$arr_form_section_id = isset($tmp_array) && is_array($tmp_array) ? $tmp_array : $obj_user->section_id;
			}

			$this->data['sections_selected'] = $arr_form_section_id;
			$this->data['section_id'] = 'id="section_id"';
			//note: active group id should always be 2
			$tmp_array = $this->as_ion_auth->get_sections_array($this->session->userdata('active_group_id'), $this->session->userdata('user_id'), $this->session->userdata('herd_code'), NULL, array('subscription', 'public', 'unmanaged'));
			$this->data['section_options'] = $this->as_ion_auth->set_form_array($tmp_array, 'id', 'name');
			unset($tmp_array);
			$this->data['consultant_user_id'] = array(
				'name' => 'consultant_user_id',
				'id' => 'consultant_user_id',
				'type' => 'text',
				'value' => $this->form_validation->set_value('consultant_user_id', $arr_relationship ? $arr_relationship['consultant_user_id'] : $cuid),
			);
			$this->data['exp_date'] = array(
				'name' => 'exp_date',
				'id' => 'exp_date',
				'type' => 'text',
				'value' => $this->form_validation->set_value('exp_date', $arr_relationship ? mysql_to_human($arr_relationship['exp_date']) : ''),
			);
/*			$this->data['consult_request'] = array(
				'name' => 'consult_request',
				'id' => 'consult_request',
				'type' => 'hidden',
				'value' => $this->form_validation->set_value('consult_request', $this->data['consult_request']),
			); */
			$this->data['request_denied'] = array(
				'name' => 'request_status_id',
				'id' => 'request_denied',
				'type' => 'radio',
				'value' => 2,
				'checked' => set_radio('request_status_id', 'deny', $arr_relationship && $arr_relationship['request_status_id'] == 2 ? TRUE : FALSE)
			);
			$this->data['request_granted'] = array(
				'name' => 'request_status_id',
				'id' => 'request_granted',
				'type' => 'radio',
				'value' => 1,
				'checked' => set_radio('request_status_id', 'grant', $arr_relationship && $arr_relationship['request_status_id'] != 2 ? TRUE : FALSE)
			);
			$this->data['write_data'] = array(
				'name' => 'write_data',
				'id' => 'write_data',
				'type' => 'checkbox',
				'value' => '1',
				'checked' => set_checkbox('write_data', 1, $arr_relationship && $arr_relationship['write_data'] == '1' ? TRUE : FALSE)
			);
			$this->data['disclaimer'] = array(
				'name' => 'disclaimer',
				'id' => 'disclaimer',
				'type' => 'checkbox',
				'value' => '1',
				'checked' => FALSE
			);


			$this->carabiner->css('agsource.datepick.css', 'screen');
			$this->carabiner->css('jquery.datetimeentry.css', 'screen');
			if(is_array($this->page_header_data)){
				$this->page_header_data = array_merge($this->page_header_data,
					array(
						'title'=>'Grant Consultant Access - ' . $this->config->item('product_name'),
						'description'=>'Grant Consultant Access to ' . $this->config->item('product_name'),
						'arr_headjs_line'=>array(
							'{datepick: "' . $this->config->item("base_url_assets") . 'js/jquery/jquery.datepick.min.js"}',
							'{report_helper: "' . $this->config->item("base_url_assets") . 'js/consultant_helper.js"}',
						)
					)
				);
			}
			$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
			$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
			$this->data['page_heading'] = 'Grant Consultant Access - ' . $this->config->item('product_name');
			$footer_data = array(
			);
			$this->data['page_footer'] = $this->load->view('page_footer', $footer_data, TRUE);

			$this->load->view('auth/consult/allow_consult', $this->data);
		}
	}

		//Consultants only, request permission to view herd
	function consult_request() {
		if((!$this->as_ion_auth->logged_in())){
       		$this->session->keep_flashdata('message');
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
       		redirect(site_url('auth/login'));
		}
		if($this->session->userdata('active_group_id') != 9) {
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
			$this->session->set_flashdata('message', 'Only consultant can request permission to view a herd\'s data.');
			redirect('auth');
		}

		$this->data['title'] = "Request Access to Herd";

		//validate form input
		$this->form_validation->set_rules('herd_code', 'Herd Code', 'trim|required|exact_length[8]');
		$this->form_validation->set_rules('section_id', 'Sections', 'required');
		$this->form_validation->set_rules('exp_date', 'Expiration Date', 'trim');
		$this->form_validation->set_rules('write_data', 'Enter Event Data', '');
//		$this->form_validation->set_rules('consult_request', '', '');
		$this->form_validation->set_rules('disclaimer', 'Confirmation of Understanding', 'required');

		if ($this->form_validation->run() == TRUE) {
			if(!$this->herd_model->herd_is_registered($this->input->post('herd_code'))){
				$this->session->set_flashdata('message', 'Herd ' . $this->input->post('herd_code') . ' is not registered for ' . $this->config->item('product_name') . '.  In order to access their data, they must be registered for ' . $this->config->item('product_name') . '.');
				redirect(site_url('auth/consult_request'));
			}
			$arr_relationship_data = array(
				'herd_code' => $this->input->post('herd_code'),
				'consultant_user_id' => $this->session->userdata('user_id'),
				'consult_request' => 1,
				'write_data' => $this->input->post('write_data')
			);
			$tmp = human_to_mysql($this->input->post('exp_date'));
			if(isset($tmp) && !empty($tmp)) $arr_relationship_data['exp_date'] = $tmp;
			if ($this->as_ion_auth->consult_request($arr_relationship_data, $this->input->post('section_id'))) {
				$this->access_log_model->write_entry(35);
				$msg = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());
				$this->session->set_flashdata('message', $msg);
				redirect(site_url($redirect_url)); //  to manage access page
			}
			else { //if the request was un-successful
				//redirect them back to the login page
				$msg = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());
				$this->session->set_flashdata('message', $msg);
				redirect(site_url('auth/consult_request'));
			}
		}
		else {
			//set the flash data error message if there is one
			$this->data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());

			// get sections for user
//			$user_id = $this->input->post('user_id');
			$user_id = $this->session->userdata('user_id');
			$obj_user = $this->ion_auth_model->user($user_id)->row();
			$obj_user->arr_groups = array_keys($this->ion_auth_model->get_users_group_array($obj_user->id));
			
			//note: active group id should always be 9
			$tmp_array = $this->as_ion_auth->get_sections_array(9, $user_id, $obj_user->herd_code, NULL, array('subscription','public','unmanaged'));
			$obj_user->section_id = $this->as_ion_auth->set_form_array($tmp_array, 'id', 'id'); // populate array of sections for which user is enrolled

			$tmp_array = $this->input->post('section_id');
//			$arr_form_section_id = $this->form_validation->set_value('section_id');//, $obj_user->section_id);
			$arr_form_section_id = isset($tmp_array) && is_array($tmp_array) ? $tmp_array : $obj_user->section_id;

			$this->data['sections_selected'] = $arr_form_section_id;
			$this->data['section_id'] = 'id="section_id"';
			//note: active group id should always be 9
			$tmp_array = $this->as_ion_auth->get_sections_array(9, $user_id, FALSE, NULL, array('subscription','public','unmanaged'));
			$this->data['section_options'] = $this->as_ion_auth->set_form_array($tmp_array, 'id', 'name');

			$this->data['herd_code'] = array(
				'name' => 'herd_code',
				'id' => 'herd_code',
				'type' => 'text',
				'value' => $this->form_validation->set_value('herd_code'),
			);
			$this->data['exp_date'] = array(
				'name' => 'exp_date',
				'id' => 'exp_date',
				'type' => 'text',
				'value' => $this->form_validation->set_value('exp_date'),
			);
			$this->data['write_data'] = array(
				'name' => 'write_data',
				'id' => 'write_data',
				'type' => 'checkbox',
				'value' => '1',
				'checked' => set_checkbox('write_data', 1, FALSE)
			);
			$this->data['disclaimer'] = array(
				'name' => 'disclaimer',
				'id' => 'disclaimer',
				'type' => 'checkbox',
				'value' => '1',
				'checked' => FALSE
			);

			$this->carabiner->css('agsource.datepick.css', 'screen');
			$this->carabiner->css('jquery.datetimeentry.css', 'screen');
			if(is_array($this->page_header_data)){
				$this->page_header_data = array_merge($this->page_header_data,
					array(
						'title'=>'Request Data Access - ' . $this->config->item('product_name'),
						'description'=>'Grant Consultant Access to ' . $this->config->item('product_name'),
						'arr_headjs_line'=>array(
							'{datepick: "' . $this->config->item("base_url_assets") . 'js/jquery/jquery.datepick.min.js"}',
							'{report_helper: "' . $this->config->item("base_url_assets") . 'js/consultant_helper.js"}',
						)
					)
				);
			}
			$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
			$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
			$this->data['page_heading'] = 'Request Data Access - ' . $this->config->item('product_name');
			$footer_data = array(
			);
			$this->data['page_footer'] = $this->load->view('page_footer', $footer_data, TRUE);

			$this->load->view('auth/consult/consult_request', $this->data);
		}
	}

	function list_accounts(){
		if(!$this->as_ion_auth->has_permission("Manage Other Accounts")){
       		$this->session->set_flashdata('message',  $this->session->flashdata('message') . "You do not have permission to edit user accounts.");
       		redirect(site_url("auth/index"), 'refresh');
		}
		//set the flash data error message if there is one
		$this->data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());
		//list the users
		$this->data['users'] = $this->as_ion_auth->get_child_users();
		$this->data['arr_group_lookup'] = $this->ion_auth_model->arr_group_lookup;
		
		$this->carabiner->css('report.css', 'screen');
		$this->carabiner->css('datatables/table_ui.css', 'screen');
		if(is_array($this->page_header_data)){
			$this->page_header_data = array_merge($this->page_header_data,
				array(
					'title'=>'User List - ' . $this->config->item('product_name'),
					'description'=>'Log In Form - ' . $this->config->item('product_name'),
					'arr_headjs_line'=>array(
						'{datatable: "' . $this->config->item("base_url_assets") . 'js/jquery/jquery.dataTables.min.js"}'
					)
				)
			);
		}
		$this->footer_data = Array(
			'arr_foot_line'=>array(
				'<script type="text/javascript">head.ready("datatable", function(){ $("#sortable").dataTable({
					"iDisplayLength": -1,
					"aaSorting": [[1,"asc"]],
					"aLengthMenu": [[50, 100, 200, -1], [50, 100, 200, "All"]]
				}); });</script>'
			)
		);
		$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
		$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, true);
		$this->data['page_heading'] = 'User List - ' . $this->config->item('product_name');
		$this->data['page_footer'] = $this->load->view('page_footer', $this->footer_data, TRUE);
		$this->load->view('auth/index', $this->data);
	}

	//log the user in
	//CDT overrides built-in function to allow us to redirect user to the original page they requested after login in
	function login()
	{
		$redirect_url = set_redirect_url('login');
		// set redirect url
		//$this->session->keep_flashdata('redirect_url');
/*		$tmp = $this->session->flashdata('redirect_url');
		$redirect_url = $tmp !== FALSE ? $tmp : $this->as_ion_auth->referrer;
		//if(strpos($redirect_url, 'auth') !== FALSE) $redirect_url = 'auth/index';
		if(strpos($redirect_url, 'login') || strpos($redirect_url, 'logout')) $redirect_url = '';
		
		$this->session->set_flashdata('redirect_url', $redirect_url);
*/
		$this->data['title'] = "Login";

		//validate form input
		$this->form_validation->set_rules('identity', 'Email Address', 'trim|required|valid_email');
		$this->form_validation->set_rules('password', 'Password', 'trim|required');

		if ($this->form_validation->run() == TRUE)
		{ //check to see if the user is logging in
			//check for "remember me"
			$remember = (bool) $this->input->post('remember');

			if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember))
			{ //if the login is successful
				//redirect them back to the home page
				$this->access_log_model->write_entry(1); //1 is the page code for login for the user management section
				$this->session->set_flashdata('message', $this->as_ion_auth->messages());
				redirect(site_url($redirect_url));
			}
			else
			{ //if the login was un-successful
				//redirect them back to the login page
				$this->session->set_flashdata('redirect_url', $redirect_url);
				$this->session->set_flashdata('message', $this->as_ion_auth->errors());
				redirect(site_url('auth/login')); //use redirects instead of loading views for compatibility with MY_Controller libraries
			}
		}
		else
		{  //the user is not logging in so display the login page
			//set the flash data error message if there is one
			$this->data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());

			$this->data['identity'] = array('name' => 'identity',
				'id' => 'identity',
				'type' => 'text',
				'value' => $this->form_validation->set_value('identity'),
			);
			$this->data['password'] = array('name' => 'password',
				'id' => 'password',
				'type' => 'password',
			);

			if(is_array($this->page_header_data)){
				$this->page_header_data = array_merge($this->page_header_data,
					array(
						'title'=>'Log In - ' . $this->config->item('product_name'),
						'description'=>'Log In Form for ' . $this->config->item('product_name')
					)
				);
			}
			$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
			$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
			$this->data['page_heading'] = 'Log In - ' . $this->config->item('product_name');
			$this->data['page_footer'] = $this->load->view('page_footer', NULL, TRUE);

			$this->load->view('auth/login', $this->data);
		}
	}

	//log the user out
	function logout()
	{
		//IE seemed to cache the redirect from previous page loads.  Prevent any caching
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		$this->data['title'] = "Logout";

		//log the user out
		$logout = $this->as_ion_auth->logout();

		//redirect them
		redirect(site_url('auth/login'));
	}

	//change password
	function change_password()
	{
		if (!$this->as_ion_auth->logged_in()){
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
		}
		if(is_array($this->page_header_data)){
			$this->page_header_data = array_merge($this->page_header_data,
				array(
					'title'=>'Update Password - ' . $this->config->item('product_name'),
					'description'=>'Update Password - ' . $this->config->item('product_name')
				)
			);
		}
		$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
		$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, true);
		$this->data['page_heading'] = 'Update Password - ' . $this->config->item('product_name');
		$this->data['page_footer'] = $this->load->view('page_footer', null, true);
		parent::change_password;
	}

	//forgot password
	function forgot_password()
	{
		if(is_array($this->page_header_data)){
			$this->page_header_data = array_merge($this->page_header_data,
				array(
					'title'=>'Forgotten Password - ' . $this->config->item('product_name'),
					'description'=>'Forgot Password - ' . $this->config->item('product_name')
				)
			);
		}
		$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
		$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
		$this->data['page_heading'] = 'Forgotten Password - ' . $this->config->item('product_name');
		$this->data['page_footer'] = $this->load->view('page_footer', null, TRUE);
		parent::forgot_password();
	}

	//deactivate the user
	function deactivate($id = NULL)
	{
		if(is_array($this->page_header_data)){
			$this->page_header_data = array_merge($this->page_header_data,
				array(
					'title'=>'Deactivate User - ' . $this->config->item('product_name'),
					'description'=>'Deactivate User - ' . $this->config->item('product_name')
				)
			);
		}
		$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
		$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, true);
		$this->data['page_heading'] = 'Deactivate User - ' . $this->config->item('product_name');
		$this->data['page_footer'] = $this->load->view('page_footer', null, true);
		parent::deactivate($id);
	}

	//create a new user
//@todo : verify that producer are not allowed to add or modify groups
	function create_user()
	{
		$this->data['title'] = "Create Account";

		//validate form input
		$this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
		$this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
		$this->form_validation->set_rules('email', 'Email Address', 'trim|required|valid_email');
		$this->form_validation->set_rules('supervisor_num', 'Field Technician Number', 'exact_length[6]');
		$this->form_validation->set_rules('region_id', 'Association Number (if a member of an association)', 'exact_length[3]');
		$this->form_validation->set_rules('phone1', 'First Part of Phone', 'exact_length[3]');
		$this->form_validation->set_rules('phone2', 'Second Part of Phone', 'exact_length[3]');
		$this->form_validation->set_rules('phone3', 'Third Part of Phone', 'exact_length[4]');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]');
		$this->form_validation->set_rules('password_confirm', 'Password Confirmation', 'trim|required');
		$this->form_validation->set_rules('group_id[]', 'Name of User Group');
		$this->form_validation->set_rules('gsg_access_level', 'Access Level for this user', 'xss_clean');
		//$this->form_validation->set_rules('terms', 'Agree to Terms', 'required|exact_length[1]');
		//$this->form_validation->set_rules('company', 'Company Name', 'xss_clean');
		$this->form_validation->set_rules('herd_code', 'Herd Code', 'exact_length[8]');
		$this->form_validation->set_rules('herd_release_code', 'Release Code', 'trim|exact_length[10]');
		$this->form_validation->set_rules('section_id[]', 'Section');

		$is_validated = $this->form_validation->run();
		if ($is_validated === TRUE) {
			$arr_posted_group_id = $this->form_validation->set_value('group_id[]');
			if(!$this->as_ion_auth->group_assignable($arr_posted_group_id)){
				$this->session->set_flashdata('message', 'You do not have permissions to create a user with the user group you selected.  Please try again, or contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . '.');
				redirect("auth/create_user", 'refresh');
				die();
			}
			
			//start with nothing
			$region_id = NULL;
			$supervisor_num = NULL;
			$herd_code = NULL;
			$herd_release_code = NULL;

			//Set variables that depend on group(s) selected
			if(in_array(5, $arr_posted_group_id) || in_array(8, $arr_posted_group_id)){
				$region_id = $this->input->post('region_id[]'); //field techs and managers
				$supervisor_num = $this->input->post('supervisor_num'); //field tech only
			}
			elseif(in_array(3, $arr_posted_group_id)){
				$region_id = $this->input->post('region_id[]'); //field techs and managers
			}
			if(in_array(2, $arr_posted_group_id)){ //producers
				$herd_code = $this->input->post('herd_code') ? $this->input->post('herd_code') : NULL;
				$herd_release_code = $this->input->post('herd_release_code');
				$this->load->model('herd_model');
				$error = $this->herd_model->herd_authorization_error($herd_code, $herd_release_code);
//				if($error){
//					$this->as_ion_auth->set_error($error);
//					$is_validated = false;
//				}
			}
			$username = substr(strtolower($this->input->post('first_name')) . ' ' . strtolower($this->input->post('last_name')),0,15);
			$email = $this->input->post('email');
			$password = $this->input->post('password');
			$additional_data = array('first_name' => $this->input->post('first_name'),
				'herd_code' => $herd_code,
				'last_name' => $this->input->post('last_name'),
//				'company' => $this->input->post('company') ? $this->input->post('company') : NULL,
				'supervisor_num' => $supervisor_num,
				'region_id' => $region_id,
				'phone' => $this->input->post('phone1') . '-' . $this->input->post('phone2') . '-' . $this->input->post('phone3'),
//				'group_id' => $arr_posted_group_id,
				'section_id' => $this->input->post('section_id')
			);
			if($additional_data['phone'] == '--') $additional_data['phone'] = '';
		}
		if ($is_validated === TRUE && $this->as_ion_auth->register($username, $password, $email, $additional_data, $arr_posted_group_id)) { //check to see if we are creating the user
			//redirect them back to the admin page
			//$this->as_ion_auth->activate();
			$this->session->set_flashdata('message', "Your account has been created.  Please check your e-mail for instructions on activating your account.");
			redirect(site_url("auth/login"), 'refresh');
		}
		else { //display the create user form
				//get default group_id
			$default_group_name = $this->config->item('default_group', 'ion_auth');
			$default_group_id = $this->ion_auth_model->get_group_by_name($default_group_name)->id;
			$arr_form_group_id = $this->form_validation->set_value('group_id[]', array($default_group_id));
			$this->data['group_selected'] = $arr_form_group_id;

			$arr_form_section_id = $this->form_validation->set_value('section_id[]', array());
			$this->data['section_selected'] = $arr_form_section_id;
			
			$region_id_in = $this->form_validation->set_value('region_id[]');
			$arr_form_region_id = !empty($region_id_in) ? $region_id_in : $this->session->userdata('arr_regions');
			//set the flash data error message if there is one
			$this->page_header_data['message'] = (validation_errors() ? validation_errors() : ($this->as_ion_auth->errors() ? $this->as_ion_auth->errors() : $this->session->flashdata('message')));
//die($this->page_header_data['message']);
			
			$this->data['first_name'] = array('name' => 'first_name',
				'id' => 'first_name',
				'type' => 'text',
				'value' => $this->form_validation->set_value('first_name'),
				'size' => '25',
				'maxlength' => '50',
				'class' => 'require'
			);
			$this->data['last_name'] = array('name' => 'last_name',
				'id' => 'last_name',
				'type' => 'text',
				'value' => $this->form_validation->set_value('last_name'),
				'size' => '25',
				'maxlength' => '50',
			'class' => 'require'
			);
			$this->data['email'] = array('name' => 'email',
				'id' => 'email',
				'type' => 'text',
				'value' => $this->form_validation->set_value('email'),
				'size' => '50',
				'maxlength' => '100',
			'class' => 'require'
			);
			$this->data['herd_code'] = array('name' => 'herd_code',
				'id' => 'herd_code',
				'type' => 'text',
				'size' => '8',
				'maxlength' => '8',
				'value' => $this->form_validation->set_value('herd_code')
			);
			if(in_array('2', $arr_form_group_id)) $this->data['herd_code']['class'] = 'require';
			$this->data['herd_release_code'] = array('name' => 'herd_release_code',
				'id' => 'herd_release_code',
				'type' => 'text',
				'size' => '10',
				'maxlength' => '10',
				'value' => $this->form_validation->set_value('herd_release_code')
			);
			if(in_array('2', $arr_form_group_id)) $this->data['herd_release_code']['class'] = 'require';

			//Application Data
			if($this->as_ion_auth->has_permission("Assign Sections")){
				//$this->data['section_selected'] = array();
				$this->data['section_id'] = 'id="section_id"';
				$this->data['section_options'] = $this->as_ion_auth->ion_auth_model(array('subscription'));
			}
			if($this->as_ion_auth->has_permission("Manage Other Accounts")){
				//$this->load->model('dhi_supervisor_model');
				if($this->as_ion_auth->is_admin){
					$this->data['region_options'] = $this->as_ion_auth->get_region_dropdown_data();
					$this->data['region_selected'] = $this->form_validation->set_value('region_id[]');
					$this->data['region_id'] = 'class = "require"';
				}
				else {
					$this->data['region_id'] = array('name' => 'region_id[]',
						'id' => 'region_id',
						'type' => 'hidden',
						'class' => 'require',
						'value' => $this->session->userdata('arr_regions')
					);
				}

				$this->data['group_id'] = 'id="group_id" class = "require" multiple size="4"';
				$this->data['group_options'] = $this->as_ion_auth->get_group_dropdown_data();
				$this->data['group_selected'] = $arr_form_group_id;
				$this->data['supervisor_num_options'] = !empty($arr_form_region_id)?$this->as_ion_auth->get_dhi_supervisor_dropdown_data(array_keys($arr_form_region_id)):array();
				$this->data['supervisor_num_selected'] = $this->form_validation->set_value('supervisor_num');
				if(!empty($this->data['supervisor_num_options'])){
					$this->data['supervisor_num'] = 'class="require"';
				}
				else{
					$this->data['supervisor_num'] = array('name' => 'supervisor_num',
						'id' => 'supervisor_num',
						'type' => 'text',
						'size' => '6',
						'maxlength' => '6',
						'value' => $this->form_validation->set_value('supervisor_num'),
						'class' => 'require'
						);
				}
			}
			$this->data['phone1'] = array('name' => 'phone1',
				'id' => 'phone1',
				'type' => 'text',
				'size' => '3',
				'maxlength' => '3',
				'value' => $this->form_validation->set_value('phone1'),
			);
			$this->data['phone2'] = array('name' => 'phone2',
				'id' => 'phone2',
				'type' => 'text',
				'size' => '3',
				'maxlength' => '3',
				'value' => $this->form_validation->set_value('phone2'),
			);
			$this->data['phone3'] = array('name' => 'phone3',
				'id' => 'phone3',
				'type' => 'text',
				'size' => '4',
				'maxlength' => '4',
				'value' => $this->form_validation->set_value('phone3'),
			);
			$this->data['password'] = array('name' => 'password',
				'id' => 'password',
				'type' => 'password',
				'value' => $this->form_validation->set_value('password'),
				'class' => 'required'
			);
			$this->data['password_confirm'] = array('name' => 'password_confirm',
				'id' => 'password_confirm',
				'type' => 'password',
				'value' => $this->form_validation->set_value('password_confirm'),
				'class' => 'required'
			);
			if(is_array($this->page_header_data)){
				$this->page_header_data = array_merge($this->page_header_data,
					array(
						'title'=>'Register User - ' . $this->config->item('product_name'),
						'description'=>'Register user for ' . $this->config->item('product_name')
					)
				);
			}
			$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
//die($this->load->view('auth/section_nav', NULL, TRUE));
			$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
			$this->data['page_heading'] = 'Register User - ' . $this->config->item('product_name');
			$footer_data = array(
				'arr_deferred_js'=>array(
					$this->config->item('base_url_assets') . 'js/gs_auth_form_helper.js',
				)
			);
			$this->data['page_footer'] = $this->load->view('page_footer', $footer_data, TRUE);
			$this->load->view('auth/create_user', $this->data);
		}
	}

	//create a new user
	function edit_user($user_id = FALSE) {
		if($user_id === FALSE) $user_id = $this->session->userdata('user_id');
		//does the logged in user have permission to edit this user?
		if (!$this->as_ion_auth->logged_in()) {
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
			redirect(site_url('auth'), 'refresh');
        }
        elseif(($this->as_ion_auth->is_child_user($user_id) && ($this->as_ion_auth->has_permission("Manage Other Accounts"))) || $user_id == $this->session->userdata('user_id')){
			$this->data['title'] = "Edit Account";
			//validate form input
			$this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
			$this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
			$this->form_validation->set_rules('email', 'Email Address', 'trim|required|valid_email');
			$this->form_validation->set_rules('supervisor_num', 'Field Technician Number', 'exact_length[6]');
			$this->form_validation->set_rules('region_id[]', 'Association/Region Number');
			$this->form_validation->set_rules('phone1', 'First Part of Phone', 'exact_length[3]');
			$this->form_validation->set_rules('phone2', 'Second Part of Phone', 'exact_length[3]');
			$this->form_validation->set_rules('phone3', 'Third Part of Phone', 'exact_length[4]');
			$this->form_validation->set_rules('password', 'Password', 'trim|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]');
			$this->form_validation->set_rules('password_confirm', 'Password Confirmation', 'trim');
			$this->form_validation->set_rules('group_id[]', 'Name of Account Group');
			$this->form_validation->set_rules('gsg_access_level', 'Access Level for this user');
			$this->form_validation->set_rules('section_id[]', 'Section');
			
			$email_in = $this->input->post('email');
			$is_submitted = empty($email_in)?FALSE:TRUE;
			$is_validated = $this->form_validation->run();
			if ($is_validated === TRUE) {
				//populate data fields for specific group choices
				//set group booleans
				$arr_posted_group_id = $this->input->post('group_id');
				$is_tech_rss_rsm = count(array_intersect(array(3,5,6,7,8), $arr_posted_group_id)) > 0;
				$is_admin_dsr_consult = count(array_intersect(array(1,4,9), $arr_posted_group_id)) > 0;

				//start with the minimum
				$user_id = $this->input->post('user_id');
				$arr_posted_group_id = FALSE;
				$region_id = NULL;
				$supervisor_num = NULL;
				
				//add data as appropriate for given groups
				if($is_tech_rss_rsm){
					$arr_posted_group_id = $this->input->post('group_id');
					if(!$this->as_ion_auth->group_assignable($arr_posted_group_id)){
						$this->session->set_flashdata('message', 'You do not have permissions to edit a user with the user group you selected.  Please try again, or contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . '.');
						redirect(site_url("auth/edit_user/$user_id", 'refresh'));
						die();
					}
					$region_id = $this->input->post('region_id[]');
					$supervisor_num = $this->input->post('supervisor_num');
				}
				//if the logged in user is not a producer, and the user being edited is not a technician, rss or regional manager(admin, dsr, consultant)
				elseif($is_admin_dsr_consult){
					$arr_posted_group_id = $this->input->post('group_id');
					if(!$this->as_ion_auth->group_assignable($arr_posted_group_id)){
						$this->session->set_flashdata('message', 'You do not have permissions to edit a user with the user group you selected.  Please try again, or contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . '.');
						redirect(site_url("auth/edit_user/$user_id"), 'refresh');
						die();
					}
				}
				$obj_user = $this->ion_auth_model->user($user_id)->row();
				if($this->input->post('herd_code') && $this->input->post('herd_code') != $obj_user->herd_code){
					$herd_code = $this->input->post('herd_code') ? $this->input->post('herd_code') : NULL;
					$herd_release_code = $this->input->post('herd_release_code');
					$this->load->model('herd_model');
					$error = $this->herd_model->herd_authorization_error($herd_code, $herd_release_code);
					if($error){
						$this->as_ion_auth->set_error($error);
						$is_validated = false;
					}
				}
				
				//populate
				$username = substr(strtolower($this->input->post('first_name')) . ' ' . strtolower($this->input->post('last_name')),0,15);
				$email = $this->input->post('email');
				$data = array('username' => $username,
					'email' => $email,
					'first_name' => $this->input->post('first_name'),
					'last_name' => $this->input->post('last_name'),
//					'company' => $this->input->post('company') ? $this->input->post('company') : NULL,
					'phone' => $this->input->post('phone1') . '-' . $this->input->post('phone2') . '-' . $this->input->post('phone3'),
					'group_id' => $arr_posted_group_id,
					'supervisor_num' => $supervisor_num,
					'region_id' => $region_id,
					'herd_code' => $this->input->post('herd_code') ? $this->input->post('herd_code') : NULL
				);
				if($data['phone'] == '--') $data['phone'] = '';
				if(isset($_POST['gsg_access_level'])) $data['access_level'] = $this->input->post('gsg_access_level');
				if(isset($_POST['section_id'])) $data['section_id'] = $this->input->post('section_id');
				$password = $this->input->post('password');
				if(!empty($password)) $data['password'] = $password;
			}
			if ($is_validated === TRUE && $this->ion_auth_model->update($user_id, $data)) { //check to see if we are creating the user
				//redirect them back to the admin page
				//$this->as_ion_auth->activate();
				$this->session->set_flashdata('message', "Account Edited");
				redirect(site_url("auth"), 'refresh');
			}
			else { //display the edit user form
				if(isset($obj_user) === FALSE) $obj_user = $this->ion_auth_model->user($user_id)->row();
				$obj_user->arr_groups = array_keys($this->ion_auth_model->get_users_group_array($obj_user->id));
				//get default group_id
				if(empty($obj_user->arr_groups)){ //if no group is set, set the default group
					$default_group_name = $this->config->item('default_group', 'ion_auth');
					$obj_user->arr_groups = array($this->ion_auth_model->get_group_by_name($default_group_name)->id);
				}
				$arr_form_group_id = $this->form_validation->set_value('group_id[]', $obj_user->arr_groups);
				$this->data['group_selected'] = $arr_form_group_id;

				$obj_user->section_id = $this->as_ion_auth->set_form_array($this->ion_auth_model->get_subscribed_sections_array($obj_user->arr_groups, $user_id, $this->as_ion_auth->super_section_id), 'id', 'id'); // populate array of sections for which user is enrolled
				$arr_form_section_id = $this->form_validation->set_value('section_id[]', $obj_user->section_id);
				$this->data['section_selected'] = $arr_form_section_id;

				$arr_form_region_id = $this->form_validation->set_value('region_id[]', !empty($obj_user->region_id) ? $obj_user->region_id : $this->session->userdata('arr_regions'));
				$this->data['region_selected'] = $arr_form_region_id;
				$form_supervisor_num = $this->form_validation->set_value('supervisor_num', !empty($obj_user->supervisor_num) ? $obj_user->supervisor_num : $this->session->userdata('supervisor_num'));
				
				//set the flash data error message if there is one
/****** MESSAGE NEEDS TO GO TO HEADER, NOT PAGE ****/
				$this->data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());
				
				$this->data['first_name'] = array('name' => 'first_name',
					'id' => 'first_name',
					'type' => 'text',
					'size' => '25',
					'maxlength' => '50',
					'value' => $this->form_validation->set_value('first_name', $obj_user->first_name),
					'class' => 'require'
				);
				$this->data['last_name'] = array('name' => 'last_name',
					'id' => 'last_name',
					'type' => 'text',
					'size' => '25',
					'maxlength' => '50',
					'value' => $this->form_validation->set_value('last_name', $obj_user->last_name),
					'class' => 'require'
				);
				$this->data['email'] = array('name' => 'email',
					'id' => 'email',
					'type' => 'text',
					'size' => '50',
					'maxlength' => '100',
					'value' => $this->form_validation->set_value('email', $obj_user->email),
					'class' => 'require'
				);
				//if($this->as_ion_auth->is_admin || $this->as_ion_auth->is_manager){ // and the manager is editing a non-manager
					$this->data['group_options'] = $this->as_ion_auth->get_group_dropdown_data();
					$this->data['group_id'] = 'id="group_id" class = "require" multiple size="4"';
				//}
				/*else {
					$this->data['group_id'] = array('name' => 'group_id',
						'id' => 'group_id',
						'type' => 'hidden',
						'value' => $arr_form_group_id,
					);
				}*/

				if($this->as_ion_auth->has_permission("Assign Sections")){
					$this->data['section_id'] = 'id="section_id"';
					$this->data['section_options'] = $this->ion_auth_model->get_keyed_section_array(array('subscription'));
						$this->data[] = $obj_user->section_id;
				}

				//if(in_array('3', $arr_form_group_id) || in_array('5', $arr_form_group_id) || in_array('6', $arr_form_group_id) || in_array('7', $arr_form_group_id) || in_array('8', $arr_form_group_id)){
				//	if($this->as_ion_auth->is_admin){
						$this->data['region_options'] = $this->as_ion_auth->get_region_dropdown_data();
						$this->data['region_selected'] = $this->form_validation->set_value('region_id[]', $obj_user->region_id);
						if(in_array('3', $arr_form_group_id) || in_array('5', $arr_form_group_id) || in_array('6', $arr_form_group_id) || in_array('7', $arr_form_group_id) || in_array('8', $arr_form_group_id)) $this->data['region_id'] = 'class = "require"';
						else $this->data['region_id'] = 'class = "require"';
				//	}
				/*	else {
						$this->data['region_id[]'] = array('name' => 'region_id[]',
							'id' => 'region_id',
							'type' => 'hidden',
							'value' => $this->form_validation->set_value('region_id', $obj_user->region_id),
						);
					} */
				//}
				//if(in_array('5', $arr_form_group_id) || in_array('7', $arr_form_group_id) || in_array('8', $arr_form_group_id)){
				//	if($this->as_ion_auth->is_admin || $this->as_ion_auth->is_manager){
						$this->data['supervisor_num_options'] = !empty($arr_form_region_id)?$this->as_ion_auth->get_dhi_supervisor_dropdown_data($arr_form_region_id):array();
						$this->data['supervisor_num_selected'] = $this->form_validation->set_value('supervisor_num', 0);//$obj_user->supervisor_num);
						if(!empty($this->data['supervisor_num_options'])){
							$this->data['supervisor_num'] = 'class = "require"';
						}
						else{
							$this->data['supervisor_num'] = array('name' => 'supervisor_num',
								'id' => 'supervisor_num',
								'type' => 'text',
								'size' => '6',
								'maxlength' => '6',
								'class' => 'require',
								'value' => 0
//								'value' => $this->form_validation->set_value('supervisor_num', $obj_user->supervisor_num)
								);
						}
				//	}
				/*	else{
						$this->data['supervisor_num'] = array('name' => 'supervisor_num',
							'id' => 'supervisor_num',
							'type' => 'hidden',
							'value' => $this->form_validation->set_value('supervisor_num', $obj_user->supervisor_num),
						);
					}*/
				//}
				//herd info
				$this->data['herd_code'] = array('name' => 'herd_code',
					'id' => 'herd_code',
					'type' => 'text',
					'size' => '8',
					'maxlength' => '8',
					'value' => $this->form_validation->set_value('herd_code', $obj_user->herd_code)
				);
				if(in_array('2', $arr_form_group_id)) $this->data['herd_code']['class'] = 'require';
				$this->data['herd_release_code'] = array('name' => 'herd_release_code',
					'id' => 'herd_release_code',
					'type' => 'text',
					'size' => '10',
					'maxlength' => '10',
					'value' => $this->form_validation->set_value('herd_release_code')
				);

				//more general info
				$phone1 = '';
				$phone2 = '';
				$phone3 = '';
				if(!$is_submitted && !empty($obj_user->phone)){
					$arr_phone = explode('-', $obj_user->phone);
					if(count($arr_phone) == 3) list($phone1, $phone2, $phone3) = explode('-', $obj_user->phone);
				}
				$this->data['phone1'] = array('name' => 'phone1',
					'id' => 'phone1',
					'type' => 'text',
					'size' => '3',
					'maxlength' => '3',
					'value' => $this->form_validation->set_value('phone1', $phone1)
				);
				$this->data['phone2'] = array('name' => 'phone2',
					'id' => 'phone2',
					'type' => 'text',
					'size' => '3',
					'maxlength' => '3',
					'value' => $this->form_validation->set_value('phone2', $phone2)
				);
				$this->data['phone3'] = array('name' => 'phone3',
					'id' => 'phone3',
					'type' => 'text',
					'size' => '4',
					'maxlength' => '4',
					'value' => $this->form_validation->set_value('phone3', $phone3)
				);
				$this->data['user_id'] = array('name' => 'user_id',
					'id' => 'user_id',
					'type' => 'hidden',
					'value' => $user_id
				);
				$this->data['password'] = array('name' => 'password',
					'id' => 'password',
					'type' => 'password',
					'value' => $this->form_validation->set_value('password')
				);
				$this->data['password_confirm'] = array('name' => 'password_confirm',
					'id' => 'password_confirm',
					'type' => 'password',
					'value' => $this->form_validation->set_value('password_confirm')
				);
				if(is_array($this->page_header_data)){
					$this->page_header_data = array_merge($this->page_header_data,
						array(
							'title'=>'Edit User - ' . $this->config->item('product_name'),
							'description'=>'Edit user for ' . $this->config->item('product_name')
						)
					);
				}
				$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
				$this->footer_data = array(
					'arr_deferred_js'=>array(
						$this->config->item('base_url_assets') . 'js/gs_auth_form_helper.js',
					)
				);
				$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, true);
				$this->data['page_heading'] = 'Edit User - ' . $this->config->item('product_name');
				$this->data['page_footer'] = $this->load->view('page_footer', $this->footer_data, true);
				$this->load->view('auth/edit_user', $this->data);
			}
       	}
       	else {
       		$this->session->set_flashdata('message', "You do not have permission to edit the requested account.");
       		redirect(site_url());
       	}
	}
	
	function set_role($group_id){
		if(array_key_exists($group_id, $this->session->userdata('arr_groups'))){
			$this->session->set_userdata('active_group_id', $group_id);
		}
		else {
			$this->session->set_flashdata('message', "You do not have rights to the requested group.");
		}
		redirect(site_url($this->as_ion_auth->referrer));
	}
	
	// used with dashboard graph
	function graph_snapshot($report, $type = NULL) {
		$this->load->helper('report_chart_helper');
		$graph['config'] = array(
			'xAxis' => array(
				'title' => array(
					'text' => 'Days in Milk, Current Lactation'
				)
			),
			'yAxis' => array(
				0 => array(
					'title' => array(
						'text' => 'Avg Linear Score'
					)
				),
				1 => array(
					'title' => array(
						'text' => 'Net Merit Amt'
					),
					'opposite' => "TRUE"
				)
			),
			'series' => array(
				0 => array(
					'name' => 'Avg Linear Score',
					'yAxis' => '0'
				),
				1=> array(
					'name' => 'Net Merit Amt',
					'yAxis' => '1'
				)
			),
			'title' => array(
				'text' => 'Lactation Graph 3'
			),
			'subtitle' => array(
				'text' => 'Avg Linear Score and Net Merit'
			),
		);
		$this->load->library('chart');
		$graph['data'] = $this->chart->get_sample_graph_data('35991623', 'M');

		$return_val = prep_output($output, $graph, $report_count, $return_output);
		if($return_val) return $return_val;

/*	    if ($type == 'ajax') // load inline view for call from ajax
	        $this->load->view('data', $graph);

	    else if ($type == 'chart') {
			// Set the JSON header
			header("Content-type: text/javascript");
			$graph['config']['chart']['renderTo'] = '';
			$return_val = 'process_chart(' . json_encode($graph) . ');';
	    	echo $return_val;
	    	//echo $this->cow_heifer . ' - ' . $this->herd_code . ' - ' . $this->animal_model;
	    	exit;
	    }

	    else // load the default view
	        var_dump($graph);
	    	//$this->load->view('default', $graph);
*/	}

}
