<?php
/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 8/22/2016
 * Time: 2:31 PM
 */

namespace myagsource\Form;


interface iFormSubmissionFactory
{
    //public function getByPage($page_id, $herd_code);
    public function getForm($form_id);
}