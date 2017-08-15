<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . 'libraries/Site/WebContent/Navigation.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');
require_once(APPPATH . 'libraries/dhi/HerdAccess.php');
//require_once(APPPATH . 'libraries/Products/Products/Products.php');
require_once(APPPATH . 'core/MY_Api_Controller.php');

use \myagsource\Site\WebContent\Navigation;
use \myagsource\dhi\Herd;
use \myagsource\dhi\HerdAccess;
use \myagsource\Api\Response\ResponseMessage;
//use \myagsource\Products\Products\Products;

class Nav extends MY_Api_Controller {
	
	function __construct(){
		parent::__construct();

        if(!$this->session->userdata('user_id')) {
            $this->sendResponse(401);
        }
    }

	function index(){
		$this->load->model('web_content/navigation_model');
        $this->load->model('dhi/herd_model');

		$navigation = new Navigation($this->navigation_model,
            $this->herd,
            $this->session->userdata('user_id'),
            $this->permissions->permissionsList(),
            $this->as_ion_auth->get_users_group_array($this->session->userdata('user_id')),
            $this->session->userdata('active_group_id')
        );

		//echo $navigation->jsonOutput('DHI');
        $this->sendResponse(200, null, ['nav'=> $navigation->toArray('DHI')]);

        exit;
    }
}