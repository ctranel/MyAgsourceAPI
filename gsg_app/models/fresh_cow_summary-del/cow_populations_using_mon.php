<?php
require_once APPPATH . 'models/report_model.php';
class Cow_populations_using_mon extends Report_model {
	public function __construct(){
		parent::__construct();
		$this->arr_unsortable_columns[] = 'total';
	    $this->arr_numeric_fields[] = 'total';
		$this->arr_fields['Total'] = 'total';
		$this->arr_field_sort['total'];
		$this->arr_field_table['total'] = $this->primary_table_name;
		$this->arr_pdf_widths['total'] = '11';
		$this->adjust_fields($this->session->userdata('herd_code'));
	}
	
	/**
	 * @method prep_select_fields() allows you to override basic defaults for fields included in results, including adding joins.
	 * @param array fields to include
	 * @return modified array of fields
	 * @author Chris Tranel
	 **/
	function prep_select_fields($arr_fields) {
		if(is_array($arr_fields)){
			// resolve field name/data/format exceptions
			if (($key = array_search('total', $arr_fields)) !== FALSE) {
				$arr_fields[$key] = '(l1_calving_cnt + l4_calving_cnt) AS total';
			}
		}
		parent::prep_select_fields($arr_fields);
	}
	
	/**
	 * @method search()
	 * @param string herd code
	 * @param array filter criteria
	 * @param array sort by
	 * @param array sort order
	 * @return array results of search
	 * @author Chris Tranel
	 **/
	function search($herd_code, $arr_filter_criteria, $arr_sort_by, $arr_sort_order) {
die('here');
		$this->arr_field_sort["CAST(CONCAT_WS('-', tci_year, tci_month, '01') AS DATE)"] = 'ASC';
		$dataset = parent::search($herd_code, $arr_filter_criteria, $arr_sort_by, $arr_sort_order);
		$new_dataset = parent::pivot($dataset, 'fresh_month', '10', '30');
		
		$this->arr_fields['Annual'] = array('Avg#/Mo'=>'Avg#/Mo', 'Total'=>'Total');
		$this->arr_pdf_widths['Avg#/Mo'] = '11';
		$this->arr_field_sort['Avg#/Mo'] = 'ASC';
		$this->arr_pdf_widths['Total'] = '11';
		$this->arr_field_sort['Total'] = 'ASC';
		
		foreach($new_dataset as $k => &$r){
			if(isset($r[NULL])){
				$sum = $r[NULL];
				$r['Avg#/Mo'] = round($sum / 12);
				$r['Total'] = $sum;
				unset($r[NULL]);
			}
			else return array();
		}
		
		$this->arr_unsortable_columns[] = 'Avg#/Mo';
		$this->arr_unsortable_columns[] = 'Total';
		unset($this->arr_fields[NULL]);
		return $new_dataset;
	}

	/*  
	 * Overrides parent function, adds ability to block escape of sort field
	 * 
	 * @method prep_sort()
	 * @param array fields to sort by
	 * @param array sort order--corresponds to first parameter
	 * @author Chris Tranel
	 */
	protected function zzzprep_sort($arr_sort_by, $arr_sort_order){
		$arr_len = is_array($arr_sort_by)?count($arr_sort_by):0;
		for($c=0; $c<$arr_len; $c++) {
			$sort_order = ($arr_sort_order[$c] == 'DESC') ? 'DESC' : 'ASC';
			$table = isset($this->arr_field_table[$arr_sort_by[$c]]) && !empty($this->arr_field_table[$arr_sort_by[$c]])?$this->arr_field_table[$arr_sort_by[$c]] . '.':'';
			if(in_array($arr_sort_by[$c], $this->arr_unsortable_columns) === FALSE && !empty($arr_sort_by[$c])){
				if($this->arr_field_sort[$arr_sort_by[$c]] == 'ASC'){
					//put the select in an array in case the field includes a function with commas between parameters 
					$this->{$this->db_group_name}->select(array($table . $arr_sort_by[$c] . ' IS NULL AS isnull' . $c), FALSE);
					$this->{$this->db_group_name}->order_by('isnull' . $c, $sort_order, FALSE);
				}
				if(in_array($arr_sort_by[$c], $this->arr_mixed_sort_fields) !== FALSE){
					$this->{$this->db_group_name}->order_by('CAST(' . $table . $arr_sort_by[$c] . ' AS UNSIGNED)', $sort_order);
					$this->{$this->db_group_name}->order_by($table . $arr_sort_by[$c], $sort_order, FALSE);
				}
				else {
					$this->{$this->db_group_name}->order_by($table . $arr_sort_by[$c], $sort_order, FALSE);
				}
			}
		}
	}
}