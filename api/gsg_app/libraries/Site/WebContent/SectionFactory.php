<?php

namespace myagsource\Site\WebContent;

require_once(APPPATH . 'libraries/Site/WebContent/Section.php');
require_once(APPPATH . 'libraries/Site/WebContent/PageFactory.php');

use \myagsource\Site\WebContent\Section;
use \myagsource\Site\WebContent\PageFactory;
use \myagsource\Site\iWebContentRepository;
use \myagsource\Site\iWebContent;
use \myagsource\dhi\Herd;

/**
 * A repository? for section objects
 * 
 * 
 * @name SectionFactory
 * @author ctranel
 * 
 *        
 */
class SectionFactory implements iWebContentRepository {
	/**
	 * $datasource_sections
	 * @var Section_model
	 **/
	protected $datasource_sections;

	/**
	 * $page_factory
	 * @var PageFactory
	 **/
	protected $page_factory;

	function __construct(\Section_model $datasource_sections, PageFactory $page_factory) {
		$this->datasource_sections = $datasource_sections;
		$this->page_factory = $page_factory;
	}
	
	/*
	 * @returns Section
	public function getByPath($path, $parent_id = null){
		$criteria = ['path' => $path];
		if(isset($parent_id)){
			$criteria['parent_id'] = $parent_id;
		}
		$results = $this->datasource_sections->getByCriteria($criteria);
		if(empty($results)){
			return false;
		}
		return new Section($results[0]['id'], $results[0]['parent_id'], $results[0]['name'], $results[0]['description'], $results[0]['scope'], $results[0]['active'], $results[0]['path'], $results[0]['default_page_path']);
	}
*/

	/**
	 * @method loadChildren()
	 * @param iWebContent $section
	 * @param iWebContentRepository $page_factory
	 * @param int user id
	 * @param Herd herd
	 * @param array task permissions
	 * @return void
	 * @access public
	 **/
	//if we allow producers to select which sections to allow, we will need to pass that array to this section as well
	public function loadChildren(iWebContent $section, iWebContentRepository $page_factory, $user_id, Herd $herd, $permissions_list){ 
		//if children have already been loaded
		$tmp = $section->children();
		if(isset($tmp)){
			return;
		}
		$children = [];
		$tmp_array = [];

		//Get Subsections
		if(in_array('View All Content', $permissions_list)){
			$criteria = ['parent_id' => $section->id()];
			$tmp_array = $this->datasource_sections->getByCriteria($criteria);
		}
		/* 
		 * subscription is different in that it fetches content by herd data (i.e. herd output) for users that 
		 * have permission only for subscribed content.  All other scopes are strictly users-based
		 */
		else{
			if(in_array('View Subscriptions', $permissions_list)){
				$tmp_array = array_merge($tmp_array, $this->datasource_sections->getSubscribedSections($section->id(), $herd->herdCode()));
			}
			if(in_array('View Account', $permissions_list)){
				$criteria = ['ls.name' => 'View Account', 'parent_id' => $section->id()];
				$tmp_array = array_merge($tmp_array, $this->datasource_sections->getByCriteria($criteria));
			}
			if(in_array('View Admin', $permissions_list)){
				$criteria = ['ls.name' => 'View Admin', 'parent_id' => $section->id()];
				$tmp_array = array_merge($tmp_array, $this->datasource_sections->getByCriteria($criteria));
			}
			
			$tmp_array = array_merge($tmp_array, $this->datasource_sections->getPublicSections($section->id()));			

			require_once(APPPATH . 'helpers/multid_array_helper.php');
			usort($tmp_array, \sort_by_key_value_comp('list_order'));
		}
		
		if(is_array($tmp_array) && !empty($tmp_array)){
			foreach($tmp_array as $k => $v){
				$children[] = new Section($v['id'], $v['parent_id'], $v['name'], $v['description'], $v['scope'], $v['active'], $v['path'], $v['default_page_path']);
			}
		}
		
		if(count($children) > 0){
			$section->loadChildren($children);
		}
		
		//Load child pages
		$pages = $page_factory->getBySection($section->id());
		if(is_array($pages)){
			$section->loadPages($pages);
		}
	}
}

?>