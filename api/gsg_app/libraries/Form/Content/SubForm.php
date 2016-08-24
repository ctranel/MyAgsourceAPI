<?php
/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 8/22/2016
 * Time: 11:34 AM
 */

namespace myagsource\Form\Content;

require_once APPPATH . 'libraries/Form/iSubForm.php';

use myagsource\Form\iSubForm;
use myagsource\Form\iForm;

class SubForm implements iSubForm
{
    public function __construct($operator, $operand2, iForm $form)
    {
        $this->operator = $operator;
        $this->operand = $operand2;
        $this->form = $form;
    }
    
    public function toArray(){
        return [
            'operator' => $this->operator,
            'operand' => $this->operand,
            'form' => $this->form,
        ];
    }
    
    public function write($form_data)    {
        $this->form->write($form_data);
    }
}