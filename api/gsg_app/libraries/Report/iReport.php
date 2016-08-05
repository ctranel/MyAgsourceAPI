<?php

namespace myagsource\Report;


/**
 *
 * @author ctranel
 *        
 */
interface iReport {
	public function id();
	//public function name();
	//public function description();
	//public function path();
	//public function displayType();
	//public function title();

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