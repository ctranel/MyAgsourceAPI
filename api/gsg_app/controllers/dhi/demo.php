<?php
//namespace myagsource;
require_once(APPPATH . 'core/MY_Api_Controller.php');

//require_once(APPPATH . 'libraries/Benchmarks/Benchmarks.php');
//require_once(APPPATH . 'libraries/dhi/Herd.php');

//use \myagsource\Benchmarks\Benchmarks;
use \myagsource\dhi\Herd;
use \myagsource\Api\Response\ResponseMessage;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* -----------------------------------------------------------------
 *	CLASS comments
*  @file: demo.php
*  @author: ctranel
*
*  @description: sets session herd to be the default herd and redirects to landing page when navigating to this page.
*
* -----------------------------------------------------------------
*/

class Demo extends MY_Api_Controller {

    function __construct()
    {
        parent::__construct();
    }
	
	function index(){
		//Clear out session
		$this->session->unset_userdata('herd_code');
		$this->session->unset_userdata('arr_pstring');
		$this->session->unset_userdata('pstring');
		$this->session->unset_userdata('arr_tstring');
		$this->session->unset_userdata('tstring');
		
		if($this->as_ion_auth->login('support@myagsource.com', 'AQECTGBUZI', false)){ //if the login is successful
            //$this->_record_access(1); //1 is the page code for login for the user management section
/*
            //get permissions (also in constuctor, put in function/class somewhere)
            $this->load->model('permissions_model');
            $this->load->model('product_model');
            $herd = new Herd($this->herd_model, $this->herd->herdCode());
            $group_permissions = ProgramPermissions::getGroupPermissionsList($this->permissions_model, $this->session->userdata('active_group_id'));
            $products = new Products($this->product_model, $this->herd, $group_permissions);
            $this->permissions = new ProgramPermissions($this->permissions_model, $group_permissions, $products->allHerdProductCodes());
            $msgs = [];
            $msgs[] = new ResponseMessage("This is sample herd data, please login or register to see your herd's data.", 'message');
*/
            //send response
            $this->sendResponse(200, null);
        }

        $this->sendResponse(401, new ResponseMessage($this->as_ion_auth->errors(), 'error'));
	}
/*
	protected function set_herd_session_data(){
		$this->session->set_userdata('herd_code', $this->herd->herdCode());
//		$this->session->set_userdata('pstring', 0);
//		$this->session->set_userdata('arr_pstring', $this->herd_model->get_pstring_array($this->herd->getHerdCode()));
//		$this->session->set_userdata('breed_code', 'HO');
//		$this->session->set_userdata('arr_breeds', $this->herd_model->breedArray($this->herd->getHerdCode()));
		//load new benchmarks
		$this->load->model('setting_form_model');
		$benchmarks_lib = new Benchmarks($this->session->userdata('user_id'), $this->input->post('herd_code'), $this->herd, $this->setting_form_model);
		$this->session->set_userdata('benchmarks', $benchmarks_lib->getSettingKeyValues());
	} */
}
