<?php

namespace myagsource\Page\Content\Form;

require_once(APPPATH . 'libraries/Page/Content/Form/Form.php');
require_once(APPPATH . 'libraries/Page/Content/Form/Control/FormControls.php');

use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\Site\WebContent\WebBlockFactory;
use \myagsource\Page\Content\Form\Control\FormControls;

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
	 * supplemental_factory
	 * @var SupplementalFactory
	 **/
	protected $supplemental_factory;
	
	function __construct(\setting_model $datasource, WebBlockFactory $web_block_factory, SupplementalFactory $supplemental_factory = null) {//, \db_field_model $datasource_dbfield
		$this->datasource = $datasource;
		//$this->datasource_dbfield = $datasource_dbfield;
		$this->supplemental_factory = $supplemental_factory;
		$this->web_block_factory = $web_block_factory;
	}
	
	/*
	 * getByPath
	 * 
	 * @param int form id
	 * @author ctranel
	 * @returns \myagsource\Page\Content\Form\Form[]
	 */
	public function getObject($form_id){
		$results = $this->datasource->getFormControlData($form_id);
		if(empty($results)){
			return false;
		}

		return $this->dataToObject($results[0]);
	}

	/*
	 * getBySection
	 * 
	 * @param int page_id
	 * @author ctranel
	 * @returns \myagsource\Page\Content\Form\Form[]
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
    * getBySection
    *
    * @param array of form data
    * @author ctranel
    * @returns Array of Forms
    */
    protected function dataToObject($form_data){
		$web_block = $this->web_block_factory->blockFromData($form_data);
        $fc = new FormControls($this->datasource, $form_data['form_id']);
        return new Form($web_block, $fc, $form_data['dom_id'], $form_data['action']);
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

?>
