<?php
namespace myagsource\settings;
require_once APPPATH . 'libraries' . FS_SEP . 'settings' . FS_SEP . 'Session_settings.php';

use myagsource\settings\Session_settings;

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

class Benchmarks_lib extends Session_settings
{
	/**
	 * table object used in benchmarks
	 * @var object db_table
	 **/
	protected $db_table;

	/**
	 * table that stores most recent data for all herd/pstring data for deriving benchmark groups
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
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct($user_id, $herd_code, $herd_info, $setting_model, $session_values = NULL)
	{
		parent::__construct($user_id, $herd_code, $setting_model, 'benchmarks', $session_values);
		
		$this->arr_herd_size_groups = array(
			1 => array('floor' => 1, 'ceiling' => 124),
			2 => array('floor' => 125, 'ceiling' => 500),
			3 => array('floor' => 501, 'ceiling' => 2000),
			4 => array('floor' => 2001, 'ceiling' => 100000),
		);

		/**
		 * @todo : need a more elegent/flexible/reliable (using DB) way to set a multidimensional setting
		 */
		$this->arr_criteria_table = array(
			'PROD' => array(
				'field' => 'rha_milk_lbs',
				'sort_order' => 'desc',
			),
			'GEN' => array(
				'field' => 'net_merit_amt',
				'sort_order' => 'desc',
			),
			'REPRO' => array(
				'field' => 'pregnancy_rate_pct',
				'sort_order' => 'desc',
			),
			'UH' => array(
				'field' => 'wtd_avg_scc',
				'sort_order' => 'asc',
			),
		);
		
		$this->setHerdDefaults($herd_info['breed_code'], $herd_info['herd_size']);
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
		//the breed setting is an array
		$this->arr_settings['breed']->setDefaultValue(array($breed));
		
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
		//We can set this, but it will currently only be used for Holstein herds
		if($herd_size){
			foreach($this->arr_herd_size_groups as $k=>$v){
				if($v['floor'] <= $herd_size && $v['ceiling'] >= $herd_size){
					$herd_size = $v['floor'] . '|' . $v['ceiling'];
				}
			}
			return $herd_size;
		}
		else return NULL;
	}
	
	/**
	 * gets text description of benchmarks being used
	 *
	 * @return string
	 * @author ctranel
	 **/
	public function get_bench_text($sess_benchmarks){
		if(!isset($sess_benchmarks) || $sess_benchmarks === FALSE){
			return "Benchmark session not set";
		}
		$bench_text = 'Benchmark herds determined by ' . $this->arr_settings['criteria']->getDisplayText();
		$bench_text .= ' for ' . $this->arr_settings['breed']->getDisplayText();
		$bench_text .= ' herds ' . $this->arr_settings['herd_size']->getDisplayText() . ' animals';
		return $bench_text;
	}
	
	/**
	 * @method addBenchmarkRow
	 * @description retrieves row(s) of benchmark data into an array
	 * @param object database table
	 * @param array session benchmarks
	 * @param object benchmark model
	 * @param string row_head_field - the db field name of the column into which benchmark header text is inserted
	 * @param array of strings db field names to exclude
	 * @param array of strings arr_group_by (db field names)
	 * @return array
	 * @author ctranel
	 **/
	function addBenchmarkRow($db_table, &$benchmark_model, $row_head_field, $arr_fields_to_exclude = array('herd_code', 'pstring', 'lact_group_code', 'ls_type_code', 'sol_group_code'), $arr_group_by){
		if(isset($db_table)){
			$this->db_table = $db_table;
		}
		
		$bench_settings = $this->getSettingKeyValues();

		$avg_fields = $benchmark_model->get_benchmark_fields($this->db_table->full_table_name(), $arr_fields_to_exclude);
		list($herd_size_floor, $herd_size_ceiling) = explode('|', $this->arr_settings['herd_size']->getCurrValue());
		$bench_sql = $benchmark_model->build_benchmark_query(
			$this->db_table,
			$avg_fields,
			$this->arr_criteria_table[$this->arr_settings['criteria']->getCurrValue()],
			$this->herd_benchmark_pool_table,
			$this->arr_settings['metric']->getCurrValue(),
			$herd_size_floor,
			$herd_size_ceiling,
			$this->arr_settings['breed']->getCurrValue(),
			$arr_group_by
		);
		$arr_benchmarks = $benchmark_model->getBenchmarkData($bench_sql);

		//$this->arr_settings['metric']->getCurrValue() in place of $sess_benchmarks['metric']?
		$tmp_metric = $this->arr_settings['metric']->getLookupOptions();
		$bench_head_text = ucwords(strtolower($tmp_metric[$this->arr_settings['metric']->getCurrValue()])) . ' (n=' . $arr_benchmarks[0]['cnt_herds'] . ')';

		foreach($arr_benchmarks as &$b){
			unset($b['cnt_herds']);
			if(isset($b['pstring'])){
				$b['pstring'] = '';
			}
			$b = array($row_head_field => $bench_head_text) + $b;
		}
		return $arr_benchmarks;
	}
}