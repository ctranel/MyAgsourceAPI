<?php
namespace myagsource\Report\Content\Table\Header;

require_once(APPPATH . 'libraries/Report/Content/Table/Header/TableHeaderCell.php');

use \myagsource\Report\Content\Table\Header\TableHeaderCell;
//use \myagsource\Supplemental\iSupplemental;
use \myagsource\Report\iBlock;
use \myagsource\Supplemental\Content\SupplementalFactory;

/**
* Name:  Table Header Library
*
* Author: ctranel
*
* Created: 2015/03/01
*
* Description:  Logic and service functions to support multi-level table headers.
*
* Requirements: PHP5 or above
* 
* @todo: this class works, but needs to be cleaned up and have arrays converted into objects
* @todo: add pdf width info
*
*/

class TableHeader {
	/**
	 * header_structure
	 * 
	 * multi-dimensional array representing html header structure
	 * 
	 * @var array
	 **/
	protected $header_structure;
	
	/**
	 * header_groups
	 * 
	 * @var array
	 **/
	protected $header_groups;
	
	/**
	 * header_group_fields
	 * 
	 * @var array
	 **/
	protected $header_group_fields;
	
	/**
	 * block
	 * 
	 * Report\Block object
	 * @var Block
	 **/
	protected $block_fields;
	
	/**
	 * rows
	 * 
	 * container of TableHeaderRow objects
	 * @var \SplObjectStorage
	 **/
	protected $rows;
	
	/**
	 * supplemental
	 * 
	 * @var SupplementalFactory
	 **/
	protected $supplemental_factory;
	
	public function __construct(iBlock $block, $header_groups, $supplemental_factory){
		$this->header_group_fields = [];
		$this->block = $block;
		$this->header_groups = $header_groups;
		$this->supplemental_factory = $supplemental_factory;
	}
	/**
	 *  @method: columnCount() returns count of columns
	 *  @access public
	 *  @return int
	 **/
	public function columnCount(){
		return $this->block->reportFields()->count();
	}
	
	/**
	 *  @method: get_table_header_array() takes array of data structure and returns and array of menu data including text,
	 * 		colspan, rowspan and level (to be used to create class names in view) *
	 *  @access public
	 **/
	public function getTableHeaderStructure(){
		$depth = 0;
		$rowspan = 1;
		$this->setTableHeaderGroups();
		
		$tot_levels = array_depth($this->header_group_fields);
		$this->header_structure = []; //return value
		$this->getHeaderLayer($this->header_group_fields, $depth, $rowspan, $tot_levels, []);
		ksort($this->header_structure);
		return $this->header_structure;
	}
	
	/**
	 * @method setTableHeaderStructure()
	 * @return multi-dimensional array of header data ('arr_unsortable_columns', 'arr_field_sort', 'arr_header_data')
	 * @author ctranel
	 * 
	 * @todo: pass objects instead of arrays in objects
	 **/
	protected function setTableHeaderGroups(){
		if(is_array($this->header_groups) && !empty($this->header_groups)){
			foreach($this->header_groups as $h){
				$header_group_supplemental = $this->supplemental_factory->getHeaderGrpSupplemental($h['header_group_id'], $h['a_href'], $h['a_rel'], $h['a_title'], $h['a_class'], $h['comment']);
				
				//if it is a top level element
				if($h['parent_id'] == NULL) {
					//$this->header_group_fields[] = new TableHeaderCell($h['id'], $h['parent_id'], $h['text']);//, 'pdf_width' => 0];
					$this->header_group_fields[] = ['id' => $h['id'], 'parent_id' => $h['parent_id'], 'text' => $h['text'], 'children' => null, 'pdf_width' => 0, 'supplemental' => $header_group_supplemental];
				}
				//else it is inserted into the parent array
				else{
					//$this->nest($this->header_group_fields, new TableHeaderCell($h['id'], $h['parent_id'], $h['text']));//, 'pdf_width' => 0];
					$this->nest($this->header_group_fields, ['id' => $h['id'], 'parent_id' => $h['parent_id'], 'text' => $h['text'], 'pdf_width' => 0, 'supplemental' => $header_group_supplemental]);
				}
			}

			//add leaves (columns) to structure
			$fields = $this->block->reportFields();
			foreach($fields as $f){
				if($f->isDisplayed()){
					$this->addLeaf($this->header_group_fields, $f->headerGroupId(), ['children' => ['id' => null, 'parent_id' => $f->headerGroupId(), 'text' => $f->displayName(), 'pdf_width' => $f->pdfWidth(), 'is_sortable' => $f->isSortable(), 'is_displayed' => $f->isDisplayed(), 'default_sort_order' => $f->defaultSortOrder(), 'supplemental' => $f->headerSupplemental()]]);
					//$this->addLeaf($this->header_group_fields, $f->headerGroupId(), new TableHeaderCell($h['id'], $h['parent_id'], $h['text']));
						//['children' => ['id' => null, 'parent_id' => $f->headerGroupId(), 'text' => $f->displayName(), 'pdf_width' => $f->pdfWidth(), 'is_sortable' => $f->isSortable(), 'is_displayed' => $f->isDisplayed()]]);
				}
			}
		}
	}
	
	/** 
	 * @method getHeaderLayer() Takes array of header structure by reference from the parent function, as well as array of data structure, depth level and total number of tiers in the header structure.
	 * Returns and array of menu data including text, colspan, rowspan, level (to be used to create class names in view)
	 * and database field name.
	 *  @access protected
	 *  @param array $arr_header_ section_data
	 *  @param int current depth within hierarchy
	 *  @param int rowspan
	 *  @param int total levels in header array hierarchy
	 *  @param array pdf column widths
	 **/
	protected function getHeaderLayer($arr_data_in, $curr_depth, $rowspan, $tot_levels, $parent_was_empty = FALSE){
		//@todo: supplemental links
		foreach($arr_data_in as $v){
			$v['text'] = trim($v['text']);
			if(empty($v['text'])){ //if the header has no text, keep the current depth, but add one to the rowspan
				$rowspan++;
			}
			else{ //if there is text in the header, increment the depth (not the rowspan), and create an entry in the header structure array
				$curr_depth++;
			}

			if(isset($v['children']) && is_array($v['children'])){
				//get number of leaves and PDF width for this array
				$this->setLeafSums($v);

				//add data to object array ($this->header_structure)
				if(!empty($v['text'])){ //if the header has no text, keep the current depth, but add one to the rowspan
					$tmp = new TableHeaderCell($v['id'], $v['parent_id'], $v['text'], $v['pdf_width'], $v['supplemental']);
					$tmp->setSpan($v['num_leaves'], $rowspan);
					if(isset($v['supplemental'])){
						$tmp->addSupplemental($v['supplemental']);
					}
					$this->header_structure[($curr_depth - 1)][] = $tmp;
				}
				//recursively retrieve header info for this sub-array
				$pass_rowspan = $parent_was_empty && !empty($v['text']) ? 1 : $rowspan;
				$pass_depth = $parent_was_empty && !empty($v['text']) ? $curr_depth + 1 : $curr_depth;
				$this->getHeaderLayer($v['children'], $pass_depth, $pass_rowspan, $tot_levels, empty($v['text']));
			}
			else { //add leaf node
//@todo: add new class (header leaf) that shares interface with or extends TableHeaderCell (instead of setLeafFields)
				$tmp = new TableHeaderCell($v['id'], $v['parent_id'], $v['text'], $v['pdf_width'], $v['supplemental']);
				$tmp->setLeafFields(1, $rowspan, $v['is_sortable'], $v['is_displayed'], $v['default_sort_order'], $v['supplemental']);

				$this->header_structure[($curr_depth - 1)][] = $tmp;
				$this->columns++;
			}
			if(empty($v['text'])) $rowspan--;
			else $curr_depth--;
		}
	}

	/**
	 * getLeafAggregates
	 *
	 * recursively that inserts new value into multi-level array using 'id' and 'parent_id' elements of arrays to be inserted
	 *
	 * @param array into which value will be inserted
	 * @param array of fields to be summmed
	 * @return void
	 * @author ctranel
	 */
	protected function setLeafSums(&$array){
		if (!is_array($array)){
			return false;
		}
		
		if(isset($array['children']) && is_array($array['children'])){
			$sum_pdf = 0;
			$sum_leaves = 0;
			foreach($array['children'] as &$v){
				if(isset($v['children']) && is_array($v['children'])){
					$this->setLeafSums($v);
				}
				$sum_pdf += $v['pdf_width'];
				$sum_leaves += isset($v['num_leaves']) ? $v['num_leaves'] : 1;
			}
			$array['num_leaves'] = $sum_leaves;
			$array['pdf_width'] = $sum_pdf;
		}
	}
	
	/**
	 * nest
	 *
	 * recursively that inserts new value into multi-level array using 'id' and 'parent_id' elements of arrays to be inserted
	 *
	 * @param array into which value will be inserted
	 * @param array key into which child array should be added
	 * @param array value to be inserted
	 * @return void
	 * @author ctranel
	 */
	protected function nest(&$array, $new_val_in){
		if (!is_array($array) || !isset($new_val_in) || !is_array($new_val_in)){
			return false;
		}
		//iterate through flat array to find spot in 
		foreach($array as &$v){
			//we don't want to interate through leaf arrays
			if(!isset($v['id'])){
				continue;
			}
			//add child if a match is found
			if($v['id'] === $new_val_in['parent_id']){
				if(!isset($v['children']) || !is_array($v['children'])){
					$v['children'] = [];
				}
				$v['children'][] = $new_val_in;
				return;
			}
			//if it is not found, and the current node is an array, make recursive call
			elseif(isset($v['children']) && is_array($v['children'])){
				$this->nest($v['children'], $new_val_in);
			}
		}
	}
	
	/**
	 * addLeaf
	 *
	 * inserts leaf at appropriate point in nested array
	 *
	 * @param array into which value will be inserted
	 * @param array key into which child array should be added
	 * @param array value to be inserted
	 * @return void
	 * @author ctranel
	 */
	protected function addLeaf(&$input, $key_in, $new_val_in, $arr_order = NULL){
		if (!is_array($input)){
			return false;
		}
		$cnt = 0;
		$arr_copy = $input;
		foreach ($arr_copy AS $key =>$value){
			//if the array into which $new_val_in is being inserted already has children
			if (isset($input[$key]['children']) && is_array($input[$key]['children'])) {
				foreach($input[$key]['children'] as &$c){
					if (!empty($new_val_in) && !empty($key_in)){
						if($c['id'] == $key_in){
							$c['children'][] = $new_val_in['children'];
						}
					}
//					$this->addLeaf($input[$key], $key_in, $new_val_in, $arr_order);
				}
			}
			//if the array into which $new_val_in is being inserted does not yet have children
			else {
				$saved_value = $value;
				if (!empty($new_val_in)){
					if (!empty($key_in)){
						if($key == $key_in) $value = $new_val_in;
					}
/*
					elseif (is_array($input)){
						//root level $input does not have a key, and cannot have list order.  if key_in is empty, traverse array and insert in appropriate slot
						if(isset($arr_order) && is_array($arr_order) && $arr_order[key($new_val_in)] == ($arr_order[$key] - 1)){
die('4');
							echo $key . ' - ' . $key_in . "1\n\n";
							array_insert($input['children'], $cnt, $new_val_in['children']);
						}
						elseif($arr_order[key($new_val_in)] == count($arr_order) && $arr_order[key($new_val_in)] == $arr_order[$key]){
die('5');
							echo $key . ' - ' . $key_in . "2\n\n";
							$input[$key]['children'] = $new_val_in[$key]['children'];
							return true;
						}
					}
					if ($value != $saved_value){
die('6');
						echo $key . ' - ' . $key_in . "3\n\n";
						$input[$key]['children'] = $value['children'];
						return true;
					}
 */
				}
			}
			$cnt++;
		}
		return true;
	}
}
