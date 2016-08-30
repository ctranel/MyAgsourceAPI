<?php

namespace myagsource\Page\Content\FormBlock;

require_once(APPPATH . 'libraries/Page/Content/FormBlock/FormBlock.php');
require_once(APPPATH . 'libraries/Form/Content/Form.php');
require_once(APPPATH . 'libraries/Settings/SettingForm.php');
require_once(APPPATH . 'libraries/Settings/SettingFormControl.php');

use myagsource\Form\Content\FormFactory;
use myagsource\Settings\SettingForm;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\Site\WebContent\WebBlockFactory;
use \myagsource\Form\Content\Form;
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
class FormBlockFactory {
	/**
	 * datasource_blocks
	 * @var form_model
	 **/
	protected $datasource;

	/**
	 * datasource_dbfield
	 * @var db_field_model
	 **/
	//protected $datasource_dbfield;

	/**
	 * web_block_factory
	 * @var WebBlockFactory
	 **/
	protected $web_block_factory;


    /**
     * setting_form_factory
     * @var FormFactory
     **/
    protected $setting_form_factory;

	/**
	 * entry_form_factory
	 * @var FormFactory
	 **/
	protected $entry_form_factory;

	/**
	 * supplemental_factory
	 * @var SupplementalFactory
	 **/
	protected $supplemental_factory;

    /**
     * user_id
     * @var int
     **/
    protected $user_id;

    /**
     * herd_code
     * @var string
     **/
    protected $herd_code;
    
    function __construct(\setting_model $datasource, WebBlockFactory $web_block_factory, FormFactory $setting_form_factory, FormFactory $entry_form_factory, SupplementalFactory $supplemental_factory = null, $user_id, $herd_code) {//, \db_field_model $datasource_dbfield
		$this->datasource = $datasource;
		//$this->datasource_dbfield = $datasource_dbfield;
		$this->supplemental_factory = $supplemental_factory;
		$this->web_block_factory = $web_block_factory;
        $this->setting_form_factory = $setting_form_factory;
		$this->entry_form_factory = $entry_form_factory;
        $this->user_id = $user_id;
        $this->herd_code = $herd_code;
	}
	
	/*
	 * getObject
	 * 
	 * @param int form id
	 * @author ctranel
	 * @returns \myagsource\Page\Content\FormBlock\FormBlock[]
	 */
	public function getObject($form_id){
		$results = $this->datasource->getFormById($form_id);
		if(empty($results)){
			return false;
		}

		return $this->dataToObject($results[0]);
	}

	/*
	 * getByPage
	 * 
	 * @param int page_id
	 * @author ctranel
	 * @returns \myagsource\Page\Content\FormBlock\FormBlock[]
	 */
	public function getByPage($page_id){
		$forms = [];
		$results = $this->datasource->getFormsByPage($page_id);
		if(empty($results)){
			return [];
		}

		foreach($results as $r){
            $forms[$r['list_order']] = $this->dataToObject($r);
		}
		return $forms;
	}

    /*
    * dataToObject
    *
    * @param array of form data
    * @author ctranel
    * @returns Array of Forms
    */
    protected function dataToObject($form_data){
		$web_block = $this->web_block_factory->blockFromData($form_data);

        if(strpos($form_data['display_type'], 'setting') !== false){
            $f = $this->setting_form_factory->getSettingForm($form_data['form_id'], $this->user_id, $this->herd_code);
        }
		elseif(strpos($form_data['display_type'], 'entry') !== false){
			$f = $this->emtry_form_factory->getForm($form_data['form_id'], $this->user_id, $this->herd_code);
		}
//        else{
//            $f = $this->form_factory->getForm($form_data['form_id']);
//        }
		return new FormBlock($web_block, $f);
    }



    //@TODO: WHERE SHOULD THIS GO??
	protected function keyFieldGroupData($field_groups){
		if(!isset($field_groups) || empty($field_groups)){
			return false;
		}
		
		$ret = [];
		foreach($field_groups as $fg){
			$fg_num = $fg['field_group_num'];
			unset($fg['field_group_num']);
			$ret[$fg_num] = $fg;
		}
		
		return $ret;
	}
}