<?php
namespace myagsource\Settings;

require_once(APPPATH . 'libraries/Form/Control/FormControl.php');

use myagsource\Form\Control\FormControl;

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
		parent::__construct($control_data, $datasource);
	}

    public function setDefaultValue($new_value){
        $this->default_value = $new_value;
    }

    public function controlToSettingsData(){

    }
}