<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 6/20/2016
 * Time: 2:37 PM
 */
interface iForm_Model
{
    public function getFormsByPage($page_id);
    public function getSubFormsByParentId($parent_form_id);
    public function getFormById($form_id);
    public function getFormControlData($form_id, $key_params);
    public function getLookupOptions($control_id);
    public function upsert($form_id, $form_data);
}