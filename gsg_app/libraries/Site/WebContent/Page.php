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
use myagsource\Form\Content\FormFactory;
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

class Page implements iPage {//iWebContent,
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
	//protected $supplemental_factory;

    /**
     * page supplemental object
     * @var Supplemental
     **/
    protected $supplemental;

    /**
	 * report blocks
	 * @var Block[]
	 **/
	protected $blocks;

	/**
	 * forms
	 * @var Form[]
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
	 * array of iWebContent objects 
     * @var iWebContent[]
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
	public function __construct($page_data, Blocks $block_factory, FormFactory $form_factory, SupplementalFactory $supplemental_factory = null, Filters $filters = null, Benchmarks $benchmarks = null) {
        $this->id = $page_data['id'];
        $this->section_id = $page_data['section_id'];
		$this->name = $page_data['name'];
		$this->description = $page_data['description'];
		$this->scope = $page_data['scope'];
		$this->active = $page_data['active'];
		$this->path = $page_data['path'];
        $this->route = $page_data['route'];

        $this->supplemental = $supplemental_factory->getPageSupplemental($this->id);

		//$this->blocks = $blocks;
		//$this->forms = $forms;
		$this->filters = $filters;
        $this->benchmarks = $benchmarks;
        
        $this->loadChildren($block_factory, $form_factory);
	}

    public function toArray(){
        $ret = [
            'section_id' => $this->section_id,
            'name' => $this->name,
            'descriptions' => $this->description,
            'scope' => $this->scope,
            'path' => $this->path,
            'route' => $this->route,
        ];

        if(!empty($this->filters->toArray())){
            $ret['filters'] = $this->filters->toArray();
        }
        if($this->hasBenchmark() && !empty($this->benchmarks->toArray())){
            $ret['benchmarks'] = $this->benchmarks->toArray();
        }
        if(!empty($this->supplemental->toArray())){
            $ret['supplemental'] = $this->supplemental->toArray();
        }
        if(isset($this->forms) && is_array($this->forms) && !empty($this->forms)){
            $forms = [];
            foreach($this->forms as $f){
                $forms[] = $f->toArray();
            }
            $ret['forms'] = $forms;
            unset($forms);
        }
        if(isset($this->blocks) && is_array($this->blocks) && !empty($this->blocks)){
            $blocks = [];
            foreach($this->blocks as $b){
                $blocks[] = $b->toArray();
            }
            $ret['blocks'] = $blocks;
            unset($blocks);
        }
        return $ret;
    }

	public function toJson(){
		return json_encode($this->toArray());
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
		if(!isset($this->blocks) || empty($this->blocks)){
            return false;
        }
        foreach($this->blocks as $b){
			if($b->hasBenchmark()){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * @method loadChildren()
	 * @param iWebContent[]
	 * @return void
	 * @access public
	* */
	public function loadChildren($block_factory, $form_factory){
		//$this->children = $children;
        //populate forms and report blocks
        $this->forms = $form_factory->getByPage($this->id);
        $this->blocks = $block_factory->getByPage($this->id);
	}
}


