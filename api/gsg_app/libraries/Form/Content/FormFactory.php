<?php

namespace myagsource\Form\Content;

require_once(APPPATH . 'libraries/Settings/SettingForm.php');
require_once(APPPATH . 'libraries/Settings/SettingFormControl.php');
require_once(APPPATH . 'libraries/Form/Content/Form.php');
require_once(APPPATH . 'libraries/Form/Content/Control/FormControl.php');
require_once(APPPATH . 'libraries/Form/Content/SubForm.php');
require_once(APPPATH . 'libraries/Form/iFormFactory.php');
require_once(APPPATH . 'libraries/Validation/Input/Validator.php');
require_once(APPPATH . 'models/Forms/iForm_Model.php');

use \myagsource\Form\iFormFactory;
use \myagsource\Form\Content\SubForm;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\Form\Content\Control\FormControl;
use myagsource\Validation\Input\Validator;

/**
 * A factory for form objects
 * 
 * 
 * @name Forms
 * @author ctranel
 * 
 *        
 */
class FormFactory implements iFormFactory{
	/**
	 * datasource_blocks
	 * @var form_model
	 **/
	protected $datasource;

    /**
     * params for identify data that populates form
     * @var array
     **/
    protected $key_params;

    function __construct(\iForm_Model $datasource, SupplementalFactory $supplemental_factory = null, $key_params = null) {//, \db_field_model $datasource_dbfield
		$this->datasource = $datasource;
        $this->key_params = $key_params;
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
    public function getByPage($page_id, $herd_code){
        $forms = [];
        $results = $this->datasource->getFormsByPage($page_id);
        if(empty($results)){
            return [];
        }

        foreach($results as $r){
            $forms[$r['list_order']] = $this->createForm($r, $herd_code);
        }
        return $forms;
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

        //this function depends on an existing record
        $control_data = $this->datasource->getFormControlData($form_data['form_id'], $this->key_params, $ancestor_form_ids);

        $fc = [];
        if(is_array($control_data) && !empty($control_data) && is_array($control_data[0])){
            foreach($control_data as $d){
                $validators = null;
                if(isset($d['validators'])){
                    $validators = [];
                    $valids = explode('|', $d['validators']);
                    foreach($valids as $v){
                        list($name, $comparison_value) = explode(':', $v);
                        $validators[] = new Validator($name, $comparison_value);
                    }
                }

                $s = isset($subforms[$d['name']]) ? $subforms[$d['name']] : null;
                $options = null;
                if(strpos($d['control_type'], 'lookup') !== false){
                    $options = $this->getLookupOptions($d['id'], $d['control_type']);
                }
                $fc[] = new FormControl($d, $validators, $options, $s);
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

    /* -----------------------------------------------------------------
*  getLookupOptions

*  Returns all options

*  @since: version 1
*  @author: ctranel
*  @date: Jun 26, 2014
*  @param: string setting name
*  @return array of key=>value pairs
*  @throws:
* -----------------------------------------------------------------
*/
    protected function getLookupOptions($control_id, $control_type){
        if(strpos($control_type, 'lookup') === false){
            return false;
        }

        if(strpos($control_type, 'data_lookup') !== false){
            $options = $this->datasource->getLookupOptions($control_id);
        }
        $herd_code = isset($this->key_params['herd_code']) ? $this->key_params['herd_code'] : null;
        if(strpos($control_type, 'herd_lookup') !== false && isset($herd_code)){
            $options = $this->datasource->getHerdLookupOptions($control_id, $herd_code);
        }
        $serial_num = isset($this->key_params['serial_num']) ? $this->key_params['serial_num'] : null;
        if(strpos($control_type, 'animal_lookup') !== false && isset($herd_code) && isset($serial_num)){
            $options = $this->datasource->getAnimalLookupOptions($control_id, $herd_code, $serial_num);
        }
        $ret = [];

        if(isset($options) && is_array($options) && !empty($options)){
            $keys = array_keys($options[0]);
            foreach($options as $o){
                //if(isset($o['value'])){
                $ret[] = ['value' => $o[$keys[0]], 'text' => $o[$keys[1]]];
                //}
                //else{
                //    $this->options[] = ['value' => $o['key_value'], 'text' => $o['description']];
                //}
            }
        }

        return $ret;
    }
}
