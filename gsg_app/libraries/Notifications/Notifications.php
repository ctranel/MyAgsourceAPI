<?php
namespace myagsource\notices;
require_once 'NoticeFactory.php';

use myagsource\notices\NoticeFactory;


if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Notifications{

    private $notice_model;
    private $arr_notices;
    
    
    public function __construct(\Notice_Model $notice_model) {
        $this->notice_model = $notice_model;
    }

    public function populateNotices($types = 'system') {
        $arr_data = $this->notice_model->get_notices($types);
        $this->arr_notices = array();
        foreach($arr_data as $notice_data){
            $this->arr_notices[] = NoticeFactory::createNotice($notice_data);
        }
    }
    
    public function getNoticesTexts() {
        $messages = array();
        foreach($this->arr_notices as $a) {
            $messages[] = $a->getMessage();
        }
        return $messages;
    }
    
    public function getNotices() {
        return $this->arr_notices;
    }
    
}

?>