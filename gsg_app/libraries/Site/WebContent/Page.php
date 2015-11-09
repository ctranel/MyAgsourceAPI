<?php
namespace myagsource\Site\WebContent;

require_once APPPATH . 'libraries/Site/iPage.php';
require_once APPPATH . 'libraries/Site/iWebContent.php';
require_once APPPATH . 'libraries/Site/iWebContentRepository.php';

use myagsource\Site\iPage;
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
* @todo: add filters?
*
*/

class Page implements iWebContent, iPage {
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

	public function scope(){
		return $this->scope;
	}

	public function children(){
		return $this->children;
	}
		
	public function hasBenchmark(){
		foreach($this->children as $c){
			if($c->hasBenchmark()){
				return true;
			}
		}
		return false;
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
}


