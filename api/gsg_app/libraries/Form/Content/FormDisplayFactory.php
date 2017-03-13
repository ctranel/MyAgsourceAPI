<?php
namespace myagsource\Form\Content;

require_once(APPPATH . 'libraries/Form/Content/Form.php');
require_once(APPPATH . 'libraries/Form/Content/Control/FormControl.php');
require_once(APPPATH . 'libraries/Form/Content/SubForm.php');
require_once(APPPATH . 'libraries/Form/Content/SubBlockShell.php');
require_once(APPPATH . 'libraries/Form/Content/SubBlock.php');
require_once(APPPATH . 'libraries/Form/Content/SubFormShell.php');
require_once(APPPATH . 'libraries/Form/Content/SubContentCondition.php');
require_once(APPPATH . 'libraries/Form/Content/SubContentConditionGroup.php');
//require_once(APPPATH . 'libraries/Form/iFormDisplayFactory.php');
require_once(APPPATH . 'libraries/Validation/Input/Validator.php');
require_once(APPPATH . 'models/Forms/iForm_Model.php');

//use \myagsource\Form\iFormDisplayFactory;
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
class FormDisplayFactory {// implements iFormFactory{
	/**
	 * datasource
	 * @var form_model
	 **/
	protected $datasource;

    /**
     * listing_datasource
     * @var iListing_model
     **/
    //protected $listing_datasource;

    /**
     * params for identify data that populates form
     * @var array
     **/
    protected $key_params;

    function __construct(\iForm_Model $datasource, SupplementalFactory $supplemental_factory = null, $key_params = null) {//, $report_factory, $option_listing_factory, $setting_form_factory
		$this->datasource = $datasource;
        $this->key_params = $key_params;
/*
        $this->supplemental_factory = $supplemental_factory;
        $this->report_factory = $report_factory;
        $this->option_listing_factory = $option_listing_factory;
        $this->setting_form_factory = $setting_form_factory;
*/	}

    /*
     * getByPage
     *
     * @param int page_id
         * @param string herd_code
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
            $forms[$r['list_order']] = $this->createDisplayForm($r, $herd_code);
        }
        return $forms;
    }

    /*
     * getByBlock
     *
     * @param int block_id
         * @param string herd_code
     * @author ctranel
     * @returns \myagsource\Site\iBlock[]
*/
    public function getByBlock($block_id, $herd_code){
        $results = $this->datasource->getFormByBlock($block_id);
        if(empty($results)){
            return [];
        }

        $r = $results[0];
        $form = $this->createDisplayForm($r, $herd_code);

        return $form;
    }

    /*
     * getFormDisplay
     *
     * @param int form id
     * @author ctranel
     * @returns \myagsource\Form\Form
     */
    public function getFormDisplay($form_id, $herd_code){
        $results = $this->datasource->getFormById($form_id);
        if(empty($results)){
            throw new \Exception('No data found for requested form.');
        }
        return $this->createDisplayForm($results[0], $herd_code);
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
        return $this->createDisplayForm($results[0], $herd_code);
    }


    /*
    * getFormControlData
    *
    * @param int of form id
    * @param array of ints ancestor_form_ids
    * @author ctranel
    * @returns Array of Forms
    */
    protected function getFormControlData($form_id, $ancestor_form_ids = null)    {
        //this function depends on an existing record
        $control_data = $this->datasource->getFormControlData($form_id, $this->key_params, $ancestor_form_ids);

        return $control_data;
    }

    /*
    * createDisplayForm
    *
    * @param array of form data
    * @param string herd code
    * @param array of ints ancestor_form_ids
    * @author ctranel
    * @returns Array of Forms
    */
    protected function createDisplayForm($form_data, $herd_code, $ancestor_form_ids = null){
        $subforms = $this->getSubFormShells($form_data['form_id'], $herd_code, $ancestor_form_ids);
        $subblocks = $this->getSubBlockShells($form_data['form_id'], $herd_code, $ancestor_form_ids);

        //this function depends on an existing record
        $control_data = $this->getFormControlData($form_data['form_id'], $ancestor_form_ids);

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
                $b = isset($subblocks[$d['name']]) ? $subblocks[$d['name']] : null;
                $options = null;
                if(strpos($d['control_type'], 'lookup') !== false){
                    $options = $this->getLookupOptions($d['id'], $d['control_type'], $d['data_type']);
                }
                $fc[] = new FormControl($d, $validators, $options, $s, $b);//, $this->listing_datasource
            }
        }
        return new Form($form_data['form_id'], $this->datasource, $fc, $form_data['form_name'], $form_data['dom_id'], $form_data['action'], $herd_code);
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
    protected function getSubBlockShells($parent_form_id, $herd_code, $ancestor_form_ids = null){
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

        //parse each subblock separately
        foreach($results as $k => $r){
            if(!isset($subforms[$r['parent_control_name']][$r['block_id']])){
                $subblock_groups = $this->extractConditionGroups($subblock_data[$r['parent_control_name']][$r['block_id']]);
                $subblocks[$r['parent_control_name']][$r['block_id']] = new SubBlockShell($subblock_groups, $r['block_id'], $r['name'], $r['display_type'], $r['subblock_content_id'], $r['datalink_form_id']);
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
    protected function getSubFormShells($parent_form_id, $herd_code, $ancestor_form_ids = null){
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
                $subform_groups = $this->extractConditionGroups($subform_data[$r['parent_control_name']][$r['form_id']]);
                $subforms[$r['parent_control_name']][$r['form_id']] = new SubFormShell($subform_groups, $r['form_id']);
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
                    = $this->structureSubFormCondData([$v], $cond_key_field)[$v['parent_control_name']][$v[$cond_key_field]]['condition_groups'][$v['condition_group_id']]['conditions'][$v['condition_id']];
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
*  getControlOptionsById

*  Returns all options

*  @since: version 1
*  @author: ctranel
*  @date: Jun 26, 2014
*  @param: int control id
*  @return array of control meta data
*  @throws:
* -----------------------------------------------------------------
*/
    public function getControlOptionsById($control_id){
        $control_meta = $this->datasource->getControlMetaById($control_id);

        return $this->getLookupOptions($control_meta['id'], $control_meta['control_type'], $control_meta['data_type']);
    }


    /* -----------------------------------------------------------------
    *  getLookupOptions

    *  Returns all options

    *  @since: version 1
    *  @author: ctranel
    *  @date: Jun 26, 2014
    *  @param: int control_id
    *  @param: string control_type
    *  @param: string data_type
    *  @return array of key=>value pairs
    *  @throws:
    * -----------------------------------------------------------------
    */
    protected function getLookupOptions($control_id, $control_type, $data_type){
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
                if($data_type === 'int'){
                    $o[$keys[0]] = (int)$o[$keys[0]];
                }
                $ret[] = ['value' => $o[$keys[0]], 'label' => $o[$keys[1]]];
            }
        }

        return $ret;
    }

    /* -----------------------------------------------------------------
    *  getLookupKeys

    *  Returns all options

    *  @since: version 1
    *  @author: ctranel
    *  @date: Jun 26, 2014
    *  @param: int control_id
    *  @return array of key=>value pairs
    *  @throws:
    * -----------------------------------------------------------------
    */
    public function getLookupKeys($control_id){
        if(isset($control_id) === false){
            throw new \Exception("Unable to look up option keys.");
        }

        $ret = $this->datasource->getLookupKeys($control_id);

        return $ret;
    }
}
