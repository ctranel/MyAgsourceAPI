<?php
namespace myagsource\Page\Content;

require_once APPPATH . 'libraries/ci_pdf.php';

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
* @todo: skip ci_pdf object and go directly to interface (derive interface from current 3rd party class) for tcpdf
*/

class Pdf{
	/**
	 * ci_pdf
	 *
	 * Ci_pdf object
	 * @var Ci_pdf
	 **/
	protected $ci_pdf;
	
	public function __construct($ci_pdf){
		$this->ci_pdf = $ci_pdf;
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
		$this->ci_pdf->setFontSubsetting(FALSE);

		// set document information
		$this->ci_pdf->SetSubject($product_name);
		$this->ci_pdf->SetKeywords($product_name);
		// set PDF table information
		$this->ci_pdf->herd_code = $this->herd_code;
		$this->ci_pdf->orientation = $orientation;

		// add first page
		$first_table = TRUE;
		foreach($blocks as $k=>$b){
			if(is_string($b['data'])){ //if the data is not an array, it is assumed that the data is HTML
				if($first_table) $this->ci_pdf->AddPage();
				$this->ci_pdf->writeHTML($b['data']);
			} 
			else {
				$objUser = $this->ci->ion_auth_model->user()->row();
			//populate object variables
				$this->ci_pdf->header_structure = $b['header_structure'];
				$this->ci_pdf->arr_pdf_width = $b['arr_pdf_widths'];
				$this->ci_pdf->sort_text = $this->sort_text;
				$this->ci_pdf->arr_filter_text = $arr_filter_text;
				$this->ci_pdf->arr_herd_data = $herd_data;
				$this->ci_pdf->consultant = $objUser->first_name . ' ' . $objUser->last_name;
				//$this->ci_pdf->bench_text = $benchmarks->get_bench_text();
				$this->ci_pdf->page_title = $product_name;
				$this->ci_pdf->table_title = $b['title'];
				if($first_table) $this->ci_pdf->AddPage();
				else $this->ci_pdf->make_table_header();
				$this->ci_pdf->make_table($b['data']);
			}
			$first_table = FALSE;
		}
				
		//Close and output PDF document
		$this->ci_pdf->Output($product_name . '-' . $this->ci->session->userdata('herd_code') . '.pdf', 'D');
	}
}