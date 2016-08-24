<?php
/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 8/22/2016
 * Time: 2:31 PM
 */

namespace myagsource\Form;


interface iFormFactory
{
    public function getObject($form_id);
    public function getSettingForm($form_id, $user_id, $herd_code);
}