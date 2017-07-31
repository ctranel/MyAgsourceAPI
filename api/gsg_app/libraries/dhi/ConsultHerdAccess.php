<?php
namespace myagsource\dhi;

use myagsource\dhi\Herd;
use myagsource\Site\iPage;


/**
* Name:  HerdAccess
*
* Author: ctranel
*  
* Created:  12-12-2014
*
* Description:  Provides information about a herd's accessible pages (AKA reports).
*
* Requirements: PHP5 or above
*/

class ConsultHerdAccess
{
	/**
	 * datasource
	 * @var service_group_model
	 **/
	protected $datasource;

	/**
	 * herd
	 * @var Herd
	 **/
	protected $herd;

	/**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct($datasource, Herd $herd) {
		$this->datasource = $datasource;
		$this->herd = $herd;
    }

    /**
     * @method getHerdPermissionsByStatus()
     * @description does service group account number exist?
     * @param string service group user_id
     * @return array
     * @access public
     *
     **/
    public function getHerdPermissionsByStatus($sg_user_id){
        if(empty($sg_user_id)){
            $this->set_error('service_group_required');
            return false;
        }
        $herds = $this->datasource->getHerdDataByPermissions($sg_user_id);
        if(!isset($herds) || !is_array($herds)){
            return false;
        }

        $arr_herds = [];
        foreach($herds as $h){
            $exp_date = new \DateTime($h['expires_date']);
            if($h['status'] === 'grant' && $h['expires_date'] !== null && $exp_date < new \DateTime()){
                $arr_herds['expired'][] = $h;
            }
            else {
                $arr_herds[$h['status']][] = $h;
            }
        }
        return $arr_herds;
    }

    /**
     * @method getConsultantsByHerd()
     * @description does service group account number exist?
     * @param string service group user_id
     * @return array
     * @access public
     *
     **/
    public function getConsultantsByHerd($herd_code){
        if(empty($herd_code)){
            $this->set_error('herd_code_required');
            return false;
        }
        $sgs = $this->datasource->getServiceGroupDataByHerd($herd_code);
        if(!isset($sgs) || !is_array($sgs)){
            return false;
        }

        $arr_sgs = [];
        foreach($sgs as $h){
            $exp_date = new \DateTime($h['exp_date']);
            if($h['request_status'] === 'grant' && $h['exp_date'] !== null && $exp_date < new \DateTime()){
                $arr_sgs['expired'][] = $h;
            }
            else {
                $arr_sgs[$h['request_status']][] = $h;
            }
        }
        return $arr_sgs;
    }

    /**
     * @method service_grp_exists()
     * @description does service group account number exist?
     * @param string service group account number
     * @return boolean
     * @access public
     *
     **/
    public function service_grp_exists($sg_acct_num){
        if(empty($sg_acct_num)){
            $this->set_error('service_group_required');
            return FALSE;
        }
        $tmp = $this->datasource->get_service_group_account($sg_acct_num);
        if(isset($tmp)){
            return TRUE;
        }
        $this->set_error('service_group_not_found');
        return FALSE;
    }

    /**
     * @method allow_service_grp()
     * @description write consultant-herd relationship record
     * @param string consultant user id
     * @param string herd code
     * @param array sections for which consultant should be give permissions
     * @return boolean
     * @access public
     *
     **/
    public function allow_service_grp($arr_relationship_data, $arr_section_id) {
        $old_relationship_id = $this->datasource->get_consult_relationship_id($arr_relationship_data['sg_user_id'], $arr_relationship_data['herd_code']);
        //insert into consulants_herds
        $relationship_id = $this->datasource->set_consult_relationship($arr_relationship_data, $old_relationship_id);
        //insert each section into consulants_herds_sections
        if($relationship_id !== FALSE){
            $success = $this->datasource->set_consult_sections($arr_section_id, $relationship_id, $old_relationship_id);
            if($success){
                $this->set_message('consultant_status_update_successful');
                //send e-mail
                $arr_herd_info = $this->herd_model->header_info($this->session->userdata('herd_code'));
                $consultant_info = $this->datasource->user($arr_relationship_data['sg_user_id'])->result_array();
                $consultant_info = $consultant_info[0];

                if($arr_relationship_data['request_status_id'] == 1) $message = $this->load->view($this->config->item('email_templates', 'ion_auth').$this->config->item('consult_granted', 'ion_auth'), $arr_herd_info, true);
                elseif($arr_relationship_data['request_status_id'] == 2) $message = $this->load->view($this->config->item('email_templates', 'ion_auth').$this->config->item('consult_denied', 'ion_auth'), $arr_herd_info, true);

                if(isset($message)){
                    $this->email->clear();
                    $this->email->from($this->config->item('admin_email'), $this->config->item('site_title'));
                    $this->email->to($consultant_info['email']);
                    $this->email->cc($this->session->userdata('email'));
                    $this->email->subject($this->config->item('site_title') . ' - Consultant Access');
                    $this->email->message($message);

                    if ($this->email->send() == TRUE) {
                        $this->set_message('consultant_status_email_successful');
                        return TRUE;
                    }
                }
                $this->set_error('consultant_status_email_unsuccessful');
                return TRUE; // Even if e-mail is not sent, consultant info was recorded
            }
        }
        $this->set_error('consultant_status_update_unsuccessful');
        return FALSE;
    }

    /**
     * @method service_grp_request()
     * @abstract write consultant-herd relationship record
     * @param array relationship data
     * @param array sections for which consultant should be give permissions
     * @param date expiration date for access
     * @return boolean
     * @access public
     *
     **/
    public function service_grp_request($arr_relationship_data, $arr_section_id) {
        $old_relationship_id = $this->datasource->get_consult_relationship_id($arr_relationship_data['sg_user_id'], $arr_relationship_data['herd_code']);
        //insert into consulants_herds
        $relationship_id = $this->datasource->set_consult_relationship($arr_relationship_data, $old_relationship_id);
        //insert each section into consulants_herds_sections
        if($relationship_id !== FALSE){
            $success = $this->datasource->set_consult_sections($arr_section_id, $relationship_id, $old_relationship_id);
            if($success){
                $this->set_message('consultant_request_recorded');
                $this->send_consultant_request($arr_relationship_data, $relationship_id);
                return TRUE; // Even if e-mail is not sent, consultant info was recorded
            }
        }
        return FALSE;
    }

    /**
     * @method send_consultant_request()
     * @abstract write consultant-herd relationship record
     * @param string herd code
     * @param int consultant's user id
     * @param array sections for which consultant should be give permissions
     * @param date expiration date for access
     * @return boolean
     * @access public
     **/
    function send_consultant_request($arr_relationship_data, $relationship_id){
        //send e-mail
        $consultant_info = $this->datasource->user($arr_relationship_data['sg_user_id'])->result_array();
        $consultant_info[0]['relationship_id'] = $relationship_id;
        $email_data = array(
            'id' => $consultant_info[0]['id'],
            'company' => '',//$consultant_info[0]['company'],
            'first_name' => $consultant_info[0]['first_name'],
            'last_name' => $consultant_info[0]['last_name'],
            'herd_code' => $arr_relationship_data['herd_code'],
            'sg_acct_num' => $consultant_info[0]['sg_acct_num'],
        );
        $message = $this->load->view($this->config->item('email_templates', 'ion_auth').$this->config->item('service_grp_request', 'ion_auth'), $email_data, TRUE);
        $arr_herd_emails = $this->herd_model->get_herd_emails($arr_relationship_data['herd_code']);
        //If there are no matching users, send email to customer service
        if(!isset($arr_herd_emails) || !is_array($arr_herd_emails)){
            return true;
        }

        $arr_herd_emails = array_flatten($arr_herd_emails);
        if(isset($message)){
            $this->email->clear();
            $this->email->from($this->config->item('admin_email'), $this->config->item('site_title'));
            $this->email->to($arr_herd_emails);
            //$this->email->cc($this->session->userdata('email'));
            $this->email->subject($this->config->item('site_title') . ' - Consultant Access');
            $this->email->message($message);

            if ($this->email->send() == TRUE) {
                $this->set_message('consultant_status_email_successful');
                return TRUE;
            }
        }
        $this->set_error('consultant_status_email_unsuccessful');
        return FALSE;
    }
}
