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
	 * section_datasource
	 * @var object
	 **/
	protected $section_datasource;

	/**
	 * section parent_section_id
	 * @var boolean
	 **/
	protected $parent_section_id;

	/**
	 * section id
	 * @var int
	 **/
	protected $section_id;

	/**
	 * section name
	 * @var string
	 **/
	protected $section_name;
	
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
	public function __construct($section_datasource, $section_id, $parent_section_id) {
		$this->section_datasource = $section_datasource;
		$this->parent_section_id = $parent_section_id;
		$this->section_id = $section_id;
	}

	public function childKeyValuePairs(){
		
	}
	
	/**
	 * @method setChildren()
	 * @param int user id
	 * @param string herd code
	 * @param int parent section id
	 * @param array section scopes to include
	 * @param boolean has permission to view non-owned herds
	 * @param boolean has permission to view non-owned herds only with owners explicit permission
	 * @return void
	 * @access public
	 *
	 **/
	public function setChildren($user_id, $herd_code, $parent_section_id, array $arr_scope, $view_non_own, $view_non_own_w_permission ){//Array $array){ //array directly from data source
		$tmp_array = array();
		foreach($arr_scope as $s){
			switch ($s) {
				case 'subscription':
					$a = $this->web_content_model->get_subscribed_sections_array($user_id, $parent_section_id, $herd_code,$view_non_own);
					if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
					break;
				case 'unmanaged':
					$a = $this->web_content_model->get_unmanaged_sections_array();
					if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
					break;
//				case 'admin':
//					if($this->is_admin){
//						$a = $this->web_content_model->get_child_sections_by_scope($s, $super_section_id);
//						if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
//					}
//					break;
				default: //public, account, user-specific
					$a = $this->web_content_model->get_child_sections_by_scope($s, $arr_super_section_id);
					if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
					break;
			}
		}

		if(!$view_non_own && $view_non_own_w_permission && !$this->ion_auth_model->user_owns_herd($herd_code) && !empty($herd_code)){
			if(is_array($tmp_array) && !empty($tmp_array)){
				$this->child_sections = new \SplObjectStorage();
				foreach($tmp_array as $k => $v){
					if($this->ion_auth_model->consultant_has_access($user_id, $herd_code, $v['id'])){
						
						$this->child_sections = $v;
					}
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
	public function subscribed_section($section_id){
		return TRUE;
		$tmp_array = $this->arr_user_sections;
		if(isset($tmp_array) && is_array($tmp_array)){
			$this->load->helper('multid_array_helper');
			$tmp_arr_sections = array_extract_value_recursive('id', $tmp_array);
			return in_array($section_id, $tmp_arr_sections);
		}
		else return false;
	}
	 **/
	
	
}


