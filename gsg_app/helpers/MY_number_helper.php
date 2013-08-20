<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * format_profit_currency
 *
 * formats currency for presentation in economic opportunity section
 *
 * @access	public
 * @param	double unformatted number
 * @return	string
 */
if ( ! function_exists('format_profit_currency')) {
	function format_profit_currency($num){
		if(!is_numeric($num)) return FALSE;
		if($num < 0) $return_val = '($' . number_format(abs($num)) . ')';
		else $return_val = '$' . number_format($num);
		return $return_val;
	}
}