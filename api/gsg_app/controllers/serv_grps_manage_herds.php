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

class Serv_grps_manage_herds extends MY_Api_Controller {
    function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');

        if($this->permissions->hasPermission('View Assign w permission') !== true) {
            $this->sendResponse(403, new ResponseMessage('You do not have permission to view non-owned herds.', 'error'));
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
     * @description manage_service_grp is the page service groups use to manage herd access
     */
    function index(){
        $herds_by_status = $this->as_ion_auth->getHerdPermissionsByStatus($this->session->userdata('user_id'));
        $this->sendResponse(200, null, $herds_by_status);


        //$this->sendResponse(400, new ResponseMessage($this->as_ion_auth->errors(), 'error'));
    }

    function remove_access(){
        $herds_by_status = $this->as_ion_auth->getHerdPermissionsByStatus($this->session->userdata('user_id'));

        $this->form_validation->set_rules('modify', 'Herd Selection');

        if($this->form_validation->run() == false){
            $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
        }

        $arr_modify_id = $this->input->userInput('modify');
        if(isset($arr_modify_id) && is_array($arr_modify_id)){
            if($this->ion_auth_model->batch_herd_revoke($arr_modify_id)) {
                $this->_record_access(41);
                $this->sendResponse(200, new ResponseMessage('Consultant access adjusted successfully.', 'error'));
            }
            else{
                $this->sendResponse(500, new ResponseMessage('Consultant access adjustment failed.  Please try again.', 'error'));
            }
        }

        $this->sendResponse(400, new ResponseMessage($this->as_ion_auth->errors(), 'error'));
    }

    function restore_access(){
        $this->form_validation->set_rules('modify', 'Herd Selection');

        if($this->form_validation->run() == false){
            $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
        }

        $action = $this->input->userInput('submit');
        $arr_modify_id = $this->input->userInput('modify');
        if(isset($arr_modify_id) && is_array($arr_modify_id)){
            //if consultant had revoked access, they can restore it (call grant_access)
            foreach($arr_modify_id as $k=>$id){
                if($this->ion_auth_model->get_consult_status_text($id) != 'consult revoked'){
                    unset($arr_modify_id[$k]);
                }
            }
            if(!empty($arr_modify_id) && $this->ion_auth_model->batch_grant_consult($arr_modify_id)) {
                $this->_record_access(34);
                $this->sendResponse(200, new ResponseMessage('Consultant access adjusted successfully.', 'message'));
            }
            else{
                $this->sendResponse(500, new ResponseMessage('Consultant access adjustment failed.  Please try again.', 'error'));
            }
        }

        $this->sendResponse(400, new ResponseMessage($this->as_ion_auth->errors(), 'error'));
    }

    function resend_email(){
        $this->form_validation->set_rules('modify', 'Herd Selection');

        if($this->form_validation->run() == false){
            $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
        }

        $action = $this->input->userInput('submit');
        $arr_modify_id = $this->input->userInput('modify');
        if(isset($arr_modify_id) && is_array($arr_modify_id)){
                    foreach($arr_modify_id as $k=>$id){
                        $arr_relationship_data = $this->ion_auth_model->get_consult_relationship_by_id($id);
                        if ($this->as_ion_auth->send_consultant_request($arr_relationship_data, $id, $this->config->item('cust_serv_email'))) {
                            $this->_record_access(35);
                            $this->sendResponse(200, new ResponseMessage($this->as_ion_auth->messages(), 'message'));
                        }
                        else {
                            $this->sendResponse(400, new ResponseMessage($this->as_ion_auth->errors(), 'error'));
                        }
                    }
        }

        $this->sendResponse(400, new ResponseMessage($this->as_ion_auth->errors(), 'error'));
    }

    //Consultants only, request permission to view herd
    function request_access() { //was service_grp_request
        //validate form input
        $this->form_validation->set_rules('herd_code', 'Herd Code', 'trim|required|exact_length[8]');
        $this->form_validation->set_rules('herd_release_code', 'Release Code', 'trim|required|exact_length[10]');
        $this->form_validation->set_rules('section_id', 'Sections', '');
        $this->form_validation->set_rules('exp_date', 'Expiration Date', 'trim');
        $this->form_validation->set_rules('write_data', 'Enter Event Data', '');
//		$this->form_validation->set_rules('request_status_id', '', '');
        $this->form_validation->set_rules('disclaimer', 'Confirmation of Understanding', 'required');

        if ($this->form_validation->run() === false) {
            $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
        }

        $herd_code = $this->input->userInput('herd_code');
        $herd_release_code = $this->input->userInput('herd_release_code');
        $error = $this->herd_model->herd_authorization_error($herd_code, $herd_release_code);

        if($this->ion_auth_model->get_consult_relationship_id($this->session->userdata('user_id'), $herd_code) !== FALSE){
            $error = 'Relationship already exists';
        }
        if($error){
            $this->sendResponse(400, new ResponseMessage($error, 'error'));
        }

        //passed initial checks, now prep submission data
        $arr_relationship_data = [
            'herd_code' => $herd_code,
            'sg_user_id' => $this->session->userdata('user_id'),
            'service_grp_request' => 1, //bit - did a service group request
            'write_data' => (int)$this->input->userInput('write_data'),
            'request_status_id' => 7, //7 is the id for open request
            'active_date' => date('Y-m-d'),
            'active_user_id' => $this->session->userdata('user_id'),
        ];
        $tmp = human_to_mysql($this->input->userInput('exp_date'));
        if(isset($tmp) && !empty($tmp)) $arr_relationship_data['exp_date'] = $tmp;

        //convert submitted section id values to int
        /*			$arr_post_section_id = $this->input->userInput('section_id');
                array_walk($arr_post_section_id, function (&$value) { $value = (int)$value; });
        */			$arr_post_section_id = array();

        if ($this->as_ion_auth->service_grp_request($arr_relationship_data, $arr_post_section_id, $this->config->item('cust_serv_email'))) {
            $this->_record_access(35);
            $this->sendResponse(200, new ResponseMessage($this->as_ion_auth->messages(), 'message')); //  to manage access page
        }
        else { //if the request was un-successful
            $this->sendResponse(500, new ResponseMessage($this->as_ion_auth->errors(), 'error'));
        }
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
