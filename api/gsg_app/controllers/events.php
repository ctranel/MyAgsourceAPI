<?php
//namespace myagsource;
require_once(APPPATH . 'controllers/dpage.php');
require_once(APPPATH . 'libraries/dhi/AnimalEvent.php');
require_once(APPPATH . 'libraries/dhi/BatchEvent.php');


use \myagsource\Api\Response\ResponseMessage;
use \myagsource\dhi\AnimalEvent;
use \myagsource\dhi\BatchEvent;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* -----------------------------------------------------------------
 *	CLASS comments
 *  @file: events.php
 *  @author: ctranel
 *
 *  @description: Parent abstract class that drives report page generation.  All database driven report pages 
 *  	extend this class.
 *
 * -----------------------------------------------------------------
 */

class events extends dpage {
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
	
	function code_map(){
	    //var_dump($this->settings);
        $events = $this->herd->getEventMap();
        if(empty(array_filter($events))) {
            $this->sendResponse(404, new ResponseMessage('No events found for herd ' . $this->herd->herdCode() . '.', 'error'));
        }
        $this->sendResponse(200, null, ['event_map' => $events]);
	}

	function is_eligible(){
        $input = $this->input->userInputArray();
        if(empty($input) || count($input) == 0){
            $this->sendResponse(400, new ResponseMessage('No data sent with request.', 'error'));
        }

        if(!isset($input['event_cd']) || empty($input['event_cd'])){
            $this->sendResponse(204);
        }

        $this->load->model('dhi/events_model');
        try{
            $animal_event = new AnimalEvent($this->events_model, $input['herd_code'], (int)$input['serial_num']);
            $is_eligible = $animal_event->isEligible((int)$input['event_cd'], $input['event_dt'], isset($input['ID']) ? $input['ID'] : null);
        }
        catch(exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }

        //processed successfully, no errors
        if($is_eligible){
            $this->sendResponse(202);
        }

        //if there are errors
        try {
            $errors = $animal_event->eligibleMessage();
            array_walk($errors, function (&$v, $k) {
                $v = new ResponseMessage($v, 'error');
            });
        }
        catch(exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
        $this->sendResponse(200, $errors);
    }

    function herd_defaults(){
        $input = $this->input->userInputArray();
        if(empty($input) || count($input) == 0){
            $this->sendResponse(400, new ResponseMessage('No data sent with request.', 'error'));
        }

        if(!isset($input['event_cd']) || empty($input['event_cd'])){
            $this->sendResponse(204);
        }

        $this->load->model('dhi/events_model');
        try{
            $defaults = $this->events_model->getHerdDefaultValues($input['herd_code'], $input['event_cd']);
        }
        catch(exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }

        $this->sendResponse(200, null, ['herd_defaults' => $defaults]);
    }

    function eligible_animals(){
        $input = $this->input->userInputArray();
        if(empty($input) || count($input) == 0){
            $this->sendResponse(400, new ResponseMessage('No data sent with request.', 'error'));
        }

        if(!isset($input['event_cd']) || empty($input['event_cd'])){
            $this->sendResponse(204);
        }

        $this->load->model('dhi/events_model');
        try{
            $batch_event = new BatchEvent($this->events_model, $input['herd_code']);
            $eligible_animals = $batch_event->eligibleAnimals((int)$input['event_cd'], $input['event_dt']);
        }
        catch(exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }

        //processed successfully, no errors
        if($eligible_animals){
            $this->sendResponse(200, null, ['eligible_animals' => $eligible_animals]);
        }

        //if there are errors
        try {
            $errors = $batch_event->eligibleMessage();
            array_walk($errors, function (&$v, $k) {
                $v = new ResponseMessage($v, 'error');
            });
        }
        catch(exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
        $this->sendResponse(200, $errors);
    }

}