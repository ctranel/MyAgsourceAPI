<?php
namespace myagsource\notices;
require_once 'NoticeFactory.php';


class NoticeFactory {
    
    public function _construct(){
        
    }

    public static function createNotice($notice_model, $notice_data){
        return new Notice($notice_model, $notice_data);
    }
    
}

?>