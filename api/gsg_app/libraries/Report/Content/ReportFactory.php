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
use \myagsource\dhi\Herd;

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
     * herd
     * @var Herd
     **/
    protected $herd;

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
        DbTableFactory $db_table_factory,
		Herd $herd
    ) {
		$this->datasource_reports = $datasource_reports;
		$this->datasource_dbfield = $datasource_dbfield;
		$this->supplemental_factory = $supplemental_factory;
        $this->filters = $filters;
        $this->data_handler = $data_handler;
        $this->db_table_factory = $db_table_factory;
        $this->herd = $herd;
	}


    /*
     * getByBlock
     *
     * @param int block_id
     * @param array key=>value new filter data
     * @author ctranel
     * @returns Report
*/
    public function getByBlock($block_id, $filter_data = null){
        //$criteria = ['b.id' => $block_id];
        if(isset($filter_data) && !empty($filter_data)){
			$this->filters->resetFilters($filter_data);
		}
		$results = $this->datasource_reports->getBlock($block_id);
        if(empty($results)){
            return [];
        }
        $r = $results[0];
        $report = $this->dataToObject($r);

        return $report;
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
		
		$results = $this->datasource_reports->getByPage($page_id);
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
	public function blockFromData($data){
		return $this->dataToObject($data);
	}
*/

	/*
	 * dataToObject
	 * 
	 * @param array result set row
	 * @author ctranel
	 * @returns \myagsource\Report\iReport
	 */
	protected function dataToObject($report_meta){
		$field_groups = $this->datasource_reports->getFieldGroupData($report_meta['id']);
		$field_groups = $this->keyFieldGroupData($field_groups);
		$pivot_field = null;

		if(isset($report_meta['pivot_db_field']) && !empty($report_meta['pivot_db_field'])){
			$p = $this->datasource_dbfield->getFieldData($report_meta['pivot_db_field']);
			$data_conversion = null;
			if(isset($p['conversion_name'])) {
				$data_conversion = new DataConversion($p['conversion_name'], $p['metric_label'], $p['metric_abbrev'],
					$p['to_metric_factor'], $p['metric_rounding_precision'], $p['imperial_label'], $p['imperial_abbrev'], $p['to_imperial_factor'], $p['imperial_rounding_precision']);
			}
			$pivot_field = new DbField($p['id'], $p['db_table'], $p['db_field_name'], $p['name'], $p['description'], $p['pdf_width'], $p['default_sort_order'], $p['datatype'], $p['max_length'], $p['decimal_scale'], $p['unit_of_measure'], $p['is_timespan'], $p['is_foreign_key'], $p['is_nullable'], $p['is_natural_sort'], $data_conversion);
		}

        $report_meta['is_metric'] = $this->herd->isMetric();
        $report_meta['field_data'] = $this->datasource_reports->getFieldData($report_meta['id'], $report_meta['is_metric']);

        $sort_data = $this->datasource_reports->getSortData($report_meta['id']);
        $sorts = $this->getDefaultSort($sort_data);

        $report_meta['primary_table_name'] = $this->getPrimaryDBTableName($report_meta['field_data']);
        $report_meta['where_groups'] = $this->getWhereGroups($report_meta['id'], $report_meta['primary_table_name'], $sorts, $report_meta['max_rows'], $report_meta['is_metric']);

		if($report_meta['display_type'] === 'table'){
			$report = new Table($this->datasource_reports, $report_meta, $this->filters, $sorts, $this->supplemental_factory, $this->data_handler, $this->db_table_factory, $pivot_field, $field_groups);
		}
		else{
			$report = new Chart($this->datasource_reports, $report_meta, $this->filters, $sorts, $this->supplemental_factory, $this->data_handler, $this->db_table_factory, $pivot_field, $field_groups, (bool)$report_meta['keep_nulls']);
		}
		return $report;
	}

    /**
     * @method getDefaultSort()
     * @return array of Sort objects
     * @author ctranel
     **/
    protected function getDefaultSort($sort_data){
        $default_sorts = [];
        if(is_array($sort_data)){
            foreach($sort_data as $s){
                $datafield = new DbField($s['db_field_id'], $s['table_name'], $s['db_field_name'], $s['name'], $s['description'], $s['pdf_width'], $s['default_sort_order'],
                    $s['datatype'], $s['max_length'], $s['decimal_scale'], $s['unit_of_measure'], $s['is_timespan'], $s['is_foreign_key'], $s['is_nullable'], $s['is_natural_sort'], null);
                $default_sorts[] = new Sort($datafield, $s['sort_order']);
            }
        }
        return $default_sorts;
    }

    /**
     * getSortDateFieldName()
     *
     * returns field-name of date sort if it exists
     *
     * @return string field name
     * @access public
     * */
    public function getSortDateFieldName($sorts){
        if(isset($sorts) && count($sorts) > 0){
            foreach($sorts as $s){
                if($s->isDate()){
                    return $s->fieldName();
                }
            }
        }
        return null;
    }


    /**
     * getWhereGroups()
     *
     * @return void
     * @author ctranel
     **/
    protected function getWhereGroups($report_id, $primary_table_name, $sorts, $max_rows, $is_metric){
        $data = $this->datasource_reports->getWhereData($report_id, $is_metric);

        //dates are usually displayed ascending, so simply getting the top (x) rows will not have current data.
        //need to get the range for which we display data when the sort is a date and include them in where groups
        if(isset($max_rows) && $max_rows > 0){
            $column_name = $this->getSortDateFieldName($sorts);

            if(isset($column_name)){
                //if there is no group, add one
                if((!is_array($data) || empty($data))){
                    $data[] = [
                        'name' => null,
                        'description' => null,
                        'unit_of_measure' => null,
                        'db_field_id' => null,
                        'table_name' => null,
                        'db_field_name' => null,
                        'pdf_width' => null,
                        'default_sort_order' => null,
                        'datatype' => null,
                        'is_timespan' => 0,
                        'is_natural_sort' => 0,
                        'is_foreign_key' => 0,
                        'is_nullable' => 0,
                        'decimal_scale' => null,
                        'max_length' => null,
                        'id' => 1,
                        'parent_id' => 0,
                        'condition_id' => null,
                        'group_operator' => 'and',
                        'operator' => 'null',
                        'operand' => null,
                    ];
                }

                $data[] = [
                    'name' => null,
                    'description' => null,
                    'unit_of_measure' => null,
                    'db_field_id' => null,
                    'table_name' => null,
                    'db_field_name' => $column_name,
                    'pdf_width' => null,
                    'default_sort_order' => null,
                    'datatype' => null,
                    'is_timespan' => 0,
                    'is_natural_sort' => 0,
                    'is_foreign_key' => 0,
                    'is_nullable' => 0,
                    'decimal_scale' => null,
                    'max_length' => null,
                    'id' => null,
                    'parent_id' => $data[0]['id'],
                    'condition_id' => 99,
                    'group_operator' => 'and',
                    'operator' => 'between',
                    'operand' => $this->herd->getDateRangeStart($primary_table_name, $column_name, $max_rows) . '|' . $this->herd->getRecentTest(),
                ];
            }
        }

        if(!is_array($data) || empty($data)){
            return;
        }

        return $this->buildWhereTree($data);
    }
    /**
     * buildWhereTree()
     *
     * Recursive function that returns children (where conditions and group (groups are recursive))
     *
     * @param array of data
     * @param int parent_id
     * @param string parent_operator
     * @return array of tree branch
     * @author ctranel
     **/
    protected function buildWhereTree(array $data, $parent_id = 0, $parent_operator = 'and'){
        if(!isset($data) || !is_array($data)){
            return;
        }

        $criteria = [];
        $children = [];
        foreach ($data as $k=>$s) {
            if ($s['parent_id'] == $parent_id) {
                $newdata = $data;
                unset($newdata[$k]);

                $data_conversion = null;

                if(isset($s['condition_id'])) {
                    if (isset($s['conversion_name'])) {
                        $data_conversion = new DataConversion($s['conversion_name'], $s['metric_label'],
                            $s['metric_abbrev'],
                            $s['to_metric_factor'], $s['metric_rounding_precision'], $s['imperial_label'],
                            $s['imperial_abbrev'], $s['to_imperial_factor'], $s['imperial_rounding_precision']);
                    }
                    $criteria_datafield = new DbField($s['db_field_id'], $s['table_name'], $s['db_field_name'],
                        $s['name'], $s['description'], $s['pdf_width'], $s['default_sort_order'],
                        $s['datatype'], $s['max_length'], $s['decimal_scale'], $s['unit_of_measure'], $s['is_timespan'],
                        $s['is_foreign_key'], $s['is_nullable'], $s['is_natural_sort'], $data_conversion);
                    $criteria[] = new WhereCriteria($criteria_datafield, $s['operator'], $s['operand']);
                }
                else{
                    $children[] = $this->buildWhereTree($newdata, $s['id'], $s['group_operator']);
                }
            }
        }
        if(count($criteria) > 0 || count($children) > 0){
            return new WhereGroup($parent_operator, $criteria, $children);
        }
    }

    /**
     * @method getPrimaryDBTableName()
     * @param
     * @return string table name
     * @author ctranel
     **/
    protected function getPrimaryDBTableName($report_fields){
        $max_cnt = 0;
        $max_table = null;
        $db_tables = [];

        if(is_array($report_fields) && count($report_fields) >  1){
            foreach($report_fields as $f){
                $tbl = $f['table_name'];

                if(isset($db_tables[$tbl])){
                    $db_tables[$tbl]['cnt']++;
                }
                else{
                    $db_tables[$tbl]['cnt'] = 1;
                }

                if($db_tables[$tbl]['cnt'] > $max_cnt){
                    $max_cnt = $db_tables[$tbl]['cnt'];
                    $max_table = $tbl;
                }
            }
            return $max_table;
        }
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
