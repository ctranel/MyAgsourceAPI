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
	 */
	function __construct(\report_data_model $report_data_datasource, Benchmarks $benchmarks = null) {
        $this->report_data_datasource = $report_data_datasource;
        $this->benchmarks = $benchmarks;
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
                $block_data_handler = new $data_handler_name($report, $this->report_data_datasource, $this->benchmarks, $db_table);
            }

            if(is_a($report, '\myagsource\Report\Content\Chart\Chart')){
                $data_handler_name = 'myagsource\\Page\\Content\\Chart\\' . $data_handler_name;
                $block_data_handler = new $data_handler_name($report, $this->report_data_datasource);
            }
		}

		//if no specific data-handling library found, go with the general data-handling library 
		if(!isset($data_handler_name)){
			if(is_a($report, '\myagsource\Report\Content\Table\Table')){
				$block_data_handler = new TableData($report, $this->report_data_datasource, $this->benchmarks, $db_table);
			}
			
			if(is_a($report, '\myagsource\Report\Content\Chart\Chart')){
				$block_data_handler = new ChartData($report, $this->report_data_datasource);
			}
		}
		return $block_data_handler;
	}
}

?>