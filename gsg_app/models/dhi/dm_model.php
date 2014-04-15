<?php
class Dm_model extends CI_Model {
	protected $tables;
	
	public function __construct()
	{
		parent::__construct();
		$this->db_group_name = 'alert';
		$this->{$this->db_group_name} = $this->load->database($this->db_group_name, TRUE);
	}
	
	/**
	 * @method get_credentials()
	 * @param string herd code
	 * @return array of data for the herd header record
	 * @access public
	 *
	 **/
	function get_credentials($herd_code = FALSE){
		if (!$herd_code){
			$herd_code = $this->session->userdata('herd_code');
		}
		// results query
		$q = $this->{$this->db_group_name}->select("UserID, Password", FALSE)
		->from('user_auth')
		->where('UserID',$herd_code);
		$ret = $q->get()->result_array();
		if(!empty($ret)) return $ret[0];
		else return FALSE;
	} //end function
}
