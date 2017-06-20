<?php

namespace myagsource;

require_once(APPPATH . 'libraries/Report/iReport.php');
require_once(APPPATH . 'libraries/Report/Content/Table/TableData.php');
require_once(APPPATH . 'libraries/Report/Content/Chart/ChartData.php');

use \myagsource\Report\iReport;
use \myagsource\Report\Content\Chart\Chart;
//use \myagsource\Page\iReportBlock;
use \myagsource\Report\Content\Table\TableData;
use \myagsource\Report\Content\Chart\ChartData;
use \myagsource\Datasource\DbObjects\DbTable;
use \myagsource\Benchmarks\Benchmarks;

/**
 * DataHandler
 *
 * @author ctranel
 *        
* Created:  08-03-2015
*
* Description:  Works with datahandling for app
*
 */
class DataHandler {
    /**
     * report_data_datasource
     *
     * @var \report_data_model
     **/
    protected $report_data_datasource;

    /**
     * benchmarks
     *
     * @var Benchmarks
     **/
    protected $benchmarks;

    /**
     * is_metric
     *
     * @var boolean
     **/
    protected $is_metric;

    /**
	 */
	function __construct(\report_data_model $report_data_datasource, Benchmarks $benchmarks, $is_metric) {
        $this->report_data_datasource = $report_data_datasource;
        $this->benchmarks = $benchmarks;
        $this->is_metric = (bool)$is_metric;
	}
	
	/* -----------------------------------------------------------------
	*  load

	*  loads either chart or table data handler

	*  @author: ctranel
	*  @date: May 13, 2015
	*  @param: iReportBlock 
	*  @param: string path
	*  @param: report_data_model
	*  @param: DbTable 
	*  @param: Benchmarks 
	*  @return iReportBlock
	*  @throws: 
	 * 
	 */
	
	function load(iReport $report, $path, DbTable $db_table){
        if(file_exists(APPPATH . $path)){
            $data_handler_name = ucwords(substr($path, (strripos($path, '/') + 1)));
            list($data_handler_name, $ext) = explode('.', $data_handler_name);
            
            require_once APPPATH . $path;
            
            if(is_a($report, '\myagsource\Report\Content\Table\Table')){
                $data_handler_name = 'myagsource\\Page\\Content\\Table\\' . $data_handler_name;
                return new $data_handler_name($report, $this->report_data_datasource, $this->benchmarks, $db_table, $this->is_metric);
            }

            if(is_a($report, '\myagsource\Report\Content\Chart\Chart')){
                $data_handler_name = 'myagsource\\Page\\Content\\Chart\\' . $data_handler_name;
                return new $data_handler_name($report, $this->report_data_datasource, $this->is_metric);
            }
		}

        if(is_a($report, '\myagsource\Report\Content\Table\Table')){
            return new TableData($report, $this->report_data_datasource, $this->benchmarks, $db_table, $this->is_metric);
        }

        if(is_a($report, '\myagsource\Report\Content\Chart\Chart')){
            return new ChartData($report, $this->report_data_datasource, $this->is_metric);
        }

		throw new \Exception('No data handlers found for requested content.');
	}
}

?>