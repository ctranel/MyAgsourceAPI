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


interface iFormControls
{
    public function __construct($datasource, $form_id);
    public function getControls();
    public function getControl($control_data);
}