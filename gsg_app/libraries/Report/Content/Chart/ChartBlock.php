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

	//added to know when to remove or keep nulls in chart data - KLM
	/**
	 * chart_type
	 * @var boolean
	 **/
	protected $keep_nulls;
	
	/**
	 */
	function __construct($block_datasource, $id, $page_id, $name, $description, $scope, $active, $path, $max_rows, $cnt_row, 
			$sum_row, $avg_row, $bench_row, $is_summary, $display_type, $chart_type, SupplementalFactory $supp_factory, $field_groups, $keep_nulls) {
		parent::__construct($block_datasource, $id, $page_id, $name, $description, $scope, $active, $path, $max_rows, $cnt_row, 
			$sum_row, $avg_row, $bench_row, $is_summary, $display_type, $supp_factory, $field_groups);
		
		$this->setReportFields();
		
		$this->keep_nulls = $keep_nulls;
		$this->chart_type = $chart_type;
		$this->x_axes = new \SplObjectStorage();
		$this->y_axes = new \SplObjectStorage();
		$this->setChartAxes();
		$this->setSeries();
	}

	public function keepNulls(){
	    return $this->keep_nulls;
	}
	
	public function xAxes(){
		return $this->x_axes;
	}
	
//currently using for testing only
	public function series(){
		return $this->series;
	}

	public function chartType(){
		return $this->chart_type;
	}

	public function categories(){
		return $this->categories;
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
				$this->report_fields->attach(new ChartField($s['id'], $s['name'], $datafield, $s['category_id'], $s['is_displayed'], $s['display_format'], $s['aggregate'], $s['is_sortable'], $s['chart_type'], $s['axis_index'], $s['trend_type'], $s['field_group'], $header_supp, $data_supp, $s['field_group'], $s['field_group_ref_key']));
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
				//@todo: this probably shouldn't be in report_fields.  Have prep select function pull fields from x axis objects?
				//$this->report_fields->attach(new ChartField($a['id'], $a['name'], $datafield, null, false, $display_format, null, true, null, null, null, null));
				
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
	 * @method getXAxisOutput
	 * @return array
	 * @access protected
	 *
	 **/
	protected function getXAxisOutput(){
		$ret = [];
		$cnt = 0;
		if($this->x_axes->count() === 0){
			return;
		}
		foreach($this->x_axes as $a){
			if($cnt === 0 || $a->category() === null){
				$ret[$cnt] = $a->getOutputData();
			}
			if($cnt === 0 && $a->category() !== null){
				$ret[$cnt]['categories'] = $this->categories;
			}
			$cnt++;
		}
		return $ret;
	}

	/**
	 * @method getYAxisOutput
	 * @return array
	 * @access protected
	 *
	 **/
	protected function getYAxisOutput(){
		$ret = [];
		$cnt = 0;
		if($this->y_axes->count() === 0){
			return;
		}

		foreach($this->y_axes as $a){
			$ret[$cnt] = $a->getOutputData();
			$cnt++;
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
		if($this->x_axes->count() > 0){
			$ret['arr_axes']['x'] = $this->getXAxisOutput();
		}
		if($this->y_axes->count() > 0){
			$ret['arr_axes']['y'] = $this->getYAxisOutput();
		}
		if(isset($this->series) && !empty($this->series)){
			$ret['series'] = $this->series;
		}
		return $ret;
	}

	/**
	 * setSeries
	 * 
	 * 
	 * 
	 * @return void
	 * @access protected
	 *
	 **/
	protected function setSeries(){
		if(!empty($this->categories) || !empty($this->field_groups)){
			$this->series = $this->deriveSeries();//count($this->json['data'], COUNT_RECURSIVE));
			$this->series = array_values($this->series);
			return;
		}
		$cnt = 0;

		//boxplots have 3 columns per series, all other chart types are 1:1
		foreach($this->report_fields as $f){
			$idx = $f->fieldGroup();
			$idx = isset($idx) ? (int)$idx : $cnt;
				
			if($f->isDisplayed()){
				$this->series[$idx] = [
					'name' => $f->displayName(),
					'um' => $f->unitOfMeasure(),
					'type' => $f->chartType(),
					'yAxis' => $f->axisIndex(),
				];
				if($f->trendType() !== null){
					$this->series[$idx]['regression'] = true;
					$this->series[$idx]['regressionSettings'] = [
						'type' => $f->trendType(),
						'order' => 8,
					];
				}
				$cnt++;
			}
		}
		$this->series = array_values($this->series);
	}

	/**
	 * @method numSeries
	 * 
	 * used only for boxplots.  hopefully this functionality can be absorbed by field group functionality
	 * 
	 * @return int number of series on chart
	 * @access protected
	 *
	 **/
	public function numSeries(){
		$series = [];
		foreach($this->report_fields as $f){
			$sg = $f->fieldGroup();
			if(!isset($sg)){
				continue;
			}
			$idx = array_search($sg, $series, true);
			if($idx === false){
				$series[] = $f->fieldGroup();
			}
		}
		if(empty($series)){
			return $this->report_fields->count();
		}
		return count($series);
	}

	/**
	 * @method deriveSeries
	 * @param int num series'
	 * @return array of output data for block
	 * @access protected
	 *
	 **/
	protected function deriveSeries(){
		$return_val = [];
		$cat_cnt_divisor = count($this->categories) === 0 ? 1 : count($this->categories);
		$field_cnt = (int)$this->report_fields->count() / $cat_cnt_divisor;

		$c = 0;
		foreach($this->report_fields as $f){
			$fg = $f->fieldGroup();
			if(isset($fg)){
				$idx = $fg;
				$name = $this->field_groups[$fg]['name'];
			}
			else{
				$idx = $f->id();
				$name = $f->displayName();
			}
			
			$return_val[$idx]['name'] = $name;
			if($f->unitOfMeasure()){
				$return_val[$idx]['um'] = $f->unitOfMeasure();
			}
			if($f->axisIndex()){
				$return_val[$idx]['yAxis'] = $f->axisIndex();
			}
			if($f->chartType()){
				$return_val[$idx]['type'] = $f->chartType();
			}
			
			$c++;
			if($c >= $field_cnt){
				break;
			}
		}
		return $return_val;
	}
	
	/************************************* 
	 * overridden Block functions
	 *************************************/
	
	/**
	 * @method getSelectFields()
	 *
	 * Retrieves fields designated as select, supplemental params (if set),
	 *
	 * @return string table name
	 * @access public
	 * */
	public function getSelectFields(){
		$ret = parent::getSelectFields();
		if(isset($this->x_axes) && is_a($this->x_axes, 'SplObjectStorage')){
			foreach($this->x_axes as $a){
				$type = $a->dataType();
				if(strpos($type, 'date') !== false || strpos($type, 'time') !== false){
					$ret[] = "FORMAT(" . $a->dbFieldName() . ", 'MM-dd-yyyy', 'en-US') AS " . $a->dbFieldName();
				}
				else{
					$ret[] = $a->dbFieldName();
				}
			}
		}
		return $ret;
	}
}
?>
