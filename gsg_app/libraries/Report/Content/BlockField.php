<?php
namespace myagsource\Report\Content;

/**
 * Name:  BlockField
 *
 * Author: ctranel
 *
 * Created:  02-03-2015
 *
 * Description:  Metadata typically associated with data storage for data fields..
 *
 */
abstract class BlockField {
	/**
	 * id
	 * @var int
	 **/
	protected $id;
	
	/**
	 * db_field
	 * @var iDataField
	 **/
	protected $db_field;

	/**
	 * block name
	 * @var string
	 **/
	protected $name;
		
	/**
	 * displayed
	 * @var boolean
	 **/
	protected $displayed;
	
	
	/**
	 */
	function __construct($id, $name, iDataField $data_field, $displayed) {
	}
}

?>