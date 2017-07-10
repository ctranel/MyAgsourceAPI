<?php

namespace myagsource\Report\Content\Table;

//require_once APPPATH . 'libraries/Datasource/DbObjects/DbField.php';
require_once APPPATH . 'libraries/Report/Content/Table/TableField.php';
require_once APPPATH . 'libraries/Report/Content/Report.php';
require_once(APPPATH . 'libraries/Report/Content/Table/Header/TableHeader.php');

use \myagsource\Datasource\DbObjects\DbTableFactory;
use \myagsource\DataHandler;
use \myagsource\Datasource\iDataField;
use \myagsource\Datasource\DbObjects\DbField;
use \myagsource\Report\Content\Table\TableField;
use \myagsource\Report\Content\Report;
use \myagsource\Filters\ReportFilters;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\Report\Content\Table\Header\TableHeader;
use \myagsource\Datasource\DbObjects\DataConversion;

/**
 * Name:  Table
 *
 * Author: ctranel
 *
 * Created:  02-03-2015
 *
 * Description:  Contains properties and methods specific to displaying tables on the website.
 *
 */
class Table extends Report {
	/**
	 * table_header
	 * @var TableHeader
	 **/
	protected $table_header;
	
	/**
	 * top_row
	 * @var array
	 **/
	protected $top_row;
	
	function __construct($table_datasource, $report_meta, ReportFilters $filters, SupplementalFactory $supp_factory = null, DataHandler $data_handler, DbTableFactory $db_table_factory, iDataField $pivot_field = null, $field_groups, $is_metric) {
		parent::__construct($table_datasource, $report_meta, $filters, $supp_factory, $data_handler, $db_table_factory, $pivot_field, $field_groups, $is_metric);

        if(!isset($this->report_fields) || empty($this->report_fields)){
            return;
        }

        $this->setDataset($report_meta['path']);
        $this->setTableHeader();
	}
	
	/**
	 * setTableHeader
	 * 
	 * @param int report count
	 * @return array of output data for table
	 * @access public
	 *
	 **/
	public function setTableHeader(){
        //new for API refactoring
        $header_groups = $this->datasource->getHeaderGroups($this->id());

        //@todo: pull this only when needed? move adjustHeaderGroups to Table or TableHeader class
        $arr_dates = null;//$this->herd_model->get_test_dates_7_short($this->session->userdata('herd_code'));
        $header_groups = TableHeader::mergeDateIntoHeader($header_groups, $arr_dates);
        //end new for API refactoring

        $this->table_header = new TableHeader($this, $header_groups, $this->supplemental_factory);
		
		$top_row = null;

///var_dump($this->getFieldFormatByName($this->pivot_field->dbFieldName()));

		if($this->hasPivot() && is_array($this->dataset) && !empty($this->dataset)){
			reset($this->dataset);
			$tmp_key = key($this->dataset);
			//add placeholder for column generated from header row
			$this->top_row = $this->dataset[$tmp_key];
            ksort($this->top_row);
			unset($this->dataset[$tmp_key]);
		}
	}

	/**
	 * setFlatTableHeader
	 *
	 * @param int report count
	 * @return void
	 * @access public
	 *
	 **/
	public function setFlatTableHeader(&$report_data, $header_groups){
		$this->table_header = new TableHeader($this, $header_groups, $this->supplemental_factory);

		$top_row = null;
		if($this->hasPivot() && is_array($report_data) && !empty($report_data)){
			reset($report_data);
			$tmp_key = key($report_data);
			//add placeholder for column generated from header row
			$this->top_row = array_merge([''],$report_data[$tmp_key]);
			unset($report_data[$tmp_key]);
		}
	}

	/**
	 * getTableHeaderData
	 * 
	 * @param int report count
	 * @return array of table header data
	 * @access public
	 *
	 **/
	public function getTableHeaderData($report_count){
        $header_data_format = $this->getFieldFormatByName($this->pivotFieldName());

        return [
			'structure' => $this->table_header->getTableHeaderStructure($this->top_row, $header_data_format),
			'block' => $this,
			'report_count' => $report_count
		];
		
	}

	/**
	 * getTableHeaderCsv
	 * 
	 * @return array of leaves from table header tree
	 * @access public
	 *
	 **/
	public function getTableHeaderLeafs(){
        $header_data_format = $this->getFieldFormatByName($this->pivotFieldName());
		$header_leafs = $this->table_header->getHeaderLeafs($this->top_row, $header_data_format);
		return $header_leafs;
	}

	/**
	 * getOutputData
	 * 
	 * overrides parent function
	 * 
	 * @return array of output data for table
	 * @access public
	 *
	 **/
	public function getOutputData(){
		$return_val = parent::getOutputData();
		$return_val['num_columns'] = $this->table_header->columnCount();
		return $return_val;
	}

    /**
     * toArray
     *
     * @return array representation of object
     *
     **/
    public function toArray(){
        $ret = parent::toArray();
        $header_data_format = $this->getFieldFormatByName($this->pivotFieldName());

        if(isset($this->table_header)){
            $this->table_header->getTableHeaderStructure($this->top_row, $header_data_format);
            $ret['table_header'] = $this->table_header->toArray();
        }
        if(is_array($this->report_fields) && !empty($this->report_fields)){
            $fields = [];
            foreach($this->report_fields as $k=>$f){
                $fields[$f->dbFieldName()] = $f->toArray();
            }
            //$ret['report_fields'] = $fields;
        }
        return $ret;
    }

	/**
	 * setReportFields
	 * 
	 * Sets the datafields property of datafields that are to be included in the table
	 * 
	 * @method setReportFields()
	 * @return void
	 * @access protected
	 **/
	protected function setReportFields(){
		$arr_res = $this->datasource->getFieldData($this->id, $this->is_metric);
		if(is_array($arr_res)){
			foreach($arr_res as $s){
                //aggregate
				if(isset($s['aggregate']) && !empty($s['aggregate'])){
					$this->has_aggregate = true;
				}

				$this->setSupplemental($s);

                //set report field
                $data_conversion = null;
                if(isset($s['conversion_name'])) {
                    $data_conversion = new DataConversion($s['conversion_name'], $s['metric_label'], $s['metric_abbrev'],
                        $s['to_metric_factor'], $s['metric_rounding_precision'], $s['imperial_label'], $s['imperial_abbrev'], $s['to_imperial_factor'], $s['imperial_rounding_precision']);
                }
                $datafield = new DbField($s['db_field_id'], $s['table_name'], $s['db_field_name'], $s['name'], $s['description'], $s['pdf_width'], $s['default_sort_order'],
                    $s['datatype'], $s['max_length'], $s['decimal_scale'], $s['unit_of_measure'], $s['is_timespan'], $s['is_foreign_key'], $s['is_nullable'], $s['is_natural_sort'], $data_conversion);
				$this->report_fields[] = new TableField(
				    $s['id'],
                    $s['name'],
                    $datafield,
                    $s['category_id'],
                    $s['is_displayed'],
                    $s['display_format'],
                    $s['aggregate'],
                    $s['is_sortable'],
                    isset($this->header_supplemental[$s['db_field_name']]) ? $this->header_supplemental[$s['db_field_name']] : null,
                    isset($this->dataset_supplemental[$s['db_field_name']]) ? $this->dataset_supplemental[$s['db_field_name']] : null,
                    $s['table_header_group_id'],
                    $s['field_group'],
                    $s['field_group_ref_key']
                );
			}
		}
	}
	
	public function getFieldDataType($field_name){
	    foreach ($this->report_fields as $k => $v) {
	        if($v->dbFieldName() == $field_name) {
	           return $v->getDataType();
	        }
	    }
	}
	
}
?>