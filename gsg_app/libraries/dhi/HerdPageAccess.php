<?php
namespace myagsource\dhi;

use myagsource\dhi\Herd;
use myagsource\Site\WebContent\Page;


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

class HerdPageAccess
{
	/**
	 * datasource
	 * @var object
	 **/
	protected $datasource;

	/**
	 * herd
	 * @var Herd
	 **/
	protected $herd;

	/**
	 * page
	 * @var Page
	 **/
	protected $page;

	/**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct($datasource, Herd $herd, Page $page) {
		$this->datasource = $datasource;
		$this->herd = $herd;
		$this->page = $page;
	}

	/**
	 * @method hasAccess()
	 * @return boolean
	 * @access public
	 **/
	public function hasAccess(){
		$page_data = $this->datasource->getHerdPagesData($this->herd->herdCode());
		if(!isset($page_data) || empty($page_data)){
			return [];
		}
		foreach($page_data as $p){
			if($p['id'] === $this->page->id()){
				return true;
			}
		}
		
		return false;
	}

	/**
	 * @method getAccessibleHerdsData()
	 * @param int user_id
	 * @param int region_num (need to accept array?)
	 * @return mixed array of herd data or boolean
	 * @access public
	 *
	
	public function getAccessiblePages(){
		if(!$user_id || !$arr_permissions){
			return false;
		}
		$arr_return_reg = [];
		$arr_return_user = [];
		$arr_return_permission = [];
	
		if(in_array('View All Herds', $arr_permissions)){
			return $this->datasource->getHerds();
		}
		if(in_array('View Herds In Region', $arr_permissions)){
			if(!isset($arr_regions) || !is_array($arr_regions)){
				return FALSE;
			}
			$tmp = $this->datasource->getHerdsByRegion($arr_regions, $limit_in);
			if(isset($tmp) && is_array($tmp)) $arr_return_reg = $tmp;
			unset($tmp);
		}
		if(in_array('View Assigned Herds', $arr_permissions)){
			$arr_return_user = $this->datasource->getHerdsByUser($user_id, $limit_in);
		}
		if(in_array('View Assign w permission', $arr_permissions)){
			$arr_return_permission = $this->datasource->getHerdsByPermissionGranted($limit_in);
		}
		return array_merge($arr_return_reg, $arr_return_user, $arr_return_permission);
	}
	 **/
}




