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
require_once(APPPATH . 'libraries/Form/iFormSubmissionFactory.php');
require_once(APPPATH . 'libraries/Validation/Input/Validator.php');
require_once(APPPATH . 'models/Forms/iForm_Model.php');

use \myagsource\Site\WebContent\WebBlockFactory;
use \myagsource\Form\iFormSubmissionFactory;
//use myagsource\Site\WebContent\SubBlock;
use myagsource\Listings\iListingFactory;
use myagsource\Report\Content\ReportFactory;
use myagsource\Settings\Form\SettingsFormSubmissionFactory;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\Form\Content\Control\FormControlGroup;
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
class FormSubmissionFactory implements iFormSubmissionFactory{
	/**
	 * datasource
	 * @var form_model
	 **/
	protected $datasource;

    /**
     * supplemental_factory
     * @var SupplementalFactory
     **/
    protected $supplemental_factory;

    /**
     * report_factory
     * @var ReportFactory
     **/
    protected $report_factory;

    /**
     * option_listing_factory
     * @var ListingFactory
     **/
    protected $option_listing_factory;

    /**
     * setting_form_factory
     * @var SettingsFormSubmissionFactory
     **/
    protected $setting_form_factory;

    /**
     * block_datasource
     * @var block_model
     **/
    protected $block_datasource;

    /**
     * params for identify data that populates form
     * @var array
     **/
    protected $key_params;

    /**
     * data submitted (form data)
     * @var array
     **/
    protected $submitted_values;



    function __construct(\iForm_Model $datasource, $key_params, $submitted_values, SupplementalFactory $supplemental_factory, ReportFactory $report_factory, iListingFactory $option_listing_factory, iFormSubmissionFactory $setting_form_factory, $block_datasource) {//, \iListing_model $listing_datasource, \db_field_model $datasource_dbfield
		$this->datasource = $datasource;
        $this->key_params = $key_params;
        $this->submitted_values = $submitted_values;

        $this->supplemental_factory = $supplemental_factory;
        $this->report_factory = $report_factory;
        $this->option_listing_factory = $option_listing_factory;
        $this->setting_form_factory = $setting_form_factory;
        $this->block_datasource = $block_datasource;
	}

    /*
     * getByPage
     *
     * @param int page_id
         * @param string herd_code
     * @author ctranel
     * @returns \myagsource\Page\Content\FormBlock\FormBlock[]
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
*/

    /*
     * getByBlock
     *
     * @param int block_id
         * @param string herd_code
     * @author ctranel
     * @returns \myagsource\Site\iBlock[]
*/
    public function getByBlock($block_id){
        $results = $this->datasource->getFormByBlock($block_id);
        if(empty($results)){
            return [];
        }

        $r = $results[0];
        $form = $this->createForm($r);

        return $form;
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
			throw new \Exception('No data found for requested form (' . $form_id . ').');
		}
		return $this->createForm($results[0]);
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
    protected function createForm($form_data, $ancestor_form_ids = null){
        $subforms = $this->getSubForms($form_data['form_id'], $ancestor_form_ids);
        $subblocks = $this->getSubBlocks($form_data['form_id'], $ancestor_form_ids);

        //this function depends on an existing record
        $control_data = $this->datasource->getFormControlData($form_data['form_id'], $this->key_params, $ancestor_form_ids);

        $control_group_data = $this->extractControlGroup($control_data);
        unset($control_data);

        $control_group_keys = array_keys($control_group_data);
        $control_groups = [];

        foreach($control_group_keys as $cgk) {
            $fc = [];

            if (!is_array($control_group_data[$cgk]) || empty($control_group_data[$cgk]) || !is_array($control_group_data[$cgk][0])) {
                $control_data[$cgk] = [];
            }

            foreach($control_group_data[$cgk] as $d){
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

                $fc[] = new FormControl($d, $validators, $options, $s, $b);//, $this->listing_datasource
            }

            $control_groups[] = new FormControlGroup($control_group_data[$cgk][0]['control_group'], $control_group_data[$cgk][0]['cg_list_order'], $fc);
        }
        return new Form($form_data['form_id'], $this->datasource, $control_groups, $form_data['form_name'], $form_data['dom_id'], $form_data['action'], $this->key_params['herd_code']);
    }

    /*
    * extractControlGroup
    *
    * @param array of control data
    * @author ctranel
    * @returns Array of Control data group by control group name
    */
    protected function extractControlGroup($control_data) {
        if(!isset($control_data) || !is_array($control_data)){
            return [];
        }

        $cg = [];

        foreach($control_data as $c){
            $cg[$c['control_group']][] = $c;
        }

        return $cg;
    }

    /*
    * getSubBlocks
    *
    * @param int parent form id
    * @param string herd code
    * @param array of ints ancestor_form_ids
    * @author ctranel
    * @returns Array of Forms
    */
    protected function getSubBlocks($parent_form_id, $ancestor_form_ids = null){
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
        foreach($subblock_data as $control_name => $sblocks){
            foreach($sblocks as $block_id => $sb){
                //for form submissions, subblocks load only when there is a datalink
                if(!isset($sb['datalink_form_id'])){
                    continue;
                }
                $block = $this->_loadBlock($block_id);

                $datalink = $this->getForm($sb['datalink_form_id']);

                $subblock_groups = $this->extractConditionGroups($subblock_data[$control_name][$block_id]);
                $subblocks[$control_name][$block_id] = new SubBlock($subblock_groups, $block, $datalink);
            }
        }

        return $subblocks;
    }

    /*
    * getSubForms
    *
    * @param int parent form id
    * @param string herd code
    * @param array of ints ancestor_form_ids
    * @author ctranel
    * @returns Array of Forms
    */
    protected function getSubForms($parent_form_id, $ancestor_form_ids = null){
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
                $form = $this->createForm($r, $ancestor_form_ids);
                $subform_groups = $this->extractConditionGroups($subform_data[$r['parent_control_name']][$r['form_id']]);
                $subforms[$r['parent_control_name']][$r['form_id']] = new SubForm($subform_groups, $form);
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
                //only with subblocks, not subforms:
                $conditions_data[$v['parent_control_name']][$v[$cond_key_field]]['datalink_form_id'] = isset($v['datalink_form_id']) ? $v['datalink_form_id'] : null;
            }
        }
        return $conditions_data;
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
*/

    protected function _loadBlockContent($block_id){
        //create block content
        $key_fields = $this->block_datasource->getKeysByBlock($block_id);
        $keys = array_intersect_key($this->submitted_values, array_flip( $key_fields));

        $report = $this->report_factory->getByBlock($block_id,$keys);
        if(!empty($report)){
            return $report;
        }
        $setting_forms = $this->setting_form_factory->getByBlock($block_id, $this->key_params['herd_code']); //@todo: add user id
        if(!empty($setting_forms)){
            return array_values($setting_forms)[0];
        }
        $entry_forms = $this->getByBlock($block_id);
        if(!empty($entry_forms)){
            return array_values($entry_forms)[0];
        }
        //$serial_num = isset($params['serial_num']) ? $params['serial_num'] : null;
        $listings = $this->option_listing_factory->getByBlock($block_id, $this->key_params);
        if(!empty($listings)){
            return array_values($listings)[0];
        }

        throw new \Exception('No content found for requested page block.');
    }

    protected function _loadBlock($block_id){
        $block_content = $this->_loadBlockContent($block_id);

        $web_block_factory = new WebBlockFactory($this->block_datasource, $this->supplemental_factory);

        //create blocks for content
        $block = $web_block_factory->getBlock($block_id, $block_content);

        return $block;
    }
}
