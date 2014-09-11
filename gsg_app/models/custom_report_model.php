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
	/**
	 * @method create_block($data)
	 * @param array data to insert
	 * @access public
	 *
	 **/
	function create_block($data){
		$this->db->insert('users.dbo.blocks', $data);
		if($this->db->affected_rows() <= 0){
			$this->error = "Could not create report";
			return FALSE;
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
			$this->error = "Could not add block to page";
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * @method create_block($data)
	 * @param array data to insert
	 * @access public
	 *
	 **/
	function add_header_group($data){
		$this->db->insert('users.dbo.block_header_groups', $data);
		if($this->db->affected_rows() <= 0){
			$this->error = "Could not create report";
			return FALSE;
		}
		else {
			$arr_ret = $this->db
			->query("SELECT SCOPE_IDENTITY()")
			->result_array();
			return $arr_ret[0]['computed'];
		}
	}

	/**
	 * @method add_yaxes
	 * @param array of axes data
	 * @access public
	 *
	 **/
	function add_yaxes($data){
		$this->db->insert_batch('users.dbo.block_axes', $data);
		if($this->db->affected_rows() <= 0){
			$this->error = "Could not add yaxes to block";
			return FALSE;
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
		$this->db->insert_batch('users.dbo.block_axes', $data);
		if($this->db->affected_rows() <= 0){
			$this->error = "Could not add xaxes to block";
			return FALSE;
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
		$this->db->insert_batch('users.dbo.blocks_select_fields', $data);
		if($this->db->affected_rows() <= 0){
			$this->error = "Could not add columns";
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * @method add_sort_by
	 * @param array with block and page ids
	 * @access public
	 *
	 **/
	function add_sort_by($data){
		$this->db->insert_batch('users.dbo.blocks_sort_by', $data);
		if($this->db->affected_rows() <= 0){
			$this->error = "Could not add sort by to block";
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * @method add_where
	 * @param array with block and page ids
	 * @access public
	 *
	 **/
	function add_where($data){
//need to account for where groups
		$this->db->insert('users.dbo.blocks_sort_by', $data);
		if($this->db->affected_rows() <= 0){
			$this->error = "Could not add sort by to block";
			return FALSE;
		}
		return TRUE;
	}

	function get_tables_by_category($cat_id){
		return $this->db
			->query("WITH cteAnchor AS (
					 SELECT id, parent_id
					 FROM users.dbo.db_table_categories 
					 WHERE id = " . $cat_id . "
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
		$arr_return = array();
		$tmp = $this->get_tables_by_category($cat_id)->result_array();
		if(is_array($tmp)){
//			$arr_return[0] = 'Select one';
			foreach($tmp as $t){
				$arr_return[$t['id']] = $t['name'];
			}
		}
		return $arr_return;
	}
	
	function get_fields(){
		return $this->db->get('users.dbo.db_fields');
	}
	
	function get_fields_select_data($table_id){
		$arr_return = array();
		$this->db
		->select('id, db_field_name, name, is_timespan_field')
		->where('users.dbo.db_fields.db_table_id', $table_id);
		$tmp = $this->get_fields()->result_array();
		if(is_array($tmp)){
			foreach($tmp as $t){
				$arr_return[$t['id']] = array($t['id'], $t['db_field_name'], $t['name'], $t['is_timespan_field']);
			}
		}
		return $arr_return;
	}
	
	function get_insert_after_data($page_id){
		$arr_return = array();
		$tmp = $this->db
		->select('pb.list_order, b.name')
		->from('users.dbo.pages_blocks pb')
		->join('users.dbo.blocks b', 'pb.block_id = b.id', 'left')
		->where('pb.page_id', $page_id)
		->order_by('pb.list_order')
		->get()->result_array();
		if(is_array($tmp)){
			foreach($tmp as $t){
				$arr_return[$t['list_order']] = $t['name'];
			}
		}
		return $arr_return;
	}
}
