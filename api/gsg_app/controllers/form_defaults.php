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

	function site_from_pen(){
        $input = $this->input->userInputArray();
        if(empty($input) || count($input) == 0){
            $this->sendResponse(400, new ResponseMessage('No data sent with request.', 'error'));
        }

        if(!isset($input['pen_num']) || empty($input['pen_num'])){
            $this->sendResponse(204);
        }

        $this->load->model('Forms/form_defaults_model');
        try{
            $defaults = new Defaults($this->form_defaults_model);
            $defaults = $defaults->siteByPen($input['herd_code'], (int)$input['pen_num']);
            $this->sendResponse(200, null, ['defaults' => $defaults]);
        }
        catch(exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
    }

	/*
	 * returns records from TD.herd.rxtx when adding treatments to custom events
	 */
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

    /*
     * returns records from TD.herd.rxtx with control names matching treatments to animal events
     */
    function herd_event_treatment(){
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
            $defaults = $defaults->herdEventTreatment((int)$input['rxtxid']);
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

        if(isset($input['sire_naab']) && !empty($input['sire_naab']) && isset($input['herd_code']) && !empty($input['herd_code'])){
            try{
                $this->load->model('dhi/animal_model');
                $input['sire_naab'] = Animal::formatNAAB($this->animal_model, $input['sire_naab'], $input['herd_code']);
            }
            catch(\Exception $e){
                $this->sendResponse(400, new ResponseMessage($e->getMessage(), 'error'));
            }

            $this->load->model('Forms/form_defaults_model');
            try{
                $defaults = new Defaults($this->form_defaults_model);
                $bull_defaults = $defaults->etSireNAABData($input['sire_naab']);
                if(empty($bull_defaults)){
                    $bull_defaults['sire_naab'] = ltrim($input['sire_naab'], '0');
                }
                else{
                    $bull_defaults['sire_naab'] = ltrim($bull_defaults['sire_naab'], '0');
                };
                if(isset($bull_defaults['sire_bull_id'])){
                    $bull_defaults['sire_bull_id'] = ltrim($bull_defaults['sire_bull_id'], '0');
                }
                $this->sendResponse(200, null, ['defaults' => $bull_defaults]);
            }
            catch(exception $e){
                $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
            }
        }

        if(isset($input['sire_bull_id']) && !empty($input['sire_bull_id'])){
            try{
                $sire_id_pieces = Animal::formatOfficialId($input['sire_bull_id']);
            }
            catch(\Exception $e){
                $this->sendResponse(400, new ResponseMessage($e->getMessage(), 'error'));
            }

            $this->load->model('Forms/form_defaults_model');
            try{
                $defaults = new Defaults($this->form_defaults_model);
                $bull_defaults = $defaults->etSireIDData($sire_id_pieces['stored_id']);
                if(empty($bull_defaults)){
                    $bull_defaults = [
                        'country_cd' => $sire_id_pieces['country_cd'],
                    ];
                }
                $bull_defaults['sire_bull_id'] = $sire_id_pieces['id'];
                if(isset($bull_defaults['sire_naab'])){
                    $bull_defaults['sire_naab'] = ltrim($bull_defaults['sire_naab'], '0');
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

        if(isset($input['sire_naab']) && !empty($input['sire_naab']) && isset($input['herd_code']) && !empty($input['herd_code'])){
            try{
                $this->load->model('dhi/animal_model');
                $input['sire_naab'] = Animal::formatNAAB($this->animal_model, $input['sire_naab'], $input['herd_code']);
            }
            catch(\Exception $e){
                $this->sendResponse(400, new ResponseMessage($e->getMessage(), 'error'));
            }

            $this->load->model('Forms/form_defaults_model');
            try{
                $defaults = new Defaults($this->form_defaults_model);
                $bull_defaults = $defaults->animalSireNAABData($input['sire_naab']);
                if(empty($bull_defaults)){
                    $bull_defaults['sire_naab'] = ltrim($input['sire_naab'], '0');
                }
                else{
                    $bull_defaults['sire_naab'] = ltrim($bull_defaults['sire_naab'], '0');
                };
                if(isset($bull_defaults['sire_id_num'])){
                    $bull_defaults['sire_id_num'] = ltrim($bull_defaults['sire_id_num'], '0');
                }

                $this->sendResponse(200, null, ['defaults' => $bull_defaults]);
            }
            catch(exception $e){
                $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
            }
        }

        if(isset($input['sire_id_num']) && !empty($input['sire_id_num'])){
            try{
                $sire_id_pieces = Animal::formatOfficialId($input['sire_id_num']);
            }
            catch(\Exception $e){
                $this->sendResponse(400, new ResponseMessage($e->getMessage(), 'error'));
            }

            $this->load->model('Forms/form_defaults_model');
            try{
                $defaults = new Defaults($this->form_defaults_model);
                $bull_defaults = $defaults->animalSireIDData($sire_id_pieces['stored_id']);
                if(empty($bull_defaults)){
                    $bull_defaults = [
                        'sire_country_cd' => $sire_id_pieces['country_cd'],
                    ];
                }
               $bull_defaults['sire_id_num'] = $sire_id_pieces['id'];
               if(isset($bull_defaults['sire_naab'])){
                   $bull_defaults['sire_naab'] = ltrim($bull_defaults['sire_naab'], '0');
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

        if(isset($input['naab']) && !empty($input['naab']) && isset($input['herd_code']) && !empty($input['herd_code'])){
            try{
                $this->load->model('dhi/animal_model');
                $input['naab'] = Animal::formatNAAB($this->animal_model, $input['naab'], $input['herd_code']);
            }
            catch(\Exception $e){
                $this->sendResponse(400, new ResponseMessage($e->getMessage(), 'error'));
            }

            $this->load->model('Forms/form_defaults_model');
            try{
                $defaults = new Defaults($this->form_defaults_model);
                $bull_defaults = $defaults->sireNAABData($input['naab']);
                if(empty($bull_defaults)){
                    $bull_defaults['naab'] = ltrim($input['naab'], '0');
                }
                else{
                    $bull_defaults['naab'] = ltrim($bull_defaults['naab'], '0');
                };
                if(isset($bull_defaults['bull_id'])){
                    $bull_defaults['bull_id'] = ltrim($bull_defaults['bull_id'], '0');
                }

                $this->sendResponse(200, null, ['defaults' => $bull_defaults]);
            }
            catch(exception $e){
                $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
            }
        }

        if(isset($input['bull_id']) && !empty($input['bull_id'])){
            try{
                $sire_id_pieces = Animal::formatOfficialId($input['bull_id']);
            }
            catch(\Exception $e){
                $this->sendResponse(400, new ResponseMessage($e->getMessage(), 'error'));
            }

            $this->load->model('Forms/form_defaults_model');
            try{
                $defaults = new Defaults($this->form_defaults_model);
                $bull_defaults = $defaults->sireIDData($sire_id_pieces['stored_id']);

                if(empty($bull_defaults)){
                    $bull_defaults = [
                        'country_cd' => $sire_id_pieces['country_cd'],
                    ];
                }
                $bull_defaults['bull_id'] = $sire_id_pieces['id'];
                if(isset($bull_defaults['sire_naab'])){
                    $bull_defaults['sire_naab'] = ltrim($bull_defaults['sire_naab'], '0');
                }
                $this->sendResponse(200, null, ['defaults' => $bull_defaults]);
            }
            catch(exception $e){
                $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
            }
        }

        $this->sendResponse(204);
    }

    function format_official_id(){
        $input = $this->input->userInputArray();
        if(empty($input) || count($input) == 0){
            $this->sendResponse(400, new ResponseMessage('No data sent with request.', 'error'));
        }

        if(isset($input['officialid']) && !empty($input['officialid'])){
            try{
                $id_pieces = Animal::formatOfficialId($input['officialid']);
            }
            catch(\Exception $e){
                $this->sendResponse(400, new ResponseMessage($e->getMessage(), 'error'));
            }

            try{
                $bull_defaults = [
                    'country_cd' => $id_pieces['country_cd'],
                    'official_id' => $id_pieces['id'],
                ];
                $this->sendResponse(200, null, ['defaults' => $bull_defaults]);
            }
            catch(exception $e){
                $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
            }
        }

        $this->sendResponse(204);
    }
}