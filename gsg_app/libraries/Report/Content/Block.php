<?php
namespace myagsource\Report\Content;

require_once APPPATH . 'libraries/Report/iBlock.php';
require_once APPPATH . 'libraries/Report/Content/Sort.php';
require_once APPPATH . 'libraries/Datasource/DbObjects/DbField.php';
//require_once APPPATH . 'libraries/Site/iWebContentRepository.php';

use \myagsource\Report\iBlock;
use \myagsource\dhi\Herd;
use \myagsource\Report\Content\Sort;
use \myagsource\Datasource\DbObjects\DbField;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\Datasource\iDataField;
use \myagsource\report_filters\Filters;

/**
* Name:  Block
*
* Author: ctranel
*  
* Created:  02-02-2015
*
* Description:  Contains properties and methods specific to displaying blocks of the website.
*
*/

abstract class Block implements iBlock {
	/**
	 * block id
	 * @var int
	 **/
	protected $id;

	/**
	 * page_id
	 * @var int
	 **/
	protected $page_id;

	/**
	 * block name
	 * @var string
	 **/
	protected $name;
	
	/**
	 * block description
	 * @var string
	 **/
	protected $description;
	
	/**
	 * block path
	 * @var string
	 **/
	protected $path;
	
	/**
	 * collection of ReportField objects
	 * @var SplObjectStorage
	 **/
	protected $report_fields;
	
	/**
	 * collection of iDataField objects
	 * @var SplObjectStorage
	 **/
	protected $group_by;

	/**
	 * collection of WhereGroup objects
	 * @var SplObjectStorage
	 **/
	protected $where_groups;
	
	/**
	 * collection of Sort objects
	 * @var SplObjectStorage
	 **/
	protected $default_sorts;
	
	/**
	 * collection of Sort objects
	 * @var SplObjectStorage
	 **/
	protected $sorts;
	
	/**
	 * iDataField object
	 * @var iDataField
	 **/
	protected $pivot_field;
	
	/**
	 * max_rows
	 * @var int
	 **/
	protected $max_rows;

	/**
	 * cnt_row
	 * @var boolean
	 **/
	protected $cnt_row;
	
	/**
	 * sum_row
	 * @var boolean
	 **/
	protected $sum_row;
	
		/**
	 * avg_row
	 * @var boolean
	 **/
	protected $avg_row;
	
	/**
	 * bench_row
	 * @var boolean
	 **/
	protected $bench_row;
	
	/**
	 * display_type
	 * @var string
	 **/
	protected $display_type;
	
	/**
	 * is_summary
	 * @var boolean
	 **/
	protected $is_summary;
	
	/**
	 * has_aggregate
	 * @var boolean
	 **/
	protected $has_aggregate;
	
	/**
	 * scope
	 * @var string
	 **/
	protected $scope;
	
	/**
	 * active
	 * @var boolean
	 **/
	protected $active;
	
	//@todo: below should be in BlockData?
	/**
	 * filters
	 * 
	 * @var Filters
	 **/
	protected $filters;
	
	/**
	 * primary_table_name
	 * @var string
	 **/
	protected $primary_table_name;
	
	/**
	 * joins
	 * @var Joins
	 **/
	protected $joins;
	
	
/**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct($block_datasource, $id, $page_id, $name, $description, $scope, $active, $path, $max_rows, $cnt_row, 
			$sum_row, $avg_row, $bench_row, $is_summary, $display_type, SupplementalFactory $supp_factory = null) {
		$this->datasource = $block_datasource;
		$this->id = $id;
		$this->page_id = $page_id;
		$this->name = $name;
		$this->description = $description;
		$this->scope = $scope;
		$this->active = $active;
		$this->path = $path;
		$this->max_rows = $max_rows;
		$this->cnt_row = $cnt_row;
		$this->sum_row = $sum_row;
		$this->avg_row = $avg_row;
		$this->bench_row = $bench_row;
		$this->is_summary = $is_summary;
		//$this->group_by_fields = $group_by_fields;
		//$this->where_fields = $group_by_fields;
		$this->display_type = $display_type;
		
		//load data for remaining fields
		$this->report_fields = $this->setReportFields($supp_factory);
		$this->sorts = $this->setDefaultSort();
		//@todo: filters
	}
	
	/*
	 * a slew of getters
	 */
	
	public function id(){
		return $this->id;
	}

	public function path(){
		return $this->path;
	}

	public function name(){
		return $this->name;
	}

	public function title(){
		return $this->description;
	}

	public function pivotFieldName(){
		return $this->pivot_field->dbFieldName();
	}

	public function primaryTableName(){
		return $this->primary_table_name;
	}

	public function sorts(){
		return $this->sorts;
	}

	public function joins(){
		return $this->joins;
	}
	
	public function displayType(){
		return $this->display_type;
	}

	public function subtitle(){
		return $this->filters->get_filter_text();
	}

	public function hasBenchmarks(){
		return $this->bench_row;
	}

	public function hasAvgRow(){
		return $this->avg_row;
	}

	public function hasSumRow(){
		return $this->sum_row;
	}
	
	public function hasPivot(){
		return isset($this->pivot_field);
	}
	
	public function reportFields(){
		return $this->report_fields;
	}

	/**
	 * @method sortText()
	 * @return string sort text
	 * @access public
	* */
	public function sortText($is_verbose = false){
		$ret = '';
		$is_first = true;
		if(isset($this->sorts) && count($this->sorts) > 0){
			foreach($this->sorts as $s){
				$ret .= $is_verbose ? $s->sort_text($is_first) : $s->sort_text_brief($is_first);
				$is_first = false;
			}
		}
		
		return $ret;
	}
	
	/**
	 * @method resetSort()
	 * @return void
	 * @access public
	* */
	public function resetSort(){
		$this->sorts->removeAll($this->sorts);
	}
	
	/**
	 * @method addSort()
	 * @param SplObjectStorage of Sort objects
	 * @return void
	 * @access public
	* */
	public function addSort(Sort $sort){
		$this->sorts->attach($sort);
	}
	
	/**
	 * @method addSortField()
	 * @param iDataField sort field
	 * @param string sort order
	 * @return void
	 * @access public
	* */
	public function addSortField(iDataField $datafield, $sort_order){
		$this->sorts->attach(new Sort($datafield, $sort_order));
	}
	
	/**
	 * @method setDefaultSort()
	 * @return void
	 * @author ctranel
	 **/
	public function setDefaultSort(){
		$this->default_sorts = new \SplObjectStorage();
		
		$arr_ret = [];
		$arr_res = $this->datasource->getSortData($this->id);
		if(is_array($arr_res)){
			foreach($arr_res as $s){
				$datafield = new DbField($s['db_field_id'], $s['db_table_id'], $s['db_field_name'], $s['name'], $s['description'], $s['pdf_width'], $s['default_sort_order'],
						 $s['datatype'], $s['max_length'], $s['decimal_scale'], $s['unit_of_measure'], $s['is_timespan'], $s['is_foreign_key'], $s['is_nullable'], $s['is_natural_sort']);
				$this->default_sorts->attach(new Sort($datafield, $s['sort_order']));
			}
			$this->sorts = $this->default_sorts;
		}
	}
	
	/**
	 * @method sortFieldNames()
	 * @return ordered array of field names
	 * @access public
	public function sortFieldNames(){
		$ret = [];
		if(isset($this->sorts) && count($this->sorts) > 0){
			foreach($this->sorts as $s){
				$ret[] = $s->fieldName();
			}
		}
		
		return $ret;
	}
	* */
	/**
	 * sortOrders
	 *
	 * @method sortOrders()
	 * @return ordered array of sort orders
	 * @access public
	 public function sortOrders(){
	 $ret = [];
	 if(isset($this->sorts) && count($this->sorts) > 0){
	 foreach($this->sorts as $s){
	 $ret[] = $s->order();
	 }
	 }
	
	 return $ret;
	 }
	 * */
	
	/**
	 * @method setFilters()
	 * @param Filter object
	 * @return void
	 * @access public
	* */
	public function setFilters(Filters $filters){
		$this->filters = $filters;
	}
	
	/**
	 * @method getFieldTable()
	 * @param field name
	 * @return string table name
	 * @access public
	 * 
	 * @todo: change this to return tables for all fields and iterate where it is called?
	* */
	public function getFieldTable($field_name){
		if(isset($this->report_fields) && count($this->report_fields) > 0){
			foreach($this->report_fields as $f){
				if($f->dbFieldName() === $field_name){
					return $f->dbTableName();
				}
			}
		}
		return null;
	}
	
	
	/**
	 * @method getSelectFields()
	 * 
	 * Retrieves fields designated as select, supplemental params (if set), 
	 * 
	 * @return string table name
	 * @access public
	 * */
	public function getSelectFields(){
		$ret = [];
		if(isset($this->report_fields) && count($this->report_fields) > 0){
			foreach($this->report_fields as $f){
				$ret[] = $f->selectFieldText();
			}
		}
		//@todo: supplemental params
		
		
		return $ret;
	}
	
	/**
	 * @method getGroupBy()
	 * //@param SplObjectStorage of GroupBy objects
	 * @return void
	 * @access public
	* */
	public function getGroupBy(){
		$ret = [];
		
		//@todo: pull in group by fields from database
		if($this->has_aggregate && isset($this->report_fields) && count($this->report_fields) > 0){
			foreach($this->report_fields as $f){
				if(!$f->isAggregate()){
					$ret[] = $f->dbFieldName();
				}
			}
		}
		return $ret;
	}
	
	public function defaultSort(){
		return $this->default_sort;
	}

	public function displayBenchRow(){
		return $this->bench_row;
	}
	
	/**
	 * @method setPivot()
	 * @param iDataField pivot field
	 * @return void
	 * @access public
	* */
	public function setPivot(iDataField $pivot_field){
		$this->pivot_field = $pivot_field;
	}
	
	/**
	 * @method setGroupBy()
	 * @param SplObjectStorage of GroupBy objects
	 * @return void
	 * @access public
	* */
	protected function setGroupBy(){
		$this->group_by = $group_by;
	}
	
}


