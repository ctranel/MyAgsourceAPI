<?php
//namespace myagsource;

require_once(APPPATH . 'core/MY_Api_Controller.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');
require_once(APPPATH . 'libraries/AccessLog.php');
require_once(APPPATH . 'libraries/Benchmarks/Benchmarks.php');
require_once(APPPATH . 'libraries/dhi/HerdAccess.php');
//require_once APPPATH . 'libraries/Settings/Settings.php';
require_once APPPATH . 'libraries/Api/Response/ResponseMessage.php';
require_once APPPATH . 'libraries/Site/WebContent/Navigation.php';

use \myagsource\AccessLog;
use \myagsource\dhi\Herd;
use \myagsource\Benchmarks\Benchmarks;
use \myagsource\dhi\HerdAccess;
//use \myagsource\Settings\Settings;
use \myagsource\Api\Response\ResponseMessage;
use \myagsource\Site\WebContent\Navigation;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Change_herd extends MY_Api_Controller {
	/* 
	 * @var Herd object
	 */
	//protected $herd;
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

    function herd_list(){
        //get herd list
        $tmp_arr = $this->herd_access->getAccessibleHerdOptions($this->session->userdata('user_id'), $this->permissions->permissionsList(), $this->session->userdata('arr_regions'));
        //@todo: handle if there is only 1 herd (or 0)
        if(count($tmp_arr) === 0){
            $this->sendResponse(404, new ResponseMessage('No herds found.', 'error'));
        }

        //send response
        $this->sendResponse(200, [], ['herd_codes' => $tmp_arr]);
    }

	/**
	 * @method select() - option list and input field to select a herd (text field auto-selects options list value).
	 * 			sets session herd code on successful submissions.
	 *
	 * @access	public
	 * @return	void
	 */
	function select(){
		if(!$this->permissions->hasPermission("Select Herd")){
			$this->sendResponse(403, new ResponseMessage('You do not have permission to select a herd.', 'error'));
		}

		//validate form input
		$this->load->library('form_validation');
		$this->form_validation->set_rules('herd_code', 'Herd', 'required|max_length[8]');
		//$this->form_validation->set_rules('herd_code_fill', 'Type Herd Code');
		if($this->form_validation->run_input() === true){
            //if the user has access to only 1 herd, set them up with that herd regardless of form submission
            $tmp_arr = $this->herd_access->getAccessibleHerdsData($this->session->userdata('user_id'), $this->permissions->permissionsList(), $this->session->userdata('arr_regions'));
            if(is_array($tmp_arr) && !empty($tmp_arr) && count($tmp_arr) === 1) {
                try{
                    $resp_msg = [];
                    $msg = $this->_loadSessionHerd($tmp_arr[0]['herd_code']);
                    if(!empty($msg)){
                        $resp_msg[] = new ResponseMessage($msg, 'message');
                    }
                    $herd_info = $this->_setSessionHerdData();
                    $this->_record_access(2); //2 is the page code for herd change
                 //SHOULD WE LOAD DASHBOARD CONTENT HERE???
                    $this->sendResponse(200, $resp_msg, ['herd_info' => $herd_info]);
                }
                catch(Exception $e){
                    $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
                }
            }

            //if the user has access to more than 1 herd
            try{
                $resp_msg = [];
                $msg = $this->_loadSessionHerd($this->input->userInput('herd_code'));
                if(!empty($msg)){
                    $resp_msg = new ResponseMessage($msg, 'message');
                }
                $herd_info = $this->_setSessionHerdData();
                $this->_record_access(2); //2 is the page code for herd change
                $this->sendResponse(200, $resp_msg, ['herd_info' => $herd_info]);
            }
            catch(Exception $e){
                $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
            }
            //else send error
		}
        $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
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
		if(!$this->permissions->hasPermission("Request Herd")){
            $this->sendResponse(403, new ResponseMessage('You do not have permissions to request herds.', 'error'));
		}

		//validate form input
		$this->load->library('form_validation');
		$this->form_validation->set_rules('herd_code', 'Herd', 'required|exact_length[8]');
		$this->form_validation->set_rules('herd_release_code', 'Herd Release Code', 'required|exact_length[10]');

        if($this->form_validation->run() === false){
            $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
        }

        $herd_code = $this->input->userInput('herd_code');
		if(!empty($herd_code)){//if form is submitted
			$herd_release_code = $this->input->userInput('herd_release_code');
			$error = $this->herd_model->herd_authorization_error($herd_code, $herd_release_code);
			if($error){
                $this->sendResponse(403, new ResponseMessage('Invalid data submitted: ' . $error, 'error'));
			}
		}

        $this->_loadSessionHerd($this->input->userInput('herd_code'));
        $herd_info = $this->_setSessionHerdData();
        $this->_record_access(2); //2 is the page code for herd change
        $this->sendResponse(200, null, ['herd_info' => $herd_info]);
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

    /*
     * _setSessionHerd
     * 
     * @param: string herd code
     * @return response text
     */
    protected function _loadSessionHerd($herd_code){
        $msg = '';
        //if(!isset($this->herd)){
            $this->herd = new Herd($this->herd_model, $herd_code);
        //}
        if($this->session->userdata('active_group_id') == 2){ //user is a producer
            $trials = $this->herd->getTrialData();
            if(isset($trials) && is_array($trials)){
                $today  = new DateTime();
                foreach($trials as $t){
                    if($t['herd_trial_warning'] === null || $t['herd_trial_expires'] === null){
                        continue;
                    }
                    $warn_date = new DateTime($t['herd_trial_warning']);
                    $expire_date = new DateTime($t['herd_trial_expires']);
                    $days_remain = $expire_date->diff($today)->days;
                    if($t['herd_trial_is_expired'] === 1){
                        $msg = 'The trial period on ' . $t['value_abbrev'] . ' for herd ' . $this->herd->herdCode() . ' has expired. Please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' to enroll on ' . $t['value_abbrev'] . ' and get the full benefit of the MyAgSource web site.';
                    }
                    elseif($warn_date <= $today){
                        $msg = 'Herd ' . $this->herd->herdCode() . ' has ' . $days_remain . ' days remaining on its free trial of ' . $t['value_abbrev'] . '.  To ensure uninterrupted access, please contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . ' to enroll on ' . $t['value_abbrev'] . ' and get the full benefit of the MyAgSource web site.';
                    }
                }
            }
        }
        return $msg;
    }

	protected function _setSessionHerdData(){
        $herd_info = $this->herd->header_info($this->herd->herdCode());
		$this->session->set_userdata('herd_code', $this->herd->herdCode());
		$this->session->set_userdata('recent_test_date', $herd_info['test_date']);
		//load new benchmarks
		//$this->load->model('Forms/setting_form_model', null, false, ['user_id'=>$this->session->userdata('user_id'), 'herd_code'=>$this->herd->herdCode()]);
		$this->load->model('Settings/benchmark_model', null, false, ['user_id' => $this->session->userdata('user_id'), 'herd_code' => $this->session->userdata('herd_code')]);
        $benchmarks = new Benchmarks($this->benchmark_model, $this->session->userdata('user_id'), $this->herd->herdCode(), $herd_info, []);
		$this->session->set_userdata('benchmarks', $benchmarks->getSettingKeyValues());

        return $herd_info;
		//$general_dhi = new Settings($this->session->userdata('user_id'), $this->herd->herdCode(), $this->setting_form_model, 'general_dhi', []);
		//$this->session->set_userdata('general_dhi', $general_dhi->getSettingKeyValues());
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
