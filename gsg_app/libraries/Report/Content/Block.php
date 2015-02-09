<?php
namespace myagsource\Report\Content;

require_once APPPATH . 'libraries/Report/iBlock.php';
//require_once APPPATH . 'libraries/Site/iWebContentRepository.php';

use myagsource\Site\iBlock;
use myagsource\dhi\Herd;
/**
* Name:  Block
*
* Author: ctranel
*  
* Created:  02-02-2015
*
* Description:  Contains properties and methods specific to displaying blocks of the website.
*
*/

abstract class Block implements iBlock {
	/**
	 * block id
	 * @var int
	 **/
	protected $id;

	/**
	 * page_id
	 * @var int
	 **/
	protected $page_id;

	/**
	 * block name
	 * @var string
	 **/
	protected $name;
	
	/**
	 * block description
	 * @var string
	 **/
	protected $description;
	
	/**
	 * block path
	 * @var string
	 **/
	protected $path;
	
	/**
	 * primary table name
	 * @var string
	 **/
	protected $primary_table_name;
	
	/**
	 * collection of ReportField objects
	 * @var SplObjectStorage
	 **/
	protected $report_fields;
	
	/**
	 * collection of DbField objects
	 * @var SplObjectStorage
	 **/
	protected $group_by;

	/**
	 * collection of WhereGroup objects
	 * @var SplObjectStorage
	 **/
	protected $where_groups;
	
	/**
	 * collection of Sort objects
	 * @var SplObjectStorage
	 **/
	protected $sorts;
	
	/**
	 * DbField object
	 * @var DbField
	 **/
	protected $pivot_db_field;
	
	/**
	 * max_rows
	 * @var int
	 **/
	protected $max_rows;

	/**
	 * cnt_row
	 * @var boolean
	 **/
	protected $cnt_row;
	
	/**
	 * sum_row
	 * @var boolean
	 **/
	protected $sum_row;
	
		/**
	 * avg_row
	 * @var boolean
	 **/
	protected $avg_row;
	
	/**
	 * bench_row
	 * @var boolean
	 **/
	protected $bench_row;
	
	/**
	 * display_type
	 * @var string
	 **/
	protected $display_type;
	
	/**
	 * is_summary
	 * @var boolean
	 **/
	protected $is_summary;
	
	/**
	 * scope
	 * @var string
	 **/
	protected $scope;
	
	/**
	 * active
	 * @var boolean
	 **/
	protected $active;
	
	
/**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct($id, $page_id, $name, $description, $scope, $active, $path, $max_rows, $cnt_row, 
			$sum_row, $avg_row, $bench_row, $is_summary, \SplObjectStorage $report_fields,
			\SplObjectStorage $group_by_fields, $display_type) {
		$this->id = $id;
		$this->page_id = $page_id;
		$this->name = $name;
		$this->description = $description;
		$this->scope = $scope;
		$this->active = $active;
		$this->path = $path;
//set these vars
		$this->max_rows = $max_rows;
		$this->cnt_row = $cnt_row;
		$this->sum_row = $sum_row;
		$this->avg_row = $avg_row;
		$this->bench_row = $bench_row;
		//$this->pivot_db_field = $pivot_db_field;
		$this->is_summary = $is_summary;
		$this->report_fields = $report_fields;
		//$this->group_by_fields = $group_by_fields;
		$this->display_type = $display_type;
		//$this->sort = $sort;
	}
	
	public function id(){
		return $this->id;
	}

	public function path(){
		return $this->path;
	}

	public function name(){
		return $this->name;
	}

	public function displayBenchRow(){
		return $this->bench_row;
	}

	/**
	 * @method setPivot()
	 * @param DbField pivot field
	 * @return void
	 * @access public
	* */
	protected function setPivot(DbField $pivot_db_field){
		$this->db_pivot_field = $pivot_db_field;
	}
	
	/**
	 * @method setSort()
	 * @param SplObjectStorage of Sort objects
	 * @return void
	 * @access public
	* */
	protected function setSort(\SplObjectStorage $sorts){
		$this->sorts = $sorts;
	}
	
	/**
	 * @method setGroupBy()
	 * @param SplObjectStorage of GroupBy objects
	 * @return void
	 * @access public
	* */
	protected function setGroupBy(\SplObjectStorage $group_by){
		$this->group_by = $group_by;
	}
	
	/**
	 * @method loadData()
	 * @param int report_count
	 * @param string file_format
	 * @return void
	 * @access public
	* */
	protected function loadData($report_count, $file_format){
		$arr_this_block = get_element_by_key($block, $this->{$this->primary_model_name}->arr_blocks);
		$this->max_rows = $arr_this_block['max_rows'];
		$this->cnt_row = $arr_this_block['cnt_row'];
		$this->sum_row = $arr_this_block['sum_row'];
		$this->avg_row = $arr_this_block['avg_row'];
		$this->bench_row = $arr_this_block['bench_row'];
		$this->pivot_db_field = isset($arr_this_block['pivot_db_field']) ? $arr_this_block['pivot_db_field'] : NULL;
		if($this->display == 'table' || $this->display == 'array'){
			$this->load_table($arr_this_block, $report_count);
		}
		elseif($this->display == 'chart'){
			$this->load_chart($arr_this_block, $report_count);
		}
	}
	
	/**
	 * @method loadChildren()
	 * @param \SplObjectStorage children
	 * @return void
	 * @access public
	* */
	public function loadChildren(\SplObjectStorage $children){
		$this->children = $children;
	}
	
	/*
	 * getCompleteData
	 * 
	 * @param int page_id
	 * @author ctranel
	 * @returns SplObjectStorage of Blocks
	public function getCompleteData($page_id){
		$children = new \SplObjectStorage();
		
		$criteria = ['page_id' => $page_id];
		$join = ['pages_block pb' => 'p.id = pb.page_id'];
		$results = $this->datasource_blocks->getByCriteria($criteria, $join);
		if(empty($results)){
			return false;
		}
		return new Page($this->datasource_blocks, $results[0]['id'], $results[0]['parent_id'], $results[0]['name'], $results[0]['description'], $results[0]['scope'], $results[0]['path']);
	}
	 */
	/**
	 * @method loadChildren()
	 * @param int user id
	 * @param Herd herd
	 * @param array task permissions
	 * @return void
	 * @access public
	//if we allow producers to select which sections to allow, we will need to pass that array to this section as well
	public function loadChildren($user_id, $herd, $arr_task_permissions){ 
		$tmp_array = [];
		if(in_array('View All Content', $arr_task_permissions)){
			$criteria = ['page_id' => $this->id];
			$tmp_array = $this->datasource->getByCriteria($criteria);
		}
		 
		//subscription is different in that it fetches content by herd data (i.e. herd output) for users that 
		//have permission only for subscribed content.  All other scopes are strictly users-based
		
		else{
			if(in_array('View Subscriptions', $arr_task_permissions)){
				$tmp_array = array_merge($tmp_array, $this->datasource->getSubscribedSections($user_id, $this->id, $herd->herdCode()));
			}
			if(in_array('View Account', $arr_task_permissions)){
				$criteria = ['ls.name' => 'View Account', 'page_id' => $this->id];
				$tmp_array = array_merge($tmp_array, $this->datasource->getByCriteria($criteria));
			}
			if(in_array('View Admin', $arr_task_permissions)){
				$criteria = ['ls.name' => 'View Admin', 'page_id' => $this->id];
				$tmp_array = array_merge($tmp_array, $this->datasource->getByCriteria($criteria));
			}
		}
		
		if(is_array($tmp_array) && !empty($tmp_array)){
			$this->children = new \SplObjectStorage();
			foreach($tmp_array as $k => $v){
				$this->children->attach(new Section($this->datasource_sections, $this->datasource_pages, $this->datasource_blocks, $v['id'], $v['page_id'], $v['name'], $v['description'], $v['scope'], $v['active'], $v['path']));
			}
		}
	}
	 **/
}


