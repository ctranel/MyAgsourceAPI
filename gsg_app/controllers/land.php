<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Land extends CI_Controller {
	protected $page_header_data;
	protected $footer_data;
	protected $data;
	
	function __construct(){
		parent::__construct();
		if(!isset($this->as_ion_auth) || $this->session->userdata('herd_code') === FALSE){
			$this->session->keep_flashdata('redirect_url');
			redirect(site_url('auth/login'));
		}
		$this->page_header_data['user_sections'] = $this->as_ion_auth->arr_user_super_sections;
		$this->page_header_data['num_herds'] = $this->as_ion_auth->get_num_viewable_herds($this->session->userdata('user_id'), $this->session->userdata('arr_regions'));
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
	function index($pstring = NULL){
		$arr_pstring = $this->session->userdata('arr_pstring');
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
		
		$this->page_header_data['message'] = $this->session->flashdata('message');

		// Select modules for logged in user

		$this->carabiner->css('dashboard.css', 'screen');
//		$this->carabiner->css('accordion.css', 'screen');
		$this->carabiner->css('benchmarks.css');
		$this->carabiner->css('report.css');
//		$this->carabiner->css('chart.css', 'print');
		$this->carabiner->css('report.css', 'print');
		$this->carabiner->css('boxes.css');
		if(is_array($this->page_header_data)){
			$this->page_header_data = array_merge($this->page_header_data,
				array(
					'title'=>'Dashboard - ' . $this->config->item("product_name"),
					'description'=>'Dashboard for ' . $this->config->item("product_name"),
					'arr_headjs_line'=>array(
//						'{twitter: "' . $this->config->item("base_url_assets") . 'js/jquery/jquery.jtweetsanywhere-1.2.1.min.js"}',
//						'{graph_helper: "' . $this->config->item("base_url_assets") . 'js/charts/graph_helper.js"}',
						'{helper: "' . $this->config->item("base_url_assets") . 'js/as_dashboard_helper.js"}'
					)
				)
			);
		}

		//header and footer
		$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
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

		$resource_data = Array('email' => $this->session->userdata('email'), 'name' => $this->session->userdata('first_name') . ' ' . $this->session->userdata('last_name'));
		$this->data['widget']['info'][] = array(
			'content' => $this->load->view('auth/dashboard/resources', $resource_data, TRUE),
			'title' => 'Resources'
		);

		require_once APPPATH . 'controllers/dhi/herd_overview.php';
		$tmp = new Herd_overview();
		$arr_content = $tmp->index($pstring);
		$this->data['widget']['feature'][] = array(
			'content' => $arr_content['content'],
			'title' => $arr_content['title'],
			'subtitle' => $arr_content['subtitle'],
		);

		if($this->as_ion_auth->has_permission('Update SG Access')){
			$consultants_by_status = $this->ion_auth_model->get_consultants_by_herd($this->session->userdata('herd_code'));
			if(isset($consultants_by_status['open']) && is_array($consultants_by_status['open'])){
				$this->data['widget']['feature'][] = array(
					'content' => $this->_set_consult_section($consultants_by_status['open'], 'open', array('Grant Access', 'Deny Access')),
					'title' => 'Open Consultant Requests'
				);
			}
		}
		
		$product_data = Array('sections' => $this->as_ion_auth->get_promo_sections());
		if(isset($product_data['sections']) && !empty($product_data['sections'])){
			$this->data['widget']['info'][] = array(
				'content' => $this->load->view('auth/dashboard/other_products', $product_data, TRUE),
				'title' => 'Other Products'
			);
		}

		//page
		$nav_data = array(
				'arr_pstring' => $arr_pstring
				,'curr_pstring' => $pstring
		);
		$this->data['report_nav'] = $this->load->view('auth/dashboard/report_nav', $nav_data, TRUE);
		
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
