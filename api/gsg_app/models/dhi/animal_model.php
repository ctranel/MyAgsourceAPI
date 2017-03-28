<?php
require_once APPPATH . 'libraries/MssqlUtility.php';

use \myagsource\MssqlUtility;

class Animal_model extends CI_Model {

    protected $mssql_utility;

    public function __construct(){
        parent::__construct();
    }

    /**
     * @method cowIdData()
     * @param string herd code
     * @param int serial num
     * @return array of cow id data.
     * @access public
     *
     **/

    public function cowIdData($herd_code, $serial_num){
        $herd_code = MssqlUtility::escape($herd_code);
        $serial_num = (int)$serial_num;

        if(empty($herd_code) || empty($serial_num)){
            throw new Exception('Animal is not specified');
        }

        $result = $this->db
            ->select('serial_num, control_num, list_order AS list_order_num, visible_id, barn_name')
            ->where('herd_code', $herd_code)
            ->where('serial_num', $serial_num)
            ->get('[TD].[animal].[id]')
            ->result_array();
        if(is_array($result) && isset($result[0]) && is_array($result[0])){
            return $result[0];
        }
    }

    /**
     * @method cowIdData()
     * @param string herd code
     * @param int serial num
     * @return array of cow id data.
     * @access public
     *
     **/

    public function getAnimalDataByControlNum($herd_code, $control_num){
        $herd_code = MssqlUtility::escape($herd_code);
        $control_num = (int)$control_num;

        if(empty($herd_code) || empty($control_num)){
            throw new Exception('Animal is not specified');
        }

        $result = $this->db
            ->select('serial_num, control_num, list_order AS list_order_num, visible_id, barn_name')
            ->where('herd_code', $herd_code)
            ->where('control_num', $control_num)
            ->where('isactive', 1)
            ->get('[TD].[animal].[id]')
            ->result_array();
        if(is_array($result) && isset($result[0]) && is_array($result[0])){
            return $result[0];
        }
    }

    /**
     * @method cowIdData()
     * @param string herd code
     * @param int serial num
     * @return array of cow id data.
     * @access public
     *
     **/

    public function getNaabBreedCode($breed, $species_code){
        $breed = MssqlUtility::escape($breed);
        $species_code = MssqlUtility::escape($species_code);

        if(empty($breed) || empty($species_code)){
            throw new Exception('Cannot look up NAAB.');
        }

        $result = $this->db
            ->select('naab')
            ->where('descr', $breed)
            ->where('species_cd', $species_code)
            ->get('[TD].[ref].[breeds]')
            ->result_array();
        //only return a result if there is exactly 1 match, otherwise request is ambiguous
        if(is_array($result) && count($result)==1 && isset($result[0]) && is_array($result[0])){
            return $result[0]['naab'];
        }
    }
}
