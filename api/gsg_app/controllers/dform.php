<?php
//namespace myagsource;

require_once(APPPATH . 'controllers/dpage.php');
require_once(APPPATH . 'libraries/Settings/Settings.php');
require_once(APPPATH . 'libraries/Settings/Form/SettingsFormSubmissionFactory.php');
require_once(APPPATH . 'libraries/Settings/Form/SettingsFormDisplayFactory.php');
require_once(APPPATH . 'libraries/Form/Content/FormSubmissionFactory.php');

use \myagsource\Datasource\DbObjects\DbTableFactory;
use \myagsource\Settings\Form\SettingsFormSubmissionFactory;
use \myagsource\Settings\Form\SettingsFormDisplayFactory;
use \myagsource\Form\Content\FormSubmissionFactory;
use \myagsource\Form\Content\FormDisplayFactory;
use \myagsource\Listings\Content\ListingFactory;
use \myagsource\Report\Content\ReportFactory;
use \myagsource\Api\Response\ResponseMessage;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\DataHandler;
use \myagsource\Benchmarks\Benchmarks;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class dform extends dpage {
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
	 * @method settings() - setting submission.
	 *
	 * @access	public
	 * @return	void
	 */
	function settings($form_id){
		//validate form input
        $this->load->library('herds');
		$this->load->library('form_validation');
        try{
            //$this->form_validation->set_rules('herd_code', 'Herd', 'required|max_length[8]');
            //$this->form_validation->set_rules('herd_code_fill', 'Type Herd Code');

            //get form object
            //@todo: split form core from form display (core would be included in display), no need for web content or supplemental here
            $this->load->model('Forms/setting_form_model');//, null, false, ['user_id'=>$this->session->userdata('user_id'), 'herd_code'=>$this->session->userdata('herd_code')]);
            $setting_form_factory = new SettingsFormSubmissionFactory($this->setting_form_model, ['herd_code'=>$this->session->userdata('herd_code'), 'user_id'=>$this->session->userdata('user_id')]);

            $form = $setting_form_factory->getForm($form_id, $this->session->userdata('herd_code'), $this->session->userdata('user_id'));
            $input = $this->input->userInputArray();
        }
        catch(Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }

		if($this->form_validation->run_input() === true){
            try{
                $form->write($input);

                $resp_msg = [];
                //$msg = $this->_loadSessionHerd($tmp_arr[0]['herd_code']);
                //if(!empty($msg)){
                $resp_msg = new ResponseMessage('Form submission successful', 'message');
                //}
                //$this->_record_access(2); //2 is the page code for herd change
/*                $this->load->model('web_content/navigation_model');
                $navigation = new Navigation($this->navigation_model, $this->herd, $this->permissions->permissionsList());
                $payload = ['nav' => $navigation->toArray('DHI')]; */
                $this->sendResponse(200, $resp_msg);
            }
            catch(Exception $e){
                $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
            }
		}
		elseif(!$input || empty($input)){
            //$form->;
        }
        $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
	}

	/**
	 * @method get_entry() - setting submission.
	 *
     * @param int form id
     * @param string json data (key data for retrieving form)
	 * @access	public
	 * @return	void
	 */
	function get_entry($form_id, $json_data = null){
        $params = [];
        if(isset($json_data)) {
            $params = (array)json_decode(urldecode($json_data));
        }

		try{
            //validate form input
            $this->load->model('supplemental_model');
            $supplemental_factory = new SupplementalFactory($this->supplemental_model, site_url());
            $this->load->library('herds');
            $this->load->library('form_validation');
            $this->load->model('Forms/data_entry_model');

            $form_factory = new FormDisplayFactory($this->data_entry_model, $supplemental_factory, $params + ['herd_code'=>$this->session->userdata('herd_code')]);

            $form = $form_factory->getFormDisplay($form_id, $this->session->userdata('herd_code'));
        }
        catch(Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }

        $this->sendResponse(200, $this->message, $form->toArray());
	}

    /**
     * @method get_subform_entry()
     *
     * @param int form id
     * @param string json data (key data for retrieving form)
     * @access	public
     * @return	void
     */
    function get_subform_entry($form_id, $json_data = null){
        $params = [];
        if(isset($json_data)) {
            $params = (array)json_decode(urldecode($json_data));
        }

        try{
            $display_data = $this->block_model->getDisplayDataByContent('form', $form_id);
            //validate form input
            $this->load->model('supplemental_model');
            $supplemental_factory = new SupplementalFactory($this->supplemental_model, site_url());
            $this->load->library('herds');
            $this->load->library('form_validation');

            if($display_data['display_type_id'] !== 6 && $display_data['display_type_id'] !== 7){
                $this->sendResponse(400, new ResponseMessage("Requested non-form data in form context.", 'error'));
            }

            if($display_data['display_type_id'] === 6) { //setting form
                $this->load->model('Forms/setting_form_model');//, null, false, ['user_id'=>$this->session->userdata('user_id'), 'herd_code'=>$this->session->userdata('herd_code')]);
                $form_factory = new SettingsFormDisplayFactory($this->setting_form_model, $supplemental_factory, ['user_id'=>$this->session->userdata('user_id'), 'herd_code'=>$this->session->userdata('herd_code')]);
                $form = $form_factory->getSubformDisplay($form_id, $this->session->userdata('herd_code'));
            }
            elseif($display_data['display_type_id'] === 7){ //display_form
                $this->load->model('Forms/data_entry_model');
                $form_factory = new FormDisplayFactory($this->data_entry_model, $supplemental_factory, $params + ['herd_code'=>$this->session->userdata('herd_code')]);
                $form = $form_factory->getSubformDisplay($form_id, $this->session->userdata('herd_code'));
            }


        }
        catch(\Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }

        $this->sendResponse(200, $this->message, $form->toArray());
    }

    /**
     * @method get_entry() - setting submission.
     *
     * @param int form id
     * @param string json data (form data)
     * @access	public
     * @return	void
     */
    function put_entry($form_id, $json_data = null){
        $params = [];
        if(isset($json_data)) {
            $params = (array)json_decode(urldecode($json_data));
        }

        $input = $this->input->userInputArray();

            //validate form input
        //$this->load->model('supplemental_model');
        //$supplemental_factory = new SupplementalFactory($this->supplemental_model, site_url());
        $this->load->library('herds');
        $this->load->library('form_validation');

        try{
            $form_factory = $this->_formFactory($params + ['herd_code'=>$this->session->userdata('herd_code')], $input);
            $form = $form_factory->getForm($form_id);
        }
        catch(Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
        //$this->form_validation->set_rules('herd_code', 'Herd', 'required|max_length[8]');
        //$this->form_validation->set_rules('herd_code_fill', 'Type Herd Code');


        if(!$input || empty($input)){
            $this->sendResponse(200, $this->message, $form->toArray());
        }
        if($this->form_validation->run_input() === true){
            try{
                //add field values for logging
                $input['logid'] = $this->session->userdata('user_id');
                //DB functions use server time, will do the same to be consistent
                $date = new DateTime("now");
                $input['logdttm'] = $date->format("Y-m-d\TH:i:s");
                $parent_control_id = null;

                //control_id indicates that the form is from a herd option "add" button
                //return data to add entry to triggering option list
                if(isset($input['parent_control_id'])){
                    $parent_control_id = $input['parent_control_id'];
                    unset($input['parent_control_id']);
                }

                $entity_keys = $form->write($input);

                //if subcontent = listing,
                //$form->writeSubContent()

                $resp_msg = new ResponseMessage('Form submission successful', 'message');
                //$this->_record_access(2); //2 is the page code for herd change

                if($parent_control_id){
                    //use the inserted value
                    $lookup_keys = $form_factory->getLookupKeys($parent_control_id);
                    $value = isset($entity_keys[$lookup_keys['value_column']]) ? $entity_keys[$lookup_keys['value_column']] : $input[$lookup_keys['value_column']];
                    $label = $input[$lookup_keys['desc_column']];
                    $label = isset($label) && !empty($label) ? $label : $value;
                    $this->sendResponse(200, $resp_msg, ['option' => [$value => $label]]);
                }

                $this->sendResponse(200, $resp_msg, ['identity_keys' => $entity_keys]);
            }
            catch(\Exception $e){
                $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
            }
        }
        $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
    }

    /**
     * @method put_batch()
     *
     * @param int form id
     * @param string json data (key data)
     * @access	public
     * @return	void
     */
    function put_batch($form_id, $json_data = null) {
        $params = [];
        if(isset($json_data)) {
            $params = (array)json_decode(urldecode($json_data));
        }

        $input = $this->input->userInputArray();

        //validate form input
        $this->load->library('herds');
        $this->load->library('form_validation');
        $form_factory = $this->_formFactory($params + ['herd_code'=>$this->session->userdata('herd_code')], $input);

        $form = $form_factory->getForm($form_id);


        if($this->form_validation->run_input() === true){
            try{
                //get form object

                //add field values for logging
                $input['logid'] = $this->session->userdata('user_id');
                $date = new DateTime("now");
                $input['logdttm'] = $date->format("Y-m-d H:i:s");

                $entity_keys = $form->writeBatch($input);

                $resp_msg = [];
                //$msg = $this->_loadSessionHerd($tmp_arr[0]['herd_code']);
                //if(!empty($msg)){
                $resp_msg = new ResponseMessage('Form submission successful', 'message');
                //}
                //$this->_record_access(2); //2 is the page code for herd change
                /*                $this->load->model('web_content/navigation_model');
                                $navigation = new Navigation($this->navigation_model, $this->herd, $this->permissions->permissionsList());
                                $payload = ['nav' => $navigation->toArray('DHI')]; */
                $this->sendResponse(200, $resp_msg, ['identity_keys' => $entity_keys]);
            }
            catch(Exception $e){
                $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
            }
        }
        $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
    }

    /**
     * @method delete_entry() - setting submission.
     *
     * @param int form id
     * @param string json data (key data)
     * @access	public
     * @return	void
     */
    function delete_entry($form_id, $json_data = null){
        if(!isset($json_data)){
            $this->sendResponse(400, new ResponseMessage('No criteria sent for deletion.', 'error'));
        }
        $input = (array)json_decode(urldecode($json_data));

        $form_factory = $this->_formFactory($input + ['herd_code'=>$this->session->userdata('herd_code')], $input);

        $form = $form_factory->getForm($form_id);
            try{
                $form->delete($input);

                $resp_msg = new ResponseMessage('The record was removed', 'message');

                $this->sendResponse(200, $resp_msg);
            }
            catch(Exception $e){
                $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
            }
    }

    /**
     * @method delete_batch() - setting submission.
     *
     * @param int form id
     * @param string json data (key data)
     * @access	public
     * @return	void
     */
    function delete_batch($form_id, $json_data = null){
        if(!isset($json_data)){
            $this->sendResponse(400, new ResponseMessage('No criteria sent for deletion.', 'error'));
        }
        $input = (array)json_decode(urldecode($json_data));

        $form_factory = $this->_formFactory($input + ['herd_code'=>$this->session->userdata('herd_code')]);

        $form = $form_factory->getForm($form_id);
        try{
            $form->deleteBatch($input);

            $resp_msg = new ResponseMessage('The record was removed', 'message');

            $this->sendResponse(200, $resp_msg);
        }
        catch(Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
    }

    /**
     * @method deactivate() - setting submission.
     *
     * @param int form id
     * @param string json data (key data)
     * @access	public
     * @return	void
     */
    function deactivate($form_id, $json_data = null){
        if(!isset($json_data)){
            $this->sendResponse(400, new ResponseMessage('No criteria sent for deletion.', 'error'));
        }
        $input = (array)json_decode(urldecode($json_data));

        $form_factory = $this->_formFactory($input + ['herd_code'=>$this->session->userdata('herd_code')], $input);

        $form = $form_factory->getForm($form_id);

        try{
            //add field values for logging
            $input['logid'] = $this->session->userdata('user_id');
            //DB functions use server time, will do the same to be consistent
            $date = new DateTime("now");
            $input['logdttm'] = $date->format("Y-m-d\TH:i:s");

            $form->deactivate($input);

            $resp_msg = new ResponseMessage('The record was removed', 'message');

            $this->sendResponse(200, $resp_msg);
        }
        catch(Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
    }

    /**
     * @method activate() - setting submission.
     *
     * @param int form id
     * @param string json data (key data)
     * @access	public
     * @return	void
     */
    function activate($form_id, $json_data = null){
        $params = [];
        if(isset($json_data)) {
            $params = (array)json_decode(urldecode($json_data));
        }

        $input = $this->input->userInputArray();

        $form_factory = $this->_formFactory($params, $input);

        $form = $form_factory->getForm($form_id);

        try{
            //add field values for logging
            $input['logid'] = $this->session->userdata('user_id');
            //DB functions use server time, will do the same to be consistent
            $date = new DateTime("now");
            $input['logdttm'] = $date->format("Y-m-d\TH:i:s");

            $form->activate($input);

            $resp_msg = new ResponseMessage('The record was activated', 'message');

            $this->sendResponse(200, $resp_msg);
        }
        catch(Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
    }

    public function animal_options($herd_code, $serial_num, $control_id){
        $this->load->model('Forms/data_entry_model');
        try {
            $form_factory = new FormDisplayFactory($this->data_entry_model, null, ['herd_code'=>$herd_code, 'serial_num'=>$serial_num]);

            $options = $form_factory->getControlOptionsById($control_id);

            $this->sendResponse(200, null, ['options' => $options]);
        }
        catch(Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
    }

    protected function _formFactory($key_data, $form_data = null){
$page_id = 105;


//$this->load->model('web_content/block_model');
//$key_fields = $this->block_model->getKeysByContentId($form_id);



        $this->filters = $this->_filters($page_id, $key_data);
        $benchmarks = $this->_benchmarks();
        $supplemental_factory = $this->_supplementalFactory();

        //load factories for block content

        $this->load->model('Datasource/db_table_model');
        $db_table_factory = new DbTableFactory($this->db_table_model);

        $this->load->model('ReportContent/report_data_model');
        $data_handler = new DataHandler($this->report_data_model, $benchmarks, $this->herd->isMetric());

        $this->load->model('Datasource/db_field_model');
        $this->load->model('ReportContent/report_block_model');
        $report_factory = new ReportFactory($this->report_block_model, $this->db_field_model, $this->filters, $supplemental_factory, $data_handler, $db_table_factory);

        $this->load->model('Listings/herd_options_model');
        $option_listing_factory = new ListingFactory($this->herd_options_model, $key_data + ['herd_code'=>$this->session->userdata('herd_code')]);

        $this->load->model('Forms/setting_form_model');//, null, false, ['user_id'=>$this->session->userdata('user_id'), 'herd_code'=>$this->session->userdata('herd_code')]);
        $setting_form_factory = new SettingsFormSubmissionFactory($this->setting_form_model, $key_data + ['herd_code'=>$this->session->userdata('herd_code'), 'user_id'=>$this->session->userdata('user_id')]);

        $this->load->model('web_content/block_model');
        $this->load->model('Forms/Data_entry_model');//, null, false, $params + ['herd_code'=>$this->session->userdata('herd_code')]);
        $entry_form_factory = new FormSubmissionFactory($this->Data_entry_model, $key_data + ['herd_code'=>$this->session->userdata('herd_code')], $form_data, $supplemental_factory, $report_factory, $option_listing_factory, $setting_form_factory, $this->block_model);

        return $entry_form_factory;
    }
}
