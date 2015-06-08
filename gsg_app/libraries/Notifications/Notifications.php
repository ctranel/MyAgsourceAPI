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

    
    
    
}

?>