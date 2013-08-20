<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'libraries/pdf.php';
/**
* Name:  AgSource Laboratories Library File
*
* Author: Chris Tranel
*		  ctranel@agsource.com
*

*
* Created:  3.18.2011
*
* Description:  Library for rendering reports for the Genetic Selection Guide
*
* Requirements: PHP5 or above
*
*/

require_once APPPATH . 'libraries/Reports.php';

class Gsg_report extends Reports{
	/**
	 * create_pdf - creates PDF version of report.
	 * @param array of data to include in PDF.  Each element of the array will be a separate table/section.
	 * @param report title
	 * @param array of widths for the columns in the PDF document.  Keys are the values of the first parameter.
	 * @param array of herd data to be included in the PDF report header.
	 * @param array of the table header structure.
	 * @return void
	 * @author Chris Tranel, Agsource
	 **/
	function create_pdf($data, $product_name, $arr_filter_text = NULL, $arr_pdf_widths = NULL, $herd_data = NULL, $header_structure = NULL, $orientation = 'L'){
		$this->ci->load->library('pdf_gsg');
		$this->ci->pdf_gsg->setFontSubsetting(FALSE);

		// set document information
		$this->ci->pdf_gsg->SetSubject($product_name);
		$this->ci->pdf_gsg->SetKeywords($product_name);
		// set PDF table information
		$this->ci->pdf_gsg->herd_code = $this->herd_code;
		$this->ci->pdf_gsg->orientation = $orientation;

		// add first page
		$first_table = TRUE;
		foreach($data as $k=>$d){
			if(is_string($d)){ //if the data is not an array, it is assumed that the data is HTML
				if($first_table) $this->ci->pdf->AddPage();
				$this->ci->pdf_gsg->writeHTML($d);
			} 
			else {
				$this->ci->pdf_gsg->header_structure = $header_structure;
				$this->ci->pdf_gsg->arr_pdf_width = $arr_pdf_widths;
				$this->ci->pdf_gsg->sort_text = $this->sort_text;
				$this->ci->pdf_gsg->arr_filter_text = $arr_filter_text;
				$this->ci->pdf_gsg->arr_herd_data = $herd_data;
				$this->ci->pdf_gsg->page_title = $product_name;
				$this->ci->pdf_gsg->table_title = $k;
				if($first_table) $this->ci->pdf_gsg->AddPage();
				else $this->ci->pdf_gsg->make_table_header();
				$this->ci->pdf_gsg->make_table($d);
			}
			$first_table = FALSE;
		}
				
		//Close and output PDF document
		$this->ci->pdf_gsg->Output($product_name . '-' . $this->ci->session->userdata('herd_code') . '.pdf', 'D');
	}
}