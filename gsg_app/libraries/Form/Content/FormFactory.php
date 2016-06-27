<?php

namespace myagsource\Form\Content;

require_once(APPPATH . 'libraries/Form/Content/Form.php');

use \myagsource\Supplemental\Content\SupplementalFactory;

/**
 * A repository? for form objects
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
	 * supplemental_factory
	 * @var SupplementalFactory
	 **/
	protected $supplemental_factory;
	
	function __construct(\setting_model $datasource, SupplementalFactory $supplemental_factory = null) {//, \db_field_model $datasource_dbfield
		$this->datasource = $datasource;
		//$this->datasource_dbfield = $datasource_dbfield;
		$this->supplemental_factory = $supplemental_factory;
	}
	
	/*
	 * getByPath
	 * 
	 * @param string path
	 * @author ctranel
	 * @returns \myagsource\Report\iBlock
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
	 * @returns Form[]
	 */
	public function getByPage($page_id){
		$forms = [];
		
		$results = $this->datasource->getFormsByPage($page_id);
		if(empty($results)){
			return false;
		}

		foreach($results as $r){
            $forms[] = $this->dataToObject($r);
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
        $fc = new FormControls($this->datasource, $form_data['id']);
        return new Form($fc, $this->datasource, $form_data['id'], $form_data['page_id'], $form_data['name'], $form_data['description'], $form_data['dom_id'], $form_data['action']);
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
