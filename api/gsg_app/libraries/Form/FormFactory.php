<?php

namespace myagsource\Form;

require_once(APPPATH . 'libraries/Form/Form.php');
require_once(APPPATH . 'libraries/Form/Control/FormControls.php');

use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\Form\Control\FormControls;

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
	 * supplemental_factory
	 * @var SupplementalFactory
	 **/
	protected $supplemental_factory;
	
	function __construct(\setting_model $datasource, SupplementalFactory $supplemental_factory = null) {//, \db_field_model $datasource_dbfield
		$this->datasource = $datasource;
		$this->supplemental_factory = $supplemental_factory;
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
        return new Form($fc, $form_data['dom_id'], $form_data['action']);
    }
}

?>
