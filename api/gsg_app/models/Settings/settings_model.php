<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* -----------------------------------------------------------------
*  @description: Benchmark data access
*  @author: ctranel
*
*
*  -----------------------------------------------------------------
*/

class Settings_model extends CI_Model {
    /**
     * user_id
     * @var int
     **/
    protected $user_id;

    /**
     * herd_code
     * @var string
     **/
    protected $herd_code;

    public function __construct($args){
		parent::__construct();
        $this->user_id = $args['user_id'];
        $this->herd_code = $args['herd_code'];
	}

	/**
	 * @method getSettingsData()
	 * @param string herd_code
	 * @return array of data fields for the current primary table, excluding those fields in the param
	 * @access protected
	 *
	 **/
	public function getSettingsData(){
        if(isset($this->user_id) && $this->user_id != FALSE){
            $this->db
                ->join('users.setng.user_herd_settings uhs', "s.id = uhs.setting_id AND (uhs.user_id = " . $this->user_id . " OR uhs.user_id IS NULL) AND (uhs.herd_code = '" . $this->herd_code . "' OR uhs.herd_code IS NULL)", 'left');
        }
        else{
            $this->db
                ->join('users.setng.user_herd_settings uhs', "s.id = uhs.setting_id AND uhs.user_id IS NULL AND (uhs.herd_code = '" . $this->herd_code . "' OR uhs.herd_code IS NULL)", 'left');
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
