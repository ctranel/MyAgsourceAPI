<?php

namespace myagsource\Report\Content;

//require_once APPPATH . 'libraries/Datasource/DbObjects/DbTable.php';
require_once APPPATH . 'libraries/Datasource/DbObjects/DbField.php';
require_once APPPATH . 'libraries/Report/Content/TableField.php';
require_once APPPATH . 'libraries/Report/Content/Block.php';

//use \myagsource\Datasource\DbObjects\DbTable;
use \myagsource\Datasource\DbObjects\DbField;
use \myagsource\Report\Content\TableField;
use \myagsource\Report\Content\Block;

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
			$sum_row, $avg_row, $bench_row, $is_summary, $display_type, \myagsource\Supplemental\Content\SupplementalFactory $supp_factory = null) {
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
	public function setReportFields(\myagsource\Supplemental\Content\SupplementalFactory $supp_factory = null){
		$this->report_fields = new \SplObjectStorage();
			
		$arr_ret = array();
		$arr_res = $this->datasource->getFieldData($this->id);
		if(is_array($arr_res)){
			$header_supp = null;
			$data_supp = null;
			foreach($arr_res as $s){
				if(isset($supp_factory)){
					$header_supp = $supp_factory->getColHeaderSupplemental($s['id'], $s['head_a_href'], $s['head_a_rel'], $s['head_a_title'], $s['head_a_class'], $s['head_comment']);
					$data_supp = $supp_factory->getColDataSupplemental($s['id'], $s['a_href'], $s['a_rel'], $s['a_title'], $s['a_class']);
				}
				$datafield = new DbField($s['db_field_id'], $s['table_name'], $s['db_field_name'], $s['name'], $s['description'], $s['pdf_width'], $s['default_sort_order'],
						 $s['datatype'], $s['max_length'], $s['decimal_scale'], $s['unit_of_measure'], $s['is_timespan'], $s['is_foreign_key'], $s['is_nullable'], $s['is_natural_sort']);
				$this->report_fields->attach(new TableField($s['id'], $s['name'], $datafield, $header_supp, $data_supp, $s['displayed']));
			}
		}
		

				
/*				//column data
				$block_supp = Supplemental::getColDataSupplemental($fd['bsf_id'], $this->supplemental_model, site_url());
				$this->arr_field_links[$fn] = $block_supp->getContent();
				//add fields included in the supplemental parameters to the field list used for composing select queries (not displayed)
				foreach($block_supp->supplementalLinks() as $s){
					foreach($s->params() as $p){
						if(!in_array($p->value_db_field_name(), $this->arr_db_field_list)){
							$this->arr_db_field_list[] = $p->value_db_field_name();			
						}
					}
				}
				//column header
				$block_supp = Supplemental::getColHeaderSupplemental($fd['bsf_id'], $this->supplemental_model, site_url());
				$this->arr_header_links[$fn] = $block_supp->getContent();
*/
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \myagsource\Site\iWebContent::children()
	 *
	 */
	protected function loadData($report_count){
		//$this->{$this->primary_model_name}->populate_field_meta_arrays($arr_this_block['id']);
		$arr_field_list = $this->{$this->primary_model_name}->get_fieldlist_array();
		$results = $this->{$this->primary_model_name}->search($this->session->userdata('herd_code'), $arr_this_block['path'], $this->filters->criteriaKeyValue(), $this->arr_sort_by, $this->arr_sort_order, $this->max_rows);
		if($this->bench_row){
		//if the data is pivoted, set the pivoted field as the row header, else use the first non-pstring column
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
			$this->load->model('benchmark_model');
			$this->load->model('db_table_model');
			$this->load->model('setting_model');
			$herd_info = $this->herd_model->header_info($this->herd_code);
			$this->benchmarks_lib = new Benchmarks_lib($this->session->userdata('user_id'), $this->input->post('herd_code'), $herd_info, $this->setting_model, $this->session->userdata('benchmarks'));
			$this->db_table = new DbTable($this->{$this->primary_model_name}->get_primary_table_name(), $this->db_table_model);
			//$sess_benchmarks = $this->session->userdata('benchmarks');
			$arr_group_by = $this->{$this->primary_model_name}->get_group_by_fields($arr_this_block['id']);
//			$arr_group_by = array_filter($arr_group_by);
			$arr_bench_data = $this->benchmarks_lib->addBenchmarkRow(
					$this->db_table,
					$this->session->userdata('benchmarks'),
					$this->benchmark_model,
					$row_head_field,
					$arr_field_list,
					$this->{$this->primary_model_name}->get_group_by_fields($arr_this_block['id'])
				);
			if(count($arr_bench_data) > 1){
			/*
			 * @todo: if block_group_by isset (i.e., there are multiple rows of benchmarks), need to iterate through result set and place benchmark rows in correct spots.
			 * 	(i.e., when the value of the group_by field changes, insert the benchmark row that matches the previous value in the group by field)
			 */
			}
			else{
				$results[] = $arr_bench_data[0];
			}
		}
		if(!empty($this->pivot_db_field)){
			$results = $this->{$this->primary_model_name}->pivot($results, $this->pivot_db_field, 10, 10, $this->avg_row, $this->sum_row);
		}
		
		$tmp = array(
			'form_id' => $this->report_form_id,
			'report_path' => $this->report_path,
			'arr_sort_by' => $this->arr_sort_by,
			'arr_sort_order' => $this->arr_sort_order,
			'block' => $arr_this_block['path'],
			'report_count' => $report_count
		);
		$tmp2 = $this->{$this->primary_model_name}->get_table_header_data();
		$table_header_data = array_merge($tmp, $tmp2);
		if(isset($this->benchmarks_lib)){
			$bench_text = $this->benchmarks_lib->get_bench_text($this->session->userdata('benchmarks'));
		}
		$this->report_data = array(
			'table_header' => $this->load->view('table_header', $table_header_data, TRUE),
			'num_columns' => $table_header_data['num_columns'],
			'table_id' => $arr_this_block['path'],
			'fields' => $this->{$this->primary_model_name}->get_fieldlist_array(),
			'report_data' => $results,
			'table_heading' => $title,
			'table_sub_heading' => $subtitle,
			'arr_numeric_fields' => $this->{$this->primary_model_name}->get_numeric_fields(),
			'arr_decimal_places' => $this->{$this->primary_model_name}->get_decimal_places(),
			'arr_field_links' => $this->{$this->primary_model_name}->get_field_links(),
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
}



?>