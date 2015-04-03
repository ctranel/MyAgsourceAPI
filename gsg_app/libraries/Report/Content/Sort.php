<?php
namespace myagsource\Report\Content;

require_once APPPATH . 'libraries/Report/iSort.php';
require_once APPPATH . 'libraries/Report/iBlock.php';

use \myagsource\Report\iSort;
use \myagsource\Datasource\iDataField;
use myagsource;
/**
 * Name:  Sort
 *
 * Author: ctranel
 *
 * Created:  02-03-2015
 *
 * Description:  Sort.
 *
 */
class Sort implements iSort {
	/**
	 * field
	 * @var iDataField
	 **/
	protected $datafield;
	
	/**
	 * order
	 * @var string
	 **/
	protected $order;
	
	/**
	 */
	/* -----------------------------------------------------------------
	*  Constructor

	*  Sets datafield and order properties

	*  @author: ctranel
	*  @date: Feb 10, 2015
	*  @param: iDataField sort field
	*  @param: string sort order
	*  @return datatype
	*  @throws: 
	* -----------------------------------------------------------------
	\*/
	public function __construct(\myagsource\Datasource\iDataField $datafield, $order) {
		$this->datafield = $datafield;
		$this->order = $order;
	}
	
	/* -----------------------------------------------------------------
	*  fieldName

	*  Returns name of field in sort

	*  @author: ctranel
	*  @date: Feb 10, 2015
	*  @return string field name
	*  @throws: 
	* -----------------------------------------------------------------
	\*/
	public function fieldName(){
		return $this->datafield->dbFieldName();
	}

	/* -----------------------------------------------------------------
	*  order

	*  Returns sort order (ASC or DESC)

	*  @author: ctranel
	*  @date: Feb 10, 2015
	*  @return string sort order
	*  @throws: 
	* -----------------------------------------------------------------
	\*/
	public function order(){
		return $this->order;
	}

	/**
	 * sort_text - sets text description of sort fields and order.
	 * @return string description of sort fields and order
	 * @author ctranel
	 **/
	public function sort_text($is_first){
		$intro = $is_first ? 'Sorted by ': 'then ';
		$sort_order_text = $this->order == "DESC"?'descending':'ascending';
		$this->sort_text = $intro . ucwords(str_replace('_', ' ', $this->datafield->dbFieldName())) . ' in ' . $sort_order_text . ' order';
	}
	
	/**
	 * sort_text_brief - returns brief text description of sort fields and order.  Does not set object property
	 * @return string description of sort fields and order
	 * @author ctranel
	 **/
	public function sort_text_brief($is_first){
		$intro = $is_first ? 'Sorted by ': 'then ';
		$sort_order_text = $this->order == "DESC"?'descending':'ascending';
		$this->sort_text = $intro . ucwords(str_replace('_', ' ', $this->datafield->dbFieldName())) . ', ' . $sort_order_text;
	}
}

?>