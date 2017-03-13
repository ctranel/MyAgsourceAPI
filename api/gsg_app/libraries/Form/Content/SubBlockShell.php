<?php
/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 8/22/2016
 * Time: 11:34 AM
 */

namespace myagsource\Form\Content;

require_once APPPATH . 'libraries/Form/iSubBlock.php';

//use \myagsource\Site\iBlock;
use \myagsource\Form\iSubBlock;

class SubBlockShell
{
    /**
     * @var array of SubBlockConditionGroup objects
     */
    protected $condition_groups;

    /**
     * @var int
     */
    protected $block_id;

    /**
     * @var string
     */
    protected $block_name;

    /**
     * @var string
     */
    protected $content_type;

    /**
     * @var int
     */
    protected $content_id;

    /**
     * @var int
     */
    protected $datalink_form_id;

    public function __construct($condition_groups, $block_id, $block_name, $content_type, $content_id, $datalink_form_id)
    {
        $this->condition_groups = $condition_groups;
        $this->block_id = $block_id;
        $this->block_name = $block_name;
        $this->content_type = $content_type;
        $this->content_id = $content_id;
        $this->datalink_form_id = $datalink_form_id;
    }
    
    public function toArray(){
        $ret = [
            'block_id' => $this->block_id,
            'block_name' => $this->block_name,
            'content_type' => $this->content_type,
            'content_id' => $this->content_id,
        ];

        if(isset($this->datalink_form_id)){
            $ret['datalink_form_id'] = $this->datalink_form_id;
        }

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
}