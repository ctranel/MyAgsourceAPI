<?php
namespace myagsource\Site\WebContent;

require_once APPPATH . 'libraries/Site/iWebContent.php';
require_once APPPATH . 'libraries/Site/iWebContentRepository.php';

use myagsource\Site\iWebContent;
use myagsource\Site\iWebContentRepository;
use myagsource\dhi\Herd;
/**
* Name:  Page
*
* Author: ctranel
*  
* Created:  11-24-2014
*
* Description:  Contains properties and methods specific to displaying sections of the website.
*
* @todo: this library will be the basis for pages, blocks, etc, and will eventually have an abstract and/or interface to reflect the commonalities
*
*/

class Page implements iWebContent {
	/**
	 * section id
	 * @var int
	 **/
	protected $id;

	/**
	 * section section_id
	 * @var boolean
	 **/
	protected $section_id;

	/**
	 * section name
	 * @var string
	 **/
	protected $name;
	
	/**
	 * section description
	 * @var string
	 **/
	protected $description;
	
	/**
	 * scope
	 * @var string
	 **/
	protected $scope;
	
	/**
	 * scope
	 * @var string
	 **/
	protected $active;
	
	/**
	 * section path
	 * @var string
	 **/
	protected $path;
	
	/**
	 * collection of iWebContent objects
	 * @var SplObjectStorage
	 **/
	protected $children;
	
	/**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct($id, $section_id, $name, $description, $scope, $active, $path) {
		$this->section_id = $section_id;
		$this->name = $name;
		$this->description = $description;
		$this->scope = $scope;
		$this->active = $active;
		$this->path = $path;
		$this->id = $id;
	}
	
	public function id(){
		return $this->id;
	}

	public function path(){
		return $this->path;
	}

	public function name(){
		return $this->name;
	}

	public function children(){
		return $this->children;
	}
		
	/**
	 * @method loadChildren()
	 * @param \SplObjectStorage children
	 * @return void
	 * @access public
	* */
	public function loadChildren(\SplObjectStorage $children){
		$this->children = $children;
	}
	
	/**
	 * @method loadChildren()
	 * @param int user id
	 * @param Herd herd
	 * @param array task permissions
	 * @return void
	 * @access public
	//if we allow producers to select which sections to allow, we will need to pass that array to this section as well
	public function loadChildren($user_id, $herd, $arr_task_permissions){ 
		$tmp_array = [];
		if(in_array('View All Content', $arr_task_permissions)){
			$criteria = ['section_id' => $this->id];
			$tmp_array = $this->datasource->getByCriteria($criteria);
		}
		 
		//subscription is different in that it fetches content by herd data (i.e. herd output) for users that 
		//have permission only for subscribed content.  All other scopes are strictly users-based
		
		else{
			if(in_array('View Subscriptions', $arr_task_permissions)){
				$tmp_array = array_merge($tmp_array, $this->datasource->getSubscribedSections($user_id, $this->id, $herd->herdCode()));
			}
			if(in_array('View Account', $arr_task_permissions)){
				$criteria = ['ls.name' => 'View Account', 'section_id' => $this->id];
				$tmp_array = array_merge($tmp_array, $this->datasource->getByCriteria($criteria));
			}
			if(in_array('View Admin', $arr_task_permissions)){
				$criteria = ['ls.name' => 'View Admin', 'section_id' => $this->id];
				$tmp_array = array_merge($tmp_array, $this->datasource->getByCriteria($criteria));
			}
		}
		
		if(is_array($tmp_array) && !empty($tmp_array)){
			$this->children = new \SplObjectStorage();
			foreach($tmp_array as $k => $v){
				$this->children->attach(new Section($this->datasource_sections, $this->datasource_pages, $this->datasource_blocks, $v['id'], $v['section_id'], $v['name'], $v['description'], $v['scope'], $v['active'], $v['path']));
			}
		}
	}
	 **/
}


