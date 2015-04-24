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
		$pages = new \SplObjectStorage();
		$criteria = ['section_id' => $section_id];
		$results = $this->datasource_pages->getByCriteria($criteria);
		
		if(empty($results) || !is_array($results)){
			return false;
		}
		foreach($results as $k => $v){
			$pages->attach(new Page($v['id'], $v['section_id'], $v['name'], $v['description'], $v['scope_id'], $v['active'], $v['path']));
		}
		return $pages;
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
		
		//Get child pages
		
		
		
	}
}
?>