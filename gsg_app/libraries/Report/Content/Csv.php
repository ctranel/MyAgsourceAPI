<?php
namespace myagsource\Report\Content;

/**
* Name:  Report Function Library File
*
* Author: ctranel
*		  ctranel@agsource.com
*

*
* Created:  3.18.2011
*
* Description:  Library for rendering reports
*
* Requirements: PHP5 or above
*
*/

class Csv{
	//protected $arr_filters;
	public $arr_sort_by;
	public $arr_sort_order;
	public $herd_code;
	
	public function __construct(){
	}
	
	/**
	 * create_csv - creates CSV version of report.
	 * @param array of data for report.
	 * @param string herd code
	 * @return void
	 * @author ctranel
	 **/
	function create_csv($data){
		$delimiter = ",";
		$newline = "\r\n";
		return $this->csv_from_result($data, $delimiter, $newline);
	}
	
	/**
	 * Generate CSV from a array or query result object
	 *
	 * @access	public
	 * @param	object/array	The data array or query result object
	 * @param	string	The delimiter - comma by default
	 * @param	string	The newline character - \n by default
	 * @param	string	The enclosure - double quote by default
	 * @return	string
	 */
	protected function csv_from_result($data, $delim = ",", $newline = "\n", $enclosure = '"', $header_override = FALSE) {
		// Next blast through the result array and build out the rows
		if(is_object($data) && method_exists($data, 'list_fields')) $data = $data->result_array();
		if(is_array($data) && !empty($data)){
			// Begin output with header
			$arr_tmp = array();
			$i = 0;
			$cnt = count($data);
			$active_el = current($data);
			$out = '';
				
			while(count($arr_tmp) < 2 && $i < $cnt){
				//echo "\n" . count($data) . ' - ' . $cnt . ' - ' . $i . "\n";
				$arr_tmp = $active_el;
				$i++;
				$active_el = next($data);
			}
			foreach ($data as $row) {
				foreach ($row as $key=>$item) {
					if(stripos($key, 'isnull') === FALSE) {
						//handle special cases
						//@todo: get special cases out of here
						/*
						if ($key === "net_merit_amtzz"){
							$arr_item = explode('*', $item);
							$denote = isset($arr_item[1]) ? '*' : '';
							$out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $arr_item[0]).$enclosure.$delim;
							$out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $denote).$enclosure.$delim;
						}
						else{ */
							$out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $item).$enclosure.$delim;
						//}
					}
				}
				$out = rtrim($out);
				$out .= $newline;
			}
			return $out;
		}
		else {
			show_error('There is no data to download');
			return false;
		}
	}
}