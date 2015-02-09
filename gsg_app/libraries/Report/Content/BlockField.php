<?php
namespace libraries\Report\Content;

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
	 * @var DbField
	 **/
	protected $db_field;

	/**
	 * block name
	 * @var string
	 **/
	protected $name;
		
	/**
	 * display
	 * @var boolean
	 **/
	protected $display;
	
	
	/**
	 */
	function __construct() {
	}
}

?>