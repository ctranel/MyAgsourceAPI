<?php

namespace myagsource\Report\Content;

//require_once APPPATH . 'libraries/Site/iWebContentRepository.php';
require_once APPPATH . 'libraries/Report/Content/BlockField.php';

use \myagsource\Report\Content\BlockField;

/**
 * Name:  TableField
 *
 * Author: ctranel
 *
 * Created:  02-03-2015
 *
 * Description:  Contains properties and methods specific to displaying table fields of the website.
 *
 */
class TableField extends BlockField {
	/**
	 * header_group_id
	 * @var int
	 **/
	protected $header_group_id;

	/**
	 * link
	 * @var Link
	 **/
	protected $link;

	/**
	 * a_title
	 * @var string
	protected $a_title;
	 **/

	/**
	 * a_rel
	 * @var string
	protected $a_rel;
	 **/

	/**
	 * a_class
	 * @var string
	protected $a_class;
	 **/

	/**
	 */
	function __construct() {
	}

}



?>