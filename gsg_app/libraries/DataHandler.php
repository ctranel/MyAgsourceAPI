<?php

namespace myagsource;

require_once(APPPATH . 'libraries/Report/iBlock.php');
require_once(APPPATH . 'libraries/Report/Content/Table/TableData.php');
require_once(APPPATH . 'libraries/Report/Content/Chart/ChartData.php');

use \myagsource\Report\iBlock;
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
	 */
	function __construct() {
	}
	
	/* -----------------------------------------------------------------
	*  load

	*  loads either chart or table data handler

	*  @author: ctranel
	*  @date: May 13, 2015
	*  @param: iBlock 
	*  @param: string path
	*  @param: report_data_model
	*  @param: DbTable 
	*  @param: Benchmarks 
	*  @return iBlockData
	*  @throws: 
	 * 
	 * @todo: get rid of "new" keywords, or consider this a factory?
	 */
	
	function load(iBlock $block, $path, \report_data_model $report_data_datasource, DbTable $db_table, Benchmarks $benchmarks = null){
		while(strpos($path, '/') !== false){
			if(file_exists(APPPATH . $path . '.php')){
				$data_handler_name = ucwords(substr($path, (strripos($path, '/') + 1)));
				require_once APPPATH . $path . '.php';
				if($block->displayType() == 'table'){
					$data_handler_name = 'myagsource\\Report\\Content\\Table\\' . $data_handler_name;
					$block_data_handler = new $data_handler_name($block, $report_data_datasource, $benchmarks, $db_table);
				}
				
				if($block->displayType() == 'trend chart' || $block->displayType() == 'compare chart'){
					$data_handler_name = 'myagsource\\Report\\Content\\Chart\\' . $data_handler_name;
					$block_data_handler = new $data_handler_name($block, $report_data_datasource);
				}
			}
			$path = substr($path, 0, strripos($path, '/'));
		}

		//if no specific data-handling library found, go with the general data-handling library 
		if(!isset($data_handler_name)){
			if($block->displayType() == 'table'){
				$block_data_handler = new TableData($block, $report_data_datasource, $benchmarks, $db_table);
			}
			
			if($block->displayType() == 'trend chart' || $block->displayType() == 'compare chart'){
				$block_data_handler = new ChartData($block, $report_data_datasource);
			}
		}
		return $block_data_handler;
	}
}

?>