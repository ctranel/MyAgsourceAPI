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
            ->select('rb.id, b.name,b.[description],b.path,b.isactive,dt.name AS display_type,s.name AS scope,ct.name as chart_type,rb.max_rows,rb.cnt_row,rb.sum_row,rb.avg_row,rb.pivot_db_field,rb.bench_row,rb.is_summary,rb.keep_nulls')//, pb.page_id, pb.list_order
            ->where('b.isactive', 1)
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
			->select('rb.id, b.id AS block_id, b.name,b.[description],b.path,b.isactive,dt.name AS display_type,s.name AS scope,ct.name as chart_type,rb.max_rows,rb.cnt_row,rb.sum_row,rb.avg_row,rb.pivot_db_field,rb.bench_row,rb.is_summary,rb.keep_nulls')
			->where('b.isactive', 1)
			->join('users.dbo.reports rb', 'b.id = rb.block_id', 'inner')
			->join('users.dbo.lookup_display_types dt', 'b.display_type_id = dt.id', 'inner')
			->join('users.dbo.lookup_scopes s', 'b.scope_id = s.id', 'inner')
			->join('users.dbo.lookup_chart_types ct', 'rb.chart_type_id = ct.id', 'left')
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
     * getByPage
     * @param associative array of criteria
     * @param 2d array of joins ('table', 'condition')
     * @return array of section data
     * @author ctranel
     **/
    public function getByPage($page_id) {
        $page_id = (int)$page_id;
        $this->db
            ->select('pb.page_id, pb.list_order')
            ->join('users.dbo.pages_blocks pb', 'b.id = pb.block_id', 'inner')
            ->where('pb.page_id', $page_id)
            ->order_by('pb.list_order', 'asc');
        return $this->getBlocks();
    }

    /**
	 * @method getWhereData()
	 * @param int report id
     * @param bool is_metric
	 * @return returns multi-dimensional array, arr_sort_by field data and arr_sort_order
	 * @author ctranel
	 **/
	public function getWhereData($report_id, $is_metric){
        if($is_metric){
            $cond_select = "CASE WHEN mc.id IS NOT NULL THEN REPLACE(f.name, mc.imperial_abbrev, mc.metric_abbrev) ELSE f.name END AS [name]
			        , CASE WHEN mc.id IS NOT NULL THEN REPLACE(f.description, mc.imperial_abbrev, mc.metric_abbrev) ELSE f.description END AS [description]
					, CASE WHEN mc.id IS NOT NULL THEN REPLACE(f.unit_of_measure, mc.imperial_abbrev, mc.metric_abbrev) ELSE f.unit_of_measure END AS [unit_of_measure]";
            $cg_select = "NULL AS [name]
			        , NULL AS [description]
					, NULL AS [unit_of_measure]";
        }
        else{
            $cond_select = "f.name, f.description, f.unit_of_measure";
            $cg_select = "NULL AS name, NULL AS description,  NULL AS unit_of_measure";
        }

        return $this->db->query(
			"SELECT " . $cg_select . ", null AS db_field_id, null AS table_name, null AS db_field_name, null AS pdf_width, null AS default_sort_order, null AS datatype, null AS is_timespan, null AS is_natural_sort, null AS is_foreign_key, null AS is_nullable, null AS decimal_scale, null AS datatype, null AS max_length
				, wg.operator AS group_operator, wg.id, COALESCE(wg.parent_id, 0) AS parent_id, null AS condition_id, null as operator, null AS operand
				, null AS conversion_name, null AS metric_label, null AS metric_abbrev, null AS to_metric_factor, null AS metric_rounding_precision, null AS imperial_label, null AS imperial_abbrev, null AS to_imperial_factor, null AS imperial_rounding_precision
			FROM users.dbo.reports_where_groups wg
			WHERE wg.report_id = " . $report_id . "
			
			UNION
			
			SELECT " . $cond_select . ", f.id AS db_field_id, t.name AS table_name, f.db_field_name, f.pdf_width, f.default_sort_order, f.data_type as datatype, f.is_timespan_field as is_timespan, f.is_natural_sort, is_fk_field AS is_foreign_key, f.is_nullable, f.decimal_points AS decimal_scale, f.data_type as datatype, f.max_length
				, wg.operator AS group_operator, wc.id, wc.where_group_id AS parent_id, wc.id AS condition_id, wc.operator, wc.operand
				, mc.name AS conversion_name, mc.metric_label, mc.metric_abbrev, mc.to_metric_factor, mc.metric_rounding_precision, mc.imperial_label, mc.imperial_abbrev, mc.to_imperial_factor, mc.imperial_rounding_precision
			FROM users.dbo.reports_where_groups wg
                INNER JOIN users.dbo.reports_where_conditions wc ON wg.report_id = " . $report_id . " AND wg.id = wc.where_group_id
                INNER JOIN users.dbo.db_fields f ON wc.field_id = f.id
                INNER JOIN users.dbo.db_tables t ON f.db_table_id = t.id
                LEFT JOIN users.dbo.metric_conversion mc ON f.conversion_id = mc.id
			WHERE wg.report_id = " . $report_id)
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
			->select("f.id AS db_field_id, t.name AS table_name, f.db_field_name
				, f.name, f.description, f.pdf_width, f.default_sort_order, f.data_type, f.is_timespan_field as is_timespan
				, f.is_natural_sort, is_fk_field AS is_foreign_key, f.is_nullable, f.decimal_points AS decimal_scale, f.unit_of_measure, f.data_type as datatype, f.max_length, s.sort_order")
			->from('users.dbo.reports_sort_by s')
			->join('users.dbo.db_fields f', 's.field_id = f.id AND s.report_id = ' . $report_id , 'inner')
			->join('users.dbo.db_tables t', 'f.db_table_id = t.id', 'inner')
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
	public function getFieldData($report_id, $is_metric){
        if($is_metric){
            $this->db
                ->select("CASE WHEN mc.id IS NOT NULL THEN REPLACE(f.name, mc.imperial_abbrev, mc.metric_abbrev) ELSE f.name END AS [name]
			        , CASE WHEN mc.id IS NOT NULL THEN REPLACE(f.description, mc.imperial_abbrev, mc.metric_abbrev) ELSE f.description END AS [description]
					, CASE WHEN mc.id IS NOT NULL THEN REPLACE(f.unit_of_measure, mc.imperial_abbrev, mc.metric_abbrev) ELSE f.unit_of_measure END AS [unit_of_measure]");
        }
        else{
            $this->db
                ->select("f.name, f.description, f.unit_of_measure");
        }

		return $this->db
			->select("f.rsf_id AS id, f.db_field_id, f.table_name, f.db_field_name, f.pdf_width, f.default_sort_order, f.data_type as datatype, f.max_length
			        , f.category_id, f.decimal_points AS decimal_scale, f.is_timespan_field as is_timespan, f.is_fk_field AS is_foreign_key
					, f.is_nullable, f.is_sortable, f.is_natural_sort, f.is_displayed, f.supp_id, f.a_href, f.a_rel, f.a_title
					, f.a_class, f.head_a_href, f.head_a_rel, f.head_a_title, f.head_a_class, f.head_supp_id, f.head_comment, f.aggregate
					, f.display_format, f.table_header_group_id, f.chart_type, f.axis_index, f.trend_type, f.field_group, f.field_group_ref_key
					, mc.name AS conversion_name, mc.metric_label, mc.metric_abbrev, mc.to_metric_factor, mc.metric_rounding_precision, mc.imperial_label, mc.imperial_abbrev, mc.to_imperial_factor, mc.imperial_rounding_precision")
			->where('report_id', $report_id)
//			->order_by('field_group')
			->order_by('list_order')
            ->join('users.dbo.metric_conversion mc', 'f.conversion_id = mc.id', 'left')
			->get('users.dbo.v_report_field_data f')
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
	public function getChartAxes($report_id, $is_metric){
        if($is_metric){
            $this->db
                ->select("CASE WHEN mc.id IS NOT NULL THEN REPLACE(f.name, mc.imperial_abbrev, mc.metric_abbrev) ELSE f.name END AS [name]
			        , CASE WHEN mc.id IS NOT NULL THEN REPLACE(f.description, mc.imperial_abbrev, mc.metric_abbrev) ELSE f.description END AS [description]
					, CASE WHEN mc.id IS NOT NULL THEN REPLACE(f.unit_of_measure, mc.imperial_abbrev, mc.metric_abbrev) ELSE f.unit_of_measure END AS [unit_of_measure]
                    , CASE WHEN mc.id IS NOT NULL THEN REPLACE(text, mc.imperial_abbrev, mc.metric_abbrev) ELSE text END AS [text]");
       }
        else{
            $this->db
                ->select("f.name, f.description, f.unit_of_measure, text");
        }

		$this->db
		->select("a.id, a.x_or_y, a.min, a.max, a.opposite, a.data_type, a.db_field_id, CONCAT(db.name, '.', t.db_schema, '.', t.name) AS table_name, f.db_field_name, f.pdf_width, f.default_sort_order, f.data_type as datatype, f.max_length
		            ,f.decimal_points AS decimal_scale, f.is_timespan_field as is_timespan, f.is_fk_field AS is_foreign_key, f.is_nullable, f.is_natural_sort, c.name AS category
					,mc.name AS conversion_name,mc.metric_label,mc.metric_abbrev,mc.to_metric_factor,mc.metric_rounding_precision,mc.imperial_label,mc.imperial_abbrev,mc.to_imperial_factor,mc.imperial_rounding_precision")
		->from('users.dbo.chart_axes AS a')
		->join('users.dbo.chart_categories AS c', 'a.id = c.block_axis_id', 'left')
		->join('users.dbo.db_fields AS f', 'a.db_field_id = f.id', 'left')
		->join('users.dbo.db_tables AS t', 'f.db_table_id = t.id', 'left')
        ->join('users.dbo.db_databases AS db', 't.database_id = db.id', 'left')
        ->join('users.dbo.metric_conversion mc', 'a.conversion_id = mc.id', 'left')
		->where('a.report_id', $report_id)
		->order_by('a.list_order', 'asc')
		->order_by('c.list_order', 'asc');
		$result = $this->db->get()->result_array();

		return $result;
	}
}
