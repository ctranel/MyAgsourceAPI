<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Block_model extends CI_Model {
	public function __construct(){
		parent::__construct();
	}

	/**
	 * @method getBlocks
	 * @return array of block data
	 * @author ctranel
	 **/
	public function getBlocks() {
		$this->db
			->select('b.id, pb.page_id, b.name,b.[description],b.path,dt.name AS display_type,s.name AS scope,b.active')//,b.chart_type_id,b.max_rows,b.cnt_row,b.sum_row,b.avg_row,b.pivot_db_field,b.bench_row,b.is_summary
			->where('b.active', 1)
			->join('users.dbo.lookup_display_types dt', 'b.display_type_id = dt.id', 'inner')
			->join('users.dbo.lookup_scopes s', 'b.scope_id = s.id', 'inner')
			->join('users.dbo.pages_blocks pb', 'b.id = pb.block_id', 'inner')
			->order_by('list_order', 'asc')
			->from('users.dbo.blocks b');
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
	 * get_block_links
	 * @param int section id
	 * @return array of block info keyed by path
	 * @author ctranel
	 **/
	public function getCompleteData() {
		$arr_return = [];
		if(isset($section_id)) $this->db->where('p.section_id', $section_id);
		$result = $this->db
		->select("p.id AS page_id, b.id, p.section_id, b.path, b.name, ct.name AS chart_type, b.description, p.path AS page, p.name AS page_name, CASE WHEN dt.name LIKE '%chart' THEN 'chart' ELSE dt.name END AS display_type,s.path AS section_path, b.max_rows, b.cnt_row, b.sum_row, b.avg_row, b.bench_row, pf.db_field_name AS pivot_db_field, b.is_summary")
		->join('users.dbo.pages AS p', 'p.section_id = s.id', 'left')
		->join('users.dbo.pages_blocks AS pb', 'p.id = pb.page_id', 'left')
		->join('users.dbo.blocks AS b', 'pb.block_id = b.id', 'left')
		->join('users.dbo.lookup_display_types AS dt', 'b.display_type_id = dt.id', 'left')
		->join('users.dbo.lookup_chart_types AS ct', 'b.chart_type_id = ct.id', 'left')
		->join('users.dbo.db_fields AS pf', 'pf.id = b.pivot_db_field', 'left')
		//->where($this->tables['blocks'] . '.display IS NOT NULL')
		->where('b.path IS NOT NULL')
		->order_by('s.list_order', 'asc')
		->order_by('p.list_order', 'asc')
		->order_by('pb.list_order', 'asc')
		->get('users.dbo.sections AS s')->result_array();
//@todo: remove code below from model file
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
}
