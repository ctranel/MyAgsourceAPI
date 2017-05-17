<?php
require_once(APPPATH . 'controllers/dpage.php');
require_once APPPATH . 'libraries/Site/WebContent/WebBlockFactory.php';
require_once APPPATH . 'libraries/Listings/Content/ListingFactory.php';
require_once(APPPATH . 'libraries/Supplemental/Content/SupplementalFactory.php');
require_once(APPPATH . 'libraries/dhi/Animal.php');

use \myagsource\AccessLog;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\DataHandler;
use \myagsource\dhi\Animal;
use \myagsource\Datasource\DbObjects\DbTableFactory;
use \myagsource\Site\WebContent\WebBlockFactory;
use \myagsource\Report\Content\ReportFactory;
use \myagsource\Listings\Content\ListingFactory;
use \myagsource\Form\Content\FormDisplayFactory;
use \myagsource\Api\Response\ResponseMessage;

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

            //page content
            $this->load->model('ReportContent/report_block_model');

            $this->load->model('Listings/event_listing_model');
            $option_listing_factory = new ListingFactory($this->event_listing_model);

            //create block content
            $listing = $option_listing_factory->getListing(3, ['herd_code' => $this->session->userdata('herd_code'), 'serial_num' => $serial_num]);

            //create blocks for content
            $web_block_factory = new WebBlockFactory($this->block_model, $supplemental_factory);
            $listing_block = $web_block_factory->getBlock(323, $listing);

            $blocks = [$listing_block];
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
            $params = ['serial_num' => $serial_num];
            $this->load->model('dhi/animal_model');
            $editable = Animal::isActive($this->animal_model, $this->session->userdata('herd_code'), $serial_num);

            //supplemental factory
            $this->load->model('supplemental_model');
            $supplemental_factory = new SupplementalFactory($this->supplemental_model, site_url());

            //Set up site content objects
            $this->load->model('web_content/page_model', null, false, $this->session->userdata('user_id'));
            $this->load->model('web_content/block_model');

            //page content
            $this->load->model('ReportContent/report_block_model');

            $this->load->model('Forms/Data_entry_model');//, null, false, $params + ['herd_code'=>$this->session->userdata('herd_code')]);
            $entry_form_factory = new FormDisplayFactory($this->Data_entry_model, $supplemental_factory, $params + ['herd_code'=>$this->session->userdata('herd_code')]);

            //create block content
            //$listing = $option_listing_factory->getListing(3, ['herd_code' => $this->session->userdata('herd_code'), 'serial_num' => $serial_num]);
            $entry_forms = $entry_form_factory->getByPage(126, $this->session->userdata('herd_code'), $editable);

            //create blocks for content
            $web_block_factory = new WebBlockFactory($this->block_model, $supplemental_factory);
            if(is_array($entry_forms)){
                foreach($entry_forms as $f){
                    $block = $web_block_factory->getBlock(415, $f);
                    $data['blocks'][] = $block->toArray();
                }
            }
        }
        catch(Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }

        $this->sendResponse(200, null, $data);
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
            $this->load->model('dhi/animal_model');
            $cow_data = $this->animal_model->cowIdData($this->session->userdata('herd_code'), $serial_num);

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
            $this->load->model('dhi/animal_model');
            $cow_data = $this->animal_model->cowIdData($this->session->userdata('herd_code'), $serial_num);

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
		$params = [
		    'herd_code' => $this->session->userdata('herd_code'),
            'serial_num' => $serial_num,
            'lact_num' => $lact_num,
        ];

        try{
            $supplemental_factory = $this->_supplementalFactory();
            $this->filters = $this->_filters(79, $params);
            $benchmarks = $this->_benchmarks();

            //load models for block content
            $this->load->model('ReportContent/report_block_model');
            $this->load->model('Datasource/db_field_model');
            $this->load->model('ReportContent/report_data_model');
            $this->load->model('Datasource/db_table_model');
            $benchmarks = $this->_benchmarks();

            //load factories for block content
            $data_handler = new DataHandler($this->report_data_model, $benchmarks);
            $db_table_factory = new DbTableFactory($this->db_table_model);
            $report_factory = new ReportFactory($this->report_block_model, $this->db_field_model, $this->filters, $supplemental_factory, $data_handler, $db_table_factory);

            $report = $report_factory->getByBlock(410);

            //create blocks for content
            $web_block_factory = new WebBlockFactory($this->block_model, $supplemental_factory);
            $report_block = $web_block_factory->getBlock(410, $report);

            $blocks = [$report_block];
            if(is_array($blocks)){
                foreach($blocks as $b){
                    $data['blocks'][] = $b->toArray();
                }
            }
            $data['lact_num'] = $lact_num;
        }
        catch(Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }

        $this->sendResponse(200, null, $data);
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