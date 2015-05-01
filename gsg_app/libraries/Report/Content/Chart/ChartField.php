<?php

namespace myagsource\Report\Content\Chart;

//require_once APPPATH . 'libraries/Site/iWebContentRepository.php';
require_once APPPATH . 'libraries/Report/Content/BlockField.php';
require_once APPPATH . 'libraries/Datasource/DbObjects/DbField.php';

use \myagsource\Report\Content\BlockField;
use \myagsource\Datasource\DbObjects\DbField;

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
	public function __construct($id, $name, DbField $data_field, $is_displayed, $display_format, $aggregate, $is_sortable, $header_supp = null, $data_supp = null) {
		parent::__construct($id, $name, $data_field, $is_displayed, $display_format, $aggregate, $is_sortable, $header_supp, $data_supp);
	}

}

?>