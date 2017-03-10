<?php
//namespace myagsource;
require_once(APPPATH . 'controllers/dpage.php');
require_once APPPATH . 'libraries/Site/WebContent/WebBlockFactory.php';
require_once APPPATH . 'libraries/Listings/Content/ListingFactory.php';
require_once(APPPATH . 'libraries/Supplemental/Content/SupplementalFactory.php');
require_once(APPPATH . 'libraries/Site/WebContent/Page.php');
require_once(APPPATH . 'libraries/dhi/HerdPageAccess.php');
require_once(APPPATH . 'libraries/Site/WebContent/PageAccess.php');

use \myagsource\Site\WebContent\WebBlockFactory;
use \myagsource\Listings\Content\ListingFactory;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\Site\WebContent\Page;
use \myagsource\dhi\HerdPageAccess;
use \myagsource\Site\WebContent\PageAccess;

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
        $cow_options = $this->herd->getCowOptions($this->settings->getValue('cow_id_field'));
        if(empty(array_filter($cow_options))) {
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
        $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
    }


}