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
	 * chart_type
	 * @var string
	 **/
	protected $chart_type;

	/**
	 * axis_index
	 * @var int
	 **/
	protected $axis_index;

	/**
	 * trend_type
	 * @var string
	 **/
	protected $trend_type;

	/**
	 * field_group
	 * @var int
	 **/
	protected $field_group;

	/**
	 */
	public function __construct($id, $name, DbField $data_field, $is_displayed, $display_format, $aggregate, $is_sortable, $chart_type, $axis_index, $trend_type, $field_group, $header_supp = null, $data_supp = null) {
		parent::__construct($id, $name, $data_field, $is_displayed, $display_format, $aggregate, $is_sortable, $header_supp, $data_supp);
		$this->chart_type = $chart_type;
		$this->axis_index = $axis_index;
		$this->trend_type = $trend_type;
		$this->field_group = $field_group;
	}
	
	public function chartType(){
		return $this->chart_type;
	}

	public function axisIndex(){
		return $this->axis_index;
	}

	public function trendType(){
		return $this->trend_type;
	}

	public function seriesGroup(){
		return $this->field_group;
	}
}

?>