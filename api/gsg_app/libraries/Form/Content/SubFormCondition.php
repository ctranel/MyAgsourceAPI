<?php
/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 8/22/2016
 * Time: 11:34 AM
 */

namespace myagsource\Form\Content;

//require_once APPPATH . 'libraries/Form/iSubFormCondition.php';

//use myagsource\Form\iSubFormCondtion;

class SubFormCondition //implements iSubFormCondition
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
}