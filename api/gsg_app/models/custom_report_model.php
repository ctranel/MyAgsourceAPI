<?php
class Custom_report_model extends CI_Model {

	protected $error;
	
	public function __construct(){
		parent::__construct();

	}
	
	public function error(){
		return $this->error;
	}

	function start_transaction(){
		$this->db->trans_start();
	}
	
	function complete_transaction(){
		$this->db->trans_complete();
	}

    function trans_status(){
        $this->db->trans_status();
    }

    function reportMeta($report_id){
        $results = $this->db
            ->select("r.[block_id],r.[id] AS report_id,b.display_type_id,r.[chart_type_id],r.[max_rows],r.[cnt_row],r.[sum_row],r.[avg_row],r.[pivot_db_field],r.[bench_row],r.[is_summary],r.[keep_nulls]")
            ->from("[users].[dbo].[reports] r")
            ->join("users.dbo.blocks b", "r.block_id = b.id", "inner")
            ->where("r.id", $report_id)
            ->get()->result_array();

        if(!$results){
            throw new Exception('No report meta data found.');
        }
        $err = $this->db->_error_message();
        if(!empty($err)){
            throw new \Exception($err);
        }

        if(isset($results[0]) && is_array($results[0])) {
            return $results[0];
        }

        return [];

    }

	/**
	 * @method create_block($data)
	 * @param array data to insert
	 * @access public
	 *
	 **/
	function create_block($data){
		$this->db->insert('users.dbo.blocks', $data);

		if($this->db->affected_rows() <= 0){
			throw new \Exception("Could not create block");
		}
		else {
			$arr_ret = $this->db
			->query("SELECT SCOPE_IDENTITY()")
			->result_array();
			return $arr_ret[0]['computed'];
		}
	}

    /**
     * @method create_report($data)
     * @param array data to insert
     * @access public
     *
     **/
    function create_report($data){
        $this->db->insert('users.dbo.reports', $data);
        if($this->db->affected_rows() <= 0){
            throw new \Exception("Could not create report");
        }
        else {
            $arr_ret = $this->db
                ->query("SELECT SCOPE_IDENTITY()")
                ->result_array();
            return $arr_ret[0]['computed'];
        }
    }

    /**
	 * @method add_block_to_page
	 * @param array with block and page ids
	 * @access public
	 *
	 **/
	function add_block_to_page($data){
		$this->db->insert('users.dbo.pages_blocks', $data);
		if($this->db->affected_rows() <= 0){
            throw new \Exception("Could not add block to page");
		}
		return TRUE;
	}

	/**
	 * @method create_block($data)
	 * @param array data to insert
     * @return int
	 * @access public
	 *
	 **/
	function add_header_group($data){
		$this->db->insert('users.dbo.table_header_groups', $data);
		if($this->db->affected_rows() <= 0){
            throw new \Exception("Could not create header group");
		}

        $arr_ret = $this->db
        ->query("SELECT SCOPE_IDENTITY()")
        ->result_array();
        return $arr_ret[0]['computed'];
	}

	/**
	 * @method add_yaxes
	 * @param array of axes data
	 * @access public
	 *
	 **/
	function add_yaxes($data){
        if(!isset($data) || empty($data)){
            return;
        }
        $prepped = $this->prepBatchData($data);
        $sql = "INSERT INTO users.dbo.chart_axes (" . implode(",", $prepped['keys']) . ")
            VALUES (" . implode("),(", $prepped['values']) . ")";
        $this->db->query($sql);

		if($this->db->affected_rows() <= 0){
            throw new \Exception("Could not add Y axis to chart");
		}
		return TRUE;
	}

	/**
	 * @method add_xaxes
	 * @param array of axes data
	 * @access public
	 *
	 **/
	function add_xaxis($data){
        try{
            $prepped = $this->prepBatchData($data);
        }
        catch(\Exception $e){
            throw new \Exception("No data found for X axis, the chart cannot be created.");
        }

        $sql = "INSERT INTO users.dbo.chart_axes (" . implode(",", $prepped['keys']) . ")
            VALUES (" . implode("),(", $prepped['values']) . ")";
        $this->db->query($sql);

		if($this->db->affected_rows() <= 0){
            throw new \Exception("Could not add X axis to chart");
		}
		return TRUE;
	}

	/**
	 * @method add_columns
	 * @param 2-d array with column (block-field) data
	 * @access public
	 *
	 **/
	function add_columns($data){
        try{
            $prepped = $this->prepBatchData($data);
        }
        catch(\Exception $e){
            throw new \Exception("No table columns found, report cannot be created.");
        }

        $sql = "INSERT INTO users.dbo.reports_select_fields (" . implode(",", $prepped['keys']) . ")
            VALUES (" . implode("),(", $prepped['values']) . ")";
		$this->db->query($sql);

		if($this->db->affected_rows() <= 0){
            throw new \Exception("Could not add columns to report");
		}

		$sql = "UPDATE rsf SET display_format = 'MM-dd-yy' FROM users.dbo.reports_select_fields rsf INNER JOIN users.dbo.db_fields db ON field_id = db.id WHERE db.data_type LIKE '%date%' AND report_id = " . $data[0]['report_id'];
		$this->db->query($sql);
		return TRUE;
	}

	function prepBatchData($data){
        if(!isset($data) || empty($data)){
            throw new \Exception('No data passed to batch function.');
        }

        foreach($data as $r){
            if(!isset($keys)){
                $keys = array_keys($r);
            }
            foreach($r AS &$v){
                if(!isset($v)){
                    $v = "NULL";
                }
                elseif(!is_int($v) && !is_float($v) && !is_bool($v)){
                    $v = "'$v'";
                }
            }
            $values[] = implode(",", $r);
        }

        return ['keys'=>$keys, 'values'=>$values];
    }

	/**
	 * @method add_sort_by
	 * @param array with block and page ids
	 * @access public
	 *
	 **/
	function add_sort_by($data){
	    if(!isset($data) || empty($data)){
	        return;
        }
        $prepped = $this->prepBatchData($data);
        $sql = "INSERT INTO users.dbo.reports_sort_by (" . implode(",", $prepped['keys']) . ")
            VALUES (" . implode("),(", $prepped['values']) . ")";
        $this->db->query($sql);

		if($this->db->affected_rows() <= 0){
            throw new \Exception("Could not add sort to report");
		}
		return TRUE;
	}

	/**
	 * @method add_where_group
	 * @param array with where group data
     * @return int
	 * @access public
	 *
	 **/
	function add_where_group($data){
		$this->db->insert('users.dbo.reports_where_groups', $data);
		if($this->db->affected_rows() <= 0){
            throw new \Exception("Could not add conditions to report");
		}
        $arr_ret = $this->db
            ->query("SELECT SCOPE_IDENTITY()")
            ->result_array();
        return $arr_ret[0]['computed'];
	}

    /**
     * @method add_where_conditions
     * @param array with where condition data
     * @return int
     * @access public
     *
     **/
    function add_where_conditions($data){
        if(!isset($data) || empty($data)){
            return;
        }
        $prepped = $this->prepBatchData($data);
        $sql = "INSERT INTO users.dbo.reports_where_conditions (" . implode(",", $prepped['keys']) . ")
            VALUES (" . implode("),(", $prepped['values']) . ")";
        $this->db->query($sql);


//        $this->db->insert_batch('users.dbo.reports_where_conditions', $data);
        if($this->db->affected_rows() <= 0){
            throw new \Exception("Could not add conditions to report");
        }
        $arr_ret = $this->db
            ->query("SELECT SCOPE_IDENTITY()")
            ->result_array();
        return $arr_ret[0]['computed'];
    }

    function get_tables_by_category($cat_id){
		return $this->db
			->query("WITH cteAnchor AS (
					 SELECT id, parent_id
					 FROM users.dbo.db_table_categories 
					 WHERE id = " . (int)$cat_id . "
				), cteRecursive AS (
					SELECT id, parent_id
					  FROM cteAnchor
					 UNION all 
					 SELECT t.id, t.parent_id
					 FROM users.dbo.db_table_categories t
					 join cteRecursive r ON r.id = t.parent_id
				)
				SELECT [id]
				      ,[category_id]
				      ,[database_id]
				      ,[name]
				      ,[description]
				      ,[is_public]
				  FROM [users].[dbo].[db_tables]
				  WHERE category_id IN(
								SELECT DISTINCT id FROM cteRecursive)");
			
	}
	
	function get_tables_select_data($cat_id){
		$tmp = $this->get_tables_by_category($cat_id)->result_array();
		if(isset($tmp) && is_array($tmp)){
            return $tmp;
		}
	}
	
	function get_fields(){
		return $this->db->get('users.dbo.db_fields');
	}
	
	function get_fields_select_data($table_id){
		$this->db
		->select('id, db_field_name, name, is_timespan_field, data_type')
		->where('users.dbo.db_fields.db_table_id', $table_id);
		$tmp = $this->get_fields()->result_array();
		if(isset($tmp) && is_array($tmp)){
			return $tmp;
		}
	}
	
	function get_insert_after_data($page_id){
		$tmp = $this->db
		->select('pb.list_order, b.name')
		->from('users.dbo.pages_blocks pb')
		->join('users.dbo.blocks b', 'pb.block_id = b.id', 'left')
		->where('pb.page_id', $page_id)
		->order_by('pb.list_order')
		->get()->result_array();
		if(isset($tmp) && is_array($tmp)){
			return $tmp;
		}
	}

    /**
     * getChartDisplayTypes
     * @return array of chart display types
     * @author ctranel
     **/
    public function getChartDisplayTypes() {
        return $this->db
            //->where($this->tables['lookup_chart_types'] . '.active', 1)
            ->get('users.dbo.lookup_chart_types');
    }

    /**
     * @method get_pages_select_data()
     * @return int id of section
     * @access public
     *
     **/
    function getPagesSelectDataByUser($user_id, $section_id){
        $user_id = (int)$user_id;
        $section_id = (int)$section_id;

        $arr_return = array();
        $this->db
            ->select('id, name')
            ->where('users.dbo.pages.section_id', $section_id)
            ->where('(users.dbo.pages.user_id IS NULL OR users.dbo.pages.user_id = ' . $user_id . ')');
        $tmp = $this->get_pages()->result_array();
        if(isset($tmp) && is_array($tmp)){
            return $tmp;
        }
    }

    /**
     * get_pages
     * @return array of page data
     * @author ctranel
     **/
    private function get_pages() {
        return $this->db
            ->where('users.dbo.pages.isactive', 1)
            ->order_by('users.dbo.pages.list_order')
            ->get('users.dbo.pages');
    }

}

