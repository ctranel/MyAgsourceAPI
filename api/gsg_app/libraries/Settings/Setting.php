<?php
namespace myagsource\Settings;

require_once(APPPATH . 'libraries/Form/Content/Control/FormControl.php');

use myagsource\Form\Content\Control\FormControl;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 
 * 
* Name:  setting class
*
* Author: ctranel
*
* Created:  2014-06-20
*
*/

class Setting extends FormControl {
	public function __construct($control_data, $datasource){
		parent::__construct($datasource, $control_data);
	}

    public function setDefaultValue($new_value){
        $this->default_value = $new_value;
    }

    public function controlToSettingsData(){

    }
}