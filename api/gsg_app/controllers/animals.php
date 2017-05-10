<?php
//namespace myagsource;
require_once(APPPATH . 'controllers/dpage.php');

use \myagsource\Api\Response\ResponseMessage;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* -----------------------------------------------------------------
 *	CLASS comments
 *  @file: report_parent.php
 *  @author: ctranel
 *
 *  @description: Parent abstract class that drives report page generation.  All database driven report pages 
 *  	extend this class.
 *
 * -----------------------------------------------------------------
 */

class animals extends dpage {
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
	
	function listing(){
        $cow_options = $this->herd->getCowOptions(
            $this->settings->getValue('cow_id_field'),
            (bool)$this->settings->getValue('show_heifers'),
            (bool)$this->settings->getValue('keep_bull_calves'),
            (bool)$this->settings->getValue('show_sold')
        );
        if(!is_array($cow_options) || empty(array_filter($cow_options))) {
            $this->sendResponse(404, new ResponseMessage('No animals found for herd ' . $this->herd->herdCode() . '.  Please select a report from the navigation', 'error'));
        }
        $this->sendResponse(200, null, ['animals' => $cow_options]);
	}

    function select_breed($species_id){
        try{
            $this->load->model('dhi/animal_model');
            $data = $this->animal_model->getBreeds($species_id);
            $this->sendResponse(200, null, ['options' => json_encode($data)]);
        }
        catch(\Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
    }

    function check_ctrl_num(){
        $input = $this->input->userInputArray();
        try{
            $this->load->model('dhi/animal_model');
            $data = $this->animal_model->getAnimalDataByControlNum($input['herd_code'], $input['control_num']);

            if(count($data) > 0){
                $this->sendResponse(400,  new ResponseMessage('Control Number '. $input['control_num'] . ' is already in use.  Please enter a different number.', 'error'));
            }
            $this->sendResponse(200, null);
        }
        catch(\Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
    }
}