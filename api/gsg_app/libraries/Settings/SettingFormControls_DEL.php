<?php
namespace myagsource\Settings;

require_once APPPATH . 'libraries/Form/Content/Control/FormControls.php';
require_once APPPATH . 'libraries/Settings/SettingFormControl.php';

use \myagsource\Form\Content\Control\FormControls;

/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 6/20/2016
 * Time: 11:53 AM
 */

class SettingFormControls extends FormControls
{
    public function getControl($control_data){
        //get subforms here?
        return new SettingFormControl($this->datasource, $control_data);
    }
}