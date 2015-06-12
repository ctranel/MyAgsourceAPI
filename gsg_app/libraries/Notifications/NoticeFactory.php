<?php
namespace myagsource\notices;
require_once 'Notice.php';


class NoticeFactory {
    
    public function _construct(){
        
    }

    public static function createNotice($notice_data){
        return new Notice($notice_data);
    }
    
}

?>