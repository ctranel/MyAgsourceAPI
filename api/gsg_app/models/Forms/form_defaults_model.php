<?php
require_once APPPATH . 'libraries/MssqlUtility.php';

use \myagsource\MssqlUtility;

class Form_defaults_model extends CI_Model {
	public function __construct(){
		parent::__construct();
	}

    /**
     * @method getBreedingSireDefaultValues()
     * @param string herd code
     * @param int sire code
     * @return array of key -> value data
     * @access public
     *
     **/
    public function getETSireDefaultValues($herd_code, $sire_id){
        $event_code = (int)$sire_id;
        $herd_code = MssqlUtility::escape($herd_code);

        $res = $this->db
            ->select("
               [name] AS sire_name
              ,[naab] AS sire_naab
              ,[bull_id] AS sire_bull_id
              ,[breed_cd] AS sire_breed_cd
              ,[country_cd] AS sire_country_cd
            ")
            ->where('herd_code', $herd_code)
            ->where('ID', $sire_id)
            ->where('isactive', 1)
            ->get("[TD].[herd].[breeding_sires]")
            ->result_array();

        if($res === false ){
            throw new \Exception('Breeding sire defaults: ' . $this->db->_error_message());
        }
        $err = $this->db->_error_message();
        if(!empty($err)){
            throw new \Exception($err);
        }

        if(isset($res[0]) && is_array($res[0])){
            return $res[0];
        }

        return [];
    }

    /**
     * @method getETDonorDefaultValues()
     * @param string herd_code
     * @param int serial_num
     * @return array of animal id data
     * @access public
     *
     **/
    public function getETDonorDefaultValues($herd_code, $serial_num){
        $serial_num = (int)$serial_num;
        $herd_code = MssqlUtility::escape($herd_code);

        $res = $this->db
            ->select("
               [barn_name] AS donor_name
              ,[breed_cd] AS donor_breed_cd
              ,[control_num] AS donor_control_num
              ,[officialid] AS donor_officialid
              ,[country_cd] AS donor_country_cd
            ")
            ->where('herd_code', $herd_code)
            ->where('serial_num', $serial_num)
            ->where('isactive', 1)
            ->get("[TD].[animal].[id]")
            ->result_array();

        if($res === false ){
            throw new \Exception('Breeding sire defaults: ' . $this->db->_error_message());
        }
        $err = $this->db->_error_message();
        if(!empty($err)){
            throw new \Exception($err);
        }

        if(isset($res[0]) && is_array($res[0])){
            return $res[0];
        }

        return [];
    }
}
