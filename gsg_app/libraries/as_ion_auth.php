<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'libraries/dhi/Herd.php';
require_once APPPATH . 'libraries/Ion_auth.php';
require_once APPPATH . 'libraries/AccessLog.php';
require_once APPPATH . 'libraries/Site/WebContent/Sections.php';
require_once APPPATH . 'libraries/Site/WebContent/Pages.php';
require_once APPPATH . 'libraries/Site/WebContent/Blocks.php';
require_once(APPPATH . 'libraries/dhi/HerdAccess.php');

use \myagsource\AccessLog;
use \myagsource\Site\WebContent\Sections;
use \myagsource\Site\WebContent\Pages;
use \myagsource\Site\WebContent\Blocks;
use \myagsource\dhi\Herd;
use \myagsource\dhi\HerdAccess;

/**
* Name:  AS Ion Auth
* Author: ctranel
* Description:  Extends Ben Edmunds Ion Auth library.
* Requirements: PHP5.3 or above
*/

class As_ion_auth extends Ion_auth {
	/**
	 * is_admin
	 *
	 * @var boolean
	 **/
	public $is_admin;

	/**
	 * referrer
	 *
	 * @var string
	 **/
	public $referrer;

	/**
	 * arr_groups
	 *
	 * @var array id=>name
	 **/
//	public $arr_groups;

	/**
	 * arr_task_permissions
	 *
	 * @var array
	 **/
	protected $arr_task_permissions;

	/**
	 * top_sections
	 *
	 * @var SplObjectStorage
	 **/
	public $top_sections;

	/**
	 * user_sections
	 *
	 * @var array
	 **/
	public $user_sections;

	/**
	 * gsg_access
	 *
	 * @var string
	 **/
	public $gsg_access;
	
	/**
	 * herd_access
	 *
	 * @var HerdAccess
	 **/
	protected $herd_access; //object

	public function __construct(){
		//@todo: get most of this into a controller
		//if (!$this->logged_in() && get_cookie('identity') && get_cookie('remember_code')){
			//$this->arr_task_permissions = $this->ion_auth_model->getTaskPermissions('3');
		//}
		parent::__construct();
//		$this->load->model('access_log_model');
//		$this->access_log = new AccessLog($this->access_log_model);
		$this->load->model('web_content/section_model');
		$this->load->model('web_content/page_model', null, false, $this->session->userdata('user_id'));
		$this->load->model('web_content/block_model');
		$this->load->model('dhi/region_model');
		$this->load->model('dhi/herd_model');
		$this->load->helper('url');
		$this->herd_access = new HerdAccess($this->herd_model);
		$blocks = new Blocks($this->block_model);
		$pages = new Pages($this->page_model, $blocks);
		$sections = new Sections($this->section_model, $pages);
		$herd = new Herd($this->herd_model, $this->session->userdata('herd_code'));
		
		$tmp_uri= $this->uri->uri_string();

		if($this->logged_in()){
			$this->arr_task_permissions = $this->ion_auth_model->getTaskPermissions();
			//RETRIEVE NAVIGATION DATA
			if(strpos($tmp_uri, 'ajax') === FALSE && strpos($tmp_uri, '/csv/') === FALSE && strpos($tmp_uri, '/demo') === false){//set supersection if there is one
				$section_path = $this->router->fetch_class(); //this should match the name of this file (minus ".php".  Also used as base for css and js file names and model directory name
				$control_dir = $this->router->fetch_directory();
				//all sections must have directories in the main controller directory
				if($control_dir != $section_path && !empty($control_dir)){
					$active_section = $sections->getByPath($control_dir);
					$sections->loadChildren($active_section, $pages, $this->session->userdata('user_id'), $herd, $this->arr_task_permissions);
				} 
				
				/* will be used if we allow producers to specify consultant access by section
				$sections_with_permission = [];
				if($this->has_permission('View Assign w permission')){
					$sections_with_permission = $this->ion_auth_model->sectionsWithPermission($this->session->userdata('user_id'), $this->session->userdata('herd_code'));
				}
				*/
				//@todo: hard-coded "dhi/" path will not work when we add soil labs or any division other than DHI.  Will need the division user is in.
				$dhi_section = $sections->getByPath('dhi/');
				$sections->loadChildren($dhi_section, $pages, $this->session->userdata('user_id'), $herd, $this->arr_task_permissions);
				$this->top_sections = $dhi_section->children();
				if(isset($active_section)){
					$this->user_sections = $active_section->children();
				}
			}
		}

		//reliably set the referrer, used when determining whether to set the redirect variable on pages like select herd
		$this->referrer = $this->session->userdata('referrer');

		if($this->session->userdata('referrer') != $tmp_uri && strpos($tmp_uri, 'ajax') === false && strpos($tmp_uri, '/csv/') === false && strpos($tmp_uri, '/demo') === false){
			$this->session->set_userdata('referrer', $tmp_uri);
		}
		
		$this->is_admin = $this->is_admin();
	}
	
	public function arr_task_permissions(){
		return $this->arr_task_permissions;
	}
	
	//overridden functions below
	/**
	 * @method register
	 *
	 * @return boolean/void
	 * @author ctranel
	 **/
	public function register($username, $password, $email, $additional_data = array(), $group_name = array()) {
		$id = parent::register($username, $password, $email, $additional_data, $group_name);
		$herd_code = $additional_data['herd_code'];
		
		$this->load->model('dhi/tech_model');
		
		if($id && isset($herd_code) && !empty($herd_code)){
			$data = array(
				'email'     => $email,
				'herd_code'	=> $herd_code,
				'phone'		=> $additional_data['phone'],
				'best_time'	=> $additional_data['best_time'],
				'arr_herd'	=> $this->herd_model->get_herd($herd_code),
				'arr_tech'	=> $this->tech_model->get_tech_by_herd($herd_code),
			);
			if(!$this->config->item('use_ci_email', 'ion_auth')) {
				return $data;
			}
			else {
				$message = $this->load->view($this->config->item('email_templates', 'ion_auth').$this->config->item('user_herd_data', 'ion_auth'), $data, true);
				$this->email->clear();
				$this->email->from($this->config->item('admin_email'), $this->config->item('site_title'));
				$this->email->to($this->config->item('cust_serv_email'));
				$this->email->subject($this->config->item('site_title') . ' - Account Activation Info - ' . $additional_data['last_name']);
//				$this->email->subject($this->config->item('site_title') . ' - Account Activation Info');
				$this->email->message($message);
				$this->email->send();
			}
		}
		return $id;
	}
	
	
	/**
	 * @method logout
	 *
	 * @return boolean/void
	 * @author ctranel
	 **/
	public function logout()
	{
		$this->session->unset_userdata('arr_groups');
		$this->session->unset_userdata('active_group_id');
		$this->session->unset_userdata('herd_code');
//		$this->session->unset_userdata('arr_pstring');
		$this->session->unset_userdata('herd_code');
		$this->session->unset_userdata('herd_code');
		$this->session->unset_userdata('first_name');
		$this->session->unset_userdata('last_name');
//		$this->session->unset_userdata('company');
		$this->session->unset_userdata('phone');
		$this->session->unset_userdata('section_id');
		$this->session->unset_userdata('access_level');
//		$this->session->unset_userdata('pstring');
//		$this->session->unset_userdata('herd_size_code');
//		$this->session->unset_userdata('all_breeds_code');

		return parent::logout();
	}

	/**
	 * @method is_admin
	 *
	 * @return bool
	 * @author ctranel
	 **/
	public function is_admin() {
		return $this->session->userdata('active_group_id') === $this->config->item('admin_group', 'ion_auth');
	}

	/**
	 * @method is_editable_user()
	 * @param int user id
	 * @return boolean
	 * @access public
	 *
	 **/
	public function is_editable_user($user_id, $logged_user_id){
		$obj_user = $this->ion_auth_model->user($user_id)->row();
		//$obj_user->arr_groups = array_keys($this->ion_auth_model->get_users_group_array($obj_user->id));
		if($this->has_permission('Edit All Users')){
			return TRUE;
		}
		if($this->has_permission('Edit Users In Region')){
			return ($this->ion_auth_model->is_child_user_by_association($obj_user->assoc_acct_num, $user_id));
		}
		if($this->has_permission('Edit Producers In Region')){
			return $this->ion_auth_model->is_child_user_by_group_and_association(2, $obj_user->assoc_acct_num, $user_id);
		}
		if($this->as_ion_auth->has_permission("Edit Own Account") && $user_id == $logged_user_id){
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * @method get_editable_users()
	 * @return array of child user objects
	 * @access public
	 *
	 **/
	public function get_editable_users(){
		$obj_user = $this->ion_auth_model->user($this->session->userdata('user_id'))->row();
		//$obj_user->arr_groups = array_keys($this->ion_auth_model->get_users_group_array($obj_user->id));
		
		if($this->has_permission('Edit All Users')){
			return $this->ion_auth_model->users()->result_array();
		}
		if($this->has_permission('Edit Users In Region')){
			$arr_tmp2 = $this->ion_auth_model->get_users_by_association($obj_user->assoc_acct_num)->result_array();
			return $arr_tmp2;
		}
		if($this->has_permission('Edit Producers In Region')){
			return $this->ion_auth_model->get_users_by_group_and_association(2, $obj_user->assoc_acct_num)->result_array();
		}
		else { // Genex groups or producers
			return array();
		}
	}

	/**
	 * @method get_viewable_herds()
	 * @param int user_id
	 * @param array of region acct_num=>name
	 * @return mixed array of herds or boolean
	 * @access public
	 *
	 **/
	public function get_viewable_herds($user_id, $arr_regions = false, $limit_in = NULL){
		$arr_return_reg = array();
		$arr_return_user = array();
		$arr_return_permission = array();
		if($this->has_permission('View All Herds')){
			return $this->herd_model->getHerds();
		}
		
		if($this->has_permission('View Herds In Region')){
			if(!isset($arr_regions) || !is_array($arr_regions)){
				return FALSE;
			}
			//extract region number from account number
			$arr_region_nums = array();
			foreach($arr_regions AS $k => $v){
				$arr_region_nums[] = substr($k, -3);
			}
			$arr_return_reg = $this->herd_model->getHerdsByRegion($arr_region_nums, $limit_in);
		}
		if($this->has_permission('View Assigned Herds')){
			$arr_return_user = $this->herd_model->getHerdsByUser($user_id, $limit_in);
		}
		if($this->has_permission('View Assign w permission')){
			$arr_return_permission = $this->herd_model->getHerdsByPermissionGranted($user_id, $limit_in);
		}
		return array_merge($arr_return_reg, $arr_return_user, $arr_return_permission);
	}

	

	/**
	 * @method get_group_dropdown_data()
	 * @param int active group id
	 * @return array (key=>value) array of groups for populating options lists
	 * @access public
	 * 
	 *
	 **/
	public function get_group_dropdown_data($active_group_id){
		$arr_groups = array();
		$ret_array = array();
		if($this->has_permission('Add All Users')){
			$arr_groups = $this->ion_auth_model->get_active_groups()->result_array();
		}
		elseif($this->logged_in()){
			if($this->has_permission('Add Users In Region')){
				$arr_groups = $this->ion_auth_model->get_editable_groups($active_group_id);
			}
		}
		if(empty($arr_groups)){
			$arr_groups[] = array('id'=>'2', 'name'=>'Producer');
			$arr_groups[] = array('id'=>'9', 'name'=>'Consultant');
		}
		if(is_array($arr_groups)) {
			$ret_array[''] = "Select one";
			foreach($arr_groups as $g){
				$ret_array[$g['id']] = $g['name'];
			}
			return $ret_array;
		}
		else {
			return false;
		}
	}

	/**
	 * @method get_assoc_dropdown_data()
	 * @param array of association numbers to which the user currently has access (array_keys($this->session->userdata('arr_regions')) in CI)
	 * @return array 1d (key=>value) array of herds for populating options lists
	 * @access public
	 *
	 **/
	public function get_assoc_dropdown_data($arr_users_regions){
		$ret_array = array();
		if($this->has_permission("Edit All Users") || $this->has_permission("Add All Users")){
			$arr_assn_obj = $this->region_model->get_regions();
		}
		else{
			$arr_assn_obj = $this->region_model->get_region_by_field('assoc_acct_num', $arr_users_regions);
		}
		if(is_array($arr_assn_obj)) {
			$ret_array[''] = "Select";
			foreach($arr_assn_obj as $g){
				$ret_array[$g->account_num] = $g->assoc_name;
			}
			return $ret_array;
		}
		elseif(is_object($arr_assn_obj)) {
			return $arr_assn_obj;
		}
		else {
			return false;
		}
	}

	/**
	 * @method get_dhi_supervisor_dropdown_data()
	 * @param array of tech objects
	 * @return array 1d (key=>value) array of herds for populating options lists
	 * @access public
	 *
	 **/
	public function get_dhi_supervisor_dropdown_data($arr_tech_obj){
		if(is_array($arr_tech_obj)) {
			$ret_array[''] = "Select one";
			foreach($arr_tech_obj as $g){
				$ret_array[$g->account_num] = $g->name;
			}
			return $ret_array;
		}
		elseif(is_object($arr_tech_obj)) {
			return $arr_tech_obj;
		}
		else {
			return false;
		}
	}

	/**
	 * @method has_permission
	 * @param string task name
	 * @return boolean
	 * @access public
	 *
	 **/
	public function has_permission($task_name){
		$tmp_array = $this->arr_task_permissions;
		if(is_array($tmp_array) !== FALSE) return in_array($task_name, $tmp_array);
		else return FALSE;
	}

/**
	 * @method record_section_inquiry()
	 * @param array of section names
	 * @param string comments
	 * @return boolean success
	 * @access public
	 *
	 **/
	public function record_section_inquiry($arr_sections_in, $comments){
		//send emails
		$data['sections'] = $arr_sections_in;
		$data['comments'] = $comments;
		$data['name'] = $this->session->userdata('first_name') . ' ' . $this->session->userdata('last_name');
		$data['email'] = $this->session->userdata('email');
		$herd_code = $this->session->userdata('herd_code');
		if(isset($herd_code) && !empty($herd_code)) $data['herd_code'] = $this->session->userdata('herd_code');

		$message = $this->load->view('auth/email/section_request.php', $data, true);
		$this->email->clear();
		$config['mailtype'] = $this->config->item('email_type', 'ion_auth');
		$this->email->initialize($config);
		$this->email->set_newline("\r\n");
		$this->email->from($this->config->item('admin_email'), $this->config->item('site_title'));
		$this->email->to($data['email']);
		$this->email->cc($this->config->item('cust_serv_email'));
		$this->email->subject($this->config->item('site_title') . ' - Product Inquiry');
		$this->email->message($message);

		if ($this->email->send()){
		//record in database?
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @method set_form_array()
	 * @param array of db result arrays
	 * @param string value field name
	 * @param string text field name
	 * @return array 1d (key=>value) array of herds for populating options lists
	 * @access public
	 *
	 **/
	public function set_form_array($array_in, $value_in, $text_in, $prepend_select = TRUE){
		$new_array = $prepend_select ? array(''=>'Select one') : array();
		if(is_array($array_in)) array_walk($array_in, create_function ('$value, $key, $obj', '$obj["obj_in"][$value[$obj["value_in"]]] = $value[$obj["text_in"]];'), array('obj_in' => &$new_array, 'value_in' => $value_in, 'text_in' => $text_in));
		return $new_array;
	}

	/**
	 * @method group_assignable()
	 * @param int form group id -- group id for which change is being attempted
	 * @return boolean
	 * @access public
	 *
	 **/
	public function group_assignable($arr_form_group_id){
		$session_group_id = $this->session->userdata('active_group_id');
		if($this->logged_in() === FALSE){
			$arr_tmp = array_intersect($arr_form_group_id, array('2', '9', '13')); //if select groups include only 2 and 9 (producer and consultant)
			if(count($arr_tmp) == count($arr_form_group_id)) return TRUE;
			return false;
		}
		if($this->is_admin){
			return TRUE;
		}
		if(in_array($session_group_id, $arr_form_group_id) && count($arr_form_group_id) == 1){
			return TRUE;
		}
		if(!in_array($session_group_id, $arr_form_group_id) && count($arr_form_group_id) == 1
			&& $this->has_permission("Edit All Users") === FALSE && $this->has_permission("Edit Users In Region") === FALSE
			&& $this->has_permission("Add All Users") === FALSE && $this->has_permission("Add Users In Region") === FALSE
		) {
			return FALSE;
		}

		$tmp = $this->ion_auth_model->get_editable_groups($session_group_id);
		//get_editable_groups returns a multidimensional array, need to extract ids
		$arr_child_groups = get_elements_by_key('id', $tmp);
		unset($tmp);
		unset($arr_form_group_id[$session_group_id]);
		$arr_tmp = array_intersect($arr_form_group_id, $arr_child_groups);
		if(count($arr_tmp) == count($arr_form_group_id)) return TRUE;
		return FALSE;
	}

	/**
	 * @method getHerdPermissionsByStatus()
	 * @description does service group account number exist?
	 * @param string service group user_id
	 * @return array
	 * @access public
	 *
	 **/
	public function getHerdPermissionsByStatus($sg_user_id){
		if(empty($sg_user_id)){
			$this->set_error('service_group_required');
			return false;
		}
		$herds = $this->herd_model->getHerdDataByPermissions($sg_user_id);
		if(!isset($herds) || !is_array($herds)){
			return false;
		}
		
		$arr_herds = [];
		foreach($herds as $h){
			$exp_date = new DateTime($h['expires_date']);
			if($h['status'] === 'grant' && $h['expires_date'] !== null && new DateTime($h['expires_date']) < new DateTime()){
				$arr_herds['expired'][] = $h;
			}
			else {
				$arr_herds[$h['status']][] = $h;
			}
		}
		return $arr_herds;
	}
	
	/**
	 * @method getConsultantsByHerd()
	 * @description does service group account number exist?
	 * @param string service group user_id
	 * @return array
	 * @access public
	 *
	 **/
	public function getConsultantsByHerd($herd_code){
		if(empty($herd_code)){
			$this->set_error('herd_code_required');
			return false;
		}
		$sgs = $this->ion_auth_model->getServiceGroupDataByHerd($herd_code);
		if(!isset($sgs) || !is_array($sgs)){
			return false;
		}
		
		$arr_sgs = [];
		foreach($sgs as $h){
			$exp_date = new DateTime($h['exp_date']);
			if($h['request_status'] === 'grant' && $h['exp_date'] !== null && new DateTime($h['exp_date']) < new DateTime()){
				$arr_sgs['expired'][] = $h;
			}
			else {
				$arr_sgs[$h['request_status']][] = $h;
			}
		}
		return $arr_sgs;
	}
	
	/**
	 * @method service_grp_exists()
	 * @description does service group account number exist?
	 * @param string service group account number
	 * @return boolean
	 * @access public
	 *
	 **/
	public function service_grp_exists($sg_acct_num){
		if(empty($sg_acct_num)){
			$this->set_error('service_group_required');
			return FALSE;
		}
		$tmp = $this->ion_auth_model->get_service_group_account($sg_acct_num);
		if(isset($tmp)){
			return TRUE;
		}
		$this->set_error('service_group_not_found');
		return FALSE;
	}
	
	/**
	 * @method allow_service_grp()
	 * @description write consultant-herd relationship record
	 * @param string consultant user id
	 * @param string herd code
	 * @param array sections for which consultant should be give permissions
	 * @return boolean
	 * @access public
	 *
	 **/
	public function allow_service_grp($arr_relationship_data, $arr_section_id) {
		$old_relationship_id = $this->ion_auth_model->get_consult_relationship_id($arr_relationship_data['sg_user_id'], $arr_relationship_data['herd_code']);
		//insert into consulants_herds
		$relationship_id = $this->ion_auth_model->set_consult_relationship($arr_relationship_data, $old_relationship_id);
		//insert each section into consulants_herds_sections
		if($relationship_id !== FALSE){
			$success = $this->ion_auth_model->set_consult_sections($arr_section_id, $relationship_id, $old_relationship_id);
			if($success){
				$this->set_message('consultant_status_update_successful');
				//send e-mail
				$arr_herd_info = $this->herd_model->header_info($this->session->userdata('herd_code'));
				$consultant_info = $this->ion_auth_model->user($arr_relationship_data['sg_user_id'])->result_array();
				$consultant_info = $consultant_info[0];

				if($arr_relationship_data['request_status_id'] == 1) $message = $this->load->view($this->config->item('email_templates', 'ion_auth').$this->config->item('consult_granted', 'ion_auth'), $arr_herd_info, true);
				elseif($arr_relationship_data['request_status_id'] == 2) $message = $this->load->view($this->config->item('email_templates', 'ion_auth').$this->config->item('consult_denied', 'ion_auth'), $arr_herd_info, true);

				if(isset($message)){
					$this->email->clear();
					$this->email->from($this->config->item('admin_email'), $this->config->item('site_title'));
					$this->email->to($consultant_info['email']);
					$this->email->cc($this->session->userdata('email'));
					$this->email->subject($this->config->item('site_title') . ' - Consultant Access');
					$this->email->message($message);

					if ($this->email->send() == TRUE) {
						$this->set_message('consultant_status_email_successful');
						return TRUE;
					}
				}
				$this->set_error('consultant_status_email_unsuccessful');
				return TRUE; // Even if e-mail is not sent, consultant info was recorded
			}
		}
		$this->set_error('consultant_status_update_unsuccessful');
		return FALSE;
	}

	/**
	 * @method service_grp_request()
	 * @abstract write consultant-herd relationship record
	 * @param string herd code
	 * @param int consultant's user id
	 * @param array sections for which consultant should be give permissions
	 * @param date expiration date for access
	 * @return boolean
	 * @access public
	 *
	 **/
	public function service_grp_request($arr_relationship_data, $arr_section_id, $cust_serv_email) {
		$old_relationship_id = $this->ion_auth_model->get_consult_relationship_id($arr_relationship_data['sg_user_id'], $arr_relationship_data['herd_code']);
		//insert into consulants_herds
		$relationship_id = $this->ion_auth_model->set_consult_relationship($arr_relationship_data, $old_relationship_id);
		//insert each section into consulants_herds_sections
		if($relationship_id !== FALSE){
			$success = $this->ion_auth_model->set_consult_sections($arr_section_id, $relationship_id, $old_relationship_id);
			if($success){
				$this->set_message('consultant_request_recorded');
				$this->send_consultant_request($arr_relationship_data, $relationship_id, $cust_serv_email);
				return TRUE; // Even if e-mail is not sent, consultant info was recorded
			}
		}
		return FALSE;
	}
	
	/**
	 * @method send_consultant_request()
	 * @abstract write consultant-herd relationship record
	 * @param string herd code
	 * @param int consultant's user id
	 * @param array sections for which consultant should be give permissions
	 * @param date expiration date for access
	 * @return boolean
	 * @access public
	 * @todo remove "die" before uploading to server
	 **/
	function send_consultant_request($arr_relationship_data, $relationship_id, $cust_serv_email){
		//send e-mail
		$consultant_info = $this->ion_auth_model->user($arr_relationship_data['sg_user_id'])->result_array();
		$consultant_info[0]['relationship_id'] = $relationship_id;
		$email_data = array(
			'id' => $consultant_info[0]['id'],
			'company' => '',//$consultant_info[0]['company'],
			'first_name' => $consultant_info[0]['first_name'],
			'last_name' => $consultant_info[0]['last_name'],
			'herd_code' => $arr_relationship_data['herd_code'],
			'sg_acct_num' => $consultant_info[0]['sg_acct_num'],
		);
		$message = $this->load->view($this->config->item('email_templates', 'ion_auth').$this->config->item('service_grp_request', 'ion_auth'), $email_data, TRUE);
		$arr_herd_emails = $this->herd_model->get_herd_emails($arr_relationship_data['herd_code']);
		//If there are no matching users, send email to customer service
		if(isset($arr_herd_emails) && is_array($arr_herd_emails)){
			$arr_herd_emails = array_flatten($arr_herd_emails);
		}
		else{
			$arr_herd_emails = array($cust_serv_email);
		}

		if(isset($message)){
			$this->email->clear();
			$this->email->from($this->config->item('admin_email'), $this->config->item('site_title'));
			$this->email->to($arr_herd_emails);
			//$this->email->cc($this->session->userdata('email'));
			$this->email->subject($this->config->item('site_title') . ' - Consultant Access');
			$this->email->message($message);

			if ($this->email->send() == TRUE) {
				$this->set_message('consultant_status_email_successful');
				return TRUE;
			}
		}
		$this->set_error('consultant_status_email_unsuccessful');
//die($message);
		return FALSE;
	}
}
