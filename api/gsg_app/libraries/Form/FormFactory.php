<?php

namespace myagsource\Form;

require_once(APPPATH . 'libraries/Settings/SettingForm.php');
require_once(APPPATH . 'libraries/Settings/SettingFormControls.php');
require_once(APPPATH . 'libraries/Form/Form.php');
require_once(APPPATH . 'libraries/Form/Control/FormControls.php');

use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\Settings\SettingFormControls;
use \myagsource\Form\FormControls;

/**
 * A factory for form objects
 * 
 * 
 * @name Forms
 * @author ctranel
 * 
 *        
 */
class FormFactory {
	/**
	 * datasource_blocks
	 * @var form_model
	 **/
	protected $datasource;

	function __construct(\setting_model $datasource, SupplementalFactory $supplemental_factory = null) {//, \db_field_model $datasource_dbfield
		$this->datasource = $datasource;
	}
	
	/*
	 * getObject
	 * 
	 * @param int form id
	 * @author ctranel
	 * @returns \myagsource\Form\Form
	 */
	public function getObject($form_id){
		$results = $this->datasource->getFormById($form_id);
		if(empty($results)){
			return false;
		}

		return $this->dataToObject($results[0]);
	}

	/*
    * dataToObject
    *
    * @param array of form data
    * @author ctranel
    * @returns Array of Forms
    */
    protected function dataToObject($form_data){
        $fc = new FormControls($this->datasource, $form_data['form_id']);
        return new Form($this->datasource, $fc, $form_data['dom_id'], $form_data['action']);
    }

	/*
	 * getSettingForm
	 *
	 * @param int form id
	 * @author ctranel
	 * @returns \myagsource\Settings\SettingForm
	 */
	public function getSettingForm($form_id, $user_id, $herd_code){
		$results = $this->datasource->getFormById($form_id);
		if(empty($results)){
			return false;
		}

		$fc = new SettingFormControls($this->datasource, $results[0]['form_id']);
		return new \myagsource\Settings\SettingForm($this->datasource, $fc, $results[0]['dom_id'], $results[0]['action'],$user_id, $herd_code);
	}
}
