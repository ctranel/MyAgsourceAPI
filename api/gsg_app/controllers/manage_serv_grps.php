<?php
require_once(APPPATH . 'libraries/dhi/Herd.php');
require_once(APPPATH . 'libraries/dhi/HerdAccess.php');
require_once(APPPATH . 'core/MY_Api_Controller.php');
require_once(APPPATH . 'libraries/as_ion_auth.php');
require_once(APPPATH . 'libraries/Products/Products/Products.php');
require_once(APPPATH . 'libraries/Permissions/Permissions/ProgramPermissions.php');
require_once(APPPATH . 'libraries/Notifications/Notifications.php');

use \myagsource\AccessLog;
use \myagsource\dhi\Herd;
use \myagsource\dhi\HerdAccess;
use \myagsource\As_ion_auth;
use \myagsource\Products\Products\Products;
use \myagsource\Permissions\Permissions\ProgramPermissions;
use \myagsource\notices\Notifications;
use \myagsource\Api\Response\ResponseMessage;


defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/ionauth.php';
require_once APPPATH . 'libraries/AccessLog.php';

class Manage_serv_grps extends MY_Api_Controller {
    function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');

        if ($this->permissions->hasPermission('Update SG Access') === false) {
            $this->sendResponse(403,
                new ResponseMessage('You do not have permission to manage consultant access to this herd.', 'error'));
        }

        /* Load the profile.php config file if it exists
        if (ENVIRONMENT == 'development' || ENVIRONMENT == 'localhost') {
            $this->config->load('profiler', false, true);
            if ($this->config->config['enable_profiler']) {
                $this->output->enable_profiler(TRUE);
            }
        }*/
    }

    /*
     * @description index is the page producers use to manage service group access
     */
    function index()
    {
        $consultants_by_status = $this->as_ion_auth->getConsultantsByHerd($this->session->userdata('herd_code'));
        $this->sendResponse(200, null, $consultants_by_status);


        //$this->sendResponse(400, new ResponseMessage($this->as_ion_auth->errors(), 'error'));
    }

    function remove_access(){
        $input = $this->input->userInputArray();

        $this->form_validation->set_rules('modify', 'Herd Selection');
        if($this->form_validation->run() === false){
            $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
        }

        $action = $this->input->userInput('submit');
        $arr_modify_id = $this->input->userInput('modify');
        if(isset($arr_modify_id) && is_array($arr_modify_id)){

            if($this->ion_auth_model->batch_herd_revoke($arr_modify_id)) {
                $this->_record_access(41);
                $this->sendResponse(200, new ResponseMessage('Consultant access adjusted successfully.', 'message'));
            }
            else{
                $this->sendResponse(500, new ResponseMessage('Consultant access adjustment failed.  Please try again.', 'error'));
            }
        }
    }

    function grant_access(){
        $input = $this->input->userInputArray();

        $this->form_validation->set_rules('modify', 'Herd Selection');
        if($this->form_validation->run() === false){
            $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
        }

        $action = $this->input->userInput('submit');
        $arr_modify_id = $this->input->userInput('modify');
        if(isset($arr_modify_id) && is_array($arr_modify_id)){
            if($this->ion_auth_model->batch_grant_consult($arr_modify_id)) {
                $this->_record_access(34);
                $this->sendResponse(200, new ResponseMessage('Consultant access adjusted successfully.', 'error'));
            }
            else{
                $this->sendResponse(500, new ResponseMessage('Consultant access adjustment failed.  Please try again.', 'error'));
            }
        }
    }

    function deny_access(){
        $input = $this->input->userInputArray();

        $this->form_validation->set_rules('modify', 'Herd Selection');
        if($this->form_validation->run() === false){
            $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
        }

        $action = $this->input->userInput('submit');
        $arr_modify_id = $this->input->userInput('modify');
        if(isset($arr_modify_id) && is_array($arr_modify_id)){
            if($this->ion_auth_model->batch_deny_consult($arr_modify_id)) {
                $this->_record_access(42);
                $this->sendResponse(200, new ResponseMessage('Consultant access adjusted successfully.'));
            }
            else{
                $this->sendResponse(500, new ResponseMessage('Consultant access adjustment failed.  Please try again.', 'error'));
            }
        }
    }

    function remove_expiration(){
        $input = $this->input->userInputArray();

        $this->form_validation->set_rules('modify', 'Herd Selection');
        if($this->form_validation->run() === false){
            $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
        }

        $action = $this->input->userInput('submit');
        $arr_modify_id = $this->input->userInput('modify');
        if(isset($arr_modify_id) && is_array($arr_modify_id)){
            if($this->ion_auth_model->batch_remove_consult_expire($arr_modify_id)) {
                $this->_record_access(43);
                $this->sendResponse(200, new ResponseMessage('Consultant access adjusted successfully.', 'error'));
            }
            else{
                $this->sendResponse(500, new ResponseMessage('Consultant access adjustment failed.  Please try again.', 'error'));
            }
        }
    }

    //Producers only, give consultant permission to view herd
    function access($cuid = NULL) {
        if((!$this->as_ion_auth->logged_in())){
            $this->sendResponse(401);
        }
        //@todo: replace reference to group with permission-based condition
        if($this->session->userdata('active_group_id') != 2) {
            $this->sendResponse(403, new ResponseMessage('Only producers can manage consultant access to their herd data.', 'error'));
        }

        //validate form input
        $this->form_validation->set_rules('section_id', 'Sections', '');
        $this->form_validation->set_rules('exp_date', 'Expiration Date', 'trim');
        $this->form_validation->set_rules('request_status_id', 'Request Status', '');
        $this->form_validation->set_rules('write_data', 'Enter Event Data', '');
        //$this->form_validation->set_rules('request_status_id', '', '');
        $this->form_validation->set_rules('disclaimer', 'Confirmation of Understanding', 'required');

        if ($this->form_validation->run() === false) {
            $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
        }

        $arr_relationship_data = array(
            'sg_user_id' => (int)$this->input->userInput('sg_user_id'),
            'herd_code' => $this->session->userdata('herd_code'),
            'write_data' => (int)$this->input->userInput('write_data'),
            'active_date' => date('Y-m-d'),
            'active_user_id' => $this->session->userdata('user_id'),
        );
        $post_request_status_id = $this->input->userInput('request_status_id');
        if(isset($post_request_status_id) && !empty($post_request_status_id)){
            $arr_relationship_data['request_status_id'] = (int)$post_request_status_id;
        }
        $tmp = human_to_mysql($this->input->userInput('exp_date'));
        if(isset($tmp) && !empty($tmp)) {
            $arr_relationship_data['exp_date'] = $tmp;
        }
        elseif(isset($tmp) && empty($tmp)) {
            $arr_relationship_data['exp_date'] = null;
        }

        //convert submitted section id values to int
        $arr_post_section_id = $this->input->userInput('section_id');
        if(isset($arr_post_section_id) && is_array($arr_post_section_id)){
            array_walk($arr_post_section_id, function (&$value) { $value = (int)$value; });
        }

        if ($this->as_ion_auth->allow_service_grp($arr_relationship_data, $arr_post_section_id)) { //if permission is granted successfully
            $this->_record_access(34);
            $this->sendResponse(200, new ResponseMessage('Permission is granted successfully', 'message'));
        }

        $this->sendResponse(400, new ResponseMessage($this->as_ion_auth->errors(), 'error'));
    }

    protected function _record_access($event_id){
        if($this->session->userdata('user_id') === FALSE){
            return FALSE;
        }
        $herd_code = $this->session->userdata('herd_code');
        $recent_test = $this->session->userdata('recent_test_date');
        $recent_test = empty($recent_test) ? NULL : $recent_test;

        $this->load->model('access_log_model');
        $access_log = new AccessLog($this->access_log_model);

        $access_log->writeEntry(
            $this->as_ion_auth->is_admin(),
            $event_id,
            $herd_code,
            $recent_test,
            $this->session->userdata('user_id'),
            $this->session->userdata('active_group_id')
        );
    }
}
