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

class SubFormConditionGroup //implements iSubFormConditionGroup
{
    /**
     * @var string
     */
    protected $operator;

    /**
     * @var array of SubFormConditionGroup objects
     */
    protected $child_groups;

    /**
     * @var array of SubFormCondition objects
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
}