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
     * getByBlock
     *
     * @param int block_id
     * @param array key=>value new filter data
	 * @param boolean is metric
     * @author ctranel
     * @returns Report
*/
    public function getByBlock($block_id, $filter_data = null, $is_metric){
        //$criteria = ['b.id' => $block_id];
        if(isset($filter_data) && !empty($filter_data)){
			$this->filters->resetFilters($filter_data);
		}
		$results = $this->datasource_reports->getBlock($block_id);
        if(empty($results)){
            return [];
        }
        $r = $results[0];
        $report = $this->dataToObject($r, $is_metric);

        return $report;
    }

    /*
     * getByPage
     *
     * @param int page_id
     * @author ctranel
     * @returns array of Reports
*/
	public function getByPage($page_id, $is_metric){
		$reports = [];
		
		$results = $this->datasource_reports->getByPage($page_id);
		if(empty($results)){
			return [];
		}
		foreach($results as $r){
			$reports[$r['list_order']] = $this->dataToObject($r, $is_metric);
		}
		return $reports;
	}

	/*
     * blockFromData
     *
     * @param associative array of data needed for block creation
	 * @param boolean is metric
     * @author ctranel
     * @returns Report
	public function blockFromData($data, $is_metric){
		return $this->dataToObject($data, $is_metric);
	}
*/

	/*
	 * dataToObject
	 * 
	 * @param array result set row
	 * @param boolean display as metric if data has a conversion set up
	 * @author ctranel
	 * @returns \myagsource\Report\iReport
	 */
	protected function dataToObject($report_meta, $is_metric){
		$field_groups = $this->datasource_reports->getFieldGroupData($report_meta['id']);
		$field_groups = $this->keyFieldGroupData($field_groups);

		$pivot_field = null;

		if(isset($report_meta['pivot_db_field']) && !empty($report_meta['pivot_db_field'])){
			$p = $this->datasource_dbfield->getFieldData($report_meta['pivot_db_field']);
			$pivot_field = new DbField($p['id'], $p['db_table'], $p['db_field_name'], $p['name'], $p['description'], $p['pdf_width'], $p['default_sort_order'], $p['datatype'], $p['max_length'], $p['decimal_scale'], $p['unit_of_measure'], $p['is_timespan'], $p['is_foreign_key'], $p['is_nullable'], $p['is_natural_sort']);
		}

		if($report_meta['display_type'] === 'table'){
			$report = new Table($this->datasource_reports, $report_meta, $this->filters, $this->supplemental_factory, $this->data_handler, $this->db_table_factory, $pivot_field, $field_groups, $is_metric);
		}
		else{
			$report = new Chart($this->datasource_reports, $report_meta, $this->filters, $this->supplemental_factory, $this->data_handler, $this->db_table_factory, $pivot_field, $field_groups, $is_metric, (bool)$report_meta['keep_nulls']);
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
