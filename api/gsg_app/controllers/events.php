<?php
//namespace myagsource;
require_once(APPPATH . 'controllers/dpage.php');
require_once(APPPATH . 'libraries/dhi/AnimalEvent.php');
require_once(APPPATH . 'libraries/dhi/HerdEvents.php');
require_once(APPPATH . 'libraries/dhi/BatchEvent.php');
require_once(APPPATH . 'models/dhi/events_model.php');


use \myagsource\Api\Response\ResponseMessage;
use \myagsource\dhi\AnimalEvent;
use \myagsource\dhi\BatchEvent;
use \myagsource\dhi\HerdEvents;

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
    protected $events_model;

	function __construct(){
		parent::__construct();
        $this->events_model = new Events_model();

		/* Load the profile.php config file if it exists*/
		if (ENVIRONMENT == 'development' || ENVIRONMENT == 'localhost') {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			}
		}
	}
	
	function code_map(){
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

        if(!isset($input['event_cd']) || empty($input['event_cd']) || !isset($input['event_dt']) || empty($input['event_dt'])){
            $this->sendResponse(204);
        }

        try{
            $animal_event = new AnimalEvent($this->events_model, $input['herd_code'], (int)$input['serial_num']);
            $is_eligible = $animal_event->isEligible((int)$input['event_cd'], $input['event_dt'], isset($input['animal_event_id']) ? $input['animal_event_id'] : null);
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

        try{
            $herd_events = new HerdEvents($this->events_model, $input['herd_code']);
            $defaults = $herd_events->getHerdDefaultValues($input['event_cd'], $input['serial_num']);
        }
        catch(exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }

        $this->sendResponse(200, null, ['defaults' => $defaults]);
    }

    function eligible_animals(){
        $input = $this->input->userInputArray();
        if(empty($input) || count($input) == 0){
            $this->sendResponse(400, new ResponseMessage('No data sent with request.', 'error'));
        }

        if(!isset($input['event_cd']) || empty($input['event_cd'])){
            $this->sendResponse(204);
        }

        try{
            $batch_event = new BatchEvent($this->events_model, $input['herd_code']);
            $eligible_animals = $batch_event->eligibleAnimals((int)$input['event_cd'], $input['event_dt']);
        }
        catch(exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }

        //processed successfully, no errors
        if($eligible_animals){
            $this->sendResponse(200, null, ['options' => $eligible_animals]);
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

    function valid_days_bred(){
        $input = $this->input->userInputArray();
        if(empty($input) || count($input) == 0){
            $this->sendResponse(400, new ResponseMessage('No data sent with request.', 'error'));
        }

        try {
            $lact_date = $this->events_model->activeEventPeriodStartDate($input['herd_code'], $input['serial_num']);

            $dtDate_bred = new \DateTime($input['event_dt']);
            $dtDate_bred->modify('-' . $input['days_bred'] . ' days');
            $dtLact_date = new \DateTime($lact_date);
        }
        catch(exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }

        if($dtDate_bred > $dtLact_date){
            $this->sendResponse(202);
        }

        $msg = new ResponseMessage('Event date minus days bred (' . $dtDate_bred->format('m-d-Y') . ') must be later than the most recent fresh date or birthdate (' . $dtLact_date->format('m-d-Y') . ').', 'error');
        $this->sendResponse(200, [$msg]);
    }
}