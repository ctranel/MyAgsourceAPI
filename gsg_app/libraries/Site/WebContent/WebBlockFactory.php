<?php

namespace myagsource\Site\WebContent;

require_once(APPPATH . 'libraries/Site/WebContent/Block.php');
require_once(APPPATH . 'libraries/Site/iWebContentRepository.php');
require_once(APPPATH . 'libraries/Site/iWebContent.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');

use myagsource\Page\Content\ReportBlockFactory;
use \myagsource\Site\WebContent\WebBlockFactory;
use \myagsource\Site\iWebContentRepository;
use \myagsource\Site\iWebContent;
use \myagsource\dhi\Herd;

/**
 * A repository? for page objects
 * 
 * 
 * @name WebBlockFactory
 * @author ctranel
 * 
 *        
 */
class WebBlockFactory implements iWebContentRepository {
	/**
	 * datasource_blocks
	 * @var block_model
	 **/
	protected $datasource_blocks;

	/**
	 * web_block_factory
	 * @var WebBlockFactory
	 **/
	protected $web_block_factory;

	/**
	 * form_block_factory
	 * @var FormBlockFactory
	 **/
	protected $form_block_factory;

	function __construct(\Block_model $datasource_blocks) {//, WebBlockFactory $web_block_factory, FormBlockFactory $form_block_factory
		$this->datasource_blocks = $datasource_blocks;
	}
	
	/*
	 * getByPath
	 * 
	 * @param string path
	 * @author ctranel
	 * @returns Block
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
		return new Block($results[0]['id'], $results[0]['page_id'], $results[0]['name'], $results[0]['description'], $results[0]['display_type'], $results[0]['scope'], $results[0]['active'], $results[0]['path']);//, $results[0]['bench_row']
	}

	/*
	 * getByPage
	 * 
	 * @param int page_id
	 * @author ctranel
	 * @returns Block[]
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
			$blocks[] = new Block($r['id'], $r['page_id'], $r['name'], $r['description'], $r['display_type'], $r['scope'], $r['active'], $r['path']);//, $r['bench_row']
		}
		return $blocks;
	}

    /*
     * blockFromData
     *
     * @param associative array of data needed for block creation
     * @author ctranel
     * @returns Block
     */
    public function blockFromData($data){
        return new Block($data['id'], $data['page_id'], $data['name'], $data['description'], $data['display_type'], $data['scope'], $data['active'], $data['path']);//, $data['bench_row']
    }
}

?>