<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH . 'core/MY_Api_Controller.php');
require_once APPPATH . 'libraries/CustomReport.php';

use \myagsource\CustomReport;

class Custom_content extends MY_Api_Controller {
	protected $page_header_data;

	function __construct()
	{
        parent::__construct();

        $this->load->model('custom_report_model');

		/* Load the profile.php config file if it exists
		$this->config->load('profiler', false, true);
		if ($this->config->config['enable_profiler']) {
			$this->output->enable_profiler(TRUE);
		} */
	}

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
	function create(){
/*
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
*/
        try{
            $input = $this->input->userInputArray();
            $is_validated = $this->form_validation->run_input();

            $input['user_id'] = $this->session->userdata('active_group_id') == 1 ? NULL : $this->session->userdata('user_id');
            $form_factory = $this->_formFactory(['herd_code'=>$this->session->userdata('herd_code'), 'user_id'=>$input['user_id']], $input);
    $form_id = 35;
            $form = $form_factory->getForm($form_id);

            if ($is_validated === true) {
                $custom_report = new CustomReport($this->custom_report_model);


                $custom_report->add_report($input);
                //if($header_groups)
                die();
            }
            $entity_keys = $form->write($input);

            //if subcontent = listing,
            //$form->writeSubContent()

            $resp_msg = new ResponseMessage('Form submission successful', 'message');
            //$this->_record_access(2); //2 is the page code for herd change

//            if($parent_control_id){
//                //use the inserted value
//                $lookup_keys = $form_factory->getLookupKeys($parent_control_id);
//                $value = isset($entity_keys[$lookup_keys['value_column']]) ? $entity_keys[$lookup_keys['value_column']] : $input[$lookup_keys['value_column']];
//                $this->sendResponse(200, $resp_msg, ['option' => [$value => $input[$lookup_keys['desc_column']]]]);
//            }

            $this->sendResponse(200, $resp_msg, ['identity_keys' => $entity_keys]);
        }
        catch(\Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
        $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
    }

	function select_page($section_id){
        try{
            $data = $this->custom_report_model->getPagesSelectDataByUser($this->session->userdata('user_id'), $section_id);
            $this->sendResponse(200, null, ['options' => json_encode($data)]);
        }
        catch(\Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
        $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
	}

	function select_table($cow_or_summary){
        try{
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
            $this->sendResponse(200, null, ['options' => json_encode($data)]);
        }
        catch(\Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
        $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
	}

	function select_field_data($table_id){
        try{
		    $data = $this->custom_report_model->get_fields_select_data($table_id);
            $this->sendResponse(200, null, ['options' => json_encode($data)]);
        }
        catch(\Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
        $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
	}

	function select_list_order($page_id){
        try{
		    $data = $this->custom_report_model->get_insert_after_data($page_id);
            $this->sendResponse(200, null, ['options' => json_encode($data)]);
        }
        catch(\Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
        $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
	}
}
