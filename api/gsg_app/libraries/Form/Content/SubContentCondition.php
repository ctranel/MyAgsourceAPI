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
    protected $operator;

    /**
     * @var string
     */
    protected $operand;

    public function __construct($operator, $operand)
    {
        $this->operator = $operator;
        $this->operand = $operand;
    }
    
    public function toArray(){
        $ret = [
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
}