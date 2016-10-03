<?php
//namespace myagsource;

require_once(APPPATH . 'controllers/dpage.php');
require_once APPPATH . 'libraries/Settings/SessionSettings.php';
require_once(APPPATH . 'libraries/Form/Content/FormFactory.php');

use \myagsource\Form\Content\FormFactory;

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
		//$this->form_validation->set_rules('herd_code', 'Herd', 'required|max_length[8]');
		//$this->form_validation->set_rules('herd_code_fill', 'Type Herd Code');
        $this->load->model('Forms/setting_model', null, false, ['user_id'=>$this->session->userdata('user_id'), 'herd_code'=>$this->session->userdata('herd_code')]);
        $form_factory = new FormFactory($this->setting_model);

        $form = $form_factory->getSettingForm($form_id, $this->session->userdata('user_id'), $this->session->userdata('herd_code'));
        $input = $this->input->userInputArray();

		if($this->form_validation->run_input() === true){
            try{
                //get form object
                //@todo: split form core from form display (core would be included in display), no need for web content or supplemental here
                //supplemental factory
                $this->load->model('supplemental_model');
                $supplemental_factory = new SupplementalFactory($this->supplemental_model, site_url());

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
		$this->load->library('herds');
		$this->load->library('form_validation');
        $this->load->model('Forms/data_entry_model', null, false, $params + ['herd_code'=>$this->session->userdata('herd_code')]);
        $form_factory = new FormFactory($this->data_entry_model);

        $form = $form_factory->getForm($form_id, $this->session->userdata('herd_code'));

        $this->sendResponse(200, $this->message, $form->toArray());
	}

    /**
     * @method get_entry() - setting submission.
     *
     * @param int form id
     * @param string json data (key data for retrieving form)
     * @access	public
     * @return	void
     */
    function put_entry($form_id, $json_data = null){
        $params = [];
        if(isset($json_data)) {
            $params = (array)json_decode(urldecode($json_data));
        }
        //validate form input
        $this->load->library('herds');
        $this->load->library('form_validation');
        $this->load->model('Forms/data_entry_model', null, false, $params + ['herd_code'=>$this->session->userdata('herd_code')]);
        $form_factory = new FormFactory($this->data_entry_model);

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
                $this->load->model('supplemental_model');
                $supplemental_factory = new SupplementalFactory($this->supplemental_model, site_url());

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
        $this->sendResponse(400, new ResponseMessage(validation_errors(), 'error'));
    }
}
