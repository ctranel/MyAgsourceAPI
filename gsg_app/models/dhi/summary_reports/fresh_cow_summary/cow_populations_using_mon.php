<?php
require_once APPPATH . 'models/report_model.php';
class Transitioncowindex extends Report_model {
	public function __construct(){
		parent::__construct();
/*		$this->arr_unsortable_columns[] = 'total';
	    $this->arr_numeric_fields[] = 'total';
		$this->arr_fields['Total'] = 'total';
		$this->arr_field_sort['total'];
		$this->arr_field_table['total'] = $this->primary_table_name;
		$this->arr_pdf_widths['total'] = '11';
		$this->adjust_fields($this->session->userdata('herd_code'));
*/	}
	
	/*  
	 * @method pivot() overrides report_model
	 * @param array dataset
	 * @param string header field
	 * @param int pdf with of header field
	 * @param bool add average column
	 * @param bool add sum column
	 * @return array pivoted resultset
	 * @author ctranel
	 */
	public function pivot($arr_dataset, $header_field, $header_field_width, $label_column_width, $bool_avg_column = FALSE, $bool_sum_column = FALSE, $bool_bench_column = FALSE){
		$tmp_total = 0;
		$new_dataset = parent::pivot($arr_dataset, $header_field, $header_field_width, $label_column_width, $bool_avg_column, $bool_sum_column, $bool_bench_column);
		//update total field in new dataset
		return $new_dataset;
	}
}