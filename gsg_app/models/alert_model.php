<?php
class Alert_model extends CI_Model {
	protected $arr_fields;
	protected $arr_pdf_widths;
	protected $arr_sort_fields;
	protected $unsortable_columns;
	protected $tables;
	
	public function __construct()
	{
		parent::__construct();
		$this->db_group_name = 'alert';
		$this->unsortable_columns = array();

		$this->{$this->db_group_name} = $this->load->database($this->db_group_name, TRUE);
		$this->unsortable_columns = array('BARN_NAME', 'VISID', 'LIST_NUM', 'STRING', 'LAC_NUM', 'DIM', 'MILK', 'MLM', 'FAT', 'PRO', 'MUN', 'SCC', 'PERC_CELLS', 'CUMM_PERC');
	}
	
	/**
	 * @method get_fields()
	 * @return array (label => DB field name)
	 * @author Chris Tranel
	 **/
	function get_fields(){
		$this->arr_fields = Array(
			'Barn Name'=>'BARN_NAME',
			'Vis ID'=>'VISID',
			'List #'=>'LIST_NUM',
			'Pen'=>'STRING',
			'Lac #'=>'LAC_NUM',
			'DIM'=>'DIM',//CALCULATED (TO_DAYS(TEST_DATE) - TO_DAYS(CALV_DT))
			'Milk'=>'MILK',
			'MLM'=>'MLM',
			'Fat'=>'FAT',
			'Pro'=>'PRO',
			'MUN'=>'MUN',
			'SCC'=>'SCC',
			'% Cells'=>'PERC_CELLS', //CALCULATED highest 20: @cell_perc:=ROUND(((MILK * SCC)/(SELECT SUM(MILK * SCC) FROM hotsheet WHERE HERD_NUM = '35450173'))*100) AS PERC_CELLS
			'Cum %'=>'CUMM_PERC' //CALCULATED highest 20: @running_sum:=@running_sum+@cell_perc AS CUMM_PERC
			);
		return $this->arr_fields;
	}

	/**
	 * @method get_pdf_widths()
	 * @return array (field name => PDF Column Width)
	 * @author Chris Tranel
	 **/
	function get_pdf_widths(){
		$this->arr_pdf_widths = array(
			'BARN_NAME' => 15,
			'VISID' => 15,
			'LIST_NUM' => 15,
			'STRING' => 11,
			'LAC_NUM' => 10,
			'DIM' => 13,
			'MILK' => 13,
			'MLM' => 10,
			'FAT' => 13,
			'PRO' => 13,
			'MUN' => 10,
			'SCC' => 13,
			'PERC_CELLS' => 10,
			'CUMM_PERC' => 10
		);
		return $this->arr_pdf_widths;
	}
	
	/**
	 * @method get_field_sort()
	 * @return array (field name => sort order)
	 * @author Chris Tranel
	 **/
	function get_field_sort(){
		$this->arr_sort_fields = array(
			'BARN_NAME' => 'DESC',
			'VISID' => 'DESC',
			'LIST_NUM' => 'DESC',
			'STRING' => 'DESC',
			'LAC_NUM' => 'DESC',
			'DIM' => 'DESC',
			'MILK' => 'DESC',
			'MLM' => 'DESC',
			'FAT' => 'DESC',
			'PRO' => 'DESC',
			'MUN' => 'DESC',
			'SCC' => 'DESC',
			'PERC_CELLS' => 'DESC',
			'CUMM_PERC' => 'DESC'
		);
		return $this->arr_sort_fields;
	}
		
	/**
	 * @method search()
	 * @param array fields to include
	 * @param array filter criteria
	 * @param array sort by
	 * @param array sort order
	 * @param array fields sort (field name =>  default sort order)
	 * @return array results of search
	 * @author Chris Tranel
	 **/
	function search($herd_code, $arr_fields, $arr_filter_criteria, $arr_sort_by, $arr_sort_order, $arr_field_sort) {
		if(is_array($arr_fields)){
			// resolve field name/data/format exceptions
			if (($key = array_search('DIM', $arr_fields)) !== FALSE) {
				$arr_fields[$key] = '(TO_DAYS(TEST_DATE) - TO_DAYS(CALV_DT)) AS DIM';
			}
			if (($key = array_search('PERC_CELLS', $arr_fields)) !== FALSE) {
				$arr_fields[$key] = "@cell_perc:=ROUND(((MILK * SCC)/(SELECT SUM(MILK * SCC) FROM hotsheet WHERE HERD_NUM = '" . $herd_code . "'))*100) AS PERC_CELLS";
			}
			if (($key = array_search('CUMM_PERC', $arr_fields)) !== FALSE) {
				$arr_fields[$key] = '@running_sum:=@running_sum+@cell_perc AS CUMM_PERC';
			}
		}
		//set variable to be used in the query
		$fields = is_array($arr_fields)?implode(', ', $arr_fields):'*';

		//SORT
		// null items should be last for items whose 
		$arr_len = is_array($arr_sort_by)?count($arr_sort_by):0;
		for($c=0; $c<$arr_len; $c++) {
			$sort_order = ($arr_sort_order[$c] == 'DESC') ? 'DESC' : 'ASC';
			//$sort_by = (!in_array($arr_sort_by[$c], $unsortable_columns)) ? $arr_sort_by[$c] : 'net_merit_amt';
			if(!empty($arr_sort_by[$c])){
				if($arr_sort_by[$c] == 'section'){
					$this->{$this->db_group_name}->order_by('section', $sort_order);
				}
				else {
					$this->{$this->db_group_name}->order_by($arr_sort_by[$c], $sort_order);
				}
			}
		}

		// set up the query variables
		$this->{$this->db_group_name}->query('SET @running_sum =0, @cell_perc =0');
		$this->{$this->db_group_name}->select($fields, FALSE)
			->from('hotsheet')->where('HERD_NUM', $herd_code)
			->limit(20, 0);
		$ret['rows'] = $this->{$this->db_group_name}->get();
		$ret['unsortable_columns'] = $this->unsortable_columns;
		return $ret;
	}

	/**
	 * @method header_info()
	 * @param string herd code
	 * @return array of data for the herd header record
	 * @access public
	 *
	 **/
	function header_info($herd_code){
		// results query
		$q = $this->{$this->db_group_name}->select("herd_code, farm_name, herd_owner, association_num, FORMAT(test_date,'MM-dd-yyyy') AS test_date, weighted_scc_avg", FALSE)
		->from('v_herds')
		->where('herd_code',$herd_code);
		$ret['rows'] = $q->get()->result();
		return $ret;
	} //end function

	/**
	 * @method get_graph_data()
	 * @param string herd code
	 * @return array of data for the alert graph
	 * @access public
	 *
	 **/
	function get_graph_data($herd_code){
		$data = $this->{$this->db_group_name}->select('(ROUND((DATEDIFF(TEST_DATE, `CALV_DT`) / 50), 0) * 50) AS DIM, ROUND(AVG(`MILK`)) AS MILK, ROUND(SUM(`SCC` * MILK) / SUM(MILK) ) AS SCC, COUNT(*) AS NUM_ANIMALS', FALSE)
		//$this->{$this->db_group_name}->select('DATEDIFF(TEST_DATE, CALV_DT) AS DIM, MILK, SCC', FALSE)
		->where('DATEDIFF(TEST_DATE, `CALV_DT`) IS NOT NULL')
		->where('SCC > 0')
		->where('MILK > 0')
		->where('HERD_NUM', $herd_code)
		->group_by('DIM')
		->get('hotsheet')->result_array();

//print_r($data);
			foreach($data as $d){ //each($tmp_array as $k=>$d){
				$arr_return[0][] = array((float)$d['DIM'], (float)$d['MILK']);
				$arr_return[1][] = array((float)$d['DIM'], (float)$d['SCC']);
				$arr_return[2][] = array((float)$d['DIM'], (float)$d['NUM_ANIMALS']);
			}
			return $arr_return;
	}	
}
