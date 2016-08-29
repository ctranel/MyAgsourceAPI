<?php
//namespace myagsource;

require_once(APPPATH . 'core/MY_Api_Controller.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');
require_once(APPPATH . 'libraries/AccessLog.php');
require_once(APPPATH . 'libraries/Benchmarks/Benchmarks.php');
require_once(APPPATH . 'libraries/dhi/HerdAccess.php');
require_once APPPATH . 'libraries/Settings/SessionSettings.php';
require_once APPPATH . 'libraries/Api/Response/ResponseMessage.php';
require_once APPPATH . 'libraries/Site/WebContent/Navigation.php';
require_once(APPPATH . 'libraries/Form/Content/FormFactory.php');
require_once(APPPATH . 'libraries/Site/WebContent/WebBlockFactory.php');
require_once(APPPATH . 'libraries/Supplemental/Content/SupplementalFactory.php');

use \myagsource\AccessLog;
use \myagsource\dhi\Herd;
use \myagsource\Benchmarks\Benchmarks;
use \myagsource\dhi\HerdAccess;
use \myagsource\Settings\SessionSettings;
use \myagsource\Api\Response\ResponseMessage;
use \myagsource\Site\WebContent\Navigation;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\Site\WebContent\WebBlockFactory;
use \myagsource\Form\Content\FormFactory;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class dform extends MY_Api_Controller {
	/* 
	 * @var Herd object
	 */
	protected $herd;
	/* 
	 * @var HerdAccess object
	 */
	protected $herd_access;
	/* 
	 * @var AccessLog object
	 */
	protected $access_log;

	protected $notifications;
	protected $notices;

	function __construct(){
		parent::__construct();

		$this->load->model('herd_model');
		$this->herd_access = new HerdAccess($this->herd_model);

		if(!isset($this->as_ion_auth) || !$this->as_ion_auth->logged_in()){
			$this->sendResponse(401);
		}
		$this->load->model('access_log_model');
		$this->access_log = new AccessLog($this->access_log_model);

//		$this->page_header_data['num_herds'] = $this->herd_access->getNumAccessibleHerds($this->session->userdata('user_id'), $this->permissions->permissionsList(), $this->session->userdata('arr_regions'));

		/* Load the profile.php config file if it exists */
		if ((ENVIRONMENT == 'development' || ENVIRONMENT == 'localhost') && strpos($this->router->method, 'ajax') === false) {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			}
		}
	}

	function index(){
		$this->sendResponse(404);
	}

	/**
	 * @method settings() - setting submission.
	 *
	 * @access	public
	 * @return	void
	 */
	function settings($form_id){
		//validate form input
        $this->load->library('herds');
		$this->load->library('form_validation');
		//$this->form_validation->set_rules('herd_code', 'Herd', 'required|max_length[8]');
		//$this->form_validation->set_rules('herd_code_fill', 'Type Herd Code');
//die(var_dump($this->form_validation->run_input()));
		if($this->form_validation->run_input() === true){
            try{
                //get form object
                //@todo: split form core from form display (core would be included in display), no need for web content or supplemental here
                //supplemental factory
                $this->load->model('supplemental_model');
                $supplemental_factory = new SupplementalFactory($this->supplemental_model, site_url());

                $this->load->model('Forms/setting_model', null, false, ['user_id'=>$this->session->userdata('user_id'), 'herd_code'=>$this->session->userdata('herd_code')]);
                $form_factory = new FormFactory($this->setting_model);
                
                $form = $form_factory->getSettingForm($form_id, $this->session->userdata('user_id'), $this->session->userdata('herd_code'));
                $form->write($this->input->userInputArray());

                $resp_msg = [];
                //$msg = $this->_loadSessionHerd($tmp_arr[0]['herd_code']);
                //if(!empty($msg)){
                $resp_msg = new ResponseMessage('Form submission successful', 'message');
                //}
                //$this->_record_access(2); //2 is the page code for herd change
/*                $this->load->model('web_content/navigation_model');
                $navigation = new Navigation($this->navigation_model, $this->herd, $this->permissions->permissionsList());
                $payload = ['nav' => $navigation->toArray('DHI')]; */
                $this->sendResponse(200, $resp_msg);
            }
            catch(Exception $e){
                $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
            }
		}
        $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
	}

    public function herd_enrolled($herd_code){
        //determines type of access for service groups
        if($this->permissions->hasPermission('View Assign w permission') === false) {
            $enroll_status = 0;
            $has_accessed = false;
        }
        else{
            $this->herd = new Herd($this->herd_model, $herd_code);
            //for now, we want to warn if herd is not enrolled on full product
            $enroll_status = $this->herd->getHerdEnrollStatus(['AMYA-550', 'AMYA-500', 'APAG-505']);
            $recent_test = $this->herd->getRecentTest();
            $has_accessed = $this->access_log->sgHasAccessedTest($this->session->userdata('sg_acct_num'), $herd_code, null, $recent_test);
        }
        $this->sendResponse(200, null, json_encode(['enroll_status' => $enroll_status, 'new_test' => !$has_accessed]));
    }

	protected function _record_access($event_id){
		if($this->session->userdata('user_id') === FALSE){
			return FALSE;
		}
		$herd_code = $this->session->userdata('herd_code');
		$recent_test = $this->session->userdata('recent_test_date');
		$recent_test = empty($recent_test) ? NULL : $recent_test;

		$this->access_log->writeEntry(
			$this->as_ion_auth->is_admin(),
			$event_id,
			$herd_code,
			$recent_test,
			$this->session->userdata('user_id'),
			$this->session->userdata('active_group_id')
		);
	}
}
