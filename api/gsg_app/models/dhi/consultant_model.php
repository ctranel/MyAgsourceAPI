<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Name:  Consultant Model
 *
 * Author:  ctranel (extends class by Ben Edmunds)
 * 		   ctranel@agsource.com
  *
 * Created:  06.29.2012
 *
 * Description:  Extends model created bu Ben Edmunds
 *
 * Requirements: PHP5 or above
 *
 */
require_once APPPATH . 'models/ion_auth_parent_model.php';

class Consultant_model
{
	public function __construct()
	{
		parent::__construct();
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
            ->select('service_groups_herds_id, herd_code, exp_date, sg_user_id, first_name, last_name, account_name, request_status_id, request_status_id_text, e.[email_address] AS email')
            ->where('sg_user_id', $sg_user_id)

        if(isset($order_by)){
            $this->db->order_by($order_by);
        }
        else{
            $this->db->order_by('herd_code', 'asc');
        }

        $results = $this->db->get('users.sg.vmat_service_groups_herds')->result_array();
        return $results;
    }

    /**
     * @method getServiceGroupDataByHerd
     *
     * @param string herd code
     * @return array of consultant records, keyed by consultant status
     * @author ctranel
     **/
    public function getServiceGroupDataByHerd($herd_code){
        $result = $this->db
            ->select('service_groups_herds_id, herd_code, exp_date, sg_user_id, first_name, last_name, account_name, request_status_id, request_status_id_text')
            ->from('users.sg.vmat_service_groups_herds')
//		->where('(ch.exp_date IS NULL OR ch.exp_date > GETDATE())')
            ->where('herd_code', $herd_code)
            ->get()
            ->result_array();

        if(isset($result)){
            return $result;
        }
        else return FALSE;
    }

    /**
	 * @method get_consult_relationship_id
	 *
	 * @param string consultant user id
	 * @param string herd code
	 * @return int/bool id of record matching params
	 * @author ctranel
	 **/
	public function get_consult_relationship_id($sg_user_id, $herd_code){
		$this->db->select('id');
		$result = $this->get_consult_relationship($sg_user_id, $herd_code);
		if(!empty($result)) return $result['id'];
		else return FALSE;
	}

	/**
	 * @method get_consult_relationship
	 *
	 * @param string consultant user id
	 * @param string herd code
	 * @return array/bool of fields of record matching params, false if none found
	 * @author ctranel
	 **/
	public function get_consult_relationship($sg_user_id, $herd_code){
		$result = $this->db->get_where('users.sg.service_groups_herds', array('sg_user_id' => $sg_user_id, 'herd_code' => $herd_code))->result_array();
		if(!empty($result)) return $result[0];
		else return FALSE;
	}

	/**
	 * @method get_consult_relationship_by_id
	 *
	 * @param int relationship id
	 * @return array/bool of fields of record matching params, false if none found
	 * @author ctranel
	 **/
	public function get_consult_relationship_by_id($id){
		$result = $this->db
			->where(array('id' => $id))
			->get('users.sg.service_groups_herds')->result_array();
		if(!empty($result)) return $result[0];
		else return FALSE;
	}

	/**
	 * @method get_service_group_account
	 *
	 * @param string service group account number
	 * @return array of consultant records, keyed by consultant status
	 * @author ctranel
	 **/
	public function get_service_group_account($service_grp){
		$result = $this->db
			->select('account_num, account_name')
			->where('account_num', $service_grp)
			->get('address.dbo.service_group')
			->result_array();
		if(count($result) > 0){
			return $result[0];
		}
		return null;
	}
	
	/**
	 * @method batch_grant_consult
	 *
	 * @param array permission record ids to be modified
	 * @return boolean
	 * @author ctranel
	 **/
	public function batch_grant_consult($arr_modify_ids){
		return $this->batch_update_status($arr_modify_ids, 1);
	}

	/**
	 * @method batch_deny_consult
	 *
	 * @param array permission record ids to be modified
	 * @return boolean
	 * @author ctranel
	 **/
	public function batch_deny_consult($arr_modify_ids){
		return $this->batch_update_status($arr_modify_ids, 2);
	}

	/**
	 * @method batch_consult_revoke
	 * @description consultant initiates revocation of access
	 * @param array permission record ids to be modified
	 * @return boolean
	 * @author ctranel
	 **/
	public function batch_consult_revoke($arr_modify_ids){
		return $this->batch_update_status($arr_modify_ids, 3);
	}

	/**
	 * @method batch_herd_revoke
	 * @description herd initiates revocation of access
	 * @param array permission record ids to be modified
	 * @return boolean
	 * @author ctranel
	 **/
	public function batch_herd_revoke($arr_modify_ids){
		return $this->batch_update_status($arr_modify_ids, 4);
	}

	/**
	 * @method batch_update_status
	 *
	 * @param array permission record ids to be modified
	 * @return boolean
	 * @author ctranel
	 **/
	public function batch_update_status($arr_modify_ids, $new_status_id){
		if(isset($arr_modify_ids) && is_array($arr_modify_ids)){
			$result = $this->db
			->where_in('id', $arr_modify_ids)
			->update('users.sg.service_groups_herds', array('request_status_id' => $new_status_id));
			return $result;
		}
		else return FALSE;
	}

	/**
	 * @method batch_remove_consult_expire
	 *
	 * @param array permission record ids to be modified
	 * @return boolean
	 * @author ctranel
	 **/
	public function batch_remove_consult_expire($arr_modify_ids){
		if(isset($arr_modify_ids) && is_array($arr_modify_ids)){
			$result = $this->db
			->where_in('id', $arr_modify_ids)
			->update('users.sg.service_groups_herds', array('exp_date' => NULL));
			return $result;
		}
		else return FALSE;
	}

	/**
	 * @method get_consult_status_text
	 *
	 * @param int relationship id
	 * @return string consultant request status
	 * @author ctranel
	 **/
	public function get_consult_status_text($id){
		$result = $this->db
		->select('lus.name')
		->join('users.sg.lookup_sg_request_status lus', 'ch.request_status_id = lus.id')
		->get_where('users.sg.service_groups_herds ch', array('ch.id' => $id))->result();
		if(!empty($result)) return $result[0]->name;
		else return FALSE;
	}
	
	/**
	 * @method set_consult_relationship
	 *
	 * @param array of data to be inserted into DB
	 * @param int/NULL original id of relationship record
	 * @return int/bool insert id if insert is successful, FALSE if not
	 * @author ctranel
	 **/
	public function set_consult_relationship($arr_relationship_data, $old_id = NULL){
		$success = FALSE;
		if(isset($old_id) && !empty($old_id)) {
			$this->db->where('id', $old_id);
			if($this->db->update('users.sg.service_groups_herds', $arr_relationship_data)){
				$success = $old_id;
			}
		}
		else {
			if($this->db->insert('users.sg.service_groups_herds', $arr_relationship_data)){
				$success = $this->db->insert_id();
			}
		}
		return $success;
	}

	/**
	 * @method get_consult_rel_sections
	 *
	 * @param int id of relationship record
	 * @return array/bool array of section ids, FALSE if none found
	 * @author ctranel
	 **/
	function get_consult_rel_sections($rel_id){
		$result = $this->db
		->select('section_id')
		->get_where('users.sg.service_groups_herds_sections', array('service_groups_herds_id'=>$rel_id))
		->result_array();
		if(!empty($result)) return array_flatten($result);
		else return FALSE;
	}

	/**
	 * @method set_consult_sections
	 *
	 * @param array of sections to be inserted into DB
	 * @param int id of relationship record for current update
	 * @param int/NULL original id of relationship record
	 * @return bool TRUE if insert is successful, FALSE if not
	 * @author ctranel
	 **/
	public function set_consult_sections($arr_section_id, $consult_relationship_id, $old_id = NULL){
		if(isset($old_id) && !empty($old_id)) {
			$this->db->delete('users.sg.service_groups_herds_sections', array('service_groups_herds_id' => $old_id));
		}
		if(is_array($arr_section_id) && !empty($arr_section_id)){
			foreach($arr_section_id as $a){
				$success = $this->db->insert('users.sg.service_groups_herds_sections', array('service_groups_herds_id' => $consult_relationship_id, 'section_id' => $a));
				if(!$success) return FALSE;
			}
			return TRUE;
		}
		else return TRUE;
	}

	/**
	 * @method userHasPermission
	 *
	 * @param int consultant id
	 * @param string herd code
	 * @param int section id
	 * @return bool
	 * @author ctranel
	 **/
	public function userHasPermission($sg_user_id, $herd_code, $section_id){
		$results = $this->db
		->select('sg_user_id')
		->from('users.sg.service_groups_herds ch')
		->join('users.sg.service_groups_herds_sections' . ' chs', 'ch.id = chs.service_groups_herds_id')
		->where('ch.sg_user_id = ' . $sg_user_id)
		->where('ch.herd_code = ' . $herd_code)
		->where('(ch.exp_date IS NULL OR ch.exp_date > GETDATE())')
		->where('ch.request_status_id', 1)
		->where('chs.section_id = ' . $section_id)
		->count_all_results();

		if($results > 0) return TRUE;
		else return FALSE;
	}
	
	/**
	 * @method permissionGrantedSections
	 *
	 * @param int consultant id
	 * @param string herd code
	 * @return bool
	 * @author ctranel
	 **/
	public function permissionGrantedSections($sg_user_id, $herd_code){
		$results = $this->db
		->select('chs.section_id')
		->from('users.sg.service_groups_herds ch')
		->join('users.sg.service_groups_herds_sections' . ' chs', 'ch.id = chs.service_groups_herds_id')
		->where('ch.sg_user_id = ' . $sg_user_id)
		->where('ch.herd_code', $herd_code)
		->where('(ch.exp_date IS NULL OR ch.exp_date > GETDATE())')
		->where('ch.request_status_id', 1)
		->get()
		->result_array();
		 $ret = array_flatten($results);
		return $ret;
	}
}
