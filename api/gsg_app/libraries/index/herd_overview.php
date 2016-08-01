<?php
namespace myagsource\Page\Content\Table;

require_once APPPATH . 'libraries/Page/Content/Table/TableData.php';

use \myagsource\Datasource\DbObjects\DbTable;
use \myagsource\Benchmarks\Benchmarks;

class Herd_overview extends TableData {
	protected $arr_high_is_bad = ['l1_avg_linear_score', 'l4_avg_linear_score', 'wtd_avg_scc',
			'l0_new_infection_pct', 'chronic_cases_pct', 'l1_1st_new_infection_pct', 'pregnancy_loss_pct',
			'l0_exit_herd_pct', 'l0_left_60_dim_pct'
	];
	
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
	public function pivot($arr_dataset){
		$new_dataset = parent::pivot($arr_dataset);
		if(isset($new_dataset) && is_array($new_dataset)){
			$first = true;
			foreach($new_dataset as $k => $r){
				$trend = '';
				if(isset($r[0]) && isset($r[1])){
					$trend = $this->get_trend_symbol($r[0], $r[1], in_array($k, $this->arr_high_is_bad));
				}
				if($first){
					$trend = 'Trend';
					$first = false;
				}
				$new_dataset[$k] = 	array_slice($r, 0, 1, true) + ['Trend' => $trend] + array_slice($r, 1, null, true);
			}
		}
		return $new_dataset;
	}
	
	function get_trend_symbol($curr, $prev, $high_is_bad){
		if($prev == $curr || !isset($prev)) return '';
		if(($prev < $curr && !$high_is_bad) || ($prev > $curr && $high_is_bad)) return '+';
		else return '-';
	}
}