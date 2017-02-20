<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class report_block_model extends CI_Model {
	public function __construct(){
		parent::__construct();
		$this->tables = $this->config->item('tables', 'ion_auth');
	}

    /**
     * @method getBlock
     * @param int block_id
     * @return array of block data
     * @author ctranel
     **/
    public function getBlock($block_id) {
        $this->db
            ->select('rb.id, b.name,b.[description],b.path,b.active,dt.name AS display_type,s.name AS scope,ct.name as chart_type,rb.max_rows,rb.cnt_row,rb.sum_row,rb.avg_row,rb.pivot_db_field,rb.bench_row,rb.is_summary,rb.keep_nulls')//, pb.page_id, pb.list_order
            ->where('b.active', 1)
            ->where('b.id', $block_id)
            ->join('users.dbo.reports rb', 'b.id = rb.block_id', 'inner')
            ->join('users.dbo.lookup_display_types dt', 'b.display_type_id = dt.id', 'inner')
            ->join('users.dbo.lookup_scopes s', 'b.scope_id = s.id', 'inner')
            ->join('users.dbo.lookup_chart_types ct', 'rb.chart_type_id = ct.id', 'left')
            ->from('users.dbo.blocks' . ' b');
        return $this->db->get()->result_array();
    }

    /**
	 * @method getBlocks
	 * @return array of block data
	 * @author ctranel
	 **/
	public function getBlocks() {
		$this->db
			->select('rb.id, pb.page_id, b.name,b.[description],b.path,b.active,dt.name AS display_type,s.name AS scope,ct.name as chart_type,rb.max_rows,rb.cnt_row,rb.sum_row,rb.avg_row,rb.pivot_db_field,rb.bench_row,rb.is_summary,rb.keep_nulls, pb.list_order')
			->where('b.active', 1)
			->join('users.dbo.reports rb', 'b.id = rb.block_id', 'inner')
			->join('users.dbo.lookup_display_types dt', 'b.display_type_id = dt.id', 'inner')
			->join('users.dbo.lookup_scopes s', 'b.scope_id = s.id', 'inner')
			->join('users.dbo.pages_blocks pb', 'b.id = pb.block_id', 'inner')
			->join('users.dbo.lookup_chart_types ct', 'rb.chart_type_id = ct.id', 'left')
			->order_by('list_order', 'asc')
			->from('users.dbo.blocks' . ' b');
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
	 * @method getWhereData()
	 * @param int block id
	 * @return returns multi-dimensional array, arr_sort_by field data and arr_sort_order
	 * @author ctranel
	 * @todo: implement nested where group iteration (i.e., parent_id field of where groups)
	 **/
	public function getWhereData($report_id){
		return $this->db
			->select("f.id AS db_field_id, f.db_table_id, f.db_field_name
				, f.name, f.description, f.pdf_width, f.default_sort_order, f.data_type, f.is_timespan_field as is_timespan
				, f.is_natural_sort, is_fk_field AS is_foreign_key, f.is_nullable, f.decimal_points AS decimal_scale, f.unit_of_measure, f.data_type as datatype, f.max_length, wg.operator, wc.where_group_id, wc.condition")
			->from('users.dbo.reports_where_groups wg')
			//->join('users.dbo.reports_where_groups wg2', 'wg.id = wg2.parent_id', 'inner')
			->join('users.dbo.reports_where_conditions wc', 'wg.report_id = ' . $report_id . ' AND wg.id = wc.where_group_id', 'inner')
			->join('users.dbo.db_fields f', 'wc.field_id = f.id' , 'inner')
			->get()
			->result_array();
	}
	
	/**
	 * @method getSortData()
	 * @param int block id
	 * @return returns multi-dimensional array, arr_sort_by field data and arr_sort_order
	 * @author ctranel
	 **/
	public function getSortData($report_id){
		return $this->db
			->select("f.id AS db_field_id, f.db_table_id, f.db_field_name
				, f.name, f.description, f.pdf_width, f.default_sort_order, f.data_type, f.is_timespan_field as is_timespan
				, f.is_natural_sort, is_fk_field AS is_foreign_key, f.is_nullable, f.decimal_points AS decimal_scale, f.unit_of_measure, f.data_type as datatype, f.max_length, s.sort_order")
			->from('users.dbo.reports_sort_by s')
			->join('users.dbo.db_fields f', 's.field_id = f.id AND s.report_id = ' . $report_id , 'inner')
//			->join('users.dbo.db_tables t', 'f.db_table_id = t.id', 'inner')
//			->join('users.dbo.db_databases d', 't.database_id = d.id' , 'inner')
			->order_by('s.list_order', 'asc')
			->get()
			->result_array();
	}
	
	/**
	 * @method getFieldData()
	 * @param int block id
	 * @return returns multi-dimensional array, one row for each field
	 * @author ctranel
	 **/
	public function getFieldData($report_id){
		return $this->db
			->select("rsf_id AS id, db_field_id, table_name, db_field_name, name, description, pdf_width, default_sort_order, data_type as datatype, max_length, category_id,
					decimal_points AS decimal_scale, unit_of_measure, is_timespan_field as is_timespan, is_fk_field AS is_foreign_key, is_nullable, is_sortable, is_natural_sort,
					is_displayed, supp_id, a_href, a_rel, a_title, a_class, head_a_href, head_a_rel, head_a_title, head_a_class, head_supp_id, head_comment, aggregate,
					display_format, table_header_group_id, chart_type, axis_index, trend_type, field_group, field_group_ref_key")
			->where('report_id', $report_id)
//			->order_by('field_group')
			->order_by('list_order')
			->get('users.dbo.v_report_field_data')
			->result_array();
	}
	
	/**
	 * @method getFieldGroupData()
	 * @param int block id
	 * @return returns result set array
	 * @author ctranel
	 **/
	public function getFieldGroupData($report_id){
		return $this->db
			->select("[field_group_num],[name],[trend_type]")
			->where('report_id', $report_id)
			->order_by('field_group_num')
			->get('users.dbo.field_groups')
			->result_array();
	}
	
	/**
	 * @method getFieldByName()
	 * @param int block id
	 * @param string field name
	 * @return returns array
	 * @author ctranel
	 **/
	public function getFieldByName($report_id, $field_name){
		$ret = $this->db
			->select("rsf_id AS id, db_field_id, table_name, db_field_name, name, description, pdf_width, default_sort_order, data_type as datatype, max_length, category_id, 
					decimal_points AS decimal_scale, unit_of_measure, is_timespan_field as is_timespan, is_fk_field AS is_foreign_key, is_nullable, is_sortable, is_natural_sort,
					is_displayed, supp_id, a_href, a_rel, a_title, a_class, head_a_href, head_a_rel, head_a_title, head_a_class, head_supp_id, head_comment, aggregate,
					display_format, table_header_group_id, chart_type, axis_index, trend_type, field_group, field_group_ref_key")
			->where('report_id', $report_id)
			->where('db_field_name', $field_name)
			->get('users.dbo.v_report_field_data')
			->result_array();
		if(isset($ret) && is_array($ret)){
			return $ret[0];
		}
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
	 * 
	 * @todo: add supplemental data
	 **/
	public function getHeaderGroups($report_id){
		$grouping_sql = "WITH cteAnchor AS (
					 SELECT rs.report_id, th.id, th.[text], th.parent_id, th.list_order
					 FROM users.dbo.table_header_groups th
					 	LEFT JOIN users.dbo.reports_select_fields rs ON th.id = rs.table_header_group_id
					 WHERE report_id = ".$report_id."
				), cteRecursive AS (
					SELECT report_id, id, [text], parent_id, list_order
					  FROM cteAnchor
					 UNION all 
					 SELECT r.report_id, t.id, t.[text], t.parent_id, t.list_order
					 FROM users.dbo.table_header_groups t
					 join cteRecursive r ON r.parent_id = t.id
				)
				SELECT h.* FROM (
					SELECT DISTINCT * FROM cteRecursive
				) h

				ORDER BY parent_id, list_order";

		$arr_groupings = $this->db->query($grouping_sql)->result_array();
		return $arr_groupings;
	}
	
	/**
	 * get_chart_display_types
	 * @return array of section data
	 * @author ctranel
	 **/
	public function get_chart_display_types() {
		return $this->db
			//->where($this->tables['lookup_chart_types'] . '.active', 1)
			->get($this->tables['lookup_chart_types']);
	}

	/**
	 * @method getChartAxes - retrieve data for categories, axes, etc.
	 * @param int block id
	 * @return array of meta data for the block
	 * @access public
	 *
	 **/
	public function getChartAxes($report_id){
		$this->db
		->select("a.id, a.x_or_y, a.min, a.max, a.opposite, a.data_type, a.db_field_id, t.name AS table_name, f.db_field_name, f.name, f.description, f.pdf_width, f.default_sort_order, f.data_type as datatype, f.max_length,
					f.decimal_points AS decimal_scale, f.unit_of_measure, f.is_timespan_field as is_timespan, f.is_fk_field AS is_foreign_key, f.is_nullable, f.is_natural_sort, text, c.name AS category")
		->from('users.dbo.chart_axes AS a')
		->join('users.dbo.chart_categories AS c', 'a.id = c.block_axis_id', 'left')
		->join('users.dbo.db_fields AS f', 'a.db_field_id = f.id', 'left')
		->join('users.dbo.db_tables AS t', 'f.db_table_id = t.id', 'left')
		->where('a.report_id', $report_id)
		->order_by('a.list_order', 'asc')
		->order_by('c.list_order', 'asc');
		$result = $this->db->get()->result_array();

		return $result;
	}
}
