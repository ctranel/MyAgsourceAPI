<?php
namespace myagsource\web_content;

use myagsource\web_content\iWebContent;
/**
* Name:  Sections
*
* Author: ctranel
*  
* Created:  11-24-2014
*
* Description:  Contains properties and methods specific to displaying sections of the website.
*
* Requirements: PHP5 or above
* 
* @todo: this library will be the basis for pages, blocks, etc, and will eventually have an abstract and/or interface to reflect the commonalities
*
*/

class Section //implements iWebContent
{
	/**
	 * datasource
	 * @var object
	 **/
	protected $datasource;

	/**
	 * section parent_id
	 * @var boolean
	 **/
	protected $parent_id;

	/**
	 * section id
	 * @var int
	 **/
	protected $id;

	/**
	 * section name
	 * @var string
	 **/
	protected $name;
	
	/**
	 * scope
	 * @var string
	 **/
	protected $scope;
	
	/**
	 * section description
	 * @var string
	 **/
	protected $description;
	
	/**
	 * section path
	 * @var string
	 **/
	protected $path;
	
	/**
	 * collection of section objects
	 * @var SplObjectStorage
	 **/
	protected $child_sections;
	
	/**
	 * collection of page objects
	 * @var SplObjectStorage
	 **/
	protected $child_pages;
	
	

	/**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct($datasource, $id, $parent_id, $name, $description, $scope, $path) {
		$this->datasource = $datasource;
		$this->parent_id = $parent_id;
		$this->name = $name;
		$this->description = $description;
		$this->scope = $scope;
		$this->path = $path;
		$this->id = $id;
	}

	public function childKeyValuePairs(){
		
	}
	
	/**
	 * @method setChildren()
	 * @param int user id
	 * @param string herd code
	 * @param array section scopes to include
	 * @param boolean has permission to view non-owned herds
	 * @param boolean has permission to view non-owned herds only with owners explicit permission
	 * @return void
	 * @access public
	 *
	 *
	 *need users_owns_herd, 
	 **/
	public function setChildren($user_id, $herd_code, array $arr_scope, $view_non_own, $view_non_own_w_permission ){//Array $array){ //array directly from data source
		$tmp_array = [];
		foreach($arr_scope as $s){
			switch ($s) {
				/* 
				 * subscription is different in that it fetches content by herd data (i.e. herd output) for users that 
				 * have permission only for subscribed content.  All other scopes are strictly users-based
				 */
				case 'subscription':
					$a = $this->datasource->getSubscribedSectionsArray($user_id, $this->id, $herd_code, $view_non_own);
					if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
					break;
/* 				not currently used
				case 'unmanaged':
					$a = $this->datasource->get_unmanaged_sections_array();
					if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
					break;
*//*
				case 'admin':
					if($this->is_admin){
						$a = $this->datasource->get_child_sections_by_scope($s, $super_id);
						if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
					}
					break;
*/
				default: //public, account, user-specific
/*
 * @todo: remove database-specific reference (ls.name)
 */					$criteria = ['ls.name' => $s, 'parent_id' => $this->id];
					$a = $this->datasource->getByCriteria($criteria);
					if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
					break;
			}
		}

		if(!$view_non_own && $view_non_own_w_permission && TRUE && !empty($herd_code)){
			if(is_array($tmp_array) && !empty($tmp_array)){
				$this->child_sections = new \SplObjectStorage();
				foreach($tmp_array as $k => $v){
					//if($this->ion_auth_model->userHasPermission($user_id, $herd_code, $v['id'])){
						$this->child_sections.attach(new Section($this->datasource, $v['id'], $v['parent_id'], $v['name'], $v['description'], $v['scope'], $v['path']));
					//}
				}
				return $arr_return;
			}
			return FALSE;
		}

		else return $tmp_array;
	}
	public function children(){
		return $this->child_sections;
	}
	public function childrenList(){
	}
	
	
	public function hasChildren(){}
//	public function setParentObj(){}
//	public function parentObj(){}
	public function hasParent(){}
	
	public function getChildrenByScope(){}
	public function getChildrenByHerd(){}
	public function getChildrenByUser(){}
	public function getChildrenByPath(){}
		
	/**
	 * @method subscribed_section
	 *
	 * @param int section id
	 * @return boolean - true if the user is signed up for the specified section
	 * @author ctranel
	public function subscribed_section($id){
		return TRUE;
		$tmp_array = $this->arr_user_sections;
		if(isset($tmp_array) && is_array($tmp_array)){
			$this->load->helper('multid_array_helper');
			$tmp_arr_sections = array_extract_value_recursive('id', $tmp_array);
			return in_array($id, $tmp_arr_sections);
		}
		else return false;
	}
	 **/
	
	
}


