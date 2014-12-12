<?php
namespace myagsource\web_content;

use myagsource\web_content\iWebContent;
use myagsource\dhi\Herd;
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
	
	public function path(){
		return $this->path;
	}

	public function name(){
		return $this->name;
	}

	public function childKeyValuePairs(){
		
	}
	
	/**
	 * @method setChildren()
	 * @param int user id
	 * @param Herd herd
	 * @param array task permissions
	 * @return void
	 * @access public
	 **/
	//if we allow producers to select which sections to allow, we will need to pass that array to this section as well
	public function setChildren($user_id, Herd $herd, $arr_task_permissions){ 
		$tmp_array = [];
		if(in_array('View All Content', $arr_task_permissions)){
			$criteria = ['parent_id' => $this->id];
			$tmp_array = $this->datasource->getByCriteria($criteria);
		}
		/* 
		 * subscription is different in that it fetches content by herd data (i.e. herd output) for users that 
		 * have permission only for subscribed content.  All other scopes are strictly users-based
		 */
		else{
			if(in_array('View Subscriptions', $arr_task_permissions)){
				$tmp_array = array_merge($tmp_array, $this->datasource->getSubscribedSections($user_id, $this->id, $herd->herdCode()));
			}
			if(in_array('View Account', $arr_task_permissions)){
				$criteria = ['ls.name' => 'View Account', 'parent_id' => $this->id];
				$tmp_array = array_merge($tmp_array, $this->datasource->getByCriteria($criteria));
			}
			if(in_array('View Admin', $arr_task_permissions)){
				$criteria = ['ls.name' => 'View Admin', 'parent_id' => $this->id];
				$tmp_array = array_merge($tmp_array, $this->datasource->getByCriteria($criteria));
			}
		}
		
		if(is_array($tmp_array) && !empty($tmp_array)){
			$this->child_sections = new \SplObjectStorage();
			foreach($tmp_array as $k => $v){
				$this->child_sections->attach(new Section($this->datasource, $v['id'], $v['parent_id'], $v['name'], $v['description'], $v['scope'], $v['path']));
			}
		}
/*		
		foreach($arr_scope as $s){
			switch ($s) {
				case 'subscription':
					$a = $this->datasource->getSubscribedSections($user_id, $this->id, $herd->herdCode(), $view_non_own);
					if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
					break;
 				not currently used
				case 'unmanaged':
					$a = $this->datasource->get_unmanaged_sections_array();
					if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
					break;

				case 'admin':
					if($this->is_admin){
						$a = $this->datasource->get_child_sections_by_scope($s, $super_id);
						if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
					}
					break;

				default: //public, account, user-specific
					$criteria = ['ls.name' => $s, 'parent_id' => $this->id];
					$a = $this->datasource->getByCriteria($criteria);
					if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
					break;
			}
		}
*/
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


