<?php
namespace myagsource\Page\Content;

require_once APPPATH . 'libraries/Page/iWhereCriteria.php';

use \myagsource\Report\iWhereCriteria;
use \myagsource\Datasource\iDataField;
//use \myagsource;

/**
 * Name:  WhereCriteria
 *
 * Author: ctranel
 *
 * Created:  06-04-2015
 *
 * Description:  WhereCriteria.
 *
 */
class WhereCriteria implements iWhereCriteria {
	/**
	 * field
	 * @var iDataField
	 **/
	protected $datafield;
	
	/**
	 * condition
	 * @var string
	 **/
	protected $condition;
	
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
	public function __construct(\myagsource\Datasource\iDataField $datafield, $condition) {
		$this->datafield = $datafield;
		$this->condition = $condition;
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

	/**
	 * criteria
	 *
	 * SQL conditional string.
	 * 
	 * @return 
	 * @author ctranel
	 **/
	public function criteria(){
		return $this->datafield->dbFieldName() . ' ' . $this->condition;
	}
}

?>