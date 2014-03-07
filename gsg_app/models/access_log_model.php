<?php
require_once APPPATH . 'models/report_model.php';
class Access_log_model extends Report_Model {
	public function __construct(){
		parent::__construct();
		$this->db_group_name = 'default';
		$this->{$this->db_group_name} = $this->load->database($this->db_group_name, TRUE);
		/*in the case of the access log model, the section id is set AFTER the parent is called so that the reference
		 * back to the access log model does not cause problem.  That is the only other model that call the get block links method.
		 */ 
		$this->section_id = '3';
		$this->arr_blocks = $this->get_block_links($this->section_id);
		$this->primary_table_name = $this->tables['access_log'];
		$this->arr_joins = array(
			'section_id'=>array('table' => $this->tables['pages'], 'join_text' => $this->tables['access_log'] . '.page_id = ' . $this->tables['pages'] . '.id')
		);
		$this->arr_unsortable_columns = array();
	    $this->arr_notnull_fields = array(); //used for imports
	    $this->arr_zero_is_null_fields = array();
	    $this->arr_numeric_fields = array(); //used for imports
		$this->arr_natural_sort_fields = array();
		$this->arr_date_fields = array();
		$this->arr_datetime_fields = array('access_time');
		$this->arr_auto_filter_field[] = 'access_time_dbfrom';
		$this->arr_auto_filter_operator[] = " > ";
		$this->arr_auto_filter_criteria[] = date('m-d-Y', strtotime("-1 week"));
		$this->arr_auto_filter_alert[] = 'The requested search would have resulted in over 1000 results, and would have significantly slowed the performance of the report.  
			To maintain the best performance, results from the past week that match your selected criteria are being shown.  
			If you would like to see more results despite the negative effect on performance, please go to the filter section and select the quartiles you would like to include in your results.';
		$this->arr_fields = Array(
			'Section Name'=>'section_name',
			'Access Time'=>'access_time',
			'Page'=>'page_id',
			'Format'=>'format',
			'User'=>'user_id',
			'Group'=>'group_id',
			'Herd'=>'herd_code',
			'User Region'=>'user_association_num',
			'User Tech Num'=>'user_tech_num',
			'Sort'=>'sort_text',
			'Filter'=>'filter_text'
		);
		$this->arr_field_table = array(
			'section_name' => $this->arr_joins['section_id']['table'],
			'access_time' => $this->primary_table_name,
			'page_id' => $this->primary_table_name,
			'format' => $this->primary_table_name,
			'user_id' => $this->primary_table_name,
			'group_id' => $this->primary_table_name,
			'herd_code' => $this->primary_table_name,
			'user_association_num' => $this->primary_table_name,
			'user_tech_num' => $this->primary_table_name,
			'sort_text' => $this->primary_table_name,
			'filter_text' => $this->primary_table_name
		);
		$this->arr_field_sort = array(
			'section_name' => 'ASC',
			'access_time' => 'DESC',
			'page_id' => 'ASC',
			'format' => 'ASC',
			'user_id' => 'ASC',
			'group_id' => 'DESC',
			'herd_code' => 'ASC',
			'user_association_num' => 'ASC',
			'user_tech_num' => 'ASC',
			'sort_text' => 'ASC',
			'filter_text' => 'ASC'
		);
		$this->arr_pdf_widths = array(
			'section_name' => '24',
			'access_time' => '20',
			'page_id' => '22',
			'format' => '10',
			'user_id' => '30',
			'group_id' => '12',
			'herd_code' => '14',
			'user_association_num' => '12',
			'user_tech_num' => '16',
//			'herd_subscription_level' => '14',
			'sort_text' => '45',
			'filter_text' => '65'
		);
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
			if (($key = array_search('page_id', $arr_fields)) !== FALSE) {
				$arr_fields[$key] = $this->tables['pages'] . '.name AS page_id';
				//section pages are always included
			}
			if (($key = array_search('user_id', $arr_fields)) !== FALSE) {
				$arr_fields[$key] = "CONCAT(" . $this->tables['users'] . ".first_name, ' ', " . $this->tables['users'] . ".last_name) AS user_id";
				$this->{$this->db_group_name}->join($this->tables['users'], $this->tables['access_log'] . '.user_id = ' . $this->tables['users'] . '.id');
			}
			if (($key = array_search('user_association_num', $arr_fields)) !== FALSE) {
				$arr_fields[$key] = $this->tables['regions'] . ".association_num AS user_association_num";
				$this->{$this->db_group_name}->join($this->tables['regions'], $this->tables['access_log'] . '.user_association_num = ' . $this->tables['regions'] . '.association_num');
			}
			if (($key = array_search('group_id', $arr_fields)) !== FALSE) {
				$arr_fields[$key] = 'groups.name AS group_id';
				$this->db->join('groups', 'access_log.group_id = groups.id');
			}
			if (($key = array_search('section_name', $arr_fields)) !== FALSE) {
				$arr_fields[$key] = $this->tables['sections'] . '.name AS section_name';
				$this->{$this->db_group_name}->join($this->tables['sections'], $this->tables['pages'] . '.section_id = ' . $this->tables['sections'] . '.id');
			}
		}
		return $arr_fields;
	}

	/** function prep_where_criteria -- overrode parent function to set where criteria to end of the date given (on form, the user enters only the date).
	 * DATES NOW ACCOUNTED FOR IN PARENT??
	 * translates filter criteria into sql format
	 * @param $arr_filter_criteria
	 * @return void
	
	protected function prep_where_criteria($arr_filter_criteria){
		foreach($arr_filter_criteria as $k => $v){
			if(empty($v) === FALSE){
				if(is_array($v)){
					if(($tmp_key = array_search('NULL', $v)) !== FALSE){
						unset($v[$tmp_key]);
						$text = implode(',', $v);
						if(!empty($v)) $this->{$this->db_group_name}->where("($k IS NULL OR $k IN ( $text ))");
						else $this->{$this->db_group_name}->where("$k IS NULL");
					}
					else $this->{$this->db_group_name}->where_in($k, $v);
				}
				else { //is not an array
					if(substr($k, -5) == "_dbto"){ //ranges
						$db_field = substr($k, 0, -5);
						//overrode this line only--if we add time to user form, this function can be removed.
						$this->{$this->db_group_name}->where("$db_field BETWEEN '" . date_to_mysqldatetime($arr_filter_criteria[$db_field . '_dbfrom']) . "' AND '" . date_to_mysqldatetime($arr_filter_criteria[$db_field . '_dbto'] . ' 23:59:59') . "'");
					}
					elseif(substr($k, -7) != "_dbfrom"){ //default--it skips the opposite end of the range as _dbto
						$this->{$this->db_group_name}->where($k, $v);
					}
				} 
			}
		}
	}
	 */
	
	/**
	 * @method get_section_select_data()
	 * @access public
	 *
	function get_section_select_data(){
		$arr_ret = array();
		$arr_section = $this->db->get_sections_by_user($this->session->userdata('user_id'))->result_array();
		if(is_array($arr_section)){
			foreach($arr_section as $s){
				$arr_ret[$s['id']] = $s['name'];
			}
		}
	}	 **/


	/**
	 * get_page_filters
FUNCTION MOVED?
	 * @return array of filter data for given page
	 * @author Chris Tranel
	public function get_page_filters($section_id, $page_url_segment) {
		$ret_array = array();
		$results = $this->{$this->db_group_name}
			->select('pf.*, f.db_field_name')
			->where('p.section_id', $section_id)
			->where('p.url_segment', $page_url_segment)
			->join($this->tables['pages'] . ' p', "pf.page_id = p.id")
			->join('users.dbo.db_fields f', "pf.field_id = f.id")
			->order_by('pf.list_order')
			->get('users.dbo.page_filters pf')
			->result_array();
		if(isset($results) && is_array($results)){
			foreach($results as $r){
				$ret_array[$r['db_field_name']] = array(
					'db_field_name' => $r['db_field_name']
					,'name' => $r['name']
					,'type' => $r['type']
					,'default_value' => unserialize($r['default_value'])
				);
			}
		}
		return $ret_array;
	}
	 **/
	
	/**
	 * get_keyed_page_array
	 *
	 * @return 2d array ([section_id][id]=name)
	 * @author Chris Tranel
	 **/
	public function get_keyed_page_array() {
		$this->{$this->db_group_name}->select($this->tables['pages'] . '.id, '. $this->tables['pages'] . '.section_id, ' . $this->tables['pages'] . '.name')
		->join($this->tables['sections'], $this->tables['pages'] . '.section_id = ' . $this->tables['sections'] . '.id')
		->order_by($this->tables['sections'] . '.name', 'asc')
		->order_by($this->tables['pages'] . '.name', 'asc');
		$arr_page_obj = $this->get_pages()->result();
		if(is_array($arr_page_obj)) {
			foreach($arr_page_obj as $e){
				$ret_array[$e->section_id][$e->id] = $e->name;
			}
			return $ret_array;
		}
		else return false;
	}

	/**
	 * get_pages
	 * @return array of section data
	 * @author Chris Tranel
	 **/
	private function get_pages() {
		//need to check/adjust user_id to allow showing of default herd when no users are logged in
		$uid = $this->session->userdata('user_id');
		if(!isset($uid) || empty($uid)) $uid = 0;
		return $this->{$this->db_group_name}
			->where($this->tables['pages'] . '.active', 1)
			->where("(" . $this->tables['pages'] . ".user_id IS NULL OR " . $this->tables['pages'] . ".user_id = " . $uid . ")")
			->order_by($this->tables['pages'] . '.list_order')
			->get($this->tables['pages']);
	}
	
	/**
	 * get_sections
	 * @return array of section data
	 * @author Chris Tranel
	 **/
	private function get_sections() {
		return $this->{$this->db_group_name}
			->where($this->tables['sections'] . '.active', 1)
			->where("(" . $this->tables['sections'] . ".user_id IS NULL OR " . $this->tables['sections'] . ".user_id = " . $this->session->userdata('user_id') . ")")
			->order_by($this->tables['sections'] . '.list_order')
			->get($this->tables['sections']);
	}
	
	/**
	 * get_pages_by_criteria
	 * @param associative array of criteria
	 * @return array of section data
	 * @author Chris Tranel
	 **/
	public function get_pages_by_criteria($arr_criteria) {
		$this->{$this->db_group_name}
			->where($arr_criteria);
		return $this->get_pages();
	}

	function get_sections_select_data($super_section_id){
		$arr_return = array();
		$this->{$this->db_group_name}
		->select('id, name')
		->where($this->tables['sections'] . '.super_section_id', $super_section_id);
		$tmp = $this->get_sections()->result_array();
		if(is_array($tmp)){
			$arr_return[0] = 'Select one';
			foreach($tmp as $t){
				$arr_return[$t['id']] = $t['name'];
			}
		}
		return $arr_return;
	}
	
	function get_pages_select_data($section_id){
		$arr_return = array();
		$this->{$this->db_group_name}
		->select('id, name')
		->where($this->tables['pages'] . '.section_id', $section_id);
		$tmp = $this->get_pages()->result_array();
		if(is_array($tmp)){
			$arr_return[0] = 'Select one';
			foreach($tmp as $t){
				$arr_return[$t['id']] = $t['name'];
			}
		}
		return $arr_return;
	}
	
	/**
	 * get_blocks
	 * @return array of section data
	 * @author Chris Tranel
	 **/
	private function get_blocks() {
		return $this->{$this->db_group_name}
			->where($this->tables['blocks'] . '.active', 1)
			->get($this->tables['blocks']);
	}
	
	/**
	 * get_block_display_types
	 * @return array of section data
	 * @author Chris Tranel
	 **/
	public function get_block_display_types() {
		return $this->{$this->db_group_name}
			//->where($this->tables['lookup_display_types'] . '.active', 1)
			->get($this->tables['lookup_display_types']);
	}
	
	/**
	 * get_chart_display_types
	 * @return array of section data
	 * @author Chris Tranel
	 **/
	public function get_chart_display_types() {
		return $this->{$this->db_group_name}
			//->where($this->tables['lookup_chart_types'] . '.active', 1)
			->get($this->tables['lookup_chart_types']);
	}
	
	/**
	 * get_block_links
	 * @param int section id
	 * @return array of block info keyed by url_segment
	 * @author Chris Tranel
	 **/
	public function get_block_links($section_id = NULL) {
		$arr_return = array();
		if(isset($section_id)) $this->{$this->db_group_name}->where('p.section_id', $section_id);
		$result = $this->{$this->db_group_name}
		->select("p.id AS page_id, b.id, p.section_id, b.url_segment, b.name, ct.name AS chart_type, b.description, p.url_segment AS page, p.name AS page_name, CASE WHEN dt.name LIKE '%chart' THEN 'chart' ELSE dt.name END AS display,s.path AS section_path, b.max_rows, b.cnt_row, b.sum_row, b.avg_row, b.bench_row, pf.db_field_name AS pivot_db_field, b.is_summary")
		->join($this->tables['pages'] . ' AS p', 'p.section_id = s.id', 'left')
		->join($this->tables['pages_blocks'] . ' AS pb', 'p.id = pb.page_id', 'left')
		->join($this->tables['blocks'] . ' AS b', 'pb.block_id = b.id', 'left')
		->join($this->tables['lookup_display_types'] . ' AS dt', 'b.display_type_id = dt.id', 'left')
		->join('users.dbo.lookup_chart_types AS ct', 'b.chart_type_id = ct.id', 'left')
		->join('users.dbo.db_fields AS pf', 'pf.id = b.pivot_db_field', 'left')
		//->where($this->tables['blocks'] . '.display IS NOT NULL')
		->where('b.url_segment IS NOT NULL')
		->order_by('s.list_order', 'asc')
		->order_by('p.list_order', 'asc')
		->order_by('b.list_order', 'asc')
		->get($this->tables['sections'] . ' AS s')->result_array();
		if(is_array($result) && !empty($result)){
			foreach($result as $r){
				$arr_return[$r['page']]['page_id'] = $r['page_id'];
				$arr_return[$r['page']]['name'] = $r['page_name'];
				if(empty($r['url_segment']) === FALSE){
					$arr_return[$r['page']]['display'][$r['display']][$r['url_segment']] = array(
						'id'=>$r['id'],
						'section_id'=>$r['section_id'],
						'name'=>$r['name'],
						'description'=>$r['description'],
						'url_segment'=>$r['url_segment'],
						'section_path'=>$r['section_path'],
						'chart_type'=>$r['chart_type'],
						'max_rows'=>$r['max_rows'],
						'cnt_row'=>$r['cnt_row'],
						'sum_row'=>$r['sum_row'],
						'avg_row'=>$r['avg_row'],
						'bench_row'=>$r['bench_row'],
						'is_summary'=>$r['is_summary'],
						'pivot_db_field'=>$r['pivot_db_field']
					);
				} 
				else	{
					$arr_return[$r['page']]['display'][$r['display']][] = array(
						'id'=>$r['id'],
						'section_id'=>$r['section_id'],
						'name'=>$r['name'],
						'description'=>$r['description'],
						'url_segment'=>$r['url_segment'],
						'is_summary'=>$r['is_summary'],
						'section_path'=>$r['section_path']
					);
				}
 			}
			return $arr_return;
		}
		else return FALSE;
	}
	
	/**
	 * write_entry
	 *
	 * @param int page id
	 * @param string format (web, pdf or csv) defaults to web
	 * @param string sort order (NULL, ASC or DESC) defaults to NULL
	 * @param string filter text, defaults to NULL
	 * @return boolean
	 * @author Chris Tranel
	 **/
	function write_entry($page_id, $format='web', $sort=NULL, $filters=NULL){
		if($this->as_ion_auth->is_admin()) return 1; //do not record admin action
		$tmp_array = array(
			'page_id'=>$page_id,
			'format'=>$format,
			'user_id'=>$this->session->userdata('user_id'),
			'group_id'=>$this->session->userdata('active_group_id'),
			'herd_code'=>$this->session->userdata('herd_code'),
			'user_supervisor_acct_num'=>$this->session->userdata('supervisor_acct_num'),
			'user_association_acct_num'=>implode(',', array_keys($this->session->userdata('arr_regions'))),
			'access_time'=> date('Y-m-d H:i:s')
		);
		if ($sort) $tmp_array['sort_text'] = $sort;
		if ($filters) $tmp_array['filter_text'] = $filters;
		return $this->{$this->db_group_name}->insert($this->tables['access_log'], $tmp_array);
	}
}
