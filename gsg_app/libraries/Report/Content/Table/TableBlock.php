<?php

namespace myagsource\Report\Content\Table;

//require_once APPPATH . 'libraries/Datasource/DbObjects/DbTable.php';
require_once APPPATH . 'libraries/Datasource/DbObjects/DbField.php';
require_once APPPATH . 'libraries/Report/Content/Table/TableField.php';
require_once APPPATH . 'libraries/Report/Content/Block.php';

//use \myagsource\Datasource\DbObjects\DbTable;
use \myagsource\Datasource\DbObjects\DbField;
use \myagsource\Report\Content\Table\TableField;
use \myagsource\Report\Content\Block;
use \myagsource\Supplemental\Content\SupplementalFactory;

/**
 * Name:  TableBlock
 *
 * Author: ctranel
 *
 * Created:  02-03-2015
 *
 * Description:  Contains properties and methods specific to displaying table blocks of the website.
 *
 */
class TableBlock extends Block {
	
	/**
	 */
	function __construct($block_datasource, $id, $page_id, $name, $description, $scope, $active, $path, $max_rows, $cnt_row, 
			$sum_row, $avg_row, $bench_row, $is_summary, $display_type, SupplementalFactory $supp_factory = null) {
		parent::__construct($block_datasource, $id, $page_id, $name, $description, $scope, $active, $path, $max_rows, $cnt_row, 
			$sum_row, $avg_row, $bench_row, $is_summary, $display_type, $supp_factory);
	}
	
	/**
	 * setReportFields
	 * 
	 * Sets the datafields property of datafields that are to be included in the block
	 * 
	 * @method setReportFields()
	 * @return void
	 * @access public
	 **/
	public function setReportFields(SupplementalFactory $supp_factory = null){
		$arr_table_ref_cnt = [];
		$this->has_aggregate = false;
		$this->report_fields = new \SplObjectStorage();
			
		$arr_ret = array();
		$arr_res = $this->datasource->getFieldData($this->id);
		if(is_array($arr_res)){
			foreach($arr_res as $s){
				$header_supp = null;
				$data_supp = null;
				if(isset($s['aggregate']) && !empty($s['aggregate'])){
					$this->has_aggregate = true;
				}
				if(isset($supp_factory)){
					if(isset($s['head_supp_id'])){
						$header_supp = $supp_factory->getColHeaderSupplemental($s['head_supp_id'], $s['head_a_href'], $s['head_a_rel'], $s['head_a_title'], $s['head_a_class'], $s['head_comment']);
					}
					if(isset($s['supp_id'])){
						$data_supp = $supp_factory->getColDataSupplemental($s['supp_id'], $s['a_href'], $s['a_rel'], $s['a_title'], $s['a_class']);
					}
				}
				$arr_table_ref_cnt[$s['table_name']] = isset($arr_table_ref_cnt[$s['table_name']]) ? ($arr_table_ref_cnt[$s['table_name']] + 1) : 1;
				$datafield = new DbField($s['db_field_id'], $s['table_name'], $s['db_field_name'], $s['name'], $s['description'], $s['pdf_width'], $s['default_sort_order'],
						 $s['datatype'], $s['max_length'], $s['decimal_scale'], $s['unit_of_measure'], $s['is_timespan'], $s['is_foreign_key'], $s['is_nullable'], $s['is_natural_sort']);
				$this->report_fields->attach(new TableField($s['id'], $s['name'], $datafield, $s['is_displayed'], $s['display_format'], $s['aggregate'], $s['is_sortable'], $header_supp, $data_supp, $s['block_header_group_id']));
			}
			$this->primary_table_name = array_search(max($arr_table_ref_cnt), $arr_table_ref_cnt);
			//set up arr_fields hierarchy
			if(is_array($arr_table_ref_cnt) && count($arr_table_ref_cnt) >  1){
				foreach($arr_table_ref_cnt as $t => $cnt){
					if($t != $this->primary_table_name){
						$this->joins[] = array('table'=>$t, 'join_text'=>$this->get_join_text($this->primary_table_name, $t));
					}
				}
			}
		}
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \myagsource\Site\iWebContent::children()
	 *
	public function loadData($report_count){
		//$report_datasource->populate_field_meta_arrays($arr_this_block['id']);
		$arr_field_list = $this->getFieldlistArray();
		$results = $report_datasource->search($this->session->userdata('herd_code'), $arr_this_block['path'], $this->filters->criteriaKeyValue(), $this->arr_sort_by, $this->arr_sort_order, $this->max_rows);
		if($this->bench_row){
		//if the data is pivoted, set the pivoted field as the row header, else use the first non-pstring column
			$row_head_field = $this->getRowHeadField($arr_field_list);
			

			$this->load->model('db_table_model');
			$this->load->model('setting_model');
			$herd_info = $this->herd_model->header_info($this->herd_code);
			$this->benchmarks = new Benchmarks($this->session->userdata('user_id'), $this->input->post('herd_code'), $herd_info, $this->setting_model, $this->benchmark_model, $this->session->userdata('benchmarks'));
			$this->db_table = new DbTable($report_datasource->get_primary_table_name(), $this->db_table_model);
			//$sess_benchmarks = $this->session->userdata('benchmarks');

			
			$arr_group_by = $report_datasource->get_group_by_fields($arr_this_block['id']);
//			$arr_group_by = array_filter($arr_group_by);
			$arr_bench_data = $this->benchmarks->addBenchmarkRow(
					$this->db_table,
					$this->session->userdata('benchmarks'),
					$row_head_field,
					$arr_field_list,
					$report_datasource->get_group_by_fields($arr_this_block['id'])
				);
			if(count($arr_bench_data) > 1){
			
			 // @todo: if block_group_by isset (i.e., there are multiple rows of benchmarks), need to iterate through result set and place benchmark rows in correct spots.
			 // (i.e., when the value of the group_by field changes, insert the benchmark row that matches the previous value in the group by field)
			 
			}
			else{
				$results[] = $arr_bench_data[0];
			}
		}
		if(!empty($this->pivot_db_field)){
			$results = $report_datasource->pivot($results, $this->pivot_db_field, 10, 10, $this->avg_row, $this->sum_row);
		}
		
		$tmp = array(
			'form_id' => $this->report_form_id,
			'report_path' => $this->report_path,
			'arr_sort_by' => $this->arr_sort_by,
			'arr_sort_order' => $this->arr_sort_order,
			'block' => $arr_this_block['path'],
			'report_count' => $report_count
		);
		$tmp2 = $report_datasource->get_table_header_data();
		$table_header_data = array_merge($tmp, $tmp2);
		if(isset($this->benchmarks)){
			$bench_text = $this->benchmarks->get_bench_text($this->session->userdata('benchmarks'));
		}
		$this->report_data = array(
			'table_header' => $this->load->view('table_header', $table_header_data, TRUE),
			'num_columns' => $table_header_data['num_columns'],
			'table_id' => $arr_this_block['path'],
			'fields' => $report_datasource->get_fieldlist_array(),
			'report_data' => $results,
			'table_heading' => $title,
			'table_sub_heading' => $subtitle,
			'arr_numeric_fields' => $report_datasource->get_numeric_fields(),
			'arr_decimal_places' => $report_datasource->get_decimal_places(),
			'arr_field_links' => $report_datasource->get_field_links(),
		);
		
		if(isset($this->supplemental) && !empty($this->supplemental)){
			$this->report_data['supplemental'] = $this->supplemental;
		}
		
		if(isset($bench_text)){
			$this->report_data['table_benchmark_text'] = $bench_text;
		}

		if(isset($this->report_data) && is_array($this->report_data)) {
			$this->html = $this->load->view('report_table.php', $this->report_data, TRUE);
		}
		else {
			$this->html = '<p class="message">No data found.</p>';
		}
		$this->display = 'table';
	}
	*/
/*	
	protected function getRowHeadField($arr_field_list){
		$row_head_field = NULL;
		if(!empty($this->pivot_db_field)){
			$row_head_field = $this->pivot_db_field;
		}
		else{
			foreach($arr_field_list as $fl){
				if($fl != 'pstring'){
					$row_head_field = $fl;
					break;
				}
			}
		}
		
		return $row_head_field;
	}
*/
}


?>