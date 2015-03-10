<?php
namespace myagsource\Report\Content\Table\Header;

//require_once(APPPATH . 'libraries/Report/Content/Table/Header/TableHeaderRow.php');


//use \myagsource\Report\Content\Table\Header\TableHeaderRow;
use myagsource\Supplemental\iSupplemental;
use myagsource\Report\iBlock;
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
	 * supplemental
	 * 
	 * @var iSupplemental
	 **/
	protected $supplemental;
	
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
	
	
	//protected $arr_header_data;
	//protected $depth;
	//protected $rowspan;
	//protected $tot_levels;
	//protected $arr_pdf_widths;
	//protected $columns;
	
	public function __construct(iBlock $block, $header_groups){//, iSupplemental $supplemental = null){
		$this->header_group_fields = [];
		$this->block = $block;
		$this->header_groups = $header_groups;
		//$this->supplemental = $supplemental;
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
	public function getTableHeaderStructure($arr_dates = null){
		$depth = 0;
		$rowspan = 1;
		$this->setTableHeaderGroups($arr_dates);
		
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
	 **/
	protected function setTableHeaderGroups($arr_dates = null){
//var_dump($arr_dates);
		if(is_array($this->header_groups) && !empty($this->header_groups)){
			foreach($this->header_groups as &$ag){
				$c = 0;
		//@todo: KLM block should not be in this class--controller?
		//KLM - Added logic to convert header text to date text from herd_model function get_test_dates_7_short
				if(isset($arr_dates) && is_array($arr_dates)){
					foreach($arr_dates[0] as $key => $value){
						if ($key == $ag['text']) {
							if ($value == '0-0') {
								$value='No Test (-'.$c.')';
							}
							$ag['text'] = $value;
							break;
						}
						$c++;
					}
				}
		//end KLM	
			}
			unset($ag);
			foreach($this->header_groups as $h){
				//if it is a top level element
				if($h['parent_id'] == NULL) {
					$this->header_group_fields[$h['id']] = ['id' => $h['id'], 'parent_id' => $h['parent_id'], 'text' => $h['text'], 'children' => null, 'pdf_width' => 0];
				}
				//else it is inserted into the parent array
				else{
					$this->addChildByKey($this->header_group_fields, $h['parent_id'], ['children' => [['id' => $h['id'], 'parent_id' => $h['parent_id'], 'text' => $h['text'], 'pdf_width' => 0]]]);
				}
			}
			
			$fields = $this->block->reportFields();
			foreach($fields as $f){
				if($f->isDisplayed()){
					$this->addChildByKey($this->header_group_fields, $f->headerGroupId(), ['children' => ['id' => null, 'parent_id' => $f->headerGroupId(), 'text' => $f->displayName(), 'pdf_width' => $f->pdfWidth(), 'is_sortable' => $f->isSortable(), 'is_displayed' => $f->isDisplayed()]]);
				}
			}
		}

		
		//$this->structure = $this->getTableHeaderStructure($this->header_group_fields);
	}
	
	/** 
	 * @method getHeaderLayer() Takes array of header structure by reference from the parent function, as well as array of data structure, depth level and total number of tiers in the header structure.
	 * Returns and array of menu data including text, colspan, rowspan, level (to be used to create class names in view)
	 * and database field name.
	 *  @access protected
	 *  @param array $arr_header_ section_data
	 *  @param int current depth within hierarchy
	 *  @param int total levels in header array hierarchy
	 *  @param array pdf column widths
	 **/
	protected function getHeaderLayer($arr_data_in, $curr_depth, $rowspan, $tot_levels, $arr_pdf_widths, $parent_was_empty = FALSE){
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
				$num_leaves = 0;
				$pdf_width = 0;

				array_walk_recursive( 
					$v,
					create_function(
						'$val, $key, $obj',
						'
								if($key === "id" && !isset($val)) {
									$obj["num_leaves_in"] = $obj["num_leaves_in"] + 1; 
									if(isset($obj["node"]["pdf_width"]) && !empty($obj["node"]["pdf_width"])) $obj["pdf_width_in"] += $obj["node"]["pdf_width"];
								}
						'
					),
					['num_leaves_in' => &$num_leaves, 'pdf_width_in' => &$pdf_width, 'node' => $v]
				);

				//add data to object array ($this->header_structure)
				if(!empty($v['text'])){ //if the header has no text, keep the current depth, but add one to the rowspan
					//new TableHeaderCell($v['id'], $v['parent_id'], $v['text'], $num_leaves, $rowspan);
					$this->header_structure[($curr_depth - 1)][] = ['text' => $v['text'], 'colspan' => $num_leaves, 'rowspan' => $rowspan, 'pdf_width' => $pdf_width];
				}
				//recursively retrieve header info for this sub-array
				$pass_rowspan = $parent_was_empty && !empty($v['text']) ? 1 : $rowspan;
				$pass_depth = $parent_was_empty && !empty($v['text']) ? $curr_depth + 1 : $curr_depth;
				$this->getHeaderLayer($v['children'], $pass_depth, $pass_rowspan, $tot_levels, $arr_pdf_widths, empty($v['text']));
			}
			else { //add leaf node
				//new TableHeaderCell($v['id'], $v['parent_id'], $v['text'], $num_leaves, $rowspan);
				$this->header_structure[($curr_depth - 1)][] =['text' => $v['text'], 'colspan' => 1, 'rowspan' => $rowspan, 'pdf_width' => $v['pdf_width']];
				$this->columns++;
			}
			if(empty($v['text'])) $rowspan--;
			else $curr_depth--;
		}
	}
	
	/**
	 * addChildByKey
	 *
	 * @description Sets value for first match of $key in mulitdimensional array
	 *
	 * @param array into which value will be inserted
	 * @param array key into which child array should be added
	 * @param array value to be inserted
	 * @return void
	 * @author ctranel
	 */
	protected function addChildByKey(&$input, $key_in, $new_val_in, $arr_order = NULL){
		if (!is_array($input)){
			return false;
		}
		$cnt = 0;
		$arr_copy = $input;
		foreach ($arr_copy AS $key =>$value){
			//$key = $value['id'];
			if (isset($input[$key]['children']) && is_array($input[$key]['children'])) {
				foreach($input[$key]['children'] as &$c){
					if (!empty($new_val_in) && !empty($key_in)){
						if($c['id'] == $key_in){
							$c['children'][] = $new_val_in['children'];
						}
					}
					$this->addChildByKey($input[$key], $key_in, $new_val_in, $arr_order);
				}
			}
			else {
				$saved_value = $value;
				if (!empty($new_val_in)){
					if (!empty($key_in)){
						if($key == $key_in) $value = $new_val_in;
					}
					elseif (is_array($input)){
						//root level $input does not have a key, and cannot have list order.  if key_in is empty, traverse array and insert in appropriate slot
						if(isset($arr_order) && is_array($arr_order) && $arr_order[key($new_val_in)] == ($arr_order[$key] - 1)){
							array_insert($input['children'], $cnt, $new_val_in['children']);
						}
						elseif($arr_order[key($new_val_in)] == count($arr_order) && $arr_order[key($new_val_in)] == $arr_order[$key]){
							$input[$key]['children'] = $new_val_in[$key]['children'];
							return true;
						}
					}
					if ($value != $saved_value){
						$input[$key]['children'] = $value['children'];
						return true;
					}
				}
			}
			$cnt++;
		}
		return true;
	}
}
