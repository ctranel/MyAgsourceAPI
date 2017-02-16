<?php
/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 8/22/2016
 * Time: 11:34 AM
 */

namespace myagsource\Form\Content;

//require_once APPPATH . 'libraries/Form/iSubContentCondition.php';

//use myagsource\Form\iSubFormCondtion;

class SubContentCondition //implements iSubContentCondition
{
    /**
     * @var string
     */
    protected $element_name;

    /**
     * @var string
     */
    protected $operator;

    /**
     * @var string
     */
    protected $operand;

    public function __construct($element_name, $operator, $operand)
    {
        $this->element_name = $element_name;
        $this->operator = $operator;
        $this->operand = $operand;
    }
    
    public function toArray(){
        $ret = [
            'element_name' => $this->element_name,
            'operator' => $this->operator,
            'operand' => $this->operand,
        ];

        return $ret;
    }

    /**
     * conditionsMet
     *
     * are all subform conditions met?
     *
     * @param $control_value
     * @return bool
     */
    public function conditionsMet($form_values){
        switch($this->operator){
            case "=":
                return $form_values[$this->element_name] === $this->operand;
            case "!=":
                return $form_values[$this->element_name] !== $this->operand;
            case ">":
                return $form_values[$this->element_name] > $this->operand;
            case "<":
                return $form_values[$this->element_name] < $this->operand;
        }
        return false;
    }
}