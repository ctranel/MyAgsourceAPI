<?php
/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 8/22/2016
 * Time: 11:34 AM
 */

namespace myagsource\Form\Content;

//require_once APPPATH . 'libraries/Form/iSubBlock.php';

//use \myagsource\Site\iBlock;
//use \myagsource\Form\iSubBlock;

class SubBlock //implements iSubBlock
{
    /**
     * @var array of SubBlockConditionGroup objects
     */
    protected $condition_groups;

    /**
     * @var int
     */
    protected $block_id;

    public function __construct($condition_groups, $block_id)
    {
        $this->condition_groups = $condition_groups;
        $this->block_id = $block_id;
    }
    
    public function toArray(){
        $ret['block_id'] = $this->block_id;

        if(isset($this->condition_groups) && is_array($this->condition_groups) && !empty($this->condition_groups)){
            $ret['condition_groups'] = [];
            foreach($this->condition_groups as $s){
                $ret['condition_groups'][] = $s->toArray();
            }
        }

        return $ret;
    }

    /**
     * conditionsMet
     *
     * are all subblock conditions met?
     *
     * @param $control_value
     * @return bool
     */
    public function conditionsMet($control_value){
        foreach($this->condition_groups as $cg){
            if(!$cg->conditionsMet($control_value)){
                return false;
            }
        }

        return true;
    }
}