<?php

namespace myagsource\Report\Content\Table;

require_once APPPATH . 'libraries/Datasource/DbObjects/DbTable.php';
//require_once APPPATH . 'libraries/Datasource/DbObjects/DbField.php';
//require_once APPPATH . 'libraries/Report/Content/Table/TableField.php';
//require_once APPPATH . 'libraries/Report/Content/Block.php';
require_once APPPATH . 'libraries/Report/Content/BlockData.php';

use \myagsource\Report\Content\BlockData;
use \myagsource\Datasource\DbObjects\DbTable;
//use \myagsource\Datasource\DbObjects\DbField;
//use \myagsource\Report\Content\Table\TableField;
//use \myagsource\Report\Content\Block;
use \myagsource\Benchmarks\Benchmarks;

/**
 * Name:  TableData
 *
 * Author: ctranel
 *
 * Created:  04-29-2015
 *
 * Description: Data handler for table blocks
 *
 */
class TableData extends BlockData {
	/**
	protected $block;
	protected $report_datasource;
	protected $dataset;
	**/	

	/**
	 * benchmarks
	 *
	 * benchmarks
	 * @var Benchmarks
	 **/
	protected $benchmarks;
	
	/**
	 * db_table
	 *
	 * db_table
	 * @var DbTable
	 **/
	protected $db_table;
	
	/**
	 * @todo: add filter data
	 */
	function __construct(TableBlock $block, \Report_data_model $report_datasource, Benchmarks $benchmarks, DbTable $db_table) {
		parent::__construct($block, $report_datasource);
		$this->benchmarks = $benchmarks;
		$this->db_table = $db_table;
	}
	
	/**
	 * @method getData()
	 * @param array - key-value pairs for criteria
	 * @return array of data for the chart
	 * @access public
	 * 
	 *@todo: criteria param should be removed when filter data is included in object
	 **/
		public function getData($criteria_key_value){
		//$report_datasource->populate_field_meta_arrays($arr_this_block['id']);
		$arr_field_list = $this->block->getFieldlistArray();
		$criteria_key_value = $this->whereCriteria($criteria_key_value);
		$select_fields = $this->block->getSelectFields();
		$results = $this->report_datasource->search($this->block, $select_fields, $criteria_key_value);
		if($this->block->hasBenchmark() && isset($this->benchmarks)){
		//if the data is pivoted, set the pivoted field as the row header, else use the first non-pstring column
			$row_head_field = $this->getRowHeadField($arr_field_list);
			$arr_bench_data = $this->benchmarks->addBenchmarkRow(
				$this->db_table,
				$row_head_field,
				$arr_field_list,
				$this->block->getGroupBy()
			);
			if(count($arr_bench_data) > 1){
			/*
			 * @todo: if block_group_by isset (i.e., there are multiple rows of benchmarks), need to iterate through result set and place benchmark rows in correct spots.
			 * 	(i.e., when the value of the group_by field changes, insert the benchmark row that matches the previous value in the group by field)
			 */
			}
			else{
				$results[] = $arr_bench_data[0];
			}
		}
		if($this->block->hasPivot()){
			$results = $this->pivot($results, $this->block->pivotFieldName(), 10, 10, $this->block->hasAvgRow(), $this->block->hasSumRow());
		}
		return $results;
	}

	/* 
	 * getRowHeadField
	 * 
	 * if the data is pivoted, set the pivoted field as the row header, else use the first non-pstring column
	 *  
	 * @method getRowHeadField()
	 * @param array field list
	 * @return string field name
	 * @author ctranel
	 */
	protected function getRowHeadField($arr_field_list){
		if(!empty($this->pivot_db_field)){
			return $this->pivot_db_field;
		}
		else{
			foreach($arr_field_list as $fl){
				//@todo: remove reference to specific field name
				if($fl != 'pstring'){
					return $fl;
				}
			}
		}
		return;
	}
}
?>