<?php defined('BASEPATH') OR exit('No direct script access allowed');
//require_once(APPPATH . 'libraries/dhi/HerdAccess.php');
require_once(APPPATH . 'core/MY_Api_Controller.php');

//use myagsource\dhi\HerdAccess;
use \myagsource\Api\Response\ResponseMessage;

class HTTP_Error extends MY_Api_Controller {

	function __construct() {
		parent::__construct();

        if(!$this->session->userdata('user_id')) {
            $this->sendResponse(401);
        }

        if(!isset($this->herd)){
            $this->sendResponse(400,  new ResponseMessage('A herd code is required to generate navigation.', 'error'));
        }
	}
	
	function index($failed_url = null) {
		//$this->load->model('herd_model');
		//$herd_access = new HerdAccess($this->herd_model);
		
        $this->sendResponse(404, new ResponseMessage('Sorry, we could not find the information you requested.  
            Please use the navigation to continue.  If you continue to have problems, please <a id="contact_us" href="mailto:support@myagsource.com">click here</a> and
		    describe the problem.', 'error'));
    }
}