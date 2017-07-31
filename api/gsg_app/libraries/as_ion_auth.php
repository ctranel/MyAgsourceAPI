<?php 
namespace myagsource;

use myagsource\Permissions\Permissions\ProgramPermissions;

require_once(APPPATH . 'libraries/Ion_auth.php');

//use \myagsource\Ion_auth;

/**
* Name:  AS Ion Auth
* Author: ctranel
* Description:  Extends Ben Edmunds Ion Auth library.
* Requirements: PHP5.3 or above
*/

class As_ion_auth extends \Ion_auth {
	/**
	 * is_admin
	 *
	 * @var boolean
	 **/
	public $is_admin;

	/**
	 * permission
	 *
	 * @var ProgramPermissions
	 **/
	protected $permissions;

	public function __construct($permission, $session){
		//@todo: get most of this into a controller
		//if (!$this->logged_in() && get_cookie('identity') && get_cookie('remember_code')){
			//$this->arr_task_permissions = $this->ion_auth_model->getTaskPermissions('3');
		//}
		parent::__construct($session);

		$this->permissions = $permission;
        $this->is_admin = $this->is_admin();
	}

	//overridden functions below
	/**
	 * @method register
	 *
	 * @return boolean/void
	 * @author ctranel
	 **/
	public function register($username, $password, $email, $additional_data = array(), $group_name = array(), $report_code) {
		$id = parent::register($username, $password, $email, $additional_data, $group_name);

        if(!$id){
            throw new \Exception('Recording of registration failed: ' . implode(', ', $this->errors()));
        }

		$herd_code = $additional_data['herd_code'];

        //if herd is set, insert entry to herd output
        if(isset($herd_code) && !empty($herd_code) && isset($report_code) && !empty($report_code)){
            if(!$this->herd_model->addHerdOutput($herd_code, $report_code)){
                throw new \Exception('No product information included');
            }
        }
		
		$this->load->model('dhi/tech_model');
		
        $data = array(
            'first_name' => $additional_data['first_name'],
            'last_name' => $additional_data['last_name'],
            'group'=> implode(', ', $group_name),
            'email'     => $email,
            'herd_code'	=> $herd_code,
            'arr_herd'	=> $this->herd_model->get_herd($herd_code),
            'arr_tech'	=> $this->tech_model->get_tech_by_herd($herd_code),
        );

        $message = $this->load->view($this->config->item('email_templates', 'ion_auth').$this->config->item('user_herd_data', 'ion_auth'), $data, true);
        $this->email->clear();
        $this->email->from($this->config->item('admin_email'), $this->config->item('site_title'));
        $this->email->to([$this->config->item('field_email')]);
        $this->email->subject($this->config->item('site_title') . ' - Account Activation Info - ' . $additional_data['last_name']);
//				$this->email->subject($this->config->item('site_title') . ' - Account Activation Info');
        $this->email->message($message);
        $this->email->send();

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
		if($this->permissions->hasPermission('Edit All Users')){
			return TRUE;
		}
		if($this->permissions->hasPermission('Edit Users In Region')){
			return ($this->ion_auth_model->is_child_user_by_association($obj_user->assoc_acct_num, $user_id));
		}
		if($this->permissions->hasPermission('Edit Producers In Region')){
			return $this->ion_auth_model->is_child_user_by_group_and_association(2, $obj_user->assoc_acct_num, $user_id);
		}
		if($this->permissions->hasPermission("Edit Own Account") && $user_id == $logged_user_id){
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
		
		if($this->permissions->hasPermission('Edit All Users')){
			return $this->ion_auth_model->users()->result_array();
		}
		if($this->permissions->hasPermission('Edit Users In Region')){
			$arr_tmp2 = $this->ion_auth_model->get_users_by_association($obj_user->assoc_acct_num)->result_array();
			return $arr_tmp2;
		}
		if($this->permissions->hasPermission('Edit Producers In Region')){
			return $this->ion_auth_model->get_users_by_group_and_association(2, $obj_user->assoc_acct_num)->result_array();
		}
		else { // Genex groups or producers
			return array();
		}
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
		if(isset($this->permissions)){
			if($this->permissions->hasPermission('Add All Users')){
				$arr_groups = $this->ion_auth_model->get_active_groups()->result_array();
			}
			elseif($this->logged_in()){
				if($this->permissions->hasPermission('Add Users In Region')){
					$arr_groups = $this->ion_auth_model->get_editable_groups($active_group_id);
				}
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
        $this->load->model('dhi/region_model');
		if($this->permissions->hasPermission("Edit All Users") || $this->permissions->hasPermission("Add All Users")){
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
	 * @method recordProductInquiry()
     * @param string user name
     * @param string user email
     * @param string herd code
	 * @param array of product codes
	 * @param string comments
	 * @return boolean success
	 * @access public
	 *
	 **/
	public function recordProductInquiry($user_name, $user_email, $herd_code, $products_in, $comments){
		//send emails
		$data['products'] = $products_in;
		$data['comments'] = $comments;
		$data['name'] = $user_name;
		$data['email'] = $user_email;
		if(isset($herd_code) && !empty($herd_code)){
            $data['herd_code'] = $herd_code;
        }

		$message = $this->load->view('auth/email/productInfoRequest.tpl.php', $data, true);
		$this->email->clear();
		$config['mailtype'] = $this->config->item('email_type', 'ion_auth');
		$this->email->initialize($config);
		$this->email->set_newline("\r\n");
		$this->email->from($this->config->item('admin_email'), $this->config->item('site_title'));
		$this->email->to($this->config->item('field_email'));
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
			&& $this->permissions->hasPermission("Edit All Users") === FALSE && $this->permissions->hasPermission("Edit Users In Region") === FALSE
			&& $this->permissions->hasPermission("Add All Users") === FALSE && $this->permissions->hasPermission("Add Users In Region") === FALSE
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

}
