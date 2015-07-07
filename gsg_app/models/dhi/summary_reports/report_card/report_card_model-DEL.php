<?php
require_once APPPATH . 'models/report_model.php';
class Report_card_model extends Report_model {
	/* boolean is the report historical (vs current test)
	 * 
	 */
	protected $is_historical;
	
	public function __construct($section_path){
		parent::__construct($section_path);
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
	 * @method prep_select_fields()
	 * @param arr_fields: copy of fields array to be formatted into SQL
	 * @return array of sql-prepped select fields
	 * @author ctranel
	 **/
	protected function prep_select_fields(){
		if(!$this->is_historical){
			foreach($this->arr_db_field_list as $sf){
				$this->arr_db_field_list[] = str_replace('_pct', '', $sf);
				$this->arr_db_field_list[] = str_replace('_pct', '_10_pct', $sf);
				$this->arr_db_field_list[] = str_replace('_pct', '_50_pct', $sf);
				$this->arr_db_field_list[] = str_replace('_pct', '_90_pct', $sf);
			}
		}
		return parent::prep_select_fields();
	}
	
	/**
	 * @method setRowToSeries - used when each row from a set of database results corresponds with a series of data.
	 * @param array of field name base text (for percentages, add '_pct')
	 * @return array of data for the graph
	 * @access protected
	 *
	 **/
	protected function setRowToSeries($data, $arr_fieldname_base, $arr_categories){
		if($this->is_historical){
			return parent::setRowToSeries($data, $arr_fieldname_base, $arr_categories);
		}
		if(is_array($data) && !empty($data)){
			foreach($data as $k=>$row){
				$count = 0;
				foreach($arr_fieldname_base as $f){
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
			return $arr_return;
		}
		else return FALSE;
	}
}