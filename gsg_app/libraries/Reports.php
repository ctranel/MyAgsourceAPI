<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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

class Reports{
	//protected $arr_filters;
	public $arr_sort_by;
	public $arr_sort_order;
	public $arr_filter_text; //used with PDFs
	public $sort_text; //used with PDFs
	public $herd_code;
	
	public function __construct(){
		$this->ci =& get_instance();
	}
	
	/**
	 * get_herd_info - retrieves herd data (for use in report headers)
	 * @return array of herd data
	 * @author ctranel
	 **/
	public function get_herd_info($herd_code_in = FALSE){
		$this->ci->load->model('herd_model');
		if(!$herd_code_in) $herd_code_in = $this->herd_code;
		return $this->ci->herd_model->header_info($herd_code_in);
	}

	/**
	 * sort_text - sets text description of sort fields and order.
	 * @return string description of sort fields and order
	 * @author ctranel
	 **/
	public function sort_text($arr_sort_by, $arr_sort_order){
		if(is_array($arr_sort_by) && !empty($arr_sort_by)){
			$count = count($arr_sort_by);
			for($x = 0; $x < $count; $x++){
				$intro = $x == 0 ? 'Sorted by ': 'then ';
				$sort_order_text = $arr_sort_order[$x] == "DESC"?'descending':'ascending';
				$this->sort_text = $intro . ucwords(str_replace('_', ' ', $arr_sort_by[$x])) . ' in ' . $sort_order_text . ' order';
			}
			return $this->sort_text;
		}
		else {
			$this->sort_text = '';
			return false;
		}
	}
	
	/**
	 * sort_text_brief - returns brief text description of sort fields and order.  Does not set object property
	 * @return string description of sort fields and order
	 * @author ctranel
	 **/
	public function sort_text_brief($arr_sort_by, $arr_sort_order){
		if(is_array($arr_sort_by) && !empty($arr_sort_by)){
			$count = count($arr_sort_by);
			$ret_val = NULL;
			for($x = 0; $x < $count; $x++){
				$sort_order_text = $arr_sort_order[$x] == "DESC"?'descending':'ascending';
				$ret_val = ucwords(str_replace('_', ' ', $arr_sort_by[$x])) . ', ' . $arr_sort_order[$x];
			}
			return $ret_val;
		}
		else {
			return false;
		}
	}
	
	/**
	 * create_pdf - creates PDF version of report.
	 * @param array of data to include in PDF.  Each element of the array will be a separate table/section.
	 * @param report title
	 * @param array of widths for the columns in the PDF document.  Keys are the values of the first parameter.
	 * @param array of herd data to be included in the PDF report header.
	 * @param array of the table header structure.
	 * @return void
	 * @author ctranel
	 **/
	function create_pdf($blocks, $product_name, $arr_filter_text = NULL, $herd_data = NULL, $orientation = 'L'){
		$this->ci->load->library('pdf');
		$this->ci->pdf->setFontSubsetting(FALSE);

		// set document information
		$this->ci->pdf->SetSubject($product_name);
		$this->ci->pdf->SetKeywords($product_name);
		// set PDF table information
		$this->ci->pdf->herd_code = $this->herd_code;
		$this->ci->pdf->orientation = $orientation;

		// add first page
		$first_table = TRUE;
		foreach($blocks as $k=>$b){
			if(is_string($b['data'])){ //if the data is not an array, it is assumed that the data is HTML
				if($first_table) $this->ci->pdf->AddPage();
				$this->ci->pdf->writeHTML($b['data']);
			} 
			else {
				$objUser = $this->ci->ion_auth_model->user()->row();
			//populate object variables
				$this->ci->pdf->header_structure = $b['header_structure'];
				$this->ci->pdf->arr_pdf_width = $b['arr_pdf_widths'];
				$this->ci->pdf->sort_text = $this->sort_text;
				$this->ci->pdf->arr_filter_text = $arr_filter_text;
				$this->ci->pdf->arr_herd_data = $herd_data;
				$this->ci->pdf->consultant = $objUser->first_name . ' ' . $objUser->last_name;
				//$this->ci->pdf->bench_text = $this->ci->benchmarks_lib->get_bench_text();
				$this->ci->pdf->page_title = $product_name;
				$this->ci->pdf->table_title = $b['title'];
				if($first_table) $this->ci->pdf->AddPage();
				else $this->ci->pdf->make_table_header();
				$this->ci->pdf->make_table($b['data']);
			}
			$first_table = FALSE;
		}
				
		//Close and output PDF document
		$this->ci->pdf->Output($product_name . '-' . $this->ci->session->userdata('herd_code') . '.pdf', 'D');
	}

	/**
	 * create_csv - creates PDF version of report.
	 * @param array of data for report.
	 * @return void
	 * @author ctranel
	 **/
	function create_csv($data){
		$delimiter = ",";
		$newline = "\r\n";
		$this->ci->config->set_item('compress_output', FALSE);
		//$this->load->helper('table_header');
		$this->ci->load->helper('csv');
		
		$filename = $this->herd_code . '-' . date('mdy-His') . '.csv';
		header('Content-type: application/excel');
		header('Content-disposition: attachment; filename=' . $filename);
		echo csv_from_result($data, $delimiter, $newline);//, NULL, $arr_header_override
	}
}