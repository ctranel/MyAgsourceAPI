<?php
namespace myagsource\Report\Content;

use myagsource\Supplemental\iSupplemental;
use myagsource\Report\Content\iBlock;
/**
* Name:  Table Header Library
*
* Author: ctranel
*
* Created:  04.07.2014
*
* Description:  Logic and service functions to support multi-level table headers.
*
* Requirements: PHP5 or above
*
*/

class TableHeader
{
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
	 * block
	 * 
	 * Report\Block object
	 * @var Block
	 **/
	protected $block_fields;
	
	/**
	 * structure
	 * 
	 * nested array representation of header structure
	 * @var array
	 **/
	protected $structure;
	
	
	//protected $arr_header_data;
	//protected $depth;
	//protected $rowspan;
	//protected $tot_levels;
	//protected $arr_pdf_widths;
	//protected $columns;
	
	public function __construct(iBlock $block, $header_groups){//, iSupplemental $supplemental = null){
		$this->block = $block;
		$this->header_groups = $header_groups;
		//$this->supplemental = $supplemental;
	}
	/**
	 *  @method: getColumnCount() returns count of columns
	 *  @access public
	 *  @return int
	 **/
	public function getColumnCount(){
		return $this->block->ReportFields()->count();
	}
	
	/**
	 * @method getTableHeaderData()
	 * @return multi-dimensional array of header data ('arr_unsortable_columns', 'arr_field_sort', 'arr_header_data')
	 * @author ctranel
	 **/
	function getTableHeaderData($arr_dates = null){
		$arr_fields = [];
		$arr_ref = [];
		$arr_order = [];

		//KLM - Added logic to convert header text to date text from herd_model function get_test_dates_7_short
		if(is_array($this->header_groups) && !empty($this->header_groups)){
			foreach($this->header_groups as &$ag){
				$arr_ref[$ag['id']] = (string)$ag['text'];
				$arr_order[(string)$ag['text']] = $ag['list_order'];
				$c = 0;
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
			}
			unset($ag);
		//end KLM	
		
			foreach($this->header_groups as $h){
				$h['text'] = (string)$h['text'];
				if($h['parent_id'] == NULL) {
					$arr_fields[$h['text']] = $h['id'];
				}
				else{
					set_element_by_key($arr_fields, $arr_ref[$h['parent_id']], array((string)$h['text'] => $h['id']));
				}
			}
		}

/*
@todo: just use object in view instead???		
		$unsortable = [];
		
		foreach($this->block->ReportFields() as $b){
			if(!$b->isSortable()){
				$unsortable[] = $b->dbFieldName();
			}
		}
 */		
		$table_header_data = array(
			'arr_unsortable_columns' => $this->arr_unsortable_columns,
			'arr_field_sort' => $this->arr_field_sort,
			'arr_header_data' => $this->arr_fields,
			'arr_header_links' => $this->arr_header_links,
		);
		$this->structure = $this->getTableHeaderStructure();
		//$table_header_data['num_columns'] = $this->getColumnCount();
		return array('arr_ref' => $arr_ref, 'arr_fields' => $arr_fields, 'arr_order' => $arr_order);
//		return $table_header_data;
	}
	
	/**
	 *  @method: get_table_header_array() takes array of data structure and returns and array of menu data including text,
	 * 		colspan, rowspan and level (to be used to create class names in view) *
	 *  @access public
	 **/
	protected function getTableHeaderStructure(){
		$depth = 0;
		$rowspan = 1;
		$tot_levels = array_depth($arr_header_data);
		$this->arr_header_structure = []; //return value
		$this->getHeaderLayer($arr_header_data, $depth, $rowspan, $tot_levels);
		ksort($this->arr_header_structure);
		return $this->arr_header_structure;
	} //end function table_header_cell
	
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
		foreach($arr_data_in as $k => $v){
			$trim_k = trim($k);
			if(empty($trim_k)){ //if the header has no text, keep the current depth, but add one to the rowspan
				$rowspan++;
			}
			else{ //if there is text in the header, increment the depth (not the rowspan), and create an entry in the header structure array
				$curr_depth++;
			}
			if(is_array($v)){
				//get number of leaves and PDF width for this array
				$num_leaves = 0;
				$pdf_width = 0;
				array_walk_recursive( 
					$v,
					create_function(
						'$val, $key, $obj',
						'$obj["num_leaves_in"] = $obj["num_leaves_in"] + 1; if(!empty($obj["arr_pdf_widths"])) $obj["pdf_width"] += $obj["arr_pdf_widths"][$val];'
					),
					array('num_leaves_in' => &$num_leaves, 'pdf_width' => &$pdf_width, 'arr_pdf_widths' => $arr_pdf_widths)
				);
				//add data to object array ($this->arr_header_structure)
				if(!empty($trim_k)){ //if the header has no text, keep the current depth, but add one to the rowspan
					$this->arr_header_structure[($curr_depth - 1)][] = Array('text' => $k, 'colspan' => $num_leaves, 'rowspan' => $rowspan, 'pdf_width' => $pdf_width);
				}
				//recursively retrieve header info for this sub-array
				$pass_rowspan = $parent_was_empty && !empty($trim_k) ? 1 : $rowspan;
				$pass_depth = $parent_was_empty && !empty($trim_k) ? $curr_depth + 1 : $curr_depth;
				$this->getHeaderLayer($v, $pass_depth, $pass_rowspan, $tot_levels, $arr_pdf_widths, empty($trim_k));
			}
			else { //add leaf node
				$this->arr_header_structure[($curr_depth - 1)][] = Array('text' => $k, 'colspan' => '1', 'rowspan' => $rowspan, 'field_name' => $v);
				$this->columns++;
			}
			if(empty($trim_k)) $rowspan--;
			else $curr_depth--;
		}
	}
}
