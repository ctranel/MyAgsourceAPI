<?php
require_once APPPATH . 'models/report_model.php';
class Herd_overview_model extends Report_model {
	protected $arr_high_is_bad = array('l1_avg_linear_score', 'l4_avg_linear_score', 'wtd_avg_scc', 
			'l0_new_infection_pct', 'chronic_cases_pct', 'l1_1st_new_infection_pct', 'pregnancy_loss_pct', 
			'l0_exit_herd_pct', 'l0_left_60_dim_pct'
	);
	
	public function __construct($section_path = NULL)
	{
		parent::__construct($section_path);
	}
	
	/*  
	 * @method pivot() overrides report_model
	 * @param array dataset
	 * @param string header field
	 * @param int pdf with of header field
	 * @param bool add average column
	 * @param bool add sum column
	 * @return array pivoted resultset
	 * @author ctranel
	 */
	public function pivot($arr_dataset, $header_field, $header_field_width, $label_column_width, $bool_avg_column = FALSE, $bool_sum_column = FALSE, $bool_bench_column = FALSE){
		$new_dataset = parent::pivot($arr_dataset, $header_field, $header_field_width, $label_column_width, $bool_avg_column, $bool_sum_column, $bool_bench_column);
		$this->arr_unsortable_columns[] = 'Trend';
		$this->arr_fields = array_slice($this->arr_fields, 0, 2, true) +
							array('Trend' => 'Trend') +
							array_slice($this->arr_fields, 2, null, true);
		if(isset($new_dataset) && is_array($new_dataset)){
			foreach($new_dataset as $k => $r){
				$trend = $this->get_trend_symbol($r['Prev Test'], $r['Curr Test'], in_array($k, $this->arr_high_is_bad));
				$new_dataset[$k] = 	array_slice($r, 0, 3, true) +
									array('Trend' => $trend) +
									array_slice($r, 3, null, true);
			}
		}
		//add trend column in new dataset
		$this->arr_db_field_list = $this->arr_fields;
		return $new_dataset;
	}

	function get_trend_symbol($prev, $curr, $high_is_bad){
		if($prev == $curr || !isset($prev)) return '';
		if(($prev < $curr && !$high_is_bad) || ($prev > $curr && $high_is_bad)) return '+';
		else return '-';
	}
}
