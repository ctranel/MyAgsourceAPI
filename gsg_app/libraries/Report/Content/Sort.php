<?php
namespace myagsource\Report\Content;

require_once APPPATH . 'libraries/Report/iSort.php';
require_once APPPATH . 'libraries/Report/iBlock.php';

use \myagsource\Report\iSort;
use \myagsource\Datasource\iDataField;
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
	public function __construct(iDataField $datafield, $order) {
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
		return $this->datafield->name();
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
}

?>