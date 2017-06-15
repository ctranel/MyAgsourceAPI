<?php
require_once APPPATH . 'libraries/MssqlUtility.php';

use \myagsource\MssqlUtility;

class Herd_model extends CI_Model {

	protected $tables;

    protected $mssql_utility;
	
	public function __construct(){
		parent::__construct();
	}

	/**
	 * @method herd_authorization_error()
	 * @param string herd code
	 * @param string release code
	 * @return mixed false if there is no error, "herd_release_code" or "herd_code"
	 * 			if there is an error.
	 * @access public
	 *
	 **/
	function herd_authorization_error($herd_code, $herd_release_code){
		//check herd_release_code against herd_code
		$db_tmp_obj = $this->db->select('account_password')
		->where('herd_code', $herd_code)
        ->where('member_status_code', 'A')
        ->where('dhi_quit_date IS NULL')
		->get('herd.dbo.herd_id')
		->row();
		$db_herd_release_code = '';
		if(is_a($db_tmp_obj, 'stdClass')) {
			$db_herd_release_code = $db_tmp_obj->account_password;
			if ($db_herd_release_code != $herd_release_code || $db_herd_release_code == ''){
				return 'account_creation_invalid_herd_release_code';
			}
		}
		else{
			return 'account_creation_invalid_herd_code';
		}
		return false;
	}


	/**
	 * getHerdsByRegion
	 *
	 * @param string region number
	 * @return array of object herd
	 * @author ctranel
	 **/
	function getHerdsByRegion($region_arr_in, $limit = null){
		if (!isset($region_arr_in)  || !is_array($region_arr_in) || empty($region_arr_in)) {
			//return FALSE;
			return array();
		}	
		$this->db
			->join('address.dbo.association a', 'h.dhi_affiliate_num = a.affiliate_num AND h.association_num = a.association_num', 'inner')
			->where_in('a.assoc_name', $region_arr_in);
		return $this->getHerds($limit);
	}

	/**
	 * getHerdCodesByRegion
	 *
	 * @param string region number
	 * @return array of object herd
	 * @author ctranel
	 **/
	function getHerdCodesByRegion($region_arr_in, $limit = null){
		if (!isset($region_arr_in) || !is_array($region_arr_in) || empty($region_arr_in)) {
			return false;
		}	
		$this->db
			->join('address.dbo.association a', 'h.dhi_affiliate_num = a.affiliate_num AND h.association_num = a.association_num', 'inner')
			->where_in('a.assoc_name', $region_arr_in);
		return $this->getHerdCodes($limit);
	}

	/**
	 * @method getHerdsByPermissionGranted
	 * @param int consultant's user id
	 * @return array of stdClass with all herds for given tech num
	 * @access public
	 * @author ctranel
	 *
	 **/
	function getHerdsByPermissionGranted($sg_user_id = false){
		if(!$sg_user_id){
            $sg_user_id = $this->session->userdata('user_id');
        }
		$this->db->join('users.dbo.service_groups_herds ch', 'h.herd_code = ch.herd_code')
		->where('ch.sg_user_id', $sg_user_id)
		->where('(ch.exp_date > GETDATE() OR ch.exp_date IS NULL)')
		->where('request_status_id', 1);
		return $this->getHerds();
	}
	/**
	 * @method getHerdsByUser
	 *
	 * @param int user id
	 * @return simple array of herd codes
	 *         empty array if no herds found.
	 * @author Carol McCullough-Dieter
	 * @description This function queries the users_herds table and the herd_id table,
	 *           excluding herds that are expired for this user
	 *           and also excluding herds that are not active.
	 **/
	public function getHerdsByUser($user_id, $limit = false){
		
		if(!$user_id){
            $user_id = $this->session->userdata('user_id');
        }
		$this->db->join('users.dbo.users_herds uh', 'h.herd_code = uh.herd_code')
		->where('uh.user_id', $user_id)
		->where ('uh.isactive',1);
//		->where(' (users.dbo.users_herds.expire_date >'. now() . ' OR  users.dbo.users_herds.expire_date IS NULL) ');
		return $this->getHerds($limit,null);
		
		
	}

	/**
	 * @method getHerdsBySupervisor
	 *
	 * @param int user id
	 * @param mixed limit
	 * @return simple array of herd codes
	 *         empty array if no herds found.
	 * @author ctranel
	 **/
	public function getHerdsBySupervisor($user_id, $limit = false){
		if(!$user_id){
			$user_id = $this->session->userdata('user_id');
		}
		$this->db->join('users.dbo.users_dhi_supervisors us', 'CONCAT(\'SP\', h.supervisor_num) = us.supervisor_acct_num', 'inner')
			->where('us.user_id', $user_id);
		return $this->getHerds($limit,null);
	}

	/**
	 * getHerdsByCriteria
	 *
	 * @param array criteria (field=>value pairs)
	 * @param int limit
	 * @param int offset
	 * @param string order_by
	 * @return object herd
	 * @author ctranel
	 **/
	public function getHerdsByCriteria($criteria=null, $limit=null, $offset=null, $order_by=null)
	{
		$this->db->where($criteria);
		return $this->getHerds($limit, $offset, $order_by);
	}

	/**
	 * update_herds_by_criteria
	 *
	 * @param array field=>value combinations for update data
	 * @param array field=>value combinations for criteria
	 * @return mixed
	 * @author ctranel
	 **/
	public function update_herds_by_criteria($data, $criteria=null)
	{
		$this->db->where($criteria);
		return $this->db->update('herd.dbo.herd_id', $data);
	}

	/**
	 * insert_herd
	 *
	 * @param array field=>value combinations
	 * @return mixed
	 * @author ctranel
	 **/
	public function insert_herd($data)
	{
		return $this->db->insert('herd.dbo.herd_id', $data);
	}

	/**
	 * @method getHerds()
	 * @param int limit
	 * @param int offset
	 * @param string sort by field
	 * @return array of stdClass objects with all herds
	 * @access public
	 * @author ctranel
	 **/
	public function getHerds($limit=null, $offset=null, $order_by='herd_owner')
	{
		$this->db
		->join('address.dbo.email e', 'h.herd_code = e.account_num', 'left')
		->select('h.[herd_code],h.[farm_name],h.[herd_owner],h.[contact_fn],h.[contact_ln],h.[address_1],h.[address_2]
				,h.[city],h.[state],h.[zip_5],h.[zip_4],h.[primary_area_code],h.[primary_phone_num],h.[association_num]
				,h.[dhi_affiliate_num],h.[supervisor_num],h.[owner_privacy],h.[records_release_code], e.[email_address] AS email')
		->where("h.dhi_quit_date IS NULL");
		
		if(isset($order_by)){
            $this->db->order_by($order_by);
        }
		if (isset($limit) && isset($offset)){
            $this->db->limit($limit, $offset);
        }
		elseif(isset($limit)){
            $this->db->limit($limit);
        }
		$results = $this->db->get('herd.dbo.herd_id h')->result_array();
		return $results;
	}

	/**
	 * @method get_herd()
	 * @param string herd code
	 * @return 1D array of herd data
	 * @access public
	 * @author ctranel
	 **/
	public function get_herd($herd_code)
	{
		$this->db->where('h.herd_code', $herd_code);
		$arr_return = $this->getHerds(1,0);
		if(isset($arr_return[0])) return $arr_return[0];
		else return false;
	}

	/**
	 * @method getHerdCodes()
	 * @param int limit
	 * @param int offset
	 * @param string sort by field
	 * @return array of herd codes (1d)
	 * @access public
	 * @author ctranel
	 **/
	public function getHerdCodes($limit=null, $offset=null)
	{
		$this->db
		->select('h.[herd_code]')
		->where("h.dhi_quit_date IS NULL");
		
		if(isset($order_by)){
            $this->db->order_by($order_by);
        }
		if (isset($limit) && isset($offset)){
            $this->db->limit($limit, $offset);
        }
		elseif(isset($limit)){
            $this->db->limit($limit);
        }
		$results = $this->db->get('herd.dbo.herd_id h');
		$this->load->helper('multid_array_helper');
		return get_elements_by_key('herd_code', $results->result_array());
	}

	/**
	 * @method getHerdCodesByPermissionGranted
	 * @param int consultant's user id
	 * @return array of stdClass with all herds for given tech num
	 * @access public
	 *
	 **/
	function getHerdCodesByPermissionGranted($sg_user_id = false){
		if(!$sg_user_id) $sg_user_id = $this->session->userdata('user_id');
		$this->db->join('users.dbo.service_groups_herds ch', 'h.herd_code = ch.herd_code')
		->where('ch.sg_user_id', $sg_user_id)
		->where('(ch.exp_date > GETDATE() OR ch.exp_date IS NULL)')
		->where('request_status_id', 1);
		return $this->getHerdCodes();
	}

	/**
	 * @method getHerdDataByPermissions
	 * @param int consultant's user id
	 * @return array of arrays representing data table
	 * @access public
	 *
	 **/
	function getHerdDataByPermissions($sg_user_id = false){
		if(!$sg_user_id) $sg_user_id = $this->session->userdata('user_id');
		$this->db
			->select('rs.name AS status, ch.exp_date AS expires_date, ch.id')
			->join('users.dbo.service_groups_herds ch', 'h.herd_code = ch.herd_code', 'inner')
			->join('users.dbo.lookup_sg_request_status rs', 'ch.request_status_id = rs.id', 'inner')
		->where('ch.sg_user_id', $sg_user_id);
		return $this->getHerds();
	}

	/**
	 * @method getHerdCodesByUser
	 *
	 * @param int user id
	 * @return simple array of herd codes
	 *         empty array if no herds found.
	 * @description This function queries the users_herds table and the herd_id table,
	 *           excluding herds that are expired for this user
	 *           and also excluding herds that are not active.
	 **/
	public function getHerdCodesByUser($user_id, $limit = null){
		if(!$user_id){
			return false;
		}
		$this->db->join('users.dbo.users_herds' . ' uh', 'h.herd_code = uh.herd_code')
		->where('uh.user_id', $user_id)
		->where ('uh.isactive',1);
		return $this->getHerdCodes($limit,null);
	}

	/**
	 * @method getHerdCodesBySupervisor
	 *
	 * @param int user id
	 * @param mixed limit
	 * @return simple array of herd codes
	 *         empty array if no herds found.
	 **/
	public function getHerdCodesBySupervisor($user_id, $limit = null){
		if(!$user_id){
			return false;
		}
		$this->db->join('users.dbo.users_dhi_supervisors us', 'CONCAT(\'SP\', h.supervisor_num) = us.supervisor_acct_num', 'inner')
			->where('us.user_id', $user_id);
		return $this->getHerdCodes($limit,null);
	}

    /**
     * @method isMetric()
     * @param string herd code
     * @return array of data for the herd header record
     * @access public
     *
     **/
    public function isMetric($herd_code){
        $q = $this->db->select("CAST(CASE WHEN ho.[lbs_kilos_code] = 'K' THEN 1 ELSE 0 END AS BIT) AS is_metric")
            ->from('herd.dbo.herd_id h')
            ->join('[TD].[ro_herd].[herd_options] ho', 'h.herd_code = ho.herd_code', 'inner')
            ->where('h.herd_code',$herd_code);
        $ret = $q->get()->result_array();
        if(!empty($ret) && is_array($ret)){
            return $ret[0]['is_metric'];
        }
        else{
            return false;
        }
    } //end function

    /**
	 * @method header_info()
	 * @param string herd code
	 * @return array of data for the herd header record
	 * @access public
	 *
	 **/
	public function header_info($herd_code){
		$q = $this->db->select("h.herd_code, h.farm_name, h.herd_owner, h.breed_code, h.state, r.assoc_name, CONCAT(s.first_name, ' ', s.last_name) AS supervisor_name, FORMAT(ct.test_date,'MM-dd-yyyy') AS test_date, ct.cow_cnt AS herd_size, ct.milk_cow_cnt", FALSE)
		->from('herd.dbo.herd_id h')
		->join('[herd].[dbo].[view_herd_id_curr_test] ct', 'h.herd_code = ' . 'ct.herd_code', 'left')
		->join('address.dbo.association r', 'ct.association_num = r.association_num', 'left')
		->join('address.dbo.dhi_supervisor s', "CONCAT('SP', h.supervisor_num) = s.account_num", 'left')
		->where('h.herd_code',$herd_code);
		$ret = $q->get()->result_array();
		if(!empty($ret) && is_array($ret)){
            return $ret[0];
        }
		else{
            return false;
        }
	} //end function

	/**
	 * @method get_field()
	 * @abstract gets the given field name for the field that is currently loaded
	 * @param string field name
	 * @param string herd code
	 * @return mixed value for given field
	 * @access public
	 *
	 **/
	public function get_field($field_name, $herd_code = false){
		// results query
		if(!$herd_code) $herd_code = $this->session->userdata('herd_code');
		if(strlen($herd_code) != 8) return null;
		$q = $this->db
		->select($field_name)
		->from('herd.dbo.herd_id')
		->where('herd_code',$herd_code)
		->limit(1);
		$ret = $q->get()->result_array();
		if(!empty($ret) && is_array($ret)){
            return $ret[0][$field_name];
        }
		else return false;
	} //end function
	
	/**
	 * @method get_lookup_values()
	 * @param string field to be populated
	 * @return array
	 * @access public
	 * @author ctranel
	 **/
	public function get_lookup_values($data_field)
	{
		$arr_return = array(0=>'Select One');
		$results = $this->db
		->select('id, value, description')
		->get('users.dbo.lookup_' . $data_field)->result_array();
		foreach($results as $r){
			$arr_return[$r['id']] = $r['description'];
		}
		return $arr_return;
	}

	/**
	 * herd_is_registered
	 * @param string herd code
	 * @return bool
	 * @author ctranel
	 *     
	 **/
	public function herd_is_registered($herd_code){
		$arr_results = $this->db->select('herd_code')
		->from('users.dbo.users u')
		->join('users.dbo.users_herds uh', 'u.id = uh.user_id')
		->join('users.dbo.users_groups ug', 'u.id = ug.user_id')
		->where('uh.herd_code', $herd_code)
		->where('u.active', 1)
		->where('ug.group_id', 2)
		->get()
		->result_array();
		if(is_array($arr_results) && !empty($arr_results)){
            return true;
        }
		else return false;
	}

	/**
	 * get_herd_emails
	 * @description retrieves email address of all users association with the passed herd
	 * @param string herd code
	 * @return array of e-mail addresses
	 * @author ctranel
	 **/
	public function get_herd_emails($herd_code){
		$arr_results = $this->db->select('email')
		->from('users.dbo.users u')
		->join('users.dbo.users_herds uh', 'u.id = uh.user_id')
		->join('users.dbo.users_groups ug', 'u.id = ug.user_id')
		->where('uh.herd_code', $herd_code)
		->where('u.active', 1)
		->where('ug.group_id', 2)
		->get()
		->result_array();
		if(is_array($arr_results) && !empty($arr_results)) return $arr_results;
		else return false;
	}

	/**
	 * get_herd_test_dates_7
	 * @param string herd code
	 * @return array of test_dates from rpm.dbo.t13_herd_info
	 * @author Kevin Marshall
	 **/
	public function get_herd_test_dates_7($herd_code){
		$arr_results = $this->db->select('test_date_1,test_date_2,test_date_3,test_date_4,test_date_5,test_date_6,test_date_7')
		->from('rpm.dbo.t13_herd_info')
		->where('herd_code', $herd_code)
		->get()
		->result_array();
		if(is_array($arr_results) && !empty($arr_results)) return $arr_results;
		else return false;
	}

	public function get_test_dates_7_short($herd_code){
		$rpmdb = $this->load->database('default', true);
		$arr_results = $rpmdb->select('short_date_1,short_date_2,short_date_3,short_date_4,short_date_5,short_date_6,short_date_7')
		->from('vma.dbo.vma_Dates_Last_7_Tests')
		->where('herd_code', $herd_code)
		->get()
		->result_array();
		if(is_array($arr_results) && !empty($arr_results)) return $arr_results;
		else return false;
	}
	
	/**
	 * get_recent_test
	 * @param string herd code
	 * @return date string most recent test date
	 * @author ctranel
	 **/
	public function get_recent_test($herd_code){
		$result = $this->db
			->select('MAX(test_date) AS test_date')
			->where('herd_code', $herd_code)
			->get('[herd].[dbo].[herd_test_turnaround]')
			->result_array();
		if(is_array($result)){
			return $result[0]['test_date'];
		}
		return false;
	}
	
	/**
	 * getHerdEnrollmentData
	 * @param string herd code
	 * @param string or array of report codes
	 * @return array of herd output data arrays
	 * @author ctranel
	public function getHerdEnrollmentData($herd_code, $report_code = NULL){
		if(isset($report_code)){
			if(!is_array($report_code)){
				$report_code = array($report_code);
			}
			$this->db->where_in('report_code', $report_code);
		}
		else{
			$this->db->join('users.dbo.pages_dhi_products pr', 'ho.report_code = pr.report_code', 'inner');
		}
		$result = $this->db
			->select('ho.report_code, ho.herd_is_paying, ho.herd_is_active_trial')
			->distinct()
            ->where('herd_code', $herd_code)
			->where('end_date IS NULL')
			->where('activity_code !=', 'Q')
			->get('[users].[dbo].[v_user_status_info] ho')
			->result_array();
		if(is_array($result)){
			return $result;
		}
		return [];
	}
**/

	/**
	 * addHerdOutput
	 * @param string herd code
	 * @param string or array of report codes
	 * @return boolean successful
	 * @author ctranel
	 **/
	public function addHerdOutput($herd_code, $report_code){
		if(!isset($herd_code) || empty($herd_code)){
			throw new \exception('Herd code specified');
		}
        if(!isset($report_code) || empty($report_code)){
            throw new \exception('Report code specified');
        }

        $herd_code = \myagsource\MssqlUtility::escape($herd_code);
        $report_code = \myagsource\MssqlUtility::escape($report_code);
        $seq_num_sql = "SELECT (max(seq_num)  + 1) FROM herd.dbo.herd_output WHERE seq_num < 30 GROUP BY herd_code HAVING herd_code = '" . $herd_code . "'";

        $sql = "INSERT INTO herd.dbo.herd_output (herd_code, report_code, seq_num, send_to_num, bill_account_num, copy_cnt, medium_type_code, start_date, activity_code)"
            . "VALUES ('$herd_code', $report_code,($seq_num_sql), '00000001', 'AS035099', 1, 'W', '" . date('Y-m-d') . "', 'A')";

        if($result = $this->db->query($sql)){
            return true;
        }

    	return false;
	}

	/**
	 * getCowList
	 * @param string herd code
	 * @param string or array id fields
     * @param boolean show_heifers
     * @param boolean show_bulls
     * @param boolean show_sold
	 * @return array
	 * @author ctranel
	 **/
	public function getCowList($herd_code, $id_field, $show_heifers, $show_bulls, $show_sold){
		if(empty($id_field)){
		    throw new Exception('Cow ID field not specified');
        }

        if(!$show_heifers){
            $this->db->where('curr_lact_num > ', 0);
        }
        if(!$show_bulls){
            $this->db->where('sex_cd', 1);
        }
        if(!$show_sold){
            $this->db->where('isactive', 1);
        }

	    $result = $this->db
		->select('serial_num')
		->select($id_field)
		->where('herd_code', $herd_code)
		->order_by('users.dbo.naturalize(' . $id_field . ')')
		->get('[TD].[animal].[id]')
		->result_array();
		if(is_array($result)){
			return $result;
		}
		return false;
	}


	/**
	 * getHerdPagesData
	 * @param string herd code
	 * @return array of section data
	 * @author ctranel
	 **/
	public function getHerdPagesData($herd_code) {
		if(!isset($herd_code) || empty($herd_code)){
			return false;
		}
	
		$sql = "
			SELECT a.*, ls.name AS scope FROM (
				SELECT p.id, section_id, name, description, scope_id, path, isactive, list_order, pr.report_code
				FROM users.dbo.pages p
					INNER JOIN users.dbo.pages_dhi_products pr ON (p.id = pr.page_id AND p.isactive = 1 AND p.scope_id = 2)
					INNER JOIN users.dbo.v_user_status_info si ON pr.report_code = si.report_code AND si.herd_code = '" . $herd_code . "' AND (si.herd_is_paying = 1 OR si.herd_is_active_trial = 1)
	
				UNION ALL
	
				SELECT id, section_id, name, description, scope_id, path, isactive, list_order, NULL AS report_code
				FROM users.dbo.pages p
				WHERE scope_id = 1 AND isactive = 1
			) a
		
			INNER JOIN users.dbo.lookup_scopes ls ON a.scope_id = ls.id
			ORDER BY list_order
		";
		return $this->db->query($sql)->result_array();
	}
	
	/**
	 * getHerdPagesData
	 * @param string herd code
	 * @return array of section data
	 * @author ctranel
	 **/
	public function getTrialData($herd_code, $report_code = null) {
		if(isset($report_code) && !empty($report_code)){
			if(!is_array($report_code)){
				$report_code = [$report_code];
				$this->db->where_in('si.report_code', $report_code);
			}
		}
		$r = $this->db
			->distinct()
			->select('si.herd_is_active_trial, si.herd_trial_is_expired, si.herd_trial_warning, si.herd_trial_expires, rd.value_abbrev')
			->join('dhi_tables.dbo.report_code_definition rd', 'si.report_code = rd.code_value', 'inner')
			->where('si.herd_code', $herd_code)
			->where('si.herd_is_on_test', 1)
			->where('si.herd_is_on_report', 1)
			->where('si.herd_has_active_web_user', 1)
			->where('(si.herd_is_active_trial = 1 OR si.herd_trial_is_expired = 1)')
			->get('users.dbo.v_user_status_info si')->result_array();
		return $r;
	}

    /**
     * getEventMap
     * @param string herd code
     * @return array of section data
     * @author ctranel
     **/
    public function getEventMap($herd_code) {
        $r = $this->db
            ->select('event_cat, event_cd')
            ->where('herd_code', $herd_code)
            ->get('td.herd.events')->result_array();
        return $r;
    }

}
