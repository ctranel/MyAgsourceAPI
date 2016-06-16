<?php
namespace myagsource\Site\WebContent;

require_once APPPATH . 'libraries/Site/iPage.php';
require_once APPPATH . 'libraries/Site/iWebContent.php';
require_once APPPATH . 'libraries/Site/iWebContentRepository.php';

use myagsource\Benchmarks\Benchmarks;
use myagsource\report_filters\Filters;
use myagsource\Site\iPage;
use myagsource\Site\iWebContent;
use myagsource\Site\iWebContentRepository;
use myagsource\dhi\Herd;
use myagsource\Supplemental\Content\SupplementalFactory;

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
     * page route
     * @var string
     **/
    protected $route;

    /**
     * Source for page data
     * @var page_model
     **/
    protected $datasource;

    /**
	 * Factory for supplemental objects
	 * @var SupplementalFactory
	 **/
	protected $supplemental_factory;

	/**
	 * report blocks
	 * @var Blocks
	 **/
	protected $blocks;

	/**
	 * forms
	 * @var Forms
	 **/
	protected $forms;

	/**
	 * report filters
	 * @var Filters
	 **/
	protected $filters;

    /**
     * report benchmarks
     * @var Benchmarks
     **/
    protected $benchmarks;

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
	 * 
	 * @todo: may need to add herd header info too
	 **/
	public function __construct($id, $datasource, SupplementalFactory $supplemental_factory, Blocks $blocks, Forms $forms, Filters $filters, Benchmarks $benchmarks) {
		$this->datasource = $datasource;
        $page_data = $this->datasource->getPage($id);

        $this->section_id = $section_id;
		$this->name = $name;
		$this->description = $description;
		$this->scope = $scope;
		$this->active = $active;
		$this->path = $path;


		$this->id = $id;
		$this->supplemental_factory = $supplemental_factory;
		$this->blocks = $blocks;
		$this->forms = $forms;
		$this->filters = $filters;
        $this->benchmarks = $benchmarks;
	}
	
	public function toJson(){
		json_encode(
			
		);
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


