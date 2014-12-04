<?php

namespace myagsource\web_content;

require_once(APPPATH . 'libraries' . FS_SEP . 'web_content' . FS_SEP . 'Section.php');

use \myagsource\web_content\Section;
/**
 * A repository for section objects
 * 
 * 
 * @name Sections
 * @author ctranel
 * 
 *        
 */
class Sections { //implements WebContentRepository
	/*
	 * datasource
	 * @var webContentDatasource
	 */
	protected $datasource;
	/**
	 */
	function __construct(\Section_model $datasource) {
		$this->datasource = $datasource;
	}
	
	/*
	 * @returns Section
	 */
	public function getByPath($path){
		$criteria = ['path' => $path];
		$results = $this->datasource->getByCriteria($criteria);
		if(empty($results)){
			return false;
		}
		return new Section($this->datasource, $results[0]['id'], $results[0]['parent_id'], $results[0]['name'], $results[0]['description'], $results[0]['scope'], $results[0]['path']);
	}

	/*
	 * @returns SplObjectStorage
	public function getByParent($parent_id){
		$criteria = ['parent_id' => $parent_id];
		$results = $this->datasource->getByCriteria($criteria);
		
		$ret = new \SplObjectStorage();
		foreach($results as $r){
			$ret->attach(new Section($this->datasource, $r['id'], $r['parent_id'], $r['name'], $r['description'], $r['scope'], $r['path']));
		}
		return $ret;
	}
	 */
}

?>