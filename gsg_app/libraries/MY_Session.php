<?php
class MY_Session extends CI_Session {
	/**
	* keep_all_flashdata
	* @return  void
	*/
	function __construct(){
		parent::__construct();
	}
	
	function keep_all_flashdata(){
		foreach($this->all_userdata() as $key => $val){
		  if(strpos($key,'flash:old:') > -1){ // key is flashdata
		    $item = substr($key , strlen('flash:old:'));
		    $this->keep_flashdata($item);
		  }
		}
	}
}