<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . 'libraries/Site/WebContent/Navigation.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');
require_once(APPPATH . 'libraries/dhi/HerdAccess.php');
//require_once(APPPATH . 'libraries/Products/Products/Products.php');
require_once(APPPATH . 'core/MY_Api_Controller.php');

use \myagsource\Site\WebContent\Navigation;
use \myagsource\dhi\Herd;
use \myagsource\dhi\HerdAccess;
//use \myagsource\Products\Products\Products;

class Nav extends MY_Api_Controller {
	
	function __construct(){
		parent::__construct();

		$this->load->model('herd_model');
		$this->herd_access = new HerdAccess($this->herd_model);

		if(!isset($this->as_ion_auth) || !$this->as_ion_auth->logged_in()){
			$this->sendResponse(401);
		}

		//$this->page_header_data['num_herds'] = $this->herd_access->getNumAccessibleHerds($this->session->userdata('user_id'), $this->permissions->permissionsList(), $this->session->userdata('arr_regions'));

		/* Load the profile.php config file if it exists
		if (ENVIRONMENT == 'development' || ENVIRONMENT == 'localhost') {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			} 
		} */
	}

	function index(){
		$this->load->model('web_content/navigation_model');
        $this->load->model('dhi/herd_model');
		$herd = new Herd($this->herd_model, $this->session->userdata('herd_code'));
		$Navigation = new Navigation($this->navigation_model, $herd, $this->permissions->permissionsList());

		echo $Navigation->jsonOutput('DHI');
		exit;
    }
}