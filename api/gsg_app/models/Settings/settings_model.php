<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'libraries/MssqlUtility.php';

use \myagsource\MssqlUtility;

/* -----------------------------------------------------------------
*  @description: Benchmark data access
*  @author: ctranel
*
*
*  -----------------------------------------------------------------
*/

class Settings_model extends CI_Model {

    public function __construct(){
		parent::__construct();
	}

	/**
	 * @method getSettingsData()
	 * @param string herd_code
	 * @return array of data fields for the current primary table, excluding those fields in the param
	 * @access protected
	 *
	 **/
	public function getSettingsData($user_id, $herd_code){
        $user_id = (int)$user_id;
        $herd_code = MssqlUtility::escape($herd_code);

	    if(isset($user_id) && $user_id != FALSE){
            $this->db
                ->join('users.setng.user_herd_settings uhs', "s.id = uhs.setting_id AND (uhs.user_id = " . $user_id . " OR uhs.user_id IS NULL) AND (uhs.herd_code = '" . $herd_code . "' OR uhs.herd_code IS NULL)", 'left');
        }
        else{
            $this->db
                ->join('users.setng.user_herd_settings uhs', "s.id = uhs.setting_id AND uhs.user_id IS NULL AND (uhs.herd_code = '" . $herd_code . "' OR uhs.herd_code IS NULL)", 'left');
        }
		$ret = $this->db
            ->select('s.id, s.name, s.label, uhs.value, s.default_value, c.name AS control_type')
            ->from('users.setng.settings s')
            ->join('users.frm.control_types c', "s.type_id = c.id", 'inner')
            ->get()
            ->result_array();

        return $ret;
	}

}
