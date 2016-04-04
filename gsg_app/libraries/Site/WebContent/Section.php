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
}


