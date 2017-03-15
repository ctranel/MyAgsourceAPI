<?php
/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 8/22/2016
 * Time: 11:34 AM
 */

namespace myagsource\Form\Content;


use myagsource\Form\iForm;

class SubFormShell
{
    /**
     * @var array of SubContentConditionGroup objects
     */
    protected $condition_groups;

    /**
     * @var int
     */
    protected $form_id;

    /**
     * @var int
     */
    protected $list_order;

    public function __construct($condition_groups, $form_id, $list_order)
    {
        $this->condition_groups = $condition_groups;
        $this->form_id = $form_id;
        $this->list_order = $list_order;
    }
    
    public function toArray(){
        $ret['form_id'] = $this->form_id;
        $ret['list_order'] = $this->list_order;

        if(isset($this->condition_groups) && is_array($this->condition_groups) && !empty($this->condition_groups)){
            $ret['condition_groups'] = [];
            foreach($this->condition_groups as $s){
                $ret['condition_groups'][] = $s->toArray();
            }
        }

        return $ret;
    }
}