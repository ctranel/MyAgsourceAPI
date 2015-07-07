<?php
namespace myagsource\Report\Content\Chart;

require_once APPPATH . 'libraries/Report/Content/Chart/ChartData.php';

class Report_card extends ChartData {
	/* boolean is the report historical (vs current test)
	 * 
	 */
	protected $is_historical;
	
	public function __construct(ChartBlock $block, \Report_data_model $report_datasource){
		parent::__construct($block, $report_datasource);
	}

	/**
	 * @method historical()
	 * @description sets object is_historical property value according to param value
	 * @param bool 
	 * @return void
	 * @author ctranel
	 **/
	public function historical($is_historical){
		$this->is_historical = $is_historical;
	}
	
	/**
	 * @method prepSelectFields()
	 * @param arr_fields: copy of fields array to be formatted into SQL
	 * @return array of sql-prepped select fields
	 * @author ctranel
	protected function prepSelectFields(){
		if(!$this->is_historical){
			foreach($this->arr_db_field_list as $sf){
				$this->block->addFieldName(str_replace('_pct', '', $sf));
				$this->block->addFieldName(str_replace('_pct', '_10_pct', $sf));
				$this->block->addFieldName(str_replace('_pct', '_50_pct', $sf));
				$this->block->addFieldName(str_replace('_pct', '_90_pct', $sf));
			}
		}
		return parent::prep_select_fields();
	}
	 **/
	
	/**
	 * @method setRowToSeries - used when each row from a set of database results corresponds with a series of data.
	 * @param array of field name base text (for percentages, add '_pct')
	 * @return array of data for the graph
	 * @access protected
	 *
	 **/
	protected function setRowToSerieszzz(){
		if($this->is_historical){
			return parent::setRowToSeries();
		}
		if(is_array($this->dataset) && !empty($this->dataset)){
			$arr_return = [];
			foreach($this->dataset as $k=>$row){
				$count = 0;
				foreach($this->block->getFieldlistArray() as $f){
					if(strpos($f, 'pct') === false){
						$f = str_replace('_pct', '', $f);
						//bar series
						$arr_return[0][$count] = array(
							'x' => $count,
							'y' => (float)$row[$f . '_pct'],
							'val' => (float)$row[$f],
						);
						//scatterplot point for each of the 3 benchmark breakpoints
						$arr_return[1][] = array(
							'x' => $count,
							'y' => 10,
							'val' => (float)$row[$f . '_10_pct'],
						);
						$arr_return[1][] = array(
							'x' => $count,
							'y' => 50,
							'val' => (float)$row[$f . '_50_pct'],
						);
						$arr_return[1][] = array(
							'x' => $count,
							'y' => 90,
							'val' => (float)$row[$f . '_90_pct'],
						);
						$count++;
					}
				}
			}
			return $arr_return;
		}
		else return FALSE;
	}
}