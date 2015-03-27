<?php

namespace myagsource\Site\WebContent;

require_once(APPPATH . 'libraries/Site/WebContent/Block.php');
require_once(APPPATH . 'libraries/Site/iWebContentRepository.php');
require_once(APPPATH . 'libraries/Site/iWebContent.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');

use \myagsource\Site\WebContent\Block;
use \myagsource\Site\iWebContentRepository;
use \myagsource\Site\iWebContent;
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
class Blocks implements iWebContentRepository {
	/**
	 * datasource_blocks
	 * @var block_model
	 **/
	protected $datasource_blocks;

	function __construct(\Block_model $datasource_blocks) {
		$this->datasource_blocks = $datasource_blocks;
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
			$criteria['page_id'] = $parent_id;
		}
		$results = $this->datasource_blocks->getByCriteria($criteria);
		if(empty($results)){
			return false;
		}
		return new Block($results[0]['id'], $results[0]['page_id'], $results[0]['name'], $results[0]['description'], $results[0]['display_type'], $results[0]['scope'], $results[0]['active'], $results[0]['path']);
	}

	/*
	 * getBySection
	 * 
	 * @param int page_id
	 * @author ctranel
	 * @returns SplObjectStorage of Blocks
	 */
	public function getByPage($page_id){
		$blocks = new \SplObjectStorage();
		$criteria = ['page_id' => $page_id];
//		$join = [['table' => 'pages_blocks pb', 'condition' => 'b.id = pb.block_id AND pb.page_id = ' . $page_id]];
		$results = $this->datasource_blocks->getByCriteria($criteria);
		if(empty($results)){
			return false;
		}
		foreach($results as $r){
			$blocks->attach(new Block($r['id'], $r['page_id'], $r['name'], $r['description'], $r['display_type'], $r['scope'], $r['active'], $r['path']));
		}
		return $blocks;
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