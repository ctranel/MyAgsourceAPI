<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/report_parent.php';
class Download_log extends parent_report {
	protected $arr_filters;
	protected $arr_keyed_pages;

	function __construct(){
		parent::__construct();
		$this->section_id = 3;
		$this->report_form_id = 'filter-form';
		$this->product_name = 'Benchmark Download Log';
		$this->report_path = 'bench_report/download_log';
		$this->herd_code = strlen($this->session->userdata('herd_code')) == 8?$this->session->userdata('herd_code'):NULL;
		$this->arr_filters = array('access_time', 'pages', 'user_region_id');

		if($this->authorize()) { //authorize function is in report parent class
			if(!$this->as_ion_auth->is_admin){
				$this->session->set_flashdata('message', 'You must be an administrator to access ' . $this->product_name);
				redirect(site_url('auth/index'));
			}
			$this->load->model('bench_report/download_log_model');
			$this->load->library('reports');
			$this->reports->herd_code = $this->herd_code;
			$this->arr_pages = $this->download_log_model->arr_pages;
			//there are no pages for this section ($arr_pages), but the section is used to report on pages from the benchmarks app ($arr_keyed_pages)
			$this->arr_keyed_pages = $this->download_log_model->get_keyed_page_array();
		}
		else {
			if($this->session->flashdata('message')) $this->session->keep_flashdata('message');
			redirect('auth/index', 'refresh');
		}
		/* Load the profile.php config file if it exists
		$this->config->load('profiler', false, true);
		if ($this->config->config['enable_profiler']) {
			$this->output->enable_profiler(TRUE);
		} */
	}

	function index(){
		redirect(site_url('bench_report/download_log/display'));
	}

/**
 * @method display() - displays the genetic selection guide
 *
 * @access	public
 * @param	string - pipe delimited list of sort fields
 * @param	string - pipe delimited list of sort orders that correspond with the first parameter
 * @param	string - output format (csv, pdf, defaults to screen)
 * @return	void
 */
	function display($sort_by = 'access_time', $sort_order = 'DESC', $display_format = NULL) {
		$this->load->helper('multid_array');

		$arr_sort_by = array_values(explode('|', trim(urldecode($sort_by))));
		$arr_sort_order = array_values(explode('|', trim(urldecode($sort_order))));

		//validate form input for filters
		$this->load->library('form_validation');
		$this->form_validation->set_rules('user_region_id', 'Association Number (if a member of an association)', 'exact_length[3]');
		$this->form_validation->set_rules('access_time_dbfrom', 'Access Date From', 'trim');
		$this->form_validation->set_rules('access_time_dbfrom', 'Access Date To', 'trim');
		$this->form_validation->set_rules('page_id', 'Event');

		//handle filters
		if ($this->form_validation->run() == TRUE) { //successful submission
			$arr_filter_criteria['page_id'] = is_array($this->input->post('page_id', TRUE))?$this->input->post('page_id', TRUE):array();
			$arr_filter_criteria['user_region_id'] =is_array($this->input->post('user_region_id', TRUE))?$this->input->post('user_region_id', TRUE):array();
			$arr_filter_criteria['access_time_dbfrom'] = $this->input->post('access_time_dbfrom');
			$arr_filter_criteria['access_time_dbto'] = $this->input->post('access_time_dbto');
		}
		else {
			$arr_filter_criteria = array(
				'page_id' => array(),
				'group_id' => array(),
				'access_time_dbfrom' => date('m-d-Y',strtotime("-1 month")),
				'access_time_dbto' => date('m-d-Y')
			);
		}
		if(validation_errors()) $this->download_log_model->arr_messages[] = validation_errors();
		//end handle filters

		// get dataset
		$arr_data_fields = $this->download_log_model->get_fields();
		$results = $this->download_log_model->search($this->session->userdata('herd_code'), $arr_filter_criteria, $arr_sort_by, $arr_sort_order);
		if(is_array($this->download_log_model->arr_messages) && array_key_exists('filter_alert', $this->download_log_model->arr_messages)) {
			$arr_auto_criteria_field = $this->download_log_model->get_auto_filter_criteria();
			$arr_criteria_is_array = array('page_id');
			foreach($arr_auto_criteria_field as $acf){
				if(in_array($acf['key'], $arr_criteria_is_array)) $arr_filter_criteria[$acf['key']][] = $acf['value'];
				else $arr_filter_criteria[$acf['key']] = $acf['value'];
			}
		}
		//convert page_ids to text
		if(isset($arr_filter_criteria['page_id']) && is_array($arr_filter_criteria['page_id'])){
			foreach($arr_filter_criteria['page_id'] as $k => $v) $arr_filter_criteria['page_id'][$k] = $this->arr_keyed_pages[$v];
		}
		$arr_filter_text = $this->reports->filters_to_text($arr_filter_criteria, NULL);
		$log_filter_text = is_array($arr_filter_text) && !empty($arr_filter_text)?implode('; ', $arr_filter_text):'';		//end handle filters
		// end get dataset

		$this->reports->sort_text($arr_sort_by, $arr_sort_order);//this function sets text, and could return it if needed

		if ($display_format == 'csv'){
			if(is_array($results) && !empty($results)){
				$this->reports->create_csv($results);
				$this->access_log_model->write_entry($this->arr_pages[$block]['id'], 'csv', $this->reports->sort_text_brief($arr_sort_by, $arr_sort_order), $log_filter_text); //5 is the page code for GSG CSV download
				exit;
			}
			else {
				$this->download_log_model->arr_messages[] = 'There is no data to export into an Excel file.';
			}
		}
		elseif ($display_format == 'pdf') {
			$this->load->helper('table_header');
			$arr_pdf_widths = $this->download_log_model->get_pdf_widths();
			$header_structure = get_table_header_array($arr_data_fields, $arr_pdf_widths);
			$this->reports->create_pdf(array($results), $this->product_name, $arr_filter_text, $arr_pdf_widths, NULL, $header_structure);
			$this->access_log_model->write_entry($this->arr_pages[$block]['id'], 'pdf', $this->reports->sort_text_brief($arr_sort_by, $arr_sort_order), $log_filter_text); //6 is the page code for PDF download, 1 is the section id for GSG
			exit;
		}

//		else{
			// set up table header
			$this->load->helper('table_header');
			$table_header_data = array(
				'arr_unsortable_columns' => $this->download_log_model->get_unsortable_columns(),
				'form_id' => $this->report_form_id,
				'report_path' => $this->report_path,
				'arr_sort_by' => $arr_sort_by,
				'arr_sort_order' => $arr_sort_order,
				'structure' => get_table_header_array($arr_data_fields), //table header helper function
				'arr_field_sort' => $this->download_log_model->get_field_sort(),
				'arr_header_data' => $arr_data_fields,
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
						'title'=>$this->config->item('product_name'),
						'description'=>$this->config->item('product_name'),
						'messages' => $this->download_log_model->arr_messages,
						'page_heading' => $this->product_name,
						'section_nav' => $this->load->view('bench_report/section_nav', NULL, TRUE),
						'arr_headjs_line'=>array(
							'{datepick: "' . $this->config->item("base_url_assets") . 'js/jquery/jquery.datepick.min.js"}',
							'{log_helper: "' . $this->config->item("base_url_assets") . 'js/as_download_log_helper.js"}'
						)
					)
				);
			}
			unset($this->download_log_model->arr_messages); //clear message var once it is displayed

			$page_footer_data = array(
			);

			//filters and csv/pdf links
			$filter_data = array(
				'sort_by'=>$arr_sort_by,
				'sort_order'=>$arr_sort_order,
				'arr_filters'=>$this->arr_filters,
				'report_path'=>$this->report_path,
//				'user_region_id' => '',
//				'region_options' => $this->as_ion_auth->get_region_dropdown_data(),
//				'region_selected' => $this->form_validation->set_value('user_region_id'),
				'page_id' => '',
				'page_options' => $this->arr_keyed_pages,
				'page_selected' => $arr_filter_criteria['page_id'],
				'access_time_dbto' => array(
					'name' => 'access_time_dbto',
					'id' => 'access_time_dbto',
					'type' => 'text',
					'value' => $arr_filter_criteria['access_time_dbto'],
					'size' => '10'
				),
				'access_time_dbfrom' => array(
					'name' => 'access_time_dbfrom',
					'id' => 'access_time_dbfrom',
					'type' => 'text',
					'value' => $arr_filter_criteria['access_time_dbfrom'],
					'size' => '10'
				)
			);

			$table_contents = array(
				'data_table' => $this->load->view('report_table', $table_data, TRUE),
				'table_title' =>  $this->product_name
			);

			$data = array(
				'page_header' => $this->load->view('page_header', $this->page_header_data, TRUE),
				'filters' => $this->load->view('bench_report/filters', $filter_data, TRUE),
				//'download_links' => $this->load->view('download_links', $filter_data, TRUE),
				'sort_by' => $arr_sort_by,
				'sort_order' => $arr_sort_order,
				'report_table' => array($this->load->view('table', $table_contents, TRUE)),
				'page_footer' => $this->load->view('page_footer', $page_footer_data, TRUE),
				'message' => (validation_errors()) ? validation_errors() : $this->session->flashdata('message')
			);

			$this->load->view('report', $data);
			$this->access_log_model->write_entry($this->arr_pages[0]['id'], 'web', $this->reports->sort_text_brief($arr_sort_by, $arr_sort_order), $log_filter_text); //10 is the event code for Benchmark Activity view
		//}
	}
}