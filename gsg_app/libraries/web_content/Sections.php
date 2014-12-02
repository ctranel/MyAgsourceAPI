<?php

namespace myagsource\web_content;

require_once(APPPATH . 'libraries' . FS_SEP . 'web_content' . FS_SEP . 'Section.php');

use \myagsource\web_content\Section;
/**
 * A collections of section objects
 * 
 * 
 * @name Sections
 * @author ctranel
 * 
 *        
 */
class Sections extends \SplObjectStorage { //implements WebContentRepository
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
	
	public function getByPath($path){
		$criteria = ['path' => $path];
		$params = $this->datasource->getByCriteria($criteria);
		return new Section($this->datasource, $params[0]['id'], $params[0]['parent_id']);
	}
}

?>