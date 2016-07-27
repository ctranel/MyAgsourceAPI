<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Name:  Permissions Model
 *
 * Author:  ctranel
 * 		   ctranel@agsource.com
 *
 * Created:  2016-03-31
 *
 * Description:  Retrieves data related to permission
 *
 * Requirements: PHP5 or above
 *
 */

class Permissions_Model extends CI_Model
{
    public function __construct(){
        parent::__construct();
    }

    /**
     * @method getGroupPermissionsData
     *
     * @param int group id
     * @return array tasks for which the group has permissions
     * @author ctranel
     **/
    public function getGroupPermissionsData($group_id){
        $results = $this->db->select('name, description')
            ->join('users.dbo.groups_tasks gt', 't.id = gt.task_id', 'inner')
            ->where('gt.group_id', $group_id)
            ->get('users.dbo.tasks t')
            ->result_array();

        return $results;
    }

    /**
     * @method getProductPermissionsData
     *
     * @param array of strings report code
     * @return array tasks for which the group has permissions
     * @author ctranel
     **/
    public function getProductPermissionsData($report_codes){
        $results = $this->db
            ->select('name, description')
            ->distinct()
            ->join('users.dbo.dhi_products_tasks rt', 't.id = rt.task_id', 'inner')
            ->where_in('rt.report_code', $report_codes)
            ->get('users.dbo.tasks t')
            ->result_array();

        return $results;
    }
}