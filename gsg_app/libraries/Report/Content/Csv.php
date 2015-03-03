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
	 * create_csv - creates PDF version of report.
	 * @param array of data for report.
	 * @param string herd code
	 * @return void
	 * @author ctranel
	 **/
	function create_csv($data, $herd_code){
		$delimiter = ",";
		$newline = "\r\n";
		echo $this->csv_from_result($data, $delimiter, $newline);
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
			$print_header_next = TRUE;
				
			while(count($arr_tmp) < 2 && $i < $cnt){
				//echo "\n" . count($data) . ' - ' . $cnt . ' - ' . $i . "\n";
				$arr_tmp = $active_el;
				$i++;
				$active_el = next($data);
			}
			//reset($data);
			foreach ($data as $row) {
				if($print_header_next){
					$out .= _write_header($row, $enclosure, $delim, $newline);
					$print_header_next = FALSE;
				}
				foreach ($row as $key=>$item) {
					if(stripos($key, 'isnull') === FALSE) {
						//handle special cases
						if ($key == "net_merit_amt"){
							$arr_item = explode('*', $item);
							$denote = isset($arr_item[1]) ? '*' : '';
							$out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $arr_item[0]).$enclosure.$delim;
							$out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $denote).$enclosure.$delim;
						}
						else $out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $item).$enclosure.$delim;
					}
				}
				$out = rtrim($out);
				$out .= $newline;
				if(count($row) == 1) $print_header_next = TRUE;
			}
			return $out;
		}
		else {
			show_error('There is no data to download');
			return false;
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Generate CSV from a query result object
	 *
	 * @access	public
	 * @param	array	Row data
	 * @param	string	The enclosure
	 * @param	string	The delimiter
	 * @param	string	The newline character
	 * @return	string
	 */
	protected function _write_header($row, $enclosure, $delim, $newline){
		$out = '';
		foreach ($row as $name=>$value) {
			if(stripos($name, 'isnull') === FALSE){
				if(stripos($name, 'DATE') !== FALSE) $name = '';
				if ($name == "net_merit_amt"){
					$out .= $enclosure.strtoupper(str_replace('_', ' ', str_replace($enclosure, $enclosure.$enclosure, $name))).$enclosure.$delim;
					$out .= $enclosure.strtoupper(str_replace('_', ' ', str_replace($enclosure, $enclosure.$enclosure, "NM$ is EST"))).$enclosure.$delim;
				}
				else {
					$out .= $enclosure.strtoupper(str_replace('_', ' ', str_replace($enclosure, $enclosure.$enclosure, $name))).$enclosure.$delim;
				}
			}
		}
		$out = rtrim($out);
		$out .= $newline;
		return $out;
	}
	
}