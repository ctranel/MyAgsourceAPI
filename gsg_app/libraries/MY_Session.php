<?php
class MY_Session extends CI_Session {
	function __construct(){
		parent::__construct();
	}

    /**
     * keep_all_flashdata
     * @return  void
     */
	function keep_all_flashdata(){
		foreach($this->all_userdata() as $key => $val){
		  if(strpos($key,'flash:old:') > -1){ // key is flashdata
		    $item = substr($key , strlen('flash:old:'));
		    $this->keep_flashdata($item);
		  }
		}
	}

    /**
     * clear_flashdata
     * @return  void
	function clear_flashdata(){
var_dump($this->userdata); die;
		foreach($this->all_userdata() as $key => $val){
			if(strpos($key,'flash:old:') > -1){ // key is flashdata
				$item = substr($key , strlen('flash:old:'));
			}
		}
	}
*/
}