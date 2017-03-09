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

class SubContentConditionGroup //implements iSubContentConditionGroup
{
    /**
     * @var string
     */
    protected $operator;

    /**
     * @var array of SubContentConditionGroup objects
     */
    protected $child_groups;

    /**
     * @var array of SubContentCondition objects
     */
    protected $conditions;

    public function __construct($operator, $child_groups, $conditions = null)
    {
        $this->operator = $operator;
        $this->child_groups = $child_groups;
        $this->conditions = $conditions;
    }
    
    public function toArray(){
        $ret = [
            'operator' => $this->operator,
        ];
        if(isset($this->conditions) && is_array($this->conditions)){
            $ret['conditions'] = [];
            foreach($this->conditions as $v){
                $ret['conditions'][] = $v->toArray();
            }
        }
        if(isset($this->child_groups) && is_array($this->child_groups)){
            $ret['child_groups'] = [];
            foreach($this->child_groups as $v){
                $ret['child_groups'][] = $v->toArray();
            }
        }

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
        foreach($this->conditions as $c){
            if(!$c->conditionsMet($form_values) && $this->operator === "AND"){
                return false;
            }
            if($c->conditionsMet($form_values) && $this->operator === "OR"){
                return true;
            }
        }
        return true;
    }
}