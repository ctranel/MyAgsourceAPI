<?php

namespace myagsource\Report\Content\Chart;

require_once APPPATH . 'libraries/Report/Content/ReportField.php';
require_once APPPATH . 'libraries/Datasource/DbObjects/DbField.php';

use \myagsource\Report\Content\ReportField;
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
class ChartField extends ReportField {
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
	public function __construct($id, $name, DbField $data_field, $category_id, $is_displayed, $display_format, $aggregate, $is_sortable, $chart_type, $axis_index, $trend_type, $field_group, $header_supp = null, $data_supp = null, $field_group = null, $field_group_ref_key = null) {
		parent::__construct($id, $name, $data_field, $category_id, $is_displayed, $display_format, $aggregate, $is_sortable, $header_supp, $data_supp, $field_group, $field_group_ref_key);
		$this->chart_type = $chart_type;
		$this->axis_index = $axis_index;
		$this->trend_type = $trend_type;
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
	
	public function toArray(){
        $ret = parent::toArray();
        $ret['trend_type'] = $this->trend_type;
        $ret['chart_type'] = $this->chart_type;

        return $ret;
    }
}

?>