<?php

namespace myagsource\Report\Content;

require_once(APPPATH . 'libraries/Report/Content/TableBlock.php');
require_once(APPPATH . 'libraries/Report/Content/ChartBlock.php');
//require_once(APPPATH . 'libraries/Report/iReportContentRepository.php');
require_once(APPPATH . 'libraries/Report/iBlock.php');
require_once(APPPATH . 'libraries/dhi/herd.php');

use \myagsource\Report\Content\TableBlock;
use \myagsource\Report\Content\ChartBlock;
use \myagsource\Report\iBlock;
use \myagsource\dhi\Herd;

/**
 * A repository? for report block objects
 * 
 * 
 * @name Pages
 * @author ctranel
 * 
 *        
 */
class Blocks {// implements iReportContentRepository {
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
	public function getByPath($path){
		$block = null;
		$criteria = ['path' => $path];
		$results = $this->datasource_blocks->getByCriteria($criteria);
		if(empty($results)){
			return false;
		}
		if($results[0]['display_type'] === 'table'){
			$block = new TableBlock($results[0]['id'], $results[0]['page_id'], $results[0]['name'], $results[0]['description'], $results[0]['scope'], $results[0]['active'], $results[0]['path'], $results[0]['max_rows'], $results[0]['cnt_row'], 
			$results[0]['sum_row'], $results[0]['avg_row'], $results[0]['bench_row'], $results[0]['is_summary'], $results[0]['display_type']);
		}
		else{
			$block = new ChartBlock($results[0]['id'], $results[0]['page_id'], $results[0]['name'], $results[0]['description'], $results[0]['scope'], $results[0]['active'], $results[0]['path'], $results[0]['max_rows'], $results[0]['cnt_row'], 
			$results[0]['sum_row'], $results[0]['avg_row'], $results[0]['bench_row'], $results[0]['is_summary'], $results[0]['display_type']);
		}
		return $block;
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
			if($r['display_type'] === 'table'){
				$blocks->attach(new TableBlock($r['id'], $r['page_id'], $r['name'], $r['description'], $r['scope'], $r['active'], $r['path']));
			}
			else{
				$blocks->attach(new ChartBlock($r['id'], $r['page_id'], $r['name'], $r['description'], $r['scope'], $r['active'], $r['path']));
			}
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