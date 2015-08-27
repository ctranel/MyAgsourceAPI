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

 class Fresh_cow_summary_model extends CI_Model{
	
	/**
	 * __construct
	 *
	 * @return void
	 * @author kmarshall
	 **/
	public function __construct(){
		parent::__construct();
	}

	function getFCPageTip($herd_code) {
		$page_tip = array();
		$statement = 'Cow Populations and Number of Sold or Died events tables report actual numbers by month.  All other results determined using ';
		$statement2 = ' test day';
		$resultset = $this->db
		->select('herd_size_code')
		->where('herd_code', $herd_code)
		->get('vma.dbo.vma_Fresh_Cow_Num_Tests')
		->result_array();
		if(isset($resultset[0]) && !empty($resultset[0])) {
			if ($resultset[0]['herd_size_code'] > 1) $statement2.= 's';
			$statement2.= '.  Sold counts do not include animals sold for dairy.';
			$page_tip['numbertests'] = $statement.$resultset[0]['herd_size_code'].$statement2;
		}
		else {
			$page_tip['numbertests'] = 'Number of tests in composite not found';
		}
		return $page_tip;
		 
	}
}