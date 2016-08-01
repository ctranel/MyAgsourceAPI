<?php
namespace myagsource\notices;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Notice{

    private $message;
    private $start_time;
    private $end_time;
    private $author;
    private $type;
    private $time_added;
    
    public function __construct($notice_data){

        $this->message = $notice_data['message'];
        $this->start_time = $notice_data['start_time'];
        $this->end_time = $notice_data['end_time'];
        $this->author = $notice_data['author'];
        $this->type = $notice_data['type'];
        $this->time_added = $notice_data['time_added'];
        
    }
    
    public function toArray() {
        $arr_return = array(
        	 'message' => $this->message
            ,'start_time' => $this->start_time
            ,'end_time' => $this->end_time
            ,'author' => $this->author
            ,'type' => $this->type
            ,'time_added' => $this->time_added
        );
        return $arr_return;
    }
    
    public function getMessage() {
        return $this->message;
    }
    
}

