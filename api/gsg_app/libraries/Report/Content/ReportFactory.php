<?php

namespace myagsource\Report\Content;

require_once(APPPATH . 'libraries/Report/Content/Table/Table.php');
require_once(APPPATH . 'libraries/Report/Content/Chart/Chart.php');
require_once(APPPATH . 'libraries/Report/iReport.php');
require_once(APPPATH . 'libraries/Datasource/DbObjects/DbTable.php');
require_once(APPPATH . 'libraries/Datasource/DbObjects/DbField.php');

use \myagsource\Report\Content\Table\Table;
use \myagsource\Report\Content\Chart\Chart;
use \myagsource\Filters\ReportFilters;
use \myagsource\Report\iReport;
use myagsource\Supplemental\Content\SupplementalFactory;
use myagsource\Datasource\DbObjects\DbField;
use \myagsource\Datasource\DbObjects\DbTableFactory;
use \myagsource\DataHandler;

/**
 * A repository? for report objects
 * 
 * 
 * @name ReportFactory
 * @author ctranel
 * 
 *        
 */
class ReportFactory {
	/**
	 * datasource_reports
	 * @var report_model
	 **/
	protected $datasource_reports;

    /**
     * filters
     * @var ReportFilters
     **/
    protected $filters;

    /**
	 * datasource_dbfield
	 * @var db_field_model
	 **/
	protected $datasource_dbfield;

	/**
	 * supplemental_factory
	 * @var SupplementalFactory
	 **/
	protected $supplemental_factory;

    /**
     * data_handler
     * @var DataHandler
     **/
    protected $data_handler;

    /**
     * db_table_factory
     * @var DbTableFactory
     **/
    protected $db_table_factory;

    function __construct(
		\report_block_model $datasource_reports,
        \db_field_model $datasource_dbfield, 
        ReportFilters $filters, 
        SupplementalFactory $supplemental_factory = null, 
        DataHandler $data_handler,
        DbTableFactory $db_table_factory
    ) {
		$this->datasource_reports = $datasource_reports;
		$this->datasource_dbfield = $datasource_dbfield;
		$this->supplemental_factory = $supplemental_factory;
        $this->filters = $filters;
        $this->data_handler = $data_handler;
        $this->db_table_factory = $db_table_factory;
	}

	/*
	 * getByPage
	 * 
	 * @param int page_id
	 * @author ctranel
	 * @returns array of Reports
*/
	public function getByPage($page_id){
		$reports = [];
		
		$criteria = ['page_id' => $page_id];
		$results = $this->datasource_reports->getByCriteria($criteria);
		if(empty($results)){
			return [];
		}
		foreach($results as $r){
			$reports[$r['list_order']] = $this->dataToObject($r);
		}
		return $reports;
	}

	/*
     * blockFromData
     *
     * @param associative array of data needed for block creation
     * @author ctranel
     * @returns Report
     */
	public function blockFromData($data){
		return $this->dataToObject($data);
	}

	/*
	 * dataToObject
	 * 
	 * @param array result set row
	 * @author ctranel
	 * @returns \myagsource\Report\iReport
	 */
	protected function dataToObject($report_data){
		$field_groups = $this->datasource_reports->getFieldGroupData($report_data['id']);
		$field_groups = $this->keyFieldGroupData($field_groups);


		if($report_data['display_type'] === 'table'){
			$report = new Table($this->datasource_reports, $report_data['id'], $report_data['path'], $report_data['max_rows'], $report_data['cnt_row'], $report_data['sum_row'], $report_data['avg_row'], $report_data['bench_row'], $report_data['is_summary'], $report_data['display_type'], $this->filters, $this->supplemental_factory, $this->data_handler, $this->db_table_factory, $field_groups);
			if(isset($report_data['pivot_db_field'])){
				$p = $this->datasource_dbfield->getFieldData($report_data['pivot_db_field']);
				$pivot_field = new DbField($p['id'], $p['db_table'], $p['db_field_name'], $p['name'], $p['description'], $p['pdf_width'], $p['default_sort_order'], $p['datatype'], $p['max_length'], $p['decimal_scale'], $p['unit_of_measure'], $p['is_timespan'], $p['is_foreign_key'], $p['is_nullable'], $p['is_natural_sort']);
				$report->setPivot($pivot_field);
			}
		}
		else{
			$report = new Chart($this->datasource_reports, $report_data['id'], $report_data['path'], $report_data['max_rows'], $report_data['cnt_row'], $report_data['sum_row'], $report_data['avg_row'], $report_data['bench_row'], $report_data['is_summary'], $report_data['display_type'], $report_data['chart_type'], $this->filters, $this->supplemental_factory, $this->data_handler, $this->db_table_factory, $field_groups, (bool)$report_data['keep_nulls']);
		}
		return $report;
	}
	
	
	//@TODO: WHERE SHOULD THIS GO??
	protected function keyFieldGroupData($field_groups){
		if(!isset($field_groups) || empty($field_groups)){
			return false;
		}
		
		$ret = [];
		foreach($field_groups as $fg){
			$fg_num = $fg['field_group_num'];
			unset($fg['field_group_num']);
			$ret[$fg_num] = $fg;
		}
		
		return $ret;
	}
}

?>
