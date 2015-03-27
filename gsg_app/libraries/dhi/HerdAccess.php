<?php
namespace myagsource\dhi;

use myagsource\dhi\Herd;
/**
* Name:  HerdAccess
*
* Author: ctranel
*  
* Created:  12-12-2014
*
* Description:  Provides information about a user's access to herds.
*
* Requirements: PHP5 or above
*/

class HerdAccess
{
	/**
	 * datasource
	 * @var object
	 **/
	protected $datasource;

	/**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct($datasource) {
		$this->datasource = $datasource;
	}

	/**
	 * Returns a list of herds to which user has access
	 * 
	 * @method herdList()
	 * @param int user id
	 * @param array regions to which user belongs
	 * @param array list of permissions
	 * @return void
	 * @access public
	 **/
	public function herdCodeList($user_id, array $arr_regions, array $arr_permissions){
		if(in_array('View All Herds', $arr_permissions)){
			return $this->datasource->getHerds();
		}
		$arr_herds = [];
		if(in_array('View Herds in Region', $arr_permissions)){
			array_merge($arr_herds, $this->datasource->getHerdCodesByRegion($arr_regions));
		}
		if(in_array('View Assigned Herds', $arr_permissions)){
			array_merge($arr_herds, $this->datasource->getHerdCodesByUser($user_id));
		}
		if(in_array('View Assign w permission', $arr_permissions)){
			array_merge($arr_herds, $this->datasource->getHerdCodesByPermissionGranted($user_id));
		}
		
		return $arr_herds;
	}
	
	/**
	 * @method hasAccess()
	 * @param int user id
	 * @param string herd code
	 * @param array regions to which user belongs
	 * @param array list of permissions
	 * @return void
	 * @access public
	 **/
	public function hasAccess($user_id, $herd_code, array $arr_regions, array $arr_permissions){
		if(!$user_id || !$herd_code || !$arr_permissions){
			return false;
		}
		if(in_array('View All "Herds', $arr_permissions)){
			return true;
		}
		if(in_array('View Herds in Region', $arr_permissions)){
			if(in_array($herd_code, $this->datasource->getHerdCodesByRegion($arr_regions))){
				return true;
			}
		}
		if(in_array('View Assigned Herds', $arr_permissions)){
			if(in_array($herd_code, $this->datasource->getHerdCodesByUser($user_id))){
				return true;
			}
		}
		if(in_array('View Assign w permission', $arr_permissions)){
			if(in_array($herd_code, $this->datasource->getHerdCodesByPermissionGranted($user_id))){
				return true;
			}
		}
		return false;
	}

	/**
	 * @method getAccessibleHerdCodes()
	 * @param int user_id
	 * @param int region_num (need to accept array?)
	 * @return mixed array of herds or boolean
	 * @access public
	 *
	 **/
	
	public function getAccessibleHerdCodes($user_id, $arr_permissions, $arr_regions = false, $limit_in = NULL){
		if(!$user_id || !$arr_permissions){
			return false;
		}
		$arr_return_reg = array();
		$arr_return_user = array();
		$arr_return_permission = array();
	
		if(in_array('View All Herds', $arr_permissions)){
			return $this->datasource->getHerdCodes(null, null, $limit_in);
		}
		if(in_array('View Herds in Region', $arr_permissions)){
			if(!isset($arr_regions) || !is_array($arr_regions)){
				return FALSE;
			}
			//extract region number from account number
			$arr_region_nums = array();
			foreach($arr_regions AS $k => $v){
				$arr_region_nums[] = substr($k, -3);
			}
			$tmp = $this->datasource->getHerdCodesByRegion($arr_region_nums, $limit_in);
			if(isset($tmp) && is_array($tmp)) $arr_return_reg = $tmp;
			unset($tmp);
		}
		if(in_array('View Assigned Herds', $arr_permissions)){
			$arr_return_user = $this->datasource->getHerdCodesByUser($user_id, $limit_in);
		}
		if(in_array('View Assign w permission', $arr_permissions)){
			$arr_return_permission = $this->datasource->getHerdCodesByPermissionGranted($limit_in);
		}
		return array_merge($arr_return_reg, $arr_return_user, $arr_return_permission);
	}
	
	/**
	 * @method getNumAccessibleHerds()
	 * @param int user_id
	 * @param array of region acct_num=>name
	 * @return mixed array of herds or boolean
	 * @access public
	 *
	 **/
	public function getNumAccessibleHerds($user_id, $arr_permissions,  $arr_regions = false){
		return count($this->getAccessibleHerdCodes($user_id, $arr_permissions, $arr_regions));
	}
}




