<?php

namespace myagsource\Report\Content;

require_once(APPPATH . 'libraries/Report/Content/TableBlock.php');
require_once(APPPATH . 'libraries/Report/Content/ChartBlock.php');
//require_once(APPPATH . 'libraries/Report/iReportContentRepository.php');
require_once(APPPATH . 'libraries/Report/iBlock.php');
require_once(APPPATH . 'libraries/dhi/herd.php');

use \myagsource\Report\Content\TableBlock;
use \myagsource\Report\Content\ChartBlock;
use \myagsource\Report\iBlock;
use \myagsource\dhi\Herd;
use myagsource\Supplemental\Content\SupplementalFactory;

/**
 * A repository? for report block objects
 * 
 * 
 * @name Pages
 * @author ctranel
 * 
 *        
 */
class Blocks {// implements iReportContentRepository {
	/**
	 * datasource_blocks
	 * @var block_model
	 **/
	protected $datasource_blocks;

	/**
	 * supplemental_factory
	 * @var SupplementalFactory
	 **/
	protected $supplemental_factory;
	
	function __construct(\report_block_model $datasource_blocks, \myagsource\Supplemental\Content\SupplementalFactory $supplemental_factory = null) {
		$this->datasource_blocks = $datasource_blocks;
		$this->supplemental_factory = $supplemental_factory;
	}
	
	/*
	 * getByPath
	 * 
	 * @param string path
	 * @author ctranel
	 * @returns Page
	 */
	public function getByPath($path){
		$block = null;
		$criteria = ['path' => $path];
		$results = $this->datasource_blocks->getByCriteria($criteria);
		if(empty($results)){
			return false;
		}

		//$sort = 
		$r = $results[0];
		if($r['display_type'] === 'table'){
			$block = new TableBlock($this->datasource_blocks, $r['id'], $r['page_id'], $r['name'], $r['description'], $r['scope'], $r['active'], $r['path'], $r['max_rows'], $r['cnt_row'], 
				$r['sum_row'], $r['avg_row'], $r['bench_row'], $r['is_summary'], $r['display_type'], $this->supplemental_factory);
		}
		else{
			$block = new ChartBlock($this->datasource_blocks, $r['id'], $r['page_id'], $r['name'], $r['description'], $r['scope'], $r['active'], $r['path'], $r['max_rows'], $r['cnt_row'], 
				$r['sum_row'], $r['avg_row'], $r['bench_row'], $r['is_summary'], $r['display_type'], $this->supplemental_factory);
		}
		return $block;
	}

	/*
	 * getBySection
	 * 
	 * @param int page_id
	 * @author ctranel
	 * @returns SplObjectStorage of Blocks
	 */
	public function getByPage($page_id){
		$blocks = new \SplObjectStorage();
		
		$criteria = ['page_id' => $page_id];
//		$join = [['table' => 'pages_blocks pb', 'condition' => 'b.id = pb.block_id AND pb.page_id = ' . $page_id]];
		$results = $this->datasource_blocks->getByCriteria($criteria);
		if(empty($results)){
			return false;
		}
		foreach($results as $r){
			if($r['display_type'] === 'table'){
				$blocks->attach(new TableBlock($this->datasource_blocks, $r['id'], $r['page_id'], $r['name'], $r['description'], $r['scope'], $r['active'], $r['path'], $r['max_rows'], $r['cnt_row'], $r['sum_row'], $r['avg_row'], $r['bench_row'], $r['is_summary'], $r['display_type'], $this->supplemental_factory));
			}
			else{
				$blocks->attach(new ChartBlock($this->datasource_blocks, $r['id'], $r['page_id'], $r['name'], $r['description'], $r['scope'], $r['active'], $r['path'], $r['max_rows'], $r['cnt_row'], $r['sum_row'], $r['avg_row'], $r['bench_row'], $r['is_summary'], $r['display_type'], $this->supplemental_factory));
			}
		}
		return $blocks;
	}
}

?>