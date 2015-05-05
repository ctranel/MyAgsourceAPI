<?php

namespace myagsource\Report\Content\Chart;

require_once APPPATH . 'libraries/Datasource/DbObjects/DbField.php';
require_once APPPATH . 'libraries/Report/Content/Chart/ChartField.php';
require_once APPPATH . 'libraries/Report/Content/Chart/XAxis.php';
require_once APPPATH . 'libraries/Report/Content/Chart/YAxis.php';
require_once APPPATH . 'libraries/Report/Content/Block.php';

use \myagsource\Datasource\DbObjects\DbField;
use \myagsource\Report\Content\Chart\ChartField;
use \myagsource\Report\Content\Block;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\Report\Content\Chart\XAxis;
use \myagsource\Report\Content\Chart\YAxis;

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
	 * array of category field names
	 * @var array
	 **/
	protected $categories;
	
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
			$sum_row, $avg_row, $bench_row, $is_summary, $display_type, $chart_type, SupplementalFactory $supp_factory) {
		parent::__construct($block_datasource, $id, $page_id, $name, $description, $scope, $active, $path, $max_rows, $cnt_row, 
			$sum_row, $avg_row, $bench_row, $is_summary, $display_type, $supp_factory);
		
		$this->setReportFields();
		
		$this->chart_type = $chart_type;
		$this->x_axes = new \SplObjectStorage();
		$this->y_axes = new \SplObjectStorage();
		$this->setChartAxes();
	}
		
	public function xAxes(){
		return $this->x_axes;
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

	/**
	 * @method setChartAxes - retrieve data for categories, axes, etc.
	 * @param int block id
	 * @return void
	 * @access protected
	 *
	 **/
	protected function setChartAxes(){
		$data = $this->datasource->getChartAxes($this->id);
		if(!is_array($data) || empty($data) || count($data) < 1){
			return false;
		}
		
		$this->categories = [];
		foreach($data as $a){
			$datafield = null;
			if(isset($a['db_field_id']) && !empty($a['db_field_id'])){
				$datafield = new DbField($a['db_field_id'], $a['table_name'], $a['db_field_name'], $a['name'], $a['description'], $a['pdf_width'], $a['default_sort_order'],
					$a['datatype'], $a['max_length'], $a['decimal_scale'], $a['unit_of_measure'], $a['is_timespan'], $a['is_foreign_key'], $a['is_nullable'], $a['is_natural_sort']);
				//add fields as a report field so it is included in the select statement
				$display_format = $a['data_type'] === 'datetime' ? 'MM-dd-yy' : null;
				$this->report_fields->attach(new ChartField($a['id'], $a['name'], $datafield, false, $display_format, null, 0));
				
			}
			if($a['x_or_y'] === 'x'){
				//if($a['data_type'] === 'datetime' || $a['data_type'] === 'date'){
				//	$this->xaxis_field = $datafield;
				//}
				if(isset($a['category']) && !empty($a['category'])){
					$this->categories[] = $a['category'];
				}
				$this->x_axes->attach(new XAxis($a['min'], $a['max'], $a['opposite'], $datafield, $a['data_type'], $a['text'], $a['category']));
			}
			if($a['x_or_y'] === 'y'){
				$this->y_axes->attach(new YAxis($a['min'], $a['max'], $a['opposite'], $a['text'], $datafield));
			}
		}
	}

	/**
	 * @method getAxesOutput
	 * @return array of output data for x and y axis.  null if no axes in chart
	 * @access protected
	 *
	 **/
	protected function getAxesOutput(){
		$tmp = [];
		$ret = [];
		$cnt = 0;
		if($this->x_axes->count() === 0 && $this->y_axes->count() === 0){
			return;
		}
		foreach($this->x_axes as $a){
			if($cnt === 0 || $a->category() === null){
				$tmp[$cnt] = $a->getOutputData();
			}
			if($cnt === 0 && $a->category() !== null){
				$tmp[$cnt]['categories'] = $this->categories;
			}
			$cnt++;
		}
		if(!empty($tmp)){
			$ret['x'] = $tmp;
		}
	
		$tmp = [];
		$cnt = 0;
		foreach($this->y_axes as $a){
			$tmp[$cnt] = $a->getOutputData();
			$cnt++;
		}
		if(!empty($tmp)){
			$ret['y'] = $tmp;
		}
		return $ret;
	}
	
	/**
	 * @method getOutputData
	 * @return array of output data for block
	 * @access public
	 *
	 **/
	public function getOutputData(){
		$ret = parent::getOutputData();
		$ret['chart_type'] = $this->chart_type;
		$ret['arr_axes'] = $this->getAxesOutput();
		if(empty($ret['arr_axes'])){
			unset($ret['arr_axes']);
		}
		$ret['series'] = $this->getSeriesOutput();
		if(empty($ret['series'])){
			unset($ret['series']);
		}
		return $ret;
	}

	/**
	 * @method getSeriesOutput
	 * @return array of output data for block
	 * @access protected
	 *
	 **/
	protected function getSeriesOutput(){
		if(!empty($this->categories)){
			return $this->derive_series();//count($this->json['data'], COUNT_RECURSIVE));
		}
		$ret = [];
		foreach($this->report_fields as $f){
			if($f->isDisplayed()){
				$ret[] = [
					'name' => $f->displayName(),
					'um' => $f->unitOfMeasure(),
				];
			}
		}
		return $ret;
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
	
	protected function derive_series(){//$cnt_arr_datapoints){
		//as of 9/11/2014, in order to get labels correct, we need to change the header text in blocks_select_fields for the first {number of series'} fields
		//in order for this function to work correctly, the DB view must have all fields in one row, or have series' as columns and categories as row keys.
		$return_val = [];
		$c = 0;
	
		//allow for normalized or non-normalized data
//		if((int)($cnt_arr_datapoints / $this->report_fields->count()) === 1){
			$num_series = $this->report_fields->count() / count($this->categories);
//		}
//		else{
//			$num_series = $this->report_fields->count();
//		}
	
		foreach($this->report_fields as $f){
			$return_val[$c]['name'] = $f->displayName();
			if($f->unitOfMeasure()){
				$return_val[$c]['um'] = $f->unitOfMeasure();
			}
			if($f->axisIndex()){
				$return_val[$c]['yAxis'] = $f->axisIndex();
			}
			if($f->chartType()){
				$return_val[$c]['type'] = $f->chartType();
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