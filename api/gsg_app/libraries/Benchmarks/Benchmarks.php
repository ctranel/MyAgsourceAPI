<?php
namespace myagsource\Benchmarks;
require_once APPPATH . 'libraries/Settings/Settings.php';

use myagsource\Settings\Settings;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Benchmarks
*
* Author: ctranel
*  
* Created:  1-11-2013
*
* Description:  Works with the report model to set benchmark criteria, calculate benchmarks and return benchmark data.
*
* Requirements: PHP5 or above
*
*/

class Benchmarks extends Settings
{
	/**
	 * table object used in benchmarks
	 * @var object db_table
	 **/
	protected $db_table;

	/**
	 * benchmark datasource
	 * @var object benchmark_model
	 **/
	protected $benchmark_model;

	/**
	 * table that stores most recent data for all data for deriving benchmark groups
	 * @var string
	 **/
	protected $herd_benchmark_pool_table = 'herd.dbo.bench_criteria_summary'; //'vma.dbo.vma_bench_criteria_summary';

	/**
	 * array of herd size groups
	 * @var array
	 **/
	protected $arr_herd_size_groups;
	
	/**
	 * array of data related to criteria (join text, etc)
	 * @var array
	 **/
	protected $arr_criteria_table;
	
	/**
	 * session_data 
	 * @var array
	 **/
	protected $session_data;
	
	/**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct(\Benchmark_model $benchmark_model, $user_id, $herd_code, $herd_info, $session_benchmarks)
	{
		parent::__construct($user_id, $herd_code, $benchmark_model, $session_benchmarks); //83 is the id of benchmarks setting form page--need to retrive this based on settings name in controller

		$this->benchmark_model = $benchmark_model;
		$this->session_data = $session_benchmarks;
		
		$this->arr_herd_size_groups = [
			1 => ['floor' => 1, 'ceiling' => 124],
			2 => ['floor' => 125, 'ceiling' => 500],
			3 => ['floor' => 501, 'ceiling' => 2000],
			4 => ['floor' => 2001, 'ceiling' => 100000],
		];

		/**
		 * @todo : need a more elegent/flexible/reliable (using DB?) way to set a multidimensional setting
		 */
		$this->arr_criteria_table = [
			'PROD' => [
				'field' => 'rha_milk_lbs',
				'sort_order' => 'desc',
			],
			'GEN' => [
				'field' => 'net_merit_amt',
				'sort_order' => 'desc',
			],
			'REPRO' => [
				'field' => 'pregnancy_rate_pct',
				'sort_order' => 'desc',
			],
			'UH' => [
				'field' => 'wtd_avg_scc',
				'sort_order' => 'asc',
			],
		];
		
		//$this->setHerdDefaults($herd_info['breed_code'], $herd_info['herd_size']);
	}

	/**
	 * gets default criteria for benchmarks
	 *
	 * @param string breed
	 * @param int herd size
	 * @return void
	 * @author ctranel
	 **/
	protected function setHerdDefaults($breed, $herd_size = FALSE){
		if(!isset($this->arr_settings)){
			$this->loadSettings();
		}
		//OVERRIDE DEFAULTS
		//the breed setting is an array
		$this->arr_settings['breed']->setDefaultValue(explode('|',$breed));
		if($breed === 'HO' || $breed === 'JE'){
			$this->arr_settings['metric']->setDefaultValue('TOP20_PCT');
		}
		
		if($breed === 'HO'){
			$this->arr_settings['herd_size']->setDefaultValue($this->get_default_herd_size_range($herd_size));
		}
	}
	
	/**
	 * gets default get_default_herd_size_range for benchmarks based on herd size parameter
	 * 
	 * Currently used only for Holstein herds
	 *
	 * @param int herd size
	 * @return string pipe-delimited range
	 * @author ctranel
	 **/
	private function get_default_herd_size_range($herd_size){
		if(!isset($herd_size)){
			return NULL;
		}
		foreach($this->arr_herd_size_groups as $k=>$v){
			if($v['floor'] <= $herd_size && $v['ceiling'] >= $herd_size){
				$herd_size = array('dbfrom' => $v['floor'], 'dbto' => $v['ceiling']);
			}
		}
		return $herd_size;
	}
	
	/**
	 * gets text description of benchmarks being used
	 *
	 * @return string
	 * @author ctranel
	 **/
	public function get_bench_text(){
		if(!isset($this->session_data) || $this->session_data === FALSE){
			return "Benchmark session not set";
		}
		$bench_text = 'Benchmark herds determined by ' . $this->arr_settings['criteria']->getDisplayText($this->session_data['criteria']);
		$bench_text .= ' for ' . $this->arr_settings['breed']->getDisplayText($this->session_data['breed']);
		$bench_text .= ' herds ' . $this->arr_settings['herd_size']->getDisplayText($this->session_data['herd_size']) . ' animals';
		return $bench_text;
	}

	/* -----------------------------------------------------------------
	 *  parses form data according to data type conventions.
	
	*  Parses form data according to data type conventions.
	
	*  @since: version 1
	*  @author: ctranel
	*  @date: July 7, 2014
	*  @param array of key-value pairs from form submission
	*  @return void
	*  @throws:
	* -----------------------------------------------------------------
	public function parseFormData($form_data){
		if($form_data['breed'] !== 'HO'){
			$form_data['herd_size']['dbfrom'] = '1';
			$form_data['herd_size']['dbto'] = '100000';
		}
		if($form_data['breed'] !== 'HO' && $form_data['breed'] !== 'JE'){
			$form_data['metric'] = 'AVG';
		}
		return parent::parseFormData($form_data);
	}
*/

	/**
	 * @method addBenchmarkRow
	 * @description retrieves row(s) of benchmark data into an array
	 * @param object database table
	 * @param array session benchmarks
	 * @param string row_head_field - the db field name of the column into which benchmark header text is inserted
	 * @param array of strings db field names to exclude
	 * @param array of strings arr_group_by (db field names)
	 * @return array
	 * @author ctranel
	 **/
	function addBenchmarkRow($db_table, $row_head_field, $arr_fields_to_exclude = ['herd_code', 'pstring', 'lact_group_code', 'ls_type_code', 'sol_group_code'], $arr_group_by){
		if(isset($db_table)){
			$this->db_table = $db_table;
		}
		
		$bench_settings = $this->getSettingKeyValues($this->session_data);

		$avg_fields = $this->benchmark_model->get_benchmark_fields($this->db_table->full_table_name(), $arr_fields_to_exclude);
		//make sure we have something to pass for all session vars
		$sess_herd_size = isset($this->session_data['herd_size']) ? $this->session_data['herd_size'] : null;
		$sess_herd_size = $this->arr_settings['herd_size']->getCurrValue($sess_herd_size);
		$sess_criteria = isset($this->session_data['criteria']) ? $this->session_data['criteria'] : null;
		$sess_metric = isset($this->session_data['metric']) ? $this->session_data['metric'] : null;
		$sess_breed = isset($this->session_data['breed']) ? $this->session_data['breed'] : null;
		
		$bench_sql = $this->benchmark_model->build_benchmark_query(
			$this->db_table,
			$avg_fields,
			$this->arr_criteria_table[$this->arr_settings['criteria']->getCurrValue($sess_criteria)],
			$this->herd_benchmark_pool_table,
			$this->arr_settings['metric']->getCurrValue($sess_metric),
			$sess_herd_size['dbfrom'],
			$sess_herd_size['dbto'],
			$this->arr_settings['breed']->getCurrValue($sess_breed),
			$arr_group_by
		);
		$arr_benchmarks = $this->benchmark_model->getBenchmarkData($bench_sql);
		$bench_head_text = $this->arr_settings['metric']->getCurrValue($sess_metric);
		if($arr_benchmarks[0]['cnt_herds'] < 3){
			$bench_head_text .= '<br>(benchmarks not available)';
		}
		else{
			$bench_head_text .= ' (n=' . $arr_benchmarks[0]['cnt_herds'] . ')';
		}
		
		foreach($arr_benchmarks as &$b){
			$cnt = $b['cnt_herds'];
			unset($b['cnt_herds']);
			if($cnt < 3){
				$keys = array_keys($b);
				$b = array_fill_keys($keys, 'na');
			}
			$b = array($row_head_field => $bench_head_text) + $b;
		}

		return $arr_benchmarks;
	}
}