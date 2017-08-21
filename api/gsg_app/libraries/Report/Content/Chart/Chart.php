<?php
namespace myagsource\Report\Content\Chart;

require_once APPPATH . 'libraries/Datasource/DbObjects/DbField.php';
require_once APPPATH . 'libraries/Report/Content/Chart/ChartField.php';
require_once APPPATH . 'libraries/Report/Content/Chart/XAxis.php';
require_once APPPATH . 'libraries/Report/Content/Chart/YAxis.php';
require_once APPPATH . 'libraries/Report/Content/Report.php';

require_once APPPATH . 'libraries/Report/iReport.php';

use \myagsource\Datasource\iDataField;
use \myagsource\Datasource\DbObjects\DbField;
use \myagsource\Datasource\DbObjects\DbTableFactory;
use \myagsource\DataHandler;
use \myagsource\Filters\ReportFilters;
use \myagsource\Report\Content\Chart\ChartField;
use \myagsource\Report\Content\Report;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\Report\Content\Chart\XAxis;
use \myagsource\Report\Content\Chart\YAxis;
use \myagsource\Datasource\DbObjects\DataConversion;

use \myagsource\Report\iReport;

/**
 * Name:  Chart
 *
 * Author: ctranel
 *
 * Created:  02-03-2015
 *
 * Description:  Contains properties and methods specific to displaying chart reports of the website.
 *
 */
class Chart extends Report {
	/**
	 * chart_type
	 * @var string
	 **/
	protected $chart_type;

	/**
	 * array of category field names
	 * @var array
	 **/
	protected $categories;

	/**
	 * array of XAxis objects
	 * @var XAxis[]
	 **/
	protected $x_axes;

	/**
	 * array of YAxis objects
	 * @var YAxis[]
	 **/
	protected $y_axes;

	/**
	 * array of series objects
	 * @var Series[]
	 **/
	protected $series;

	//added to know when to remove or keep nulls in chart data - KLM
	/**
	 * chart_type
	 * @var boolean
	 **/
	protected $keep_nulls;
	
	/**
	 */
	function __construct($report_datasource, $report_meta, ReportFilters $filters, $sorts, SupplementalFactory $supp_factory, DataHandler $data_handler, DbTableFactory $db_table_factory, iDataField $pivot_field = null, $field_groups, $keep_nulls) {
		parent::__construct($report_datasource, $report_meta, $filters, $sorts, $supp_factory, $data_handler, $db_table_factory, $pivot_field, $field_groups);

        if(!isset($this->report_fields) || empty($this->report_fields)){
            return;
        }

        $this->keep_nulls = $keep_nulls;
		$this->chart_type = $report_meta['chart_type'];
		$this->x_axes = [];
		$this->y_axes = [];
		$this->setChartAxes();
		$this->setSeries();
        $this->setDataset($report_meta['path']);
	}

	public function keepNulls(){
	    return $this->keep_nulls;
	}
	
	public function xAxes(){
		return $this->x_axes;
	}
	
//currently using for testing only
	public function series(){
		return $this->series;
	}

	public function chartType(){
		return $this->chart_type;
	}

	public function categories(){
		return $this->categories;
	}

	/**
	 * setReportFields
	 * 
	 * Sets the datafields property of datafields that are to be included in the report
	 * 
	 * @method setReportFields()
	 * @return void
	 * @access protected
	 **/
	protected function setReportFields($field_data){
		if(is_array($field_data)){
			foreach($field_data as $s){
				//aggregate
                if(isset($s['aggregate']) && !empty($s['aggregate'])){
					$this->has_aggregate = true;
				}

                $this->setSupplemental($s);

                //set report field
				$data_conversion = null;
				if(isset($s['conversion_name'])) {
					$data_conversion = new DataConversion($s['conversion_name'], $s['metric_label'], $s['metric_abbrev'],
						$s['to_metric_factor'], $s['metric_rounding_precision'], $s['imperial_label'], $s['imperial_abbrev'], $s['to_imperial_factor'], $s['imperial_rounding_precision']);
				}
				$datafield = new DbField($s['db_field_id'], $s['table_name'], $s['db_field_name'], $s['name'], $s['description'], $s['pdf_width'], $s['default_sort_order'],
					$s['datatype'], $s['max_length'], $s['decimal_scale'], $s['unit_of_measure'], $s['is_timespan'], $s['is_foreign_key'], $s['is_nullable'], $s['is_natural_sort'], $data_conversion);
				$this->report_fields[] = new ChartField(
				    $s['id'],
                    $s['name'],
                    $datafield,
                    $s['category_id'],
                    $s['is_displayed'],
                    $s['display_format'],
                    $s['aggregate'],
                    $s['is_sortable'],
                    $s['chart_type'],
                    $s['axis_index'],
                    $s['trend_type'],
                    $s['field_group'],
                    isset($this->header_supplemental[$s['db_field_name']]) ? $this->header_supplemental[$s['db_field_name']] : null,
                    isset($this->dataset_supplemental[$s['db_field_name']]) ? $this->dataset_supplemental[$s['db_field_name']] : null,
                    $s['field_group_ref_key']
                );
			}
		}
	}

    /**
     * toArray
     *
     * @return array representation of object
     *
     **/
    public function toArray(){
        if(!isset($this->report_fields) || empty($this->report_fields)){
            return;
        }

        $ret = parent::toArray();
        $ret['keep_nulls'] = $this->keep_nulls;
        if(is_array($this->categories) && !empty($this->categories)){
            $ret['categories'] = $this->categories;
        }
        $ret['chart_type'] = $this->chart_type;

        $ret['axes'] = [];
        if(is_array($this->x_axes) && !empty($this->x_axes)){
            $x = [];
            foreach($this->x_axes as $s){
                $x[] = $s->toArray();
            }
            $ret['axes']['x'] = $x;
        }
        if(is_array($this->y_axes) && !empty($this->y_axes)){
            $y = [];
            foreach($this->y_axes as $s){
                $y[] = $s->toArray();
            }
            $ret['axes']['y'] = $y;
        }
        if(is_array($this->series) && !empty($this->series)){
            $ret['series'] = $this->series;
        }
        return $ret;
    }

    /**
	 * @method setChartAxes - retrieve data for categories, axes, etc.
	 * @return void
	 * @access protected
	 *
	 **/
	protected function setChartAxes(){
		$data = $this->datasource->getChartAxes($this->id, $this->is_metric);
		if(!is_array($data) || empty($data) || count($data) < 1){
			return false;
		}
		
		$this->categories = [];
		foreach($data as $a){
			$datafield = null;
			if(isset($a['db_field_id']) && !empty($a['db_field_id'])){
				$data_conversion = null;
				if(isset($a['conversion_name'])) {
					$data_conversion = new DataConversion($a['conversion_name'], $a['metric_label'], $a['metric_abbrev'],
						$a['to_metric_factor'], $a['metric_rounding_precision'], $a['imperial_label'], $a['imperial_abbrev'], $a['to_imperial_factor'], $a['imperial_rounding_precision']);
				}
				$datafield = new DbField($a['db_field_id'], $a['table_name'], $a['db_field_name'], $a['name'], $a['description'], $a['pdf_width'], $a['default_sort_order'],
					$a['datatype'], $a['max_length'], $a['decimal_scale'], $a['unit_of_measure'], $a['is_timespan'], $a['is_foreign_key'], $a['is_nullable'], $a['is_natural_sort'], $data_conversion);
				//add fields as a report field so it is included in the select statement
				$display_format = $a['data_type'] === 'datetime' ? 'MM-dd-yy' : null;
				//@todo: this probably shouldn't be in report_fields.  Have prep select function pull fields from x axis objects?
				$this->report_fields[] = new ChartField($a['id'], $a['name'], $datafield, null, false, $display_format, null, true, null, null, null, null);
			}
			if($a['x_or_y'] === 'x'){
				//if($a['data_type'] === 'datetime' || $a['data_type'] === 'date'){
				//	$this->xaxis_field = $datafield;
				//}
				if(isset($a['category']) && !empty($a['category'])){
					$this->categories[] = $a['category'];
				}
				$this->x_axes[] = new XAxis($a['min'], $a['max'], $a['opposite'], $datafield, $a['data_type'], $a['text'], $a['category']);
			}
			if($a['x_or_y'] === 'y'){
				$this->y_axes[] = new YAxis($a['min'], $a['max'], $a['opposite'], $a['text'], $datafield);
			}
		}
	}

	/**
	 * @method getXAxisOutput
	 * @return array
	 * @access protected
	 *
	 **/
	protected function getXAxisOutput(){
		$ret = [];
		$cnt = 0;
		if(count($this->x_axes) === 0){
			return;
		}
		foreach($this->x_axes as $a){
			if($cnt === 0 || $a->category() === null){
				$ret[$cnt] = $a->getOutputData();
			}
			if($cnt === 0 && $a->category() !== null){
				$ret[$cnt]['categories'] = $this->categories;
			}
			$cnt++;
		}
		return $ret;
	}

	/**
	 * @method getYAxisOutput
	 * @return array
	 * @access protected
	 *
	 **/
	protected function getYAxisOutput(){
		$ret = [];
		$cnt = 0;
		if(count($this->y_axes) === 0){
			return;
		}

		foreach($this->y_axes as $a){
			$ret[$cnt] = $a->getOutputData();
			$cnt++;
		}
		return $ret;
	}

	/**
	 * @method getOutputData
	 * @param int number of datapoints
	 * @return array of output data for report
	 * @access public
	 *
	 **/
	public function getOutputData(){//$cnt_datapoints){
		$ret = parent::getOutputData();
		$ret['chart_type'] = $this->chart_type;
		if(count($this->x_axes) > 0){
			$ret['arr_axes']['x'] = $this->getXAxisOutput();
		}
		if(count($this->y_axes) > 0){
			$ret['arr_axes']['y'] = $this->getYAxisOutput();
		}
		if(isset($this->series) && !empty($this->series)){
			$ret['series'] = $this->series;
		}
		return $ret;
	}

	/**
	 * setSeries
	 * 
	 * 
	 * 
	 * @return void
	 * @access protected
	 *
	 **/
	protected function setSeries(){
		if(!isset($this->report_fields) || empty($this->report_fields)){
		    return;
        }

		if(!empty($this->categories) || !empty($this->field_groups)){
			$this->series = $this->deriveSeries();//count($this->json['data'], COUNT_RECURSIVE));
			$this->series = array_values($this->series);
			return;
		}
		$cnt = 0;

		//boxplots have 3 columns per series, all other chart types are 1:1
		foreach($this->report_fields as $f){
			$idx = $f->fieldGroup();
			$idx = isset($idx) ? (int)$idx : $cnt;
				
			if($f->isDisplayed()){
				$this->series[$idx] = [
					'name' => $f->displayName(),
					'um' => $f->unitOfMeasure(),
					'type' => $f->chartType(),
					'yAxis' => $f->axisIndex(),
				];
				if($f->trendType() !== null){
					$this->series[$idx]['regression'] = true;
					$this->series[$idx]['regressionSettings'] = [
						'type' => $f->trendType(),
						'order' => 8,
					];
				}
				$cnt++;
			}
		}
		$this->series = array_values($this->series);
	}

	/**
	 * @method numSeries
	 * 
	 * used only for boxplots.  hopefully this functionality can be absorbed by field group functionality
	 * 
	 * @return int number of series on chart
	 * @access protected
	 *
	 **/
	public function numSeries(){
		$series = [];
		foreach($this->report_fields as $f){
			$sg = $f->fieldGroup();
			if(!isset($sg)){
				continue;
			}
			$idx = array_search($sg, $series, true);
			if($idx === false){
				$series[] = $f->fieldGroup();
			}
		}
		if(empty($series)){
			return count($this->report_fields);
		}
		return count($series);
	}

	/**
	 * @method deriveSeries
	 * 
	 * Used when the x axis does not correspond with a data column
	 * 
	 * @return array of output data for the report
	 * @access protected
	 *
	 **/
	protected function deriveSeries(){
		$return_val = [];
		$cat_cnt_divisor = count($this->categories) === 0 ? 1 : count($this->categories);
		$field_cnt = (int)(count($this->report_fields) / $cat_cnt_divisor);

		$c = 0;
		foreach($this->report_fields as $f){
			if($f->isDisplayed()){
				$fg = $f->fieldGroup();
				if(isset($fg)){
					$idx = $fg;
					$name = $this->field_groups[$fg]['name'];
				}
				else{
					$idx = $f->id();
					$name = $f->displayName();
				}
				
				$return_val[$idx]['name'] = $name;
				if($f->unitOfMeasure()){
					$return_val[$idx]['um'] = $f->unitOfMeasure();
				}
				if($f->axisIndex()){
					$return_val[$idx]['yAxis'] = $f->axisIndex();
				}
				if($f->chartType()){
					$return_val[$idx]['type'] = $f->chartType();
				}
				
				$c++;
				if($c >= $field_cnt){
					break;
				}
			}
		}
		return $return_val;
	}
}
?>
