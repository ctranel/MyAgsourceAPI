<?php

namespace libraries\Report\Content;

//require_once APPPATH . 'libraries/Site/iWebContentRepository.php';

use myagsource\Report\Content\BlockField;

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
	 * a_href
	 * @var string
	 **/
	protected $a_href;

	/**
	 * a_title
	 * @var string
	 **/
	protected $a_title;

	/**
	 * a_rel
	 * @var string
	 **/
	protected $a_rel;

	/**
	 * a_class
	 * @var string
	 **/
	protected $a_class;

	/**
	 */
	function __construct() {
	}

}



?>