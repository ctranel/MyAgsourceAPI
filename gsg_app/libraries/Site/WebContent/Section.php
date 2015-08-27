<?php
namespace myagsource\Site\WebContent;

require_once APPPATH . 'libraries/Site/iWebContent.php';

use myagsource\Site\iWebContent;
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
* @todo: this library will be the basis for pages, blocks, etc, and will eventually have an abstract and/or interface to reflect the commonalities
*
*/

class Section implements iWebContent
{
	/**
	 * datasource
	 * @var object
	 **/
	protected $datasource_sections;

	/**
	 * datasource
	 * @var object
	 **/
	protected $datasource_pages;

	/**
	 * datasource
	 * @var object
	 **/
	protected $datasource_blocks;

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
	 * active
	 * @var string
	 **/
	protected $active;
	
	/**
	 * section path
	 * @var string
	 **/
	protected $path;
	
	/**
	 * section default_page_path
	 * @var string
	 **/
	protected $default_page_path;
	
	/**
	 * collection of iWebContent objects
	 * @var SplObjectStorage
	 **/
	protected $children;
	
	/**
	 * collection of iWebContent objects that are the leafs of the navigation tree
	 * @var SplObjectStorage
	 **/
	protected $pages;
	
	/**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct($id, $parent_id, $name, $description, $scope, $active, $path, $default_page_path) {
		$this->parent_id = $parent_id;
		$this->name = $name;
		$this->description = $description;
		$this->scope = $scope;
		$this->active = $active;
		$this->path = $path;
		$this->default_page_path = $default_page_path;
		$this->id = $id;
	}
	
	public function id(){
		return $this->id;
	}

	public function path(){
		return $this->path;
	}

	public function defaultPagePath(){
		return $this->default_page_path;
	}

	public function name(){
		return $this->name;
	}

	public function children(){
		return $this->children;
	}
	
	public function pages(){
		return $this->pages;
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
	 * @method loadPages()
	 * @param \SplObjectStorage pages
	 * @return void
	 * @access public
	 * */
	public function loadPages(\SplObjectStorage $pages){
		$this->pages = $pages;
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
		if(isset($this->children)){
			return;
		}
		$tmp_array = [];
		if(in_array('View All Content', $arr_task_permissions)){
			$criteria = ['parent_id' => $this->id];
			$tmp_array = $this->datasource_sections->getByCriteria($criteria);
		}
		//subscription is different in that it fetches content by herd data (i.e. herd output) for users that 
		//have permission only for subscribed content.  All other scopes are strictly users-based
		else{
			if(in_array('View Subscriptions', $arr_task_permissions)){
				$tmp_array = array_merge($tmp_array, $this->datasource_sections->getSubscribedSections($this->id, $herd->herdCode()));
			}
			if(in_array('View Account', $arr_task_permissions)){
				$criteria = ['ls.name' => 'View Account', 'parent_id' => $this->id];
				$tmp_array = array_merge($tmp_array, $this->datasource_sections->getByCriteria($criteria));
			}
			if(in_array('View Admin', $arr_task_permissions)){
				$criteria = ['ls.name' => 'View Admin', 'parent_id' => $this->id];
				$tmp_array = array_merge($tmp_array, $this->datasource_sections->getByCriteria($criteria));
			}
		}
		
		if(is_array($tmp_array) && !empty($tmp_array)){
			$this->children = new \SplObjectStorage();
			foreach($tmp_array as $k => $v){
				$this->children->attach(new Section($this->datasource_sections, $this->datasource_pages, $this->datasource_blocks, $v['id'], $v['parent_id'], $v['name'], $v['description'], $v['scope'], $v['active'], $v['path']));
			}
		}
	}
	 **/
}


