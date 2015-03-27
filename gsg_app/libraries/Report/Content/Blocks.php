<?php

namespace myagsource\Report\Content;

require_once(APPPATH . 'libraries/Report/Content/Table/TableBlock.php');
require_once(APPPATH . 'libraries/Report/Content/Chart/ChartBlock.php');
//require_once(APPPATH . 'libraries/Report/iReportContentRepository.php');
require_once(APPPATH . 'libraries/Report/iBlock.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');
require_once(APPPATH . 'libraries/Datasource/DbObjects/DbTable.php');
require_once(APPPATH . 'libraries/Datasource/DbObjects/DbField.php');

use \myagsource\Report\Content\Table\TableBlock;
use \myagsource\Report\Content\Chart\ChartBlock;
use \myagsource\Report\iBlock;
use \myagsource\dhi\Herd;
use myagsource\Supplemental\Content\SupplementalFactory;
use myagsource\Datasource\DbObjects\DbField;

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
	 * @var report_block_model
	 **/
	protected $datasource_blocks;

	/**
	 * datasource_dbfield
	 * @var db_field_model
	 **/
	protected $datasource_dbfield;

	/**
	 * supplemental_factory
	 * @var SupplementalFactory
	 **/
	protected $supplemental_factory;
	
	function __construct(\report_block_model $datasource_blocks, \db_field_model $datasource_dbfield, \myagsource\Supplemental\Content\SupplementalFactory $supplemental_factory = null) {
		$this->datasource_blocks = $datasource_blocks;
		$this->datasource_dbfield = $datasource_dbfield;
		$this->supplemental_factory = $supplemental_factory;
	}
	
	/*
	 * getByPath
	 * 
	 * @param string path
	 * @author ctranel
	 * @returns Page
	 */
	public function getByPath($path, $parent_id = null){
		$block = null;
		$criteria = ['path' => $path];
		if(isset($parent_id)){
			$criteria['page_id'] = $parent_id;
		}
		$results = $this->datasource_blocks->getByCriteria($criteria);
		if(empty($results)){
			return false;
		}

		//$sort = 
		$r = $results[0];
		if($r['display_type'] === 'table'){
			$block = new TableBlock($this->datasource_blocks, $r['id'], $r['page_id'], $r['name'], $r['description'], $r['scope'], $r['active'], $r['path'], $r['max_rows'], $r['cnt_row'], 
				$r['sum_row'], $r['avg_row'], $r['bench_row'], $r['is_summary'], $r['display_type'], $this->supplemental_factory);
			if(isset($r['pivot_db_field'])){
				$p = $this->datasource_dbfield->getFieldData($r['pivot_db_field']);
//var_dump($p);
				$pivot_field = new DbField($p['id'], $p['db_table'], $p['db_field_name'], $p['name'], $p['description'], $p['pdf_width'], $p['default_sort_order'], $p['datatype'], $p['max_length'], $p['decimal_scale'], $p['unit_of_measure'], $p['is_timespan'], $p['is_foreign_key'], $p['is_nullable'], $p['is_natural_sort']);
				$block->setPivot($pivot_field);
			}
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
				if(isset($r['pivot_db_field'])){
					$p = $this->datasource_dbfield->getFieldData($r['pivot_db_field']);
					$pivot_field = new DbField($p['id'], $p['db_table'], $p['db_field_name'], $p['name'], $p['description'], $p['pdf_width'], $p['default_sort_order'], $p['datatype'], $p['max_length'], $p['decimal_scale'], $p['unit_of_measure'], $p['is_timespan'], $p['is_foreign_key'], $p['is_nullable'], $p['is_natural_sort']);
					$block->setPivot($pivot_field);
				}
			}
			else{
				$blocks->attach(new ChartBlock($this->datasource_blocks, $r['id'], $r['page_id'], $r['name'], $r['description'], $r['scope'], $r['active'], $r['path'], $r['max_rows'], $r['cnt_row'], $r['sum_row'], $r['avg_row'], $r['bench_row'], $r['is_summary'], $r['display_type'], $this->supplemental_factory));
			}
		}
		return $blocks;
	}
}

?>