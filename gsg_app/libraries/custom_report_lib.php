<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Custom Report Library (for MyAgSource)
*
* Author: Chris Tranel
*
* Created:  06.28.2013
*
* Description:  Logic and service functions to support the custom report model.
*
* Requirements: PHP5 or above
*
*/

class Custom_report_lib
{
	protected $block_id;
	protected $block_user_id;
	protected $use_aggregates = FALSE;
	
	protected $arr_columns = array(); //key=field_id, value=field_id
	protected $arr_aggregate_columns = array(); //key=field_id, value=header_text
	
	protected $arr_header_groups = array(); //key=field_id, value=header_group_text
	protected $arr_header_groups_data = array();
	
	public function __construct()
	{
/*		$this->load->config('ion_auth', TRUE);
		$this->load->library('email');
		$this->load->library('session');
		$this->lang->load('ion_auth');
		$this->load->helper('cookie'); */
	}
	
	/**
	 * __call
	 *
	 * Acts as a simple way to call model methods without loads of stupid alias'
	 *
	 **/
	public function __call($method, $arguments)
	{
		if (!method_exists( $this->custom_report_model, $method) )
		{
			throw new Exception('Undefined method Custom_reports_lib::' . $method . '() called');
		}

		return call_user_func_array( array($this->custom_report_model, $method), $arguments);
	}

	/**
	 * __get
	 *
	 * Enables the use of CI super-global without having to define an extra variable.
	 *
	 * I can't remember where I first saw this, so thank you if you are the original author. -Militis
	 *
	 * @access	public
	 * @param	$var
	 * @return	mixed
	 */
	public function __get($var)
	{
		return get_instance()->$var;
	}

	/**
	 * add_report
	 *
	 * Adds each section of the block report based.
	 *
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function add_report(){
		$this->custom_report_model->start_transaction();
		$this->block_user_id = $this->session->userdata('active_group_id') == 1 ? NULL : $this->session->userdata('user_id');
		$chart_type_id = $this->input->post('chart_type_id');
		$max_rows = $this->input->post('max_rows');
		$pivot_db_field = $this->input->post('pivot_db_field');
		$arr_block_data = array(
			'user_id' => $this->block_user_id
			,'name' => $this->input->post('report_name')
			,'description' => $this->input->post('report_description')
			,'url_segment' => preg_replace("/\W|_/", '', strtolower(str_replace(' ', '_', $this->input->post('report_name'))))
			,'display_type_id' => $this->input->post('report_display_id')
			,'scope_id' => 2
			,'list_order' => $this->input->post('insert_after')
			,'chart_type_id' => empty($chart_type_id) ? NULL : $chart_type_id
			,'max_rows' => empty($max_rows) ? NULL : $max_rows
			,'cnt_row' => $this->input->post('cnt_row')
			,'sum_row' => $this->input->post('sum_row')
			,'avg_row' => $this->input->post('avg_row')
			,'bench_row' => $this->input->post('bench_row')
			,'pivot_db_field' => empty($pivot_db_field) ? NULL : $pivot_db_field
		);
		$this->block_id = $this->custom_report_model->create_block($arr_block_data);
//		if(!$this->block_id) $this->cancel_add();
		$bool_block_on_page = $this->custom_report_model->add_block_to_page(array('block_id' => $this->block_id, 'page_id' => $this->input->post('page_id')));
//		if(!$bool_block_on_page) $this->cancel_add();
		//table displays
		if($arr_block_data['display_type_id'] == 1 || $arr_block_data['display_type_id'] == 3){
echo "start <br>";
			$this->header_groups();
echo "header_groups <br>";
			$this->table_columns();
echo "table_columns <br>";
			$this->group_by();
echo "group_by <br>";
			$this->sort_by();
echo "sort_by <br>";
		}
		//chart displays
		elseif($arr_block_data['display_type_id'] == 2){
echo "yaxis <br>";
			$this->yaxes();
echo "xaxis <br>";
			$this->xaxis();
echo "trend_columns <br>";
			$this->trend_columns();
echo "group_by <br>";
			$this->group_by();
		}
		if ($this->db->trans_status() === FALSE) return FALSE;
		
		$this->custom_report_model->complete_transaction();
		return $this->db->trans_status();
	}

	//add header groups
	protected function header_groups() {
		$arr_header_groups = $this->input->post('head_group');
		$this->arr_header_groups_data = array();
		$arr_head_group_parent_index = $this->input->post('head_group_parent_index');
		$arr_header_keys = array();
		if(isset($arr_header_groups) && is_array($arr_header_groups)) $this->add_header_group($arr_header_groups, $this->arr_header_groups_data, $arr_head_group_parent_index);
	}
	
	//add columns
	protected function table_columns(){
		//$bool_cols_added = TRUE; //set to true so that if there are no columns to add, the rest of the report can be written
		$arr_col_vals = $this->input->post('column');
		$arr_aggregate_vals = $this->input->post('aggregate');
		$arr_header_group_index_vals = $this->input->post('col_head_group_index');
//var_dump($arr_col_vals);
//var_dump($this->arr_header_groups_data);
		if(isset($arr_col_vals) && is_array($arr_col_vals)){
			$arr_column_data = array();
			$cnt = 1;
			foreach($arr_col_vals as $k=>$v){
				if(strpos($k, '_') === FALSE) $field_id = $k;
				else{
					$arr_tmp = explode('_', $k);
					$field_id = $arr_tmp[1];
				}
				$cnt_header_group_rows = count($this->arr_header_groups_data);
				$aggregate_val = !empty($arr_aggregate_vals[$k]) ? $arr_aggregate_vals[$k] : NULL;
				if($field_id > 0){
					$arr_column_data[] = array(
						'block_id' => $this->block_id
						,'field_id' => $field_id
						,'aggregate' => $aggregate_val
						,'list_order' => $cnt
						,'display' => 1 //bit field, yes or no
						,'block_header_group_id' => $cnt_header_group_rows > 0 ? $this->arr_header_groups_data[$cnt_header_group_rows][$arr_header_group_index_vals[$k]]['id'] : NULL
						,'user_id' => $this->block_user_id
						,'header_text' => $v
						//,'break' => true
					);
					if($arr_aggregate_vals[$k] != NULL) $this->arr_aggregate_columns[$k] = $arr_aggregate_vals[$k];
					$this->arr_columns[$k] = $v;
					$cnt++;
				}
			}
			return $this->custom_report_model->add_columns($arr_column_data);
		}
		return false;
	}
	
	//chart columns
	protected function trend_columns(){
		//$bool_cols_added = TRUE; //set to true so that if there are no columns to add, the rest of the report can be written
		$arr_col_vals = $this->input->post('trendcolumn');
		$arr_aggregate_vals = $this->input->post('trendaggregate');
		$arr_chart_type = $this->input->post('trendgraph_type');
		$arr_axis_index_vals = $this->input->post('trendyaxis');
		if(isset($arr_col_vals) && is_array($arr_col_vals)){
			$arr_column_data = array();
			$cnt = 1;
			foreach($arr_col_vals as $k=>$v){
				if(strpos($k, '_') === FALSE) $field_id = $k;
				else{
					$arr_tmp = explode('_', $k);
					$field_id = $arr_tmp[1];
				}
				if($field_id > 0){
					$arr_column_data[] = array(
						'block_id' => $this->block_id
						,'field_id' => $field_id
						,'aggregate' => $arr_aggregate_vals[$k]
						,'chart_type_id' => $arr_chart_type[$k]
						,'list_order' => $cnt
						,'display' => 1 //bit field, yes or no
						,'user_id' => $this->block_user_id
						,'axis_index' => $arr_axis_index_vals[$k]
						,'header_text' => $v
					);
					if($arr_aggregate_vals[$k] != NULL) $this->arr_aggregate_columns[$k] = $arr_aggregate_vals[$k];
					$this->arr_columns[$k] = $v;
					$cnt++;
				}
			}
			return $this->custom_report_model->add_columns($arr_column_data);
		}
		return false;
	}
	
	//yaxes
	protected function yaxes(){
		$arr_yaxes_vals = $this->input->post('yaxis_label');
		$arr_yaxes_min = $this->input->post('yaxis_min');
		$arr_yaxes_max = $this->input->post('yaxis_max');
		$arr_yaxes_vals = $this->input->post('yaxis_label');
		$arr_yaxes_opposite = $this->input->post('yaxes_opposite');
		if(isset($arr_yaxes_vals) && is_array($arr_yaxes_vals)){
			$arr_yaxes_data = array();
			foreach($arr_yaxes_vals as $k=>$v){
				if($k >= 0){
					$cnt = 1;
					$arr_yaxes_data[] = array(
						'block_id' => $this->block_id
						,'x_or_y' => 'y'
						//,'db_field_id' => $k
						,'text' => $v
						,'min' => $arr_yaxes_min[$k]
						,'max' => $arr_yaxes_max[$k]
						,'opposite' => $arr_yaxes_opposite[$k]
						,'list_order' => "$k"
					);
					$cnt++;
				}
			}
			return $this->custom_report_model->add_yaxes($arr_yaxes_data);
		}
		return false;
	}
	
	//xaxis
	protected function xaxis(){
		$arr_xaxis_data[] = array(
			'block_id' => $this->block_id
			,'x_or_y' => 'x'
			,'db_field_id' => $this->input->post('xaxis_field')
			,'text' => $this->input->post('xaxis_label')
			,'data_type' => $this->input->post('xaxis_datatype')
		);
		return $this->custom_report_model->add_xaxis($arr_xaxis_data);
	}
	
	//group by
	protected function group_by(){
		if(is_array($this->arr_aggregate_columns) && !empty($this->arr_aggregate_columns)){
			$arr_group_by_fields = array_diff_key($this->arr_columns, $this->arr_aggregate_columns);
			if(is_array($arr_group_by_fields) && !empty($arr_group_by_fields)){
				$arr_group_by_data = array();
				$cnt = 1;
				foreach($arr_group_by_fields as $k=>$v){
					if($k > 0){
						$arr_group_by_data[] = array(
							'block_id' => $this->block_id
							,'field_id' => $k
							,'list_order' => $cnt
						);
						$cnt++;
					}
				}
				return $this->custom_report_model->add_group_by($arr_group_by_data);
			}
		}
		return false;
	}
	
	//sort by
	protected function sort_by(){
		$arr_sort_order_vals = $this->input->post('sort_order');
		if(isset($arr_sort_order_vals) && is_array($arr_sort_order_vals)){
			foreach($arr_sort_order_vals as $k=>$v){
				if($k > 0){
					$arr_sort_by_data = array();
					$cnt = 1;
					$arr_sort_by_data[] = array(
						'block_id' => $this->block_id
						,'field_id' => $k
						,'sort_order' => isset($arr_sort_order_vals[$k]) ? $arr_sort_order_vals[$k] : 'ASC' //if no value, default to ASC
						,'list_order' => $cnt
					);
					$cnt++;
				}
			}
			return $this->custom_report_model->add_sort_by($arr_sort_by_data);
		}
		return false;
	}

/* HELPER FUNCTIONS */
	protected function add_header_group($arr_header_groups, &$new_arr_header_groups, $arr_head_group_parent_index, $parent_key = NULL){
		foreach($arr_header_groups as $k => $v){
			if(is_array($v)){
				$key_param = ($parent_key) ? $parent_key . '-' . $k : $k;
				$this->add_header_group($v, $new_arr_header_groups, $arr_head_group_parent_index, $key_param);
			}
			else {
				if($v != 'Enter text here to add a header grouping' && $v != '') {
					$parent_array_index = isset($arr_head_group_parent_index[$parent_key]) ? $arr_head_group_parent_index[$parent_key][$k] : NULL;
					$parent_id = isset($new_arr_header_groups[($parent_key - 1)]) ? $new_arr_header_groups[($parent_key - 1)][$parent_array_index]['id'] : NULL;
					$arr_header_group_data = array(
						'parent_id' => $parent_id,
						'text' => $v,
						'list_order' => $k
					);
					$arr_header_group_data['id'] = $this->custom_report_model->add_header_group($arr_header_group_data);
					if($parent_key) $new_arr_header_groups[$parent_key][$k] = $arr_header_group_data;
					else $new_arr_header_groups[$k] = $arr_header_group_data;
				}
			}
		}
	}
}
				