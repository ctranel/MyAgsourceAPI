<?php

namespace myagsource\Form\Content;

require_once(APPPATH . 'libraries/Settings/SettingForm.php');
require_once(APPPATH . 'libraries/Form/Content/Form.php');
require_once(APPPATH . 'libraries/Form/Content/SubForm.php');

use \myagsource\Form\Content\SubForm;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\Settings\SettingForm;
use \myagsource\Settings\SettingFormControl;

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
	 * getForm
	 * 
	 * @param int form id
	 * @author ctranel
	 * @returns \myagsource\Form\Form
	 */
	public function getForm($form_id){
		$results = $this->datasource->getFormById($form_id);
		if(empty($results)){
			return false;
		}

		return $this->createForm($results[0]);
	}

	/*
    * createForm
    *
    * @param array of form data
    * @author ctranel
    * @returns Array of Forms
    */
    protected function createForm($form_data){
        $subforms = $this->getSubForms($form_data['id']);

        $control_data = $this->datasource->getFormControlData($form_data['id']);

        $fc = [];
        if(is_array($control_data) && !empty($control_data) && is_array($control_data[0])){
            foreach($control_data as $d){
                $fc[] = new FormControl($this->datasource, $d, $subforms);
            }
        }
        return new Form($this->datasource, $fc, $form_data['dom_id'], $form_data['action']);
    }

    protected function getSubForms($parent_form_id){
        $results = $this->datasource->getSubFormsByParentId($parent_form_id); //would return control-name-indexed array
        if(empty($results)){
            return false;
        }

        $subforms = [];
        foreach($results as $k => $r){
            $form = $this->createForm($r);
            $subforms[$r['parent_control_name']] = new SubForm($r['operator'], $r['operand'], $form);
        }

        return $subforms;
    }

    /*
     * getSettingForm
     *
     * @param int form id
     * @param int user id
     * @param string herd_code
     * @author ctranel
     * @returns \myagsource\Settings\SettingForm
     */
	public function getSettingForm($form_id, $user_id, $herd_code){
		$results = $this->datasource->getFormById($form_id);
		if(empty($results)){
			return false;
		}

		return $this->createSettingForm($results[0], $user_id, $herd_code);
	}
	
//	public function getSettingSubForms($control_name){
//        $results = $this->datasource->getFormByControlName($control_name);
//    }

    /*
     * createSettingForm
     *
     * @param array form data
	 * @param int user id
	 * @param string herd_code
     * @author ctranel
     * @returns \myagsource\Settings\SettingForm
     */
    protected function createSettingForm($form_data, $user_id, $herd_code){
        $subforms = $this->getSettingSubForms($form_data['form_id'], $user_id, $herd_code);
        $control_data = $this->datasource->getFormControlData($form_data['form_id']);

        $fc = [];
        if(is_array($control_data) && !empty($control_data) && is_array($control_data[0])){
            foreach($control_data as $d){
                $sf = isset($subforms[$d['name']]) ? $subforms[$d['name']] : null;
                $fc[] = new SettingFormControl($this->datasource, $d, $sf);
            }
        }
        return new SettingForm($this->datasource, $fc, $form_data['dom_id'], $form_data['action'],$user_id, $herd_code);
    }

    protected function getSettingSubForms($parent_form_id, $user_id, $herd_code){
        $results = $this->datasource->getSubFormsByParentId($parent_form_id); //would return control-name-indexed array

        if(empty($results)){
            return false;
        }
        
        $subforms = [];
        foreach($results as $k => $r){
            $form = $this->createSettingForm($r, $user_id, $herd_code);
            $subforms[$r['form_control_name']][] = new SubForm($r['operator'], $r['operand'], $form);
        }
        return $subforms;
    }
}
