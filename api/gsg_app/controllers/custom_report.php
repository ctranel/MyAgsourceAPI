<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/ionauth.php';
require_once(APPPATH . 'libraries/dhi/HerdAccess.php');
require_once APPPATH . 'libraries/CustomReport.php';

use \myagsource\dhi\HerdAccess;
use \myagsource\CustomReport;

class Custom_report extends MY_Controller {
	protected $page_header_data;

	function __construct()
	{
		parent::__construct();
		$this->page_header_data['num_herds'] = $this->herd_access->getNumAccessibleHerds($this->session->userdata('user_id'), $this->permissions->permissionsList(), $this->session->userdata('arr_regions'));
		$this->page_header_data['navigation'] = $this->load->view('navigation', [], TRUE);
		
		//load necessary files
		$this->load->model('custom_report_model');
		$this->load->library('form_validation');
		$this->load->helper('cookie');

		/* Load the profile.php config file if it exists
		$this->config->load('profiler', false, true);
		if ($this->config->config['enable_profiler']) {
			$this->output->enable_profiler(TRUE);
		} */
	}
	function index(){
		//$this->session->keep_all_flashdata();
		redirect(site_url('custom_report/create'), 'refresh');
	}
	function create(){
        //$this->load->library('input');
		if(!isset($this->as_ion_auth) || !$this->as_ion_auth->logged_in()){
			//$this->session->keep_all_flashdata();
			redirect(site_url('auth/login'));
		}
		$this->page_header_data['message'] = $this->session->flashdata('message');

		if(!isset($user_id) || $user_id === FALSE) $user_id = $this->session->userdata('user_id');
		//does the logged in user have permission to edit this user?
		if (!$this->as_ion_auth->logged_in()) {
			//$this->session->keep_all_flashdata();
			redirect(site_url('auth'));
        }
        else{
			$this->data['title'] = "Edit Account";
			//validate form input
/*	section
 * 	page
 *  block
 *  	- report name
 *  	- report description
 *  	- display type (table, chart, alert)
 *  	- chart type (stacked area, stacked column, column, line, bar, boxplot)
 *  	- max rows (e.g., test dates) to display
 *  	- order on page
 *  fields (if chart, check for same um)
 *  	- field id
 *  	- axis id?
 *  	- aggregate (give group by option if selected, ensure all fields are aggregate or group by)
 *  	- list order
 *  	- display? (bit)
 *  	- block header group id (tables)
 *  	- header text (tables, overrides field name)
 *  	- chart type id (for series (if none selected, inherit from block): stacked area, stacked column, column, line, bar, boxplot)
 *  group by (aggregate)
 *  	- field id
 *  where (filters/conditions)
 *  	- where group id (where_groups: parent id, block id, operator(null for first one))
 *  	- field id
 *  	- condition
 *  sort
 *  	- field id
 *  	- sort order
 *  	- list order (first, then, then...)
 *  block header groups (tables)
 *  	- parent id
 *  	- text
 *  block axes (chart)
 *  	- block id
 *  	- x or y
 *  	- field_id
 *  	- text
 * 		- min
 * 		- max
 * 		- opposite
 * 		- data type (datetime, linear)
 * 		- list order
 * chart categories (chart)
 * 		- name
 * 		- list order
 */

			//REPORT (BLOCK)
			$this->form_validation->set_rules('block_id', 'Block ID'); //null if adding
			$this->form_validation->set_rules('report_name', 'Report Name', 'trim|required|max_length[25]');
			$this->form_validation->set_rules('report_description', 'Report Description', 'trim|max_length[75]');
			$this->form_validation->set_rules('section_id', 'Section ID');
			$this->form_validation->set_rules('page_id', 'Page ID');
			$this->form_validation->set_rules('insert_after', 'Insert After');
			$this->form_validation->set_rules('report_display_id', 'Report Display Type');
			$this->form_validation->set_rules('max_rows', 'Max # of Rows', 'trim|max_length[3]');
			$this->form_validation->set_rules('chart_type_id', 'Report Chart Type');
			//REPORT FIELDS
			$this->form_validation->set_rules('column[]', 'Field');
			$this->form_validation->set_rules('aggregate[]', 'Field Calculation');
			$this->form_validation->set_rules('table_header_group_id[]', 'Table Header Group');
			$this->form_validation->set_rules('table_header_text[]', 'Table Header Text', 'trim|max_length[30]'); //for the field itself, not the header group
			$this->form_validation->set_rules('series_chart_type_id[]', 'Series Type');
			//GROUP BY
			$this->form_validation->set_rules('grouping_field_id[]', 'Grouping Field');
			$this->form_validation->set_rules('grouping_order[]', 'Grouping Order', 'trim|max_length[1]');
			//WHERE GROUP
			$this->form_validation->set_rules('where_group_parent_id[]', 'Where Group Parent');
			$this->form_validation->set_rules('where_group_operator[]', 'Where Group Operator', 'trim|max_length[1]');
			//WHERE
			$this->form_validation->set_rules('where_field_id[]', 'Where Field');
			$this->form_validation->set_rules('where_group_id[]', 'Where Group');
			$this->form_validation->set_rules('where_condition[]', 'Where Condition', 'trim|max_length[255]');
			//SORT
			$this->form_validation->set_rules('sort_field_id[]', 'Sort Field');
			$this->form_validation->set_rules('sort_order[]', 'Sort Order', 'trim|max_length[4]');
			$this->form_validation->set_rules('sort_list_order[]', 'Sort List Order', 'trim|max_length[1]');
			//REPORT HEADER GROUP (table)
			$this->form_validation->set_rules('header_group_parent_id[]', 'Header Group Parent');
			$this->form_validation->set_rules('header_group_text[]', 'Header Group Text', 'trim|max_length[50]');
			//BLOCK AXES (chart)
			$this->form_validation->set_rules('axes_x_or_y[]', 'Axes X or Y');
			$this->form_validation->set_rules('axes_field_id[]', 'Field');
			$this->form_validation->set_rules('axes_text[]', 'Axes Label', 'trim|max_length[50]');
			$this->form_validation->set_rules('axes_min[]', 'Axes Minimum Value', 'trim|max_length[6]');
			$this->form_validation->set_rules('axes_max[]', 'Axes Maximum Value', 'trim|max_length[6]');
			$this->form_validation->set_rules('axes_opposite[]', 'Opposite Side');
			$this->form_validation->set_rules('axes_data_type[]', 'Data Type'); //(datetime, linear)
			$this->form_validation->set_rules('axes_order[]', 'Axes Order', 'trim|max_length[1]');
			//CHART CATEGORIES (chart)
			$this->form_validation->set_rules('category_name[]', 'Category Name', 'trim|max_length[1]');
			$this->form_validation->set_rules('category_order[]', 'Category Order', 'trim|max_length[1]');

            $is_validated = $this->form_validation->run();

			if ($is_validated === true) {
				$custom_report = new CustomReport($this->custom_report_model);

                $input = $this->input->userInputArray();
                $input['user_id'] = $this->session->userdata('active_group_id') == 1 ? NULL : $this->session->userdata('user_id');

                $custom_report->add_report($input);
				//if($header_groups) 
				die();
			}
			else { //display the custom report form
                $this->load->model('web_content/section_model');

				$this->page_header_data['message'] = compose_error(validation_errors());
				$obj_user = $this->ion_auth_model->user($this->session->userdata('user_id'))->row();
				$this->data['form_attr'] = array('id'=>'rep-build');
				$this->data['page_heading'] = 'Custom Reports';
				$this->data['block_id'] = array('name' => 'block_id', 'id' => 'block_id', 'type' => 'hidden');
				$this->data['report_name'] = array('name' => 'report_name', 'id' => 'report_name', 'value' => set_value('report_name'));
				$this->data['report_description'] = array(
					'name' => 'report_description',
					'id' => 'report_description',
					'type' => 'text',
					'value' => set_value('report_description'),
					'size' => '50',
					'maxlength' => '75',
					//'value' => $this->form_validation->set_value('email', $obj_user->email),
					//'class' => 'require'
				);
//				$this->data['report_super_section_options'] = $this->as_ion_auth->set_form_array($this->web_content_model->get_super_sections_by_user($user_id), 'id', 'name');//$this->web_content_model->get_section_select_data();
				$this->data['report_super_section'] = 'id="super_section_id"';
				$this->data['report_super_section_selected'] = NULL;
				$this->data['report_section_options'] = $this->as_ion_auth->set_form_array($this->section_model->getSectionsByUser(79, $this->session->userdata('user_id')), 'id', 'scope'); //73 is DHI, 79 is reports
				$this->data['report_section'] = 'id="section_id"';
				$this->data['report_section_selected'] = NULL;
				$this->data['report_page_options'] = NULL;
				$this->data['report_page'] = 'id="page_id"';
				$this->data['report_page_selected'] = NULL;
				$this->data['insert_after'] = 'name="insert_after" id="insert_after"';
				$this->data['insert_after_selected'] = NULL;
				$this->data['insert_after_options'] = NULL;
				$this->data['report_display_options'] = [1=>'Table', 2=>'Trend Chart', 5=>'Comparison Chart'];//, 3 =>'Dashboard Report'
				$this->data['report_display'] = 'id="section_id" class="require"';
				$this->data['report_display_selected'] = NULL;
				$this->data['max_rows'] = array('name' => 'max_rows', 'id' => 'max_rows');
				$this->data['chart_type_options'] = $this->as_ion_auth->set_form_array($this->custom_report_model->getChartDisplayTypes()->result_array(), 'id', 'name');
				$this->data['chart_type'] = 'id="chart_type_id"';
				$this->data['chart_type_selected'] = NULL;
				$this->data['series_chart_type'] = 'id="series_chart_type-0"';
				$this->data['cow_or_summary'] = NULL;
				$this->data['cow_or_summary_selected'] = NULL;
				$this->data['choose_table_options'] = NULL;
				$this->data['choose_table'] = 'id="choose_table_id"';
				$this->data['choose_field_options'] = NULL;
				$this->data['choose_field'] = NULL;
				$this->data['pivot_db_field_options'] = array('' => 'Select one');
				$this->data['pivot_db_field'] = 'id="pivot_db_field"';
				$this->data['pivot_db_field_selected'] = NULL;
				//$data[''] = NULL;

				
				if(is_array($this->page_header_data)){
					$this->page_header_data = array_merge($this->page_header_data,
						array(
							'title'=>'Custom Report - ' . $this->config->item('product_name'),
							'description'=>'Custom report for ' . $this->config->item('product_name'),
							'arr_head_line' => array(
								'<script type="text/javascript">',
								'	var page = "";',
								'	var base_url = "' . site_url() . 'custom_report";',
								'	var herd_code = "' . $this->session->userdata('herd_code') . '";',
								'</script>'
							),						
							'arr_headjs_line' => array(
								'{customhelper: "' . $this->config->item('base_url_assets') . 'js/custom_report_helper.js"}',
								'{formhelper: "' . $this->config->item('base_url_assets') . 'js/form_helper.js"}'
							)
						)
					);
				}
				$this->carabiner->css('custom_reports.css', 'screen');
				$this->footer_data = array(
//					'arr_deferred_js'=>array(
//						$this->config->item('base_url_assets') . 'js/custom_report_helper.js',
//						$this->config->item('base_url_assets') . 'js/form_helper.js',
//					)
				);
				$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, true);
				$this->data['page_heading'] = 'Create Custom Report';
				$this->data['page_footer'] = $this->load->view('page_footer', $this->footer_data, true);
				$this->load->view('custom_report/custom_report_form', $this->data);
			}
       	} 
	}
	
	/***********  AJAX FUNCTIONS  **********************/
	function select_page_data($section_id){
		header('Content-type: application/json');
		$data = $this->custom_report_model->getPagesSelectDataByUser($this->session->userdata('user_id'), $section_id);
		$this->load->view('echo.php', ['text' => json_encode($data)]);
	}
	function select_table_data($cow_or_summary){
		header('Content-type: application/json');
		switch ($cow_or_summary){
			case 'summary':
				$cat_id = 2;
				break;
			case 'cow':
				$cat_id = 1;
				break;
			case 'admin':
				$cat_id = 34;
				break;
			default:
				$cat_id = null;
				break;
		}
		
		$data = $this->custom_report_model->get_tables_select_data($cat_id);
		$this->load->view('echo.php', ['text' => json_encode($data)]);
	}
	function select_field_data($table_id){
		header('Content-type: application/json');
		$data = $this->custom_report_model->get_fields_select_data($table_id);
		$this->load->view('echo.php', ['text' => json_encode($data)]);
	}
	function insert_after_data($page_id){
		header('Content-type: application/json');
		$data = $this->custom_report_model->get_insert_after_data($page_id);
		$this->load->view('echo.php', ['text' => json_encode($data)]);
	}
}
