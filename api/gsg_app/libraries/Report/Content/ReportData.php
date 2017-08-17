<?php

namespace myagsource\Report\Content;

require_once APPPATH . 'libraries/Report/iReport.php';
require_once APPPATH . 'libraries/Report/iReportData.php';

use \myagsource\Report\iReport;
use \myagsource\Report\iReportData;

/**
 * Name:  ReportData
 *
 * Author: ctranel
 *
 * Created:  02-18-2015
 *
 * Description: Data handler for reports
 *
 */
abstract class ReportData implements iReportData{
	/**
	 * report
	 *
	 * report report
	 * @var \myagsource\Report\iReport
	 **/
	protected $report;
	
	/**
	 * report_datasource
	 *
	 * report datasource
	 * @var \Report_data_model 
	 **/
	protected $report_datasource;

	/**
	 * is_metric
	 *
	 * @var boolean should data be displayed as metric
	 **/
	protected $is_metric;

	/**
	 * @todo: add filter data
	 */
	function __construct(iReport $report, \Report_data_model $report_datasource, $is_metric) {//, Benchmarks $benchmarks, DbTable $db_table
		$this->report = $report;
		$this->report_datasource = $report_datasource;
		$this->is_metric = (bool)$is_metric;
	}

    /**
     * reportFields()
     *
     * @return iBlockField[]
     * @author ctranel
     **/
    protected function reportFields(){
        return $this->report->reportFields();
    }

    /**
     * selectFields()
     *
     * In place to allow child classes to interact with select fields
     *
     * @return array of sql-prepped select fields
     * @author ctranel
     **/
    protected function selectFields(){
        $ret = [];
        $report_fields = $this->reportFields();
        if(isset($report_fields) && count($report_fields) > 0){
            foreach($report_fields as $f){
                $ret[] = $f->selectFieldText($this->is_metric);
            }
        }
        //supplemental params
        if(isset($this->dataset_supplemental) && count($this->dataset_supplemental) > 0){
            foreach($this->dataset_supplemental as $f){
                $ret = array_unique(array_merge($ret, $f->getLinkParamFields()));
            }
        }

        return $ret;
    }


    /** function whereCriteria
	 *
	 * translates filter criteria into sql format
	 * @param $arr_filter_criteria
	 * @return void
	 */
	
	protected function whereCriteria($arr_where_criteria){
		if(isset($arr_where_criteria) && is_array($arr_where_criteria)){
			foreach($arr_where_criteria as $k => $v){
				//@todo: the below is only for databases as datasource
				if(strpos($k, '.') === FALSE) {
					$tbl = $this->report->getFieldTable($k); //get table for this report
					$tbl = isset($tbl) && !empty($tbl) ? $tbl : $this->report->primaryTableName();
					$db_field = $tbl . '.' . $k;

					$arr_where_criteria[$k]['column'] = $db_field;
				}
			}
		}
		return $arr_where_criteria;
	}
	
	/*
	 * @method pivot()
	 * @param array dataset
	 * @return array pivoted resultset
	 * @author ctranel
	 */
	public function pivot($arr_dataset){
		$header_field = $this->report->pivotFieldName();
		$new_dataset = [];
		//don't want to insert the sum or count in middle of row, so store in temp vars until end
		$sum = [];
		$count = [];

		if(!isset($arr_dataset) || empty($arr_dataset)){
			return false;
		}

		//map the keys to a numeric array so they are an array of objects like non-pivoted datasets
        $tmp = current($arr_dataset);
        unset($tmp[$header_field]);
        $key_map = array_keys($tmp);
        $key_map[] = $header_field;
        $key_map = array_flip($key_map);

        $row_cnt = 1;
		foreach($arr_dataset as $row){
			foreach($row as $name => $val){
                if(strpos($name, 'isnull') === false && isset($row[$header_field]) && !empty($row[$header_field])) { //2nd part eliminates rows where fresh date is null (FCS)
					$new_dataset[$key_map[$name]][$row_cnt] = $val;
					if(!isset($sum[$key_map[$name]]) && $val !== null){
						$sum[$key_map[$name]] = 0;
						$count[$key_map[$name]] = 0;
					} 
					
					if($val !== NULL){
						$sum[$key_map[$name]] += $val;
						$count[$key_map[$name]]++;
					}
                    if($row_cnt === 1){
                        //insert label into first spot in row
                        array_unshift($new_dataset[$key_map[$name]], $this->report->getFieldLabelByName($name));
                    }
				}
			}
			//if the conditions within the inner loop were met, the field labels have been added to dataset, so we set first to false
            if(isset($row[$header_field]) && !empty($row[$header_field])){
                $row_cnt++;
            }
		}

		//Data is pivoted, now handle aggregate rows.
		$first = true;
		foreach($new_dataset as $k=>&$a){
			if(!empty($k)){
//				if($bool_bench_column){
//					if($arr_benchmarks[$k] !== NULL) $sum_data['benchmark'] = round($arr_benchmarks[$k], $this->arr_decimal_points[$k]);//strpos($arr_benchmarks[$k], '.') !== FALSE ? trim(trim($arr_benchmarks[$k],'0'), '.') : $arr_benchmarks[$k];
//					else $sum_data['benchmark'] = NULL;
//				}
				if($this->report->hasSumRow() && isset($sum[$k]) === true){
					if($first){
						$new_dataset[$k][] = 'Total';
					}
					else{
						$new_dataset[$k][] = $sum[$k];
					}
				}
				if($this->report->hasAvgRow() && isset($sum[$k]) === true){
					$tmp = $sum[$k] / $count[$k];
//					if(isset($this->arr_decimal_points[$k])){
//						$tmp = round($tmp, $this->arr_decimal_points[$k]);
//					}
					if($first){
						$new_dataset[$k][] = 'Average';
					}
					else{
						$new_dataset[$k][] = $tmp;
					}
				}
			}
			$first = false;
		}
		return $new_dataset;
	}

	/*
	* addAggregateRows
	* 
	* @param array dataset
	* @return array modified resultset
	* @author ctranel
	*/
	public function addAggregateRows($arr_dataset){
		$sum = [];
		$avg = [];
		$count = [];
		$data_type = [];
	
		if(!isset($arr_dataset) || empty($arr_dataset)){
			return false;
		}
		
		$first_col_key = key($arr_dataset[0]);
		$last_rows_index = count($arr_dataset) - 1;
		foreach($arr_dataset as $k => $row){
			foreach($row as $name => $val){
				if(strpos($name, 'isnull') === false && is_numeric($val)) {
					if(isset($sum[$name]) === false && $val !== null){
                        $data_type[$name] = $this->report->getFieldDataType($name);
						$sum[$name] = 0;
						$count[$name] = 0;
					}
						
					if($val !== NULL && (!$this->report->hasBenchmark() || $k < $last_rows_index)){
						$sum[$name] += $val;
						$count[$name]++;
					}
				}
				elseif(!is_numeric($val) && !isset($count[$name])){
					$sum[$name] = null;
					$count[$name] = null;
				}
			}
		}

		if($this->report->hasCntRow() && count($count) > 1){
			$count[$first_col_key] = 'CNT';
			$arr_dataset[] = $count;
		}
		if($this->report->hasSumRow() && count($sum) > 1){
			$sum[$first_col_key] = 'SUM';
			$arr_dataset[] = $sum;
		}
		if($this->report->hasAvgRow() && count($sum) > 1){
			foreach($sum as $k => $v){
			    //prevent averaging of inappropriate data_types
			    if (isset($data_type[$k])) {
                //if ((strpos($data_type[$k],'int') !== false) || (strpos($data_type[$k], 'numeric') !== false)|| (strpos($data_type[$k], 'decimal') !== false) || (strpos($data_type[$k], 'money') !== false)) {
			        //prevent division by zero
			        if ($count[$k] > 0){
			            $avg[$k] = round(($sum[$k] / $count[$k]), $this->report->getFieldDecimalScaleByName($k));
                    }
			    }  
			    else {
			        $avg[$k] = null;
			    }
			}
			$avg[$first_col_key] = 'AVG';
			$arr_dataset[] = $avg;
		}
		return $arr_dataset;
	}
	
}
?>
