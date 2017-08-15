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
		parent::__construct();
        $this->load->library('form_validation');

		/* Load the profile.php config file if it exists
		if (ENVIRONMENT == 'development' || ENVIRONMENT == 'localhost') {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			} 
		}*/
	}

	function product_info_request(){
		$arr_inquiry = $this->input->userInput('products');
        $arr_user = $this->ion_auth_model->user($this->session->userdata('user_id'))->result_array()[0];

		if(isset($arr_inquiry) && is_array($arr_inquiry)){
			if($this->as_ion_auth->recordProductInquiry($arr_user['first_name'] . ' ' . $arr_user['last_name'], $arr_user['email'],$this->herd->herdCode(), $arr_inquiry, $this->input->userInput('comments'))){
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

	function list_accounts(){
		if(!$this->permissions->hasPermission("Edit All Users") && !$this->permissions->hasPermission("Edit Users In Region")){
            $this->sendResponse(403, new ResponseMessage('You do not have permission to edit user accounts.', 'error'));
		}
		//list the users
		$this->data['users'] = $this->as_ion_auth->get_editable_users();
		$this->data['arr_group_lookup'] = $this->ion_auth_model->get_group_lookup();
	}

	function login() {
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

        $this->data['trial_days'] = $this->config->item('trial_period');

        if ($this->as_ion_auth->login($this->input->userInput('identity'), $this->input->userInput('password'), $remember)){ //if the login is successful
            $this->_record_access(1); //1 is the page code for login for the user management section
            //get permissions (also in constuctor, put in function/class somewhere)
            $this->load->model('permissions_model');
            $this->load->model('product_model');

            $group_permissions = ProgramPermissions::getGroupPermissionsList($this->permissions_model, $this->session->userdata('active_group_id'));
            $products = new Products($this->product_model, $this->herd, $group_permissions);
            $this->permissions = new ProgramPermissions($this->permissions_model, $group_permissions, $products->allHerdProductCodes());

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
        $this->form_validation->set_rules('new', 'New Password', 'required|minLength[' . $this->config->item('min_password_length', 'ion_auth') . ']|maxLength[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[new_confirm]');
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
            'required|minLength[' . $this->config->item('min_password_length',
                'ion_auth') . ']|maxLength[' . $this->config->item('max_password_length',
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

    function manage_user(){
        $user_id = $this->input->userInput('user_id');
        if(isset($user_id)){
            $this->edit_user($user_id);
        }
        else{
            $this->create_user();
        }
    }

    function create_user(){
		//validate form input
		$this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
		$this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
		$this->form_validation->set_rules('email', 'Email Address', 'trim|required|valid_email');
		$this->form_validation->set_rules('supervisor_acct_num', 'Field Technician Account Number', 'maxLength[8]');
		$this->form_validation->set_rules('sg_acct_num', 'Service Group Account Number', 'maxLength[8]');
		$this->form_validation->set_rules('assoc_acct_num[]', 'Association Account Number', 'maxLength[8]');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|minLength[' . $this->config->item('min_password_length', 'ion_auth') . ']|maxLength[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]');
		$this->form_validation->set_rules('password_confirm', 'Password Confirmation', 'trim|required');
		$this->form_validation->set_rules('group_id[]', 'Name of User Group');
		$this->form_validation->set_rules('terms', 'Terms of Use Acknowledgement', 'required|exactLength[1]');
		$this->form_validation->set_rules('herd_code', 'Herd Code', 'exactLength[8]');
		$this->form_validation->set_rules('herd_release_code', 'Release Code', 'trim|exactLength[10]');
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
        $report_code = null;
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
            $herd_code = $this->input->post('herd_code') ? $this->input->post('herd_code') : NULL;
            $herd_release_code = $this->input->post('herd_release_code');
            $error = $this->herd_model->herd_authorization_error($herd_code, $herd_release_code);
            if($error){
                $this->as_ion_auth->set_error($error);
                $is_validated = false;
            }
            $herd_data = $this->herd_model->get_herd($herd_code);
            $dmi_regions = ['092', '093', '094', '095', '099'];
            if(in_array($herd_data['association_num'], $dmi_regions) === false){
                $report_code = 'AMYA-500';
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
            'group_id' => $arr_posted_group_id,
            'section_id' => $this->input->userInput('section_id')
        );

        try{
            $is_registered = $this->as_ion_auth->register($username, $password, $email, $additional_data, $arr_posted_group_id, $report_code);
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
		$this->form_validation->set_rules('supervisor_acct_num', 'Field Technician Number', 'exactLength[8]');
		$this->form_validation->set_rules('assoc_acct_num[]', 'Association/Region Account Number', 'exactLength[8]');
		$this->form_validation->set_rules('password', 'Password', 'trim|minLength[' . $this->config->item('min_password_length', 'ion_auth') . ']|maxLength[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]');
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
		
	function assoc_techs($assoc_acct_num){
        try{
            $arr_tech_obj = $this->ion_auth_model->get_dhi_supervisor_acct_nums_by_association($assoc_acct_num);
            $supervisor_acct_num_options = $this->as_ion_auth->get_dhi_supervisor_dropdown_data($arr_tech_obj);
            $this->sendResponse(200, null, $supervisor_acct_num_options);
        }
        catch(\Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
        $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
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

		$this->load->model('access_log_model');
		$access_log = new AccessLog($this->access_log_model);
				
		$access_log->writeEntry(
			$this->as_ion_auth->is_admin(),
			$event_id,
            $this->herd->herdCode(),
            isset($this->herd) ? $this->herd->getRecentTest() : NULL,
			$this->session->userdata('user_id'),
			$this->session->userdata('active_group_id')
		);
	}
}
