<?php

namespace myagsource\web_content;

require_once(APPPATH . 'libraries' . FS_SEP . 'web_content' . FS_SEP . 'Section.php');

use \myagsource\web_content\Section;
/**
 * A collections of section objects
 * 
 * 
 * @name Sections
 * @author ctranel
 * 
 *        
 */
class Sections extends \SplObjectStorage { //implements WebContentRepository
	/*
	 * datasource
	 * @var webContentDatasource
	 */
	protected $datasource;
	/**
	 */
	function __construct(\Section_model $datasource) {
		$this->datasource = $datasource;
	}
	
	/**
	 * @method nullsetChildren()
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
	public function setChildren($parent_id, \ion_auth_model $access_model, $user_id, $herd_code, $parent_section_id, array $arr_scope, $view_non_own, $view_non_own_w_permission, $user_owns_herd ){//Array $array){ //array directly from data source
		$tmp_array = array();
		foreach($arr_scope as $s){
			switch ($s) {
				case 'subscription':
					$a = $this->datasource->get_subscribed_sections_array($user_id, $parent_section_id, $herd_code,$view_non_own);
					if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
					break;
				case 'unmanaged':
					$a = $this->datasource->get_unmanaged_sections_array();
					if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
					break;
				case 'admin':
					if($this->is_admin){
						$a = $this->datasource->get_child_sections_by_scope($s, $super_section_id);
						if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
					}
					break;
				default: //public, account, user-specific
					$a = $this->datasource->get_child_sections_by_scope($s, $arr_super_section_id);
					if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
					break;
			}
		}

		if(!$view_non_own && $view_non_own_w_permission && !$user_owns_herd && !empty($herd_code)){
			if(is_array($tmp_array) && !empty($tmp_array)){
				$arr_return = array();
				foreach($tmp_array as $k => $v){
					if($access_model->consultant_has_access($user_id, $herd_code, $v['id'])){
						$arr_return[] = $v;
					}
				}
				foreach($arr_return as $s){
					$this->attach(new Section($section_datasource, $section_id, $parent_section_id));
				}
			}
		}


	}
	
	public function getByPath($path){
		$criteria = ['path' => $path];
		$params = $this->datasource->getByCriteria($criteria);
		return new Section($this->datasource, $params[0]['id'], $params[0]['parent_id']);
	}
}

?>