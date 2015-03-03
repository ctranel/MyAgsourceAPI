<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class report_block_model extends CI_Model {
	public function __construct(){
		parent::__construct();
		$this->db_group_name = 'default';
		$this->{$this->db_group_name} = $this->load->database($this->db_group_name, TRUE);
		$this->tables = $this->config->item('tables', 'ion_auth');
	}

	/**
	 * @method getBlocks
	 * @return array of block data
	 * @author ctranel
	 **/
	public function getBlocks() {
		$this->db
			->select('b.id, pb.page_id, b.name,b.[description],b.path,dt.name AS display_type,s.name AS scope,b.chart_type_id,b.max_rows,b.cnt_row,b.sum_row,b.avg_row,b.pivot_db_field,b.bench_row,b.is_summary,b.active')
			->where('b.active', 1)
			->join('lookup_display_types dt', 'b.display_type_id = dt.id', 'inner')
			->join('lookup_scopes s', 'b.scope_id = s.id', 'inner')
			->join('pages_blocks pb', 'b.id = pb.block_id', 'inner')
			->order_by('list_order', 'asc')
			->from($this->tables['blocks'] . ' b');
		return $this->db->get()->result_array();
	}
	
	/**
	 * getPagesByCriteria
	 * @param associative array of criteria
	 * @param 2d array of joins ('table', 'condition')
	 * @return array of section data
	 * @author ctranel
	 **/
	public function getByCriteria($where, $join = null) {
		if(isset($where) && !empty($where)){
			$this->db->where($where);
		}
		if(isset($join) && !empty($join)){
			foreach($join as $j){
				$this->db->join($j['table'], $j['condition']);
			}
		}
		return $this->getBlocks();
	}
	
	/**
	 * get_block_display_types
	 * @return array of section data
	 * @author ctranel
	 **/
	public function get_block_display_types() {
		return $this->{$this->db_group_name}
			//->where($this->tables['lookup_display_types'] . '.active', 1)
			->get($this->tables['lookup_display_types']);
	}
	
	/**
	 * @method getSortData()
	 * @param int block id
	 * @return returns multi-dimensional array, arr_sort_by field data and arr_sort_order
	 * @author ctranel
	 **/
	public function getSortData($block_id){
		return $this->{$this->db_group_name}
			->select("f.id AS db_field_id, f.db_table_id, f.db_field_name
				, f.name, f.description, f.pdf_width, f.default_sort_order, f.data_type, f.is_timespan_field as is_timespan
				, f.is_natural_sort, is_fk_field AS is_foreign_key, f.is_nullable, f.decimal_points AS decimal_scale, f.unit_of_measure, f.data_type as datatype, f.max_length, s.sort_order")
			->join('users.dbo.blocks_sort_by s', 'b.id = s.block_id AND b.id = ' . $block_id, 'inner')
			->join('users.dbo.db_fields f', 's.field_id = f.id' , 'inner')
//			->join('users.dbo.db_tables t', 'f.db_table_id = t.id', 'inner')
//			->join('users.dbo.db_databases d', 't.database_id = d.id' , 'inner')
			->order_by('s.list_order', 'asc')
			->get($this->tables['blocks'] . ' b')
			->result_array();
	}
	
	/**
	 * @method getFieldData()
	 * @param string block url segment
	 * @return returns multi-dimensional array, one row for each field
	 * @author ctranel
	 **/
	public function getFieldData($block_id){
		return $this->{$this->db_group_name}
			->select("bsf_id AS id, db_field_id, table_name, db_field_name, name, description, pdf_width, default_sort_order, data_type as datatype, max_length, 
					decimal_points AS decimal_scale, unit_of_measure, is_timespan_field as is_timespan, is_fk_field AS is_foreign_key, is_nullable, is_sortable, is_natural_sort,
					is_displayed, a_href, a_rel, a_title, a_class, head_a_href, head_a_rel, head_a_title, head_a_class, head_supp_id, head_comment, aggregate,
					display_format")
			->where('block_id', $block_id)
			->order_by('list_order')
			->get('users.dbo.v_block_field_data')
			->result_array();
	}
	
	/**
	 * get_select_field_structure()
	 * 
	 * returns block (i.e., table) header structure which provides a skeleton for the organization of fields in the arr_fields object variable
	 * 				also
	 * 
	 * @param int id of current block
	 * @return array: ref = lookup array for ids, arr_fields = skeleton structure for db_fields
	 * @author ctranel
	 **/
	public function getHeaderGroups($block_in){
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
		return $arr_groupings;
	}

	/**
	 * get_block_links
	 * @param int section id
	 * @return array of block info keyed by path
	 * @author ctranel
	public function getCompleteData() {
		$arr_return = array();
		if(isset($section_id)) $this->{$this->db_group_name}->where('p.section_id', $section_id);
		$result = $this->{$this->db_group_name}
		->select("p.id AS page_id, b.id, p.section_id, b.path, b.name, ct.name AS chart_type, b.description, p.path AS page, p.name AS page_name, CASE WHEN dt.name LIKE '%chart' THEN 'chart' ELSE dt.name END AS display_type,s.path AS section_path, b.max_rows, b.cnt_row, b.sum_row, b.avg_row, b.bench_row, pf.db_field_name AS pivot_db_field, b.is_summary")
		->join($this->tables['pages'] . ' AS p', 'p.section_id = s.id', 'left')
		->join($this->tables['pages_blocks'] . ' AS pb', 'p.id = pb.page_id', 'left')
		->join($this->tables['blocks'] . ' AS b', 'pb.block_id = b.id', 'left')
		->join($this->tables['lookup_display_types'] . ' AS dt', 'b.display_type_id = dt.id', 'left')
		->join('users.dbo.lookup_chart_types AS ct', 'b.chart_type_id = ct.id', 'left')
		->join('users.dbo.db_fields AS pf', 'pf.id = b.pivot_db_field', 'left')
		//->where($this->tables['blocks'] . '.display IS NOT NULL')
		->where('b.path IS NOT NULL')
		->order_by('s.list_order', 'asc')
		->order_by('p.list_order', 'asc')
		->order_by('pb.list_order', 'asc')
		->get($this->tables['sections'] . ' AS s')->result_array();
		if(is_array($result) && !empty($result)){
			foreach($result as $r){
				$arr_return[$r['page']]['page_id'] = $r['page_id'];
				$arr_return[$r['page']]['name'] = $r['page_name'];
				if(empty($r['path']) === FALSE){
					$arr_return[$r['page']]['blocks'][$r['path']] = array(
						'id'=>$r['id'],
						'section_id'=>$r['section_id'],
						'name'=>$r['name'],
						'description'=>$r['description'],
						'path'=>$r['path'],
						'section_path'=>$r['section_path'],
						'display_type'=>$r['display_type'],
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
					$arr_return[$r['page']]['blocks'][$r['path']] = array(
						'id'=>$r['id'],
						'section_id'=>$r['section_id'],
						'name'=>$r['name'],
						'description'=>$r['description'],
						'path'=>$r['path'],
						'is_summary'=>$r['is_summary'],
						'section_path'=>$r['section_path']
					);
				}
 			}
 			return $arr_return;
		}
		else return FALSE;
	}
	 **/
	
/***  CHART *****************************************************/
	
	/**
	 * get_chart_display_types
	 * @return array of section data
	 * @author ctranel
	 **/
	public function get_chart_display_types() {
		return $this->{$this->db_group_name}
			//->where($this->tables['lookup_chart_types'] . '.active', 1)
			->get($this->tables['lookup_chart_types']);
	}
}
