<?php
require_once APPPATH . 'libraries/MssqlUtility.php';

use \myagsource\MssqlUtility;

class Cow_model extends CI_Model {

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
            ->select('serial_num, control_num, list_order_num, visible_id, barn_name, cow_id, ear_tag_num, rf_id_usain')
            ->where('herd_code', $herd_code)
            ->where('serial_num', $serial_num)
            ->get('[animal].[dbo].[cow_id]')
            ->result_array();
        if(is_array($result) && isset($result[0]) && is_array($result[0])){
            return $result[0];
        }
    }
}
