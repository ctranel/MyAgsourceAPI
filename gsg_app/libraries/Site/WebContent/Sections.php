<?php

namespace myagsource\Site\WebContent;

require_once(APPPATH . 'libraries/Site/WebContent/Section.php');
require_once(APPPATH . 'libraries/Site/WebContent/Pages.php');

use \myagsource\Site\WebContent\Section;
use \myagsource\Site\WebContent\Pages;
use \myagsource\Site\iWebContentRepository;
use \myagsource\Site\iWebContent;
use \myagsource\dhi\Herd;

/**
 * A repository? for section objects
 * 
 * 
 * @name Sections
 * @author ctranel
 * 
 *        
 */
class Sections implements iWebContentRepository {
	/**
	 * $datasource_sections
	 * @var Section_model
	 **/
	protected $datasource_sections;

	/**
	 * $pages
	 * @var Pages
	 **/
	protected $Pages;

	function __construct(\Section_model $datasource_sections, Pages $pages) {
		$this->datasource_sections = $datasource_sections;
		$this->pages = $pages;
	}
	
	/*
	 * @returns Section
	 */
	public function getByPath($path){
		$criteria = ['path' => $path];
		$results = $this->datasource_sections->getByCriteria($criteria);
		if(empty($results)){
			return false;
		}
		return new Section($results[0]['id'], $results[0]['parent_id'], $results[0]['name'], $results[0]['description'], $results[0]['scope'], $results[0]['active'], $results[0]['path']);
	}

	/**
	 * @method loadChildren()
	 * @param iWebContent $section
	 * @param iWebContentRepository $pages
	 * @param int user id
	 * @param Herd herd
	 * @param array task permissions
	 * @return void
	 * @access public
	 **/
	//if we allow producers to select which sections to allow, we will need to pass that array to this section as well
	public function loadChildren(iWebContent $section, iWebContentRepository $pages, $user_id, Herd $herd, $arr_task_permissions){ 
		//if children have already been loaded
		$tmp = $section->children();
		if(isset($tmp)){
			return;
		}
		$children = new \SplObjectStorage();
		$tmp_array = [];

		//Get Subsections
		if(in_array('View All Content', $arr_task_permissions)){
			$criteria = ['parent_id' => $section->id()];
			$tmp_array = $this->datasource_sections->getByCriteria($criteria);
		}
		/* 
		 * subscription is different in that it fetches content by herd data (i.e. herd output) for users that 
		 * have permission only for subscribed content.  All other scopes are strictly users-based
		 */
		else{
			if(in_array('View Subscriptions', $arr_task_permissions)){
				$tmp_array = array_merge($tmp_array, $this->datasource_sections->getSubscribedSections($user_id, $parent_id, $herd->herdCode()));
			}
			if(in_array('View Account', $arr_task_permissions)){
				$criteria = ['ls.name' => 'View Account', 'parent_id' => $parent_id];
				$tmp_array = array_merge($tmp_array, $this->datasource_sections->getByCriteria($criteria));
			}
			if(in_array('View Admin', $arr_task_permissions)){
				$criteria = ['ls.name' => 'View Admin', 'parent_id' => $parent_id];
				$tmp_array = array_merge($tmp_array, $this->datasource_sections->getByCriteria($criteria));
			}
		}
		
		if(is_array($tmp_array) && !empty($tmp_array)){
			foreach($tmp_array as $k => $v){
				$children->attach(new Section($v['id'], $v['parent_id'], $v['name'], $v['description'], $v['scope'], $v['active'], $v['path']));
			}
		}
		
		//Get child pages
		
		$section->loadChildren($children);
	}
	/*
	 * @returns SplObjectStorage
	public function getByParent($parent_id){
		$criteria = ['parent_id' => $parent_id];
		$results = $this->datasource->getByCriteria($criteria);
		
		$ret = new \SplObjectStorage();
		foreach($results as $r){
			$ret->attach(new Section($r['id'], $r['parent_id'], $r['name'], $r['description'], $r['scope'], $r['active'], $r['path']));
		}
		return $ret;
	}
	 */
}

?>