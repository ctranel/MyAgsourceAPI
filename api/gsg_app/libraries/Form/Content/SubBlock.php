<?php
/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 8/22/2016
 * Time: 11:34 AM
 */

namespace myagsource\Form\Content;

require_once APPPATH . 'libraries/Form/iSubBlock.php';

use \myagsource\Site\iBlock;
use \myagsource\Form\iForm;
use \myagsource\Form\iSubBlock;

class SubBlock implements iSubBlock
{
    /**
     * @var array of SubBlockConditionGroup objects
     */
    protected $condition_groups;

    /**
     * @var iBlock
     */
    protected $block;

    /**
     * @var iForm
     */
    protected $datalink_form;

    public function __construct($condition_groups, iBlock $block, iForm $datalink_form = null){
        $this->condition_groups = $condition_groups;
        $this->block = $block;
        $this->datalink_form = $datalink_form;
    }
    
    public function toArray(){
        $ret['block'] = $this->block->toArray();

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
    public function conditionsMet($form_data){ //need to pass all form data, not just control value
        foreach($this->condition_groups as $cg){
            if(!$cg->conditionsMet($form_data)){
                return false;
            }
        }

        return true;
    }

    /**
     * insertDefaultListingRecords
     *
     * are all subblock conditions met?
     *
     * @param $control_value
     * @return bool
     */
    public function insertDefaultListingRecords($parent_key_vals, $form_data, $batch_variable){
        if(isset($this->datalink_form) && $this->conditionsMet($form_data)){
            $input = $this->block->dataset();
            if(!is_array($input) || empty(array_filter($input))){
                return;
                //throw new \Exception("Default data not found.");
            }
            foreach($input as $i){
                if(isset($batch_variable)){
                    $this->datalink_form->writeBatch($i + $parent_key_vals);
                }
                else{
                    $this->datalink_form->write($i + $parent_key_vals);
                }
            }
        }
    }
}