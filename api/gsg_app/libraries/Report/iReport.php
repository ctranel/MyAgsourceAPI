<?php

namespace myagsource\Report;

use myagsource\Site\iBlockContent;


/**
 *
 * @author ctranel
 *        
 */
interface iReport extends iBlockContent {
	public function id();

	public function maxRows();
	public function pivotFieldName();
	public function primaryTableName();
	public function sorts();
	public function subtitle();
	public function hasBenchmark();
	public function isSummary();
	public function fieldGroups();
	public function hasCntRow();
	public function hasAvgRow();
	public function hasSumRow();
	public function hasPivot();
	public function reportFields();
	public function numFields();
	public function filterKeysValues();
	
	
//	public function reportFields();
//	public function setReportFields();

//	public function resetSort();
//	public function addSort(Sort $sort);
//	function addSortField(iDataField $datafield, $sort_order);
//	function joins();
//	public function setDefaultSort();
}

?>