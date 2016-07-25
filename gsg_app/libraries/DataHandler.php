<?php

namespace myagsource;

require_once(APPPATH . 'libraries/Page/iReportBlock.php');
require_once(APPPATH . 'libraries/Page/Content/Table/TableData.php');
require_once(APPPATH . 'libraries/Page/Content/Chart/ChartData.php');

use \myagsource\Report\iReportBlock;
use \myagsource\Page\Content\Table\TableData;
use \myagsource\Page\Content\Chart\ChartData;
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
	
	function load(iReportBlock $block, $path, DbTable $db_table){
        if(file_exists(APPPATH . $path)){
            $data_handler_name = ucwords(substr($path, (strripos($path, '/') + 1)));
            list($data_handler_name, $ext) = explode('.', $data_handler_name);
            
            require_once APPPATH . $path;
            
            if($block->displayType() == 'table'){
                $data_handler_name = 'myagsource\\Report\\Content\\Table\\' . $data_handler_name;
                $block_data_handler = new $data_handler_name($block, $this->report_data_datasource, $this->benchmarks, $db_table);
            }

            if($block->displayType() == 'trend chart' || $block->displayType() == 'compare chart'){
                $data_handler_name = 'myagsource\\Report\\Content\\Chart\\' . $data_handler_name;
                $block_data_handler = new $data_handler_name($block, $this->report_data_datasource);
            }
		}

		//if no specific data-handling library found, go with the general data-handling library 
		if(!isset($data_handler_name)){
			if($block->displayType() == 'table'){
				$block_data_handler = new TableData($block, $this->report_data_datasource, $this->benchmarks, $db_table);
			}
			
			if($block->displayType() == 'trend chart' || $block->displayType() == 'compare chart'){
				$block_data_handler = new ChartData($block, $this->report_data_datasource);
			}
		}
		return $block_data_handler;
	}
}

?>