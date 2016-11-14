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
    /**
     * @var array of SubFormConditionGroup objects
     */
    protected $condition_groups;

    /**
     * @var iForm
     */
    protected $form;

    public function __construct($condition_groups, iForm $form)
    {
//var_dump($this->condition_groups);
        $this->condition_groups = $condition_groups;
        $this->form = $form;
    }
    
    public function toArray(){
        $ret['form'] = $this->form->toArray();

        if(isset($this->condition_groups) && is_array($this->condition_groups) && !empty($this->condition_groups)){
            $ret['condition_groups'] = [];
            foreach($this->condition_groups as $s){
                $ret['condition_groups'][] = $s->toArray();
            }
        }

        return $ret;
    }
    
    public function write($form_data)    {
        $this->form->write($form_data);
    }

    public function action(){
        return $this->form->action();
    }
/*
    public function conditionsMet($control_value){
        switch($this->operator){
            case "=":
                return $control_value === $this->operand;
            case "!=":
                return $control_value !== $this->operand;
            case ">":
                return $control_value > $this->operand;
            case "<":
                return $control_value < $this->operand;
        }
        return false;
    }
*/
}