<?php

class Notice_Model extends CI_Model {
    
    public function __construct(){
        parent::__construct();
    }

    public function get_notices($types = array('system')) {

        $notice_array = array();
        
        //need time in T SQL format
        $timestamp = new DateTime("now",new DateTimeZone("America/Chicago"));
        $mssqltime = $timestamp->format('Y-m-d H:i:s');
        
        
        $results = $this->db
        ->select(n.id,n.message,n.start_time,n.end_time,n.author,n.type,n.time_added)
        ->where_in('n.type',$types)
        ->where($timestamp, '> n.start_time')
        ->where($timestamp, '< n.end_time')
        ->get('users.dbo.notices AS n')
        ->result_array();
        
        return $results;
    }

}