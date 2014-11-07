<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH . 'libraries' . FS_SEP . 'supplemental' . FS_SEP . 'Supplemental.php');

use \myagsource\supplemental\Supplemental;

/* -----------------------------------------------------------------
*  @description: Base data access for database-driven report generation
*  @author: ctranel
*  -----------------------------------------------------------------
*/
class Report_model extends CI_Model {
	protected $section_id;
	public $arr_tables;
	protected $test_date;
	protected $arr_field_table;//DB field name is key
	protected $arr_joins = array(); //DB 2 dimensional: 'table' and 'join_text'  TO BE REPLACED BY USING db_table_id FIELD AND is_fk_field IN DB_FIELD TABLE
	protected $primary_table_name;
 	public $date_field;
 	protected $herd_code;
	protected $arr_fields = array(); //DB list of fields structured to mimic the header, field label is key
	protected $arr_db_field_list = array(); //DB list of fields in flat array, numeric key
	protected $arr_unsortable_columns = array(); //CODE list of fields
	protected $arr_natural_sort_fields = array(); //DB list of fields
	protected $arr_date_fields = array(); //DB list of fields
	protected $arr_datetime_fields = array(); //DB list of fields
	protected $arr_timespan_fields = array(); //DB list of fields
	protected $arr_numeric_fields = array(); //DB list of fields
	protected $arr_field_sort = array(); //DB field name is key, default sort order
	protected $arr_pdf_widths = array(); //DB field name is key
	protected $arr_aggregates = array(); //numeric key, must be in same order as $arr_select_fields (array_flatten($this->arr_fields), only located in search function)
	protected $arr_chart_type = array(); //numeric key, must be in same order as $arr_select_fields (array_flatten($this->arr_fields), only located in search function)
	protected $arr_axis_index = array(); //numeric key, must be in same order as $arr_select_fields (array_flatten($this->arr_fields), only located in search function)
	protected $arr_bool_display = array(); //numeric key, must be in same order as $arr_select_fields (array_flatten($this->arr_fields), only located in search function)
	protected $arr_decimal_points = array();//DB field name is key
	protected $arr_field_links = array();//DB field name is key
	protected $arr_header_links = array();//DB field name is key
	public $arr_unit_of_measure;//DB field name is key
//	protected $arr_where_field = array();// NOT CURRENTLY USED CODE set in child classes
	protected $arr_where_operator = array();// CODE set in child classes
	protected $arr_where_criteria = array();// CODE set in child classes
	protected $arr_group_by_field = array();// CODE set in child classes
	protected $arr_auto_filter_field = array(); //add a criteria if >1000 records are returned with existing criteria
	protected $arr_auto_filter_operator = array(); //add a criteria if >1000 records are returned with existing criteria
	protected $arr_auto_filter_criteria = array(); //add a criteria if >1000 records are returned with existing criteria
	protected $arr_auto_filter_alert = array();
	protected $num_results;
	public $arr_blocks = array();
	public $arr_messages = array();
	
	public function __construct($section_path = NULL){
		parent::__construct();
		$this->load->model('dhi/herd_model');
		$this->tables  = $this->config->item('tables', 'ion_auth');

		$this->db_group_name = 'default';
		$this->{$this->db_group_name} = $this->load->database($this->db_group_name, TRUE);
		if(isset($section_path)){
			$this->section_id = $this->web_content_model->get_section_id_by_path($section_path);
		}
		if(isset($this->section_id)){
			$this->arr_blocks = $this->web_content_model->get_block_links($this->section_id);
		}
	}
	function get_primary_table_name(){
		return $this->primary_table_name;
	}
	function get_fields(){
		return $this->arr_fields;
	}
	function get_decimal_places(){
		return $this->arr_decimal_points;
	}
	function get_numeric_fields(){
		return $this->arr_numeric_fields;
	}
	function get_field_links(){
		return $this->arr_field_links;
	}
	function get_fieldlist_array(){
		return $this->arr_db_field_list;
	}
	function get_pdf_widths(){
		return $this->arr_pdf_widths;
	}
	function get_field_sort(){
		return $this->arr_field_sort;
	}
	function get_field_table(){
		return $this->arr_field_table;
	}
	function get_unsortable_columns(){
		return $this->arr_unsortable_columns;
	}
	function get_chart_type_array(){
		return $this->arr_chart_type;
	}
	function get_axis_index_array(){
		return $this->arr_axis_index;
	}
	function get_num_results(){
		return $this->num_results;
	}
	function get_section_id(){
		return $this->section_id;
	}
	function get_current_breed_code(){
		return current($this->arr_breeds);
	}
	
	function set_primary_table($table_name){
		$this->primary_table_name = $table_name;
	}
	
	function add_field($arr_field_in){
		$this->arr_fields[] = $arr_field_in;
	}
	function add_sort_field($key, $value = 'ASC'){
		$this->arr_field_sort[$key] = $value;
	}
	
	function add_unsortable_column($column){
		$this->arr_unsortable_columns[] = $column;
	}

	/**
	 * @method get_default_sort()
	 * @param string block url segment
	 * @return returns multi-dimensional array, arr_sort_by and arr_sort_order
	 * @author ctranel
	 **/
	function get_default_sort($block_url_segment){
		$arr_ret = array();
		$arr_res = $this->{$this->db_group_name}
//			->distinct()
			->select('users.dbo.db_fields.db_field_name, users.dbo.blocks_sort_by.sort_order')
			->where($this->tables['blocks'] . '.url_segment', $block_url_segment)
			->join('users.dbo.blocks_sort_by', $this->tables['blocks'] . '.id = users.dbo.blocks_sort_by.block_id' , 'left')
			->join('users.dbo.db_fields', 'users.dbo.blocks_sort_by.field_id = users.dbo.db_fields.id' , 'left')
			->order_by('users.dbo.blocks_sort_by.list_order', 'asc')
			->get($this->tables['blocks'])
			->result_array();
		if(is_array($arr_res)){
			foreach($arr_res as $s){
				$arr_ret['arr_sort_by'][] = $s['db_field_name'];
				$arr_ret['arr_sort_order'][] = $s['sort_order'];
			}
		}
		return $arr_ret;
	}
	
	/**
	 * @method get_group_by_fields()
	 * @param int id of current block
	 * @return array: ordered list of group by fields
	 * @author ctranel
	 **/
	function get_group_by_fields($block_id){
		$arr_ret = array();
		$arr_res = $this->{$this->db_group_name}
			->select('users.dbo.db_fields.db_field_name')
			->where($this->tables['blocks'] . '.id', $block_id)
			->join('users.dbo.blocks_group_by', $this->tables['blocks'] . '.id = users.dbo.blocks_group_by.block_id' , 'left')
			->join('users.dbo.db_fields', 'users.dbo.blocks_group_by.field_id = users.dbo.db_fields.id' , 'left')
			->order_by('users.dbo.blocks_group_by.list_order', 'asc')
			->get($this->tables['blocks'])
			->result_array();
		if(is_array($arr_res)){
			foreach($arr_res as $s){
				if(isset($s['db_field_name'])){
					$arr_ret[] = $s['db_field_name'];
				}
			}
		}
		if(empty($arr_ret)){
			return FALSE;
		}
		return $arr_ret;
	}
	
	/**
	 * @method get_table_header_data()
	 * @return multi-dimensional array of header data ('arr_unsortable_columns', 'arr_field_sort', 'arr_header_data')
	 * @author ctranel
	 **/
	function get_table_header_data(){
		$this->load->library('table_header');
		$table_header_data = array(
			'arr_unsortable_columns' => $this->arr_unsortable_columns,
			'arr_field_sort' => $this->arr_field_sort,
			'arr_header_data' => $this->arr_fields,
			'arr_header_links' => $this->arr_header_links,
		);
		$table_header_data['structure'] = $this->table_header->get_table_header_array($table_header_data['arr_header_data']);
		$table_header_data['num_columns'] = $this->table_header->get_column_count();
		return $table_header_data;
	}

	/**
	 * @method populate_field_meta_arrays()
	 * @param int id of current block
	 * @abstract populates report-specific object variable arrays (from DB)
	 * @return void
	 * @author ctranel
	 **/
	public function populate_field_meta_arrays($block_in){
		$arr_numeric_types = array('bigint','decimal','int','money','smallmoney','numeric','smallint','tinyint','float','real');
		$arr_field_child = array();
		$arr_table_ref_cnt = array();
		$this->arr_group_by_field = $this->get_group_by_fields($block_in);
		$arr_field_data = $this->{$this->db_group_name}
			->where('block_id', $block_in)
			->order_by('list_order')
			->get('users.dbo.v_block_field_data')
			->result_array();
		$header_data = $this->get_select_field_structure($block_in);
		if(is_array($arr_field_data) && !empty($arr_field_data)){
			foreach($arr_field_data as $fd){
				//skip over fields that are not set to display--these fields will not be available in views
				if(empty($fd['display'])) {
					continue;
				}
				$fn = $fd['db_field_name'];
				$this->arr_db_field_list[] = $fn;
				$arr_table_ref_cnt[$fd['table_name']] = isset($arr_table_ref_cnt[$fd['table_name']]) ? ($arr_table_ref_cnt[$fd['table_name']] + 1) : 1;
				$header_data['arr_order'][$fd['name']] = $fd['list_order'];
				$arr_field_child[$fd['block_header_group_id']][$fd['name']] = $fn; //used to create arr_fields nested array
				$this->arr_field_sort[$fn] = $fd['default_sort_order'];

				//supplemental data
				$this->load->model('supplemental_model');
				//column data
				$block_supp = Supplemental::getColDataSupplemental($fd['bsf_id'], $this->supplemental_model, site_url());
				$this->arr_field_links[$fn] = $block_supp->getContent();
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
				//end supplemental
				
				$this->arr_pdf_widths[$fn] = $fd['pdf_width'];
				$this->arr_aggregates[] = $fd['aggregate'];
				$this->arr_decimal_points[$fn] = $fd['decimal_points'];
				$this->arr_field_table[$fn] = $fd['table_name'];
				if(strpos($fd['data_type'], 'date') !== FALSE && strpos($fn, 'time') !== FALSE) $this->arr_datetime_fields[] = $fn;
				elseif(strpos($fd['data_type'], 'date') !== FALSE) $this->arr_date_fields[] = $fn;
				if($fd['is_nullable'] === FALSE) $arr_notnull_fields[] = $fn;
				if(in_array($fd['data_type'], $arr_numeric_types)) $this->arr_numeric_fields[] = $fn;
				if($fd['is_natural_sort']) $this->arr_natural_sort_fields[] = $fn;
			}
		}
		$this->primary_table_name = array_search(max($arr_table_ref_cnt), $arr_table_ref_cnt);
		//set up arr_fields hierarchy
		$this->arr_fields = $header_data['arr_fields'];
		//add actual field names to header hierarchy
		 $tmp = key($arr_field_child);
		 if(!empty($tmp)){
		 	$this->load->helper('multid_array_helper');
		 	$this->arr_fields = merge_arrays_on_value_key_match($this->arr_fields, $arr_field_child);
		 }
		 else($this->arr_fields = $arr_field_child);

/*		KEEPING THIS AROUND IN CASE THE NEED FOR THE 'arr_order' FUNCTIONALITY ARISES
		if(is_array($arr_field_child) && !empty($arr_field_child)){
			foreach($arr_field_child as $k=>$fc){
				// individually insert each field that does not have a parent
				if(empty($k)){
					foreach($fc as $k1=>$fc1){
						$tmp = isset($header_data['arr_ref'][$k1]) ? $header_data['arr_ref'][$k1] : NULL;
						set_element_by_key($this->arr_fields, $tmp, array($k1 => $fc1), $header_data['arr_order']);
					} 
				}
				else{
					set_element_by_key($this->arr_fields, $header_data['arr_ref'][$k], $fc, $header_data['arr_order']);
				}
			}
		} */
		if(is_array($arr_table_ref_cnt) && count($arr_table_ref_cnt) >  1){
			foreach($arr_table_ref_cnt as $t => $cnt){
				if($t != $this->primary_table_name){
					$this->arr_joins[] = array('table'=>$t, 'join_text'=>$this->get_join_text($this->primary_table_name, $t));
				}
			}
		}
//		$this->adjust_fields($this->session->userdata('herd_code'));
	}
	
	/**
	 * @method get_select_field_structure()
	 * @param int id of current block
	 * @abstract returns block (i.e., table) header structure which provides a skeleton for the organization of fields in the arr_fields object variable
	 * 				also 
	 * @return array: ref = lookup array for ids, arr_fields = skeleton structure for db_fields
	 * @author ctranel
	 **/
	protected function get_select_field_structure($block_in){
		$arr_fields = array();
		$arr_ref = array();
		$arr_order = array();
		
		$grouping_sql = "WITH cteAnchor AS (
					 SELECT bh.id, bh.[text], bh.parent_id, bh.list_order
					 FROM users.dbo.block_header_groups bh
					 	LEFT JOIN users.dbo.blocks_select_fields bs ON bh.id = bs.block_header_group_id
					 WHERE block_id = " . $block_in . "
				), cteRecursive AS (
					SELECT id, [text], parent_id, list_order
					  FROM cteAnchor
					 UNION all 
					 SELECT t.id, t.[text], t.parent_id, t.list_order
					 FROM users.dbo.block_header_groups t
					 join cteRecursive r ON r.parent_id = t.id
				)
				SELECT DISTINCT * FROM cteRecursive ORDER BY parent_id, list_order;";

		$arr_groupings = $this->{$this->db_group_name}->query($grouping_sql)->result_array();
			
		if(!is_array($arr_groupings) || empty($arr_groupings)){
			$arr_groupings = $this->{$this->db_group_name}
				->query("SELECT 1 AS id, bf.header_text AS text, NULL AS parent_id, bf.list_order
				FROM users.dbo.blocks_select_fields bf
					LEFT JOIN users.dbo.db_fields f ON bf.field_id = f.id
				WHERE bf.block_id = " . $block_in
			)->result_array();
		}

		//KLM - Added logic to convert header text to date text from herd_model function get_test_dates_7_short
		if(is_array($arr_groupings) && !empty($arr_groupings)){
			$arr_dates = $this->herd_model->get_test_dates_7_short($this->session->userdata('herd_code'));
			foreach($arr_groupings as &$ag){
				$arr_ref[$ag['id']] = (string)$ag['text'];
				$arr_order[(string)$ag['text']] = $ag['list_order'];
				$c = 0;
				if(isset($arr_dates) && is_array($arr_dates)){
					foreach($arr_dates[0] as $key => $value){
						if ($key == $ag['text']) {
							if ($value == '0-0') {
								$value='No Test (-'.$c.')';
							}
							$ag['text'] = $value;
							break;
						}
						$c++;
					}
				}
			}
			unset($ag);
		
			foreach($arr_groupings as $h){
				$h['text'] = (string)$h['text'];
				if($h['parent_id'] == NULL) {
					$arr_fields[$h['text']] = $h['id'];
				}
				else{
					set_element_by_key($arr_fields, $arr_ref[$h['parent_id']], array((string)$h['text'] => $h['id']));
				}
			}
		}

		
		return array('arr_ref' => $arr_ref, 'arr_fields' => $arr_fields, 'arr_order' => $arr_order);
	}

	protected function get_join_text($primary_table, $join_table){
		$join_text = '';
		list($a, $b, $tmp_tbl_only) = explode('.', $primary_table);
		$arr_primary_table_fields = $this->{$this->db_group_name}
			->select('db_field_name')
			->from('users.dbo.db_fields')
			->join('users.dbo.db_tables', 'users.dbo.db_fields.db_table_id = users.dbo.db_tables.id')
			->where(array('users.dbo.db_fields.is_fk_field'=>1, 'users.dbo.db_tables.name'=>$tmp_tbl_only))
			->get()
			->result_array();
		list($a, $b, $tmp_tbl_only) = explode('.', $join_table);
		$arr_join_table_fields = $this->{$this->db_group_name}
			->select('db_field_name')
			->from('users.dbo.db_fields')
			->join('users.dbo.db_tables', 'users.dbo.db_fields.db_table_id = users.dbo.db_tables.id')
			->where(array('users.dbo.db_fields.is_fk_field'=>1, 'users.dbo.db_tables.name'=>$tmp_tbl_only))
			->get()
			->result_array();
		if(is_array($arr_primary_table_fields) && is_array($arr_join_table_fields)){
			$arr_intersect = array_intersect(array_flatten($arr_primary_table_fields), array_flatten($arr_join_table_fields));
			foreach($arr_intersect as $j){
				if(!empty($join_text)) $join_text .= ' AND ';
				$join_text .= $primary_table . '.' . $j . '=' . $join_table . '.' . $j;
			}
			return $join_text;
		}
		else return FALSE;
	}
	
	/**
	 * @method search()
	 * @param string herd code
	 * @param array filter criteria
	 * @param array sort by
	 * @param array sort order
	 * @return array results of search
	 * @author ctranel
	 **/
	function search($herd_code, $block_url, $arr_filter_criteria, $arr_sort_by = array(''), $arr_sort_order = array(''), $limit = NULL) {
		$this->load->helper('multid_array_helper');
		$this->herd_code = $herd_code;
		$this->{$this->db_group_name}->start_cache();
		$this->{$this->db_group_name}->from($this->primary_table_name);
		if(is_array($this->arr_joins) && !empty($this->arr_joins)) {
			foreach($this->arr_joins as $j){
				$this->{$this->db_group_name}->join($j['table'], $j['join_text']);
			}
		}		
		if(is_array($arr_filter_criteria) && !empty($arr_filter_criteria)){
			$this->prep_where_criteria($arr_filter_criteria, $block_url);
		}
		
		if(is_array($this->arr_fields)){
			$arr_select_fields = array_flatten($this->arr_fields);
			$arr_select_fields = $this->prep_select_fields($arr_select_fields);
			//convert dates
			if(isset($this->arr_date_fields) && !empty($this->arr_date_fields)){
				foreach($this->arr_date_fields as $d){
					if (($key = array_search($d, $arr_select_fields)) !== false) $arr_select_fields[$key] = "FORMAT(" . $d . ",'MM-dd-yyyy', 'en-US') AS '" . $d . "'";
				}
			}
			//convert times
			if(isset($this->arr_datetime_fields) && !empty($this->arr_datetime_fields)){
				foreach($this->arr_datetime_fields as $d){
					if (($key = array_search($d, $arr_select_fields)) !== false) $arr_select_fields[$key] = "FORMAT(" . $d . ",'MM-dd-yyyy, hh:mm', 'en-US') AS '" . $d . "'";
				}
			}
		}

		//set variable to be used in the query - if select fields ar specified, keep them in an array so that 
		$select_fields = is_array($arr_select_fields) && !empty($arr_select_fields) ? $arr_select_fields:'*';
		
		
		/*now that the where clauses are set, let's see how many rows would be returned with that criteria.
		 *If over 1000 and a filter has not yet been set for quartiles, add the 1st quartile as a filter.
		 *Then we can add the select and sort data to the query.
		 **/
		$this->{$this->db_group_name}->stop_cache();
		if(isset($limit) === FALSE){
			$this->{$this->db_group_name}->select('COUNT(*) AS c');
			$count_result = $this->{$this->db_group_name}->get()->result_array();
			$this->num_results = $count_result[0]['c'];
			
			if($this->num_results > 1000) {// && empty($arr_filter_criteria[$this->arr_auto_filter_field[0]])) {
				$this->_set_autofilter($arr_filter_criteria);
			}
		}
		else $this->{$this->db_group_name}->limit($limit);
		
		$this->prep_group_by(); // the prep_group_by function adds group by field to the active record object
		$this->prep_sort($arr_sort_by, $arr_sort_order); // the prep_sort function adds the sort field to the active record object

		//add select fields to query
//$select_fields[] = 'd'; //uncomment to dump search query to screen
		$this->{$this->db_group_name}->select($select_fields, FALSE);
		$ret = $this->{$this->db_group_name}->get()->result_array();
		$this->num_results = count($ret);
		$this->{$this->db_group_name}->flush_cache();
		//$ret['arr_unsortable_columns'] = $this->arr_unsortable_columns;
		return $ret;
	}
	
	/**
	 * @method prep_select_fields()
	 * @param arr_fields: copy of fields array to be formatted into SQL
	 * @return array of sql-prepped select fields
	 * @author ctranel
	 **/
	protected function prep_select_fields(){
		$arr_select_fields = [];
		//@todo: add field for date format (null for non-dates), or use date flag if date formats have to be the same
		if (($key = array_search('test_date', $this->arr_db_field_list)) !== FALSE) {
			$arr_select_fields[$key] = "FORMAT(" . $this->primary_table_name . ".test_date, 'MM-dd-yy', 'en-US') AS test_date";//MMM-dd-yy
		}
		if (($key = array_search('calving_date', $this->arr_db_field_list)) !== FALSE) {
			$arr_select_fields[$key] = "FORMAT(" . $this->primary_table_name . ".calving_date, 'MM-dd-yy', 'en-US') AS calving_date";//MMM-dd-yy
		}
		if (($key = array_search('fresh_month', $this->arr_db_field_list)) !== FALSE) {
			$arr_select_fields[$key] = "FORMAT(" . $this->primary_table_name . ".fresh_month, 'MM-dd-yy', 'en-US') AS fresh_month";//MMM-dd-yy
		}
		if (($key = array_search('cycle_date', $this->arr_db_field_list)) !== FALSE) {
			$arr_select_fields[$key] = "FORMAT(" . $this->primary_table_name . ".cycle_date, 'MM-dd-yy', 'en-US') AS cycle_date";//MMM-dd-yy
		}
		if (($key = array_search('summary_date', $this->arr_db_field_list)) !== FALSE) {
			$arr_select_fields[$key] = "FORMAT(" . $this->primary_table_name . ".summary_date, 'MM-dd-yy', 'en-US') AS summary_date";//MMM-dd-yy
		}
		if (($key = array_search('peak_dim', $this->arr_db_field_list)) !== FALSE) {
			$arr_select_fields[$key] = "FORMAT(" . $this->primary_table_name . ".peak_dim, 'MM-dd-yy', 'en-US') AS peak_dim";//MMM-dd-yy
		}
		if (($key = array_search('birth_year', $this->arr_db_field_list)) !== FALSE) {
			$arr_select_fields[$key] = "FORMAT(" . $this->primary_table_name . ".birth_year, 'MM-dd-yy', 'en-US') AS birth_year";//MMM-dd-yy
		}

		foreach($this->arr_db_field_list as $k => $v){
			if(!empty($this->arr_aggregates[$k])){
				$new_name = strtolower($this->arr_aggregates[$k]) . '_' . $v;
				$arr_select_fields[$k] = $this->arr_aggregates[$k] . '(' . $this->primary_table_name . '.' . $v . ') AS ' . $new_name;
				$this->arr_db_field_list[$k] = $new_name;
				//$arr_select_fields[$k] = $new_name;
			} 
		}
		return($arr_select_fields);
	}
	

	/** function prep_where_criteria
	 * 
	 * translates filter criteria into sql format
	 * @param $arr_filter_criteria
	 * @return void
	 */
	
	protected function prep_where_criteria($arr_filter_criteria, $block_url){
		//incorporate built-in report filters if set
		/* NOT CURRENTLY USED
		if(is_array($this->arr_where_field) && !empty($this->arr_where_field)){
			$tmp_cnt = count($this->arr_where_field);
			for($x = 0; $x < $tmp_cnt; $x++){
				//if the field does not have a table prefix, add it
				if(strpos($this->arr_where_field[$x], '.') === FALSE){
					$this->arr_where_field[$x] = 
						isset($this->arr_field_table[$this->arr_where_field[$x]]) && !empty($this->arr_field_table[$this->arr_where_field[$x]])
						? $this->arr_field_table[$this->arr_where_field[$x]] . '.' . $this->arr_where_field[$x]
						: $this->primary_table_name . '.' . $this->arr_where_field[$x];
				}
				$this->{$this->db_group_name}->where($this->arr_where_field[$x] . $this->arr_where_operator[$x] . $this->arr_where_criteria[$x]);
			}
		} */
		foreach($arr_filter_criteria as $k => $v){
			//@todo: the below is only for sql server
			if(strpos($k, '.') === FALSE) {
				$db_field = isset($this->arr_field_table[$k]) && !empty($this->arr_field_table[$k])?$this->arr_field_table[$k] . '.' . $k: $this->primary_table_name . '.' . $k;
			}
/*
 * @todo: 	find another way to acheive this--without naming specific blocks.  This handles pstring filters for 
 * 			cow-level blocks that are on summary pages
 */
			if(($block_url == 'peak_milk_trends' || $block_url == 'dim_at_1st_breeding') && substr($k,-7)=='pstring'){
				if(is_array($v)){
					$tmp = array_filter($v);
					if(empty($tmp)){
						continue;
					}
				}
				elseif($v == 0){
					continue;
				}
			}

			
			if(empty($v) === FALSE || $v === '0'){
				if(is_array($v)){
					//if filter is a range
					if(key($v) === 'dbfrom' || key($v) === 'dbto'){
						if(isset($arr_filter_criteria[$k]['dbfrom']) && !empty($arr_filter_criteria[$k]['dbfrom']) && isset($arr_filter_criteria[$k]['dbto']) && !empty($arr_filter_criteria[$k]['dbto'])){
							$from = is_date_format($arr_filter_criteria[$k]['dbfrom']) ? date_to_mysqldatetime($arr_filter_criteria[$k]['dbfrom']) : $arr_filter_criteria[$k]['dbfrom'];
							$to = is_date_format($arr_filter_criteria[$k]['dbto']) ? date_to_mysqldatetime($arr_filter_criteria[$k]['dbto']) : $arr_filter_criteria[$k]['dbto'];
							$this->{$this->db_group_name}->where($db_field . " BETWEEN '" . $from . "' AND '" . $to . "'");
						}
					}
					else {
						$v = array_filter($v, create_function('$a', 'return (!empty($a) || $a === "0" || $a === 0);'));
						if(empty($v)) continue;
						$this->{$this->db_group_name}->where_in($db_field, $v);
					}
				}
				else { //is not an array
					$this->{$this->db_group_name}->where($db_field, $v);
				} 
			}
		}
	}
	
	/*  
	 * @method prep_group_by()
	 * @author ctranel
	 */
	protected function prep_group_by(){
		$arr_len = is_array($this->arr_group_by_field)?count($this->arr_group_by_field):0;
		for($c=0; $c<$arr_len; $c++) {
			$table = isset($this->arr_field_table[$this->arr_group_by_field[$c]]) && !empty($this->arr_field_table[$this->arr_group_by_field[$c]])?$this->arr_field_table[$this->arr_group_by_field[$c]] . '.':$this->primary_table_name . '.';
			if(!empty($this->arr_group_by_field[$c])){
				$this->{$this->db_group_name}->group_by($table . $this->arr_group_by_field[$c]);
			}
		}
	}
	
	/*  
	 * @method prep_sort()
	 * @param array fields to sort by
	 * @param array sort order--corresponds to first parameter
	 * @author ctranel
	 */
	protected function prep_sort($arr_sort_by, $arr_sort_order){
		$arr_len = is_array($arr_sort_by)?count($arr_sort_by):0;
		for($c=0; $c<$arr_len; $c++) {
			$sort_order = (strtoupper($arr_sort_order[$c]) == 'DESC') ? 'DESC' : 'ASC';
			$table = isset($this->arr_field_table[$arr_sort_by[$c]]) && !empty($this->arr_field_table[$arr_sort_by[$c]])?$this->arr_field_table[$arr_sort_by[$c]] . '.':$this->primary_table_name . '.';
			if((!is_array($this->arr_unsortable_columns) || in_array($arr_sort_by[$c], $this->arr_unsortable_columns) === FALSE) && !empty($arr_sort_by[$c])){
				//put the select in an array in case the field includes a function with commas between parameters 
				if(is_array($this->arr_natural_sort_fields) && in_array($arr_sort_by[$c], $this->arr_natural_sort_fields) !== FALSE){
					$this->{$this->db_group_name}->order_by('users.dbo.naturalize(' . $table . $arr_sort_by[$c] . ')', $sort_order);
				}
				else {
					$this->{$this->db_group_name}->order_by($table . $arr_sort_by[$c], $sort_order);
				}
			}
		}
	}
	
	/*  
	 * @method pivot()
	 * @param array dataset
	 * @param string header field
	 * @param int pdf with of header field
	 * @param bool add average column
	 * @param bool add sum column
	 * @return array pivoted resultset
	 * @author ctranel
	 */
	public function pivot($arr_dataset, $header_field, $header_field_width, $label_column_width, $bool_avg_column = FALSE, $bool_sum_column = FALSE){
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
				//also need to add labels as keys to number formatting arrays so that they can be referenced in the view
				if(in_array($v, $this->arr_numeric_fields)){
					$this->arr_decimal_points[$k] = $this->arr_decimal_points[$v];
					$this->arr_numeric_fields[] = $k;
				}
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
					//is this being done in the view now?
					//if(isset($this->arr_decimal_points[$k])) $val = round($val, $this->arr_decimal_points[$k]);

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
//				if($bool_bench_column){
//					if($arr_benchmarks[$k] !== NULL) $sum_data['benchmark'] = round($arr_benchmarks[$k], $this->arr_decimal_points[$k]);//strpos($arr_benchmarks[$k], '.') !== FALSE ? trim(trim($arr_benchmarks[$k],'0'), '.') : $arr_benchmarks[$k];
//					else $sum_data['benchmark'] = NULL;
//				}
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
		return $new_dataset;
	}
	
	
	protected function _set_autofilter($arr_filter_criteria){
		$this->arr_messages['filter_alert'] = '';
		$num_fields = count($this->arr_auto_filter_field);
		for($c = 0; $c < $num_fields; $c++){
			if(empty($arr_filter_criteria[$this->arr_auto_filter_field[$c]])){
				//handle range fields
				//$dbfield = str_replace('_dbfrom', '', $this->arr_auto_filter_field[$c]);
				//$dbfield = str_replace('_dbto', '', $dbfield);
				//end handle range fields
				
				$criteria = $this->arr_auto_filter_criteria[$c];
				if(in_array($dbfield, $this->arr_date_fields) || in_array($dbfield, $this->arr_datetime_fields)) $criteria = date_to_mysqldatetime($criteria);
				if(in_array($dbfield, $this->arr_numeric_fields) === FALSE) $criteria = "'" . $criteria . "'";
				
				$this->{$this->db_group_name}->where($dbfield . $this->arr_auto_filter_operator[$c] . $criteria);
				$this->arr_messages['filter_alert'] .= $this->arr_auto_filter_alert[$c];
			}
		}
	}

	public function get_auto_filter_criteria(){
		$arr_return = array();
		$num_fields = count($this->arr_auto_filter_field);
		for($c = 0; $c < $num_fields; $c++){
			$arr_return[] = array('key' => $this->arr_auto_filter_field[$c], 'value' => $this->arr_auto_filter_criteria[$c]);
		}
		return $arr_return;
	}
	
	/**
	 * get_recent_dates
	 * @return date string
	 * @author ctranel
	 **/
	public function get_recent_dates($date_field = 'test_date', $num_dates = 1, $date_format = 'MMM-yy') {
		if($date_format) $this->db->select("FORMAT(" . $date_field . ", '" . $date_format . "', 'en-US') AS " . $date_field, FALSE);
		else $this->db->select($date_field);
		$this->db
			->where($this->primary_table_name . '.herd_code', $this->session->userdata('herd_code'))
			->where($date_field . ' IS NOT NULL')
			->order_by($this->primary_table_name . '.' . $date_field, 'desc');
		if(isset($num_dates) && !empty($num_dates)){
			$this->db->limit($num_dates);		
		}
		$result = $this->db->get($this->primary_table_name)->result_array();
		if(is_array($result) && !empty($result)){
			return array_flatten($result);
		} 
		else return FALSE;
	}

    /**
    * @function get_start_test_date
    * @param string date_field - db name of the date field used for this trend
    * @param int num_dates - number of test dates to include in report
    * @param string date_format - database string for formatting date
    * @param int num_dates_to_shift - number of dates to shift the results back
    * @return string date
    * @author ctranel
    **/
    public function get_start_date($date_field = 'test_date', $num_dates = 12, $date_format = 'MMM-yy', $num_dates_to_shift = 0) {
		$sql = "SELECT FORMAT(a." . $date_field . ", 'MM-dd-yyyy', 'en-US') AS " . $date_field . "
    		FROM (SELECT DISTINCT TOP " . ($num_dates + $num_dates_to_shift) . " " . $date_field . "
                FROM " . $this->primary_table_name . " 
                WHERE herd_code = '" . $this->session->userdata('herd_code') . "' AND " . $date_field . " IS NOT NULL
                ORDER BY " . $date_field . " DESC) a";
        $result = $this->{$this->db_group_name}->query($sql)->result_array();
        if(is_array($result) && !empty($result)) return $result[(count($result) - 1)][$date_field];
		else return FALSE;
	}	
	
/******* CHART FUNCTIONS ****************/
	public function set_chart_fields($block_in){
		$arr_numeric_types = array('bigint','decimal','int','money','smallmoney','numeric','smallint','tinyint','float','real');
		$arr_field_child = array();
		$arr_table_ref_cnt = array();

		$arr_field_data = $this->{$this->db_group_name}
			->where('block_id', $block_in)
			->order_by('list_order')
			->get('users.dbo.v_block_field_data')
			->result_array();
		if(is_array($arr_field_data) && !empty($arr_field_data)){
			foreach($arr_field_data as $fd){
				$fn = $fd['db_field_name'];
				$this->arr_fields[$fd['name']] = $fn;
				$arr_table_ref_cnt[$fd['table_name']] = isset($arr_table_ref_cnt[$fd['table_name']]) ? ($arr_table_ref_cnt[$fd['table_name']] + 1) : 1;
				$this->arr_field_sort[$fn] = $fd['default_sort_order'];
				$this->arr_decimal_points[$fn] = $fd['decimal_points'];
				$this->arr_aggregates[$fn] = $fd['aggregate'];
				$this->arr_axis_index[$fn] = $fd['axis_index'];
				$this->arr_bool_display[$fn] = $fd['display'];
				$this->arr_chart_type[$fn] = $fd['chart_type'];
				$this->arr_unit_of_measure[$fn] = $fd['unit_of_measure'];
				$this->arr_field_table[$fn] = $fd['table_name'];
				if(strpos($fd['data_type'], 'date') !== FALSE && strpos($fn, 'time') !== FALSE) $this->arr_datetime_fields[] = $fn;
				elseif(strpos($fd['data_type'], 'date') !== FALSE) $this->arr_date_fields[] = $fn;
				if($fd['is_nullable'] === FALSE) $arr_notnull_fields[] = $fn;
				if(in_array($fd['data_type'], $arr_numeric_types)) $this->arr_numeric_fields[] = $fn;
				if($fd['is_natural_sort']) $this->arr_natural_sort_fields[] = $fn;
			}
		}
		$this->primary_table_name = array_search(max($arr_table_ref_cnt), $arr_table_ref_cnt);
		//set up arr_fields hierarchy
		if(is_array($arr_table_ref_cnt) && count($arr_table_ref_cnt) >  1){
			foreach($arr_table_ref_cnt as $t => $cnt){
				if($t != $this->primary_table_name){
					$this->arr_joins[] = array('table'=>$t, 'join_text'=>$this->get_join_text($this->primary_table_name, $t));
				}
			}
		}
//		$this->adjust_fields($this->session->userdata('herd_code'));
	}
	
	/**
	 * @method get_chart_axes - retrieve data for categories, axes, etc.
	 * @param int block id
	 * @return array of meta data for the block
	 * @access public
	 *
	 **/
	public function get_chart_axes($block_id){
		$arr_return = array();
		$this->{$this->db_group_name}
			->select("a.id, a.x_or_y, a.min, a.max, a.opposite, a.data_type, f.db_field_name, f.name AS field_name, f.unit_of_measure, text,c.name AS category")
			->from('users.dbo.block_axes AS a')
			->join('users.dbo.chart_categories AS c', 'a.id = c.block_axis_id', 'left')
			->join('users.dbo.db_fields AS f', 'a.db_field_id = f.id', 'left')
			->where('a.block_id', $block_id)
			->order_by('a.list_order', 'asc')
			->order_by('c.list_order', 'asc');
		$result = $this->{$this->db_group_name}->get()->result_array();
		
		$arr_keep_keys = array('min' => '', 'max' => '', 'opposite' => '', 'data_type' => '', 'db_field_name' => '', 'field_name' => '', 'text' => '');
		if(is_array($result) && !empty($result)){
			foreach($result as $a){
				if(!isset($arr_return[$a['x_or_y']][$a['id']])){
					$arr_return[$a['x_or_y']][$a['id']] = array_intersect_key($a, $arr_keep_keys);
				}
				if(isset($a['category'])){
					$arr_return[$a['x_or_y']][$a['id']]['categories'][] = $a['category'];
				}
			}
			return $arr_return;
		}
		else return FALSE;
	}
	
	/**
	 * @method set_row_to_series - used when data for multiple series' are returned in one row.  
	 * Breaks data down so that there is one row per category, each row having one entry for each series.
	 * 
	 * @param array of field name base text (for percentages, add '_pct')
	 * @return array of data for the graph
	 * @access protected
	 *
	 **/
	protected function set_row_to_series($data, $arr_fieldname_base, $arr_categories){
		$mod_base = count($arr_categories);
		if(is_array($data) && !empty($data)){
			$key = 0;
			foreach($data as $k=>$row){
				$count = 1;
				$key++;
				//must account for multiple series being returned in a single row
				foreach($arr_fieldname_base as $kk => $f){
					if($count > $mod_base && $count % $mod_base == 1) $key++;
					if(!isset($key)) $key = $k;
					$arr_return[$key][] = (float)$row[$f];
					$count++;
				}
			}
			return $arr_return;
		}
		else return FALSE;
	}
	
	
	/**
	 * @method get_graph_data()
	 * @param array database field names included on graph
	 * @param string herd code
	 * @param int number of tests to include on report
	 * @param string date field used on graph (test_date)
	 * @param string url segment of block
	 * @param array of categories
	 * @return array of data for the chart
	 * @access public
	 *
	 **/
	function get_graph_data($arr_fieldname, $arr_filters, $num_dates, $date_field, $block_url, $arr_categories = NULL){
		$data = $this->get_graph_dataset($arr_filters, $num_dates, $date_field, $block_url);
		if(isset($arr_categories) && is_array($arr_categories)){
			$return_val = $this->set_row_to_series($data, $arr_fieldname, $arr_categories);
		}
		else{
			$return_val = $this->set_longitudinal_data($data, $date_field);
		}
		return $return_val;
	}
	
	/**
	 * @method get_graph_dataset()
	 * @param string herd code
	 * @param int number of tests to include on report
	 * @param string date field used on graph (test_date)
	 * @return array of database results
	 * @access public
	 *
	 **/
	function get_graph_dataset($arr_filters, $num_dates, $date_field, $block_url){
		if(isset($date_field) && isset($num_dates)){
			$arr_filters[$date_field]['dbfrom'] = $this->get_start_date($date_field, $num_dates, 'MM-dd-yyyy');
			$arr_filters[$date_field]['dbto'] = $this->get_recent_dates($date_field, 1, 'MM-dd-yyyy')[0];
		}
		$data = $this->search($arr_filters['herd_code'], $block_url, $arr_filters, array($date_field), array('ASC'), $num_dates);
		return $data;
	}
	
	/**
	 * @method get_longitudinal_data()
	 * @param array of field name base text (for percentages, add '_pct')
	 * @param string date field used on graph (test_date)
	 * @return array of data for the graph
	 * @access protected
	 *
	 **/
	protected function set_longitudinal_data($data, $date_field = 'test_date'){
		$count = count($data);
		for($x = 0; $x < $count; $x++){
			$arr_y_values = $data[$x];

			$arr_fields = array_keys($arr_y_values);
			$date_key = array_search($date_field, $arr_fields);
			unset($arr_fields[$date_key]);
			if($date_field == 'age_months'){
				foreach($arr_fields as $k=>$f){
					$tmp_data = is_numeric($data[$x][$f]) ? (float)$data[$x][$f] : $data[$x][$f];
					$arr_return[$k][] = array($data[$x][$date_field], $tmp_data);
				}
			}
			elseif(isset($data[$x][$date_field]) && !empty($data[$x][$date_field])){
				$arr_d = explode('-', $data[$x][$date_field]);
				foreach($arr_fields as $k=>$f){
					$tmp_data = is_numeric($data[$x][$f]) ? (float)$data[$x][$f] : $data[$x][$f];
					$arr_return[$k][] = array((mktime(0, 0, 0, $arr_d[0], $arr_d[1],$arr_d[2]) * 1000), $tmp_data);
				}
			}
		}
		if(isset($arr_return) && is_array($arr_return)) return $arr_return;
		else return FALSE;
	}

	/**
	 * @method set_boxplot_data()
	 * @param array of data from active record result_array() function
	 * @param int number of boxplot series (BOXPLOT SERIES FIELDS MUST ALL BE IMMEDIATELY AFTER THE TEST DATE)
	 * @return array of data for the graph
	 * @access protected
	 *
	 **/
	protected function set_boxplot_data($data, $date_field = 'test_date', $num_boxplot_series = 1, $adjustment = 200000000){
		$row_count = 0;
		$arr_series = array();
		foreach ($data as $d){ //foreach row
			//set a variable so we can pair date with each data point
			if(!isset($d[$date_field])) continue;
			$arr_d = explode('-', $d[$date_field]);
			unset($d[$date_field]); //remove date so we can loop through the remaining data points
			//the date is formated in the database search ('m-d-y'), so we need to accommodate that in the mktime function
			$this_date = mktime(0, 0, 0, $arr_d[0], $arr_d[1],'20' . $arr_d[2]) * 1000;
			$num_series = count($d)/3;
			$field_count = 1;
			$series_count = 0;
			$offset = $this->_get_series_offset($num_series, $series_count, $adjustment);
			$arr_series[$series_count][$row_count] = array($this_date + $offset);
			$arr_series[$series_count + 1][$row_count] = array($this_date + $offset);
			foreach ($d as $f){ //for each field in row
				$tmp_data = is_numeric($f) ? (float)$f : $f;
				if($field_count <= ($num_boxplot_series * 3)){// using boxplot chart requires 4 datapoints
					$modulus = $field_count%3;
					$arr_series[$series_count][$row_count][] = $tmp_data;
					//boxplots require 5 datapoints, need to replicate each end of the box (i.e., blend whiskers into box)
					if($modulus === 1 || $modulus === 0){
						$arr_series[$series_count][$row_count][] = $tmp_data;
					}
					if($modulus === 2){ //for median, add a datapoint in the trendline series
						$arr_series[$series_count + 1][$row_count][] = $tmp_data;
					}
					if($modulus == 0 && $field_count > 1){
						$series_count += 2;
						if(($field_count + 1) <= ($num_boxplot_series * 3)){
							$offset = $this->_get_series_offset($num_series, $series_count, $adjustment);
							$arr_series[$series_count][$row_count] = array(($this_date + $offset)); //adjust date so that multiple boxplots are not on top of each other
							$arr_series[$series_count +1][$row_count] = array(($this_date + $offset)); //adjust date so that multiple boxplots are not on top of each other
						}
					}
				}
/*				else { //assumes that non-box series correspond to box series
					$offset = $this->_get_series_offset($num_series, $series_count, $adjustment);
					$arr_series[$series_count][$row_count] = array(($this_date + $offset), $tmp_data);
					$arr_series[$series_count + 1][$row_count] = array(($this_date + $offset), $tmp_data);
					$series_count += 2;
				}
*/				$field_count++;
			}
			$row_count++;
		}
		return $arr_series;
	}
	
	/**
	 * @method _get_series_offset()
	 * @param int number of series' in the dataset for which the offset is being calculated
	 * @param int numeric position of series for which offset is currently being calculated
	 * @param int standardized unit on which adjustment calculation is based
	 * @return int amount to offset date in series
	 * @access protected
	 *
	 **/
		protected function _get_series_offset($num_series, $series_count, $adjustment){
		$offset = 0;;
		if($num_series == 2){
			if($series_count == 0) {
				$offset -= $adjustment;
			}
			if($series_count == 2) {
				$offset += $adjustment;
			}
		}
		if($num_series == 3){
			if($series_count == 0) {
				$offset -= ($adjustment * 2);
			}
			if($series_count == 4) {
				$offset += ($adjustment * 2);
			}
		}
		return $offset;
	}
}
