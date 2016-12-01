<?php
/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 8/22/2016
 * Time: 9:44 AM
 */

namespace myagsource\Form;

require_once(APPPATH . 'libraries/Site/iBlockContent.php');

use myagsource\Site\iBlockContent;

interface iForm extends iBlockContent
{
    public function write($form_data);
    public function delete($form_data);
    public function action();
}