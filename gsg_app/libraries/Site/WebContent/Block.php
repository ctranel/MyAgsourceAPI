<?php
namespace myagsource\Site\WebContent;

require_once APPPATH . 'libraries/Site/iWebContent.php';
//require_once APPPATH . 'libraries/Site/iWebContentRepository.php';

use myagsource\Site\iWebContent;
use myagsource\Site\iWebContentRepository;
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

class Block implements iWebContent {
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
	 * display_type
	 * @var string
	 **/
	protected $display_type;
	
	/**
	 * is_summary
	 * @var boolean
	protected $is_summary;
	 **/
	
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
	public function __construct($id, $page_id, $name, $description, $display_type, $scope, $active, $path) {
		$this->page_id = $page_id;
		$this->name = $name;
		$this->description = $description;
		$this->display_type = $display_type;
		$this->scope = $scope;
		$this->active = $active;
		$this->path = $path;
		$this->id = $id;
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

	public function displayType(){
		return $this->display_type;
	}

	public function children(){
		return $this->report_fields;
	}

	/**
	 * @method loadData()
	 * @param int report_count
	 * @return void
	 * @access public
	protected function loadData($report_count){
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
	* */
	
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


