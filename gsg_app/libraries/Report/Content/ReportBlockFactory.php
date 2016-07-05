<?php

namespace myagsource\Report\Content;

require_once(APPPATH . 'libraries/Report/Content/Table/TableBlock.php');
require_once(APPPATH . 'libraries/Report/Content/Chart/ChartBlock.php');
require_once(APPPATH . 'libraries/Report/iBlock.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');
require_once(APPPATH . 'libraries/Datasource/DbObjects/DbTable.php');
require_once(APPPATH . 'libraries/Datasource/DbObjects/DbField.php');

use \myagsource\Report\Content\Table\TableBlock;
use \myagsource\Report\Content\Chart\ChartBlock;
use \myagsource\Filters\ReportFilters;
use \myagsource\Report\iBlock;
use \myagsource\dhi\Herd;
use myagsource\Supplemental\Content\SupplementalFactory;
use myagsource\Datasource\DbObjects\DbField;
use \myagsource\Datasource\DbObjects\DbTableFactory;
use \myagsource\DataHandler;
use \myagsource\Site\WebContent\WebBlockFactory;

/**
 * A repository? for report block objects
 * 
 * 
 * @name ReportBlockFactory
 * @author ctranel
 * 
 *        
 */
class ReportBlockFactory {// implements iReportContentRepository {
	/**
	 * datasource_blocks
	 * @var report_block_model
	 **/
	protected $datasource_blocks;

    /**
     * filters
     * @var ReportFilters
     **/
    protected $filters;

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

    /**
     * web_block_factory
     * @var WebBlockFactory
     **/
    protected $web_block_factory;

    /**
     * data_handler
     * @var DataHandler
     **/
    protected $data_handler;

    /**
     * db_table_factory
     * @var DbTableFactory
     **/
    protected $db_table_factory;

    function __construct(
		\report_block_model $datasource_blocks, 
        \db_field_model $datasource_dbfield, 
        ReportFilters $filters, 
        SupplementalFactory $supplemental_factory = null, 
        WebBlockFactory $web_block_factory,
        DataHandler $data_handler,
        DbTableFactory $db_table_factory
    ) {
		$this->datasource_blocks = $datasource_blocks;
		$this->datasource_dbfield = $datasource_dbfield;
		$this->supplemental_factory = $supplemental_factory;
        $this->web_block_factory = $web_block_factory;
        $this->filters = $filters;
        $this->data_handler = $data_handler;
        $this->db_table_factory = $db_table_factory;
	}

	/*
	 * getBlock
	 * 
	 * @param int block id
	 * @author ctranel
	 * @returns \myagsource\Report\iBlock
	 */
	public function getBlock($id){
		$criteria = ['id' => $id];
		$results = $this->datasource_blocks->getByCriteria($criteria);
		if(empty($results)){
			return false;
		}

		$r = $results[0];
		return $this->dataToObject($r);
	}

	/*
	 * getByPath
	 * 
	 * @param string path
	 * @author ctranel
	 * @returns \myagsource\Report\iBlock
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

		//$sort = 
		$r = $results[0];
		return $this->dataToObject($r);
	}

	/*
	 * getBySection
	 * 
	 * @param int page_id
	 * @author ctranel
	 * @returns array of Blocks
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
			$blocks[] = $this->dataToObject($r);
		}
		return $blocks;
	}
	
	/*
	 * getBlock
	 * 
	 * @param array result set row
	 * @author ctranel
	 * @returns \myagsource\Report\iBlock
	 */
	protected function dataToObject($report){
		$field_groups = $this->datasource_blocks->getFieldGroupData($report['id']);
		$field_groups = $this->keyFieldGroupData($field_groups);
        
        $web_block = $this->web_block_factory->blockFromData($report);
		
		if($report['display_type'] === 'table'){
			$block = new TableBlock($this->datasource_blocks, $web_block, $report['max_rows'], $report['cnt_row'], $report['sum_row'], $report['avg_row'], $report['bench_row'], $report['is_summary'], $report['display_type'], $this->filters, $this->supplemental_factory, $this->data_handler, $this->db_table_factory, $field_groups);
			if(isset($report['pivot_db_field'])){
				$p = $this->datasource_dbfield->getFieldData($report['pivot_db_field']);
				$pivot_field = new DbField($p['id'], $p['db_table'], $p['db_field_name'], $p['name'], $p['description'], $p['pdf_width'], $p['default_sort_order'], $p['datatype'], $p['max_length'], $p['decimal_scale'], $p['unit_of_measure'], $p['is_timespan'], $p['is_foreign_key'], $p['is_nullable'], $p['is_natural_sort']);
				$block->setPivot($pivot_field);
			}
		}
		else{
			$block = new ChartBlock($this->datasource_blocks, $web_block, $report['max_rows'], $report['cnt_row'], $report['sum_row'], $report['avg_row'], $report['bench_row'], $report['is_summary'], $report['display_type'], $report['chart_type'], $this->filters, $this->supplemental_factory, $this->data_handler, $this->db_table_factory, $field_groups, (bool)$report['keep_nulls']);
		}
		return $block;
	}
	
	
	//@TODO: WHERE SHOULD THIS GO??
	protected function keyFieldGroupData($field_groups){
		if(!isset($field_groups) || empty($field_groups)){
			return false;
		}
		
		$ret = [];
		foreach($field_groups as $fg){
			$fg_num = $fg['field_group_num'];
			unset($fg['field_group_num']);
			$ret[$fg_num] = $fg;
		}
		
		return $ret;
	}
}

?>
