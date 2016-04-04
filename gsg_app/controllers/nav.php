<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . 'libraries/Site/WebContent/Navigation.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');
require_once(APPPATH . 'libraries/dhi/HerdAccess.php');
require_once(APPPATH . 'libraries/Products/Products/Products.php');

use \myagsource\Site\WebContent\Navigation;
use \myagsource\dhi\Herd;
use \myagsource\dhi\HerdAccess;
use \myagsource\Products\Products\Products;

class Nav extends MY_Controller {
	
	function __construct(){
		parent::__construct();
		$this->session->keep_all_flashdata();
		
		$this->load->model('herd_model');
		$this->herd_access = new HerdAccess($this->herd_model);
		$this->herd = new Herd($this->herd_model, $this->session->userdata('herd_code'));
		
		//$this->load->model('product_model');
        //$this->products = new Products()

			//is someone logged in?
		if(!$this->as_ion_auth->logged_in() && $this->herd->herdCode() != $this->config->item('default_herd')) {
			$this->post_message("Please log in.  ");
		}
		
		//is a herd selected?
		if(!$this->herd->herdCode() || $this->herd->herdCode() == ''){
			$this->post_message("Please select a herd and try again.  ");
		}
		
		//does logged in user have access to selected herd?
		$has_herd_access = $this->herd_access->hasAccess($this->session->userdata('user_id'), $this->herd->herdCode(), $this->session->userdata('arr_regions'), $this->permissions->permissionsList());
		if(!$has_herd_access){
			$this->post_message("You do not have permission to access this herd.  Please select another herd and try again.  ");
		}
				
		if((!isset($this->as_ion_auth) || !$this->as_ion_auth->logged_in()) && $this->session->userdata('herd_code') != $this->config->item('default_herd')){
			$this->load->view('session_expired', array('url'=>$this->session->flashdata('redirect_url')));
			exit;
		}
		
		/* Load the profile.php config file if it exists
		if (ENVIRONMENT == 'development' || ENVIRONMENT == 'localhost') {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			} 
		} */
	}
	
	//redirects while retaining message and conditionally setting redirect url
	//@todo: needs to be a part of some kind of authorization class
	protected function post_message($message = ''){
		header("Cache-Control: no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header("Expires: -1");
		header("Content-type: application/json"); //being sent as json

		$this->load->view('echo.php', ['text' => '[]']);
		exit();
	}

	function index(){
		echo 'Direct access to this page is not allowed.';
    }

    function ajax_json() {
		$this->load->model('web_content/navigation_model');
		$Navigation = new Navigation($this->navigation_model, $this->herd, $this->permissions->permissionsList());

		header("Cache-Control: no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header("Expires: -1");
		header("Content-type: application/json"); //being sent as json
		
		echo $Navigation->jsonOutput('DHI');
		exit;
    }
}