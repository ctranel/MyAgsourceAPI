<?php

namespace myagsource\Page\Content\Table;

require_once APPPATH . 'libraries/Datasource/DbObjects/DbTable.php';
require_once APPPATH . 'libraries/Page/Content/ReportData.php';

use \myagsource\Page\Content\ReportData;
use \myagsource\Datasource\DbObjects\DbTable;
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
class TableData extends ReportData {
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
     * @todo: when moving away from html being sent from server, split this into "setData" and "getData", so that "prepData" can optionally be called in between
	 **/
    public function getData(){
		$criteria_key_value = $this->block->filterKeysValues();
		$arr_field_list = $this->block->getFieldlistArray();
        //if getting a subset, ensure results are most recent results
        if($this->block->maxRows() > 0){
            $sort_field = $this->block->getSortDateFieldName();
             if($sort_field){
                $criteria_key_value[$sort_field]['dbfrom'] = $this->report_datasource->getStartDate($this->block->primaryTableName(), $sort_field, $this->block->maxRows(), 'MM-dd-yyyy');
                $criteria_key_value[$sort_field]['dbto'] = $this->report_datasource->getRecentDates($this->block->primaryTableName(), $sort_field, 1, 'MM-dd-yyyy')[0];
            }
            unset($sort_field);
        }

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
			$results = $this->pivot($results);
		}
		elseif($this->block->hasCntRow() || $this->block->hasAvgRow() || $this->block->hasSumRow()){
			$results = $this->addAggregateRows($results);
		}
		$this->dataset = $results;
        return $this->dataset;
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

	/*
	 * prepareDisplayData
	 *
	 * preps query results for display
	 *
	 * @method structureData()
	 * @return void
	 * @author ctranel
	 */
	public function prepareDisplayData(){
		if(!isset($this->dataset) || !is_array($this->dataset) || empty($this->dataset)){
            throw new \exception('There is no data');
        }
        $c = 1;
        $fields = $this->block->reportFields();
        $ret = '';
        if(!$fields || count($fields) === 0) {
            throw new \exception('No columns found for this report');
        }
        if ($this->block->hasPivot()) {
            foreach ($fields as $f) {
                if (isset($this->dataset[$f->dbFieldName()]) && is_array($this->dataset[$f->dbFieldName()])):
                    $row_class = $c % 2 == 1 ? 'odd' : 'even';
                    //@todo: set a class property
                    if (!$f->isDisplayed()) {
                        continue;
                    }

                    $f->displayName($this->prepareHeaderCell($f, $f->displayName()));
                    foreach ($this->dataset[$f->dbFieldName()] as $k => $v):
                        $this->dataset[$f->dbFieldName()][$k] = $this->prepareCell($f, $v);
                    endforeach;
                    $c++;
                endif;
            }
        } else {
            foreach ($this->dataset as $k => $cr) {
                $row_class = $c % 2 == 1 ? 'odd' : 'even';
                //@todo: set a class property
                //@todo: pull this logic out of view?
                foreach ($fields as $f) {//$field_display => $field_name):
                    $field_name = $f->dbFieldName();
                    if (!$f->isDisplayed()) {
                        unset($this->dataset[$k][$field_name]);
                        continue;
                    }
                    if (is_array($cr) && array_key_exists($field_name, $cr)) {
                        $value = $cr[$field_name];
                    } elseif (is_object($cr) && property_exists($cr, $field_name)) {
                        $value = $cr->$field_name;
                    } else {
                        $value = '';
                    }
                    if ($c > (count($this->dataset) - $this->block->getAppendedRowsCount())) {
                        $this->dataset[$k][$field_name] = $this->prepareCell($f, $value, $cr, true);
                    } else {
                        $this->dataset[$k][$field_name] = $this->prepareCell($f, $value, $cr, false);
                    }
                }
                $c++;
            }
        }
        return $this->dataset;
 	}

    protected function prepareCell($f, $value, $cr = null, $appended_row = false){
        $field_name = $f->dbFieldName();
        if($f->isNumeric() && is_numeric($value)){// && $tmp_key != $value){
            $value = number_format($value, $f->decimalScale());
        }
        return $value;
    }

    protected function prepareHeaderCell($f, $value){
        return $value;
    }
}