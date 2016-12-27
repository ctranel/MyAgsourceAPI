<?php
//namespace myagsource;

require_once(APPPATH . 'controllers/dpage.php');
require_once(APPPATH . 'libraries/Settings/Settings.php');
require_once(APPPATH . 'libraries/Settings/Form/SettingsFormFactory.php');
require_once(APPPATH . 'libraries/Form/Content/FormFactory.php');

use \myagsource\Settings\Form\SettingsFormFactory;
use \myagsource\Form\Content\FormFactory;
use \myagsource\Api\Response\ResponseMessage;
use \myagsource\Supplemental\Content\SupplementalFactory;

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
        //supplemental factory
        $this->load->model('supplemental_model');
        $supplemental_factory = new SupplementalFactory($this->supplemental_model, site_url());

        //$this->form_validation->set_rules('herd_code', 'Herd', 'required|max_length[8]');
		//$this->form_validation->set_rules('herd_code_fill', 'Type Herd Code');
        $this->load->model('Forms/setting_form_model');//, null, false, ['user_id'=>$this->session->userdata('user_id'), 'herd_code'=>$this->session->userdata('herd_code')]);
        $setting_form_factory = new SettingsFormFactory($this->setting_form_model, $supplemental_factory, ['user_id'=>$this->session->userdata('user_id'), 'herd_code'=>$this->session->userdata('herd_code')]);

        $form = $setting_form_factory->getForm($form_id, $this->session->userdata('herd_code'), $this->session->userdata('user_id'));
        $input = $this->input->userInputArray();

		if($this->form_validation->run_input() === true){
            try{
                //get form object
                //@todo: split form core from form display (core would be included in display), no need for web content or supplemental here
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

        if(empty(array_filter($params))){
            $this->sendResponse(400, new ResponseMessage("No identifying information received", 'error'));
        }

		//validate form input
        $this->load->model('supplemental_model');
        $supplemental_factory = new SupplementalFactory($this->supplemental_model, site_url());
		$this->load->library('herds');
		$this->load->library('form_validation');
        $this->load->model('Forms/data_entry_model');

        $form_factory = new FormFactory($this->data_entry_model, $supplemental_factory, $params + ['herd_code'=>$this->session->userdata('herd_code')]);

        $form = $form_factory->getForm($form_id, $this->session->userdata('herd_code'));

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
            //validate form input
        //$this->load->model('supplemental_model');
        //$supplemental_factory = new SupplementalFactory($this->supplemental_model, site_url());
        $this->load->library('herds');
        $this->load->library('form_validation');
        $this->load->model('Forms/data_entry_model');//, null, false, $params + ['herd_code'=>$this->session->userdata('herd_code')]);
        $form_factory = new FormFactory($this->data_entry_model, null, $params + ['herd_code'=>$this->session->userdata('herd_code')]);

        $form = $form_factory->getForm($form_id, $this->session->userdata('herd_code'));
        $input = $this->input->userInputArray();
        //$this->form_validation->set_rules('herd_code', 'Herd', 'required|max_length[8]');
        //$this->form_validation->set_rules('herd_code_fill', 'Type Herd Code');


        if(!$input || empty($input)){
            $this->sendResponse(200, $this->message, $form->toArray());
        }
        elseif($this->form_validation->run_input() === true){
            try{
                //get form object
                //@todo: split form core from form display (core would be included in display), no need for web content or supplemental here
                //supplemental factory
                //$this->load->model('supplemental_model');
                //$supplemental_factory = new SupplementalFactory($this->supplemental_model, site_url());

                //add field values for logging
                $input['logID'] = $this->session->userdata('user_id');
                $date = new DateTime("now");
                $input['logdttm'] = $date->format("Y-m-d H:i:s");

                $entity_keys = $form->write($input);

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
     * @method batch_entry()
     *
     * @param int form id
     * @param string json data (key data)
     * @access	public
     * @return	void
     */
    function batch_entry($form_id, $json_data = null) {
        $params = [];
        if(isset($json_data)) {
            $params = (array)json_decode(urldecode($json_data));
        }

        //validate form input
        $this->load->library('herds');
        $this->load->library('form_validation');
        $this->load->model('Forms/data_entry_model');
        $form_factory = new FormFactory($this->data_entry_model, null, $params + ['herd_code'=>$this->session->userdata('herd_code')]);

        $form = $form_factory->getForm($form_id, $this->session->userdata('herd_code'));
        $input = $this->input->userInputArray();


        if($this->form_validation->run_input() === true){
            try{
                //get form object

                //add field values for logging
                $input['logID'] = $this->session->userdata('user_id');
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

        $this->load->model('Forms/data_entry_model');//, null, false, $params + ['herd_code'=>$this->session->userdata('herd_code')]);
        $form_factory = new FormFactory($this->data_entry_model, null, ['herd_code'=>$this->session->userdata('herd_code')]);

        $form = $form_factory->getForm($form_id, $this->session->userdata('herd_code'));

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
     * @method delete_entry() - setting submission.
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

        $this->load->model('Forms/data_entry_model');//, null, false, $params + ['herd_code'=>$this->session->userdata('herd_code')]);
        $form_factory = new FormFactory($this->data_entry_model, null, ['herd_code'=>$this->session->userdata('herd_code')]);

        $form = $form_factory->getForm($form_id, $this->session->userdata('herd_code'));

        try{
            $form->deactivate($input);

            $resp_msg = new ResponseMessage('The record was removed', 'message');

            $this->sendResponse(200, $resp_msg);
        }
        catch(Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
    }

    public function animal_options($herd_code, $serial_num, $control_id){
        $this->load->model('Forms/data_entry_model');
        try {
            $form_factory = new FormFactory($this->data_entry_model, null, ['herd_code'=>$herd_code, 'serial_num'=>$serial_num]);

            $options = $form_factory->getControlOptionsById($control_id);

            $this->sendResponse(200, null, ['options' => $options]);
        }
        catch(Exception $e){
            $this->sendResponse(500, new ResponseMessage($e->getMessage(), 'error'));
        }
    }
}
