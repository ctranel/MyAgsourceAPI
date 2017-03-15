<?php
//namespace myagsource;
require_once(APPPATH . 'controllers/dpage.php');
require_once(APPPATH . 'libraries/Form/Content/Defaults.php');
require_once(APPPATH . 'libraries/dhi/BatchEvent.php');
require_once(APPPATH . 'libraries/dhi/Animal.php');


use \myagsource\Api\Response\ResponseMessage;
use \myagsource\Form\Content\Defaults;
use \myagsource\dhi\Animal;

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
	
    function herd_treatment(){
        $input = $this->input->userInputArray();
        if(empty($input) || count($input) == 0){
            $this->sendResponse(400, new ResponseMessage('No data sent with request.', 'error'));
        }

        if(!isset($input['rxtxid']) || empty($input['rxtxid'])){
            $this->sendResponse(204);
        }

        $this->load->model('Forms/form_defaults_model');
        try{
            $defaults = new Defaults($this->form_defaults_model);
            $defaults = $defaults->herdTreatment((int)$input['rxtxid']);
            $this->sendResponse(200, null, ['defaults' => $defaults]);
        }
        catch(exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
    }

	function herd_sire(){
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

    function herd_donor(){
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

    function et_sire(){
        $input = $this->input->userInputArray();
        if(empty($input) || count($input) == 0){
            $this->sendResponse(400, new ResponseMessage('No data sent with request.', 'error'));
        }

        if(isset($input['sire_naab']) && !empty($input['sire_naab'])){
            try{
                $this->load->model('dhi/animal_model');
                $input['sire_naab'] = Animal::formatNAAB($this->animal_model, $input['sire_naab'], $this->settings->getValue('species'));
            }
            catch(\Exception $e){
                $this->sendResponse(400, new ResponseMessage($e->getMessage(), 'error'));
            }

            $this->load->model('Forms/form_defaults_model');
            try{
                $defaults = new Defaults($this->form_defaults_model);
                $bull_defaults = $defaults->etSireNAABData($input['sire_naab']);
                $this->sendResponse(200, null, ['defaults' => $bull_defaults]);
            }
            catch(exception $e){
                $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
            }
        }

        if(isset($input['sire_bull_id']) && !empty($input['sire_bull_id'])){
            try{
                $this->load->model('dhi/animal_model');
                $input['sire_bull_id'] = Animal::formatOfficialId($this->animal_model, $input['sire_bull_id']);
            }
            catch(\Exception $e){
                $this->sendResponse(400, new ResponseMessage($e->getMessage(), 'error'));
            }

            $this->load->model('Forms/form_defaults_model');
            try{
                $defaults = new Defaults($this->form_defaults_model);
                $bull_defaults = $defaults->etSireIDData($input['sire_bull_id']);
                if(strlen($input['sire_bull_id']) > strlen($defaults['sire_bull_id'])){
                    $defaults['sire_bull_id'] = $input['sire_bull_id'];
                }
                $this->sendResponse(200, null, ['defaults' => $bull_defaults]);
            }
            catch(exception $e){
                $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
            }
        }

        $this->sendResponse(204);
    }

    function animal_sire(){
        $input = $this->input->userInputArray();
        if(empty($input) || count($input) == 0){
            $this->sendResponse(400, new ResponseMessage('No data sent with request.', 'error'));
        }

        if(isset($input['sire_naab']) && !empty($input['sire_naab'])){
            try{
                $this->load->model('dhi/animal_model');
                $input['sire_naab'] = Animal::formatNAAB($this->animal_model, $input['sire_naab'], $this->settings->getValue('species'));
            }
            catch(\Exception $e){
                $this->sendResponse(400, new ResponseMessage($e->getMessage(), 'error'));
            }

            $this->load->model('Forms/form_defaults_model');
            try{
                $defaults = new Defaults($this->form_defaults_model);
                $bull_defaults = $defaults->animalSireNAABData($input['sire_naab']);
                $this->sendResponse(200, null, ['defaults' => $bull_defaults]);
            }
            catch(exception $e){
                $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
            }
        }

        if(isset($input['sire_bull_id']) && !empty($input['sire_bull_id'])){
            try{
                $this->load->model('dhi/animal_model');
                $input['sire_bull_id'] = Animal::formatOfficialId($this->animal_model, $input['sire_bull_id']);
            }
            catch(\Exception $e){
                $this->sendResponse(400, new ResponseMessage($e->getMessage(), 'error'));
            }

            $this->load->model('Forms/form_defaults_model');
            try{
                $defaults = new Defaults($this->form_defaults_model);
                $bull_defaults = $defaults->animalSireIDData($input['sire_bull_id']);
                if(strlen($input['sire_bull_id']) > strlen($defaults['sire_bull_id'])){
                    $defaults['sire_bull_id'] = $input['sire_bull_id'];
                }
                $this->sendResponse(200, null, ['defaults' => $bull_defaults]);
            }
            catch(exception $e){
                $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
            }
        }

        $this->sendResponse(204);
    }

    function sire(){
        $input = $this->input->userInputArray();
        if(empty($input) || count($input) == 0){
            $this->sendResponse(400, new ResponseMessage('No data sent with request.', 'error'));
        }

        if(isset($input['naab']) && !empty($input['naab'])){
            try{
                $this->load->model('dhi/animal_model');
                $input['naab'] = Animal::formatNAAB($this->animal_model, $input['naab'], $this->settings->getValue('species'));
            }
            catch(\Exception $e){
                $this->sendResponse(400, new ResponseMessage($e->getMessage(), 'error'));
            }

            $this->load->model('Forms/form_defaults_model');
            try{
                $defaults = new Defaults($this->form_defaults_model);
                $bull_defaults = $defaults->sireNAABData($input['naab']);
                $this->sendResponse(200, null, ['defaults' => $bull_defaults]);
            }
            catch(exception $e){
                $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
            }
        }

        if(isset($input['bull_id']) && !empty($input['bull_id'])){
            try{
                $this->load->model('dhi/animal_model');
                $input['bull_id'] = Animal::formatOfficialId($this->animal_model, $input['bull_id']);
            }
            catch(\Exception $e){
                $this->sendResponse(400, new ResponseMessage($e->getMessage(), 'error'));
            }

            $this->load->model('Forms/form_defaults_model');
            try{
                $defaults = new Defaults($this->form_defaults_model);
                $bull_defaults = $defaults->sireIDData($input['bull_id']);
                if(strlen($input['bull_id']) > strlen($defaults['bull_id'])){
                    $defaults['bull_id'] = $input['bull_id'];
                }
                $this->sendResponse(200, null, ['defaults' => $bull_defaults]);
            }
            catch(exception $e){
                $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
            }
        }

        $this->sendResponse(204);
    }

}