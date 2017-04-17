<?php
require_once APPPATH . 'libraries/MssqlUtility.php';

use \myagsource\MssqlUtility;

class Form_defaults_model extends CI_Model {
	public function __construct(){
		parent::__construct();
	}

    /**
     * @method getHerdTreatmentValues()
     * @param int herd event rxtx id
     * @return array of event data
     * @access public
     *
     **/
    public function getSiteByPen($herd_code, $pen_num){
        $pen_num = (int)$pen_num;
        $herd_code = MssqlUtility::escape($herd_code);

        $res = $this->db
            ->select("siteid")
            ->where('pen_num', $pen_num)
            ->where('herd_code', $herd_code)
            ->get('TD.herd.pens')
            ->result_array();

        if($res === false ){
            throw new \Exception('Pen defaults could not be found: ' . $this->db->_error_message());
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
     * @method getHerdTreatmentValues()
     * @param int herd event rxtx id
     * @return array of event data
     * @access public
     *
     **/
    public function getHerdTreatmentValues($herd_rxtx_id){
        $id = (int)$herd_rxtx_id;

        $res = $this->db
            ->select("meat_df , milk_df, cost_df, quant_df")
            ->where('id', $id)
            ->get('TD.herd.rxtx')
            ->result_array();

        if($res === false ){
            throw new \Exception('Herd treatment defaults could not be found: ' . $this->db->_error_message());
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
     * @method getHerdEventTreatmentValues()
     * @param int herd event rxtx id
     * @return array of event data
     * @access public
     *
     **/
    public function getHerdEventTreatmentValues($herd_rxtx_id){
        $id = (int)$herd_rxtx_id;

        $res = $this->db
            ->select("meat_df , milk_df, cost_df AS cost, quant_df AS med_qty")
            ->where('id', $id)
            ->get('TD.herd.rxtx')
            ->result_array();

        if($res === false ){
            throw new \Exception('Herd treatment defaults could not be found: ' . $this->db->_error_message());
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
     * @method getBreedingSireDefaultValues()
     * @param string herd code
     * @param int sire code
     * @return array of key -> value data
     * @access public
     *
     **/
    public function getETSireDefaultValues($herd_code, $sire_id){
        $sire_id = (int)$sire_id;
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

    /**
     * @method getETSireIDDefaultValues()
     * @param char bull_id
     * @return array of animal id data
     * @access public
     *
     **/
    public function getETSireIDDefaultValues($bull_id){
        $bull_id = MssqlUtility::escape($bull_id);

        $res = $this->db
            ->select("
                [bull_id] AS sire_bull_id,
                [breed_code] AS sire_breed_cd,
                [country_code] AS sire_country_cd,
                [short_ai_name] AS sire_name,
                [primary_naab] AS sire_naab"
            )
            ->where('bull_id', $bull_id)
            ->get("[sire].[dbo].[view_sire_id2]")
            ->result_array();

        if($res === false ){
            throw new \Exception('Sire ID defaults: ' . $this->db->_error_message());
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
     * @method getETSireNAABDefaultValues()
     * @param varchar NAAB
     * @return array of animal id data
     * @access public
     *
     **/
    public function getETSireNAABDefaultValues($naab){
        $naab = MssqlUtility::escape($naab);

        $res = $this->db
            ->select("
                [bull_naab] AS sire_naab,
                [breed_code] AS sire_breed_cd,
                [country_code] AS sire_country_cd,
                [bull_id] AS sire_bull_id,
                [short_ai_name] AS sire_name"
            )
            ->where('bull_naab', $naab)
            ->get("[sire].[dbo].[view_sire_naab2]")
            ->result_array();

        if($res === false ){
            throw new \Exception('Sire NAAB defaults: ' . $this->db->_error_message());
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
     * @method getSireIDDefaultValues()
     * @param char bull_id
     * @return array of animal id data
     * @access public
     *
     **/
    public function getSireIDDefaultValues($bull_id){
        $bull_id = MssqlUtility::escape($bull_id);

        $res = $this->db
            ->select("
                [bull_id] AS bull_id,
                [breed_code] AS breed_cd,
                [country_code] AS country_cd,
                [short_ai_name] AS name,
                [primary_naab] AS naab"
            )
            ->where('bull_id', $bull_id)
            ->get("[sire].[dbo].[view_sire_id2]")
            ->result_array();

        if($res === false ){
            throw new \Exception('Sire ID defaults: ' . $this->db->_error_message());
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
     * @method getSireNAABDefaultValues()
     * @param varchar NAAB
     * @return array of animal id data
     * @access public
     *
     **/
    public function getSireNAABDefaultValues($naab){
        $naab = MssqlUtility::escape($naab);

        $res = $this->db
            ->select("
                [bull_naab] AS naab,
                [breed_code] AS breed_cd,
                [country_code] AS country_cd,
                [bull_id],
                [short_ai_name] AS name"
            )
            ->where('bull_naab', $naab)
            ->get("[sire].[dbo].[view_sire_naab2]")
            ->result_array();

        if($res === false ){
            throw new \Exception('Sire NAAB defaults: ' . $this->db->_error_message());
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
     * @method getAnimalSireIDDefaultValues()
     * @param char bull_id
     * @return array of animal id data
     * @access public
     *
     **/
    public function getAnimalSireIDDefaultValues($bull_id){
        $bull_id = MssqlUtility::escape($bull_id);

        $res = $this->db
            ->select("
                [bull_id] AS sire_id_num,
                [breed_code] AS sire_breed_cd,
                [country_code] AS sire_country_cd,
                [short_ai_name] AS sire_name,
                [primary_naab] AS sire_naab"
            )
            ->where('bull_id', $bull_id)
            ->get("[sire].[dbo].[view_sire_id2]")
            ->result_array();

        if($res === false ){
            throw new \Exception('Sire ID defaults: ' . $this->db->_error_message());
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
     * @method getAnimalSireNAABDefaultValues()
     * @param varchar NAAB
     * @return array of animal id data
     * @access public
     *
     **/
    public function getAnimalSireNAABDefaultValues($naab){
        $naab = MssqlUtility::escape($naab);

        $res = $this->db
            ->select("
                [bull_naab] AS sire_naab,
                [breed_code] AS sire_breed_cd,
                [country_code] AS sire_country_cd,
                [bull_id] AS sire_id_num,
                [short_ai_name] AS sire_name"
            )
            ->where('bull_naab', $naab)
            ->get("[sire].[dbo].[view_sire_naab2]")
            ->result_array();

        if($res === false ){
            throw new \Exception('Sire NAAB defaults: ' . $this->db->_error_message());
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
