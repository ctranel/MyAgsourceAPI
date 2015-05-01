<?php

namespace myagsource\Report\Content\Table;

//require_once APPPATH . 'libraries/Datasource/DbObjects/DbTable.php';
require_once APPPATH . 'libraries/Datasource/DbObjects/DbField.php';
require_once APPPATH . 'libraries/Report/Content/Table/TableField.php';
require_once APPPATH . 'libraries/Report/Content/Block.php';

//use \myagsource\Datasource\DbObjects\DbTable;
use \myagsource\Datasource\DbObjects\DbField;
use \myagsource\Report\Content\Table\TableField;
use \myagsource\Report\Content\Block;
use \myagsource\Supplemental\Content\SupplementalFactory;

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
	 */
	function __construct($block_datasource, $id, $page_id, $name, $description, $scope, $active, $path, $max_rows, $cnt_row, 
			$sum_row, $avg_row, $bench_row, $is_summary, $display_type, SupplementalFactory $supp_factory) {
		parent::__construct($block_datasource, $id, $page_id, $name, $description, $scope, $active, $path, $max_rows, $cnt_row, 
			$sum_row, $avg_row, $bench_row, $is_summary, $display_type, $supp_factory);
		
		$this->setReportFields();
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
				if(isset($supp_factory)){
					if(isset($s['head_supp_id'])){
						$header_supp = $supp_factory->getColHeaderSupplemental($s['head_supp_id'], $s['head_a_href'], $s['head_a_rel'], $s['head_a_title'], $s['head_a_class'], $s['head_comment']);
					}
					if(isset($s['supp_id'])){
						$data_supp = $supp_factory->getColDataSupplemental($s['supp_id'], $s['a_href'], $s['a_rel'], $s['a_title'], $s['a_class']);
					}
				}
				$arr_table_ref_cnt[$s['table_name']] = isset($arr_table_ref_cnt[$s['table_name']]) ? ($arr_table_ref_cnt[$s['table_name']] + 1) : 1;
				$datafield = new DbField($s['db_field_id'], $s['table_name'], $s['db_field_name'], $s['name'], $s['description'], $s['pdf_width'], $s['default_sort_order'],
						 $s['datatype'], $s['max_length'], $s['decimal_scale'], $s['unit_of_measure'], $s['is_timespan'], $s['is_foreign_key'], $s['is_nullable'], $s['is_natural_sort']);
				$this->report_fields->attach(new TableField($s['id'], $s['name'], $datafield, $s['is_displayed'], $s['display_format'], $s['aggregate'], $s['is_sortable'], $header_supp, $data_supp, $s['block_header_group_id']));
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
}
?>