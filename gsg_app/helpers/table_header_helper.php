<?php
/**
 *  @method: get_table_header_array() takes array of data structure and returns and array of menu data including text,
 * 		colspan, rowspan and level (to be used to create class names in view) *
 *  @access public
 *  @param array $arr_header_data 
 *  @param array pdf widths
 *  @param int total levels in header array hierarchy
 **/
function get_table_header_array($arr_header_data, $arr_pdf_widths = array()){
	$depth = 0;
//var_dump($arr_header_data);
	$tot_levels = array_depth($arr_header_data);
	$arr_header_structure = array(); //return value
	getHeaderLayer($arr_header_structure, $arr_header_data, $depth, $tot_levels, $arr_pdf_widths);// not currently used, too taxing on memory.
//var_dump($arr_header_structure);
	return $arr_header_structure;
} //end function table_header_cell

/** 
 * @method getHeaderLayer() Takes array of header structure by reference from the parent function, as well as array of data structure, depth level and total number of tiers in the header structure.
 * Returns and array of menu data including text, colspan, rowspan, level (to be used to create class names in view)
 * and database field name.
 *  @access public
 *  @param array $arr_header_structure
 *  @param array $arr_header_ section_data
 *  @param int current depth within hierarchy
 *  @param int total levels in header array hierarchy
 *  @param array pdf column widths
 **/
function getHeaderLayer(&$arr_header_structure, $arr_data_in, &$depth, $tot_levels, $arr_pdf_widths){
	foreach($arr_data_in as $k => $v){
		if(is_array($v)){
			//get number of leaves and PDF width for this array
			$num_leaves = 0;
			$pdf_width = 0;
			//array_walk_recursive($v, create_function('$val, $key, $obj', '$obj["num_leaves_in"] = $obj["num_leaves_in"] + 1;'), array('num_leaves_in' => &$num_leaves));
			array_walk_recursive(
				$v,
				create_function(
					'$val, $key, $obj',
					'$obj["num_leaves_in"] = $obj["num_leaves_in"] + 1; if(!empty($obj["arr_pdf_widths"])) $obj["pdf_width"] += $obj["arr_pdf_widths"][$val];'
				),
				array('num_leaves_in' => &$num_leaves, 'pdf_width' => &$pdf_width, 'arr_pdf_widths' => $arr_pdf_widths)
			);
			//add data to return array
echo 'cols: ' . $depth . ' - ' . $k . ' - ' . $num_leaves . "\n";
			$arr_header_structure[$depth][] = Array('text' => $k, 'colspan' => $num_leaves, 'rowspan' => '1', 'pdf_width' => $pdf_width);
			getHeaderLayer($arr_header_structure, $v, ++$depth, $tot_levels, $arr_pdf_widths);
		}
		else {
echo 'leaf: ' . $depth . ' - ' . $k . ' - ' . "\n";
			$rowspan = $tot_levels - $depth;
			$arr_header_structure[$depth][] = Array('text' => $k, 'colspan' => '1', 'rowspan' => $rowspan, 'field_name' => $v);
		}
	}
	$depth--; //revert to the depth from before the array was processed.
}

/** *takes array of data structure and returns and array of menu data including text,
 * colspan, rowspan and level (to be used to create class names in view) *
 *  @access public
 *  @param array $arr_header_data
 */
function get_csv_header_array($arr_header_data){
	$tot_levels = 3; //need to make this dynamic -- snippets at bottom of page for seed code
	$depth = 0;
	$arr_header_structure = Array(); //return value
	get_csv_header($arr_header_structure, $arr_header_data, $depth, $tot_levels);
	return $arr_header_structure;
} //end function table_header_cell

/** 
 * Takes array of header structure by reference from the parent function, as well as array of data structure, depth level and total number of tiers in the header structure.
 * Returns and array of menu data including text, colspan, rowspan, level (to be used to create class names in view)
 * and database field name.
 *  @access public
 *  @param array $arr_header_structure
 *  @param array data in
 *  @param int current depth within hierarchy
 *  @param int total levels in header array hierarchy
 **/
function get_csv_header(&$arr_header_structure, $arr_data_in, &$depth, $tot_levels){
	foreach($arr_data_in as $k => $v){
		if(is_array($v)){
			//get number of leaves for this array
			$num_leaves = 0;
			array_walk_recursive($v, create_function('$val, $key, $obj', '$obj["num_leaves_in"] = $obj["num_leaves_in"] + 1;'), array('num_leaves_in' => &$num_leaves));
			//add data to return array
			$arr_header_structure[] = Array('text' => $k, 'colspan' => $num_leaves, 'rowspan' => '1');
			getHeaderLayer($arr_header_structure, $v, ++$depth, $tot_levels);
		}
		else {
			$rowspan = $tot_levels - $depth;
			$arr_header_structure[$v] = Array('text' => $k, 'colspan' => '1', 'rowspan' => $rowspan, 'field_name' => $v);
		}
	}
	$depth--; //revert to the depth from before the array was processed.
}
