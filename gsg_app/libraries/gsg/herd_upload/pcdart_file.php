<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'libraries/gsg/herd_upload/herd_file.php';

class Pcdart_file extends Herd_file {
	function __construct($params){
		parent::__construct();
		if($params['herd_member_code'] == 'M'){
			$this->arr_file_fields = array(
				'barn_name',
				'control_num',
				'cow_id',
				'rf_id_usain',
				'lact_num',
				'curr_lact_dim',
				'net_merit_amt',
				'calf_due_date',
				'repro_code',
				'pta_milk',
				'pta_fat',
				'pta_pro',
				'curr_lact_linear_score', //linear score?
				'sire_naab',
				'mgr_sire_naab',
				'dam_name',
				'dam_pta_milk',
				'dam_pta_fat',
				'dam_pta_pro',
				'dam_net_merit_amt',
				'sire_net_merit_amt'
			);
		}
		elseif($params['herd_member_code'] == 'C'){
			$this->arr_file_fields = array(
				'barn_name',
				'control_num',
				'cow_id',
				'rf_id_usain',
				'age_in_months',  //add age_in_months to DB, also split to yrs - mos
				'est_net_merit_amt',
				'calf_due_date',
				'sire_naab',
				'mgr_sire_naab',
				'dam_name',
				'dam_pta_milk',
				'dam_pta_fat',
				'dam_pta_pro',
				'dam_net_merit_amt',
				'sire_net_merit_amt'
			);
		}
		$arr_notnull_fields = array("herd_code", "serial_num", "age_years", "age_months");
	    $arr_numeric_fields = array("serial_num","pstring", "control_num", "age_years", "age_months", 'age_in_months', "lact_num", "curr_lact_dim",
	    	"dam_lact_num", "net_merit_amt", "est_net_merit_amt", "decision_guide_qtile_num", 'curr_lact_linear_score',
        	"me_avg_lbs_dev_milk", "me_avg_lbs_dev_fat", "me_avg_lbs_dev_pro", "avg_linear_score", "avg_days_open", "avg_transition_cow_index",
	    	"dam_me_avg_lbs_dev_milk", "dam_me_avg_lbs_dev_fat", "dam_me_avg_lbs_dev_pro", "dam_avg_linear_score", "dam_avg_days_open",
	    	"dam_avg_transition_cow_index",'pta_milk','pta_fat','pta_pro','dam_pta_milk','dam_pta_fat','dam_pta_pro','dam_net_merit_amt','sire_net_merit_amt');
	}
}
