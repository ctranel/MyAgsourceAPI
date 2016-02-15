<?php
namespace myagsource\Report\Content;

require_once APPPATH . 'libraries/Report/iBlock.php';
require_once APPPATH . 'libraries/Report/Content/Sort.php';
require_once APPPATH . 'libraries/Report/Content/WhereGroup.php';
require_once APPPATH . 'libraries/Datasource/DbObjects/DbField.php';
//require_once APPPATH . 'libraries/Site/iWebContentRepository.php';

use \myagsource\Report\iBlock;
use \myagsource\dhi\Herd;
use \myagsource\Report\Content\Sort;
use \myagsource\Report\Content\WhereGroup;
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
	 * array of field names to be added to the field objects
	 * @var array of strings
	 **/
	protected $addl_select_field_names;

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
	 * supp_factory
	 * @var SupplementalFactory
	 **/
	protected $supp_factory;
	
	/**
	 * supp_param_fieldnames
	 * @var array
	 **/
	protected $supp_param_fieldnames;
	
	/**
	 * field_groups
	 * @var numerically keyed array
	 **/
	protected $field_groups;
	
    //The count of rows appended to the end of a dataset's rows; the number of these rows: sum_row,avg_row,cnt_row,bench_row
	protected $appended_rows_count;
	
/**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct($block_datasource, $id, $page_id, $name, $description, $scope, $active, $path, $max_rows, $cnt_row, 
			$sum_row, $avg_row, $bench_row, $is_summary, $display_type, SupplementalFactory $supp_factory = null, $field_groups = null) {
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
		$this->field_groups = $field_groups;
		//$this->group_by_fields = $group_by_fields;
		//$this->where_fields = $group_by_fields;
		$this->display_type = $display_type;
		$this->supp_factory = $supp_factory;
		
		$this->supp_param_fieldnames = [];
		
		//load data for remaining fields
		$this->setWhereGroups();
		$this->setDefaultSort();
		//@todo: joins
		
		$this->appended_rows_count = 0;
		if ($cnt_row) {
		    $this->appended_rows_count++;
		}
		if ($sum_row) {
		    $this->appended_rows_count++;
		}
		if ($avg_row) {
		    $this->appended_rows_count++;
		}
		if ($bench_row) {
		    $this->appended_rows_count++;
		}
		
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

	public function title(){
		return $this->name;
	}
	public function maxRows(){
		return $this->max_rows;
	}
/*
	public function name(){
		return $this->name;
	}

	public function description(){
		return $this->description;
	}
*/
	public function pivotFieldName(){
		return $this->pivot_field->dbFieldName();
	}

	public function primaryTableName(){
		return $this->primary_table_name;
	}

	public function sorts(){
		return $this->sorts;
	}

/*
	public function joins(){
		return $this->joins;
	}
*/
	
	public function displayType(){
		return $this->display_type;
	}

	public function subtitle(){
		return $this->filters->get_filter_text();
	}

	public function hasBenchmark(){
		return $this->bench_row;
	}

	public function isSummary(){
		return $this->is_summary;
	}

	public function fieldGroups(){
		return $this->field_groups;
	}

	public function hasCntRow(){
		return $this->cnt_row;
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
	
	public function numFields(){
		return $this->report_fields->count();
	}

	public function getFieldlistArray(){
		if(!isset($this->report_fields) || $this->report_fields->count() === 0){
			return false;
		}
		$ret = [];
		foreach($this->report_fields as $f){
			$ret[] = $f->dbFieldName();
		}
		return $ret;
	}

	public function getDisplayedFieldArray(){
		if(!isset($this->report_fields) || $this->report_fields->count() === 0){
			return false;
		}
		$ret = [];
		foreach($this->report_fields as $f){
			if($f->isDisplayed()){
				$ret[] = $f->displayName();
			}
		}
		return $ret;
	}

	public function getAppendedRowsCount(){
	    return $this->appended_rows_count;
	}
	
	/**
	 * addFieldName
	 * 
	 * Add a field name to be included in the data query.  Will not create a new series
	 * 
	 * @return void
	 * @access public
	 *
	 **/
	public function addFieldName($name){
		$this->addl_select_field_names[] = $name;
	}

	/**
	 * @method getOutputData
	 * @return array of output data for block
	 * @access public
	 *
	 **/
	public function getOutputData(){
		return [
			'name' => $this->name,
			'description' => $this->name,
			'filter_text' => $this->filters->get_filter_text(),
			'client_data' => [
				'block' => $this->path, //original program included sort_by, sort_order, graph_order but couldn't find anywhere it was used
				
			],
		];
	}

	/**
	 * setWhereGroups()
	 * 
	 * @return void
	 * @author ctranel
	 * @todo: implement child/nested groups
	 **/
	protected function setWhereGroups(){
		$this->where_groups = new \SplObjectStorage();
		$criteria = new \SplObjectStorage();
		$arr_ret = [];
		$arr_res = $this->datasource->getWhereData($this->id);
		if(!is_array($arr_res) || empty($arr_res)){
			return;
		}
		$prev_group = $arr_res[0]['where_group_id'];
		$prev_op = $arr_res[0]['operator'];
		if(is_array($arr_res)){
			foreach($arr_res as $s){
				if($prev_group != $s['where_group_id']){
					$this->where_groups->attach(new WhereGroup($prev_op, $criteria));
					$criteria = new \SplObjectStorage();
				}
				$criteria_datafield = new DbField($s['db_field_id'], $s['db_table_id'], $s['db_field_name'], $s['name'], $s['description'], $s['pdf_width'], $s['default_sort_order'],
						 $s['datatype'], $s['max_length'], $s['decimal_scale'], $s['unit_of_measure'], $s['is_timespan'], $s['is_foreign_key'], $s['is_nullable'], $s['is_natural_sort']);
				$criteria->attach(new WhereCriteria($criteria_datafield, $s['condition']));

				$prev_group = $s['where_group_id'];
				$prev_op = $s['operator'];
			}
			//add the last item
			$this->where_groups->attach(new WhereGroup($s['operator'], $criteria));
		}
	}
	
	/**
	 * getWhereGroupArray()
	 * 
	 * @return array of (nested) where groups
	 * @author ctranel
	 **/
	public function getWhereGroupArray(){
		$ret = [];
		if(!is_a($this->where_groups, 'SplObjectStorage')){
			$this->where_groups = $this->setWhereGroups();
		}
		if($this->where_groups->count() === 0){
			return;
		}

		foreach($this->where_groups as $wg){
			$ret[] = $wg->criteria();
		}
		return $ret;
	}
	
	/**
	 * @method sortText()
	 * @return string sort text
	 * @access public
	* */
	public function sortText($is_verbose = false){
		$ret = '';
		$is_first = true;
		if(isset($this->sorts) && $this->sorts->count() > 0){
			foreach($this->sorts as $s){
				$ret .= $is_verbose ? $s->sortText($is_first) : $s->sortTextBrief($is_first);
				$is_first = false;
			}
		}
		
		return $ret;
	}
	
	/**
	 * getSortArray()
	 * 
	 * returns field-name-keyed array of sort orders
	 * 
	 * @return string sort text
	 * @access public
	* */
	public function getSortArray(){
		$ret = [];
		if(isset($this->sorts) && count($this->sorts) > 0){
			foreach($this->sorts as $s){
				$ret[$s->fieldName()] = $s->order();
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
		if(isset($this->sorts) && $this->sorts->count() > 0){
			$this->sorts->removeAll($this->sorts);
		}
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
	public function addSortField(iDataField $datafield, $sort_order){
		$this->sorts->attach(new Sort($datafield, $sort_order));
	}
	* */
	
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
		}
		$this->sorts = $this->default_sorts;
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
	 * @method isNaturalSort()
	 * @param field name
	 * @return boolean
	 * @access public
	 *
	 * */
	public function isNaturalSort($field_name){
		if(isset($this->report_fields) && count($this->report_fields) > 0){
			foreach($this->report_fields as $f){
				if($f->dbFieldName() === $field_name){
					return $f->isNaturalSort();
				}
			}
		}
		return false;
	}
	
	/**
	 * @method isNaturalSort()
	 * @param field name
	 * @return boolean
	 * @access public
	 *
	 * */
	public function isSortable($field_name){
		if(isset($this->report_fields) && count($this->report_fields) > 0){
			foreach($this->report_fields as $f){
				if($f->dbFieldName() === $field_name){
					return $f->isSortable();
				}
			}
		}
		return false;
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
		//supplemental params
		if(isset($this->supp_param_fieldnames) && count($this->supp_param_fieldnames) > 0){
			foreach($this->supp_param_fieldnames as $f){
				$ret[] = $f;
			}
		}

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
}


