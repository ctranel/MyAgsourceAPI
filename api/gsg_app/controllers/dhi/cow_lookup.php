<?php
require_once(APPPATH . 'controllers/dpage.php');
require_once APPPATH . 'libraries/Site/WebContent/WebBlockFactory.php';
require_once APPPATH . 'libraries/Listings/Content/ListingFactory.php';
require_once(APPPATH . 'libraries/Supplemental/Content/SupplementalFactory.php');

use \myagsource\AccessLog;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\Site\WebContent\WebBlockFactory;
use \myagsource\Listings\Content\ListingFactory;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Cow_lookup extends dpage {
	
	/**
	 * cow_id_field
	 * @var String
	 **/
	protected $cow_id_field;
	
	var $cow_id;
	var $curr_lact_num;
	var $curr_calving_date;
	
	function __construct(){
		parent::__construct();

		$this->cow_id_field = $this->settings->getValue('cow_id_field');
		//$herd_code = $this->session->userdata('herd_code');

		/* Load the profile.php config file if it exists
		if (ENVIRONMENT == 'development' || ENVIRONMENT == 'localhost') {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			} 
		} */
	}
	
    function index($serial_num, $show_all_events = 0){
        $this->events($serial_num, $show_all_events);
	}

	/* only the events tab uses the dynamic page infrastructure of the site.  If we want to use this for the other tabs,
	 *   we would have to create a page within the database for each tab
	*/
	function events($serial_num, $show_all_events = 0){
        $page_id = 79;

		try{
            $this->_loadObjVars($serial_num);
            $this->load->model('dhi/cow_lookup/events_model');
            $data['animal_data'] = $this->events_model->getCowArray($this->session->userdata('herd_code'), $serial_num);
            $data['animal_data']['chosen_id'] = $data['animal_data'][$this->cow_id_field];
            $data['animal_data']['serial_num'] = $serial_num;

            //supplemental factory
            $this->load->model('supplemental_model');
            $supplemental_factory = new SupplementalFactory($this->supplemental_model, site_url());

            //Set up site content objects
            $this->load->model('web_content/page_model', null, false, $this->session->userdata('user_id'));
            $this->load->model('web_content/block_model');
            $web_block_factory = new WebBlockFactory($this->block_model, $supplemental_factory);

            //page content
            $this->load->model('ReportContent/report_block_model');

            $this->load->model('Listings/event_listing_model');
            $option_listing_factory = new ListingFactory($this->event_listing_model);

            //create block content
            $listings = $option_listing_factory->getByPage($page_id, ['herd_code' => $this->session->userdata('herd_code'), 'serial_num' => $serial_num]);

            //create blocks for content
            $blocks = $web_block_factory->getBlocksFromContent($page_id, $listings, $supplemental_factory);
            if(is_array($blocks)){
                foreach($blocks as $b){
                    $data['blocks'][] = $b->toArray();
                }
            }
        }
        catch(Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }

        $this->sendResponse(200, null, $data);
	}
	
	function id($serial_num){
		try{
            $this->load->model('dhi/cow_lookup/id_model');
            $data = $this->id_model->getCowArray($this->session->userdata('herd_code'), $serial_num);
            $data['chosen_id'] = $data[$this->cow_id_field];
        }
        catch(Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }

        $this->sendResponse(200, null, ['animal_data' => $data]);
	}

	function dam($serial_num){
		$this->load->model('dhi/cow_lookup/dam_model');

    	try{
            $data['dam'] = $this->dam_model->getCowArray($this->session->userdata('herd_code'), $serial_num);
            //build lactation tables
            $this->load->model('dhi/cow_lookup/lactations_model');
            $tab = array();
            if(isset($data['dam']['dam_serial_num']) && !empty($data['dam']['dam_serial_num'])){
                $subdata['arr_lacts'] = $this->lactations_model->getLactationsArray($this->session->userdata('herd_code'), $data['dam']['dam_serial_num']);
                $tab['lact_table'] = $subdata;
                $subdata['arr_offspring'] = $this->lactations_model->getOffspringArray($this->session->userdata('herd_code'), $data['dam']['dam_serial_num']);
                $tab['offspring_table'] = $subdata;
                unset($subdata);
            }
            $data['lact_tables'] = $tab;
            unset($tab);

            $data['chosen_id'] = $data['dam'][$this->cow_id_field];
        }
        catch(Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }

        $this->sendResponse(200, null, ['animal_data' => $data]);
	}
	
	function sire($serial_num){
		try{
            $this->load->model('dhi/cow_lookup/sire_model');
            $data = $this->sire_model->getCowArray($this->session->userdata('herd_code'), $serial_num);
            $data['chosen_id'] = $data[$this->cow_id_field];

            $test_empty = array_filter($data);
        }
        catch(Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }

        if(!empty($test_empty)){
            $this->sendResponse(200, null, ['animal_data' => $data]);
    	}
    	else{
            $resp_msg = new ResponseMessage('Sire data not found', 'message');
            $this->sendResponse(404, $resp_msg);
    	}
	}
	
	function tests($serial_num, $lact_num=NULL){
		if(!isset($this->curr_lact_num) || !isset($this->cow_id)) {
			try{
                $this->_loadObjVars($serial_num);
            }
            catch(Exception $e){
                $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
            }
        }
		if(!isset($lact_num)){
			$lact_num = $this->curr_lact_num;
		}
        try{
            $this->load->model('dhi/cow_model');
            $cow_data = $this->cow_model->cowIdData($this->session->userdata('herd_code'), $serial_num);

            $this->load->model('dhi/cow_lookup/tests_model');
            $data = [
                'arr_tests' => $this->tests_model->getTests($this->session->userdata('herd_code'), $serial_num, $lact_num)
                ,'cow_id' => $this->cow_id
                ,'serial_num' => $serial_num
                ,'lact_num' => $lact_num
                ,'curr_lact_num' => $this->curr_lact_num
            ];

            $data['chosen_id'] = $cow_data[$this->cow_id_field];
        }
        catch(Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }

        $this->sendResponse(200, null, ['animal_data' => $data]);
	}
	
	function lactations($serial_num){
        try{
            $this->load->model('dhi/cow_model');
            $cow_data = $this->cow_model->cowIdData($this->session->userdata('herd_code'), $serial_num);

            $this->load->model('dhi/cow_lookup/lactations_model');
            $data['lactations'] = $this->lactations_model->getLactationsArray($this->session->userdata('herd_code'), $serial_num);
            $data['offspring'] = $this->lactations_model->getOffspringArray($this->session->userdata('herd_code'), $serial_num);

            $data['chosen_id'] = $cow_data[$this->cow_id_field];
        }
        catch(Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }

        $this->sendResponse(200, null, ['animal_data' => $data]);
	}
	
	function graphs($serial_num, $lact_num=NULL){
		if(!isset($this->curr_lact_num) || !isset($this->cow_id)) {
			try{
                $this->_loadObjVars($serial_num);
            }
            catch(Exception $e){
                $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
            }

        }
		if(!isset($lact_num)){
			$lact_num = $this->curr_lact_num;
		}

        try{
            $this->load->model('dhi/cow_model');
            $cow_data = $this->cow_model->cowIdData($this->session->userdata('herd_code'), $serial_num);

            $this->load->model('dhi/cow_lookup/graphs_model');
            $this->load->library('chart');
            $data = array(
                'arr_tests' => $this->chart->formatDataSet($this->graphs_model->getGraphData($this->session->userdata('herd_code'), $serial_num, $lact_num), 'lact_dim')
                ,'cow_id' => $this->cow_id
                ,'serial_num' => $serial_num
                ,'lact_num' => $lact_num
                ,'curr_lact_num' => $this->curr_lact_num
            );


            $ret = $this->load->view('dhi/cow_lookup/graphs', $data, true);
            $ret['chosen_id'] = $cow_data[$this->cow_id_field];
        }
        catch(Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }

        $this->sendResponse(200, null, $ret);
	}

    protected function _loadObjVars($serial_num){
		$this->load->model('dhi/cow_lookup/events_model');
		$events_data = $this->events_model->getCowArray($this->session->userdata('herd_code'), $serial_num);
   		$this->cow_id = $events_data[$this->cow_id_field];
		$this->curr_lact_num = $events_data['curr_lact_num'];
		$this->curr_calving_date = $events_data['curr_calving_date'];
	} 
	
	protected function _record_access($event_id){
		if($this->session->userdata('user_id') === FALSE){
			return FALSE;
		}
		$herd_code = $this->session->userdata('herd_code');
		$recent_test = $this->session->userdata('recent_test_date');
		$recent_test = empty($recent_test) ? NULL : $recent_test;
		
		$this->load->model('access_log_model');
		$access_log = new AccessLog($this->access_log_model);
				
		$access_log->writeEntry(
			$this->as_ion_auth->is_admin(),
			$event_id,
			$herd_code,
			$recent_test,
			$this->session->userdata('user_id'),
			$this->session->userdata('active_group_id'),
			null //no report code for cow lookup
		);
	}
}