<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Block_model extends CI_Model {
	public function __construct(){
		parent::__construct();
		$this->db_group_name = 'default';
		$this->{$this->db_group_name} = $this->load->database($this->db_group_name, TRUE);
		$this->tables = $this->config->item('tables', 'ion_auth');
	}

	/**
	 * @method get_blocks
	 * @return array of block data
	 * @author ctranel
	 **/
	public function get_blocks() {
		$this->db
			->select('name,[description],url_segment,display_type_id, active')
			->where($this->tables['blocks'] . '.active', 1)
			->order_by('list_order', 'asc')
			->from($this->tables['blocks']);
		return $this->db->get()->result_array();
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
	 * get_block_links
	 * @param int section id
	 * @return array of block info keyed by url_segment
	 * @author ctranel
	 **/
	public function get_block_links($section_id = NULL) {
		$arr_return = array();
		if(isset($section_id)) $this->{$this->db_group_name}->where('p.section_id', $section_id);
		$result = $this->{$this->db_group_name}
		->select("p.id AS page_id, b.id, p.section_id, b.url_segment, b.name, ct.name AS chart_type, b.description, p.url_segment AS page, p.name AS page_name, CASE WHEN dt.name LIKE '%chart' THEN 'chart' ELSE dt.name END AS display_type,s.path AS section_path, b.max_rows, b.cnt_row, b.sum_row, b.avg_row, b.bench_row, pf.db_field_name AS pivot_db_field, b.is_summary")
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
		->order_by('pb.list_order', 'asc')
		->get($this->tables['sections'] . ' AS s')->result_array();
		if(is_array($result) && !empty($result)){
			foreach($result as $r){
				$arr_return[$r['page']]['page_id'] = $r['page_id'];
				$arr_return[$r['page']]['name'] = $r['page_name'];
				if(empty($r['url_segment']) === FALSE){
					$arr_return[$r['page']]['blocks'][$r['url_segment']] = array(
						'id'=>$r['id'],
						'section_id'=>$r['section_id'],
						'name'=>$r['name'],
						'description'=>$r['description'],
						'url_segment'=>$r['url_segment'],
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
					$arr_return[$r['page']]['blocks'][$r['url_segment']] = array(
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
