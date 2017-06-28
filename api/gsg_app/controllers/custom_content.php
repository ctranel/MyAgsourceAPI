<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH . 'core/MY_Api_Controller.php');
require_once APPPATH . 'libraries/CustomContent/Report/CreateCustomReport.php';

use \myagsource\CustomContent\Report\CreateCustomReport;

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

	function create(){
        try{
            $input = $this->input->userInputArray();
            $user_id = $this->session->userdata('active_group_id') == 1 ? NULL : $this->session->userdata('user_id');

            $custom_report = new CreateCustomReport($this->custom_report_model, $input['report_id'], $user_id);

            $custom_report->add_report($input);
            die();

            $resp_msg = new ResponseMessage('Form submission successful', 'message');
            //$this->_record_access(2); //2 is the page code for herd change

            $this->sendResponse(200, $resp_msg, ['identity_keys' => $entity_keys]);
        }
        catch(\Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
    }

	function select_page($section_id){
        try{
            $data = $this->custom_report_model->getPagesSelectDataByUser($this->session->userdata('user_id'), $section_id);
            $return = [];
            foreach($data as $c){
                $return[] = [$c['id'] => $c['name']];
            }

            $this->sendResponse(200, null, ['options' => $return]);
        }
        catch(\Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
	}

    function select_list_order($page_id){
        try{
            $data = $this->custom_report_model->get_insert_after_data($page_id);
            $return = [];
            foreach($data as $c){
                $return[] = [$c['list_order'] => $c['name']];
            }

            $this->sendResponse(200, null, ['options' => $return]);
        }
        catch(\Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
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
            $return = [];
            foreach($data as $c){
                $return[] = [$c['id'] => $c['name']];
            }

            $this->sendResponse(200, null, ['options' => $return]);
        }
        catch(\Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
	}

	function select_field_data($table_id){
        try{
		    $data = $this->custom_report_model->get_fields_select_data($table_id);
            $return = [];
            foreach($data as $c){
                $return[] = [$c['id'] => [
                    "name" => $c['name'],
                    "is_timespan_field" => $c['is_timespan_field'],
                    "data_type" => $c['data_type'],
                ]];
                //$return[] = [$c['id'] => $c['name']];
            }

            $this->sendResponse(200, null, ['options' => $return]);
        }
        catch(\Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
        $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
	}
}
