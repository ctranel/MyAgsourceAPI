<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* -----------------------------------------------------------------
 *  Keto model
 *
 *  Handles dynamic supplemental information for the KetoMonitor report
 *
 *  @category: 
 *  @package: 
 *  @author: kmarshall
 *  @date: 20150112
 *  @version: 1.0
 * -----------------------------------------------------------------
 */
require_once APPPATH . 'models/report_model.php';

 class Keto_model extends Report_model{
	
	/**
	 * __construct
	 *
	 * @return void
	 * @author kmarshall
	 **/
	public function __construct($section_path){
		parent::__construct($section_path);
	}

	
	
	function getFreshCowCount($herd_code) {
		$statement = 'Cows fresh &ge;5 days since last test: ';
		$resultset = $this->db
			->select('count_fresh')
			->where('herd_code', $herd_code)
			->get('vma.dbo.vma_Keto_Summary_Aggregates')
			->result_array();
		if(isset($resultset[0]) && !empty($resultset[0])){
			return $statement.$resultset[0]['count_fresh'];
		}
		return 'Count of fresh cows not found';
	}
 	
	function getTestedCowCount($herd_code) {
		$statement = '% of fresh cows were tested 5 - 20 DIM';
		$resultset = $this->db
			->select('dim_5_20_pct')
			->where('herd_code', $herd_code)
			->get('vma.dbo.vma_Keto_Summary_Aggregates')
			->result_array();
		if(isset($resultset[0]) && !empty($resultset[0])) {
			return $resultset[0]['dim_5_20_pct'].$statement;
		}
		return 'Count of tested cows not found';
	}

	function getTestedCowCountEarly($herd_code) {
		$statement = '% of fresh cows were tested 5 - 11 DIM';
		$resultset = $this->db
		->select('dim_5_11_pct')
		->where('herd_code', $herd_code)
		->get('vma.dbo.vma_Keto_Summary_Aggregates')
		->result_array();
		if(isset($resultset[0]) && !empty($resultset[0])) {
			return $resultset[0]['dim_5_11_pct'].$statement;
		}
		return 'Count of cows tested early not found';
	}

	function getSummaryCategory($herd_code) {
		$statement = 'Results determined using ';
		$statement2 = ' Test Day';
		$resultset = $this->db
		->select('summary_category')
		->where('herd_code', $herd_code)
		->get('vma.dbo.vma_Keto_Summary_Aggregates')
		->result_array();
		if(isset($resultset[0]) && !empty($resultset[0])) {
			if ($resultset[0]['summary_category'] > 1) $statement2.= 's';
			return $statement.$resultset[0]['summary_category'].$statement2;
		}
		return 'Test day composite category not found';

	}

	function getTestDate($herd_code) {
		$statement = 'Test date for KetoMonitor values was ';
		$resultset = $this->db
		->select('test_date')
		->where('herd_code', $herd_code)
		->get('vma.dbo.vma_Keto_Summary_Aggregates')
		->result_array();
		if(isset($resultset[0]) && !empty($resultset[0])) {
			return $statement.$resultset[0]['test_date'];
		}
		return 'KetoMonitor test date not found.';
	}
	
	
	function getKetoPageTip($herd_code) {
		$page_tip = array();
		$page_tip['test_date'] = $this->getTestDate($herd_code);
		$page_tip['fresh'] = $this->getFreshCowCount($herd_code);
		$page_tip['tested'] = $this->getTestedCowCount($herd_code);
		$page_tip['testedearly'] = $this->getTestedCowCountEarly($herd_code);
		$page_tip['numbertests'] = $this->getSummaryCategory($herd_code);
		return $page_tip;
	}
	
 }