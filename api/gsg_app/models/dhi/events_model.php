<?php
class Events_model extends CI_Model {
	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * @method eventEligibilityData()
	 * @param string herd code
     * @param int serial_num
	 * @return array of data for determining event eligibility
	 * @access public
	 *
	 **/
	function eventEligibilityData($herd_code, $serial_num){
		if (!isset($herd_code) || !isset($serial_num)){
			throw new Exception('Missing required information');
		}
		// results query
		$ret = $this->db
            ->select("[is_active]
              ,[species_cd]
              ,[sex_cd]
              ,[birth_dt]
              ,[curr_lact_num]
              ,[earliest_dry_eligible_date]
              ,[earliest_fresh_eligible_date]
              ,[earliest_abort_eligible_date]
              ,[earliest_preg_eligible_date]
              ,[earliest_repro_eligible_date]
              ,[TopStatusDate]
              ,[TopFreshDate]
              ,[TopBredDate]
              ,[TopDryDate]
              ,[TopSoldDiedDate]
              ,[current_status]
              ,[is_bred]"
            )
            ->from('[TD].[animal].[animal_event_eligibility]')
            ->where('herd_code',$herd_code)
            ->where('serial_num',$serial_num)
		    ->get()->result_array();
        if(isset($ret[0]) && is_array($ret[0])){
            return $ret[0];
        }
	} //end function
}
