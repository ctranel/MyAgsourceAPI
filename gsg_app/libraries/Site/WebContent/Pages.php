<?php
namespace myagsource\Site\WebContent;

require_once(APPPATH . 'libraries/Site/WebContent/Page.php');
require_once(APPPATH . 'libraries/Site/iWebContentRepository.php');
require_once(APPPATH . 'libraries/Site/iWebContent.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');

use \myagsource\Form\Content\FormFactory;
use \myagsource\Site\iWebContentRepository;
use \myagsource\Site\iWebContent;
use \myagsource\Site\WebContent\Page;
use \myagsource\dhi\Herd;

/**
 * A factory for page objects
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
	protected $block_factory;

	/**
	 * $blocks
	 * @var FormFactory
	 **/
	protected $form_factory;

	function __construct(\Page_model $datasource_pages, Blocks $block_factory, FormFactory $form_factory) {
		$this->datasource_pages = $datasource_pages;
		$this->block_factory = $block_factory;
		$this->form_factory = $form_factory;
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
		return new Page($results[0], $this->block_factory, $this->form_factory);
	}

	/*
	 * getBySection
	 * 
	 * @param int section_id
	 * @author ctranel
	 * @returns Page
	 */
	public function getBySection($section_id){
		$pages = [];
		$criteria = ['section_id' => $section_id];
		$results = $this->datasource_pages->getByCriteria($criteria);
		
		if(empty($results) || !is_array($results)){
			return false;
		}
		foreach($results as $k => $v){
//var_dump($this->block_factory);
			$pages[] = new Page($v, $this->block_factory, $this->form_factory);
		}
		return $pages;
	}
}
?>