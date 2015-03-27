<?php
namespace myagsource\Site\WebContent;

require_once(APPPATH . 'libraries/Site/WebContent/Page.php');
require_once(APPPATH . 'libraries/Site/iWebContentRepository.php');
require_once(APPPATH . 'libraries/Site/iWebContent.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');

use \myagsource\Site\iWebContentRepository;
use \myagsource\Site\iWebContent;
use \myagsource\Site\WebContent\Page;
use \myagsource\dhi\Herd;

/**
 * A repository? for page objects
 * 
 * 
 * @name Pages
 * @author ctranel
 * 
 *        
 */
class Pages implements iWebContentRepository {
	/**
	 * datasource_pages
	 * @var page_model
	 **/
	protected $datasource_pages;

	/**
	 * $blocks
	 * @var Blocks
	 **/
	protected $Blocks;

	function __construct(\Page_model $datasource_pages, Blocks $blocks) {
		$this->datasource_pages = $datasource_pages;
	}
	
	/*
	 * getByPath
	 * 
	 * @param string path
	 * @author ctranel
	 * @returns Page
	 */
	public function getByPath($path, $parent_id = null){
		$criteria = ['path' => $path];
		if(isset($parent_id)){
			$criteria['section_id'] = $parent_id;
		}
		$results = $this->datasource_pages->getByCriteria($criteria);
		if(empty($results)){
			return false;
		}
		return new Page($results[0]['id'], $results[0]['section_id'], $results[0]['name'], $results[0]['description'], $results[0]['scope_id'], $results[0]['active'], $results[0]['path']);
	}

	/*
	 * getBySection
	 * 
	 * @param int section_id
	 * @author ctranel
	 * @returns Page
	 */
	public function getBySection($section_id){
		$criteria = ['section_id' => $section_id];
		$results = $this->datasource->getByCriteria($criteria);
		if(empty($results)){
			return false;
		}
		return new Page($results[0]['id'], $results[0]['section_id'], $results[0]['name'], $results[0]['description'], $results[0]['scope_id'], $results[0]['active'], $results[0]['path']);
	}

	/**
	 * @method loadChildren()
	 * @param iWebContent page
	 * @param iWebContentRepository blocks
	 * @param int user id
	 * @param Herd herd
	 * @param array task permissions
	 * @return void
	 * @access public
	 **/
	//if we allow producers to select which sections to allow, we will need to pass that array to this section as well
	public function loadChildren(iWebContent $page, iWebContentRepository $blocks, $user_id, Herd $herd, $arr_task_permissions){ 
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
			$children = new \SplObjectStorage();
			foreach($tmp_array as $k => $v){
				$children->attach(new Section($v['id'], $v['parent_id'], $v['name'], $v['description'], $v['scope'], $v['active'], $v['path']));
			}
		}
		
		//Get child pages
		
		
		
	}
	
	/*
	 * @returns SplObjectStorage
	public function getByParent($parent_id){
		$criteria = ['parent_id' => $parent_id];
		$results = $this->datasource->getByCriteria($criteria);
		
		$ret = new \SplObjectStorage();
		foreach($results as $r){
			$ret->attach(new Section($this->datasource_sections, $this->datasource_pages, $this->datasource_blocks, $r['id'], $r['parent_id'], $r['name'], $r['description'], $r['scope'], $r['path']));
		}
		return $ret;
	}
	 */
}

?>