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
	 * @author Chris Tranel
	 */
	public function pivot($arr_dataset, $header_field, $header_field_width, $label_column_width, $bool_avg_column = FALSE, $bool_sum_column = FALSE, $bool_bench_column = FALSE){
var_dump($arr_dataset);
		$tmp_total = 0;
		$new_dataset = parent::pivot($arr_dataset, $header_field, $header_field_width, $label_column_width, $bool_avg_column, $bool_sum_column, $bool_bench_column);
		//update total field in new dataset
		return $new_dataset;
		
		
/*		
$bool_bench_column = FALSE;
		$this->date_field = $header_field;
		$sess_benchmarks = $this->session->userdata('benchmarks');
		$header_text = ' ';
		$new_dataset = array();
		//headers not used in pivot tables, so we flatten the array
		$tmp_keys = array_keys(current($this->arr_fields));
		$tmp_vals = array_flatten($this->arr_fields);
		$this->arr_fields = array_combine($tmp_keys, $tmp_vals);
		foreach($this->arr_fields as $k=>$v){
			if($v == $header_field){
				$header_text = $k;
				$this->arr_unsortable_columns[] = $v;
			}
			else {
				$new_dataset[$v][$header_field] = $k;
			}
		}
		$this->arr_fields = array($header_text => $header_field); //used for labels in left-most column that are set in foreach loop above
		$this->arr_field_sort[$header_field] = 'ASC';
		$this->arr_pdf_widths[$header_field] = $label_column_width;
		if(!isset($arr_dataset) || empty($arr_dataset)) return FALSE;
		foreach($arr_dataset as $row){
			foreach($row as $name => $val){
				if($name == $header_field && isset($val)){
					$this->arr_fields[$val] = $val;
					$this->arr_pdf_widths[$val] = $header_field_width;
					$this->arr_field_sort[$val] = 'ASC';
					$this->arr_unsortable_columns[] = $val;
				}
				elseif(strpos($name, 'isnull') === FALSE && isset($row[$header_field]) && !empty($row[$header_field])) { //2nd part eliminates rows where fresh date is null (FCS)
					if(isset($this->arr_decimal_points[$k])) $val = round($val, $this->arr_decimal_points[$k]);

					if(isset($new_dataset[$name]['total']) === FALSE && $val !== NULL){
						$new_dataset[$name]['total'] = 0;
						$new_dataset[$name]['count'] = 0;
					} 
					
					$new_dataset[$name][$row[$header_field]] = $val;

					if($val !== NULL){
						$new_dataset[$name]['total'] += $val;
						$new_dataset[$name]['count'] ++;
					} 
				}				
			}
		}
		//begin benchmarks
		if($bool_bench_column){
			$this->load->library('benchmarks_lib');
			$bench_settings = $this->get_bench_settings();
			$this->benchmarks_lib->set_criteria($this->primary_table_name, $header_field, $bench_settings['metric'], $bench_settings['criteria'], $bench_settings['arr_herd_size'], $bench_settings['arr_states']);
			$bench_sql = $this->benchmarks_lib->build_benchmark_query($this);
			$arr_benchmarks = $this->{$this->db_group_name}->query($bench_sql)->result_array();
			$arr_benchmarks = $arr_benchmarks[0];
			$arr_summary_fields[ucwords(strtolower(str_replace('_', ' ', $sess_benchmarks['metric']))) . ' (n=' . $arr_benchmarks['cnt_herds'] . ')'] = 'benchmark';
			$this->arr_pdf_widths['benchmark'] = $header_field_width;
			$this->arr_field_sort['benchmark'] = 'ASC';
			$this->arr_unsortable_columns[] = 'benchmark';
		}
		if($bool_avg_column){
			$this->arr_fields['Average'] = 'average';
			$this->arr_pdf_widths['average'] = $header_field_width;
			$this->arr_field_sort['average'] = 'ASC';
			$this->arr_unsortable_columns[] = 'average';
		}
		if($bool_sum_column){
			$this->arr_fields['Total'] = 'total';
			$this->arr_pdf_widths['total'] = $header_field_width;
			$this->arr_field_sort['total'] = 'ASC';
			$this->arr_unsortable_columns[] = 'total';
			}
		foreach($new_dataset as $k=>$a){
			if(!empty($k)){
				if($bool_bench_column){
					if($arr_benchmarks[$k] !== NULL) $sum_data['benchmark'] = round($arr_benchmarks[$k], $this->arr_decimal_points[$k]);//strpos($arr_benchmarks[$k], '.') !== FALSE ? trim(trim($arr_benchmarks[$k],'0'), '.') : $arr_benchmarks[$k];
					else $sum_data['benchmark'] = NULL;
				}
				if($bool_avg_column){
					$new_dataset[$k]['average'] = $new_dataset[$k]['total'] / $new_dataset[$name]['count'];
					if(isset($this->arr_decimal_points[$k])) $new_dataset[$k]['average'] = round($new_dataset[$k]['average'], $this->arr_decimal_points[$k]);
				}
				if(($bool_avg_column && !$bool_sum_column) || (!$bool_avg_column && !$bool_sum_column)){ //total column should not be displayed on PDF if it is only used to calculate avg 
					unset($new_dataset[$k]['total']);
				}
			}
		}
		$this->arr_db_field_list = $this->arr_fields;
		return $new_dataset;*/
	}
}