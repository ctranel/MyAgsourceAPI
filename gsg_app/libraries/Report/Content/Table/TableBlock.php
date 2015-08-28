<?php

namespace myagsource\Report\Content\Table;

//require_once APPPATH . 'libraries/Datasource/DbObjects/DbTable.php';
require_once APPPATH . 'libraries/Datasource/DbObjects/DbField.php';
require_once APPPATH . 'libraries/Report/Content/Table/TableField.php';
require_once APPPATH . 'libraries/Report/Content/Block.php';
require_once(APPPATH . 'libraries/Report/Content/Table/Header/TableHeader.php');

//use \myagsource\Datasource\DbObjects\DbTable;
use \myagsource\Datasource\DbObjects\DbField;
use \myagsource\Report\Content\Table\TableField;
use \myagsource\Report\Content\Block;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\Report\Content\Table\Header\TableHeader;

/**
 * Name:  TableBlock
 *
 * Author: ctranel
 *
 * Created:  02-03-2015
 *
 * Description:  Contains properties and methods specific to displaying table blocks of the website.
 *
 */
class TableBlock extends Block {
	/**
	 * table_header
	 * @var TableHeader
	 **/
	protected $table_header;
	
	/**
	 * top_row
	 * @var array
	 **/
	protected $top_row;
	
	function __construct($block_datasource, $id, $page_id, $name, $description, $scope, $active, $path, $max_rows, $cnt_row, 
			$sum_row, $avg_row, $bench_row, $is_summary, $display_type, SupplementalFactory $supp_factory = null, $field_groups) {
		parent::__construct($block_datasource, $id, $page_id, $name, $description, $scope, $active, $path, $max_rows, $cnt_row, 
			$sum_row, $avg_row, $bench_row, $is_summary, $display_type, $supp_factory, $field_groups);
		
		$this->setReportFields();
	}
	
	/**
	 * setTableHeader
	 * 
	 * @param int report count
	 * @return array of output data for block
	 * @access public
	 *
	 **/
	public function setTableHeader(&$report_data, SupplementalFactory $supplemental_factory = null, $header_groups){
		$this->table_header = new TableHeader($this, $header_groups, $supplemental_factory);
		
		$top_row = null;
		if($this->hasPivot() && is_array($report_data) && !empty($report_data)){
			reset($report_data);
			$tmp_key = key($report_data);
			//add placeholder for column generated from header row
			$this->top_row = array_merge([''],$report_data[$tmp_key]);
			unset($report_data[$tmp_key]);
		}
	}

	/**
	 * getTableHeaderData
	 * 
	 * @param int report count
	 * @return array of output data for block
	 * @access public
	 *
	 **/
	public function getTableHeaderData($report_count){
		return [
			'structure' => $this->table_header->getTableHeaderStructure($this->top_row),
			'block' => $this,
			'report_count' => $report_count
		];
		
	}

	/**
	 * getTableHeaderCsv
	 * 
	 * @param int report count
	 * @return array of output data for block
	 * @access public
	 *
	 **/
	public function getTableHeaderLeafs(){
		$header_leafs = $this->table_header->getHeaderLeafs($this->top_row);
		return $header_leafs;
	}

	/**
	 * getOutputData
	 * 
	 * overrides parent function
	 * 
	 * @return array of output data for block
	 * @access public
	 *
	 **/
	public function getOutputData(){
		$return_val = parent::getOutputData();
		$return_val['num_columns'] = $this->table_header->columnCount();
		return $return_val;
	}

	/**
	 * setReportFields
	 * 
	 * Sets the datafields property of datafields that are to be included in the block
	 * 
	 * @method setReportFields()
	 * @return void
	 * @access public
	 **/
	public function setReportFields(){
		$arr_table_ref_cnt = [];
		$this->has_aggregate = false;
		$this->report_fields = new \SplObjectStorage();
			
		$arr_ret = array();
		$arr_res = $this->datasource->getFieldData($this->id);
		if(is_array($arr_res)){
			foreach($arr_res as $s){
				$header_supp = null;
				$data_supp = null;
				if(isset($s['aggregate']) && !empty($s['aggregate'])){
					$this->has_aggregate = true;
				}
				if(isset($this->supp_factory)){
					if(isset($s['head_supp_id'])){
						$header_supp = $this->supp_factory->getColHeaderSupplemental($s['head_supp_id'], $s['head_a_href'], $s['head_a_rel'], $s['head_a_title'], $s['head_a_class'], $s['head_comment']);
					}
					if(isset($s['supp_id'])){
						$data_supp = $this->supp_factory->getColDataSupplemental($s['supp_id'], $s['a_href'], $s['a_rel'], $s['a_title'], $s['a_class']);
						$this->supp_param_fieldnames = array_unique(array_merge($this->supp_param_fieldnames, $data_supp->getLinkParamFields()));
					}
				}
				$arr_table_ref_cnt[$s['table_name']] = isset($arr_table_ref_cnt[$s['table_name']]) ? ($arr_table_ref_cnt[$s['table_name']] + 1) : 1;
				$datafield = new DbField($s['db_field_id'], $s['table_name'], $s['db_field_name'], $s['name'], $s['description'], $s['pdf_width'], $s['default_sort_order'],
						 $s['datatype'], $s['max_length'], $s['decimal_scale'], $s['unit_of_measure'], $s['is_timespan'], $s['is_foreign_key'], $s['is_nullable'], $s['is_natural_sort']);
				$this->report_fields->attach(new TableField($s['id'], $s['name'], $datafield, $s['category_id'], $s['is_displayed'], $s['display_format'], $s['aggregate'], $s['is_sortable'], $header_supp, $data_supp, $s['block_header_group_id'], $s['field_group'], $s['field_group_ref_key']));
			}
			$this->primary_table_name = array_search(max($arr_table_ref_cnt), $arr_table_ref_cnt);
			//set up arr_fields hierarchy
			if(is_array($arr_table_ref_cnt) && count($arr_table_ref_cnt) >  1){
				foreach($arr_table_ref_cnt as $t => $cnt){
					if($t != $this->primary_table_name){
						$this->joins[] = array('table'=>$t, 'join_text'=>$this->get_join_text($this->primary_table_name, $t));
					}
				}
			}
		}
	}
	
	public function getFieldDataType($field_name){
	    foreach ($this->report_fields as $k => $v) {
	        if($v->dbFieldName() == $field_name) {
	           return $v->getDataType();
	        }
	    }
	}
	
}
?>