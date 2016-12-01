<?php
/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 8/22/2016
 * Time: 11:24 AM
 */

namespace myagsource\Form;

use \myagsource\Form\iForm;

interface iSubForm extends iForm
{
    //public function __construct($operator, $operand2, iForm $form);
    public function toArray();
    public function write($form_data);
    public function delete($form_data);
}