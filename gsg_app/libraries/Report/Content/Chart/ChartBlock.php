<?php

namespace myagsource\Report\Content\Chart;

require_once APPPATH . 'libraries/Datasource/DbObjects/DbField.php';
require_once APPPATH . 'libraries/Report/Content/Chart/ChartField.php';
require_once APPPATH . 'libraries/Report/Content/Block.php';

use \myagsource\Datasource\DbObjects\DbField;
use \myagsource\Report\Content\Chart\ChartField;
use \myagsource\Report\Content\Block;
use \myagsource\Supplemental\Content\SupplementalFactory;

/**
 * Name:  ChartBlock
 *
 * Author: ctranel
 *
 * Created:  02-03-2015
 *
 * Description:  Contains properties and methods specific to displaying chart blocks of the website.
 *
 */
class ChartBlock extends Block {
	/**
	 * chart_type
	 * @var string
	 **/
	protected $chart_type;
	
	/**
	 * collection of XAxis objects
	 * @var SplObjectStorage
	 **/
	protected $x_axes;
	
	/**
	 * collection of YAxis objects
	 * @var SplObjectStorage
	 **/
	protected $y_axes;
		
	/**
	 */
	function __construct($block_datasource, $id, $page_id, $name, $description, $scope, $active, $path, $max_rows, $cnt_row, 
			$sum_row, $avg_row, $bench_row, $is_summary, $display_type) {
		parent::__construct($block_datasource, $id, $page_id, $name, $description, $scope, $active, $path, $max_rows, $cnt_row, 
			$sum_row, $avg_row, $bench_row, $is_summary, $display_type);
	}
		
	/**
	 * (non-PHPdoc)
	 *
	 * @see \myagsource\Site\iWebContent::children()
	 *
	 */
	public function children() {
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
	public function setReportFields(SupplementalFactory $supp_factory = null){
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
				$this->report_fields->attach(new ChartField($s['id'], $s['name'], $datafield, $s['is_displayed'], $s['display_format'], $s['aggregate'], $s['is_sortable'], $header_supp, $data_supp));
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
/*	
	public function loadData($report_count){
		$arr_axes = $report_datasource->get_chart_axes($arr_this_block['id']);
		$x_axis_date_field = NULL;
	
		$this->json['name'] = $arr_this_block['name'];
		$this->json['description'] = $arr_this_block['description'];
		$this->json['chart_type'] = $arr_this_block['chart_type'];
	
		$tmp_categories = null;
		if(isset($arr_axes) && !empty($arr_axes)){
			$this->json['arr_axes'] = $arr_axes;
			$tmp_x_axis = current($this->json['arr_axes']['x']);
			if(isset($tmp_x_axis['categories'])){
				$tmp_categories = $tmp_x_axis['categories'];
			}
		}
	
		$this->json['herd_code'] = $this->session->userdata('herd_code');
	
		$report_datasource->set_chart_fields($arr_this_block['id']);
		$arr_fields = $report_datasource->get_fields();
		if(!is_array($arr_fields) || empty($arr_fields)){
			return false;
		}
		$arr_fieldnames = $this->derive_field_array($arr_fields);
	
		if(is_array($arr_axes['x'])){
			foreach($arr_axes['x'] as $a){
				$tmp_cat = isset($a['categories']) && !empty($a['categories']) ? $a['categories'] : NULL;
				if($a['data_type'] === 'datetime' || $a['data_type'] === 'date'){
					$x_axis_date_field = $a['db_field_name'];
				}
				if(isset($a['db_field_name']) && !empty($a['db_field_name'])){
					$report_datasource->add_field(array('Date' => $a['db_field_name']));
				}
			}
		}
		$this->json['data'] = $report_datasource->get_graph_data($arr_fieldnames, $this->filters->criteriaKeyValue(), $this->max_rows, $x_axis_date_field, $arr_this_block['path'], $tmp_categories);
		$this->json['series'] = $this->derive_series($arr_fields, $this->json['chart_type'], $tmp_categories, count($this->json['data'], COUNT_RECURSIVE));
		$this->json['filter_text'] = $this->filters->get_filter_text();
	}
*/	
	protected function derive_field_array($arr_fields){
		$return_val = array();
		$c = 0;
			
		foreach($arr_fields as $k=>$f){
			$return_val[$c] = $f;
			$c++;
		}
		return $return_val;
	}
	
	protected function derive_series($arr_fields, $chart_type, $arr_categories, $cnt_arr_datapoints){
		//as of 9/11/2014, in order to get labels correct, we need to change the header text in blocks_select_fields for the first {number of series'} fields
		//in order for this function to work correctly, the DB view must have all fields in one row, or have series' as columns and categories as row keys.
		$return_val = array();
		$c = 0;
		$arr_chart_type = $this->{$this->primary_model_name}->get_chart_type_array();
		$arr_axis_index = $this->{$this->primary_model_name}->get_axis_index_array();
	
		//allow for normalized or non-normalized data
		if((int)($cnt_arr_datapoints / count($arr_fields)) === 1){
			$num_series = count($arr_fields) / count($arr_categories);
		}
		else{
			$num_series = count($arr_fields);
		}
	
		foreach($arr_fields as $k=>$f){
			//these 2 arrays need to have the same numeric index so that the yaxis# can be correctly assigned to series
			$return_val[$c]['name'] = $k;
			if(isset($this->{$this->primary_model_name}->arr_unit_of_measure[$f]) && !empty($this->{$this->primary_model_name}->arr_unit_of_measure[$f])){
				$return_val[$c]['um'] = $this->{$this->primary_model_name}->arr_unit_of_measure[$f];
			}
			if(isset($arr_axis_index[$f]) && !empty($arr_axis_index[$f])){
				$return_val[$c]['yAxis'] = $arr_axis_index[$f];
			}
			if(isset($arr_chart_type[$f]) && !empty($arr_chart_type[$f])){
				$return_val[$c]['type'] = $arr_chart_type[$f];
			}
			$c++;
			if($c >= $num_series){
				break;
			}
		}
		return $return_val;
	}
}



?>