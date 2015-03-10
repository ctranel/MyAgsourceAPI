<?php

namespace myagsource\Report\Content\Chart;

//require_once APPPATH . 'libraries/Site/iWebContentRepository.php';
require_once APPPATH . 'libraries/Report/Content/BlockField.php';

use \myagsource\Report\Content\BlockField;

/**
 * Name:  ChartField
 *
 * Author: ctranel
 *
 * Created:  02-03-2015
 *
 * Description:  Contains properties and methods specific to displaying chart fields of the website.
 *
 */
class ChartField extends BlockField {
	/**
	 * axis_index
	 * @var int
	 **/
	protected $axis_index;

	/**
	 * chart_type
	 * @var string
	 **/
	protected $chart_type;

	/**
	 */
	function __construct() {
	}

}



?>