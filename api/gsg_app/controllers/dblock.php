<?php
//namespace myagsource;

require_once(APPPATH . 'controllers/dpage.php');
require_once(APPPATH . 'libraries/Form/Content/FormDisplayFactory.php');

use \myagsource\DataHandler;
use \myagsource\Datasource\DbObjects\DbTableFactory;
use \myagsource\Api\Response\ResponseMessage;
use \myagsource\Supplemental\Content\SupplementalFactory;

use \myagsource\Site\WebContent\WebBlockFactory;
use \myagsource\Settings\Form\SettingsFormDisplayFactory;
use \myagsource\Listings\Content\ListingFactory;
use \myagsource\Report\Content\ReportFactory;
use \myagsource\Form\Content\FormDisplayFactory;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class dblock extends dpage {
	protected $notifications;
	protected $notices;

	function __construct(){
		parent::__construct();

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

    /**
     * @method get()
     *
     * @param int block id
     * @param string json data (key data for retrieving form)
     * @access	public
     * @return	void
     */
    function get($block_id, $json_data = null){
        $page_id = 105; //@todo: this will work until we have content that uses filters (reports)
        $this->load->model('Listings/herd_options_model');
        $this->_processBlock($page_id, $block_id, $json_data, $this->herd_options_model);
    }

    /**
     * @method event_subblock()
     *
     * @param int block id
     * @param string json data (key data for retrieving form)
     * @access	public
     * @return	void
     */
    function event_subblock($block_id, $json_data = null){
        $page_id = 79;
        $this->load->model('Listings/event_listing_model');
        $this->_processBlock($page_id, $block_id, $json_data, $this->event_listing_model);
    }

    protected function _processBlock($page_id, $block_id, $json_data, $datasource){
        $params = [];
        if(isset($json_data)) {
            $params = array_filter((array)json_decode(urldecode($json_data)));
        }

        if(isset($params['batchid']) && is_array($params['batchid']) && count($params['batchid']) === 1){
            $params['batchid'] = $params['batchid'][0];
        }

        $listing_factory = new ListingFactory($datasource);

        $supplemental_factory = $this->_supplementalFactory();

        $this->filters = $this->_filters($page_id, $params);
        $benchmarks = $this->_benchmarks();
        try {
            $this->load->model('web_content/page_model', null, false, $this->session->userdata('user_id'));
            $this->load->model('web_content/block_model');
            $block_content = $this->_blockContent($block_id, $supplemental_factory, $params, $benchmarks, $listing_factory);

            $web_block_factory = new WebBlockFactory($this->block_model, $supplemental_factory);

            //create blocks for content
            $block = $web_block_factory->getBlock($block_id, $block_content);
        }
        catch(\Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
        $this->sendResponse(200, $this->message, $block->toArray());

    }

    protected function _blockContent($block_id, $supplemental_factory, $params, $benchmarks, $option_listing_factory){
        $this->load->model('ReportContent/report_block_model');
        $this->load->model('Datasource/db_field_model');
        $this->load->model('ReportContent/report_data_model');
        $this->load->model('Datasource/db_table_model');
        $data_handler = new DataHandler($this->report_data_model, $benchmarks);
        $db_table_factory = new DbTableFactory($this->db_table_model);

        //we want params for the child block, not the parent
        $key_fields = $this->block_model->getKeysByBlock($block_id);
//var_dump($key_fields, $params); die;
        //$params = array_intersect_key($params, array_flip( $key_fields));

        //load factories for block content
        $report_factory = new ReportFactory($this->report_block_model, $this->db_field_model, $this->filters, $supplemental_factory, $data_handler, $db_table_factory);
        $this->load->model('Forms/setting_form_model');//, null, false, ['user_id'=>$this->session->userdata('user_id'), 'herd_code'=>$this->session->userdata('herd_code')]);
        $setting_form_factory = new SettingsFormDisplayFactory($this->setting_form_model, $supplemental_factory, $params + ['herd_code'=>$this->session->userdata('herd_code'), 'user_id'=>$this->session->userdata('user_id')]);

        $this->load->model('Forms/Data_entry_model');//, null, false, $params + ['herd_code'=>$this->session->userdata('herd_code')]);
        $entry_form_factory = new FormDisplayFactory($this->Data_entry_model, $supplemental_factory,$report_factory, $option_listing_factory, $setting_form_factory, $this->block_model, $params + ['herd_code'=>$this->session->userdata('herd_code')]);

        //$this->load->model('Listings/herd_options_model');
        //$option_listing_factory = new ListingFactory($this->herd_options_model, $params + ['herd_code'=>$this->session->userdata('herd_code')]);

        //create block content
        $report = $report_factory->getByBlock($block_id);
        if(!empty($report)){
            return $report;
        }
        $setting_forms = $setting_form_factory->getByBlock($block_id);
        if(!empty($setting_forms)){
            return array_values($setting_forms)[0];
        }
        $entry_forms = $entry_form_factory->getByBlock($block_id, $this->session->userdata('herd_code'));
        if(!empty($entry_forms)){
            return array_values($entry_forms)[0];
        }
        //$serial_num = isset($params['serial_num']) ? $params['serial_num'] : null;
        $listings = $option_listing_factory->getByBlock($block_id, $params);
        if(!empty($listings)){
            return array_values($listings)[0];
        }

        throw new \Exception('No content found for requested page block.');
    }

}
