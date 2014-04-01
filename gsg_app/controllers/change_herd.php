<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Change_herd extends CI_Controller {
	//protected $herd; //herd object
	
	function __construct(){
		parent::__construct();
		if(!isset($this->as_ion_auth)){
			redirect('auth/login', 'refresh');
		}
		if((!$this->as_ion_auth->logged_in())){
			$redirect_url = set_redirect_url($this->uri->uri_string(), $this->session->flashdata('redirect_url'), $this->as_ion_auth->referrer);
			$this->session->set_flashdata('redirect_url', $redirect_url);
			if(strpos($this->session->flashdata('message'), 'Please log in.') === FALSE){
				$this->session->set_flashdata('message',  $this->session->flashdata('message') . 'Please log in.');
			}
			else{
				$this->session->keep_flashdata('message');
			}
			redirect(site_url('auth/login'));
		}
		$this->page_header_data['user_sections'] = $this->as_ion_auth->arr_user_super_sections;
		/* Load the profile.php config file if it exists */
		if (ENVIRONMENT == 'development') {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			} 
		}
	}

	function index(){
		if($this->as_ion_auth->has_permission("Select Herd")) {
			$this->session->set_flashdata('redirect_url', $redirect_url);
			redirect(site_url('change_herd/select'));
		}
		elseif($this->as_ion_auth->has_permission("Request Herd")) {
			$this->session->set_flashdata('redirect_url', $redirect_url);
			redirect(site_url('change_herd/request'));
		}
		else {
			$this->session->set_flashdata('message', 'You do not have permissions to request herds.');
			redirect(site_url($redirect_url));
		}
	}
/**
 * @method request() - input field to select a herd.
 * 			sets session herd code on successfull submissions.
 * 			Currently used only by Genex .
 *
 * @access	public
 * @return	void
 */
	function request(){
		$tmp_uri = $this->uri->uri_string();
		$redirect_url = set_redirect_url($tmp_uri, $this->session->flashdata('redirect_url'), $this->as_ion_auth->referrer);
		$this->session->set_flashdata('redirect_url', $redirect_url);
		if(empty($redirect_url) && $this->as_ion_auth->referrer !== $tmp_uri) $redirect_url = $tmp_uri;
		if($this->as_ion_auth->has_permission("Select Herd") && !$this->as_ion_auth->has_permission("Request Herd")){
			redirect(site_url('change_herd/select'));
		}
		if(!$this->as_ion_auth->has_permission("Request Herd")){
			$this->session->set_flashdata('message', 'You do not have permissions to request herds.');
			redirect(site_url($redirect_url));
		}

		//validate form input
		$this->load->library('form_validation');
		$this->form_validation->set_rules('herd_code', 'Herd', 'required|exact_length[8]');
		$this->form_validation->set_rules('herd_release_code', 'Herd Release Code', 'required|exact_length[10]');

		$herd_code = $this->input->post('herd_code');
		if(!empty($herd_code)){//if form is submitted
			$herd_release_code = $this->input->post('herd_release_code');
			$error = $this->herd_model->herd_authorization_error($herd_code, $herd_release_code);
			if($error){
				$this->session->set_flashdata('message', 'Invalid data submitted: ' . $error);
				redirect(site_url('change_herd/request'));
			}
		}

		if ($this->form_validation->run() == TRUE) { //if validation is successful
			$this->load->library('herd', array('herd_code' => $this->input->post('herd_code')));
			$this->set_herd_session_data();
			$this->_record_access(2); //2 is the page code for herd change
			redirect(site_url($redirect_url));
		}
		else {  //the user is not logging in so display the login page
			$this->page_header_data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'));
			$this->data['herd_code'] = array('name' => 'herd_code',
				'id' => 'herd_code',
				'type' => 'text',
				'value' => $this->form_validation->set_value('herd_code'),
				'class' => 'require'
			);
			$this->data['herd_release_code'] = array('name' => 'herd_release_code',
				'id' => 'herd_release_code',
				'type' => 'text',
				'value' => $this->form_validation->set_value('herd_release_code'),
				'class' => 'require'
			);
			$this->data['report_path'] = '';
			if(is_array($this->page_header_data)){
				$this->page_header_data = array_merge($this->page_header_data,
					array(
						'title'=>'Request Herd - ' . $this->config->item('product_name'),
						'description'=>'Herd Selection Form for ' . $this->config->item('product_name')
					)
				);
			}
			$arr_redirect_url = explode('/', $redirect_url);

			if(file_exists($arr_redirect_url[0] . FS_SEP . 'section_nav')) $this->page_header_data['section_nav'] = $this->load->view($arr_redirect_url[0] . '/section_nav', NULL, TRUE);
			$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
			$this->data['page_heading'] = 'Request Herd - ' . $this->config->item('product_name');
			$this->data['page_footer'] = $this->load->view('page_footer', NULL, TRUE);

			$this->load->view('herd_request', $this->data);
		}
	}

/**
 * @method select() - option list and input field to select a herd (text field auto-selects options list value).
 * 			sets session herd code on successfull submissions.
 *
 * @access	public
 * @return	void
 */
	function select(){
		$tmp_uri = $this->uri->uri_string();
		$redirect_url = set_redirect_url($tmp_uri, $this->session->flashdata('redirect_url'), $this->as_ion_auth->referrer);
		$this->session->set_flashdata('redirect_url', $redirect_url);
		if(empty($redirect_url) && $this->as_ion_auth->referrer !== $tmp_uri) $redirect_url = $tmp_uri;
		if(!$this->as_ion_auth->has_permission("Select Herd") && $this->as_ion_auth->has_permission("Request Herd")){
			redirect(site_url('change_herd/request'));
			exit();
		}
		if(!$this->as_ion_auth->has_permission("Select Herd")){
			$this->session->set_flashdata('message', 'You do not have permissions to select herds.');
			redirect(site_url($redirect_url));
			exit();
		}
		//validate form input
		$this->load->library('form_validation');
		$this->form_validation->set_rules('herd_code', 'Herd', 'required|max_length[8]');
		//$this->form_validation->set_rules('herd_code_fill', 'Type Herd Code');

		if ($this->form_validation->run() == TRUE) { //successful submission
			$this->load->library('herd', array('herd_code' => $this->input->post('herd_code')));
			$this->set_herd_session_data();
			$this->_record_access(2); //2 is the page code for herd change
			redirect(site_url($redirect_url));
			exit();
		}
		else
		{
			$this->page_header_data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());
			$tmp_arr = $this->as_ion_auth->get_viewable_herds($this->session->userdata('user_id'), $this->session->userdata('arr_regions'));
			if(is_array($tmp_arr)){
				if(count($tmp_arr) == 1){
					$this->load->library('herd', array('herd_code' => $tmp_arr[0]['herd_code']));
					$this->set_herd_session_data();
					redirect(site_url($redirect_url));
					exit();
				}
				$this->load->library('herds');
				$this->data['arr_herd_data'] = $this->herds->set_herd_dropdown_array($tmp_arr);
				unset($tmp_arr);
			}
			else{
				$this->session->set_flashdata('message', 'A list of herds could not be generated for your account.  If you believe this is an error, please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . '.');
				redirect(site_url($redirect_url));
				exit();
			}

			$this->data['herd_code_fill'] = array('name' => 'herd_code_fill',
				'id' => 'herd_code_fill',
				'type' => 'text',
				'size' => '8',
				'maxlength' => '8',
				'value' => $this->form_validation->set_value('herd_code_fill'),
			);


			if(is_array($this->page_header_data)){
				$this->page_header_data = array_merge($this->page_header_data,
					array(
						'title'=>'Select Herd - ' . $this->config->item('product_name'),
						'description'=>'Herd Selection Form for ' . $this->config->item('product_name'),
						'arr_headjs_line'=>array(
							'{datatable: "' . $this->config->item("base_url_assets") . 'js/herd_selection_helper.js"}'
						)
					)
				);
			}
			$this->page_footer_data = array(
			);
			$this->page_header_data['message'] = compose_error(validation_errors(), $this->session->flashdata('message'), $this->as_ion_auth->messages(), $this->as_ion_auth->errors());
			$arr_redirect_url = explode('/', $redirect_url);
			if(file_exists($arr_redirect_url[0] . FS_SEP . 'section_nav')) $this->page_header_data['section_nav'] = $this->load->view($arr_redirect_url[0] . '/section_nav', NULL, TRUE);
			$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
			$this->data['page_heading'] = 'Select Herd - ' . $this->config->item('product_name');
			$this->data['page_footer'] = $this->load->view('page_footer', $this->page_footer_data, TRUE);

			$this->load->view('herd_selection', $this->data);
		} // end ELSE -- form validation failed.
	}
	
	protected function set_herd_session_data(){
//die(var_dump($this->config->item('product_report_code')));
		$this->session->set_userdata('herd_code', $this->herd->getHerdCode());
		$this->session->set_userdata('arr_pstring', $this->herd_model->get_pstring_array($this->herd->getHerdCode(), FALSE));
		$this->session->set_userdata('herd_enroll_status_id', $this->herd->getHerdEnrollStatus($this->herd_model, $this->config->item('product_report_code')));
		$this->session->set_userdata('recent_test_date', $this->herd->getRecentTest($this->herd_model));
	}

	protected function _record_access($event_id){
		$herd_code = $this->session->userdata('herd_code');
		$herd_enroll_status_id = empty($herd_code) ? NULL : $this->session->userdata('herd_enroll_status_id');
		$recent_test = $this->session->userdata('recent_test_date');
		$recent_test = empty($recent_test) ? NULL : $recent_test;
		
		$this->access_log_model->write_entry(
			$event_id,
			$herd_code,
			$recent_test,
			$herd_enroll_status_id,
			$this->session->userdata('user_id'),
			$this->session->userdata('active_group_id')
		);
	}
}
