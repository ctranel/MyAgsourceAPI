<?php
namespace myagsource\Form;

/**
 * iFormControl
 * 
 * Created by PhpStorm.
 * User: ctranel
 * Date: 6/20/2016
 * Time: 11:31 AM
 */


interface iFormControl
{
    //public function __construct($control_data, $datasource);
    public function getCurrValue($session_value = null);
    public function id();
    public function getDisplayText($session_value);
    //public function setDefaultValue($new_value);
    public function getLookupOptions();
    public function getFormData($session_value = null);
}