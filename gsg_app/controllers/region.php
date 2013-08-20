<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Region extends CI_Controller {

	function __construct() {
		parent::__construct();
		if(!isset($this->as_ion_auth)) {
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
			redirect('auth/login', 'refresh');
		}

		if((!$this->as_ion_auth->logged_in())){ //redirect when live
       		$this->session->keep_flashdata('message');
			$this->session->set_flashdata('redirect_url', $this->uri->uri_string());
       		redirect(site_url('auth/login'));
		}

//		$this->as_ion_auth->is_admin = $this->as_ion_auth->is_admin();
//		$this->as_ion_auth->is_manager = $this->as_ion_auth->is_manager();

		$this->page_header_data['user_sections'] = $this->as_ion_auth->arr_user_super_sections;

		if(!$this->as_ion_auth->has_permission("Manage Staff")){
       		$this->session->set_flashdata('message',  $this->session->flashdata('message') . "You do not have permission to edit the requested association/region.");
       		redirect("auth/index", 'refresh');
		}

       	$this->load->library('form_validation');
	}

	function index(){
		//validate form input
		$this->form_validation->set_rules('region_id', 'Association/Region Number', 'exact_length[3]');
		$this->form_validation->set_rules('supervisor_num', 'Field Tech Number', 'exact_length[6]');

		//$tech_in = $this->input->post('supervisor_num');
		$region_in = $this->input->post('region_id');
		if ($this->form_validation->run() == TRUE && (!empty($tech_in) || !empty($region_in))) { //successful submission
			if (!empty($tech_in)) redirect("dhi_supervisor/edit_tech/" . $tech_in, 'refresh');
			else redirect("region/edit_region/" . $region_in, 'refresh'); //use redirects instead of loading views for compatibility with MY_Controller libraries
		}
		else {  // display the login page
			if($this->as_ion_auth->is_admin){
				$this->data['region_options'] = $this->as_ion_auth->get_region_dropdown_data();
				$this->data['region_selected'] = $this->form_validation->set_value('region_id');
				$this->data['region_id'] = 'id="region_id"';
				//$this->data['filter_selected'] = $this->form_validation->set_value('region_filter');
				//$this->data['region_filter'] = 'id="region_filter"';//used on the field tech selection form
				//$this->data['supervisor_options'] = $this->dhi_supervisor_model->get_dropdown_data();
			}
			elseif ($this->as_ion_auth->is_manager) {
				$region_id = $this->session->userdata('region_id');
				redirect("region/edit_region/" . $region_in, 'refresh');
				//$this->data['region_id'] = array('name' => 'region_id',
				//	'id' => 'region_id',
				//	'type' => 'hidden',
				//	'value' => $region_id
				//);
				//$this->data['supervisor_options'] = $this->dhi_supervisor_model->get_dropdown_data($region_id);
			}
			//$this->data['supervisor_selected'] = $this->form_validation->set_value('supervisor_num');
			//$this->data['supervisor_num'] = 'id="supervisor_num"';

			// Set up and display
			if(is_array($this->page_header_data)){
				$this->page_header_data = array_merge($this->page_header_data,
					array(
						'title'=>'Manage Staff - ' . $this->config->item('product_name'),
						'description'=>'Manage Staff - ' . $this->config->item('product_name'),
						'arr_headjs_line'=>array(
							'{customheadjs: "' . $this->config->item("base_url_assets") . 'js/custom-head.min.js"}'
					//	'{techhelper: "' . $this->config->item("base_url_assets") . 'js/gs_tech_selection_helper.js"}'
						)
					)
				);
			}
			$footer_data = array(
				'arr_deferred_js'=>array(
				)
			);
			//$this->data['tech_form'] = $this->load->view('auth/dhi_supervisor/tech_selection', $this->data, TRUE);
			$this->data['message'] = NULL;
			if(validation_errors()) $this->data['message'] .= '<p>' . validation_errors() . '</p>';
			//if(!empty($this->dhi_supervisor_model->error)) $this->data['message'] .= '<p>' . $this->field_tech_model->error . '</p>';
			if($this->session->flashdata('message')) $this->data['message'] .= '<p>' . $this->session->flashdata('message') . '</p>';
			$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
			$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
			$this->data['page_heading'] = 'Manage Staff - ' . $this->config->item('product_name');
			$this->data['page_footer'] = $this->load->view('page_footer', $footer_data, TRUE);

			$this->load->view('auth/region/region_selection', $this->data);
		}
	}

	/**
	 * create region
	 *
	 * @return bool
	 * @author Chris Tranel
	 **/
	public function create_region()
	{
		//validate form input
		$this->form_validation->set_rules('manager_first_name', 'Manager First Name', 'trim|required');
		$this->form_validation->set_rules('manager_last_name', 'Manager Last Name', 'trim|required');
		$this->form_validation->set_rules('email', 'Email Address', 'trim|valid_email');
		$this->form_validation->set_rules('region_id', 'Association/Region Number', 'required');
		$this->form_validation->set_rules('region_name', 'Region Name', 'required|trim|max_length[75]');
		$this->form_validation->set_rules('phone1', 'First Part of Phone', 'exact_length[3]');
		$this->form_validation->set_rules('phone2', 'Second Part of Phone', 'exact_length[3]');
		$this->form_validation->set_rules('phone3', 'Third Part of Phone', 'exact_length[4]');
		//set up data for processing
		$data = array(
			'manager_first_name' => $this->input->post('manager_first_name'),
			'manager_last_name' => $this->input->post('manager_last_name'),
			'region_id' => $this->input->post('region_id'),
			'region_name' => $this->input->post('region_name'),
		);
		if($this->input->post('phone3') != '') $data['phone'] = $this->input->post('phone1') . '-' . $this->input->post('phone2') . '-' . $this->input->post('phone3');
		else $data['phone'] = NULL;
		if($this->input->post('email') != '') $data['email'] = $this->input->post('email');
		else $data['email'] = NULL;
		//check to see if we are creating the region successfully
//if($this->form_validation->run() === FALSE) die('not successful');
//else die('validation successful');
		if ($this->form_validation->run() !== FALSE && $this->region_model->create_region($data)) {
			$this->session->set_flashdata('message', "The association/region has been created.");
			redirect("region", 'refresh');
		}
		else { //display the create user form
			$this->data['manager_first_name'] = array('name' => 'manager_first_name',
				'id' => 'manager_first_name',
				'type' => 'text',
				'value' => $this->form_validation->set_value('manager_first_name'),
				'size' => '25',
				'maxlength' => '50',
				'class' => 'require'
			);
			$this->data['manager_last_name'] = array('name' => 'manager_last_name',
				'id' => 'manager_last_name',
				'type' => 'text',
				'value' => $this->form_validation->set_value('manager_last_name'),
				'size' => '25',
				'maxlength' => '50',
				'class' => 'require'
			);
			$this->data['email'] = array('name' => 'email',
				'id' => 'email',
				'type' => 'text',
				'value' => $this->form_validation->set_value('email'),
				'size' => '50',
				'maxlength' => '100'
			);
			//$this->load->model('dhi_supervisor_model');
			$this->data['region_id'] = array('name' => 'region_id',
				'id' => 'region_id',
				'type' => 'text',
				'class' => 'require',
				'value' => $this->form_validation->set_value('region_id')
			);
			$this->data['region_name'] = array('name' => 'region_name',
				'id' => 'region_name',
				'type' => 'text',
				'size' => '50',
				'maxlength' => '75',
				'value' => $this->form_validation->set_value('region_name'),
				'class' => 'require'
			);
			$this->data['phone1'] = array('name' => 'phone1',
				'id' => 'phone1',
				'type' => 'text',
				'size' => '3',
				'maxlength' => '3',
				'value' => $this->form_validation->set_value('phone1'),
			);
			$this->data['phone2'] = array('name' => 'phone2',
				'id' => 'phone2',
				'type' => 'text',
				'size' => '3',
				'maxlength' => '3',
				'value' => $this->form_validation->set_value('phone2')
			);
			$this->data['phone3'] = array('name' => 'phone3',
				'id' => 'phone3',
				'type' => 'text',
				'size' => '4',
				'maxlength' => '4',
				'value' => $this->form_validation->set_value('phone3')
			);

			// set up and display views
			$this->data['title'] = "Create Region";
			//set the flash data error message if there is one
			$this->data['message'] = NULL;
			if(validation_errors()) $this->data['message'] .= '<p>' . validation_errors() . '</p>';
			if(!empty($this->region_model->error)) $this->data['message'] .= '<p>' . $this->region_model->error . '</p>';
			if($this->session->flashdata('message')) $this->data['message'] .= '<p>' . $this->session->flashdata('message') . '</p>';
			if(is_array($this->page_header_data)){
				$this->page_header_data = array_merge($this->page_header_data,
					array(
						'title'=>'Create Association/Region - ' . $this->config->item('product_name'),
						'description'=>'Create Association/Region - ' . $this->config->item('product_name')
					)
				);
			}
			$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
			$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, true);
			$this->data['page_heading'] = 'Create Association/Region - ' . $this->config->item('product_name');
			$footer_data = array(
				'arr_deferred_js'=>array(
//					$this->config->item('base_url_assets') . 'js/gs_auth_form_helper.js'
				)
			);
			$this->data['page_footer'] = $this->load->view('page_footer', $footer_data, true);
			$this->load->view('auth/region/create_region', $this->data);
		}
	}
	/**
	 * edit region
	 *
	 * @param string - Region to be edited
	 * @return bool
	 * @author Chris Tranel
	 **/
	function edit_region($region_in = NULL) {
		//validate form input
		$this->form_validation->set_rules('region_id', 'Association/Region Number', 'required');
		$this->form_validation->set_rules('manager_first_name', 'Manager First Name', 'trim|required');
		$this->form_validation->set_rules('manager_last_name', 'Manager Last Name', 'trim|required');
		$this->form_validation->set_rules('region_name', 'Region Name', 'trim|max_length[75]');
		$this->form_validation->set_rules('email', 'Email Address', 'trim|valid_email');
		$this->form_validation->set_rules('phone1', 'First Part of Phone', 'exact_length[3]');
		$this->form_validation->set_rules('phone2', 'Second Part of Phone', 'exact_length[3]');
		$this->form_validation->set_rules('phone3', 'Third Part of Phone', 'exact_length[4]');
		$is_validated = $this->form_validation->run();

		//check to see if edit form has been submitted
		$tmp = $this->input->post('manager_first_name');
		$is_submitted = empty($tmp)?FALSE:TRUE;
		//set region as appropriate for group of person logged in
		$region_in = !empty($region_in)?$region_in:$this->input->post('region_id');
		if($this->as_ion_auth->is_manager){
			$region_id = $this->session->userdata('region_id');
		}
		elseif ($this->as_ion_auth->is_admin) {
			$region_id = $region_in;
		}
		$data = array(
			'region_id' => $region_id,
			'manager_first_name' => $this->input->post('manager_first_name'),
			'manager_last_name' => $this->input->post('manager_last_name'),
			'region_name' => $this->input->post('region_name'),
		);
		if($this->input->post('phone3') != '') $data['phone'] = $this->input->post('phone1') . '-' . $this->input->post('phone2') . '-' . $this->input->post('phone3');
		else $data['phone'] = NULL;
		if($this->input->post('email') != '') $data['email'] = $this->input->post('email');
		else $data['email'] = NULL;

			if ($is_validated !== FALSE && $this->region_model->update_region($data)) {
			$this->session->set_flashdata('message', "The Association/Region has been edited");
			redirect("region", 'refresh');
		}
		else { //display the region form if validation fails or no form is submitted
			$obj_region = $this->region_model->get_region_by_field('region_id', $region_id);
			if(!empty($obj_region)) $obj_region = $obj_region[0]; //extract object from array
			else {
				$this->session->set_flashdata('message', "The requested region was not found.");
				redirect("region", 'refresh');
			}

			$this->data['manager_first_name'] = array('name' => 'manager_first_name',
				'id' => 'manager_first_name',
				'type' => 'text',
				'size' => '25',
				'maxlength' => '50',
				'value' => $this->form_validation->set_value('manager_first_name', $obj_region->manager_first_name),
				'class' => 'require'
			);
			$this->data['manager_last_name'] = array('name' => 'manager_last_name',
				'id' => 'manager_last_name',
				'type' => 'text',
				'size' => '25',
				'maxlength' => '50',
				'value' => $this->form_validation->set_value('manager_last_name', $obj_region->manager_last_name),
				'class' => 'require'
			);
			$this->data['email'] = array('name' => 'email',
				'id' => 'email',
				'type' => 'text',
				'size' => '50',
				'maxlength' => '100',
				'value' => $this->form_validation->set_value('email', $obj_region->email)
			);
			if($this->as_ion_auth->is_admin){
				$this->data['region_options'] = $this->region_model->get_dropdown_data();
				$this->data['region_selected'] = $this->form_validation->set_value('region_id', $obj_region->region_id);
				$this->data['region_id'] = 'class = "require"';
			}
			elseif($this->as_ion_auth->is_manager) {
				$this->data['region_id'] = array('name' => 'region_id',
					'id' => 'region_id',
					'type' => 'hidden',
					'class' => 'require',
					'value' => $obj_region->region_id,
				);
			}
			$this->data['region_name'] = array('name' => 'region_name',
				'id' => 'region_name',
				'type' => 'text',
				'size' => '50',
				'maxlength' => '75',
				'value' => $this->form_validation->set_value('region_name', $obj_region->region_name)
			);
			$phone1 = '';
			$phone2 = '';
			$phone3 = '';
			if(!$is_submitted && !empty($obj_region->phone)) list($phone1, $phone2, $phone3) = explode('-', $obj_region->phone);
			$this->data['phone1'] = array('name' => 'phone1',
				'id' => 'phone1',
				'type' => 'text',
				'size' => '3',
				'maxlength' => '3',
				'value' => $this->form_validation->set_value('phone1', $phone1)
			);
			$this->data['phone2'] = array('name' => 'phone2',
				'id' => 'phone2',
				'type' => 'text',
				'size' => '3',
				'maxlength' => '3',
				'value' => $this->form_validation->set_value('phone2', $phone2)
			);
			$this->data['phone3'] = array('name' => 'phone3',
				'id' => 'phone3',
				'type' => 'text',
				'size' => '4',
				'maxlength' => '4',
				'value' => $this->form_validation->set_value('phone3', $phone3)
			);
			// set up and display views
			$this->data['title'] = "Edit Association/Region Information";
			//set the flash data error message if there is one
			$this->data['message'] = NULL;
			if(validation_errors()) $this->data['message'] .= '<p>' . validation_errors() . '</p>';
			if(!empty($this->region_model->error)) $this->data['message'] .= '<p>' . $this->region_model->error . '</p>';
			if($this->session->flashdata('message')) $this->data['message'] .= '<p>' . $this->session->flashdata('message') . '</p>';
			if(is_array($this->page_header_data)){
				$this->page_header_data = array_merge($this->page_header_data,
					array(
						'title'=>'Edit Association/Region',
						'description'=>'Edit Association/Region - ' . $this->config->item('product_name')
					)
				);
			}
			$this->page_header_data['section_nav'] = $this->load->view('auth/section_nav', NULL, TRUE);
			$this->data['page_header'] = $this->load->view('page_header', $this->page_header_data, TRUE);
			$this->data['page_heading'] = 'Edit Association/Region - ' . $this->config->item('product_name');
			$this->data['page_footer'] = $this->load->view('page_footer', NULL, TRUE);
			$this->load->view('auth/region/edit_region', $this->data);
		}
	}
}
