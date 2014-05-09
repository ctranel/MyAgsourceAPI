<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/ionauth.php';

class Auth extends Ionauth {
	protected $redirect_url;
	
	function __construct()
	{
		parent::__construct();
		$this->session->keep_flashdata('redirect_url');
		$this->redirect_url = set_redirect_url($this->uri->uri_string(), $this->session->flashdata('redirect_url'), $this->as_ion_auth->referrer);
		$this->session->set_flashdata('redirect_url', $this->redirect_url);
		$this->page_header_data['user_sections'] = $this->as_ion_auth->arr_user_super_sections;
		$this->page_header_data['num_herds'] = $this->as_ion_auth->get_num_viewable_herds($this->session->userdata('user_id'), $this->session->userdata('arr_regions'));
		
		//load necessary files
		$this->load->library('form_validation');
		//$this->load->helper('cookie');

		/* Load the profile.php config file if it exists
		if (ENVIRONMENT == 'development') {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			} 
		}*/
	}
	
	function index($pstring = NULL){
			$this->session->keep_flashdata('redirect_url');
			redirect(site_url('land/index/' . $pstring));
	}

	function section_info(){
		$arr_section_inquiry = $this->input->post('sections');
		if(isset($arr_section_inquiry) && is_array($arr_section_inquiry)){
			if($this->as_ion_auth->record_section_inquiry($arr_section_inquiry, $this->input->post('comments'))){
				$this->session->set_flashdata('message', 'Thank you for your interest.  Your request for more information has been sent.');
			}
			else{
				$this->session->set_flashdata('message', 'We encountered a problem sending your request.  Please try again or contact us at ' . $this->config->item("cust_serv_email") . ' or ' . $this->config->item("cust_serv_phone") . '.');
			}
		}
		else {
			$this->session->set_flashdata('message', 'Please select one or more web products and resubmit your request.');
		}
		redirect(site_url('auth'));
	}
	
	/*
	 * @description manage_service_grp is the page producers use to manage service group access
	 */
	function manage_service_grp(){
		if((!$this->as_ion_auth->logged_in())){
       		$this->session->keep_flashdata('message');
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
       		redirect(site_url('auth/login'));
		}
		if($this->session->userdata('active_group_id') != 2) {
			$this->session->keep_flashdata('redirect_url');
			$this->session->set_flashdata('message', 'Only producers can manage consultant access to their herd data.');
			redirect('auth');
		}
		
		$this->form_validation->set_rules('modify', 'Herd Selection');

		if ($this->form_validation->run() == TRUE) {
			$action = $this->input->post('submit');
			$arr_modify_id = $this->input->post('modify');
			if(isset($arr_modify_id) && is_array($arr_modify_id)){
				switch ($action) {
					case 'Remove Access':
						if($this->ion_auth_model->batch_herd_revoke($arr_modify_id)) {
							$this->_record_access(41);
							$this->page_header_data['message'] = 'Consultant access adjusted successfully.';
						}
						else $this->page_header_data['message'] = 'Consultant access adjustment failed.  Please try again.';
					break;
					case 'Grant Access':
						if($this->ion_auth_model->batch_grant_consult($arr_modify_id)) {
							$this->_record_access(34);
							$this->page_header_data['message'] = 'Consultant access adjusted successfully.';
						}
						else $this->page_header_data['message'] = 'Consultant access adjustment failed.  Please try again.';
					break;
					case 'Deny Access':
						if($this->ion_auth_model->batch_deny_consult($arr_modify_id)) {
							$this->_record_access(42);
							$this->page_header_data['message'] = 'Consultant access adjusted successfully.';
						}
						else $this->page_header_data['message'] = 'Consultant access adjustment failed.  Please try again.';
					break;
					case 'Remove Expiration Date':
						if($this->ion_auth_model->batch_remove_consult_expire($arr_modify_id)) {
							$this->_record_access(43);
							$this->page_header_data['message'] = 'Consultant access adjusted successfully.';
						}
						else $this->page_header_data['message'] = 'Consultant access adjustment failed.  Please try again.';
					break;
					default:
						$this->page_header_data['message'] = 'Consultant access adjustment failed.  Please try again.';
					break;
				}
			}
		}
		$this->page_header_data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());
		$consultants_by_status = $this->ion_auth_model->get_consultants_by_herd($this->session->userdata('herd_code'));
		if(isset($consultants_by_status['open']) && is_array($consultants_by_status['open'])){
			$section_data['content'] = $this->_set_consult_section($consultants_by_status['open'], 'open', array('Grant Access', 'Deny Access'));
			$section_data['title'] = 'Open Requests';
			$this->data['arr_sections']['open'] = $this->load->view('auth/service_grp/service_grp_section_container', $section_data, TRUE);
		}
		if(isset($consultants_by_status['deny']) && is_array($consultants_by_status['deny'])){
			$section_data['content'] = $this->_set_consult_section($consultants_by_status['deny'], 'deny', array('Grant Access'));
			$section_data['title'] = 'Denied Requests';
			$this->data['arr_sections']['deny'] = $this->load->view('auth/service_grp/service_grp_section_container', $section_data, TRUE);
		}
		if(isset($consultants_by_status['grant']) && is_array($consultants_by_status['grant'])) {
			$section_data['content'] = $this->_set_consult_section($consultants_by_status['grant'], 'grant', array('Remove Access'));
			$section_data['title'] = 'Granted Requests';
			$this->data['arr_sections']['grant'] = $this->load->view('auth/service_grp/service_grp_section_container', $section_data, TRUE);
		}
		if(isset($consultants_by_status['expired']) && is_array($consultants_by_status['expired'])) {
			$section_data['content'] = $this->_set_consult_section($consultants_by_status['expired'], 'expired', array('Remove Expiration Date'));
			$section_data['title'] = 'Expired Requests';
			$this->data['arr_sections']['expired'] = $this->load->view('auth/service_grp/service_grp_section_container', $section_data, TRUE);
		}
		if(isset($consultants_by_status['consult revoked']) && is_array($consultants_by_status['consult revoked'])){
			$section_data['content'] = $this->_set_consult_section($consultants_by_status['consult revoked'], 'consult revoked', NULL);
			$section_data['title'] = 'Consultant Revoked Access';
			$this->data['arr_sections']['consult_revoked'] = $this->load->view('auth/service_grp/service_grp_section_container', $section_data, TRUE);
		}
		if(isset($consultants_by_status['herd revoked']) && is_array($consultants_by_status['herd revoked'])){
			$section_data['content'] = $this->_set_consult_section($consultants_by_status['herd revoked'], 'herd revoked', array('Grant Access'));
			$section_data['title'] = 'Herd Revoked Access';
			$this->data['arr_sections']['herd_revoked'] = $this->load->view('auth/service_grp/service_grp_section_container', $section_data, TRUE);
		}
		$this->carabiner->css('accordion.css', 'screen');
		if(is_array($this->page_header_data)){
			$this->page_header_data = array_merge($this->page_header_data,
				array(
					'title'=>'Manage Herd Access - ' . $this->config->item('product_name'),
					'description'=>'Manage Herd Access, ' . $this->config->item('product_name'),
					'arr_headjs_line'=>array(
						'{sg_helper: "' . $this->config->item("base_url_assets") . 'js/consultant_helper.js"}',
					)
				)
			);
		}
		$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
		$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
		$this->data['page_heading'] = "Manage Herd Access";
		$footer_data = array();
		$this->data['page_footer'] = $this->load->view('page_footer', $footer_data, TRUE);
		
		$this->load->view('auth/service_grp/manage_service_grp', $this->data);
	}
	
	/*
	 * @description manage_service_grp is the page service groups use to manage herd access
	 */
	function service_grp_manage_herds(){
		if((!$this->as_ion_auth->logged_in())){
       		$this->session->keep_flashdata('message');
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
       		redirect(site_url('auth/login'));
		}
		if($this->as_ion_auth->has_permission('View non-own w permission') !== TRUE) {
			$this->session->keep_flashdata('redirect_url');
			$this->session->set_flashdata('message', 'You do not have permission to view non-owned herds.');
			redirect('auth');
		}

		$this->form_validation->set_rules('modify', 'Herd Selection');

		if ($this->form_validation->run() == TRUE) {
			$action = $this->input->post('submit');
			$arr_modify_id = $this->input->post('modify');
			if(isset($arr_modify_id) && is_array($arr_modify_id)){
				switch ($action) {
					case 'Remove Access':
						if($this->ion_auth_model->batch_consult_revoke($arr_modify_id)){
							$this->_record_access(41);
							$this->page_header_data['message'] = 'Consultant access adjusted successfully.';
						}
						else $this->page_header_data['message'] = 'Consultant access adjustment failed.  Please try again.';
					break;
					case 'Restore Access':
						//if consultant had revoked access, they can restore it (call grant_access)
						foreach($arr_modify_id as $k=>$id){
							if($this->ion_auth_model->get_consult_status_text($id) != 'consult revoked') unset($arr_modify_id[$k]);
						}
						if(!empty($arr_modify_id) && $this->ion_auth_model->batch_grant_consult($arr_modify_id)) {
							$this->_record_access(34);
							$this->page_header_data['message'] = 'Consultant access adjusted successfully.';
						}
						else $this->page_header_data['message'] = 'Consultant access adjustment failed.  Please try again.';
					break;
					case 'Resend Request Email':
						foreach($arr_modify_id as $k=>$id){
							$arr_relationship_data = $this->ion_auth_model->get_consult_relationship_by_id($id);
							if ($this->as_ion_auth->send_consultant_request($arr_relationship_data, $id, $this->config->item('cust_serv_email'))) {
								$this->_record_access(35);
								$this->page_header_data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());
							}
							else { //if the request was un-successful
								//redirect them back to the login page
								$this->page_header_data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());
							}
						}
					break;
					default:
						$this->page_header_data['message'] = 'Consultant access adjustment failed.  Please try again.';
					break;
				}
			}
		}
		if(!isset($this->page_header_data['message'])){
			$this->page_header_data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());
		}

		$herds_by_status = $this->ion_auth_model->get_herds_by_consult($this->session->userdata('user_id'));
		if(isset($herds_by_status['open']) && is_array($herds_by_status['open'])){

			$section_data['content'] = $this->_set_consult_herd_section($herds_by_status['open'], 'open', array('Resend Request Email'));
			$section_data['title'] = 'Open Requests';
			$this->data['arr_sections']['open'] = $this->load->view('auth/service_grp/herd_section_container', $section_data, TRUE);
		}
		if(isset($herds_by_status['deny']) && is_array($herds_by_status['deny'])){
			$section_data['content'] = $this->_set_consult_herd_section($herds_by_status['deny'], 'deny', NULL);
			$section_data['title'] = 'Denied Requests';
			$this->data['arr_sections']['deny'] = $this->load->view('auth/service_grp/herd_section_container', $section_data, TRUE);
		}
		if(isset($herds_by_status['grant']) && is_array($herds_by_status['grant'])) {
			$section_data['content'] = $this->_set_consult_herd_section($herds_by_status['grant'], 'grant', array('Remove Access'));
			$section_data['title'] = 'Granted Requests';
			$this->data['arr_sections']['grant'] = $this->load->view('auth/service_grp/herd_section_container', $section_data, TRUE);
		}
		if(isset($herds_by_status['expired']) && is_array($herds_by_status['expired'])) {
			$section_data['content'] = $this->_set_consult_herd_section($herds_by_status['expired'], 'expired', array('Resend Request Email'));
			$section_data['title'] = 'Expired Requests';
			$this->data['arr_sections']['expired'] = $this->load->view('auth/service_grp/herd_section_container', $section_data, TRUE);
		}
		if(isset($herds_by_status['consult revoked']) && is_array($herds_by_status['consult revoked'])){
			$section_data['content'] = $this->_set_consult_herd_section($herds_by_status['consult revoked'], 'consult revoked', array('Restore Access'));
			$section_data['title'] = 'Consultant Revoked Access';
			$this->data['arr_sections']['consult_revoked'] = $this->load->view('auth/service_grp/herd_section_container', $section_data, TRUE);
		}
		if(isset($herds_by_status['herd revoked']) && is_array($herds_by_status['herd revoked'])){
			$section_data['content'] = $this->_set_consult_herd_section($herds_by_status['herd revoked'], 'herd revoked', NULL);
			$section_data['title'] = 'Herd Revoked Access';
			$this->data['arr_sections']['herd_revoked'] = $this->load->view('auth/service_grp/herd_section_container', $section_data, TRUE);
		}
		$this->carabiner->css('accordion.css', 'screen');
		$this->data['title'] = "Manage Herd Access";
		if(is_array($this->page_header_data)){
			$this->page_header_data = array_merge($this->page_header_data,
				array(
					'title'=>'Manage Herd Access - ' . $this->config->item('product_name'),
					'description'=>'Manage Herd Access, ' . $this->config->item('product_name'),
				)
			);
		}
		$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
		$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
		$this->data['page_heading'] = "Manage Herd Access";
		$footer_data = array();
		$this->data['page_footer'] = $this->load->view('page_footer', $footer_data, TRUE);
		$this->load->view('auth/service_grp/manage_service_grp', $this->data);
	}
	
	function _set_consult_section($data, $key, $arr_submit_options){
	//this code is also used in land/_set_consult_section
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

	function _set_consult_herd_section($data, $key, $arr_submit_options){
		if(isset($data) && is_array($data)){
			$this->section_data = array(
				'arr_submit_options' => $arr_submit_options,
				'attributes' => array('class' => $key . ' consult-form'),
			);
			foreach($data as $h) {
				$h['is_editable'] = FALSE;
				$this->section_data['arr_records'][] = $this->load->view('auth/service_grp/herd_line', $h, TRUE);
			}
			return $this->load->view('auth/service_grp/herd_section', $this->section_data, TRUE);
		}
	}
	
	//Producers only, give consultant permission to view herd
	function service_grp_access($cuid = NULL) {
		if((!$this->as_ion_auth->logged_in())){
       		$this->session->keep_flashdata('message');
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
       		redirect(site_url('auth/login'));
		}
		if($this->session->userdata('active_group_id') != 2) {
			$this->session->keep_flashdata('redirect_url');
			$this->session->set_flashdata('message', 'Only producers can manage access to their herd data.');
			redirect('auth');
		}
		
		$this->data['title'] = "Grant Consultant Access to Herd";

		//validate form input
		$this->form_validation->set_rules('section_id', 'Sections', '');
		$this->form_validation->set_rules('exp_date', 'Expiration Date', 'trim');
		$this->form_validation->set_rules('request_status_id', 'Request Status', '');
		$this->form_validation->set_rules('write_data', 'Enter Event Data', '');
		//$this->form_validation->set_rules('request_status_id', '', '');
		$this->form_validation->set_rules('disclaimer', 'Confirmation of Understanding', 'required');

		if ($this->form_validation->run() == TRUE) {
			$arr_relationship_data = array(
				'sg_user_id' => (int)$this->input->post('sg_user_id'),
				'herd_code' => $this->session->userdata('herd_code'),
				'write_data' => (int)$this->input->post('write_data'),
				'active_date' => date('Y-m-d'),
				'active_user_id' => $this->session->userdata('user_id'),
			);
			$post_request_status_id = $this->input->post('request_status_id');
			if(isset($post_request_status_id) && !empty($post_request_status_id)){
				$arr_relationship_data['request_status_id'] = (int)$post_request_status_id;
			}
			$tmp = human_to_mysql($this->input->post('exp_date'));
			if(isset($tmp) && !empty($tmp)) $arr_relationship_data['exp_date'] = $tmp;

			//convert submitted section id values to int
			$arr_post_section_id = $this->input->post('section_id');
			if(isset($arr_post_section_id) && is_array($arr_post_section_id)){
				array_walk($arr_post_section_id, function (&$value) { $value = (int)$value; });
			}
			
			if ($this->as_ion_auth->allow_service_grp($arr_relationship_data, $arr_post_section_id)) { //if permission is granted successfully
				//redirect them back to the home page
				$msg = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());
				$this->_record_access(34);
				$this->session->set_flashdata('message', $msg);
				redirect(site_url($this->redirect_url)); //to access management page?
			}
			else { //if the request was un-successful
				$msg = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());
				$this->session->set_flashdata('message', $msg);
				redirect(site_url('auth/service_grp_access'));
			}
		}
		else {
			//set the flash data error message if there is one
			$this->page_header_data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());
			//check of an existing record for this relationship
			if(isset($cuid) && !empty($cuid)){
				$arr_relationship = $this->ion_auth_model->get_consult_relationship($cuid, $this->session->userdata('herd_code'));
				$requester = $this->ion_auth_model->user($cuid)->row();
				$this->data['requester_name'] = $requester->first_name . ' ' . $requester->last_name;
			}
			else $arr_relationship = FALSE;
			
			// get sections for user
/*			if($arr_relationship['service_grp_request']){
				$arr_form_section_id = $this->ion_auth_model->get_consult_rel_sections($arr_relationship['id']);
			}
			else{
				$user_id = $this->session->userdata('user_id');
				$obj_user = $this->ion_auth_model->user($user_id)->row();
				$obj_user->arr_groups = array_keys($this->ion_auth_model->get_users_group_array($obj_user->id));
				//note: active group id should always be 2
				$tmp_array = $this->as_ion_auth->get_sections_array(2, $user_id, $obj_user->herd_code, NULL, array('subscription','public','unmanaged'));
				$obj_user->section_id = $this->as_ion_auth->set_form_array($tmp_array, 'id', 'id'); // populate array of sections for which user is enrolled
				$tmp_array = $this->input->post('section_id');
				$arr_form_section_id = isset($tmp_array) && is_array($tmp_array) ? $tmp_array : $obj_user->section_id;
			}

			$this->data['sections_selected'] = $arr_form_section_id;
			$this->data['section_id'] = 'id="section_id"';
			//note: active group id should always be 2
			$tmp_array = $this->as_ion_auth->get_sections_array($this->session->userdata('active_group_id'), $this->session->userdata('user_id'), $this->session->userdata('herd_code'), NULL, array('subscription', 'public', 'unmanaged'));
			$this->data['section_options'] = $this->as_ion_auth->set_form_array($tmp_array, 'id', 'name');
			unset($tmp_array);
*/
			$this->data['sg_user_id'] = array(
					'name' => 'sg_user_id',
					'id' => 'sg_user_id',
					'type' => 'hidden',
					'value' => $arr_relationship['sg_user_id'],
			);
			$this->data['exp_date'] = array(
				'name' => 'exp_date',
				'id' => 'exp_date',
				'type' => 'text',
				'value' => $this->form_validation->set_value('exp_date', $arr_relationship ? mysql_to_human($arr_relationship['exp_date']) : ''),
			);
			if($arr_relationship['request_status_id'] !== 3){
				$this->data['request_denied'] = array(
					'name' => 'request_status_id',
					'id' => 'request_denied',
					'type' => 'radio',
					'value' => 2,
					'checked' => set_radio('request_status_id', 'deny', $arr_relationship && $arr_relationship == 2 ? TRUE : FALSE)
				);
				$this->data['request_granted'] = array(
					'name' => 'request_status_id',
					'id' => 'request_granted',
					'type' => 'radio',
					'value' => 1,
					'checked' => set_radio('request_status_id', 'grant', $arr_relationship && $arr_relationship['request_status_id'] != 2 ? TRUE : FALSE)
				);
			}
/*			$this->data['write_data'] = array(
				'name' => 'write_data',
				'id' => 'write_data',
				'type' => 'checkbox',
				'value' => '1',
				'checked' => set_checkbox('write_data', 1, $arr_relationship && $arr_relationship['write_data'] == '1' ? TRUE : FALSE)
			); */
			$this->data['disclaimer'] = array(
				'name' => 'disclaimer',
				'id' => 'disclaimer',
				'type' => 'checkbox',
				'value' => '1',
				'checked' => FALSE
			);


			$this->carabiner->css('agsource.datepick.css', 'screen');
			$this->carabiner->css('jquery.datetimeentry.css', 'screen');
			if(is_array($this->page_header_data)){
				$this->page_header_data = array_merge($this->page_header_data,
					array(
						'title'=>'Grant Consultant Access - ' . $this->config->item('product_name'),
						'description'=>'Grant Consultant Access to ' . $this->config->item('product_name'),
						'arr_headjs_line'=>array(
							'{datepick: "' . $this->config->item("base_url_assets") . 'js/jquery/jquery.datepick.min.js"}',
							'{report_helper: "' . $this->config->item("base_url_assets") . 'js/consultant_helper.js"}',
						)
					)
				);
			}
			$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
			$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
			$this->data['page_heading'] = 'Grant Consultant Access';
			$footer_data = array(
			);
			$this->data['page_footer'] = $this->load->view('page_footer', $footer_data, TRUE);

			$this->load->view('auth/service_grp/allow_service_grp', $this->data);
		}
	}

	//Consultants only, request permission to view herd
	function service_grp_request() {
		if((!$this->as_ion_auth->logged_in())){
       		$this->session->keep_flashdata('message');
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
       		redirect(site_url('auth/login'));
		}
		if(!$this->as_ion_auth->has_permission('View non-own w permission')) {
			$this->session->keep_flashdata('redirect_url');
			$this->session->set_flashdata('message', 'You do not have permission to request the data of a herd you do not own.');
			redirect('auth');
		}

		$this->data['title'] = "Request Access to Herd";

		//validate form input
		$this->form_validation->set_rules('herd_code', 'Herd Code', 'trim|required|exact_length[8]');
		$this->form_validation->set_rules('herd_release_code', 'Release Code', 'trim|required|exact_length[10]');
		$this->form_validation->set_rules('section_id', 'Sections', '');
		$this->form_validation->set_rules('exp_date', 'Expiration Date', 'trim');
		$this->form_validation->set_rules('write_data', 'Enter Event Data', '');
//		$this->form_validation->set_rules('request_status_id', '', '');
		$this->form_validation->set_rules('disclaimer', 'Confirmation of Understanding', 'required');

		$is_validated = $this->form_validation->run();
		if ($is_validated === TRUE) {
			$herd_code = $this->input->post('herd_code');
			/* herd does not have to be registered at this point
			if(!$this->herd_model->herd_is_registered($herd_code)){
				$this->session->set_flashdata('message', 'Herd ' . $herd_code . ' is not registered for ' . $this->config->item('product_name') . '.  In order to access their data, they must be registered for ' . $this->config->item('product_name') . '.');
				redirect(site_url('auth/service_grp_request'));
			} */
			$herd_release_code = $this->input->post('herd_release_code');
			$error = $this->herd_model->herd_authorization_error($herd_code, $herd_release_code);
			
			if($this->ion_auth_model->get_consult_relationship_id($this->session->userdata('user_id'), $herd_code) !== FALSE){
				$error = 'relationship_exists';
			}
			if($error){
				$this->as_ion_auth->set_error($error);
				$is_validated = false;
			}
			$arr_relationship_data = array(
				'herd_code' => $herd_code,
				'sg_user_id' => $this->session->userdata('user_id'),
				'service_grp_request' => 1, //bit - did a service group request
				'write_data' => (int)$this->input->post('write_data'),
				'request_status_id' => 7, //7 is the id for open request
				'active_date' => date('Y-m-d'),
				'active_user_id' => $this->session->userdata('user_id'),
			);
			$tmp = human_to_mysql($this->input->post('exp_date'));
			if(isset($tmp) && !empty($tmp)) $arr_relationship_data['exp_date'] = $tmp;

			//convert submitted section id values to int
/*			$arr_post_section_id = $this->input->post('section_id');
			array_walk($arr_post_section_id, function (&$value) { $value = (int)$value; });
*/			$arr_post_section_id = array();
			
			if ($is_validated === TRUE && $this->as_ion_auth->service_grp_request($arr_relationship_data, $arr_post_section_id, $this->config->item('cust_serv_email'))) {
				$this->_record_access(35);
				$msg = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());
				$this->session->set_flashdata('message', $msg);
				redirect(site_url($this->redirect_url)); //  to manage access page
			}
			else { //if the request was un-successful
				//redirect them back to the login page
				$msg = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());
				$this->session->set_flashdata('message', $msg);
				redirect(site_url('auth/service_grp_request'));
			}
		}
		else {
			//set the flash data error message if there is one
			$this->page_header_data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());
			// get sections for user
//			$user_id = $this->input->post('user_id');
			$user_id = $this->session->userdata('user_id');
			$obj_user = $this->ion_auth_model->user($user_id)->row();
			$obj_user->arr_groups = array_keys($this->ion_auth_model->get_users_group_array($obj_user->id));
/*			
			$tmp_array = $this->as_ion_auth->get_sections_array($this->session->userdata('active_group_id'), $user_id, $obj_user->herd_code, NULL, array('subscription','public','unmanaged'));
			$obj_user->section_id = $this->as_ion_auth->set_form_array($tmp_array, 'id', 'id'); // populate array of sections for which user is enrolled

			$tmp_array = $this->input->post('section_id');
			$arr_form_section_id = isset($tmp_array) && is_array($tmp_array) ? $tmp_array : $obj_user->section_id;

			$this->data['sections_selected'] = $arr_form_section_id;
			$this->data['section_id'] = 'id="section_id"';
				
			$arr_super_sections = $this->as_ion_auth->get_super_sections_array($this->session->userdata('active_group_id'), $this->session->userdata('user_id'), $this->session->userdata('herd_code'), array('subscription','public','unmanaged'));
			$arr_super_section_id = array_extract_value_recursive('id', $arr_super_sections);
			$tmp_array = $this->as_ion_auth->get_sections_array($this->session->userdata('active_group_id'), $user_id, FALSE, $arr_super_section_id, array('subscription','public','unmanaged'));
			$this->data['section_options'] = $this->as_ion_auth->set_form_array($tmp_array, 'id', 'name');
*/
			$this->data['herd_code'] = array(
				'name' => 'herd_code',
				'id' => 'herd_code',
				'type' => 'text',
				'value' => $this->form_validation->set_value('herd_code'),
				'class' => 'required',
			);
			$this->data['herd_release_code'] = array('name' => 'herd_release_code',
				'id' => 'herd_release_code',
				'type' => 'text',
				'size' => '10',
				'maxlength' => '10',
				'value' => $this->form_validation->set_value('herd_release_code'),
				'class' => 'required',
			);
			$this->data['exp_date'] = array(
				'name' => 'exp_date',
				'id' => 'exp_date',
				'type' => 'text',
				'value' => $this->form_validation->set_value('exp_date'),
			);
/*			$this->data['write_data'] = array(
				'name' => 'write_data',
				'id' => 'write_data',
				'type' => 'checkbox',
				'value' => '1',
				'checked' => set_checkbox('write_data', 1, FALSE)
			);
*/
			$this->data['disclaimer'] = array(
				'name' => 'disclaimer',
				'id' => 'disclaimer',
				'type' => 'checkbox',
				'value' => '1',
				'checked' => FALSE
			);

			$this->carabiner->css('agsource.datepick.css', 'screen');
			$this->carabiner->css('jquery.datetimeentry.css', 'screen');
			if(is_array($this->page_header_data)){
				$this->page_header_data = array_merge($this->page_header_data,
					array(
						'title'=>'Request Data Access - ' . $this->config->item('product_name'),
						'description'=>'Grant Consultant Access to ' . $this->config->item('product_name'),
						'arr_headjs_line'=>array(
							'{datepick: "' . $this->config->item("base_url_assets") . 'js/jquery/jquery.datepick.min.js"}',
							'{report_helper: "' . $this->config->item("base_url_assets") . 'js/consultant_helper.js"}',
						)
					)
				);
			}
			$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
			$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
			$this->data['page_heading'] = 'Request Data Access';
			$footer_data = array(
			);
			$this->data['page_footer'] = $this->load->view('page_footer', $footer_data, TRUE);
			$this->load->view('auth/service_grp/service_grp_request', $this->data);
		}
	}

	function list_accounts(){
		if(!$this->as_ion_auth->has_permission("Add All Users") && !$this->as_ion_auth->has_permission("Add Users In Region")){
       		$this->session->set_flashdata('message',  $this->session->flashdata('message') . "You do not have permission to edit user accounts.");
       		redirect(site_url(), 'refresh');
		}
		//set the flash data error message if there is one
		$this->page_header_data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());
		//list the users
		$this->data['users'] = $this->as_ion_auth->get_editable_users();
		$this->data['arr_group_lookup'] = $this->ion_auth_model->get_group_lookup();
		
		$this->carabiner->css('datatables/table_ui.css', 'screen');
		if(is_array($this->page_header_data)){
			$this->page_header_data = array_merge($this->page_header_data,
				array(
					'title'=>'User List - ' . $this->config->item('product_name'),
					'description'=>'Log In Form - ' . $this->config->item('product_name'),
					'arr_headjs_line'=>array(
						'{datatable: "' . $this->config->item("base_url_assets") . 'js/jquery/jquery.dataTables.min.js"}'
					)
				)
			);
		}
		$this->footer_data = Array(
			'arr_foot_line'=>array(
				'<script type="text/javascript">head.ready("datatable", function(){ $("#sortable").dataTable({
					"iDisplayLength": -1,
					"aaSorting": [[1,"asc"]],
					"aLengthMenu": [[50, 100, 200, -1], [50, 100, 200, "All"]]
				}); });</script>'
			)
		);
		$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
		$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, true);
		$this->data['page_heading'] = 'User List';
		$this->data['page_footer'] = $this->load->view('page_footer', $this->footer_data, TRUE);
		$this->load->view('auth/index', $this->data);
	}

	//CDT overrides built-in function to allow us to redirect user to the original page they requested after login in
	function login()
	{
		$this->data['title'] = "Login";

		//validate form input
		$this->form_validation->set_rules('identity', 'Email Address', 'trim|required|valid_email');
		$this->form_validation->set_rules('password', 'Password', 'trim|required');

		if ($this->form_validation->run() == TRUE)
		{ //check to see if the user is logging in
			//check for "remember me"
			$remember = (bool) $this->input->post('remember');
			//Clear out herd code in case user was browsing demo herd before logging in.
			$this->session->unset_userdata('herd_code');
			$this->session->unset_userdata('arr_pstring');
			$this->session->unset_userdata('pstring');
			$this->session->unset_userdata('arr_tstring');
			$this->session->unset_userdata('tstring');
			//$this->session->sess_destroy();
			//$this->session->sess_create();
		
			if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember))
			{ //if the login is successful
				$this->_record_access(1); //1 is the page code for login for the user management section
				$this->session->set_flashdata('message', $this->as_ion_auth->messages());
				$this->session->set_flashdata('redirect_url', $this->redirect_url);
				redirect(site_url('dhi/change_herd/select'));
			}
			else
			{ //if the login was un-successful
				$this->session->set_flashdata('redirect_url', $this->redirect_url);
				$this->session->set_flashdata('message', $this->as_ion_auth->errors());
				redirect(site_url('auth/login')); //use redirects instead of loading views for compatibility with MY_Controller libraries
			}
		}
		else
		{  //the user is not logging in so display the login page
			//set the flash data error message if there is one
			$this->page_header_data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());

			$this->data['identity'] = array('name' => 'identity',
				'id' => 'identity',
				'type' => 'text',
				'value' => $this->form_validation->set_value('identity'),
			);
			$this->data['password'] = array('name' => 'password',
				'id' => 'password',
				'type' => 'password',
			);

			$this->carabiner->css('boxes.css', 'screen');
			$this->carabiner->css('login.css', 'screen');
			if(is_array($this->page_header_data)){
				$this->page_header_data = array_merge($this->page_header_data,
					array(
						'title'=>$this->config->item('product_name') . 'Login',
						'description'=>$this->config->item('product_name') . 'Log In Form'
					)
				);
			}
			$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
			$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
			$this->data['page_heading'] = $this->config->item('product_name') . 'Login';
			$this->data['page_footer'] = $this->load->view('page_footer', NULL, TRUE);

			$this->load->view('auth/login', $this->data);
		}
	}

	//log the user out
	function logout()
	{
		//IE seemed to cache the redirect from previous page loads.  Prevent any caching
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		$this->data['title'] = "Logout";

		//log the user out
		$logout = $this->as_ion_auth->logout();

		//redirect them
		redirect(site_url('auth/login'));
	}

	//change password
	function change_password()
	{
		if (!$this->as_ion_auth->logged_in()){
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
		}
		if(is_array($this->page_header_data)){
			$this->page_header_data = array_merge($this->page_header_data,
				array(
					'title'=>'Update Password - ' . $this->config->item('product_name'),
					'description'=>'Update Password - ' . $this->config->item('product_name')
				)
			);
		}
		$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
		$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, true);
		$this->data['page_heading'] = 'Update Password';
		$this->data['page_footer'] = $this->load->view('page_footer', null, true);
		parent::change_password;
	}

	//forgot password
	function forgot_password()
	{
		if(is_array($this->page_header_data)){
			$this->page_header_data = array_merge($this->page_header_data,
				array(
					'title'=>'Forgotten Password - ' . $this->config->item('product_name'),
					'description'=>'Forgot Password - ' . $this->config->item('product_name')
				)
			);
		}
		$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
		$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
		$this->data['page_heading'] = 'Forgotten Password';
		$this->data['page_footer'] = $this->load->view('page_footer', null, TRUE);
		parent::forgot_password();
	}

	//reset password - final step for forgotten password
	public function reset_password($code = NULL){
		if(is_array($this->page_header_data)){
			$this->page_header_data = array_merge($this->page_header_data,
				array(
					'title'=>'Reset Password - ' . $this->config->item('product_name'),
					'description'=>'Reset Password - ' . $this->config->item('product_name')
				)
			);
		}
		$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
		$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
		$this->data['page_heading'] = 'Forgotten Password';
		$this->data['page_footer'] = $this->load->view('page_footer', null, TRUE);
		parent::reset_password($code);
	}
	
	//deactivate the user
	function deactivate($id = NULL)
	{
		if(is_array($this->page_header_data)){
			$this->page_header_data = array_merge($this->page_header_data,
				array(
					'title'=>'Deactivate User - ' . $this->config->item('product_name'),
					'description'=>'Deactivate User - ' . $this->config->item('product_name')
				)
			);
		}
		$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
		$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, true);
		$this->data['page_heading'] = 'Deactivate User';
		$this->data['page_footer'] = $this->load->view('page_footer', null, true);
		parent::deactivate($id);
	}


	function create_user()
	{
		$this->data['title'] = "Create Account";

		//validate form input
		$this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
		$this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
		$this->form_validation->set_rules('email', 'Email Address', 'trim|required|valid_email');
		$this->form_validation->set_rules('supervisor_acct_num', 'Field Technician Account Number', 'max_length[8]');
		$this->form_validation->set_rules('sg_acct_num', 'Service Group Account Number', 'max_length[8]');
		$this->form_validation->set_rules('assoc_acct_num[]', 'Association Account Number', 'max_length[8]');
		$this->form_validation->set_rules('phone1', 'First Part of Phone', 'exact_length[3]|required');
		$this->form_validation->set_rules('phone2', 'Second Part of Phone', 'exact_length[3]|required');
		$this->form_validation->set_rules('phone3', 'Third Part of Phone', 'exact_length[4]|required');
		$this->form_validation->set_rules('best_time', 'Best Time to Call', 'max_length[10]|required');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]');
		$this->form_validation->set_rules('password_confirm', 'Password Confirmation', 'trim|required');
		$this->form_validation->set_rules('group_id[]', 'Name of User Group');
		$this->form_validation->set_rules('terms', 'Terms of Use Acknowledgement', 'required|exact_length[1]');
		$this->form_validation->set_rules('herd_code', 'Herd Code', 'exact_length[8]');
		$this->form_validation->set_rules('herd_release_code', 'Release Code', 'trim|exact_length[10]');
		$this->form_validation->set_rules('section_id[]', 'Section');

		$is_validated = $this->form_validation->run();
		if ($is_validated === TRUE) {
			$arr_posted_group_id = $this->form_validation->set_value('group_id[]');
			if(!$this->as_ion_auth->group_assignable($arr_posted_group_id)){
				$this->session->set_flashdata('message', 'You do not have permissions to create a user with the user group you selected.  Please try again, or contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . '.');
				redirect("auth/create_user", 'refresh');
				exit();
			}
			
			//start with nothing
			$assoc_acct_num = NULL;
			$supervisor_acct_num = NULL;
			$sg_acct_num = NULL;
			$herd_code = NULL;
			$herd_release_code = NULL;

			//Set variables that depend on group(s) selected
			if($this->as_ion_auth->has_permission("Add All Users") || $this->as_ion_auth->has_permission("Add Users In Region")){
				$arr_posted_group_id = $this->input->post('group_id');
				if(!$this->as_ion_auth->group_assignable($arr_posted_group_id)){
					$this->session->set_flashdata('message', 'You do not have permissions to add a user with the user group you selected.  Please try again, or contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . '.');
					redirect(site_url("auth/create_user/$user_id"), 'refresh');
					exit();
				}
				$assoc_acct_num = $this->input->post('assoc_acct_num');
				$supervisor_acct_num = $this->input->post('supervisor_acct_num');
				if(empty($assoc_acct_num)){
					$assoc_acct_num = NULL;
				}
				if(empty($supervisor_acct_num)){
					$supervisor_acct_num = NULL;
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
			}
			if(in_array(9, $arr_posted_group_id)){ //service groups
				$sg_acct_num = $this->input->post('sg_acct_num');
				if(!$this->as_ion_auth->service_grp_exists($sg_acct_num)){
					$is_validated = false;
				}
			}
			
			$username = substr(strtolower($this->input->post('first_name')) . ' ' . strtolower($this->input->post('last_name')),0,15);
			$email = $this->input->post('email');
			$password = $this->input->post('password');
			$additional_data = array('first_name' => $this->input->post('first_name'),
				'herd_code' => $herd_code,
				'last_name' => $this->input->post('last_name'),
				'supervisor_acct_num' => $supervisor_acct_num,
				'sg_acct_num' => $sg_acct_num,
				'assoc_acct_num' => $assoc_acct_num,
				'phone' => $this->input->post('phone1') . '-' . $this->input->post('phone2') . '-' . $this->input->post('phone3'),
				'best_time' => $this->input->post('best_time'),
				'group_id' => $arr_posted_group_id,
				'section_id' => $this->input->post('section_id')
			);
			if($additional_data['phone'] == '--') $additional_data['phone'] = '';
		}
		if ($is_validated === TRUE && $this->as_ion_auth->register($username, $password, $email, $additional_data, $arr_posted_group_id)) { //check to see if we are creating the user
			//$this->as_ion_auth->activate();
			$this->session->set_flashdata('message', "Your account has been created.  A member of the AgSource Customer Service team will contact you to activate your account.");
			redirect(site_url("auth/login"), 'refresh');
		}
		else { //display the create user form
			//set the flash data error message if there is one
			$this->page_header_data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());
							
			$this->data['first_name'] = array('name' => 'first_name',
				'id' => 'first_name',
				'type' => 'text',
				'value' => $this->form_validation->set_value('first_name'),
				'size' => '25',
				'maxlength' => '50',
				'class' => 'require'
			);
			$this->data['last_name'] = array('name' => 'last_name',
				'id' => 'last_name',
				'type' => 'text',
				'value' => $this->form_validation->set_value('last_name'),
				'size' => '25',
				'maxlength' => '50',
				'class' => 'require'
			);
			$this->data['email'] = array('name' => 'email',
				'id' => 'email',
				'type' => 'text',
				'value' => $this->form_validation->set_value('email'),
				'size' => '50',
				'maxlength' => '100',
				'class' => 'require'
			);
			$arr_form_group_id = $this->form_validation->set_value('group_id[]', array($this->config->item('default_group_id', 'ion_auth')));
			$this->data['group_selected'] = $arr_form_group_id;
			$this->data['group_options'] = $this->as_ion_auth->get_group_dropdown_data($this->session->userdata('active_group_id'));
			if(count($this->data['group_options']) > 1){ // if $this->data['group_id'] is not set, the field will not appear on the form
				$this->data['group_id'] = 'id="group_id" class = "require"';
				if($this->as_ion_auth->has_permission("Edit All Users") || $this->as_ion_auth->has_permission("Edit Users In Region")){
					$this->data['group_id'] .= ' multiple size="4"';
				}
			}
				
			if($this->as_ion_auth->has_permission("Add All Users")){
				$this->data['assoc_acct_options'] = $this->as_ion_auth->get_assoc_dropdown_data(array_keys($this->session->userdata('arr_regions')));
				$this->data['assoc_acct_selected'] = $this->form_validation->set_value('assoc_acct_num[]');
				$this->data['assoc_acct_num'] = 'class = "require"';
			}
			elseif($this->as_ion_auth->has_permission("Add Users In Region")){
				$this->data['assoc_acct_num'] = array('name' => 'assoc_acct_num[]',
					'id' => 'assoc_acct_num',
					'type' => 'hidden',
					'class' => 'require',
					'value' => $this->session->userdata('assoc_acct_num'),
				);
			}
			
			if($this->as_ion_auth->has_permission("Add All Users") || $this->as_ion_auth->has_permission("Add Users In Region")){
				$arr_form_assoc_acct_num = $this->form_validation->set_value('assoc_acct_num[]', array_keys($this->session->userdata('arr_regions')));
				$arr_tech_obj = $this->ion_auth_model->get_dhi_supervisor_acct_nums_by_association($arr_form_assoc_acct_num);
				$this->data['supervisor_acct_num_options'] = !empty($arr_form_assoc_acct_num)?$this->as_ion_auth->get_dhi_supervisor_dropdown_data($arr_tech_obj):array();
				$this->data['supervisor_acct_num_selected'] = $this->form_validation->set_value('supervisor_acct_num');
				$this->data['supervisor_acct_num'] = 'class = "require"';
			}
				
			if($this->as_ion_auth->has_permission("Assign Sections")){
				$this->data['section_selected'] = $this->form_validation->set_value('section_id[]', array());
				$this->data['section_id'] = 'id="section_id"';
				$this->data['section_options'] = $this->as_ion_auth->ion_auth_model(array('subscription'));
			}

			$this->data['herd_code'] = array('name' => 'herd_code',
				'id' => 'herd_code',
				'type' => 'text',
				'size' => '8',
				'maxlength' => '8',
				'value' => $this->form_validation->set_value('herd_code')
			);
			if(in_array('2', $arr_form_group_id)){
				$this->data['herd_code']['class'] = 'require';
			}
			
			$this->data['herd_release_code'] = array('name' => 'herd_release_code',
				'id' => 'herd_release_code',
				'type' => 'text',
				'size' => '10',
				'maxlength' => '10',
				'value' => $this->form_validation->set_value('herd_release_code')
			);
			if(in_array('2', $arr_form_group_id)) $this->data['herd_release_code']['class'] = 'require';

			$this->data['sg_acct_num'] = array('name' => 'sg_acct_num',
				'id' => 'sg_acct_num',
				'type' => 'text',
				'size' => '8',
				'maxlength' => '8',
				'class' => 'require',
				'value' => $this->form_validation->set_value('sg_acct_num'),
			);
			
			$this->data['phone1'] = array('name' => 'phone1',
				'id' => 'phone1',
				'class' => 'require',
				'type' => 'text',
				'size' => '3',
				'maxlength' => '3',
				'value' => $this->form_validation->set_value('phone1'),
			);
			$this->data['phone2'] = array('name' => 'phone2',
				'id' => 'phone2',
				'class' => 'require',
				'type' => 'text',
				'size' => '3',
				'maxlength' => '3',
				'value' => $this->form_validation->set_value('phone2'),
			);
			$this->data['phone3'] = array('name' => 'phone3',
				'id' => 'phone3',
				'class' => 'require',
				'type' => 'text',
				'size' => '4',
				'maxlength' => '4',
				'value' => $this->form_validation->set_value('phone3'),
			);
			$this->data['best_time'] = array('name' => 'best_time',
				'id' => 'best_time',
				'class' => 'require',
				'type' => 'text',
				'size' => '10',
				'maxlength' => '10',
				'value' => $this->form_validation->set_value('best_time')
			);
			$this->data['password'] = array('name' => 'password',
				'id' => 'password',
				'type' => 'password',
				'value' => $this->form_validation->set_value('password'),
				'class' => 'required'
			);
			$this->data['password_confirm'] = array('name' => 'password_confirm',
				'id' => 'password_confirm',
				'type' => 'password',
				'value' => $this->form_validation->set_value('password_confirm'),
				'class' => 'required'
			);
			if(is_array($this->page_header_data)){
				$this->page_header_data = array_merge($this->page_header_data,
					array(
						'title'=>'Register User - ' . $this->config->item('product_name'),
						'description'=>'Register user for ' . $this->config->item('product_name'),
						'arr_headjs_line'=>array(
							'{popup: "' . $this->config->item("base_url_assets") . 'js/jquery/popup.min.js"}',
						)
					)
				);
			}
			$this->carabiner->css('popup.css');
			$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
			$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
			$this->data['page_heading'] = 'Register User';
			$footer_data = array(
				'arr_deferred_js'=>array(
					$this->config->item('base_url_assets') . 'js/gs_auth_form_helper.js',
				)
			);
			$this->data['page_footer'] = $this->load->view('page_footer', $footer_data, TRUE);
			$this->data['cs_phone'] = $this->config->item('cust_serv_phone');
			$this->data['base_url_assets'] = $this->config->item("base_url_assets");
			$this->load->view('auth/create_user', $this->data);
		}
	}

	function edit_user($user_id = FALSE) {
		if($user_id === FALSE) $user_id = $this->session->userdata('user_id');
		//does the logged in user have permission to edit this user?
		if (!$this->as_ion_auth->logged_in()) {
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
			redirect(site_url('auth'), 'refresh');
        }
        if(!$this->as_ion_auth->is_editable_user($user_id, $this->session->userdata('user_id'))){
        	$this->session->set_flashdata('message', "You do not have permission to edit the requested account.");
        	redirect(site_url());
        }

        $this->data['title'] = "Edit Account";
		//validate form input
		$this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
		$this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
		$this->form_validation->set_rules('email', 'Email Address', 'trim|required|valid_email');
		$this->form_validation->set_rules('supervisor_acct_num', 'Field Technician Number', 'exact_length[8]');
		$this->form_validation->set_rules('assoc_acct_num[]', 'Association/Region Account Number', 'exact_length[8]');
		$this->form_validation->set_rules('phone1', 'First Part of Phone', 'exact_length[3]|required');
		$this->form_validation->set_rules('phone2', 'Second Part of Phone', 'exact_length[3]|required');
		$this->form_validation->set_rules('phone3', 'Third Part of Phone', 'exact_length[4]|required');
		$this->form_validation->set_rules('best_time', 'Best Time to Call', 'max_length[10]');
		$this->form_validation->set_rules('password', 'Password', 'trim|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]');
		$this->form_validation->set_rules('password_confirm', 'Password Confirmation', 'trim');
		$this->form_validation->set_rules('group_id[]', 'Name of Account Group');
		//$this->form_validation->set_rules('herd_code', 'Herd Code', 'exact_length[8]');
		$this->form_validation->set_rules('section_id[]', 'Section');
		
		$email_in = $this->input->post('email');
		$is_submitted = empty($email_in) ? FALSE : TRUE;
		$is_validated = $this->form_validation->run();
		if ($is_validated === TRUE) {
			//populate data fields for specific group choices
			//start with the minimum
			$user_id = $this->input->post('user_id');
			$arr_posted_group_id = FALSE;
			$assoc_acct_num = NULL;
			$supervisor_acct_num = NULL;
			
			//Set variables that depend on group(s) selected
			if($this->as_ion_auth->has_permission("Edit All Users") || $this->as_ion_auth->has_permission("Edit Users In Region")){
				$arr_posted_group_id = $this->input->post('group_id');
				if(!$this->as_ion_auth->group_assignable($arr_posted_group_id)){
					$this->session->set_flashdata('message', 'You do not have permissions to edit a user with the user group you selected.  Please try again, or contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . '.');
					redirect(site_url("auth/edit_user/$user_id"), 'refresh');
					exit();
				}
				$assoc_acct_num = $this->input->post('assoc_acct_num');
				$supervisor_acct_num = $this->input->post('supervisor_acct_num');
			}

			$obj_user = $this->ion_auth_model->user($user_id)->row();
			/*if($this->input->post('herd_code') && $this->input->post('herd_code') != $obj_user->herd_code){
				$herd_code = $this->input->post('herd_code') ? $this->input->post('herd_code') : NULL;
				$herd_release_code = $this->input->post('herd_release_code');
				$error = $this->herd_model->herd_authorization_error($herd_code, $herd_release_code);
				if($error){
					$this->as_ion_auth->set_error($error);
					$is_validated = false;
				}
			}*/
			
			//populate
			$username = substr(strtolower($this->input->post('first_name')) . ' ' . strtolower($this->input->post('last_name')),0,15);
			$email = $this->input->post('email');
			$data = array('username' => $username,
				'email' => $email,
				'first_name' => $this->input->post('first_name'),
				'last_name' => $this->input->post('last_name'),
				'phone' => $this->input->post('phone1') . '-' . $this->input->post('phone2') . '-' . $this->input->post('phone3'),
				'best_time' => $this->input->post('best_time'),
				'group_id' => $arr_posted_group_id,
				'supervisor_acct_num' => $supervisor_acct_num,
				'assoc_acct_num' => $assoc_acct_num,
				'herd_code' => $this->input->post('herd_code') ? $this->input->post('herd_code') : NULL
			);
			if($data['phone'] == '--') $data['phone'] = '';
			if(isset($_POST['section_id'])) $data['section_id'] = $this->input->post('section_id');
			$password = $this->input->post('password');
			if(!empty($password)) $data['password'] = $password;
		}
		$arr_curr_group_ids = array_keys($this->session->userdata('arr_groups'));
		if ($is_validated === TRUE && $this->ion_auth_model->update($user_id, $data, $this->session->userdata('active_group_id'), $arr_curr_group_ids)) { //check to see if we are creating the user
			$this->session->set_flashdata('message', "Account Edited");
			redirect(site_url("auth"), 'refresh');
		}
		else { //display the edit user form
			if(isset($obj_user) === FALSE) $obj_user = $this->ion_auth_model->user($user_id)->row();
			$this->page_header_data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());

			$this->data['first_name'] = array('name' => 'first_name',
				'id' => 'first_name',
				'type' => 'text',
				'size' => '25',
				'maxlength' => '50',
				'value' => $this->form_validation->set_value('first_name', $obj_user->first_name),
				'class' => 'require'
			);
			$this->data['last_name'] = array('name' => 'last_name',
				'id' => 'last_name',
				'type' => 'text',
				'size' => '25',
				'maxlength' => '50',
				'value' => $this->form_validation->set_value('last_name', $obj_user->last_name),
				'class' => 'require'
			);
			$this->data['email'] = array('name' => 'email',
				'id' => 'email',
				'type' => 'text',
				'size' => '50',
				'maxlength' => '100',
				'value' => $this->form_validation->set_value('email', $obj_user->email),
				'class' => 'require'
			);
			$obj_user->arr_groups = array_keys($this->ion_auth_model->get_users_group_array($obj_user->id));
			if(empty($obj_user->arr_groups)){ //if no group is set, set the default group
				$obj_user->arr_groups = array($this->config->item('default_group_id', 'ion_auth'));
			}

			$arr_form_group_id = $this->form_validation->set_value('group_id[]', $obj_user->arr_groups);
			$this->data['group_selected'] = $arr_form_group_id;
			$this->data['group_options'] = $this->as_ion_auth->get_group_dropdown_data($this->session->userdata('active_group_id'));
			if(count($this->data['group_options']) > 1){ // if $this->data['group_id'] is not set, the field will not appear on the form
				$this->data['group_id'] = 'id="group_id" class = "require"';
				if($this->as_ion_auth->has_permission("Edit All Users") || $this->as_ion_auth->has_permission("Edit Users In Region")){
					$this->data['group_id'] .= ' multiple size="4"';
				}
			}
			if($this->as_ion_auth->has_permission("Edit All Users")){
				$this->data['assoc_acct_options'] = $this->as_ion_auth->get_assoc_dropdown_data(array_keys($this->session->userdata('arr_regions')));
				$this->data['assoc_acct_selected'] = $this->form_validation->set_value('assoc_acct_num[]', !empty($obj_user->assoc_acct_num) ? $obj_user->assoc_acct_num : array_keys($this->session->userdata('arr_regions')));
				$this->data['assoc_acct_num'] = 'class = "require"';
			}
			elseif($this->as_ion_auth->has_permission("Edit Users In Region")){
				$this->data['assoc_acct_num'] = array('name' => 'assoc_acct_num[]',
						'id' => 'assoc_acct_num',
						'type' => 'hidden',
						'class' => 'require',
						'value' => $this->form_validation->set_value('assoc_acct_num', !empty($obj_user->assoc_acct_num) ? $obj_user->assoc_acct_num : $this->session->userdata('assoc_acct_num'))
				);
			}
				
			if($this->as_ion_auth->has_permission("Edit Users In Region") || $this->as_ion_auth->has_permission("Edit All Users")){
				$arr_form_assoc_acct_num = $this->form_validation->set_value('assoc_acct_num[]', !empty($obj_user->assoc_acct_num) ? $obj_user->assoc_acct_num : array_keys($this->session->userdata('arr_regions')));

				$arr_tech_obj = $this->ion_auth_model->get_dhi_supervisor_acct_nums_by_association($arr_form_assoc_acct_num);
				$this->data['supervisor_acct_num_options'] = !empty($arr_form_assoc_acct_num)?$this->as_ion_auth->get_dhi_supervisor_dropdown_data($arr_tech_obj):array();
				$this->data['supervisor_acct_num_selected'] = $this->form_validation->set_value('supervisor_acct_num', !empty($obj_user->supervisor_acct_num) ? $obj_user->supervisor_acct_num : $this->session->userdata('supervisor_acct_num'));
				$this->data['supervisor_acct_num'] = 'class = "require"';
				$obj_user->section_id = $this->as_ion_auth->set_form_array($this->web_content_model->get_subscribed_sections_array($obj_user->arr_groups, $user_id, $this->as_ion_auth->super_section_id), 'id', 'id'); // populate array of sections for which user is enrolled
				$arr_form_section_id = $this->form_validation->set_value('section_id[]', $obj_user->section_id);
				$this->data['section_selected'] = $arr_form_section_id;
			}
			if($this->as_ion_auth->has_permission("Assign Sections")){
				$this->data['section_id'] = 'id="section_id"';
				$this->data['section_options'] = $this->web_content_model->get_keyed_section_array(array('subscription'));
				$this->data['section_selected'] = $this->form_validation->set_value('section_id[]', $obj_user->section_id);
			}

			/*if($this->as_ion_auth->has_permission("Edit Users In Region") || $this->as_ion_auth->has_permission("Edit All Users")){
				$this->data['herd_code'] = array('name' => 'herd_code',
					'id' => 'herd_code',
					'type' => 'text',
					'size' => '8',
					'maxlength' => '8',
					'value' => $this->form_validation->set_value('herd_code', $obj_user->herd_code)
				);
				if(in_array('2', $arr_form_group_id)) $this->data['herd_code']['class'] = 'require';
			} */
			//more general info
			$phone1 = '';
			$phone2 = '';
			$phone3 = '';
			if(!$is_submitted && !empty($obj_user->phone)){
				$arr_phone = explode('-', $obj_user->phone);
				if(count($arr_phone) == 3) list($phone1, $phone2, $phone3) = explode('-', $obj_user->phone);
			}
			$this->data['phone1'] = array('name' => 'phone1',
				'id' => 'phone1',
				'class' => 'require',
				'type' => 'text',
				'size' => '3',
				'maxlength' => '3',
				'value' => $this->form_validation->set_value('phone1', $phone1)
			);
			$this->data['phone2'] = array('name' => 'phone2',
				'id' => 'phone2',
				'class' => 'require',
				'type' => 'text',
				'size' => '3',
				'maxlength' => '3',
				'value' => $this->form_validation->set_value('phone2', $phone2)
			);
			$this->data['phone3'] = array('name' => 'phone3',
				'id' => 'phone3',
				'class' => 'require',
				'type' => 'text',
				'size' => '4',
				'maxlength' => '4',
				'value' => $this->form_validation->set_value('phone3', $phone3)
			);
			$this->data['best_time'] = array('name' => 'best_time',
				'id' => 'best_time',
				'class' => 'require',
				'type' => 'text',
				'size' => '10',
				'maxlength' => '10',
				'value' => $this->form_validation->set_value('best_time', $obj_user->best_time)
			);
			$this->data['user_id'] = array('name' => 'user_id',
				'id' => 'user_id',
				'type' => 'hidden',
				'value' => $user_id
			);
			$this->data['password'] = array('name' => 'password',
				'id' => 'password',
				'type' => 'password',
				'value' => $this->form_validation->set_value('password')
			);
			$this->data['password_confirm'] = array('name' => 'password_confirm',
				'id' => 'password_confirm',
				'type' => 'password',
				'value' => $this->form_validation->set_value('password_confirm')
			);
			if(is_array($this->page_header_data)){
				$this->page_header_data = array_merge($this->page_header_data,
					array(
						'title'=>'Edit User - ' . $this->config->item('product_name'),
						'description'=>'Edit user for ' . $this->config->item('product_name')
					)
				);
			}
			$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
			$this->footer_data = array(
				'arr_deferred_js'=>array(
					$this->config->item('base_url_assets') . 'js/gs_auth_form_helper.js',
				)
			);
			$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, true);
			$this->data['page_heading'] = 'Edit User';
			$this->data['page_footer'] = $this->load->view('page_footer', $this->footer_data, true);
			$this->load->view('auth/edit_user', $this->data);
		}
	}
		
	function ajax_techs($assoc_acct_num){
		header('Content-type: application/json');
		$arr_tech_obj = $this->ion_auth_model->get_dhi_supervisor_acct_nums_by_association($assoc_acct_num);
		$supervisor_acct_num_options = $this->as_ion_auth->get_dhi_supervisor_dropdown_data($arr_tech_obj);
		$return_val = json_encode($supervisor_acct_num_options);
		echo $return_val;
		exit();
	}
	
	function ajax_terms(){
		$this->load->view('auth/terms', array());
	}
	
	function set_role($group_id){
		if(array_key_exists($group_id, $this->session->userdata('arr_groups'))){
			$this->session->set_userdata('active_group_id', (int)$group_id);
		}
		else {
			$this->session->set_flashdata('message', "You do not have rights to the requested group.");
		}
		redirect(site_url($this->redirect_url));
	}
	
	// used with dashboard graph
	function graph_snapshot($report, $type = NULL) {
		$this->load->helper('report_chart_helper');
		$graph['config'] = array(
			'xAxis' => array(
				'title' => array(
					'text' => 'Days in Milk, Current Lactation'
				)
			),
			'yAxis' => array(
				0 => array(
					'title' => array(
						'text' => 'Avg Linear Score'
					)
				),
				1 => array(
					'title' => array(
						'text' => 'Net Merit Amt'
					),
					'opposite' => "TRUE"
				)
			),
			'series' => array(
				0 => array(
					'name' => 'Avg Linear Score',
					'yAxis' => '0'
				),
				1=> array(
					'name' => 'Net Merit Amt',
					'yAxis' => '1'
				)
			),
			'title' => array(
				'text' => 'Lactation Graph 3'
			),
			'subtitle' => array(
				'text' => 'Avg Linear Score and Net Merit'
			),
		);
		$this->load->library('chart');
		$graph['data'] = $this->chart->get_sample_graph_data('35991623', 'M');

		$return_val = prep_output($output, $graph, $report_count, $return_output);
		if($return_val) return $return_val;

/*	    if ($type == 'ajax') // load inline view for call from ajax
	        $this->load->view('data', $graph);

	    else if ($type == 'chart') {
			// Set the JSON header
			header("Content-type: text/javascript");
			$graph['config']['chart']['renderTo'] = '';
			$return_val = 'process_chart(' . json_encode($graph) . ');';
	    	echo $return_val;
	    	//echo $this->cow_heifer . ' - ' . $this->herd_code . ' - ' . $this->animal_model;
	    	exit;
	    }

	    else // load the default view
	        var_dump($graph);
	    	//$this->load->view('default', $graph);
*/	}
	protected function _record_access($event_id){
		if($this->session->userdata('user_id') === FALSE){
			return FALSE;
		}
		$herd_code = $this->session->userdata('herd_code');
		$herd_enroll_status_id = empty($herd_code) ? NULL : $this->session->userdata('herd_enroll_status_id');
		$recent_test = $this->session->userdata('recent_test_date');
		$recent_test = empty($recent_test) ? NULL : $recent_test;
		
		$this->access_log->write_entry(
			$this->as_ion_auth->is_admin(),
			$event_id,
			$herd_code,
			$recent_test,
			$herd_enroll_status_id,
			$this->session->userdata('user_id'),
			$this->session->userdata('active_group_id')
		);
	}
}
