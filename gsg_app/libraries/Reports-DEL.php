<?php
namespace myagsource\Report\Content;

require_once APPPATH . 'libraries/Ci_pdf.php';

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
				//$this->ci->pdf->bench_text = $benchmarks->get_bench_text();
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
		$this->ci->load->helper('csv');
		
		$filename = $this->herd_code . '-' . date('mdy-His') . '.csv';
		header('Content-type: application/excel');
		header('Content-disposition: attachment; filename=' . $filename);
		echo csv_from_result($data, $delimiter, $newline);//, NULL, $arr_header_override
	}
}