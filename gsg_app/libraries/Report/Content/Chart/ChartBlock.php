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
	 * collection of series objects
	 * @var SplObjectStorage
	 **/
	protected $series;

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

	public function chartType(){
		return $this->chart_type;
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
					}
				}
				$arr_table_ref_cnt[$s['table_name']] = isset($arr_table_ref_cnt[$s['table_name']]) ? ($arr_table_ref_cnt[$s['table_name']] + 1) : 1;
				$datafield = new DbField($s['db_field_id'], $s['table_name'], $s['db_field_name'], $s['name'], $s['description'], $s['pdf_width'], $s['default_sort_order'],
						 $s['datatype'], $s['max_length'], $s['decimal_scale'], $s['unit_of_measure'], $s['is_timespan'], $s['is_foreign_key'], $s['is_nullable'], $s['is_natural_sort']);
				$this->report_fields->attach(new ChartField($s['id'], $s['name'], $datafield, $s['is_displayed'], $s['display_format'], $s['aggregate'], $s['is_sortable'], $s['chart_type'], $s['axis_index'], $s['trend_type'], $s['series_group'], $header_supp, $data_supp));
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
				$this->report_fields->attach(new ChartField($a['id'], $a['name'], $datafield, false, $display_format, null, 0, null, null, null, null));
				
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
	 * @param int number of datapoints
	 * @return array of output data for block
	 * @access public
	 *
	 **/
	public function getOutputData(){//$cnt_datapoints){
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
	 * getSeriesOutput
	 * 
	 * 
	 * 
	 * @param int number of datapoints
	 * @return array of output data for block
	 * @access protected
	 *
	 **/
	protected function getSeriesOutput(){//$cnt_datapoints){
		if(!empty($this->categories)){
			return $this->deriveSeries();//count($this->json['data'], COUNT_RECURSIVE));
		}
		$ret = [];
		$cnt = 0;

		//boxplots have 3 columns per series, all other chart types are 1:1
		foreach($this->report_fields as $f){
			$idx = $f->seriesGroup();
			$idx = isset($idx) ? (int)$idx : $cnt;
				
			if($f->isDisplayed()){
				$ret[$idx] = [
					'name' => $f->displayName(),
					'um' => $f->unitOfMeasure(),
					'type' => $f->chartType(),
					'yAxis' => $f->axisIndex(),
				];
				if($f->trendType() !== null){
					$ret[$idx]['regression'] = true;
					$ret[$idx]['regressionSettings'] = [
						'type' => $f->trendType(),
						'order' => 8,
					];
				}
				$cnt++;
			}
		}
		return $ret;
	}

	/**
	 * @method numSeries
	 * @return int number of series on chart
	 * @access protected
	 *
	 **/
	public function numSeries(){
		$series = [];
		foreach($this->report_fields as $f){
			$sg = $f->seriesGroup();
			if(!isset($sg)){
				continue;
			}
			$idx = array_search($sg, $series, true);
			if($idx === false){
				$series[] = $f->seriesGroup();
			}
		}
		if(empty($series)){
			return $this->report_fields->count();
		}
		return count($series);
	}

	/**
	 * @method deriveSeries
	 * @param int number of datapoints
	 * @return array of output data for block
	 * @access protected
	 *
	 **/
	protected function deriveSeries(){//$cnt_datapoints){
		//as of 9/11/2014, in order to get labels correct, we need to change the header text in blocks_select_fields for the first {number of series'} fields
		//in order for this function to work correctly, the DB view must have all fields in one row, or have series' as columns and categories as row keys.
		$return_val = [];
		$c = 0;

		//if there is more than one datapoint per category
		if((int)($this->report_fields->count() / count($this->categories)) > 1){
			$num_series = $this->report_fields->count() / count($this->categories);
		}
		else{
			$num_series = $this->report_fields->count();
		}

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