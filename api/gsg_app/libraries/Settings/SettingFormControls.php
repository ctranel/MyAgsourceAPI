<?php
namespace myagsource\Settings;

require_once APPPATH . 'libraries/Form/Control/FormControls.php';
require_once APPPATH . 'libraries/Settings/SettingFormControl.php';

use \myagsource\Form\Control\FormControls;

/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 6/20/2016
 * Time: 11:53 AM
 */

class SettingFormControls extends FormControls
{
    public function getControl($control_data){
        return new SettingFormControl($control_data, $this->datasource);
    }
}