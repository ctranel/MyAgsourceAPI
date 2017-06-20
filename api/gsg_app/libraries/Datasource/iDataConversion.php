<?php
/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 6/15/2017
 * Time: 3:57 PM
 */

namespace myagsource\Datasource;


interface iDataConversion
{
    function __construct($name, $metric_label, $metric_abbrev, $to_metric_factor, $metric_rounding_precision, $imperial_label, $imperial_abbrev, $to_imperial_factor, $imperial_rounding_precision);
    function metricFactor();
    function metricRoundingPrecision();

}