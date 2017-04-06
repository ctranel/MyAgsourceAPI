<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Block_model extends CI_Model {
	public function __construct(){
		parent::__construct();
	}

	/**
	 * @method getBlock
	 * @return array of block data
	 * @author ctranel
	 **/
	public function getBlock($id) {
		$id = (int)$id;

        $this->db
			->select('b.id, b.name,b.[description],b.path,dt.name AS display_type,s.name AS scope,b.isactive, 1 AS list_order')//,b.chart_type_id,b.max_rows,b.cnt_row,b.sum_row,b.avg_row,b.pivot_db_field,b.bench_row,b.is_summary
			->where('b.isactive', 1)
            ->where('b.id', $id)
			->join('users.dbo.lookup_display_types dt', 'b.display_type_id = dt.id', 'inner')
			->join('users.dbo.lookup_scopes s', 'b.scope_id = s.id', 'inner')
			->from('users.dbo.blocks b');
		$results = $this->db->get()->result_array();

        if(isset($results[0]) && is_array($results[0]) && !empty($results[0])){
            return $results[0];
        }
        throw new \Exception('No data found for requested content.');
	}

    /**
     * @method getBlocks
     * @return array of block data
     * @author ctranel
     **/
    public function getBlocks() {
        $this->db
            ->select('b.id, pb.page_id, b.name,b.[description],b.path,dt.name AS display_type,s.name AS scope,b.isactive, pb.list_order')//,b.chart_type_id,b.max_rows,b.cnt_row,b.sum_row,b.avg_row,b.pivot_db_field,b.bench_row,b.is_summary
            ->where('b.isactive', 1)
            ->join('users.dbo.lookup_display_types dt', 'b.display_type_id = dt.id', 'inner')
            ->join('users.dbo.lookup_scopes s', 'b.scope_id = s.id', 'inner')
            ->join('users.dbo.pages_blocks pb', 'b.id = pb.block_id', 'inner')
            ->order_by('pb.list_order', 'asc')
            ->from('users.dbo.blocks b');
        $results = $this->db->get()->result_array();
        $ret = [];

        array_walk($results, function($v, $k) use (&$ret){
            $ret[$v['list_order']] = $v;
        });

        return $ret;
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
     * getKeysByBlock
     * @param int block_ic
     * @return array of db field names
     * @author ctranel
     **/
    public function getKeysByBlock($block_id) {
        $res = $this->db
            ->select('db_field_name')
            ->distinct()
            ->from('users.dbo.vma_db_fields_by_block')
            ->where('is_fk_field', 1)
            ->where('block_id', $block_id)
            ->get()
            ->result_array();

        return array_column($res, 'db_field_name');
    }

    /**
     * getKeysByContentId
     * @param int content id
     * @return array of db field names
     * @author ctranel
     **/
    public function getKeysByContentId($content_type_id, $content_id) {
        $res = $this->db
            ->select('db_field_name')
            ->from('users.dbo.vma_db_fields_by_block')
            ->where('is_fk_field', 1)
            ->where('content_id', $content_id)
            ->where('display_type_id', $content_type_id)
            ->get()
            ->result_array();

        return array_column($res, 'db_field_name');
    }

    /**
     * getKeysByContentId
     * @param string content category
     * @param int content id
     * @return array of db field names
     * @author ctranel
     **/
    public function getDisplayDataByContent($content_category, $content_id) {
        $res = $this->db
            ->select('block_id, block_name, display_type_id')
            ->from('users.dbo.vma_display_objects_by_block')
            ->where('content_id', $content_id)
            ->where('display_category', $content_category)
            ->get()
            ->result_array();

        if($res === false){
            throw new \Exception('Display content not found.');
        }
        $err = $this->db->_error_message();

        if(!empty($err)){
            throw new \Exception($err);
        }

        if(isset($res[0]) && is_array($res[0])) {
            return $res[0];
        }

        return [];
    }
}
