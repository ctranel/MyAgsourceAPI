<?php
/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 6/15/2017
 * Time: 3:57 PM
 */

namespace myagsource\Datasource\DbObjects;


use myagsource\Datasource\iDataConversion;

class DataConversion implements iDataConversion
{

    /**
     * name
     * @var string
     **/
    protected $name;

    /**
     * metric_label
     * @var string
     **/
    protected $metric_label;

    /**
     * metric_abbrev
     * @var string
     **/
    protected $metric_abbrev;

    /**
     * to_metric_factor
     * @var float
     **/
    protected $to_metric_factor;

    /**
     * metric_rounding_precision
     * @var int
     **/
    protected $metric_rounding_precision;

    /**
     * imperial_label
     * @var string
     **/
    protected $imperial_label;

    /**
     * imperial_abbrev
     * @var string
     **/
    protected $imperial_abbrev;

    /**
     * to_imperial_factor
     * @var float
     **/
    protected $to_imperial_factor;

    /**
     * to_imperial_factor
     * @var int
     **/
    protected $imperial_rounding_precision;

    public function __construct($name, $metric_label, $metric_abbrev, $to_metric_factor, $metric_rounding_precision, $imperial_label, $imperial_abbrev, $to_imperial_factor, $imperial_rounding_precision){
        $this->name = $name;
        $this->metric_label = $metric_label;
        $this->metric_abbrev = $metric_abbrev;
        $this->to_metric_factor = $to_metric_factor;
        $this->metric_rounding_precision = $metric_rounding_precision;
        $this->imperial_label = $imperial_label;
        $this->imperial_abbrev = $imperial_abbrev;
        $this->to_imperial_factor = $to_imperial_factor;
        $this->imperial_rounding_precision = $imperial_rounding_precision;
    }

    public function metricFactor(){
        return $this->to_metric_factor;
    }

    public function metricRoundingPrecision(){
        return $this->metric_rounding_precision;
    }
}