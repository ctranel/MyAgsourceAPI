<?php

namespace myagsource\Report\Content\Chart;

require_once APPPATH . 'libraries/Report/Content/BlockData.php';

use \myagsource\Report\Content\BlockData;

/**
 * Name:  ChartData
 *
 * Author: ctranel
 *
 * Created:  04-29-2015
 *
 * Description: Data handler for chart blocks
 *
 */
class ChartData extends BlockData{
	/**
	protected $block;
	protected $report_datasource;
	protected $dataset;
	**/	
	
	/**
	 * categories
	 *
	 * @var Array
	protected $categories;
	 **/
	
	/**
	 * x_axis_field_name
	 *
	 * @var string
	 **/
	protected $x_axis_dbfield_name;
	
	/**
	 * @todo: add filter data
	 */
	function __construct(ChartBlock $block, \Report_data_model $report_datasource) {
		parent::__construct($block, $report_datasource);
//		$this->setCategories();
		$this->setXAxesField();
	}

	/* -----------------------------------------------------------------
	*  setCategories

	*  Sets category properties for chart data object

	*  @author: ctranel
	*  @date: Apr 29, 2015
	*  @return void
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	protected function setXAxesField(){
		foreach($this->block->xAxes() as $x){
			$f = $x->dbFieldName();
			if(isset($f) && !empty($f)){
				$this->x_axis_dbfield_name = $f;
			}
		}
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
		if(isset($this->x_axis_dbfield_name) && isset($this->x_axis_dbfield_name)){
			$criteria_key_value[$this->x_axis_dbfield_name]['dbfrom'] = $this->report_datasource->getStartDate($this->block->primaryTableName(), $this->x_axis_dbfield_name, $this->block->maxRows(), 'MM-dd-yyyy');
			$criteria_key_value[$this->x_axis_dbfield_name]['dbto'] = $this->report_datasource->getRecentDates($this->block->primaryTableName(), $this->x_axis_dbfield_name, 1, 'MM-dd-yyyy')[0];
		}
		
		$criteria_key_value = $this->whereCriteria($criteria_key_value);
		$select_fields = $this->block->getSelectFields();
		$this->dataset = $this->report_datasource->search($this->block, $select_fields, $criteria_key_value);

		$tmp_cat = $this->block->categories();
		if(isset($tmp_cat) && is_array($tmp_cat) && !empty($tmp_cat)){
			$this->splitByCategories();
			$ret = true;
		}

		$tmp_fg = $this->block->fieldGroups();
		if(isset($tmp_fg) && is_array($tmp_fg) && !empty($tmp_fg)){
			$this->concatFieldGroups();
			$ret = true;
		}
		
		//if categories and/or field groups were set, no need to continue
		if(isset($ret)){
			return $this->dataset;
		}
		
		if(!isset($this->x_axis_dbfield_name)){//not a category or trend chart (e.g., pie chart)
			return array_values($this->dataset);
		}

		//default condition
		if($this->block->chartType() === 'boxplot'){
			$num_boxplot_series = (int)($this->block->reportFields()->count() / 3);
			return $this->setBoxplotData(200000000);
		}
		return $this->setLongitudinalData();
	}
	
	/**
	 * @method setLongitudinalData()
	 * @param string date field used on graph (test_date)
	 * @return array of data for the graph
	 * @access protected
	 *
	 **/
	protected function setLongitudinalData(){
		$count = count($this->dataset);
		for($x = 0; $x < $count; $x++){
			$arr_y_values = $this->dataset[$x];

			$arr_fields = array_keys($arr_y_values);
			$date_key = array_search($this->x_axis_dbfield_name, $arr_fields);
			unset($arr_fields[$date_key]);
			if($this->x_axis_dbfield_name == 'age_months'){
				foreach($arr_fields as $k=>$f){
					$tmp_data = is_numeric($this->dataset[$x][$f]) ? (float)$this->dataset[$x][$f] : $this->dataset[$x][$f];
					$arr_return[$k][] = array($this->dataset[$x][$this->x_axis_dbfield_name], $tmp_data);
				}
			}
			elseif(isset($this->dataset[$x][$this->x_axis_dbfield_name]) && !empty($this->dataset[$x][$this->x_axis_dbfield_name])){
				$arr_d = explode('-', $this->dataset[$x][$this->x_axis_dbfield_name]);
				foreach($arr_fields as $k=>$f){
					$tmp_data = is_numeric($this->dataset[$x][$f]) ? (float)$this->dataset[$x][$f] : $this->dataset[$x][$f];
					$arr_return[$k][] = [(mktime(0, 0, 0, $arr_d[0], $arr_d[1],$arr_d[2]) * 1000), $tmp_data];
				}
			}
		}
		if(isset($arr_return) && is_array($arr_return)){
			return $arr_return;
		}
		else return FALSE;
	}

		
	/**
	 * @method setBoxplotData()
	 * @param int number of boxplot series (BOXPLOT SERIES FIELDS MUST ALL BE IMMEDIATELY AFTER THE TEST DATE)
	 * @return array of data for the graph
	 * @access protected
	 * @todo: assumes that boxplots are in groupings of 3
	 *
	 **/
	protected function setBoxplotData($adjustment = 200000000){
		$row_count = 0;
		$arr_series = [];
		$num_series = $this->block->numSeries();
		foreach ($this->dataset as $d){ //foreach row
			//ignore the row if the x axis fields is not set
			if(!isset($d[$this->x_axis_dbfield_name])){
				continue;
			}
			//set a variable so we can pair date with each data point
			$arr_d = explode('-', $d[$this->x_axis_dbfield_name]);
			unset($d[$this->x_axis_dbfield_name]); //remove date so we can loop through the remaining data points
			//if the date is formatted ('Mon-yr')
			if(count($arr_d) == 2){
				$arr_month = [];
				$this_date =strtotime($arr_d[0] . ' 15, ' . $arr_d[1]) * 1000;
			}
			//else if the date is formatted ('m-d-y')
			elseif(count($arr_d) == 3){
				$this_date = mktime(0, 0, 0, $arr_d[0], $arr_d[1], $arr_d[2]) * 1000;
			}

			$field_count = 1;
			$series_count = 0;
			$offset = $this->getSeriesOffset($num_series, $series_count, $adjustment);
			$arr_series[$series_count][$row_count] = array($this_date + $offset);
//			$arr_series[$series_count + 1][$row_count] = array($this_date + $offset);
			foreach ($d as $f){ //for each field in row
				$tmp_data = is_numeric($f) ? (float)$f : $f;
				if($field_count <= ($num_series * 3)){// using boxplot chart requires 4 datapoints
					$modulus = $field_count%3;
					$arr_series[$series_count][$row_count][] = $tmp_data;
					//boxplots require 5 datapoints, need to replicate each end of the box (i.e., blend whiskers into box)
					//@todo: this should be done on the client (highcharts)
					if($modulus === 1 || $modulus === 0){
						$arr_series[$series_count][$row_count][] = $tmp_data;
					}
					if($modulus == 0 && $field_count > 1){
						$series_count ++;
						if(($field_count + 1) <= ($num_series * 3)){
							$offset = $this->getSeriesOffset($num_series, $series_count, $adjustment);
							$arr_series[$series_count][$row_count] = array(($this_date + $offset)); //adjust date so that multiple boxplots are not on top of each other
						}
					}
				}
				$field_count++;
			}
			$row_count++;
		}
		return $arr_series;
	}
	
	/**
	 * @method getSeriesOffset()
	 * @param int number of series' in the dataset for which the offset is being calculated
	 * @param int numeric position of series for which offset is currently being calculated
	 * @param int standardized unit on which adjustment calculation is based
	 * @return int amount to offset date in series
	 * @access protected
	 *
	 **/
	protected function getSeriesOffset($num_series, $series_count, $adjustment){
		$offset = 0;;
		if($num_series == 2){
			if($series_count == 0) {
				$offset -= $adjustment;
			}
			if($series_count == 2) {
				$offset += $adjustment;
			}
		}
		if($num_series == 3){
			if($series_count == 0) {
				$offset -= ($adjustment * 2);
			}
			if($series_count == 4) {
				$offset += ($adjustment * 2);
			}
		}
		return $offset;
	}
	
	/**
	 * @method setRowToSeries - used when data for multiple series' are returned in one row.  
	 * Breaks data down so that there is one row per category, each row having one entry for each series.
	 * 
	 * @return array of data for the graph
	 * @access protected
	 *
	protected function setRowToSeries(){
		if(isset($this->block->categories()) && is_array($this->block->categories())){
			return $this->splitByCategories();
		}
		//handle field groups

		if(isset($this->block->fieldGroups()) && is_array($this->block->fieldGroups())){
			return $this->concatFieldGroups();
		}
	}
	 **/

	/**
	 * @method concatFieldGroups - used when data for multiple categories are returned in one row.  
	 * Breaks data down so that there is one row per category, each row having one entry for each series.
	 * 
	 * @return array of data for the graph
	 * @access protected
	 *
	 **/
	
	protected function concatFieldGroups(){
		if(is_array($this->dataset) && !empty($this->dataset)){
			$arr_return = [];
			foreach($this->dataset as $series=>$rows){
				$categories = [];
				$cat_idx = 0;
				foreach($rows as $cat=>$v){
					if(!array_key_exists($cat, $categories)){
						$categories[$cat] = $cat_idx;
						$cat_idx++;
					}
					//$cnt = 0;
					$ser_idx = 0;
					$field_definitions = $this->block->reportFields();
					foreach($field_definitions as $f){
						if(!isset($v[$f->dbFieldName()])){
							continue;
						}

						if($f->fieldGroup()){
							$ser_idx = $f->fieldGroup();
						}
						else{
							$ser_idx++;
						}
						
						$ref_key = $f->fieldGroupRefKey();
//print($ser_idx . ' - ' . $categories[$cat] . ' - ' . $ref_key . ' - ' . $v[$f->dbFieldName()] . "<br>\n");						
						$arr_return[$ser_idx][$categories[$cat]][$ref_key] = $v[$f->dbFieldName()];
						
						//@todo: 
						if(!isset($arr_return[$ser_idx][$categories[$cat]]['x'])){
							$arr_return[$ser_idx][$categories[$cat]]['x'] = $categories[$cat];
						}
					}
				}
			}
			foreach($arr_return as $k => $a){
				$this->dataset[$k] = array_values($a);
			}
			$this->dataset = array_values($this->dataset);
		}
		else return FALSE;
	}
	
	/**
	 * @method splitByCategories - used when data for multiple categories are returned in one row.  
	 * Breaks data down so that there is one row per category, each row having one entry for each series.
	 * 
	 * @return array of data for the graph
	 * @access protected
	 *
	 **/
	
	protected function splitByCategories(){
		$mod_base = (int)($this->block->numFields() /count($this->block->categories()));
		if(is_array($this->dataset) && !empty($this->dataset)){
			$key = 0;
			foreach($this->dataset as $k=>$row){
				$count = 1;

				$key++;
				//must account for multiple series being returned in a single row
				foreach($this->block->reportFields() as $kk => $f){
					if($count > $mod_base && $count % $mod_base == 1){
						$key++;
					}
					if(!isset($key)) $key = $k;
					
					//if field groups are used, use that. Otherwise use the key that was calculated based on incremented series'
					$fg = $f->fieldGroup();
					$series = isset($fg) ? $fg : $key;
					unset($fg);
					
					$cat = $f->categoryId();
					
					$arr_return[$series][$cat][$f->dbFieldName()] = $row[$f->dbFieldName()];
					$count++;
				}
			}
			$this->dataset = $arr_return;
		}
		else return FALSE;
	}
}
?>