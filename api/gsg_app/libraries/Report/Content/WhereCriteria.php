<?php
namespace myagsource\Report\Content;

require_once APPPATH . 'libraries/Report/iWhereCriteria.php';

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
     * operator
     * @var string
     **/
    protected $operator;

    /**
     * operand
     * @var string
     **/
    protected $operand;
	
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
	public function __construct(\myagsource\Datasource\iDataField $datafield, $operator, $operand) {
		$this->datafield = $datafield;
        $this->operator = $operator;
        $this->operand = $operand;
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
        switch(strtolower($this->operator)){
            case 'in':
                $condition = "IN('" . implode("','" , explode('|', $this->operand)) . "')";
                break;
            case 'between':
                $tmp = explode('|', $this->operand);
                $condition = "BETWEEN '" . $tmp[0] . "' AND '" . $tmp[1] . "'";
                break;
            default:
                if($this->operand === 'CURRDATE'){
                    $condition = $this->operator . " GETDATE()";
                    break;
                }
                if(!isset($this->operand)){
                    $condition = $this->operator . " NULL";
                    break;
                }
                $condition = $this->operator . " '" . $this->operand . "'";
        }

        return $this->datafield->dbFieldName() . ' ' . $condition;
/*
	    var_dump($this->condition);
	    if(is_array($this->condition)){
	        if(isset($this->condition['dbfrom'])){
                return $this->datafield->dbFieldName() . ' BETWEEN ' . $this->condition['dbfrom'] . ' AND ' . $this->condition['dbto'];
            }
        }

		return $this->datafield->dbFieldName() . ' ' . $this->condition;*/
	}
}

?>