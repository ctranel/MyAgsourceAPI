<?php
//namespace myagsource;
require_once(APPPATH . 'controllers/dpage.php');
require_once(APPPATH . 'libraries/Form/Content/Defaults.php');
require_once(APPPATH . 'libraries/dhi/BatchEvent.php');


use \myagsource\Api\Response\ResponseMessage;
use \myagsource\Form\Content\Defaults;
use \myagsource\dhi\BatchEvent;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* -----------------------------------------------------------------
 *	CLASS comments
 *  @file: defaults.php
 *  @author: ctranel
 *
 *  @description: .
 *
 * -----------------------------------------------------------------
 */

class form_defaults extends dpage {
	function __construct(){
		parent::__construct();

		/* Load the profile.php config file if it exists*/
		if (ENVIRONMENT == 'development' || ENVIRONMENT == 'localhost') {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			}
		}
	}
	
	function et_sire(){
        $input = $this->input->userInputArray();
        if(empty($input) || count($input) == 0){
            $this->sendResponse(400, new ResponseMessage('No data sent with request.', 'error'));
        }

        if(!isset($input['select_sire']) || empty($input['select_sire'])){
            $this->sendResponse(204);
        }

        $this->load->model('Forms/form_defaults_model');
        try{
            $defaults = new Defaults($this->form_defaults_model);
            $sire_defaults = $defaults->etSire($input['herd_code'], (int)$input['select_sire']);
            $this->sendResponse(200, null, ['defaults' => $sire_defaults]);
        }
        catch(exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
    }

    function et_donor(){
        $input = $this->input->userInputArray();
        if(empty($input) || count($input) == 0){
            $this->sendResponse(400, new ResponseMessage('No data sent with request.', 'error'));
        }

        if(!isset($input['donor_serial_num']) || empty($input['donor_serial_num'])){
            $this->sendResponse(204);
        }

        $this->load->model('Forms/form_defaults_model');
        try{
            $defaults = new Defaults($this->form_defaults_model);
            $donor_defaults = $defaults->etDonor($input['herd_code'], (int)$input['donor_serial_num']);
            $this->sendResponse(200, null, ['defaults' => $donor_defaults]);
        }
        catch(exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
    }
}