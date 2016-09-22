<?php

namespace myagsource\Form\Content;

require_once(APPPATH . 'libraries/Settings/SettingForm.php');
require_once(APPPATH . 'libraries/Settings/SettingFormControl.php');
require_once(APPPATH . 'libraries/Form/Content/Form.php');
require_once(APPPATH . 'libraries/Form/Content/Control/FormControl.php');
require_once(APPPATH . 'libraries/Form/Content/SubForm.php');
require_once(APPPATH . 'models/Forms/iForm_Model.php');

use \myagsource\Form\Content\SubForm;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\Settings\SettingForm;
use \myagsource\Settings\SettingFormControl;
use \myagsource\Form\Content\Control\FormControl;

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

	function __construct(\iForm_Model $datasource, SupplementalFactory $supplemental_factory = null) {//, \db_field_model $datasource_dbfield
		$this->datasource = $datasource;
	}
	
	/*
	 * getForm
	 * 
	 * @param int form id
	 * @author ctranel
	 * @returns \myagsource\Form\Form
	 */
	public function getForm($form_id, $herd_code){
		$results = $this->datasource->getFormById($form_id);
		if(empty($results)){
			return false;
		}

		return $this->createForm($results[0], $herd_code);
	}

	/*
    * createForm
    *
    * @param array of form data
    * @param string herd code
    * @param array of ints ancestor_form_ids
    * @author ctranel
    * @returns Array of Forms
    */
    protected function createForm($form_data, $herd_code, $ancestor_form_ids = null){
        $subforms = $this->getSubForms($form_data['form_id'], $herd_code, $ancestor_form_ids);
        $control_data = $this->datasource->getFormControlData($form_data['form_id'], $ancestor_form_ids);

        $fc = [];
        if(is_array($control_data) && !empty($control_data) && is_array($control_data[0])){
            foreach($control_data as $d){
                $s = isset($subforms[$d['name']]) ? $subforms[$d['name']] : null;
                $fc[] = new FormControl($this->datasource, $d, $s);
            }
        }
        return new Form($form_data['form_id'], $this->datasource, $fc, $form_data['dom_id'], $form_data['action'], $herd_code);
    }

    protected function getSubForms($parent_form_id, $herd_code, $ancestor_form_ids = null){
        $results = $this->datasource->getSubFormsByParentId($parent_form_id); //would return control-name-indexed array
        if(empty($results)){
            return false;
        }

        if(is_array($ancestor_form_ids)){
            $ancestor_form_ids = $ancestor_form_ids + [$parent_form_id];
        }
        else{
            $ancestor_form_ids = [$parent_form_id];
        }

        $subforms = [];
        foreach($results as $k => $r){
            $form = $this->createForm($r, $herd_code, $ancestor_form_ids);
            $subforms[$r['form_control_name']][] = new SubForm($r['operator'], $r['operand'], $form);
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
	
    /*
     * createSettingForm
     *
     * @param array form data
	 * @param int user id
	 * @param string herd_code
     * @author ctranel
     * @returns \myagsource\Settings\SettingForm
     */
    protected function createSettingForm($form_data, $user_id, $herd_code, $ancestor_form_ids = null){
        $subforms = $this->getSettingSubForms($form_data['form_id'], $user_id, $herd_code, $ancestor_form_ids);
        $control_data = $this->datasource->getFormControlData($form_data['form_id'], $ancestor_form_ids);

        $fc = [];
        if(is_array($control_data) && !empty($control_data) && is_array($control_data[0])){
            foreach($control_data as $d){
                $sf = isset($subforms[$d['name']]) ? $subforms[$d['name']] : null;
                $fc[] = new SettingFormControl($this->datasource, $d, $sf);
            }
        }
        return new SettingForm($form_data['form_id'], $this->datasource, $fc, $form_data['dom_id'], $form_data['action'],$user_id, $herd_code);
    }

    protected function getSettingSubForms($parent_form_id, $user_id, $herd_code, $ancestor_form_ids = null){
        $results = $this->datasource->getSubFormsByParentId($parent_form_id); //would return control-name-indexed array

        if(empty($results)){
            return false;
        }

        if(is_array($ancestor_form_ids)){
            $ancestor_form_ids = $ancestor_form_ids + [$parent_form_id];
        }
        else{
            $ancestor_form_ids = [$parent_form_id];
        }

        $subforms = [];
        foreach($results as $k => $r){
            $form = $this->createSettingForm($r, $user_id, $herd_code, $ancestor_form_ids);
            $subforms[$r['form_control_name']][] = new SubForm($r['operator'], $r['operand'], $form);
        }
        return $subforms;
    }

    /*
     * getByPage
     *
     * @param int page_id
         * @param string herd_code
         * @param int user_id
     * @author ctranel
     * @returns \myagsource\Page\Content\FormBlock\FormBlock[]
     */
    public function getByPage($page_id, $herd_code = null, $user_id = null){
        $forms = [];
        $results = $this->datasource->getFormsByPage($page_id);
        if(empty($results)){
            return [];
        }

        foreach($results as $r){
            $forms[$r['list_order']] = $this->dataToObject($r, $herd_code, $user_id);
        }
        return $forms;
    }

    /*
    * dataToObject
    *
    * @param array of form data
     * @param string herd_code
     * @param int user_id
    * @author ctranel
    * @returns Array of Forms
    */
    protected function dataToObject($form_data, $herd_code = null, $user_id = null){
        if(strpos($form_data['display_type'], 'setting') !== false){
            $f = $this->getSettingForm($form_data['form_id'], $user_id, $herd_code);
        }
        elseif(strpos($form_data['display_type'], 'entry') !== false){
            $f = $this->getForm($form_data['form_id'], $herd_code);
        }
        return $f;
    }
}
