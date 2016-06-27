<?php

namespace myagsource\Site\WebContent;

require_once(APPPATH . 'libraries/Site/WebContent/Block.php');
require_once(APPPATH . 'libraries/Site/iWebContentRepository.php');
require_once(APPPATH . 'libraries/Site/iWebContent.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');

use \myagsource\Site\WebContent\Block;
use \myagsource\Site\iWebContentRepository;
use \myagsource\Site\iWebContent;
use \myagsource\dhi\Herd;

/**
 * A repository? for page objects
 * 
 * 
 * @name Pages
 * @author ctranel
 * 
 *        
 */
class Blocks implements iWebContentRepository {
	/**
	 * datasource_blocks
	 * @var block_model
	 **/
	protected $datasource_blocks;

	function __construct(\Block_model $datasource_blocks) {
		$this->datasource_blocks = $datasource_blocks;
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
			$criteria['page_id'] = $parent_id;
		}
		$results = $this->datasource_blocks->getByCriteria($criteria);
		if(empty($results)){
			return false;
		}
		return new Block($results[0]['id'], $results[0]['page_id'], $results[0]['name'], $results[0]['description'], $results[0]['display_type'], $results[0]['scope'], $results[0]['active'], $results[0]['path'], $results[0]['bench_row']);
	}

	/*
	 * getBySection
	 * 
	 * @param int page_id
	 * @author ctranel
	 * @returns Blocks[]
	 */
	public function getByPage($page_id){
		$blocks = [];
		$criteria = ['page_id' => $page_id];
//		$join = [['table' => 'pages_blocks pb', 'condition' => 'b.id = pb.block_id AND pb.page_id = ' . $page_id]];
		$results = $this->datasource_blocks->getByCriteria($criteria);
		if(empty($results)){
			return false;
		}
		foreach($results as $r){
			$blocks[] = new Block($r['id'], $r['page_id'], $r['name'], $r['description'], $r['display_type'], $r['scope'], $r['active'], $r['path'], $r['bench_row']);
		}
		return $blocks;
	}
}

?>