<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
class Access_log extends parent_report {
	protected $arr_filters;
	protected $arr_keyed_pages;
	protected $arr_keyed_sections;
	protected $arr_keyed_pages; //[section_id][id]=name

	function __construct(){
		$this->section_path = 'access_log'; //this should match the name of this file (minus ".php".  Also used as base for css and js file names
		$this->primary_model = 'access_log_model';
		parent::__construct();
		$this->section_id = 3;
		$this->report_form_id = 'report-filter';
		$this->product_name = 'Access Log';
		$this->report_path = 'access_log';
		$this->herd_code = strlen($this->session->userdata('herd_code')) == 8?$this->session->userdata('herd_code'):NULL;
		if($this->authorize()) { //authorize function is in report parent class
			if(!$this->as_ion_auth->is_admin){
				$this->session->set_flashdata('message', 'You must be an administrator to access ' . $this->product_name);
				redirect(site_url('alert/display'));
			}
			$this->load->library('reports');
			$this->reports->herd_code = $this->herd_code;
			$this->arr_pages = $this->access_log_model->arr_pages;
			$this->arr_filters = array('access_time', 'sections', 'pages', 'format', 'groups', 'user_region_id', 'user_tech_num', 'herd_code');
//			$this->arr_keyed_pages = $this->access_log_model->get_keyed_page_array();
			$this->arr_keyed_sections = $this->ion_auth_model->get_keyed_section_array();
			$this->arr_keyed_pages = $this->access_log_model->get_keyed_page_array();
		}
		else {
			if($this->session->flashdata('message')) $this->session->keep_flashdata('message');
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
			redirect(site_url('auth/index'), 'refresh');
		}
		/* Load the profile.php config file if it exists
		$this->config->load('profiler', false, true);
		if ($this->config->config['enable_profiler']) {
			$this->output->enable_profiler(TRUE);
		} */
	}

	function index(){
		redirect(site_url('access_log/display'));
	}

	function display($sort_by = 'access_time', $sort_order = 'DESC', $display_format = NULL, $section_id = '') {
		$this->load->helper('multid_array');
		$this->load->library('form_validation');
		//not currently used, but makes sorts on multiple fields possible
		$arr_sort_by = array_values(explode('|', trim(urldecode($sort_by))));
		$arr_sort_order = array_values(explode('|', trim(urldecode($sort_order))));

		//validate form input for filters
		$this->form_validation->set_rules('user_region_id', 'Association Number (if a member of an association)', 'exact_length[3]');
		$this->form_validation->set_rules('user_tech_num', 'Field Technician Number', 'trim|max_length[6]');
		$this->form_validation->set_rules('herd_code', 'Herd Code', 'trim|max_length[8]');
		$this->form_validation->set_rules('group_id', 'User Group ID');
		$this->form_validation->set_rules('access_time_dbfrom', 'Access Date From', 'trim');
		$this->form_validation->set_rules('access_time_dbfrom', 'Access Date To', 'trim');
		$this->form_validation->set_rules('page_id', 'Page');
		$this->form_validation->set_rules('format', 'Format');
		$this->form_validation->set_rules('section_id', 'Section');


		//handle filters
		if ($this->form_validation->run() == TRUE) { //successful submission
			$arr_filter_criteria['section_id'] = is_array($this->input->post('section_id', TRUE))?$this->input->post('section_id', TRUE):array();
			$arr_filter_criteria['page_id'] = is_array($this->input->post('page_id', TRUE))?$this->input->post('page_id', TRUE):array();
			$arr_filter_criteria['format'] = is_array($this->input->post('format', TRUE))?$this->input->post('format', TRUE):array();
			$arr_filter_criteria['group_id'] = is_array($this->input->post('group_id', TRUE))?$this->input->post('group_id', TRUE):array();
			$arr_filter_criteria['herd_code'] = $this->input->post('herd_code');
			$arr_filter_criteria['user_region_id'] = $this->input->post('user_region_id');
			$arr_filter_criteria['user_tech_num'] = $this->input->post('user_tech_num');
			$arr_filter_criteria['access_time_dbfrom'] = $this->input->post('access_time_dbfrom');
			$arr_filter_criteria['access_time_dbto'] = $this->input->post('access_time_dbto');
		}
		//if no form is submitted, check to see if an section id was passed in the url
		else {
			$arr_filter_criteria = array(
				'page_id' => array(),
				'format' => array(),
				'section_id' => array(),
				'group_id' => array(),
				'access_time_dbfrom' => date('m-d-Y',strtotime("-1 month")),
				'access_time_dbto' => date('m-d-Y')
			);
			//Set section_id if passed in URL
			if(!empty($section_id)) $arr_filter_criteria['section_id'][] = $section_id;
		}

		if(validation_errors()) $this->download_log_model->arr_messages[] = validation_errors();
		//end handle filters
		// get dataset
		$arr_data_fields = $this->access_log_model->get_fields();
		$results = $this->access_log_model->search($this->session->userdata('herd_code'), $arr_filter_criteria, $arr_sort_by, $arr_sort_order);
		if(is_array($this->access_log_model->arr_messages) && array_key_exists('filter_alert', $this->access_log_model->arr_messages)) {
			$arr_auto_criteria_field = $this->access_log_model->get_auto_filter_criteria();
			$arr_criteria_is_array = array('pstring', 'decision_guide_qtile_num', 'lact_num');
			foreach($arr_auto_criteria_field as $acf){
				if(in_array($acf['key'], $arr_criteria_is_array)) $arr_filter_criteria[$acf['key']][] = $acf['value'];
				else $arr_filter_criteria[$acf['key']] = $acf['value'];
			}
		}
		$arr_filter_text = $this->reports->filters_to_text($arr_filter_criteria, NULL);
		$log_filter_text = is_array($arr_filter_text) && !empty($arr_filter_text)?implode('; ', $arr_filter_text):'';
		// end get dataset

		$this->reports->sort_text($arr_sort_by, $arr_sort_order);//this function sets text, and could return it if needed

		if ($display_format == 'csv'){
			if(is_array($results) && !empty($results)){
				$this->load->library('Reports');
				$this->reports->create_csv($results);
				$this->access_log_model->write_entry(5, 'csv', $this->reports->sort_text_brief($arr_sort_by, $arr_sort_order), $log_filter_text);
				exit;
			}
			else {
				$this->access_log_model->arr_messages[] = 'There is no data to export into an Excel file.';
			}
			exit;
		}

		elseif ($display_format == 'pdf') {
			$this->load->library('Reports');
			$this->load->helper('table_header');
			$arr_pdf_widths = $this->access_log_model->get_pdf_widths();
			$header_structure = get_table_header_array($arr_data_fields, $arr_pdf_widths);

			$this->reports->create_pdf(array($results), $this->product_name, $arr_filter_text, $arr_pdf_widths, NULL, $header_structure);
			$this->access_log_model->write_entry(5, 'pdf', $this->reports->sort_text_brief($arr_sort_by, $arr_sort_order), $log_filter_text);
			exit;
		}
//		else{
			// set up table header
			$this->load->helper('table_header');
			$table_header_data = array(
				'arr_unsortable_columns' => $this->access_log_model->get_unsortable_columns(),
				'form_id' => $this->report_form_id,
				'report_path' => $this->report_path,
				'arr_sort_by' => $arr_sort_by,
				'arr_sort_order' => $arr_sort_order,
				'structure' => get_table_header_array($arr_data_fields), //table header helper function
				'arr_field_sort' => $this->access_log_model->get_field_sort(),
				'arr_header_data' => $arr_data_fields
			);

			$table_data = array(
				'fields' => array_flatten($arr_data_fields),
				'table_header' => $this->load->view('table_header', $table_header_data, TRUE),
				'report_data' => $results,
				'table_id' => 'main-report'
			);
			unset($table_header_data);

			//set other header vars
			$this->carabiner->css('agsource.datepick.css', 'screen');
			$this->carabiner->css('jquery.datetimeentry.css', 'screen');
			$this->carabiner->css('report.css', 'screen');
			$this->carabiner->css('filters.css', 'screen');
			if(is_array($this->page_header_data)){
				$this->page_header_data = array_merge($this->page_header_data,
					array(
						'title' => $this->product_name,
						'description' => $this->product_name,
						'messages' => $this->access_log_model->arr_messages,
						'page_heading' => $this->product_name,
						'section_nav' => $this->load->view('auth/section_nav', NULL, TRUE),
						'arr_headjs_line'=>array(
							'{datepick: "' . $this->config->item("base_url_assets") . 'js/jquery/jquery.datepick.min.js"}',
							'{access_helper: "' . $this->config->item("base_url_assets") . 'js/gs_access_log_helper.js"}'
						)
					)
				);
			}
			unset($this->access_log_model->arr_messages); //clear message var once it is displayed

			$footer_data = array();

		//filters and csv/pdf links
		//set up filters form
			$this->filter_data = array(
				'sort_by' => $arr_sort_by,
				'sort_order' => $arr_sort_order,
				'arr_filters' => $this->arr_filters,
				'report_path' => $this->report_path,
				'user_region_id' => '',
				'region_options' => $this->as_ion_auth->get_region_dropdown_data(),
				'region_selected' => $this->form_validation->set_value('user_region_id'),
				'user_tech_num' => array('name' => 'user_tech_num',
					'id' => 'user_tech_num',
					'type' => 'text',
					'size' => '6',
					'maxlength' => '6',
					'value' => $this->form_validation->set_value('user_tech_num')
				),
				'herd_code' => array('name' => 'herd_code',
					'id' => 'herd_code',
					'type' => 'text',
					'size' => '8',
					'maxlength' => '8',
					'value' => $this->form_validation->set_value('herd_code')
				),
				'group_id' => '',
				'group_options' => $this->as_ion_auth->get_group_dropdown_data(),
				'group_selected' => $arr_filter_criteria['group_id'],
				'section_id' => '',
				'section_options' => $this->arr_keyed_sections,
				'section_selected' => $arr_filter_criteria['section_id'],
				'page_id' => '',
				'page_options' => $this->arr_keyed_pages,
				'page_selected' => $arr_filter_criteria['page_id'],
				'format' => '',
				'format_options' => array('web' => 'Web', 'csv' => 'CSV', 'pdf' => 'PDF'),
				'format_selected' => $arr_filter_criteria['format'],
				'access_time_dbto' => array('name' => 'access_time_dbto',
					'id' => 'access_time_dbto',
					'type' => 'text',
					'value' => $arr_filter_criteria['access_time_dbto'],
					'size' => '10'
				),
				'access_time_dbfrom' => array('name' => 'access_time_dbfrom',
					'id' => 'access_time_dbfrom',
					'type' => 'text',
					'value' => $arr_filter_criteria['access_time_dbfrom'],
					'size' => '10'
				)
			);
			$table_contents = array(
				'link_url' => site_url($this->section_path), 
				'data_table' => $this->load->view('report_table', $table_data, TRUE),
				'table_title' =>  $this->product_name
			);

			$data = array(
				'page_header' => $this->load->view('page_header', $this->page_header_data, TRUE),
				'filters' => $this->load->view('auth/access_filters', $this->filter_data, TRUE),
//				'download_links' => $this->load->view('download_links', $this->filter_data, TRUE),
				'sort_by' => $arr_sort_by,
				'sort_order' => $arr_sort_order,
				'report_table' => array($this->load->view('table', $table_contents, TRUE)),
				'charts' => array('table' => array($this->load->view('table', $table_contents, TRUE))),
				'page_footer' => $this->load->view('page_footer', $footer_data, TRUE),
			);
			$this->load->view('report', $data);
			$this->access_log_model->write_entry(5, 'web', $this->reports->sort_text_brief($arr_sort_by, $arr_sort_order), $log_filter_text); //10 is the page code for Benchmark Activity view
//		}
	}
}