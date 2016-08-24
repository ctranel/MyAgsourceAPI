<?php
/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 8/22/2016
 * Time: 9:44 AM
 */

namespace myagsource\Form;


interface iForm
{
    public function toArray();
    public function write($form_data);
}