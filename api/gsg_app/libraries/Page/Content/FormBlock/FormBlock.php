<?php
namespace myagsource\Page\Content\FormBlock;

/**
 * Form
 * 
 * Object representing individual form
 * 
 * Created by PhpStorm.
 * User: ctranel
 * Date: 6/20/2016
 * Time: 11:23 AM
 */

require_once APPPATH . 'libraries/Form/Content/Form.php';

use \myagsource\Form\Content\Form;
use \myagsource\Site\WebContent\Block as SiteBlock;

class FormBlock
{
    /**
     * SiteBlock object which contains properties and methods related to the block context within the site
     * @var SiteBlock
     **/
    protected $site_block;

    /**
     * form
     * @var Form
     **/
    protected $form;

    public function __construct(SiteBlock $site_block, Form $form){
        $this->site_block = $site_block;
        $this->form = $form;
    }

    public function displayType(){
        return $this->site_block->displayType();
    }


    public function toArray(){
        $ret = $this->form->toArray() + $this->site_block->toArray();
/*
        $ret['dom_id'] = $this->form->dom_id;
        $ret['action'] = $this->form->action;

        if(isset($this->form->controls) && is_array($this->form->controls) && !empty($this->form->controls)){
            $controls = [];
            foreach($this->form->controls as $c){
                $controls[] = $c->toArray();
            }
            $ret['controls'] = $controls;
            unset($controls);
        }
*/
        return $ret;
    }

    /* -----------------------------------------------------------------
 *  parses form data according to data type conventions.

*  Parses form data according to data type conventions.

*  @since: version 1
*  @author: ctranel
*  @date: July 1, 2014
*  @param array of key-value pairs from form submission
*  @return void
*  @throws:
* -----------------------------------------------------------------
    public function parseFormData($form_data){
        $ret_val = [];
        if(!isset($form_data) || !is_array($form_data)){
            throw new \Exception('No form data found');
        }
        foreach($this->controls as $c){
            foreach($form_data as $k=>$v){
                if($c->name() === $k){
                    $ret_val[$k] = $c->parseFormData($v);
                }
            }
        }
        var_dump($ret_val);
        return $ret_val;
    }
*/
}