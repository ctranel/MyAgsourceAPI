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

class Auth extends MY_Api_Controller {
	function __construct()
	{
		$this->ionauth = new Ionauth();
		parent::__construct();
        //$this->load->library('as_ion_auth');
        //$this->load->library('session');
        //$this->load->helper('error');
        $this->load->library('form_validation');
        $this->load->model('dhi/herd_model');

        //instantiate in case noone is logged in
        if(!$this->session->userdata('user_id')) {
            $this->as_ion_auth = new As_ion_auth(null);
            return;
        }

        //instantiate as_ion_auth with permissions
        if($this->session->userdata('active_group_id')) {
            $herd = new Herd($this->herd_model, $this->session->userdata('herd_code'));

            $this->load->model('permissions_model');
            $this->load->model('product_model');
            $group_permissions = ProgramPermissions::getGroupPermissionsList($this->permissions_model, $this->session->userdata('active_group_id'));
            $products = new Products($this->product_model, $herd, $group_permissions);
            $this->permissions = new ProgramPermissions($this->permissions_model, $group_permissions, $products->allHerdProductCodes());
        }
        $this->as_ion_auth = new As_ion_auth($this->permissions);

		/* Load the profile.php config file if it exists
		if (ENVIRONMENT == 'development' || ENVIRONMENT == 'localhost') {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			} 
		}*/
	}

    function c(){
        $this->sendResponse(200, new ResponseMessage('message', 'success'), ['user' => $this->session->userdata('user_id')]);
    }

	function product_info_request(){
		$arr_inquiry = $this->input->userInput('products');
        $arr_user = $this->ion_auth_model->user($this->session->userdata('user_id'))->result_array()[0];

		if(isset($arr_inquiry) && is_array($arr_inquiry)){
			if($this->as_ion_auth->recordProductInquiry($arr_user['first_name'] . ' ' . $arr_user['last_name'], $arr_user['email'],$this->session->userdata('herd_code'), $arr_inquiry, $this->input->userInput('comments'))){
                $this->sendResponse(200, new ResponseMessage('Thank you for your interest.  Your request for more information has been sent.', 'message'));
			}
			else{
                $this->sendResponse(500, new ResponseMessage('We encountered a problem sending your request.', 'error'));
			}
		}
		else {
            $this->sendResponse(400, new ResponseMessage('Please select one or more products and resubmit your request.', 'error'));
		}
	}

	/*
	 * @description manage_service_grp is the page producers use to manage service group access
	 */
	function manage_service_grp(){
        if((!$this->as_ion_auth->logged_in())){
            $this->sendResponse(401);
        }
		if($this->permissions->hasPermission('Update SG Access') === false) {
            $this->sendResponse(403, new ResponseMessage('You do not have permission to manage consultant access to this herd.', 'error'));
		}
		
		$this->form_validation->set_rules('modify', 'Herd Selection');
		if($this->form_validation->run() === false){
            $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
        }

        $action = $this->input->userInput('submit');
        $arr_modify_id = $this->input->userInput('modify');
        if(isset($arr_modify_id) && is_array($arr_modify_id)){
            //@todo: should have sep controller for consultant access, and sep path for each of these actions?
            switch ($action) {
                case 'Remove Access':
                    if($this->ion_auth_model->batch_herd_revoke($arr_modify_id)) {
                        $this->_record_access(41);
                        $this->sendResponse(200, new ResponseMessage('Consultant access adjusted successfully.', 'message'));
                    }
                    else{
                        $this->sendResponse(500, new ResponseMessage('Consultant access adjustment failed.  Please try again.', 'error'));
                    }
                break;
                case 'Grant Access':
                    if($this->ion_auth_model->batch_grant_consult($arr_modify_id)) {
                        $this->_record_access(34);
                        $this->sendResponse(200, new ResponseMessage('Consultant access adjusted successfully.', 'error'));
                    }
                    else{
                        $this->sendResponse(500, new ResponseMessage('Consultant access adjustment failed.  Please try again.', 'error'));
                    }
                break;
                case 'Deny Access':
                    if($this->ion_auth_model->batch_deny_consult($arr_modify_id)) {
                        $this->_record_access(42);
                        $this->sendResponse(200, new ResponseMessage('Consultant access adjusted successfully.'));
                    }
                    else{
                        $this->sendResponse(500, new ResponseMessage('Consultant access adjustment failed.  Please try again.', 'error'));
                    }
                break;
                case 'Remove Expiration Date':
                    if($this->ion_auth_model->batch_remove_consult_expire($arr_modify_id)) {
                        $this->_record_access(43);
                        $this->sendResponse(200, new ResponseMessage('Consultant access adjusted successfully.', 'error'));
                    }
                    else{
                        $this->sendResponse(500, new ResponseMessage('Consultant access adjustment failed.  Please try again.', 'error'));
                    }
                break;
                default:
                    $this->sendResponse(500, new ResponseMessage('Consultant access adjustment failed.  Please try again.', 'error'));
                break;
            }
        }

        $this->sendResponse(400, new ResponseMessage($this->as_ion_auth->errors(), 'error'));
    }
	
	/*
	 * @description manage_service_grp is the page service groups use to manage herd access
	 */
	function service_grp_manage_herds(){
        if((!$this->as_ion_auth->logged_in())){
            $this->sendResponse(401);
        }
		if($this->permissions->hasPermission('View Assign w permission') !== true) {
            $this->sendResponse(403, new ResponseMessage('You do not have permission to view non-owned herds.', 'error'));
		}

		$this->form_validation->set_rules('modify', 'Herd Selection');

        if($this->form_validation->run() == false){
            $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
        }

        $action = $this->input->userInput('submit');
        $arr_modify_id = $this->input->userInput('modify');
        if(isset($arr_modify_id) && is_array($arr_modify_id)){
            //@todo: should have sep controller for consultant access, and sep path for each of these actions
            switch ($action) {
                case 'Remove Access':
                    if($this->ion_auth_model->batch_herd_revoke($arr_modify_id)) {
                        $this->_record_access(41);
                        $this->sendResponse(200, new ResponseMessage('Consultant access adjusted successfully.', 'error'));
                    }
                    else{
                        $this->sendResponse(500, new ResponseMessage('Consultant access adjustment failed.  Please try again.', 'error'));
                    }
                    break;
                case 'Restore Access':
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
                break;
                case 'Resend Request Email':
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
                break;
                default:
                    $this->sendResponse(500, new ResponseMessage('Consultant access adjustment failed.  Please try again.', 'error'));
                break;
            }
        }

        $this->sendResponse(400, new ResponseMessage($this->as_ion_auth->errors(), 'error'));
    }
	
	//Producers only, give consultant permission to view herd
	function service_grp_access($cuid = NULL) {
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

	//Consultants only, request permission to view herd
	function service_grp_request() {
        if((!$this->as_ion_auth->logged_in())){
            $this->sendResponse(401);
        }
		if(!$this->permissions->hasPermission('View Assign w permission')) {
            $this->sendResponse(403, new ResponseMessage('You do not have permission to request the data of a herd you do not own.', 'error'));
		}

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

	function list_accounts(){
		if(!$this->permissions->hasPermission("Edit All Users") && !$this->permissions->hasPermission("Edit Users In Region")){
            $this->sendResponse(403, new ResponseMessage('You do not have permission to edit user accounts.', 'error'));
		}
		//list the users
		$this->data['users'] = $this->as_ion_auth->get_editable_users();
		$this->data['arr_group_lookup'] = $this->ion_auth_model->get_group_lookup();
	}

	function login() {
		$this->data['trial_days'] = $this->config->item('trial_period');

		//validate form input
		$this->form_validation->set_rules('identity', 'Email Address', 'trim|required|valid_email');
		$this->form_validation->set_rules('password', 'Password', 'trim|required');

		/*if ($this->form_validation->run() === false) {
            $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
        } */
        //check to see if the user is logging in
        //check for "remember me"
        $remember = (bool) $this->input->userInput('remember');
        //Clear out herd code in case user was browsing demo herd before logging in.
        $this->session->unset_userdata('herd_code');
        $this->session->unset_userdata('arr_pstring');
        $this->session->unset_userdata('pstring');
        $this->session->unset_userdata('arr_tstring');
        $this->session->unset_userdata('tstring');
        //$this->session->sess_destroy();
        //$this->session->sess_create();
    
        if ($this->as_ion_auth->login($this->input->userInput('identity'), $this->input->userInput('password'), $remember)){ //if the login is successful
            $this->_record_access(1); //1 is the page code for login for the user management section
            //get permissions (also in constuctor, put in function/class somewhere)
            $this->load->model('permissions_model');
            $this->load->model('product_model');
            $herd = new Herd($this->herd_model, $this->session->userdata('herd_code'));
            $group_permissions = ProgramPermissions::getGroupPermissionsList($this->permissions_model, $this->session->userdata('active_group_id'));
            $products = new Products($this->product_model, $herd, $group_permissions);
            $this->permissions = new ProgramPermissions($this->permissions_model, $group_permissions, $products->allHerdProductCodes());

            //get herd list
/*            $tmp_arr = $this->herd_access->getAccessibleHerdOptions($this->session->userdata('user_id'), $this->permissions->permissionsList(), $this->session->userdata('arr_regions'));
            if(count($tmp_arr) === 0){
                $this->sendResponse(404, new ResponseMessage('No herds found.', 'error'));
            }
*/
            $this->load->model('notice_model');
            $this->notifications = new Notifications($this->notice_model);
            $this->notifications->populateNotices();
            $notices = $this->notifications->getNoticesTexts();
            $msgs = [];
            foreach($notices as $n){
                $msgs[] = new ResponseMessage($n, 'message');
            }
            //we want the success message to be first, so we create a temp var and merge with that as the first value
            $msgs[] = new ResponseMessage('Login Successful', 'message');
            //send response
            $this->sendResponse(200, $msgs);
        }

        $this->sendResponse(401, new ResponseMessage($this->as_ion_auth->errors(), 'error'));
	}

	//log the user out
	function logout(){
		//log the user out
		$this->as_ion_auth->logout();

        $this->sendResponse(200);
	}

	//change password
	function change_password(){
        $this->form_validation->set_rules('old', 'Old password', 'required');
        $this->form_validation->set_rules('new', 'New Password', 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[new_confirm]');
        $this->form_validation->set_rules('new_confirm', 'Confirm New Password', 'required');

        if (!$this->as_ion_auth->logged_in()){
            $this->sendResponse(401);
        }

        //$user = $this->as_ion_auth->user()->row();
/*
        if ($this->form_validation->run() === false) {
            $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
        } */
        $identity = $this->session->userdata($this->config->item('identity', 'ion_auth'));

        $change = $this->as_ion_auth->change_password($identity, $this->input->userInput('old'), $this->input->userInput('new'));

        if ($change) { //if the password was successfully changed
            $this->as_ion_auth->logout();
            $this->sendResponse(200, new ResponseMessage($this->as_ion_auth->messages(), 'message'));
        }

        $this->sendResponse(500, new ResponseMessage($this->as_ion_auth->errors(), 'error'));
	}

	//forgot password
	function forgot_password(){
        $this->form_validation->set_rules('email', 'Email Address', 'required');
        /*if ($this->form_validation->run() === false) {
            $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
        }*/
        //run the forgotten password method to email an activation code to the user
        $forgotten = $this->as_ion_auth->forgotten_password($this->input->userInput('email'));

        if ($forgotten) { //if there were no errors
            $this->sendResponse(200, new ResponseMessage($this->as_ion_auth->messages(), 'message'));
        }

        $this->sendResponse(400, new ResponseMessage($this->as_ion_auth->errors(), 'error'));
	}

	//reset password - final step for forgotten password
	public function reset_password($code = NULL) {
        if (!$code) {
            $this->sendResponse(400, new ResponseMessage('Invalid or expired reset code.  Please restart process.', 'error'));
        }

        $user = $this->as_ion_auth->forgotten_password_check($code);

        if($user === false){
            $this->sendResponse(404, new ResponseMessage('User not found.', 'error'));
        }

        $this->form_validation->set_rules('new', 'New Password',
            'required|min_length[' . $this->config->item('min_password_length',
                'ion_auth') . ']|max_length[' . $this->config->item('max_password_length',
                'ion_auth') . ']|matches[new_confirm]');
        $this->form_validation->set_rules('new_confirm', 'Confirm New Password', 'required');

        if ($this->form_validation->run() === false) {
            $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
        }
        // do we have a valid request?
        if ($this->_valid_csrf_nonce() === FALSE || $user->id != $this->input->userInput('user_id')) {
            //something fishy might be up
            $this->as_ion_auth->clear_forgotten_password_code($code);
            $this->sendResponse(400);
        }

        // finally change the password
        $identity = $user->{$this->config->item('identity', 'ion_auth')};
        if ($this->as_ion_auth->reset_password($identity, $this->input->userInput('new'))) { //if the password was successfully changed
            $this->as_ion_auth->logout();
            $this->sendResponse(200, new ResponseMessage($this->as_ion_auth->messages(), 'message'));
        }

        $this->sendResponse(500, new ResponseMessage($this->ion_auth->errors(), 'error', ['reset_code' => $code]));
    }

    //activate the user
    function activate($id, $code=false)
    {
        if($code === false && !$this->ion_auth->is_admin()){
            $this->sendResponse(400, new ResponseMessage('Invalid or expired reset code.  Please restart process.', 'error'));
        }
        if ($this->ion_auth->is_admin()){
            $activation = $this->as_ion_auth->activate($id);
        }
        else {
            $activation = $this->as_ion_auth->activate($id, $code);
        }

        if ($activation) {
            $this->sendResponse(200, new ResponseMessage($this->ion_auth->messages(), 'message'));
        }
        else {
            $this->sendResponse(400, new ResponseMessage($this->ion_auth->errors(), 'error'));
        }
    }

    //deactivate the user
    function deactivate($id = NULL)
    {
        $id = $this->config->item('use_mongodb', 'ion_auth') ? (string) $id : (int) $id;

        $this->load->library('form_validation');
        $this->form_validation->set_rules('confirm', 'confirmation', 'required');
        $this->form_validation->set_rules('id', 'user ID', 'required|alpha_numeric');

        if ($this->form_validation->run()) {
            // do we really want to deactivate?
            if ($this->input->userInput('confirm') == 'yes') {
                // do we have a valid request?
                if ($this->_valid_csrf_nonce() === FALSE || $id != $this->input->userInput('id')) {
                    $this->sendResponse(400, new ResponseMessage('User not specified.', 'error'));
                }

                // do we have the right userlevel?
                if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
                    if($this->ion_auth->deactivate($id)) {
                        $this->sendResponse(200);
                    }
                }
                $this->sendResponse(500, new ResponseMessage($this->as_ion_auth->errors(), 'error'));
            }
            $this->sendResponse(400, new ResponseMessage('Invalid submission.', 'error'));
        }
        $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
    }

    function create_user(){
		//validate form input
		$this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
		$this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
		$this->form_validation->set_rules('email', 'Email Address', 'trim|required|valid_email');
		$this->form_validation->set_rules('supervisor_acct_num', 'Field Technician Account Number', 'max_length[8]');
		$this->form_validation->set_rules('sg_acct_num', 'Service Group Account Number', 'max_length[8]');
		$this->form_validation->set_rules('assoc_acct_num[]', 'Association Account Number', 'max_length[8]');
		$this->form_validation->set_rules('best_time', 'Best Time to Call', 'max_length[10]|required');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]');
		$this->form_validation->set_rules('password_confirm', 'Password Confirmation', 'trim|required');
		$this->form_validation->set_rules('group_id[]', 'Name of User Group');
		$this->form_validation->set_rules('terms', 'Terms of Use Acknowledgement', 'required|exact_length[1]');
		$this->form_validation->set_rules('herd_code', 'Herd Code', 'exact_length[8]');
		$this->form_validation->set_rules('herd_release_code', 'Release Code', 'trim|exact_length[10]');
		$this->form_validation->set_rules('section_id[]', 'Section');

       if($this->form_validation->run() === false){
            $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
        }

        $arr_posted_group_id = $this->input->userInput('group_id');//$this->form_validation->set_value('group_id[]');
        if(!is_array($arr_posted_group_id)){
            $arr_posted_group_id = [$arr_posted_group_id];
        }
        if(!$this->as_ion_auth->group_assignable($arr_posted_group_id)){
            $this->sendResponse(403, new ResponseMessage('You do not have permissions to create a user with the user group you selected.', 'error'));
        }

        //start with nothing
        $assoc_acct_num = NULL;
        $supervisor_acct_num = NULL;
        $sg_acct_num = NULL;
        $herd_code = NULL;
        $herd_release_code = NULL;

        //Set variables that depend on group(s) selected
        if(isset($this->permissions)){
            if($this->permissions->hasPermission("Add All Users") || $this->permissions->hasPermission("Add Users In Region")){
                $arr_posted_group_id = $this->input->userInput('group_id');
                if(!$this->as_ion_auth->group_assignable($arr_posted_group_id)){
                    $this->sendResponse(403, new ResponseMessage('You do not have permissions to add a user with the user group you selected.', 'error'));
                }
                $assoc_acct_num = $this->input->userInput('assoc_acct_num');
                $supervisor_acct_num = $this->input->userInput('supervisor_acct_num');
                if(empty($assoc_acct_num)){
                    $assoc_acct_num = NULL;
                }
                if(empty($supervisor_acct_num)){
                    $supervisor_acct_num = NULL;
                }
            }
        }
        if(in_array(2, $arr_posted_group_id) || in_array(13, $arr_posted_group_id)){ //producers
            $herd_code = $this->input->userInput('herd_code') ? $this->input->userInput('herd_code') : NULL;
            $herd_release_code = $this->input->userInput('herd_release_code');
            $error = $this->herd_model->herd_authorization_error($herd_code, $herd_release_code);
            if($error){
                $this->sendResponse(403, new ResponseMessage($error, 'error'));
            }
        }
        if(in_array(9, $arr_posted_group_id)){ //service groups
            $sg_acct_num = $this->input->userInput('sg_acct_num');
            if(!$this->as_ion_auth->service_grp_exists($sg_acct_num)){
                $this->sendResponse(400, new ResponseMessage('The service group entered does not exist.', 'error'));
            }
        }

        $username = substr(strtolower($this->input->userInput('first_name')) . ' ' . strtolower($this->input->userInput('last_name')),0,15);
        $email = $this->input->userInput('email');
        $password = $this->input->userInput('password');
        $additional_data = array('first_name' => $this->input->userInput('first_name'),
            'herd_code' => $herd_code,
            'last_name' => $this->input->userInput('last_name'),
            'supervisor_acct_num' => $supervisor_acct_num,
            'sg_acct_num' => $sg_acct_num,
            'assoc_acct_num' => $assoc_acct_num,
//            'phone' => $this->input->userInput('phone1') . '-' . $this->input->userInput('phone2') . '-' . $this->input->userInput('phone3'),
            'best_time' => $this->input->userInput('best_time'),
            'group_id' => $arr_posted_group_id,
            'section_id' => $this->input->userInput('section_id')
        );
 //       if($additional_data['phone'] == '--') $additional_data['phone'] = '';

        try{
            $is_registered = $this->as_ion_auth->register($username, $password, $email, $additional_data, $arr_posted_group_id, 'AMYA-500');
            if ($is_registered === true) { //check to see if we are creating the user
                //$this->as_ion_auth->activate();
                $this->sendResponse(200, new ResponseMessage('Your account has been created.  You will be receiving an email shortly that will confirm your registration and allow you to activate your account.', 'message'));
            }
            else{
                $this->sendResponse(400, new ResponseMessage($this->as_ion_auth->errors(), 'error'));
            }
        }
        catch(Exception $e){
            //will eventually catch registration errors here, but for now they are written to as_ion_auth errors()
            $this->sendResponse(400, new ResponseMessage($e->getMessage(), 'error'));
        }

        $this->sendResponse(500, new ResponseMessage('Registration was not successful.', 'error'));
	}

	function edit_user($user_id = FALSE) {
		if($user_id === FALSE){
			$user_id = $this->session->userdata('user_id');
		}
		//does the logged in user have permission to edit this user?
		if (!$this->as_ion_auth->logged_in()) {
            $this->sendResponse(401, new ResponseMessage('Please log in and try again.', 'error'));
        }
        if(!$this->as_ion_auth->is_editable_user($user_id, $this->session->userdata('user_id'))){
            $this->sendResponse(403, new ResponseMessage('You do not have permission to edit the requested account.', 'error'));
        }

		//validate form input
		$this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
		$this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
		$this->form_validation->set_rules('email', 'Email Address', 'trim|required|valid_email');
		$this->form_validation->set_rules('supervisor_acct_num', 'Field Technician Number', 'exact_length[8]');
		$this->form_validation->set_rules('assoc_acct_num[]', 'Association/Region Account Number', 'exact_length[8]');
		$this->form_validation->set_rules('best_time', 'Best Time to Call', 'max_length[10]|required');
		$this->form_validation->set_rules('password', 'Password', 'trim|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]');
		$this->form_validation->set_rules('password_confirm', 'Password Confirmation', 'trim');
		$this->form_validation->set_rules('group_id[]', 'Name of Account Group');
		//$this->form_validation->set_rules('herd_code', 'Herd Code', 'exact_length[8]');
		$this->form_validation->set_rules('section_id[]', 'Section');

		$email_in = $this->input->userInput('email');
		if(empty($email_in)){
            $this->sendResponse(400, new ResponseMessage('Form data not found.', 'error'));
        }

        if($this->form_validation->run() === false){
            $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
        }

        //populate data fields for specific group choices
        //start with the minimum
        $user_id = $this->input->userInput('user_id');
        $arr_posted_group_id = FALSE;
        $assoc_acct_num = NULL;
        $supervisor_acct_num = NULL;

        //Set variables that depend on group(s) selected
        if($this->permissions->hasPermission("Edit All Users") || $this->permissions->hasPermission("Edit Users In Region")){
            $arr_posted_group_id = $this->input->userInput('group_id');
            if(!$this->as_ion_auth->group_assignable($arr_posted_group_id)){
                $this->sendResponse(403, new ResponseMessage('You do not have permissions to edit a user with the user group you selected.', 'error'));
            }
            $assoc_acct_num = $this->input->userInput('assoc_acct_num');
            $supervisor_acct_num = $this->input->userInput('supervisor_acct_num');
        }

        $obj_user = $this->ion_auth_model->user($user_id)->row();
        /*if($this->input->userInput('herd_code') && $this->input->userInput('herd_code') != $obj_user->herd_code){
            $herd_code = $this->input->userInput('herd_code') ? $this->input->userInput('herd_code') : NULL;
            $herd_release_code = $this->input->userInput('herd_release_code');
            $error = $this->herd_model->herd_authorization_error($herd_code, $herd_release_code);
            if($error){
                $this->as_ion_auth->set_error($error);
                $is_validated = false;
            }
        }*/

        //populate
        $username = substr(strtolower($this->input->userInput('first_name')) . ' ' . strtolower($this->input->userInput('last_name')),0,15);
        $email = $this->input->userInput('email');
        $data = array('username' => $username,
            'email' => $email,
            'first_name' => $this->input->userInput('first_name'),
            'last_name' => $this->input->userInput('last_name'),
//            'phone' => $this->input->userInput('phone1') . '-' . $this->input->userInput('phone2') . '-' . $this->input->userInput('phone3'),
            'best_time' => $this->input->userInput('best_time'),
            'group_id' => $arr_posted_group_id,
            'supervisor_acct_num' => $supervisor_acct_num,
            'assoc_acct_num' => $assoc_acct_num,
            'herd_code' => $this->input->userInput('herd_code') ? $this->input->userInput('herd_code') : NULL
        );
        if($data['phone'] == '--') $data['phone'] = '';
        if(isset($_POST['section_id'])) $data['section_id'] = $this->input->userInput('section_id');
        $password = $this->input->userInput('password');
        if(!empty($password)) $data['password'] = $password;

		$arr_curr_group_ids = array_keys($this->session->userdata('arr_groups'));
		if ($this->ion_auth_model->update($user_id, $data, $this->session->userdata('active_group_id'), $arr_curr_group_ids)) { //check to see if we are creating the user
            $this->sendResponse(200, new ResponseMessage($this->as_ion_auth->messages(), 'message'));
		}

        $this->sendResponse(400, new ResponseMessage($this->as_ion_auth->errors(), 'error'));
	}
		
	function ajax_techs($assoc_acct_num){
		$arr_tech_obj = $this->ion_auth_model->get_dhi_supervisor_acct_nums_by_association($assoc_acct_num);
		$supervisor_acct_num_options = $this->as_ion_auth->get_dhi_supervisor_dropdown_data($arr_tech_obj);
        $this->sendResponse(200, null, $supervisor_acct_num_options);
	}
	
	function ajax_terms(){
		$text = $this->load->view('auth/terms', array(), true);
        $this->sendResponse(200, null, $text);
	}
	
	function set_role($group_id){
		if(array_key_exists($group_id, $this->session->userdata('arr_groups'))){
			$this->session->set_userdata('active_group_id', (int)$group_id);
            $this->sendResponse(200, new ResponseMessage('Active group has been set', 'message'));
		}
		else {
            $this->sendResponse(403, new ResponseMessage('You do not have rights to the requested group.', 'error'));
		}
        $this->sendResponse(500, new ResponseMessage('Request was unsuccessful.', 'error'));
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
