<?php

namespace myagsource\Settings\Form;

require_once(APPPATH . 'libraries/Settings/SettingForm.php');
require_once(APPPATH . 'libraries/Settings/SettingFormControl.php');
require_once(APPPATH . 'libraries/Form/Content/Control/FormControlGroup.php');
require_once(APPPATH . 'libraries/Form/Content/SubForm.php');
require_once(APPPATH . 'libraries/Form/Content/SubBlock.php');
require_once(APPPATH . 'libraries/Form/Content/SubFormShell.php');
require_once(APPPATH . 'libraries/Form/Content/SubContentCondition.php');
require_once(APPPATH . 'libraries/Form/Content/SubContentConditionGroup.php');
require_once(APPPATH . 'libraries/Form/iFormSubmissionFactory.php');
require_once(APPPATH . 'libraries/Validation/Input/FormValidator.php');
require_once(APPPATH . 'libraries/Validation/Input/ControlValidator.php');
require_once(APPPATH . 'models/Forms/iForm_Model.php');

use \myagsource\Form\iFormSubmissionFactory;
use \myagsource\Form\Content\SubForm;
use \myagsource\Form\Content\SubFormShell;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\Settings\SettingForm;
use \myagsource\Settings\SettingFormControl;
use \myagsource\Form\Content\Control\FormControlGroup;
use \myagsource\Validation\Input\FormValidator;
use \myagsource\Validation\Input\ControlValidator;
use \myagsource\Form\Content\SubContentCondition;
use \myagsource\Form\Content\SubContentConditionGroup;

/**
 * A factory for form objects
 * 
 * 
 * @name Forms
 * @author ctranel
 * 
 *        
 */
class SettingsFormDisplayFactory {//} implements iFormSubmissionFactory {
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
    public function getByPage($page_id, $herd_code = null, $user_id = null){
        $forms = [];
        $results = $this->datasource->getFormsByPage($page_id);
        if(empty($results)){
            return [];
        }

        foreach($results as $r){
            $forms[$r['list_order']] = $this->createDisplayForm($r, $user_id, $herd_code);
        }
        return $forms;
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
    public function getByBlock($block_id, $herd_code = null, $user_id = null){
        $results = $this->datasource->getFormByBlock($block_id);
        if(empty($results)){
            return [];
        }

        $r = $results[0];
        $form = $this->createDisplayForm($r, $user_id, $herd_code);

        return $form;
    }

    /*
     * getForm
     *
     * @param int form id
     * @param int user id
     * @param string herd_code
     * @author ctranel
     * @returns \myagsource\Settings\SettingForm
     */
	public function getFormDisplay($form_id, $herd_code=null, $user_id = null){
		$results = $this->datasource->getFormById($form_id);
		if(empty($results)){
			throw new \Exception('No data found for requested form.');
		}
		return $this->createDisplayForm($results[0], $user_id, $herd_code);
	}

    /*
     * getSubformDisplay
     *
     * @param int form id
     * @author ctranel
     * @returns \myagsource\Form\Form
     */
    public function getSubformDisplay($form_id, $herd_code){
        $results = $this->datasource->getSubformById($form_id);
        if(empty($results)){
            throw new \Exception('No data found for requested form.');
        }
        return $this->createDisplayForm($results[0], $this->key_params['herd_code'], $this->key_params['user_id']);
    }

    /*
     * createForm
     *
     * @param array form data
	 * @param int user id
	 * @param string herd_code
     * @author ctranel
     * @returns \myagsource\Settings\SettingForm
     */
    protected function createDisplayForm($form_data, $user_id, $herd_code, $ancestor_form_ids = null){
        $subforms = $this->getSubFormShells($form_data['form_id'], $user_id, $herd_code, $ancestor_form_ids);
        $subblocks = $this->getSubBlockShells($form_data['form_id'], $user_id, $herd_code, $ancestor_form_ids);

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
                        $validators[] = new ControlValidator($name, $comparison_value);
                    }
                }

                $sf = isset($subforms[$d['name']]) ? $subforms[$d['name']] : null;
                $b = isset($subblocks[$d['name']]) ? $subblocks[$d['name']] : null;
                $options = null;
                if(strpos($d['control_type'], 'lookup') !== false){
                    $options = $this->getLookupOptions($d['id'], $d['control_type']);
                }
                $fc[] = new SettingFormControl($d, $validators, $options, $sf, $b);
            }
        }
        $control_group = [new FormControlGroup(NULL, 1, $fc)];

        return new SettingForm($form_data['form_id'], $this->datasource, $control_group, $form_data['form_name'], $form_data['dom_id'], $form_data['action'], $this->formValidators($form_data['form_id']),$user_id, $herd_code);
    }

    /*
    * formValidators
    *
    * @param int form id
    * @author ctranel
    * @returns Array of FormValidator objects
    */
    protected function formValidators($form_id){
        if(!isset($form_id)){
            return [];
        }

        $validator_data = $this->datasource->getFormValidatorData($form_id);

        $ret = [];
        foreach($validator_data as $c){
            $ret[] = new FormValidator($c['validator'], $c['subject_control_name'], $c['subject_control_label'], $c['condition_control_name'], $c['condition_control_label'], $c['condition_operator'], $c['condition_value']);
        }

        return $ret;
    }

    /*
    * getSubBlockShells
    *
    * @param int parent form id
    * @param string herd code
    * @param array of ints ancestor_form_ids
    * @author ctranel
    * @returns Array of Forms
    */
    protected function getSubBlockShells($parent_form_id, $user_id, $herd_code, $ancestor_form_ids = null){
        $results = $this->datasource->getSubBlocksByParentId($parent_form_id); //would return control-name-indexed array
        if(empty($results)){
            return false;
        }

        if(is_array($ancestor_form_ids)){
            $ancestor_form_ids = $ancestor_form_ids + [$parent_form_id];
        }
        else{
            $ancestor_form_ids = [$parent_form_id];
        }

        $subblocks = [];
        //get and organize all condition data for form
        $subblock_data = $this->structureSubFormCondData($results, 'block_id');

        //parse each subform separately
        foreach($results as $k => $r){
            if(!isset($subforms[$r['parent_control_name']][$r['block_id']])){
                $subform_groups = $this->extractConditionGroups($subblock_data[$r['parent_control_name']][$r['block_id']]);
                $subblocks[$r['parent_control_name']][$r['block_id']] = new SubBlockShell($subform_groups, $r['block_id'], $r['block_name'], $r['display_type'], $r['subblock_content_id'], $r['list_order']);
            }
        }

        return $subblocks;
    }

    /*
    * getSubFormShells
    *
    * @param int parent form id
    * @param string herd code
    * @param array of ints ancestor_form_ids
    * @author ctranel
    * @returns Array of Forms
    */
    protected function getSubFormShells($parent_form_id, $user_id, $herd_code, $ancestor_form_ids = null){
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
        //get and organize all condition data for form
        $subform_data = $this->structureSubFormCondData($results, 'form_id');

        //parse each subform separately
        foreach($results as $k => $r){
            if(!isset($subforms[$r['parent_control_name']][$r['form_id']])){
                //$form = $this->createForm($r, $herd_code, $ancestor_form_ids);
                $subform_groups = $this->extractConditionGroups($subform_data[$r['parent_control_name']][$r['form_id']]);
                $subforms[$r['parent_control_name']][$r['form_id']] = new SubFormShell($subform_groups, $r['form_id'], $r['list_order']);
            }
        }

        return $subforms;
    }

    /*
    * extractConditionGroups
     *
    *
    * @param array of hierarchical subform condition data
    * @author ctranel
    * @returns Array condition group objects keyed by group id
    */
    protected function extractConditionGroups($condition_data){
        if(!isset($condition_data) || !is_array($condition_data)){
            return;
        }

        $ret = [];

        foreach($condition_data['condition_groups'] as $grp_id => $grp){
            $subgroups = null;
            $conditions = null;

            if(isset($grp['conditions']) && is_array($grp['conditions']) && !empty($grp['conditions'])) {
                foreach($grp['conditions'] as $cond_id => $cond) {
                    //$ret[$cond['form_control_name']][$cond['form_id']][$cond['group_id']][$cond['condition_id']] = new SubContentCondition($cond['operator'], $cond['operand']);
                    $conditions[] = new SubContentCondition($cond['form_control_name'], $cond['operator'], $cond['operand']);
                }
            }
            if (isset($grp['condition_groups']) && is_array($grp['condition_groups']) && !empty($grp['condition_groups'])) {
                $subgroups = $this->extractConditionGroups($grp);


            }
            $ret[$grp_id] = new SubContentConditionGroup($grp['condition_group_operator'], $subgroups, $conditions);
        }

        return $ret;
    }

    /*
    * structureSubFormCondData
     *
     * parses flat data from datasource into a hierarchical structure of condition groups with control name and form id as keys
    *
    * @param array condition data
    * @author ctranel
    * @returns array of hierarchical subform condition data
    */
    protected function structureSubFormCondData($condition_data, $cond_key_field){
        if(!isset($condition_data) || !is_array($condition_data)){
            return;
        }

        $conditions_data = [];

        foreach($condition_data as $k=>$v){
            if(isset($v['condition_group_parent_id']) && !empty($v['condition_group_parent_id'])){
                $parent_id = $v['condition_group_parent_id'];
                $v['condition_group_parent_id'] = null;
                $conditions_data[$v['parent_control_name']][$v[$cond_key_field]]['condition_groups'][$parent_id]['condition_groups'][$v['condition_group_id']]['conditions'][$v['condition_id']]
                    = $this->structureSubFormCondData([$v], $v['condition_group_operator'])[$v['parent_control_name']][$v[$cond_key_field]]['condition_groups'][$v['condition_group_id']]['conditions'][$v['condition_id']];
                $conditions_data[$v['parent_control_name']][$v[$cond_key_field]]['condition_groups'][$parent_id]['condition_groups'][$v['condition_group_id']]['condition_group_operator'] = $v['condition_group_operator'];

                //need to get the group operator from the parent group
                $parent_key = array_search($parent_id, array_column($condition_data, 'group_id'));
                $conditions_data[$v['parent_control_name']][$v[$cond_key_field]]['condition_groups'][$parent_id]['condition_group_operator'] = $condition_data[$parent_key]['condition_group_operator'];
            }
            else{
                $conditions_data[$v['parent_control_name']][$v[$cond_key_field]]['condition_groups'][$v['condition_group_id']]['conditions'][$v['condition_id']] = $v;
                $conditions_data[$v['parent_control_name']][$v[$cond_key_field]]['condition_groups'][$v['condition_group_id']]['condition_group_operator'] = $v['condition_group_operator'];
            }
        }

        return $conditions_data;
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
        $user_id = isset($this->key_params['user_id']) ? $this->key_params['user_id'] : null;
        if(strpos($control_type, 'user_lookup') !== false && isset($user_id)){
            $options = $this->datasource->getUserLookupOptions($control_id, $user_id);
        }
        $ret = [];

        if(isset($options) && is_array($options) && !empty($options)){
            $keys = array_keys($options[0]);
            foreach($options as $o){
                //if(isset($o['value'])){
                $ret[] = ['value' => $o[$keys[0]], 'label' => $o[$keys[1]]];
                //}
                //else{
                //    $this->options[] = ['value' => $o['key_value'], 'label' => $o['description']];
                //}
            }
        }

        return $ret;
    }
}
