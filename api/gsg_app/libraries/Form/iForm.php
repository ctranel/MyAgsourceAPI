<?php
/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 8/22/2016
 * Time: 9:44 AM
 */

namespace myagsource\Form;


use myagsource\Site\iBlockContent;

interface iForm extends iBlockContent
{
    public function write($form_data);
}